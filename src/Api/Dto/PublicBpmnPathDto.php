<?php

namespace Wexample\SymfonyBpmn\Api\Dto;

use Wexample\SymfonyApi\Api\Dto\AbstractEntityDto;
use Wexample\SymfonyBpmn\Entity\BpmnPath;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * BpmnPath as it goes on the wire: everything it holds is meant to be shown.
 */
class PublicBpmnPathDto extends AbstractEntityDto
{
    public string $processId;

    public int $position;

    public bool $complete;

    public array $nodeIds;

    public array $steps;

    public array $choices;

    public string $description;

    /**
     * @param BpmnPath $entity
     */
    public static function fromEntity(AbstractEntity $entity): self
    {
        $dto = parent::fromEntity($entity);

        $dto->processId = $entity->getProcessId();
        $dto->position = $entity->getPosition();
        $dto->complete = $entity->getComplete();
        $dto->nodeIds = $entity->getNodeIds();
        $dto->steps = $entity->getSteps();
        $dto->choices = $entity->getChoices();
        $dto->description = $entity->getDescription();

        return $dto;
    }
}
