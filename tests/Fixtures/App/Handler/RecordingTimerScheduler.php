<?php

namespace Wexample\SymfonyBpmn\Tests\Fixtures\App\Handler;

use DateTimeImmutable;
use Wexample\SymfonyBpmn\Interface\BpmnSubjectInterface;
use Wexample\SymfonyBpmn\Interface\BpmnTimerSchedulerInterface;

/**
 * Keeps the timers instead of sending them: a test fires them.
 */
class RecordingTimerScheduler implements BpmnTimerSchedulerInterface
{
    /** @var list<array{string, DateTimeImmutable}> */
    public array $scheduled = [];

    public function schedule(BpmnSubjectInterface $subject, string $processId, string $timer, DateTimeImmutable $fireAt): void
    {
        $this->scheduled[] = [$timer, $fireAt];
    }

    /**
     * @return list<string>
     */
    public function getTransitions(): array
    {
        return array_map(static fn (array $timer) => $timer[0], $this->scheduled);
    }
}
