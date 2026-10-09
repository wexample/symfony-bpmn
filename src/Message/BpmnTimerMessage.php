<?php

namespace Wexample\SymfonyBpmn\Message;

/**
 * A timer to fire: the subject, by class and id, and the transition it
 * fires — tried only if the subject still waits for it.
 */
final readonly class BpmnTimerMessage
{
    public function __construct(
        public string $subjectClass,
        public string $subjectId,
        public string $processId,
        public string $transition,
    ) {
    }
}
