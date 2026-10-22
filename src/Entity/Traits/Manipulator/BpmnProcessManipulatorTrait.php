<?php

namespace Wexample\SymfonyBpmn\Entity\Traits\Manipulator;

use Wexample\SymfonyBpmn\Entity\BpmnProcess;
use Wexample\SymfonyHelpers\Entity\Traits\Manipulator\EntityManipulatorTrait;

trait BpmnProcessManipulatorTrait
{
    use EntityManipulatorTrait;

    public static function getEntityClassName(): string
    {
        return BpmnProcess::class;
    }
}
