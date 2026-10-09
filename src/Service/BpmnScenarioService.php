<?php

namespace Wexample\SymfonyBpmn\Service;

use Wexample\PhpBpmn\Class\Element\Event;
use Wexample\PhpBpmn\Class\Path\Path;
use Wexample\PhpBpmn\Class\Path\PathFinder;
use Wexample\SymfonyBpmn\Class\CompiledTransition;
use Wexample\SymfonyBpmn\Exception\BpmnRuntimeException;
use Wexample\SymfonyBpmn\Interface\BpmnSubjectInterface;

/**
 * The stories a process tells — one per path through it — and a way to play
 * one on a subject, each step done by someone of its lane: a multi-actor
 * test, written as a drawing.
 *
 *     foreach ($scenarios->getPaths('maintenance_request') as $path) {
 *         $request = new Request();
 *         $scenarios->play($request, $path, fn (?string $lane) => $this->signInAs($lane), [
 *             'get_quote' => ['quote' => 350],
 *             'landlord_decides' => ['approved' => true],
 *         ]);
 *     }
 */
class BpmnScenarioService
{
    public function __construct(
        private readonly BpmnRegistry $registry,
        private readonly BpmnRunner $runner,
    ) {
    }

    /**
     * A few complete paths taking, together, every flow of the process,
     * each loop gone round once: what to show, and what to play.
     *
     * @return list<Path>
     */
    public function getCoveringPaths(string $processId): array
    {
        return (new PathFinder())->cover($this->registry->get($processId));
    }

    /**
     * Every combination, a loop gone round `$maxVisits` times at most.
     *
     * @return list<Path>
     */
    public function getPaths(string $processId, int $maxVisits = 1, bool $completeOnly = true): array
    {
        $paths = (new PathFinder($maxVisits))->find($this->registry->get($processId));

        return $completeOnly ? array_values(array_filter($paths, static fn (Path $path) => $path->isComplete())) : $paths;
    }

    /**
     * Plays $path on a subject not started yet: each step someone does,
     * done by `$actAs($lane)` signed in, with the variables given for its
     * node; each timer of the path fired, each message received; what the
     * application does, done by the runner on its own.
     *
     * @param callable(?string): void            $actAs
     * @param array<string, array<string, mixed>> $variables by node
     */
    public function play(BpmnSubjectInterface $subject, Path $path, callable $actAs, array $variables = [], ?string $processId = null): void
    {
        $processId ??= $subject->getBpmnProcessId() ?? throw new BpmnRuntimeException('Which process to play?');
        $compiled = $this->registry->compile($processId);
        $taken = [];

        foreach ($path->getSteps() as $step) {
            if ($step->flowId !== null) {
                $taken[$step->flowId] = true;
            }
        }

        $steps = $path->getSteps();

        foreach ($steps as $index => $step) {
            if ($index === 0) {
                $actAs($step->laneId);
                $this->runner->start($subject, $processId, $variables[$step->nodeId] ?? []);

                continue;
            }

            // Interrupted by the boundary event the path takes next: never
            // completed.
            $next = $steps[$index + 1] ?? null;
            $nextNode = $next ? $compiled->process->getFlow()->getNode($next->nodeId) : null;

            if ($nextNode instanceof Event && $nextNode->getAttachedTo() === $step->nodeId) {
                continue;
            }

            $transitions = $compiled->getTransitionsOf($step->nodeId);

            if (! $transitions || ! $this->isWaitedAt($subject, $transitions)) {
                // Passed through, or done by the application.
                continue;
            }

            $transition = $this->choose($transitions, $taken) ?? $transitions[0];

            if ($transition->isTimed()) {
                $this->runner->fireTimer($subject, $transition->name);
            } elseif ($transition->message !== null && ! $transition->isHuman()) {
                $this->runner->receive($subject, $transition->message, $variables[$step->nodeId] ?? []);
            } elseif ($transition->isHuman()) {
                $actAs($step->laneId);
                $this->runner->complete($subject, $step->nodeId, $variables[$step->nodeId] ?? [], $transition->name);
            }
        }
    }

    /**
     * @param list<CompiledTransition> $transitions
     */
    private function isWaitedAt(BpmnSubjectInterface $subject, array $transitions): bool
    {
        foreach ($transitions as $transition) {
            if (array_intersect($transition->from, array_keys($subject->getBpmnMarking()))) {
                return true;
            }
        }

        return false;
    }

    /**
     * The transition whose flows the path takes.
     *
     * @param list<CompiledTransition> $transitions
     * @param array<string, true>      $taken
     */
    private function choose(array $transitions, array $taken): ?CompiledTransition
    {
        foreach ($transitions as $transition) {
            if ($transition->flows && ! array_diff($transition->flows, array_keys($taken))) {
                return $transition;
            }
        }

        return null;
    }
}
