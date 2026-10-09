<?php

namespace Wexample\SymfonyBpmn\Api\Dto;

use Wexample\SymfonyApi\Api\Dto\AbstractEntityDto;
use Wexample\SymfonyBpmn\Entity\BpmnNode;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * BpmnNode as it goes on the wire: everything it holds is meant to be shown.
 */
class PublicBpmnNodeDto extends AbstractEntityDto
{
    public string $processId;

    public string $nodeId;

    public ?string $name;

    public ?string $documentation;

    public string $element;

    public string $kind;

    public ?string $variant;

    public array $definitions;

    public ?string $attachedTo;

    public ?string $laneId;

    public float $x;

    public float $y;

    public float $width;

    public float $height;

    /**
     * @param BpmnNode $entity
     */
    public static function fromEntity(AbstractEntity $entity): self
    {
        $dto = parent::fromEntity($entity);

        $dto->processId = $entity->getProcessId();
        $dto->nodeId = $entity->getNodeId();
        $dto->name = $entity->getName();
        $dto->documentation = $entity->getDocumentation();
        $dto->element = $entity->getElement();
        $dto->kind = $entity->getKind();
        $dto->variant = $entity->getVariant();
        $dto->definitions = $entity->getDefinitions();
        $dto->attachedTo = $entity->getAttachedTo();
        $dto->laneId = $entity->getLaneId();
        $dto->x = $entity->getX();
        $dto->y = $entity->getY();
        $dto->width = $entity->getWidth();
        $dto->height = $entity->getHeight();

        return $dto;
    }
}
