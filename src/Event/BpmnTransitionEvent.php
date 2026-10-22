<?php

namespace Wexample\SymfonyBpmn\Event;

use Wexample\SymfonyBpmn\Class\CompiledTransition;
use Wexample\SymfonyBpmn\Interface\BpmnSubjectInterface;

/**
 * A step of a process taken: a task done, an event happened, a join passed —
 * by someone, or by the application (`system`).
 */
final class BpmnTransitionEvent
{
    public function __construct(
        public readonly BpmnSubjectInterface $subject,
        public readonly string $processId,
        public readonly CompiledTransition $transition,
        public readonly bool $system,
    ) {
    }
}
