<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Console\Standalone;

use Dujana\ArabicNlp\Evaluation\RootEvaluateResult;
use Dujana\ArabicNlp\Evaluation\RootEvaluateRunner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'evaluate',
    description: 'Evaluate root extraction for a single word.',
)]
final class RootEvaluateStandaloneCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('word', InputArgument::REQUIRED, 'Word/form to analyze')
            ->addArgument('root', InputArgument::REQUIRED, 'Expected root')
            ->addOption('db', null, InputOption::VALUE_REQUIRED, 'Optional lexicon SQLite database path')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Output JSON');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = (new RootEvaluateRunner)->evaluate(
            word: (string) $input->getArgument('word'),
            expectedRoot: (string) $input->getArgument('root'),
            databasePath: is_string($input->getOption('db')) ? $input->getOption('db') : null,
        );

        if ((bool) $input->getOption('json')) {
            $output->writeln(json_encode(
                $result->toArray(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ));

            return $result->passed ? Command::SUCCESS : Command::FAILURE;
        }

        $this->renderResult($output, $result);

        return $result->passed ? Command::SUCCESS : Command::FAILURE;
    }

    private function renderResult(OutputInterface $output, RootEvaluateResult $result): void
    {
        $table = new Table($output);
        $table
            ->setHeaders(['Field', 'Value'])
            ->setRows([
                ['word', $result->word],
                ['expected_root', $result->expectedRoot],
                ['actual_root', $result->analysis->root ?? ''],
                ['passed', $result->passed ? 'yes' : 'no'],
                ['source', $result->analysis->rootAnalysis->best->source ?? ''],
                ['confidence', (string) $result->analysis->confidence],
            ])
            ->render();
    }
}
