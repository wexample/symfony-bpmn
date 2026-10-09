<?php

namespace Wexample\SymfonyBpmn\Api\Normalizer\Entity\BpmnProcess;

use ArrayObject;
use Wexample\SymfonyBpmn\Api\Dto\PublicBpmnProcessDto;
use Wexample\SymfonyBpmn\Entity\BpmnProcess;
use Wexample\SymfonyBpmn\Entity\Traits\Manipulator\BpmnProcessManipulatorTrait;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Interface\NormalizableDataInterface;
use Wexample\SymfonyHelpers\Normalizer\AbstractEntityNormalizer;

class DefaultBpmnProcessNormalizer extends AbstractEntityNormalizer
{
    use BpmnProcessManipulatorTrait;

    public function normalizeEntity(
        BpmnProcess|AbstractEntity $entity,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|NormalizableDataInterface|null {
        return PublicBpmnProcessDto::fromEntity($entity);
    }
}
