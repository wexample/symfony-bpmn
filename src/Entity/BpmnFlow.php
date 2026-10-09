<?php

namespace Wexample\SymfonyBpmn\Entity;

use Symfony\Component\Uid\Uuid;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * A sequence flow of a process: from where to where, under what condition, and
 * the points its line is drawn through.
 *
 * Read from the `.bpmn` files of the registry, never stored: no `#[ORM\Entity]`.
 * Its id derives from what identifies it, so a list read twice holds the same
 * entities.
 */
#[PseudocodeExport(inherited: true)]
class BpmnFlow extends AbstractEntity
{
    public const string ID_NAMESPACE = '6093fb20-af33-55c6-a344-d58fc7a1f609';

    protected string $processId;

    protected string $flowId;

    protected string $sourceId;

    protected string $targetId;

    protected ?string $name;

    protected ?string $condition;

    protected bool $isDefault;

    protected array $points;

    public function __construct(
        string $processId,
        string $flowId,
        string $sourceId,
        string $targetId,
        ?string $name,
        ?string $condition,
        bool $isDefault,
        array $points,
    ) {
        parent::__construct();

        $this->processId = $processId;
        $this->flowId = $flowId;
        $this->sourceId = $sourceId;
        $this->targetId = $targetId;
        $this->name = $name;
        $this->condition = $condition;
        $this->isDefault = $isDefault;
        $this->points = $points;

        $this->setId(Uuid::v5(Uuid::fromString(self::ID_NAMESPACE), implode("\0", [(string) $processId, (string) $flowId])));
    }

    public function getProcessId(): string
    {
        return $this->processId;
    }

    public function getFlowId(): string
    {
        return $this->flowId;
    }

    public function getSourceId(): string
    {
        return $this->sourceId;
    }

    public function getTargetId(): string
    {
        return $this->targetId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function getPoints(): array
    {
        return $this->points;
    }
}
