<?php

namespace Wexample\SymfonyBpmn\Api\Normalizer\Entity\BpmnPath;

use ArrayObject;
use Wexample\SymfonyBpmn\Api\Dto\PublicBpmnPathDto;
use Wexample\SymfonyBpmn\Entity\BpmnPath;
use Wexample\SymfonyBpmn\Entity\Traits\Manipulator\BpmnPathManipulatorTrait;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Interface\NormalizableDataInterface;
use Wexample\SymfonyHelpers\Normalizer\AbstractEntityNormalizer;

class DefaultBpmnPathNormalizer extends AbstractEntityNormalizer
{
    use BpmnPathManipulatorTrait;

    public function normalizeEntity(
        BpmnPath|AbstractEntity $entity,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|NormalizableDataInterface|null {
        return PublicBpmnPathDto::fromEntity($entity);
    }
}
