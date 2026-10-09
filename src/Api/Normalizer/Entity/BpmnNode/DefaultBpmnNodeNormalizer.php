<?php

namespace Wexample\SymfonyBpmn\Api\Normalizer\Entity\BpmnNode;

use ArrayObject;
use Wexample\SymfonyBpmn\Api\Dto\PublicBpmnNodeDto;
use Wexample\SymfonyBpmn\Entity\BpmnNode;
use Wexample\SymfonyBpmn\Entity\Traits\Manipulator\BpmnNodeManipulatorTrait;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Interface\NormalizableDataInterface;
use Wexample\SymfonyHelpers\Normalizer\AbstractEntityNormalizer;

class DefaultBpmnNodeNormalizer extends AbstractEntityNormalizer
{
    use BpmnNodeManipulatorTrait;

    public function normalizeEntity(
        BpmnNode|AbstractEntity $entity,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|NormalizableDataInterface|null {
        return PublicBpmnNodeDto::fromEntity($entity);
    }
}
