<?php

namespace Wexample\SymfonyBpmn\Service;

use Wexample\PhpBpmn\Class\Element\AbstractFlowNode;
use Wexample\PhpBpmn\Class\Element\CallActivity;
use Wexample\PhpBpmn\Class\Element\Definitions;
use Wexample\PhpBpmn\Class\Element\Event;
use Wexample\PhpBpmn\Class\Element\Gateway;
use Wexample\PhpBpmn\Class\Element\Process;
use Wexample\PhpBpmn\Class\Element\SequenceFlow;
use Wexample\PhpBpmn\Class\Element\SubProcess;
use Wexample\PhpBpmn\Class\Element\Task;
use Wexample\PhpBpmn\Enum\EventDefinitionType;
use Wexample\PhpBpmn\Enum\EventPosition;
use Wexample\PhpBpmn\Enum\GatewayType;
use Wexample\PhpBpmn\Enum\TaskType;
use Wexample\SymfonyBpmn\Class\CompiledProcess;
use Wexample\SymfonyBpmn\Class\CompiledTransition;
use Wexample\SymfonyBpmn\Class\Route;
use Wexample\SymfonyBpmn\Exception\BpmnRuntimeException;
use Wexample\SymfonyHelpers\Attribute\WorkerSafe;

/**
 * A BPMN process as a Petri net, which Symfony Workflow runs:
 *
 * - a place where the process waits: each activity (a sub-process or a call
 *   activity as one step), each intermediate event waited for, each
 *   event-based gateway, each end, the start event — and, at a parallel join,
 *   one place per way in, the join firing once all are marked;
 * - a transition per way out of a place: completing an activity leads,
 *   through the gateways, to the places waited at next. An exclusive choice
 *   gives one transition per way, each under its conditions — the default
 *   under none of the others holding —; a parallel split marks every way at
 *   once; an intermediate throw event passed on the way is raised as the
 *   transition is taken;
 * - an interrupting boundary event, a transition of its activity, fired by
 *   its timer or its message; after an event-based gateway, one transition
 *   per event.
 *
 * Automatic: what the application does — service, send, script and business
 * rule tasks — and joins. Timed: what a timer fires. Human: the rest, done by
 * someone of the node's lane.
 */
class ProcessCompiler
{
    private const int MAX_DEPTH = 64;

    #[WorkerSafe('Set at the start of each compile() and read within it only.')]
    private Process $process;

    public function compile(Definitions $definitions, Process $process): CompiledProcess
    {
        $this->process = $process;
        $flow = $process->getFlow();
        $starts = $flow->getStartEvents();

        if (! $starts) {
            throw new BpmnRuntimeException(sprintf('Process "%s" has no start event.', $process->getId()));
        }

        $places = [];
        $transitions = [];

        foreach ($flow->getNodes() as $node) {
            if ($this->isPlace($node)) {
                $places[] = $node->getId();
            }

            if ($node instanceof Gateway && $node->getType() === GatewayType::PARALLEL && count($flow->getIncoming($node->getId())) > 1) {
                foreach ($flow->getIncoming($node->getId()) as $incoming) {
                    $places[] = $this->joinPlace($node, $incoming);
                }

                $this->addTransitions($transitions, $node, CompiledTransition::KIND_JOIN, array_map(
                    fn (SequenceFlow $incoming) => $this->joinPlace($node, $incoming),
                    $flow->getIncoming($node->getId())
                ), $this->routesOut($node), automatic: true);
            }
        }

        foreach ($flow->getNodes() as $node) {
            $id = $node->getId();

            if ($node instanceof Event && $node->getPosition() === EventPosition::START) {
                $this->addTransitions($transitions, $node, CompiledTransition::KIND_START, [$id], $this->routesOut($node));
            } elseif ($node instanceof Task || $node instanceof CallActivity || $node instanceof SubProcess) {
                $this->addTransitions(
                    $transitions,
                    $node,
                    CompiledTransition::KIND_TASK,
                    [$id],
                    $this->routesOut($node),
                    automatic: $node instanceof Task && $node->getType()->isAutomatic(),
                    message: $node instanceof Task && $node->getType() === TaskType::RECEIVE ? ($node->getMessageRef() ?? $id) : null,
                );

                foreach ($flow->getBoundaryEvents($id) as $boundary) {
                    if ($boundary->isCancelActivity()) {
                        $this->addTransitions($transitions, $boundary, CompiledTransition::KIND_BOUNDARY, [$id], $this->routesOut($boundary), triggerOf: $boundary);
                    }
                }
            } elseif ($node instanceof Event && $node->getPosition() === EventPosition::INTERMEDIATE_CATCH && ! $this->followsEventGateway($node)) {
                $this->addTransitions($transitions, $node, CompiledTransition::KIND_EVENT, [$id], $this->routesOut($node), triggerOf: $node);
            } elseif ($node instanceof Gateway && $node->getType() === GatewayType::EVENT_BASED) {
                foreach ($flow->getOutgoing($id) as $outgoing) {
                    $event = $flow->getNode($outgoing->getTarget());

                    if ($event instanceof Event) {
                        $this->addTransitions($transitions, $event, CompiledTransition::KIND_EVENT, [$id], $this->routesOut($event), triggerOf: $event);
                    } elseif ($event instanceof Task) {
                        $this->addTransitions($transitions, $event, CompiledTransition::KIND_EVENT, [$id], $this->routesOut($event), message: $event->getMessageRef() ?? $event->getId());
                    }
                }
            }
        }

        $start = $starts[0];

        return new CompiledProcess(
            $definitions,
            $process,
            array_values(array_unique($places)),
            $start->getId(),
            array_values(array_map(
                static fn (Event $end) => $end->getId(),
                array_filter($flow->getNodesOf(Event::class), static fn (Event $event) => $event->getPosition() === EventPosition::END)
            )),
            $transitions,
        );
    }

