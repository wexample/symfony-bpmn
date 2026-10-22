<?php

namespace Wexample\SymfonyBpmn\Interface;

use DateTimeImmutable;

/**
 * Has a timer fire later: a timer event to fire at a date, if the subject
 * still waits for it then.
 */
interface BpmnTimerSchedulerInterface
{
    public function schedule(BpmnSubjectInterface $subject, string $processId, string $timer, DateTimeImmutable $fireAt): void;
}
