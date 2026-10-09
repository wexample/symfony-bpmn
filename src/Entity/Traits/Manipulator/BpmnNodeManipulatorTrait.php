<?php

namespace Wexample\SymfonyBpmn\Entity\Traits\Manipulator;

use Wexample\SymfonyBpmn\Entity\BpmnNode;
use Wexample\SymfonyHelpers\Entity\Traits\Manipulator\EntityManipulatorTrait;

trait BpmnNodeManipulatorTrait
{
    use EntityManipulatorTrait;

    public static function getEntityClassName(): string
    {
        return BpmnNode::class;
    }
}