    /**
     * Where the process waits.
     */
    private function isPlace(AbstractFlowNode $node): bool
    {
        if ($node instanceof Task || $node instanceof CallActivity || $node instanceof SubProcess) {
            // A receive task after an event-based gateway is one of its events.
            return ! ($node instanceof Task && $node->getType() === TaskType::RECEIVE && $this->followsEventGateway($node));
        }

        if ($node instanceof Event) {
            return match ($node->getPosition()) {
                EventPosition::START, EventPosition::END => true,
                EventPosition::INTERMEDIATE_CATCH => ! $this->followsEventGateway($node),
                default => false,
            };
        }

        return $node instanceof Gateway && $node->getType() === GatewayType::EVENT_BASED;
    }

    private function followsEventGateway(AbstractFlowNode $node): bool
    {
        foreach ($this->process->getFlow()->getIncoming($node->getId()) as $incoming) {
            $source = $this->process->getFlow()->getNode($incoming->getSource());

            if ($source instanceof Gateway && $source->getType() === GatewayType::EVENT_BASED) {
                return true;
            }
        }

        return false;
    }

    /**
     * The routes out of a node: every outgoing flow at once — BPMN's
     * uncontrolled flow —, each followed through the gateways.
     *
     * @return list<Route>
     */
    private function routesOut(AbstractFlowNode $node): array
    {
        $routes = [new Route([])];

        foreach ($this->process->getFlow()->getOutgoing($node->getId()) as $outgoing) {
            $routes = $this->combine($routes, $this->follow($outgoing, null, [], 0));
        }

        return $routes;
    }

