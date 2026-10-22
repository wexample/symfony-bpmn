<?php

namespace Wexample\SymfonyBpmn\Tests\Integration;

use Wexample\SymfonyBpmn\Entity\BpmnNode;
use Wexample\SymfonyBpmn\Service\BpmnViewService;

/**
 * The examples read as the lists a screen shows: every node drawn, every flow
 * routed, the lanes in bands, the paths and the rights listed, the same
 * entities read twice.
 */
class ViewTest extends AbstractBpmnTestCase
{
    public function testEveryProcessIsListed(): void
    {
        $ids = array_map(static fn ($process) => $process->getProcessId(), $this->view()->processes());

        foreach (['tenant_move_in', 'maintenance_request', 'visit_booking', 'engagement_letter', 'article_review'] as $id) {
            $this->assertContains($id, $ids);
        }
    }

    public function testOneProcessIsReadAsTheListHoldsIt(): void
    {
        $process = $this->view()->process('article_review');

        $this->assertSame('Article review', $process->getName());
        $this->assertSame(3, $process->getLaneCount());
        $this->assertTrue($process->getValid());
    }

    public function testEveryNodeIsDrawnAndEveryFlowRouted(): void
    {
        $view = $this->view();

        foreach ($view->processes() as $process) {
            $id = $process->getProcessId();

            foreach ($view->nodes($id) as $node) {
                $this->assertGreaterThan(0, $node->getWidth(), "$id: {$node->getNodeId()} has no size.");
            }

            foreach ($view->flows($id) as $flow) {
                $this->assertGreaterThanOrEqual(2, count($flow->getPoints()), "$id: {$flow->getFlowId()} has no route.");
            }

            foreach ($view->lanes($id) as $lane) {
                $this->assertGreaterThan(0, $lane->getHeight(), "$id: {$lane->getLaneId()} has no band.");
            }
        }
    }

    public function testANodeSaysWhatItIs(): void
    {
        $nodes = $this->view()->nodes('article_review');
        $kinds = array_unique(array_map(static fn (BpmnNode $node) => $node->getKind(), $nodes));

        $this->assertContains('event', $kinds);
        $this->assertContains('task', $kinds);
        $this->assertContains('gateway', $kinds);
    }

    public function testPathsRightsAndIssuesAreListed(): void
    {
        $view = $this->view();

        $this->assertNotEmpty($view->paths('engagement_letter'));
        $this->assertNotEmpty($view->rights('engagement_letter'));
        $this->assertIsArray($view->issues('engagement_letter'));

        foreach ($view->paths('engagement_letter') as $path) {
            $this->assertNotEmpty($path->getSteps());
        }
    }

    public function testTheSameListGivesTheSameEntities(): void
    {
        $ids = static fn (array $nodes) => array_map(static fn ($node) => (string) $node->getId(), $nodes);

        $this->assertSame($ids($this->view()->nodes('visit_booking')), $ids($this->view()->nodes('visit_booking')));
    }

    private function view(): BpmnViewService
    {
        return self::getContainer()->get('test.view');
    }
}
