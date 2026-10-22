<?php

namespace Wexample\SymfonyBpmn\Entity;

use Symfony\Component\Uid\Uuid;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * Who may do a task of a process: its lane, and the role or the grant on the
 * subject's group that acts in it — a row of the rights matrix.
 *
 * Read from the `.bpmn` files of the registry, never stored: no `#[ORM\Entity]`.
 * Its id derives from what identifies it, so a list read twice holds the same
 * entities.
 */
#[PseudocodeExport(inherited: true)]
class BpmnTaskRight extends AbstractEntity
{
    public const string ID_NAMESPACE = '0a421629-f040-5ce2-992e-210f33c93185';

    protected string $processId;

    protected string $taskId;

    protected ?string $name;

    protected string $element;

    protected ?string $laneId;

    protected ?string $laneName;

    protected ?string $role;

    protected ?string $grant;

    protected bool $automatic;

    protected array $interruptedBy;

    public function __construct(
        string $processId,
        string $taskId,
        ?string $name,
        string $element,
        ?string $laneId,
        ?string $laneName,
        ?string $role,
        ?string $grant,
        bool $automatic,
        array $interruptedBy,
    ) {
        parent::__construct();

        $this->processId = $processId;
        $this->taskId = $taskId;
        $this->name = $name;
        $this->element = $element;
        $this->laneId = $laneId;
        $this->laneName = $laneName;
        $this->role = $role;
        $this->grant = $grant;
        $this->automatic = $automatic;
        $this->interruptedBy = $interruptedBy;

        $this->setId(Uuid::v5(Uuid::fromString(self::ID_NAMESPACE), implode("\0", [(string) $processId, (string) $taskId])));
    }

    public function getProcessId(): string
    {
        return $this->processId;
    }

    public function getTaskId(): string
    {
        return $this->taskId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getElement(): string
    {
        return $this->element;
    }

    public function getLaneId(): ?string
    {
        return $this->laneId;
    }

    public function getLaneName(): ?string
    {
        return $this->laneName;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function getGrant(): ?string
    {
        return $this->grant;
    }

    public function getAutomatic(): bool
    {
        return $this->automatic;
    }

    public function getInterruptedBy(): array
    {
        return $this->interruptedBy;
    }
}
