<?php

namespace Wexample\SymfonyBpmn\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(name: 'bpmn:validate', description: 'Checks a process — or all of them —, then compiles it into a workflow.')]
class ValidateCommand extends AbstractBpmnCommand
{
    protected function configure(): void
    {
        $this->addArgument('process', InputArgument::OPTIONAL, 'A process id; all of them without it.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $ids = $input->getArgument('process') ? [$input->getArgument('process')] : array_keys($this->registry->all());
        $failed = false;

        foreach ($ids as $id) {
            $report = $this->registry->validate($id);
            $io->section($id);

            foreach ($report->getIssues() as $issue) {
                $io->writeln((string) $issue);
            }

            try {
                $compiled = $this->registry->compile($id);
                $io->writeln(sprintf('Compiled: %d places, %d transitions.', count($compiled->places), count($compiled->transitions)));
            } catch (Throwable $exception) {
                $io->error('Not compiled: ' . $exception->getMessage());
                $failed = true;
            }

            $failed = $failed || ! $report->isValid();
        }

        return $failed ? Command::FAILURE : Command::SUCCESS;
    }
}
