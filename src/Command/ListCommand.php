<?php

namespace Wexample\SymfonyBpmn\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'bpmn:list', description: 'Lists the processes of the application, their file and whether they are sound.')]
class ListCommand extends AbstractBpmnCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rows = [];

        foreach ($this->registry->all() as $id => $process) {
            $report = $this->registry->validate($id);
            $rows[] = [
                $id,
                $process->getLabel(),
                count($process->getFlow()->getNodes()),
                count($process->getAllLanes()),
                $report->isValid() ? 'ok' : count($report->getErrors()) . ' error(s)',
                $this->registry->getFile($id),
            ];
        }

        (new SymfonyStyle($input, $output))->table(['Process', 'Name', 'Nodes', 'Lanes', 'State', 'File'], $rows);

        return Command::SUCCESS;
    }
}
