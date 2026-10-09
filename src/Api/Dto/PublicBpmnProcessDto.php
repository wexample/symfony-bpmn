<?php

namespace Wexample\SymfonyBpmn\Api\Dto;

use Wexample\SymfonyApi\Api\Dto\AbstractEntityDto;
use Wexample\SymfonyBpmn\Entity\BpmnProcess;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * BpmnProcess as it goes on the wire: everything it holds is meant to be shown.
 */
class PublicBpmnProcessDto extends AbstractEntityDto
{
    public string $processId;

    public ?string $name;

    public ?string $documentation;

    public bool $executable;

    public int $laneCount;

    public int $nodeCount;

    public int $flowCount;

    public bool $valid;

    public int $errorCount;

    public int $warningCount;

    /**
     * @param BpmnProcess $entity
     */
    public static function fromEntity(AbstractEntity $entity): self
    {
        $dto = parent::fromEntity($entity);

        $dto->processId = $entity->getProcessId();
        $dto->name = $entity->getName();
        $dto->documentation = $entity->getDocumentation();
        $dto->executable = $entity->getExecutable();
        $dto->laneCount = $entity->getLaneCount();
        $dto->nodeCount = $entity->getNodeCount();
        $dto->flowCount = $entity->getFlowCount();
        $dto->valid = $entity->getValid();
        $dto->errorCount = $entity->getErrorCount();
        $dto->warningCount = $entity->getWarningCount();

        return $dto;
    }
}
