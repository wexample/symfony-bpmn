<?php

namespace Wexample\SymfonyBpmn\Api\Normalizer\Entity\BpmnTaskRight;

use ArrayObject;
use Wexample\SymfonyBpmn\Api\Dto\PublicBpmnTaskRightDto;
use Wexample\SymfonyBpmn\Entity\BpmnTaskRight;
use Wexample\SymfonyBpmn\Entity\Traits\Manipulator\BpmnTaskRightManipulatorTrait;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Interface\NormalizableDataInterface;
use Wexample\SymfonyHelpers\Normalizer\AbstractEntityNormalizer;

class DefaultBpmnTaskRightNormalizer extends AbstractEntityNormalizer
{
    use BpmnTaskRightManipulatorTrait;

    public function normalizeEntity(
        BpmnTaskRight|AbstractEntity $entity,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|NormalizableDataInterface|null {
        return PublicBpmnTaskRightDto::fromEntity($entity);
    }
}
