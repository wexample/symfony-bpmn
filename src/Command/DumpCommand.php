<?php

namespace Wexample\SymfonyBpmn\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Dumper\MermaidDumper;
use Wexample\SymfonyBpmn\Service\BpmnRegistry;
use Wexample\SymfonyBpmn\Service\BpmnWorkflowFactory;

#[AsCommand(name: 'bpmn:dump', description: 'The workflow a process compiles to, as a Mermaid graph: its places and transitions.')]
class DumpCommand extends AbstractBpmnCommand
{
    public function __construct(BpmnRegistry $registry, private readonly BpmnWorkflowFactory $workflows)
    {
        parent::__construct($registry);
    }

    protected function configure(): void
    {
        $this->addArgument('process', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $workflow = $this->workflows->get($input->getArgument('process'));
        $output->writeln((new MermaidDumper(MermaidDumper::TRANSITION_TYPE_WORKFLOW))->dump($workflow->getDefinition()));

        return Command::SUCCESS;
    }
}
