<?php

namespace Wexample\SymfonyBpmn\Tests\Integration;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Wexample\SymfonyBpmn\Event\BpmnMessageEvent;
use Wexample\SymfonyBpmn\Event\BpmnTransitionEvent;
use Wexample\SymfonyBpmn\Service\BpmnRunner;
use Wexample\SymfonyBpmn\Tests\Fixtures\App\Entity\Folder;
use Wexample\SymfonyBpmn\Tests\Fixtures\App\Handler\RecordingTimerScheduler;

abstract class AbstractBpmnTestCase extends KernelTestCase
{
    /** @var list<BpmnMessageEvent> */
    protected array $messages = [];

    /** @var list<BpmnTransitionEvent> */
    protected array $transitions = [];

    protected function setUp(): void
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        (new SchemaTool($entityManager))->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $dispatcher = self::getContainer()->get('event_dispatcher');
        $dispatcher->addListener(BpmnMessageEvent::class, fn (BpmnMessageEvent $event) => $this->messages[] = $event);
        $dispatcher->addListener(BpmnTransitionEvent::class, fn (BpmnTransitionEvent $event) => $this->transitions[] = $event);
    }

    protected function runner(): BpmnRunner
    {
        return self::getContainer()->get('test.runner');
    }

    protected function timers(): RecordingTimerScheduler
    {
        return self::getContainer()->get(RecordingTimerScheduler::class);
    }

    protected function folder(?string $team = null): Folder
    {
        return (new Folder())->setTeam($team);
    }

    protected function signIn(string ...$roles): void
    {
        $user = new InMemoryUser('someone', null, $roles);
        self::getContainer()->get('test.token_storage')->setToken(new UsernamePasswordToken($user, 'main', $roles));
    }

    /**
     * @return list<string>
     */
    protected function messageNodes(): array
    {
        return array_map(static fn (BpmnMessageEvent $event) => $event->nodeId, $this->messages);
    }
}
