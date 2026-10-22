<?php

namespace Wexample\SymfonyBpmn\Api\Dto;

use Wexample\SymfonyApi\Api\Dto\AbstractEntityDto;
use Wexample\SymfonyBpmn\Entity\BpmnLane;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * BpmnLane as it goes on the wire: everything it holds is meant to be shown.
 */
class PublicBpmnLaneDto extends AbstractEntityDto
{
    public string $processId;

    public string $laneId;

    public ?string $name;

    public ?string $parentLaneId;

    public int $depth;

    public ?string $role;

    public ?string $grant;

    public float $x;

    public float $y;

    public float $width;

    public float $height;

    /**
     * @param BpmnLane $entity
     */
    public static function fromEntity(AbstractEntity $entity): self
    {
        $dto = parent::fromEntity($entity);

        $dto->processId = $entity->getProcessId();
        $dto->laneId = $entity->getLaneId();
        $dto->name = $entity->getName();
        $dto->parentLaneId = $entity->getParentLaneId();
        $dto->depth = $entity->getDepth();
        $dto->role = $entity->getRole();
        $dto->grant = $entity->getGrant();
        $dto->x = $entity->getX();
        $dto->y = $entity->getY();
        $dto->width = $entity->getWidth();
        $dto->height = $entity->getHeight();

        return $dto;
    }
}
