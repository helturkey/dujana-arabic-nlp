<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Console;

use Dujana\ArabicNlp\Lexicon\Database\LexiconStatsResult;
use Dujana\ArabicNlp\Lexicon\Database\LexiconStatsRunner;
use Illuminate\Console\Command;
use InvalidArgumentException;

final class LexiconStatsCommand extends Command
{
    protected $signature = 'dujana:lexicon:stats
        {database? : Path to Dujana SQLite lexicon database}
        {--json : Output JSON instead of tables}';

    protected $description = 'Show statistics for the Dujana unified lexicon SQLite database.';

    public function handle(LexiconStatsRunner $runner): int
    {
        $path = (string) (
            $this->argument('database')
            ?: storage_path('app/dujana/dujana-lexicon.sqlite')
        );

        try {
            $result = $runner->stats($path);
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
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

    private function renderResult(LexiconStatsResult $result): void
    {
        $this->info('Dujana lexicon stats');
        $this->line("Database: {$result->databasePath}");

        $this->table(
            ['Metric', 'Value'],
            array_map(
                static fn (string $key, int $value): array => [$key, $value],
                array_keys($result->totals),
                $result->totals,
            ),
        );

        $this->line('Sources');
        $this->table(['Source', 'Count'], array_map(
            static fn (array $row): array => [$row['source'], $row['count']],
            $result->sources,
        ));

        $this->line('POS Categories');
        $this->table(['POS Category', 'Count'], array_map(
            static fn (array $row): array => [$row['pos_cat'], $row['count']],
            $result->posCategories,
        ));

        $this->line('Languages');
        $this->table(['Language', 'Count'], array_map(
            static fn (array $row): array => [$row['language'], $row['count']],
            $result->languages,
        ));
    }
}
