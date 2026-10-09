<?php

namespace Wexample\SymfonyBpmn\Service;

use Wexample\PhpBpmn\Class\Element\CallActivity;
use Wexample\PhpBpmn\Class\Element\SubProcess;
use Wexample\PhpBpmn\Class\Element\Task;

/**
 * Who does what in a process: each task, its lane, the role or grant the
 * lane stands for, whether the application does it, what can interrupt it —
 * the rights of the process, read off its drawing.
 */
class BpmnMatrixService
{
    public function __construct(
        private readonly BpmnRegistry $registry,
        private readonly LaneAccessService $laneAccess,
    ) {
    }

    /**
     * @return list<array{task: string, name: ?string, type: string, lane: ?string, lane_name: ?string, role: ?string, grant: ?string, automatic: bool, interrupted_by: list<string>}>
     */
    public function build(string $processId): array
    {
        $process = $this->registry->get($processId);
        $flow = $process->getFlow();
        $rows = [];

        foreach ($flow->getNodes() as $node) {
            if (! $node instanceof Task && ! $node instanceof CallActivity && ! $node instanceof SubProcess) {
                continue;
            }

            $lane = $process->getLaneOf($node->getId());
            $rule = $lane ? $this->laneAccess->getRule($processId, $lane->getId()) : ['role' => null, 'grant' => null];

            $rows[] = [
                'task' => $node->getId(),
                'name' => $node->getName(),
                'type' => $node->getElementName(),
                'lane' => $lane?->getId(),
                'lane_name' => $lane?->getName(),
                'role' => $rule['role'],
                'grant' => $rule['grant'],
                'automatic' => $node instanceof Task && $node->getType()->isAutomatic(),
                'interrupted_by' => array_map(static fn ($event) => $event->getId(), $flow->getBoundaryEvents($node->getId())),
            ];
        }

        return $rows;
    }
}
