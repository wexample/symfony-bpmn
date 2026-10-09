<?php

namespace Wexample\SymfonyBpmn\Tests\Integration;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Wexample\PhpBpmn\Class\Path\Path;
use Wexample\SymfonyBpmn\Service\BpmnScenarioService;

/**
 * Every story of a process, played by its lanes.
 */
class ScenarioTest extends AbstractBpmnTestCase
{
    private const array ROLES = [
        'manager' => 'ROLE_MANAGER',
        'partner' => 'ROLE_PARTNER',
        'client' => 'ROLE_CLIENT',
        'agent' => 'ROLE_AGENT',
        'tenant' => 'ROLE_TENANT',
        'landlord' => 'ROLE_OWNER',
        'inspector' => 'ROLE_INSPECTOR',
    ];

    public function testEveryPathOfTheEngagementLetterIsPlayedToItsEnd(): void
    {
        $scenarios = $this->scenarios();
        $paths = $scenarios->getPaths('engagement_letter', maxVisits: 2);

        // Risk high or not, the client reminded once or not.
        $this->assertCount(4, $paths);

        foreach ($paths as $path) {
            $letter = $this->folder();
            $risk = str_contains($path->describe(), 'high') ? 'high' : 'low';

            $scenarios->play($letter, $path, fn (?string $lane) => $this->signIn(self::ROLES[$lane] ?? 'ROLE_USER'), [
                'client_identified' => ['risk' => $risk],
            ], 'engagement_letter');

            $this->assertTrue($this->runner()->isFinished($letter), $path->describe());
            $this->assertSame(['renewed'], $this->runner()->getWaitingNodes($letter));
        }
    }

    public function testEveryPathOfTheMaintenanceRequestIsPlayed(): void
    {
        $scenarios = $this->scenarios();

        foreach ($scenarios->getPaths('maintenance_request') as $path) {
            $request = $this->folder();
            $above = in_array('ask_landlord', $path->getNodeIds(), true);

            $scenarios->play($request, $path, fn (?string $lane) => $this->signIn(self::ROLES[$lane] ?? 'ROLE_USER'), [
                'get_quote' => ['quote' => $above ? 500 : 100],
                'landlord_decides' => ['approved' => in_array('order_works', $path->getNodeIds(), true)],
                'works_done_received' => [],
            ], 'maintenance_request');

            $this->assertSame($path->getEnds(), $this->runner()->getWaitingNodes($request), $path->describe());
        }
    }

    public function testTheCommandsTellTheProcesses(): void
    {
        $application = new Application(self::$kernel);

        foreach ([['bpmn:list', []], ['bpmn:validate', []], ['bpmn:paths', ['process' => 'maintenance_request']], ['bpmn:matrix', ['process' => 'engagement_letter']], ['bpmn:dump', ['process' => 'tenant_move_in']]] as [$name, $arguments]) {
            $tester = new CommandTester($application->find($name));
            $this->assertSame(0, $tester->execute($arguments), $name . "\n" . $tester->getDisplay());
        }

        $tester = new CommandTester($application->find('bpmn:matrix'));
        $tester->execute(['process' => 'maintenance_request']);
        $this->assertStringContainsString('ROLE_OWNER', $tester->getDisplay());
    }

    private function scenarios(): BpmnScenarioService
    {
        return self::getContainer()->get('test.scenarios');
    }
}
