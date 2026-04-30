<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Console\Standalone;

use Dujana\ArabicNlp\Evaluation\RootEvaluateSuiteResult;
use Dujana\ArabicNlp\Evaluation\RootEvaluateSuiteRunner;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'suite',
    description: 'Evaluate root extraction against a TSV suite.',
)]
final class RootEvaluateSuiteStandaloneCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('suite', InputArgument::REQUIRED, 'TSV file with word<TAB>root rows')
            ->addOption('db', null, InputOption::VALUE_REQUIRED, 'Optional lexicon SQLite database path')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Output JSON')
            ->addOption('show-failures', null, InputOption::VALUE_NONE, 'Show failed rows');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $result = (new RootEvaluateSuiteRunner)->run(
                suitePath: (string) $input->getArgument('suite'),
                databasePath: is_string($input->getOption('db')) ? $input->getOption('db') : null,
            );
        } catch (InvalidArgumentException $exception) {
            $output->writeln('<error>'.$exception->getMessage().'</error>');

            return Command::FAILURE;
        }

        if ((bool) $input->getOption('json')) {
            $output->writeln(json_encode(
                $result->toArray(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ));

            return $result->failed === 0 ? Command::SUCCESS : Command::FAILURE;
        }

        $this->renderResult($output, $result, (bool) $input->getOption('show-failures'));

        return $result->failed === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    private function renderResult(OutputInterface $output, RootEvaluateSuiteResult $result, bool $showFailures): void
    {
        $table = new Table($output);
        $table
            ->setHeaders(['Metric', 'Value'])
            ->setRows([
                ['suite', $result->suitePath],
                ['total', (string) $result->total],
                ['passed', (string) $result->passed],
                ['failed', (string) $result->failed],
                ['pass_rate', (string) $result->passRate()],
            ])
            ->render();

        if (! $showFailures) {
            return;
        }

        $failures = array_values(array_filter(
            $result->rows,
            static fn (array $row): bool => ! (bool) $row['passed'],
        ));

        if ($failures === []) {
            return;
        }

        $output->writeln('<comment>Failures</comment>');

        $failuresTable = new Table($output);
        $failuresTable
            ->setHeaders(['Word', 'Expected', 'Actual', 'Source', 'Confidence'])
            ->setRows(array_map(
                static fn (array $row): array => [
                    $row['word'],
                    $row['expected_root'],
                    $row['actual_root'] ?? '',
                    $row['source'] ?? '',
                    (string) $row['confidence'],
                ],
                $failures,
            ))
            ->render();
    }
}