    /**
     * The routes from taking $flow, under its condition.
     *
     * @param list<string> $negated
     *
     * @return list<Route>
     */
    private function follow(SequenceFlow $flow, ?string $condition, array $negated, int $depth): array
    {
        if ($depth > self::MAX_DEPTH) {
            throw new BpmnRuntimeException(sprintf('Process "%s" loops through gateways without waiting anywhere, from "%s".', $this->process->getId(), $flow->getId()));
        }

        $target = $this->process->getFlow()->getNode($flow->getTarget())
            ?? throw new BpmnRuntimeException(sprintf('Flow "%s" leads to nothing.', $flow->getId()));

        $condition ??= $flow->getCondition();

        if ($this->isPlace($target) || ($target instanceof Event && $target->getPosition() === EventPosition::INTERMEDIATE_CATCH)) {
            return [(new Route([$target->getId()]))->prepend($condition, $negated, null, $flow->getId())];
        }

        if ($target instanceof Event && $target->getPosition() === EventPosition::INTERMEDIATE_THROW) {
            return $this->prependAll($this->followAll($target, $depth), $condition, $negated, $target->getId(), $flow->getId());
        }

        if ($target instanceof Gateway) {
            $incoming = $this->process->getFlow()->getIncoming($target->getId());
            $outgoing = $this->process->getFlow()->getOutgoing($target->getId());

            if ($target->getType() === GatewayType::PARALLEL) {
                if (count($incoming) > 1) {
                    return [(new Route([$this->joinPlace($target, $flow)]))->prepend($condition, $negated, null, $flow->getId())];
                }

                return $this->prependAll($this->followAll($target, $depth), $condition, $negated, null, $flow->getId());
            }

            // Exclusive, inclusive (run as exclusive), complex: one way.
            $routes = [];
            $conditions = array_values(array_filter(array_map(
                static fn (SequenceFlow $out) => $out->getId() === $target->getDefaultFlow() ? null : $out->getCondition(),
                $outgoing
            )));

            foreach ($outgoing as $out) {
                $default = $out->getId() === $target->getDefaultFlow();

                foreach ($this->follow($out, $default ? null : $out->getCondition(), $default ? $conditions : [], $depth + 1) as $route) {
                    $routes[] = $route->prepend($condition, $negated, null, $flow->getId());
                }
            }

            return $routes;
        }

        throw new BpmnRuntimeException(sprintf('"%s" (%s) cannot be run here.', $target->getId(), $target->getElementName()));
    }

    /**
     * Every way out of a pass-through node at once.
     *
     * @return list<Route>
     */
    private function followAll(AbstractFlowNode $node, int $depth): array
    {
        $routes = [new Route([])];

        foreach ($this->process->getFlow()->getOutgoing($node->getId()) as $outgoing) {
            $routes = $this->combine($routes, $this->follow($outgoing, null, [], $depth + 1));
        }

        return $routes;
    }

    /**
     * Every pair of routes, merged: two ways taken at once.
     *
     * @param list<Route> $left
     * @param list<Route> $right
     *
     * @return list<Route>
     */
    private function combine(array $left, array $right): array
    {
        $combined = [];

        foreach ($left as $a) {
            foreach ($right as $b) {
                $combined[] = $a->merge($b);
            }
        }

        return $combined;
    }

    /**
     * @param list<Route>  $routes
     * @param list<string> $negated
     *
     * @return list<Route>
     */
    private function prependAll(array $routes, ?string $condition, array $negated, ?string $throw, string $flow): array
    {
        return array_map(static fn (Route $route) => $route->prepend($condition, $negated, $throw, $flow), $routes);
    }

    /**
     * @param array<string, CompiledTransition> $transitions
     * @param list<string>                      $from
     * @param list<Route>                       $routes
     */
    private function addTransitions(
        array &$transitions,
        AbstractFlowNode $node,
        string $kind,
        array $from,
        array $routes,
        bool $automatic = false,
        ?Event $triggerOf = null,
        ?string $message = null,
    ): void {
        $timer = $triggerOf?->getDefinition(EventDefinitionType::TIMER);
        $messageDefinition = $triggerOf?->getDefinition(EventDefinitionType::MESSAGE) ?? $triggerOf?->getDefinition(EventDefinitionType::SIGNAL);

        foreach (array_values($routes) as $index => $route) {
            $name = count($routes) > 1 ? $node->getId() . '__' . implode('_', $route->to) : $node->getId();

            if (isset($transitions[$name])) {
                $name .= '__' . $index;
            }

            $transitions[$name] = new CompiledTransition(
                name: $name,
                node: $node->getId(),
                kind: $kind,
                from: $from,
                to: $route->to,
                lane: $this->process->getLaneOf($node->getId())?->getId(),
                automatic: $automatic,
                conditions: $route->conditions,
                negated: $route->negated,
                throws: $route->throws,
                timerKind: $timer?->getTimerKind(),
                timer: $timer?->getExpression(),
                message: $message ?? ($messageDefinition ? ($messageDefinition->getRef() ?? $node->getId()) : null),
                label: $node->getName() ?? $node->getId(),
                flows: $route->flows,
            );
        }
    }

    private function joinPlace(Gateway $join, SequenceFlow $incoming): string
    {
        return $join->getId() . '__' . $incoming->getId();
    }
}
