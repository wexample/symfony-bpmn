<?php

namespace Wexample\SymfonyBpmn\Service;

/**
 * Whether the application acts now — an automatic task, a timer, a message
 * received —, or a person: what the guards ask before checking a lane.
 */
class BpmnActorContext
{
    private int $system = 0;

    /**
     * @template T
     *
     * @param callable(): T $action
     *
     * @return T
     */
    public function asSystem(callable $action): mixed
    {
        ++$this->system;

        try {
            return $action();
        } finally {
            --$this->system;
        }
    }

    public function isSystem(): bool
    {
        return $this->system > 0;
    }
}
