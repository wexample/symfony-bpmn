<?php

namespace Wexample\SymfonyBpmn\Api\Dto;

use Wexample\SymfonyApi\Api\Dto\AbstractEntityDto;
use Wexample\SymfonyBpmn\Entity\BpmnFlow;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * BpmnFlow as it goes on the wire: everything it holds is meant to be shown.
 */
class PublicBpmnFlowDto extends AbstractEntityDto
{
    public string $processId;

    public string $flowId;

    public string $sourceId;

    public string $targetId;

    public ?string $name;

    public ?string $condition;

    public bool $isDefault;

    public array $points;

    /**
     * @param BpmnFlow $entity
     */
    public static function fromEntity(AbstractEntity $entity): self
    {
        $dto = parent::fromEntity($entity);

        $dto->processId = $entity->getProcessId();
        $dto->flowId = $entity->getFlowId();
        $dto->sourceId = $entity->getSourceId();
        $dto->targetId = $entity->getTargetId();
        $dto->name = $entity->getName();
        $dto->condition = $entity->getCondition();
        $dto->isDefault = $entity->isDefault();
        $dto->points = $entity->getPoints();

        return $dto;
    }
}
