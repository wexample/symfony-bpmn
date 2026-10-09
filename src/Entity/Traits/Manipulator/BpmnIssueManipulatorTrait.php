<?php

namespace Wexample\SymfonyBpmn\Entity\Traits\Manipulator;

use Wexample\SymfonyBpmn\Entity\BpmnIssue;
use Wexample\SymfonyHelpers\Entity\Traits\Manipulator\EntityManipulatorTrait;

trait BpmnIssueManipulatorTrait
{
    use EntityManipulatorTrait;

    public static function getEntityClassName(): string
    {
        return BpmnIssue::class;
    }
}
