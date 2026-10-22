<?php

namespace Wexample\SymfonyBpmn\Service;

use Symfony\Contracts\Service\ResetInterface;

/**
 * Whether the application acts now — an automatic task, a timer, a message
 * received —, or a person: what the guards ask before checking a lane.
 */
class BpmnActorContext implements ResetInterface
{
    private int $system = 0;

    /**
     * @template T
     *
     * @param callable(): T $action
     *
     * @return T
     */

    /**
     * Balanced by asSystem() even when the action throws; reset all the same,
     * for no request to ever start with the rights of the one before.
     */
    public function reset(): void
    {
        $this->system = 0;
    }

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
