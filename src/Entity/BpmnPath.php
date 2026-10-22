<?php

namespace Wexample\SymfonyBpmn\Entity;

use Symfony\Component\Uid\Uuid;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * One way through a process, start to end: its steps, who does each, and the
 * choices taken at each fork — a scenario.
 *
 * Read from the `.bpmn` files of the registry, never stored: no `#[ORM\Entity]`.
 * Its id derives from what identifies it, so a list read twice holds the same
 * entities.
 */
#[PseudocodeExport(inherited: true)]
class BpmnPath extends AbstractEntity
{
    public const string ID_NAMESPACE = '31f4f4e9-e345-5501-ad67-d2081cc9477d';

    protected string $processId;

    protected int $position;

    protected bool $complete;

    protected array $nodeIds;

    protected array $steps;

    protected array $choices;

    protected string $description;

    public function __construct(
        string $processId,
        int $position,
        bool $complete,
        array $nodeIds,
        array $steps,
        array $choices,
        string $description,
    ) {
        parent::__construct();

        $this->processId = $processId;
        $this->position = $position;
        $this->complete = $complete;
        $this->nodeIds = $nodeIds;
        $this->steps = $steps;
        $this->choices = $choices;
        $this->description = $description;

        $this->setId(Uuid::v5(Uuid::fromString(self::ID_NAMESPACE), implode("\0", [(string) $processId, (string) $position])));
    }

    public function getProcessId(): string
    {
        return $this->processId;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getComplete(): bool
    {
        return $this->complete;
    }

    public function getNodeIds(): array
    {
        return $this->nodeIds;
    }

    public function getSteps(): array
    {
        return $this->steps;
    }

    public function getChoices(): array
    {
        return $this->choices;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
