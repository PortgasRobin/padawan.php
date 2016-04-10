<?php

namespace Padawan\Command;

use Padawan\Framework\Complete\CompleteEngine;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Padawan\Domain\ProjectRepository;

class CompleteCommand extends AsyncCommand
{

    protected function configure()
    {
        $this->setName("complete")
            ->setDescription("Finds completion")
            ->addArgument(
                "path",
                InputArgument::REQUIRED,
                "Path to the project root"
            )->addArgument(
                "column",
                InputArgument::REQUIRED,
                "Column number of cursor position"
            )->addArgument(
                "line",
                InputArgument::REQUIRED,
                "Line number of cursor position"
            )->addArgument(
                "data",
                InputArgument::REQUIRED,
                "File contents"
            )->addArgument(
                "filepath",
                InputArgument::REQUIRED,
                "Path to file relative to project root"
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $column = $input->getArgument("column");
        $file = $input->getArgument("filepath");
        $line = $input->getArgument("line");
        $content = $input->getArgument("data");
        $path = $input->getArgument("path");
        $container = $this->getContainer();

        $projectRepository = $this->getContainer()->get(ProjectRepository::class);
        $project = $projectRepository->findByPath($path);

        $completeEngine = $this->getContainer()->get(CompleteEngine::class);
        $completion = $completeEngine->createCompletion(
            $project,
            $content,
            $line,
            $column,
            $file
        );

        yield $output->write(
            json_encode(
                [
                    "completion" => $this->prepareEntries(
                        $completion["entries"]
                    ),
                    "context" => $completion["context"]
                ]
            )
        );
    }
    protected function prepareEntries(array $entries) {
        $result = [];
        foreach ($entries as $entry) {
            $result[] = [
                "name" => $entry->getName(),
                "signature" => $entry->getSignature(),
                "description" => $entry->getDesc(),
                "menu" => $entry->getMenu()
            ];
        }
        return $result;
    }
}
