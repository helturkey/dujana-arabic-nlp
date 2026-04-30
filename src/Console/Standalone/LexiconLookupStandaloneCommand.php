<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Console\Standalone;

use Dujana\ArabicNlp\Enums\NormalizationProfileEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconLookupResult;
use Dujana\ArabicNlp\Lexicon\Database\LexiconLookupRunner;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'lookup',
    description: 'Look up a word/form in the Dujana unified lexicon database.',
)]
final class LexiconLookupStandaloneCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('word', InputArgument::REQUIRED, 'Word/form to look up')
            ->addArgument('database', InputArgument::OPTIONAL, 'Path to Dujana SQLite lexicon database')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Output JSON instead of tables')
            ->addOption('profile', null, InputOption::VALUE_REQUIRED, 'Normalization profile: search, morphology, stemming, raw', 'search');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $database = $input->getArgument('database');

        $path = is_string($database) && $database !== ''
            ? $database
            : getcwd().DIRECTORY_SEPARATOR.'dujana-lexicon.sqlite';

        try {
            $result = (new LexiconLookupRunner)->lookup(
                word: (string) $input->getArgument('word'),
                databasePath: $path,
                profile: $this->profile((string) $input->getOption('profile')),
            );
        } catch (InvalidArgumentException $exception) {
            $output->writeln('<error>'.$exception->getMessage().'</error>');

            return str_contains($exception->getMessage(), 'empty')
                ? Command::INVALID
                : Command::FAILURE;
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

    private function profile(string $profile): NormalizationProfileEnum
    {
        return NormalizationProfileEnum::tryFrom($profile) ?? NormalizationProfileEnum::Search;
    }

    private function renderResult(OutputInterface $output, LexiconLookupResult $result): void
    {
        $output->writeln("<info>Dujana lexicon lookup:</info> {$result->word}");
        $output->writeln("Database: {$result->databasePath}");
        $output->writeln('Lookup forms: '.implode(', ', $result->lookupForms));

        if ($result->results === []) {
            $output->writeln('<comment>No entries found.</comment>');

            return;
        }

        foreach ($result->results as $index => $row) {
            $entry = $row['entry'];

            $output->writeln('');
            $output->writeln('<info>Entry #'.($index + 1).'</info>');

            $this->renderEntryTable($output, $row['lookup_form'], $entry);
            $this->renderSources($output, $entry['sources'] ?? []);
            $this->renderAlternatives($output, $entry['alternatives'] ?? []);
        }
    }

    /**
     * @param  array<string,mixed>  $entry
     */
    private function renderEntryTable(OutputInterface $output, string $lookupForm, array $entry): void
    {
        $table = new Table($output);
        $table
            ->setHeaders(['Field', 'Value'])
            ->setRows([
                ['lookup_form', $lookupForm],
                ['normalized_form', $entry['normalized_form']],
                ['lemma', $entry['lemma'] ?? ''],
                ['root / best_root', $entry['root'] ?? ''],
                ['pos_cat', $entry['pos_cat'] ?? ''],
                ['pos', $entry['pos'] ?? ''],
                ['language', $entry['language'] ?? ''],
                ['confidence', (string) $entry['confidence']],
                ['source_count', (string) $entry['source_count']],
            ])
            ->render();
    }

    /**
     * @param  list<array<string,mixed>>  $sources
     */
    private function renderSources(OutputInterface $output, array $sources): void
    {
        if ($sources === []) {
            return;
        }

        $output->writeln('Sources');

        $table = new Table($output);
        $table
            ->setHeaders(['Source', 'Lemma', 'Root', 'POS', 'Confidence'])
            ->setRows(array_map(
                static fn (array $source): array => [
                    $source['source'],
                    $source['source_lemma'] ?? '',
                    $source['source_root'] ?? '',
                    $source['source_pos'] ?? '',
                    (string) $source['confidence'],
                ],
                $sources,
            ))
            ->render();
    }

    /**
     * @param  list<array<string,mixed>>  $alternatives
     */
    private function renderAlternatives(OutputInterface $output, array $alternatives): void
    {
        if ($alternatives === []) {
            return;
        }

        $output->writeln('Alternatives');

        $table = new Table($output);
        $table
            ->setHeaders(['Root', 'Confidence', 'Sources'])
            ->setRows(array_map(
                static fn (array $alternative): array => [
                    $alternative['root'],
                    (string) $alternative['confidence'],
                    implode(',', $alternative['sources'] ?? []),
                ],
                $alternatives,
            ))
            ->render();
    }
}
