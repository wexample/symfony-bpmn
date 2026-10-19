<?php

namespace Wexample\SymfonyBpmn\Service;

use Wexample\PhpBpmn\Class\Diagram\Diagram;
use Wexample\PhpBpmn\Class\Diagram\DiagramLayout;
use Wexample\PhpBpmn\Class\Element\AbstractFlowNode;
use Wexample\PhpBpmn\Class\Element\Event;
use Wexample\PhpBpmn\Class\Element\Gateway;
use Wexample\PhpBpmn\Class\Element\Lane;
use Wexample\PhpBpmn\Class\Element\Process;
use Wexample\PhpBpmn\Class\Element\Task;
use Wexample\PhpBpmn\Class\Path\Path;
use Wexample\PhpBpmn\Class\Path\PathStep;
use Wexample\PhpBpmn\Enum\IssueSeverity;
use Wexample\SymfonyBpmn\Entity\BpmnFlow;
use Wexample\SymfonyBpmn\Entity\BpmnIssue;
use Wexample\SymfonyBpmn\Entity\BpmnLane;
use Wexample\SymfonyBpmn\Entity\BpmnNode;
use Wexample\SymfonyBpmn\Entity\BpmnPath;
use Wexample\SymfonyBpmn\Entity\BpmnProcess;
use Wexample\SymfonyBpmn\Entity\BpmnTaskRight;
use Wexample\SymfonyHelpers\Attribute\WorkerSafe;

/**
 * The processes of the registry as entities a screen reads: each list an
 * entity list, normalized and sent like any other (symfony-api), and drawn
 * where the file says — or, for a file without a diagram, where the layout of
 * php-bpmn puts it.
 */
class BpmnViewService
{
    /** @var array<string, Diagram> */
    #[WorkerSafe('Layouts drawn from the bundled processes, the same for every request.')]
    private array $diagrams = [];

    public function __construct(
        private readonly BpmnRegistry $registry,
        private readonly BpmnMatrixService $matrix,
        private readonly BpmnScenarioService $scenarios,
        private readonly LaneAccessService $laneAccess,
    ) {
    }

    public function has(string $processId): bool
    {
        return $this->registry->has($processId);
    }

    /**
     * @return list<BpmnProcess>
     */
    public function processes(): array
    {
        return array_map(
            fn (string $id) => $this->process($id),
            array_keys($this->registry->all()),
        );
    }

    /**
     * One process of the registry, as its sheet heads it.
     */
    public function process(string $processId): BpmnProcess
    {
        $process = $this->registry->get($processId);
        $report = $this->registry->validate($processId);

        return new BpmnProcess(
            processId: $processId,
            name: $process->getName(),
            documentation: $process->getDocumentation(),
            executable: $process->isExecutable(),
            laneCount: count($process->getAllLanes()),
            nodeCount: count($process->getFlow()->getNodes()),
            flowCount: count($process->getFlow()->getFlows()),
            valid: $report->isValid(),
            errorCount: count($report->getErrors()),
            warningCount: count($report->getWarnings()),
        );
    }

    /**
     * Top first, a parent before its children.
     *
     * @return list<BpmnLane>
     */
    public function lanes(string $processId): array
    {
        $process = $this->registry->get($processId);
        $diagram = $this->diagram($processId);
        $lanes = [];
        $walk = function (array $children, ?Lane $parent, int $depth) use (&$walk, &$lanes, $processId, $diagram): void {
            foreach ($children as $lane) {
                $bounds = $diagram->getShape($lane->getId());
                $rule = $this->laneAccess->getRule($processId, $lane->getId());
                $lanes[] = new BpmnLane(
                    processId: $processId,
                    laneId: $lane->getId(),
                    name: $lane->getName(),
                    parentLaneId: $parent?->getId(),
                    depth: $depth,
                    role: $rule['role'],
                    grant: $rule['grant'],
                    x: $bounds?->x ?? 0.0,
                    y: $bounds?->y ?? 0.0,
                    width: $bounds?->width ?? 0.0,
                    height: $bounds?->height ?? 0.0,
                );
                $walk($lane->getChildren(), $lane, $depth + 1);
            }
        };
        $walk($process->getLanes(), null, 0);

        return $lanes;
    }

