<?php

namespace Wexample\SymfonyBpmn\Class;

/**
 * Where completing a node leads: the places marked next, through the
 * gateways passed, under their conditions — and the throw events passed on
 * the way, raised as it is taken.
 */
final readonly class Route
{
    /**
     * @param list<string> $to         the places marked
     * @param list<string> $conditions what must hold
     * @param list<string> $negated    what must not — the other ways of a default
     * @param list<string> $throws     the throw events passed
     * @param list<string> $flows      the sequence flows taken
     */
    public function __construct(
        public array $to,
        public array $conditions = [],
        public array $negated = [],
        public array $throws = [],
        public array $flows = [],
    ) {
    }

    public function merge(self $other): self
    {
        return new self(
            array_values(array_unique([...$this->to, ...$other->to])),
            [...$this->conditions, ...$other->conditions],
            [...$this->negated, ...$other->negated],
            [...$this->throws, ...$other->throws],
            [...$this->flows, ...$other->flows],
        );
    }

    public function prepend(?string $condition, array $negated, ?string $throw, string $flow): self
    {
        return new self(
            $this->to,
            $condition === null ? $this->conditions : [$condition, ...$this->conditions],
            [...$negated, ...$this->negated],
            $throw === null ? $this->throws : [$throw, ...$this->throws],
            [$flow, ...$this->flows],
        );
    }
}
