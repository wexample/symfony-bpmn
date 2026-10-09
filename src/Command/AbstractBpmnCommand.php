<?php

namespace Wexample\SymfonyBpmn\Command;

use Symfony\Component\Console\Command\Command;
use Wexample\SymfonyBpmn\Service\BpmnRegistry;

abstract class AbstractBpmnCommand extends Command
{
    public function __construct(
        protected readonly BpmnRegistry $registry,
    ) {
        parent::__construct();
    }
}
