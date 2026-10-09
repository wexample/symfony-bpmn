<?php

namespace Wexample\SymfonyBpmn\Entity;

use Symfony\Component\Uid\Uuid;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * A lane of a process — who does what it holds —, where its band is drawn, and
 * the role or grant that acts in it.
 *
 * Read from the `.bpmn` files of the registry, never stored: no `#[ORM\Entity]`.
 * Its id derives from what identifies it, so a list read twice holds the same
 * entities.
 */
#[PseudocodeExport(inherited: true)]
class BpmnLane extends AbstractEntity
{
    public const string ID_NAMESPACE = 'bca39ad9-8b41-5e73-9607-6c92c4e0ffb2';

    protected string $processId;

    protected string $laneId;

    protected ?string $name;

    protected ?string $parentLaneId;

    protected int $depth;

    protected ?string $role;

    protected ?string $grant;

    protected float $x;

    protected float $y;

    protected float $width;

    protected float $height;

    public function __construct(
        string $processId,
        string $laneId,
        ?string $name,
        ?string $parentLaneId,
        int $depth,
        ?string $role,
        ?string $grant,
        float $x,
        float $y,
        float $width,
        float $height,
    ) {
        parent::__construct();

        $this->processId = $processId;
        $this->laneId = $laneId;
        $this->name = $name;
        $this->parentLaneId = $parentLaneId;
        $this->depth = $depth;
        $this->role = $role;
        $this->grant = $grant;
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;

        $this->setId(Uuid::v5(Uuid::fromString(self::ID_NAMESPACE), implode("\0", [(string) $processId, (string) $laneId])));
    }

    public function getProcessId(): string
    {
        return $this->processId;
    }

    public function getLaneId(): string
    {
        return $this->laneId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getParentLaneId(): ?string
    {
        return $this->parentLaneId;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function getGrant(): ?string
    {
        return $this->grant;
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
