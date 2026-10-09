<?php

namespace Wexample\SymfonyBpmn\Class;

use Wexample\PhpBpmn\Class\Element\Definitions;
use Wexample\PhpBpmn\Class\Element\Process;

/**
 * A process as a Petri net: its places — where it waits: an activity, an
 * event, an event-based gateway, an end, an arrival at a parallel join —,
 * the start place, and its transitions.
 */
final class CompiledProcess
{
    /**
     * @param list<string>                      $places
     * @param list<string>                      $ends
     * @param array<string, CompiledTransition> $transitions
     */
    public function __construct(
        public readonly Definitions $definitions,
        public readonly Process $process,
        public readonly array $places,
        public readonly string $initialPlace,
        public readonly array $ends,
        public readonly array $transitions,
    ) {
    }

    public function getWorkflowName(): string
    {
        return 'bpmn.' . $this->process->getId();
    }

    public function getTransition(string $name): ?CompiledTransition
    {
        return $this->transitions[$name] ?? null;
    }

    /**
     * The transitions completing a node: a task's ways out, its boundary
     * events.
     *
     * @return list<CompiledTransition>
     */
    public function getTransitionsOf(string $nodeId): array
    {
        return array_values(array_filter($this->transitions, static fn (CompiledTransition $transition) => $transition->node === $nodeId));
    }

    /**
     * The transitions leaving a place.
     *
     * @return list<CompiledTransition>
     */
    public function getTransitionsFrom(string $place): array
    {
        return array_values(array_filter($this->transitions, static fn (CompiledTransition $transition) => in_array($place, $transition->from, true)));
    }
}