    /**
     * @return list<BpmnNode>
     */
    public function nodes(string $processId): array
    {
        $process = $this->registry->get($processId);
        $diagram = $this->diagram($processId);
        $nodes = [];

        foreach ($process->getFlow()->getNodes() as $id => $node) {
            $bounds = $diagram->getShape($id);
            [$kind, $variant] = $this->describeNode($node);
            $nodes[] = new BpmnNode(
                processId: $processId,
                nodeId: $id,
                name: $node->getName(),
                documentation: $node->getDocumentation(),
                element: $node->getElementName(),
                kind: $kind,
                variant: $variant,
                definitions: $node instanceof Event
                    ? array_map(static fn ($definition) => $definition->getType()->value, $node->getDefinitions())
                    : [],
                attachedTo: $node instanceof Event ? $node->getAttachedTo() : null,
                laneId: $process->getLaneOf($id)?->getId(),
                x: $bounds?->x ?? 0.0,
                y: $bounds?->y ?? 0.0,
                width: $bounds?->width ?? 0.0,
                height: $bounds?->height ?? 0.0,
            );
        }

        return $nodes;
    }

    /**
     * @return list<BpmnFlow>
     */
    public function flows(string $processId): array
    {
        $process = $this->registry->get($processId);
        $diagram = $this->diagram($processId);
        $defaults = [];

        foreach ($process->getFlow()->getNodes() as $node) {
            if ($node instanceof Gateway && $node->getDefaultFlow() !== null) {
                $defaults[$node->getDefaultFlow()] = true;
            }
        }

        $flows = [];

        foreach ($process->getFlow()->getFlows() as $id => $flow) {
            $flows[] = new BpmnFlow(
                processId: $processId,
                flowId: $id,
                sourceId: $flow->getSource(),
                targetId: $flow->getTarget(),
                name: $flow->getName(),
                condition: $flow->getCondition(),
                isDefault: isset($defaults[$id]),
                points: array_map(static fn (array $point) => [(float) $point[0], (float) $point[1]], $diagram->getEdge($id) ?? []),
            );
        }

        return $flows;
    }

    /**
     * The ways through the process, start to end: few, taking every flow
     * together, each loop gone round once.
     *
     * @return list<BpmnPath>
     */
    public function paths(string $processId): array
    {
        $paths = $this->scenarios->getCoveringPaths($processId);

        return array_map(
            static fn (Path $path, int $position) => new BpmnPath(
                processId: $processId,
                position: $position,
                complete: $path->isComplete(),
                nodeIds: $path->getNodeIds(),
                steps: array_map(static fn (PathStep $step) => [
                    'nodeId' => $step->nodeId,
                    'element' => $step->element,
                    'name' => $step->name,
                    'laneId' => $step->laneId,
                    'flowId' => $step->flowId,
                    'condition' => $step->condition,
                ], $path->getSteps()),
                choices: array_map(static fn (array $choice) => [
                    'nodeId' => $choice[0],
                    'flowId' => $choice[1],
                    'label' => $choice[2],
                ], $path->getChoices()),
                description: $path->describe(),
            ),
            $paths,
            array_keys($paths),
        );
    }

    /**
     * @return list<BpmnIssue>
     */
    public function issues(string $processId): array
    {
        $issues = [];

        foreach ($this->registry->validate($processId)->getIssues() as $position => $issue) {
            $issues[] = new BpmnIssue(
                processId: $processId,
                position: $position,
                severity: $issue->severity === IssueSeverity::ERROR ? 'error' : 'warning',
                code: $issue->code,
                elementId: $issue->elementId,
                message: $issue->message,
            );
        }

        return $issues;
    }

    /**
     * @return list<BpmnTaskRight>
     */
    public function rights(string $processId): array
    {
        return array_map(
            static fn (array $row) => new BpmnTaskRight(
                processId: $processId,
                taskId: $row['task'],
                name: $row['name'],
                element: $row['type'],
                laneId: $row['lane'],
                laneName: $row['lane_name'],
                role: $row['role'],
                grant: $row['grant'],
                automatic: $row['automatic'],
                interruptedBy: $row['interrupted_by'],
            ),
            $this->matrix->build($processId),
        );
    }

    /**
     * What the file draws of the process, or the layout of php-bpmn when it
     * draws none of it.
     */
    public function diagram(string $processId): Diagram
    {
        if (isset($this->diagrams[$processId])) {
            return $this->diagrams[$processId];
        }

        $process = $this->registry->get($processId);
        $nodeIds = array_keys($process->getFlow()->getNodes());

        foreach ($this->registry->getDefinitions($processId)->getDiagrams() as $diagram) {
            foreach ($nodeIds as $id) {
                if ($diagram->getShape($id) !== null) {
                    return $this->diagrams[$processId] = $diagram;
                }
            }
        }

        return $this->diagrams[$processId] = (new DiagramLayout())->layout($process);
    }

    /**
     * What family a node is of, and which of it.
     *
     * @return array{string, string|null}
     */
    private function describeNode(AbstractFlowNode $node): array
    {
        return match (true) {
            $node instanceof Event => ['event', $node->getPosition()->value],
            $node instanceof Task => ['task', $node->getType()->value],
            $node instanceof Gateway => ['gateway', $node->getType()->value],
            default => ['activity', $node->getElementName()],
        };
    }
}
