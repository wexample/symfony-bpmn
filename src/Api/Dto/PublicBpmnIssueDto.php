<?php

namespace Wexample\SymfonyBpmn\Api\Dto;

use Wexample\SymfonyApi\Api\Dto\AbstractEntityDto;
use Wexample\SymfonyBpmn\Entity\BpmnIssue;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * BpmnIssue as it goes on the wire: everything it holds is meant to be shown.
 */
class PublicBpmnIssueDto extends AbstractEntityDto
{
    public string $processId;

    public int $position;

    public string $severity;

    public string $code;

    public ?string $elementId;

    public string $message;

    /**
     * @param BpmnIssue $entity
     */
    public static function fromEntity(AbstractEntity $entity): self
    {
        $dto = parent::fromEntity($entity);

        $dto->processId = $entity->getProcessId();
        $dto->position = $entity->getPosition();
        $dto->severity = $entity->getSeverity();
        $dto->code = $entity->getCode();
        $dto->elementId = $entity->getElementId();
        $dto->message = $entity->getMessage();

        return $dto;
    }
}
