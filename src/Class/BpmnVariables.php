<?php

namespace Wexample\SymfonyBpmn\Class;

use ArrayAccess;

/**
 * The variables of a subject as its conditions read them: one not set yet
 * — the decision that sets it still ahead — reads as null, without a
 * warning.
 *
 * @implements ArrayAccess<string, mixed>
 */
final class BpmnVariables implements ArrayAccess
{
    /**
     * @param array<string, mixed> $values
     */
    public function __construct(
        private readonly array $values,
    ) {
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->values[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->values[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    public function offsetUnset(mixed $offset): void
    {
    }
}
