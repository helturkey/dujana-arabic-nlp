<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Console\Standalone;

use Dujana\ArabicNlp\Lexicon\Database\LexiconStatsResult;
use Dujana\ArabicNlp\Lexicon\Database\LexiconStatsRunner;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'stats',
    description: 'Show statistics for the Dujana unified lexicon SQLite database.',
)]
final class LexiconStatsStandaloneCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('database', InputArgument::OPTIONAL, 'Path to Dujana SQLite lexicon database')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Output JSON instead of tables');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $database = $input->getArgument('database');

        $path = is_string($database) && $database !== ''
            ? $database
            : getcwd().DIRECTORY_SEPARATOR.'dujana-lexicon.sqlite';

        try {
            $result = (new LexiconStatsRunner)->stats($path);
        } catch (InvalidArgumentException $exception) {
            $output->writeln('<error>'.$exception->getMessage().'</error>');

            return Command::FAILURE;
        }

        if ((bool) $input->getOption('json')) {
            $output->writeln(json_encode(
                $result->toArray(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ));

            return Command::SUCCESS;
        }

        $this->renderResult($output, $result);

        return Command::SUCCESS;
    }

    private function renderResult(OutputInterface $output, LexiconStatsResult $result): void
    {
        $output->writeln('<info>Dujana lexicon stats</info>');
        $output->writeln("Database: {$result->databasePath}");

        $table = new Table($output);
        $table
            ->setHeaders(['Metric', 'Value'])
            ->setRows(array_map(
                static fn (string $key, int $value): array => [$key, (string) $value],
                array_keys($result->totals),
                $result->totals,
            ))
            ->render();

        $this->table($output, 'Sources', ['Source', 'Count'], $result->sources);
        $this->table($output, 'POS Categories', ['POS Category', 'Count'], $result->posCategories);
        $this->table($output, 'Languages', ['Language', 'Count'], $result->languages);
    }

    /**
     * @param  list<array<string,mixed>>  $rows
     * @param  list<string>  $headers
     */
    private function table(OutputInterface $output, string $title, array $headers, array $rows): void
    {
        $output->writeln($title);

        $table = new Table($output);
        $table
            ->setHeaders($headers)
            ->setRows(array_map(
                static fn (array $row): array => array_map('strval', array_values($row)),
                $rows,
            ))
            ->render();
    }
}
