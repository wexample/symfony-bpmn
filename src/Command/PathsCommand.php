<?php

namespace Wexample\SymfonyBpmn\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wexample\PhpBpmn\Class\Path\PathFinder;

#[AsCommand(name: 'bpmn:paths', description: 'Tells the paths through a process, step by step and lane by lane: the scenarios to test. A few covering every flow, or every combination with --all.')]
class PathsCommand extends AbstractBpmnCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('process', InputArgument::REQUIRED)
            ->addOption('all', null, InputOption::VALUE_NONE, 'Every combination of choices, rather than a few paths taking every flow.')
            ->addOption('visits', null, InputOption::VALUE_REQUIRED, 'With --all, how many times a loop is gone round.', '1');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $process = $this->registry->get($input->getArgument('process'));

        $paths = $input->getOption('all')
            ? (new PathFinder((int) $input->getOption('visits')))->find($process)
            : (new PathFinder())->cover($process);

        foreach ($paths as $index => $path) {
            $io->section(sprintf('#%d %s%s', $index + 1, $path->describe(), $path->isComplete() ? '' : ' (cut)'));

            foreach ($path->getSteps() as $step) {
                $io->writeln(sprintf('  %-10s %-24s %s', $step->laneId ?? '-', $step->element, $step->name ?? $step->nodeId));
            }
        }

        return Command::SUCCESS;
    }
}
