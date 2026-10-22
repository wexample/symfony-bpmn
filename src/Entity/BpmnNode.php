<?php

namespace Wexample\SymfonyBpmn\Entity;

use Symfony\Component\Uid\Uuid;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * A node of a process — an event, a task, a gateway, an activity — and where
 * it is drawn.
 *
 * Read from the `.bpmn` files of the registry, never stored: no `#[ORM\Entity]`.
 * Its id derives from what identifies it, so a list read twice holds the same
 * entities.
 */
#[PseudocodeExport(inherited: true)]
class BpmnNode extends AbstractEntity
{
    public const string ID_NAMESPACE = 'a2e3153b-6744-5794-8eb5-e4e3c7dced21';

    protected string $processId;

    protected string $nodeId;

    protected ?string $name;

    protected ?string $documentation;

    protected string $element;

    protected string $kind;

    protected ?string $variant;

    protected array $definitions;

    protected ?string $attachedTo;

    protected ?string $laneId;

    protected float $x;

    protected float $y;

    protected float $width;

    protected float $height;

    public function __construct(
        string $processId,
        string $nodeId,
        ?string $name,
        ?string $documentation,
        string $element,
        string $kind,
        ?string $variant,
        array $definitions,
        ?string $attachedTo,
        ?string $laneId,
        float $x,
        float $y,
        float $width,
        float $height,
    ) {
        parent::__construct();

        $this->processId = $processId;
        $this->nodeId = $nodeId;
        $this->name = $name;
        $this->documentation = $documentation;
        $this->element = $element;
        $this->kind = $kind;
        $this->variant = $variant;
        $this->definitions = $definitions;
        $this->attachedTo = $attachedTo;
        $this->laneId = $laneId;
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;

        $this->setId(Uuid::v5(Uuid::fromString(self::ID_NAMESPACE), implode("\0", [(string) $processId, (string) $nodeId])));
    }

    public function getProcessId(): string
    {
        return $this->processId;
    }

    public function getNodeId(): string
    {
        return $this->nodeId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDocumentation(): ?string
    {
        return $this->documentation;
    }

    public function getElement(): string
    {
        return $this->element;
    }

    public function getKind(): string
    {
        return $this->kind;
    }

    public function getVariant(): ?string
    {
        return $this->variant;
    }

    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    public function getAttachedTo(): ?string
    {
        return $this->attachedTo;
    }

    public function getLaneId(): ?string
    {
        return $this->laneId;
    }

    public function getX(): float
    {
        return $this->x;
    }

    public function getY(): float
    {
        return $this->y;
    }

    public function getWidth(): float
    {
        return $this->width;
    }

    public function getHeight(): float
    {
        return $this->height;
    }
}
