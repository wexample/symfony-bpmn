<?php

namespace Wexample\SymfonyBpmn\Entity\Traits\Manipulator;

use Wexample\SymfonyBpmn\Entity\BpmnTaskRight;
use Wexample\SymfonyHelpers\Entity\Traits\Manipulator\EntityManipulatorTrait;

trait BpmnTaskRightManipulatorTrait
{
    use EntityManipulatorTrait;

    public static function getEntityClassName(): string
    {
        return BpmnTaskRight::class;
    }
}
