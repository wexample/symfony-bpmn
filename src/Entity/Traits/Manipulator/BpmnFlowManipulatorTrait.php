<?php

namespace Wexample\SymfonyBpmn\Entity\Traits\Manipulator;

use Wexample\SymfonyBpmn\Entity\BpmnFlow;
use Wexample\SymfonyHelpers\Entity\Traits\Manipulator\EntityManipulatorTrait;

trait BpmnFlowManipulatorTrait
{
    use EntityManipulatorTrait;

    public static function getEntityClassName(): string
    {
        return BpmnFlow::class;
    }
}
