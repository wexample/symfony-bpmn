<?php

namespace Wexample\SymfonyBpmn\Api\Dto;

use Wexample\SymfonyApi\Api\Dto\AbstractEntityDto;
use Wexample\SymfonyBpmn\Entity\BpmnTaskRight;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * BpmnTaskRight as it goes on the wire: everything it holds is meant to be shown.
 */
class PublicBpmnTaskRightDto extends AbstractEntityDto
{
    public string $processId;

    public string $taskId;

    public ?string $name;

    public string $element;

    public ?string $laneId;

    public ?string $laneName;

    public ?string $role;

    public ?string $grant;

    public bool $automatic;

    public array $interruptedBy;

    /**
     * @param BpmnTaskRight $entity
     */
    public static function fromEntity(AbstractEntity $entity): self
    {
        $dto = parent::fromEntity($entity);

        $dto->processId = $entity->getProcessId();
        $dto->taskId = $entity->getTaskId();
        $dto->name = $entity->getName();
        $dto->element = $entity->getElement();
        $dto->laneId = $entity->getLaneId();
        $dto->laneName = $entity->getLaneName();
        $dto->role = $entity->getRole();
        $dto->grant = $entity->getGrant();
        $dto->automatic = $entity->getAutomatic();
        $dto->interruptedBy = $entity->getInterruptedBy();

        return $dto;
    }
}
