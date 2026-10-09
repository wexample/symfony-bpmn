<?php

namespace Wexample\SymfonyBpmn\Service;

use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Metadata\InMemoryMetadataStore;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Wexample\SymfonyBpmn\Class\CompiledProcess;

/**
 * The Symfony workflow of a compiled process — a Petri net, marking stored on
 * the subject through BpmnSubjectInterface —, named `bpmn.<process id>`: its
 * guard, transition and entered events are Symfony's, under that name.
 */
class BpmnWorkflowFactory
{
    /** @var array<string, Workflow> */
    private array $workflows = [];

    public function __construct(
        private readonly BpmnRegistry $registry,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function get(string $processId): Workflow
    {
        return $this->workflows[$processId] ??= $this->build($this->registry->compile($processId));
    }

    public function build(CompiledProcess $compiled): Workflow
    {
        $transitions = [];
        $transitionsMetadata = new \SplObjectStorage();

        foreach ($compiled->transitions as $name => $compiledTransition) {
            $transition = new Transition($name, $compiledTransition->from, $compiledTransition->to);
            $transitions[] = $transition;
            $transitionsMetadata[$transition] = $compiledTransition->toMetadata();
        }

        $definition = new Definition(
            $compiled->places,
            $transitions,
            [$compiled->initialPlace],
            new InMemoryMetadataStore(
                ['label' => $compiled->process->getLabel(), 'bpmn_process' => $compiled->process->getId()],
                [],
                $transitionsMetadata
            )
        );

        return new Workflow(
            $definition,
            new MethodMarkingStore(false, 'bpmnMarking'),
            $this->eventDispatcher,
            $compiled->getWorkflowName()
        );
    }
}
