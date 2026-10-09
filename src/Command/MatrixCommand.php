<?php

namespace Wexample\SymfonyBpmn\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wexample\SymfonyBpmn\Service\BpmnMatrixService;
use Wexample\SymfonyBpmn\Service\BpmnRegistry;

#[AsCommand(name: 'bpmn:matrix', description: 'Who does each task of a process: its lane, and the role or grant the lane stands for.')]
class MatrixCommand extends AbstractBpmnCommand
{
    public function __construct(BpmnRegistry $registry, private readonly BpmnMatrixService $matrix)
    {
        parent::__construct($registry);
    }

    protected function configure(): void
    {
        $this->addArgument('process', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        (new SymfonyStyle($input, $output))->table(
            ['Task', 'Lane', 'Role', 'Grant', 'By', 'Interrupted by'],
            array_map(static fn (array $row) => [
                $row['name'] ?? $row['task'],
                $row['lane_name'] ?? $row['lane'] ?? '-',
                $row['role'] ?? '-',
                $row['grant'] ?? '-',
                $row['automatic'] ? 'application' : 'person',
                implode(', ', $row['interrupted_by']) ?: '-',
            ], $this->matrix->build($input->getArgument('process')))
        );

        return Command::SUCCESS;
    }
}
