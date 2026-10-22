<?php

namespace Wexample\SymfonyBpmn\Api\Normalizer\Entity\BpmnLane;

use ArrayObject;
use Wexample\SymfonyBpmn\Api\Dto\PublicBpmnLaneDto;
use Wexample\SymfonyBpmn\Entity\BpmnLane;
use Wexample\SymfonyBpmn\Entity\Traits\Manipulator\BpmnLaneManipulatorTrait;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Interface\NormalizableDataInterface;
use Wexample\SymfonyHelpers\Normalizer\AbstractEntityNormalizer;

class DefaultBpmnLaneNormalizer extends AbstractEntityNormalizer
{
    use BpmnLaneManipulatorTrait;

    public function normalizeEntity(
        BpmnLane|AbstractEntity $entity,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|NormalizableDataInterface|null {
        return PublicBpmnLaneDto::fromEntity($entity);
    }
}
