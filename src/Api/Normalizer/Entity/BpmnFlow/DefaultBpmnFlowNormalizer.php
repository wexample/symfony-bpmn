<?php

namespace Wexample\SymfonyBpmn\Api\Normalizer\Entity\BpmnFlow;

use ArrayObject;
use Wexample\SymfonyBpmn\Api\Dto\PublicBpmnFlowDto;
use Wexample\SymfonyBpmn\Entity\BpmnFlow;
use Wexample\SymfonyBpmn\Entity\Traits\Manipulator\BpmnFlowManipulatorTrait;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Interface\NormalizableDataInterface;
use Wexample\SymfonyHelpers\Normalizer\AbstractEntityNormalizer;

class DefaultBpmnFlowNormalizer extends AbstractEntityNormalizer
{
    use BpmnFlowManipulatorTrait;

    public function normalizeEntity(
        BpmnFlow|AbstractEntity $entity,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|NormalizableDataInterface|null {
        return PublicBpmnFlowDto::fromEntity($entity);
    }
}
