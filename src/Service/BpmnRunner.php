<?php

namespace Wexample\SymfonyBpmn\Service;

use DateTimeImmutable;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Workflow\Workflow;
use Wexample\PhpBpmn\Class\Element\Event;
use Wexample\PhpBpmn\Class\Element\Task;
use Wexample\PhpBpmn\Enum\EventDefinitionType;
use Wexample\PhpBpmn\Enum\TaskType;
use Wexample\PhpBpmn\Helper\Iso8601Helper;
use Wexample\SymfonyBpmn\Class\CompiledProcess;
use Wexample\SymfonyBpmn\Class\CompiledTransition;
use Wexample\SymfonyBpmn\Event\BpmnMessageEvent;
use Wexample\SymfonyBpmn\Event\BpmnTransitionEvent;
use Wexample\SymfonyBpmn\Exception\BpmnRuntimeException;
use Wexample\SymfonyBpmn\Interface\BpmnSubjectInterface;
use Wexample\SymfonyBpmn\Interface\BpmnTaskHandlerInterface;
use Wexample\SymfonyBpmn\Interface\BpmnTimerSchedulerInterface;

/**
 * Runs a process on a subject:
 *
 *     $runner->start($lease, 'tenant_move_in');
 *     $runner->complete($lease, 'register_guarantee');           // by someone of its lane
 *     $runner->complete($request, 'get_quote', ['quote' => 350]); // variables the next choice reads
 *     $runner->receive($lease, 'lease_signed');                  // a message from outside
 *
 * After each step it does what the application does — the automatic tasks,
 * through their handler; the joins — until the process waits for someone, a
 * message or a timer; it schedules the timers of where it now waits, and
 * tells each message sent. The subject is changed, never flushed: the caller
 * does it.
 */
class BpmnRunner
{
    private const int MAX_AUTOMATIC_STEPS = 200;

    /**
     * @param iterable<BpmnTaskHandlerInterface> $handlers
     */
    public function __construct(
        private readonly BpmnRegistry $registry,
        private readonly BpmnWorkflowFactory $workflows,
        private readonly BpmnActorContext $actor,
        private readonly BpmnTimerSchedulerInterface $timers,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LaneAccessService $laneAccess,
        #[AutowireIterator(BpmnTaskHandlerInterface::TAG)]
        private readonly iterable $handlers = [],
    ) {
    }

    /**
     * @param array<string, mixed> $variables
     */
    public function start(BpmnSubjectInterface $subject, string $processId, array $variables = []): void
    {
        if ($subject->getBpmnProcessId() !== null && $subject->getBpmnMarking()) {
            throw new BpmnRuntimeException(sprintf('The subject already follows process "%s".', $subject->getBpmnProcessId()));
        }

        $compiled = $this->registry->compile($processId);
        $subject->setBpmnProcessId($processId);
        $subject->setBpmnMarking([$compiled->initialPlace => 1]);
        $this->mergeVariables($subject, $variables);

        $this->applyFirst($subject, $compiled, $compiled->getTransitionsOf($compiled->initialPlace), sprintf('Process "%s" cannot start', $processId));
    }

    /**
     * Completes a task — or a start, an event someone says happened — the
     * subject waits for, done by the signed-in user. Where its ways out are
     * a choice, the conditions decide; `$transition` names one where several
     * hold.
     *
     * @param array<string, mixed> $variables
     */
    public function complete(BpmnSubjectInterface $subject, string $nodeId, array $variables = [], ?string $transition = null): CompiledTransition
    {
        $compiled = $this->getCompiled($subject);
        $this->mergeVariables($subject, $variables);

        $candidates = array_values(array_filter(
            $compiled->getTransitionsOf($nodeId),
            static fn (CompiledTransition $candidate) => $candidate->kind !== CompiledTransition::KIND_BOUNDARY
                && ($transition === null || $candidate->name === $transition)
        ));

        if (! $candidates) {
            throw new BpmnRuntimeException(sprintf('"%s" is nothing to complete in process "%s".', $nodeId, $compiled->process->getId()));
        }

        return $this->applyFirst($subject, $compiled, $candidates, sprintf('"%s" cannot be completed now', $nodeId));
    }

