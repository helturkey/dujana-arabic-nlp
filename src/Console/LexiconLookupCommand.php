<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Console;

use Dujana\ArabicNlp\Enums\NormalizationProfileEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconLookupResult;
use Dujana\ArabicNlp\Lexicon\Database\LexiconLookupRunner;
use Illuminate\Console\Command;
use InvalidArgumentException;

final class LexiconLookupCommand extends Command
{
    protected $signature = 'dujana:lexicon:lookup
        {word : Word/form to look up}
        {database? : Path to Dujana SQLite lexicon database}
        {--json : Output JSON instead of tables}
        {--profile=search : Normalization profile: search, morphology, stemming, raw}';

    protected $description = 'Look up a word/form in the Dujana unified lexicon database.';

    public function handle(LexiconLookupRunner $runner): int
    {
        $path = (string) (
            $this->argument('database')
            ?: storage_path('app/dujana/dujana-lexicon.sqlite')
        );

        try {
            $result = $runner->lookup(
                word: (string) $this->argument('word'),
                databasePath: $path,
                profile: $this->profile((string) $this->option('profile')),
            );
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return str_contains($exception->getMessage(), 'empty')
                ? self::INVALID
                : self::FAILURE;
        }

        if ((bool) $this->option('json')) {
            $this->line(json_encode(
                $result->toArray(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ));

            return self::SUCCESS;
        }

        $this->renderResult($result);

        return self::SUCCESS;
    }

    private function profile(string $profile): NormalizationProfileEnum
    {
        return NormalizationProfileEnum::tryFrom($profile) ?? NormalizationProfileEnum::Search;
    }

    private function renderResult(LexiconLookupResult $result): void
    {
        $this->info("Dujana lexicon lookup: {$result->word}");
        $this->line("Database: {$result->databasePath}");
        $this->line('Lookup forms: '.implode(', ', $result->lookupForms));

        if ($result->results === []) {
            $this->warn('No entries found.');

            return;
        }

        foreach ($result->results as $index => $row) {
            $entry = $row['entry'];

            $this->line('');
            $this->info('Entry #'.($index + 1));

            $this->table(
                ['Field', 'Value'],
                [
                    ['lookup_form', $row['lookup_form']],
                    ['normalized_form', $entry['normalized_form']],
                    ['lemma', $entry['lemma'] ?? ''],
                    ['root / best_root', $entry['root'] ?? ''],
                    ['pos_cat', $entry['pos_cat'] ?? ''],
                    ['pos', $entry['pos'] ?? ''],
                    ['language', $entry['language'] ?? ''],
                    ['confidence', $entry['confidence']],
                    ['source_count', $entry['source_count']],
                ],
            );

            $this->renderSources($entry['sources'] ?? []);
            $this->renderAlternatives($entry['alternatives'] ?? []);
        }
    }

    /**
     * @param  list<array<string,mixed>>  $sources
     */
    private function renderSources(array $sources): void
    {
        if ($sources === []) {
            return;
        }

        $this->line('Sources');
        $this->table(
            ['Source', 'Lemma', 'Root', 'POS', 'Confidence'],
            array_map(
                static fn (array $source): array => [
                    $source['source'],
                    $source['source_lemma'] ?? '',
                    $source['source_root'] ?? '',
                    $source['source_pos'] ?? '',
                    $source['confidence'],
                ],
                $sources,
            ),
        );
    }

    /**
     * @param  list<array<string,mixed>>  $alternatives
     */
    private function renderAlternatives(array $alternatives): void
    {
        if ($alternatives === []) {
            return;
        }

        $this->line('Alternatives');
        $this->table(
            ['Root', 'Confidence', 'Sources'],
            array_map(
                static fn (array $alternative): array => [
                    $alternative['root'],
                    $alternative['confidence'],
                    implode(',', $alternative['sources'] ?? []),
                ],
                $alternatives,
            ),
        );
    }
}
