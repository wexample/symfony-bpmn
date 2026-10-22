<?php

namespace Wexample\SymfonyBpmn\Api\Normalizer\Entity\BpmnIssue;

use ArrayObject;
use Wexample\SymfonyBpmn\Api\Dto\PublicBpmnIssueDto;
use Wexample\SymfonyBpmn\Entity\BpmnIssue;
use Wexample\SymfonyBpmn\Entity\Traits\Manipulator\BpmnIssueManipulatorTrait;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Interface\NormalizableDataInterface;
use Wexample\SymfonyHelpers\Normalizer\AbstractEntityNormalizer;

class DefaultBpmnIssueNormalizer extends AbstractEntityNormalizer
{
    use BpmnIssueManipulatorTrait;

    public function normalizeEntity(
        BpmnIssue|AbstractEntity $entity,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|NormalizableDataInterface|null {
        return PublicBpmnIssueDto::fromEntity($entity);
    }
}