    /**
     * A message from outside: every transition of the subject waiting for it
     * — a message event, a receive task, a message boundary event — taken,
     * by the application.
     *
     * @param array<string, mixed> $variables
     *
     * @return bool whether the subject was waiting for it
     */
    public function receive(BpmnSubjectInterface $subject, string $message, array $variables = []): bool
    {
        $compiled = $this->getCompiled($subject);
        $this->mergeVariables($subject, $variables);

        $waiting = array_values(array_filter($compiled->transitions, static fn (CompiledTransition $transition) => $transition->message === $message));

        return $this->actor->asSystem(function () use ($subject, $compiled, $waiting) {
            $workflow = $this->workflows->get($compiled->process->getId());

            foreach ($waiting as $transition) {
                if ($workflow->can($subject, $transition->name)) {
                    $this->apply($subject, $compiled, $workflow, $transition);
                    $this->advance($subject);

                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Fires a timer the subject still waits for: what the scheduled message
     * does, when it comes.
     *
     * @return bool whether it still waited
     */
    public function fireTimer(BpmnSubjectInterface $subject, string $transitionName): bool
    {
        $compiled = $this->getCompiled($subject);
        $transition = $compiled->getTransition($transitionName);

        if ($transition === null || ! $transition->isTimed()) {
            return false;
        }

        return $this->actor->asSystem(function () use ($subject, $compiled, $transition) {
            $workflow = $this->workflows->get($compiled->process->getId());

            if (! $workflow->can($subject, $transition->name)) {
                return false;
            }

            $this->apply($subject, $compiled, $workflow, $transition);
            $this->advance($subject);

            return true;
        });
    }

    /**
     * What the signed-in user may do now: the nodes the subject waits at
     * that someone of its lanes does. Their conditions are not asked: the
     * variables given when completing one decide its way out.
     *
     * @return list<string>
     */
    public function getAvailableNodes(BpmnSubjectInterface $subject): array
    {
        $compiled = $this->getCompiled($subject);
        $nodes = [];

        foreach (array_keys($subject->getBpmnMarking()) as $place) {
            foreach ($compiled->getTransitionsFrom($place) as $transition) {
                if ($transition->isHuman() && $this->laneAccess->mayAct($subject, $compiled->process->getId(), $transition->lane)) {
                    $nodes[$transition->node] = true;
                }
            }
        }

        return array_keys($nodes);
    }

    /**
     * Where the subject waits: the nodes marked, joins' arrivals aside.
     *
     * @return list<string>
     */
    public function getWaitingNodes(BpmnSubjectInterface $subject): array
    {
        $flow = $this->getCompiled($subject)->process->getFlow();

        return array_values(array_filter(array_keys($subject->getBpmnMarking()), static fn (string $place) => $flow->hasNode($place)));
    }

    public function isFinished(BpmnSubjectInterface $subject): bool
    {
        $marking = array_keys($subject->getBpmnMarking());

        return $marking !== [] && ! array_diff($marking, $this->getCompiled($subject)->ends);
    }

    /**
     * Takes every automatic transition the subject can, until it waits.
     */
    public function advance(BpmnSubjectInterface $subject): void
    {
        $compiled = $this->getCompiled($subject);
        $workflow = $this->workflows->get($compiled->process->getId());

        $this->actor->asSystem(function () use ($subject, $compiled, $workflow) {
            // The automatic tasks whose handlers ran, waiting for their way
            // out: a handler runs once, its variables then choose the way.
            $handled = [];

            for ($step = 0; $step < self::MAX_AUTOMATIC_STEPS; ++$step) {
                $next = null;

                foreach (array_keys($subject->getBpmnMarking()) as $place) {
                    foreach ($compiled->getTransitionsFrom($place) as $transition) {
                        if (! $transition->automatic && $transition->kind !== CompiledTransition::KIND_JOIN) {
                            continue;
                        }

                        if (! isset($handled[$place]) && $transition->kind === CompiledTransition::KIND_TASK) {
                            $handled[$place] = true;
                            $this->runHandlers($subject, $compiled, $transition);
                        }

                        if ($workflow->can($subject, $transition->name)) {
                            $next = $transition;
                            break 2;
                        }
                    }
                }

                if ($next === null) {
                    return;
                }

                unset($handled[$next->node]);
                $this->apply($subject, $compiled, $workflow, $next);
            }

            throw new BpmnRuntimeException(sprintf('Process "%s" ran more than %d automatic steps: a loop without anyone in it?', $compiled->process->getId(), self::MAX_AUTOMATIC_STEPS));
        });
    }

    /**
     * @param list<CompiledTransition> $candidates
     */
    private function applyFirst(BpmnSubjectInterface $subject, CompiledProcess $compiled, array $candidates, string $error): CompiledTransition
    {
        $workflow = $this->workflows->get($compiled->process->getId());
        $transition = $this->firstEnabled($subject, $workflow, $candidates);

        if ($transition === null) {
            $reasons = [];

            foreach ($candidates as $candidate) {
                foreach ($workflow->buildTransitionBlockerList($subject, $candidate->name) as $blocker) {
                    $reasons[] = $candidate->name . ': ' . $blocker->getMessage();
                }
            }

            throw new BpmnRuntimeException($error . ($reasons ? ' — ' . implode('; ', array_unique($reasons)) : '.'));
        }

        $this->apply($subject, $compiled, $workflow, $transition);
        $this->advance($subject);

        return $transition;
    }

    /**
     * @param list<CompiledTransition> $candidates
     */
    private function firstEnabled(BpmnSubjectInterface $subject, Workflow $workflow, array $candidates): ?CompiledTransition
    {
        foreach ($candidates as $candidate) {
            if ($workflow->can($subject, $candidate->name)) {
                return $candidate;
            }
        }

        return null;
    }

    private function apply(BpmnSubjectInterface $subject, CompiledProcess $compiled, Workflow $workflow, CompiledTransition $transition): void
    {
        $before = array_keys($subject->getBpmnMarking());
        $workflow->apply($subject, $transition->name);
        $processId = $compiled->process->getId();

        // The messages: a send task done, the throw events passed.
        $node = $compiled->process->getFlow()->getNode($transition->node);

        if ($node instanceof Task && $node->getType() === TaskType::SEND) {
            $this->tell($subject, $compiled, $node->getId(), $node->getMessageRef());
        }

        foreach ($transition->throws as $throwId) {
            $throw = $compiled->process->getFlow()->getNode($throwId);
            $definition = $throw instanceof Event ? ($throw->getDefinition(EventDefinitionType::MESSAGE) ?? $throw->getDefinition(EventDefinitionType::SIGNAL)) : null;
            $this->tell($subject, $compiled, $throwId, $definition?->getRef());
        }

        // The timers of where it waits now.
        $now = new DateTimeImmutable();

        foreach (array_diff(array_keys($subject->getBpmnMarking()), $before) as $place) {
            foreach ($compiled->getTransitionsFrom($place) as $next) {
                if ($next->isTimed() && $next->timerKind !== null
                    && ($fireAt = Iso8601Helper::getFireDate($next->timerKind, (string) $next->timer, $now)) !== null) {
                    $this->timers->schedule($subject, $processId, $next->name, $fireAt);
                }
            }
        }

        $this->eventDispatcher->dispatch(new BpmnTransitionEvent($subject, $processId, $transition, $this->actor->isSystem()));
    }

    private function tell(BpmnSubjectInterface $subject, CompiledProcess $compiled, string $nodeId, ?string $messageRef): void
    {
        $node = $compiled->process->getFlow()->getNode($nodeId);

        $this->eventDispatcher->dispatch(new BpmnMessageEvent(
            $subject,
            $compiled->process->getId(),
            $nodeId,
            $messageRef,
            $node?->getProperty('notification'),
            $compiled->process->getLaneOf($nodeId)?->getId(),
        ));
    }

    private function runHandlers(BpmnSubjectInterface $subject, CompiledProcess $compiled, CompiledTransition $transition): void
    {
        if ($transition->kind !== CompiledTransition::KIND_TASK) {
            return;
        }

        foreach ($this->handlers as $handler) {
            if ($handler->supports($compiled->process->getId(), $transition->node)) {
                $this->mergeVariables($subject, $handler->handle($subject, $compiled->process->getId(), $transition->node));
            }
        }
    }

    /**
     * @param array<string, mixed> $variables
     */
    private function mergeVariables(BpmnSubjectInterface $subject, array $variables): void
    {
        if ($variables) {
            $subject->setBpmnVariables(array_replace($subject->getBpmnVariables(), $variables));
        }
    }

    private function getCompiled(BpmnSubjectInterface $subject): CompiledProcess
    {
        $processId = $subject->getBpmnProcessId() ?? throw new BpmnRuntimeException('The subject follows no process: start one first.');

        return $this->registry->compile($processId);
    }
}
