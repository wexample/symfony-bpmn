<?php

namespace Wexample\SymfonyBpmn\Service;

use DateTimeImmutable;
use Doctrine\Persistence\Proxy;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Wexample\SymfonyBpmn\Interface\BpmnSubjectInterface;
use Wexample\SymfonyBpmn\Interface\BpmnTimerSchedulerInterface;
use Wexample\SymfonyBpmn\Message\BpmnTimerMessage;

/**
 * Timers as delayed Messenger messages: route BpmnTimerMessage to a
 * transport that keeps them — Doctrine — and consume it; sent synchronously,
 * a timer would fire at once.
 */
class MessengerTimerScheduler implements BpmnTimerSchedulerInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function schedule(BpmnSubjectInterface $subject, string $processId, string $transition, DateTimeImmutable $fireAt): void
    {
        $delay = max(0, ($fireAt->getTimestamp() - time()) * 1000);

        $this->bus->dispatch(
            new BpmnTimerMessage($subject instanceof Proxy ? get_parent_class($subject) : $subject::class, (string) $subject->getId(), $processId, $transition),
            [new DelayStamp($delay)]
        );
    }
}
