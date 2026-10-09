<?php

namespace Wexample\SymfonyBpmn\Class;

use Wexample\PhpBpmn\Enum\TimerKind;

/**
 * One transition of a compiled process: completing `node` — a task, an
 * event, a boundary event of its activity, a parallel join — from the
 * places `from` to the places `to`.
 */
final readonly class CompiledTransition
{
    public const string KIND_START = 'start';
    public const string KIND_TASK = 'task';
    public const string KIND_EVENT = 'event';
    public const string KIND_BOUNDARY = 'boundary';
    public const string KIND_JOIN = 'join';

    /**
     * @param list<string> $from
     * @param list<string> $to
     * @param list<string> $conditions
     * @param list<string> $negated
     * @param list<string> $throws
     * @param list<string> $flows   the sequence flows taken
     */
    public function __construct(
        public string $name,
        public string $node,
        public string $kind,
        public array $from,
        public array $to,
        public ?string $lane = null,
        public bool $automatic = false,
        public array $conditions = [],
        public array $negated = [],
        public array $throws = [],
        public ?TimerKind $timerKind = null,
        public ?string $timer = null,
        public ?string $message = null,
        public ?string $label = null,
        public array $flows = [],
    ) {
    }

    /**
     * Fired by a timer, not by anyone.
     */
    public function isTimed(): bool
    {
        return $this->timer !== null;
    }

    /**
     * Done by a person of its lane: neither the application, nor a timer,
     * nor a message received.
     */
    public function isHuman(): bool
    {
        return ! $this->automatic && ! $this->isTimed() && $this->message === null && $this->kind !== self::KIND_JOIN;
    }

    /**
     * @return array<string, mixed> what Symfony keeps as the transition's metadata
     */
    public function toMetadata(): array
    {
        return [
            'bpmn_node' => $this->node,
            'bpmn_kind' => $this->kind,
            'bpmn_lane' => $this->lane,
            'bpmn_automatic' => $this->automatic,
            'bpmn_conditions' => $this->conditions,
            'bpmn_negated' => $this->negated,
            'bpmn_throws' => $this->throws,
            'bpmn_timer' => $this->timer,
            'bpmn_message' => $this->message,
            'bpmn_flows' => $this->flows,
            'label' => $this->label,
        ];
    }
}
