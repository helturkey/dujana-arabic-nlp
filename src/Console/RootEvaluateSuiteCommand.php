<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Console;

use Dujana\ArabicNlp\Evaluation\RootEvaluateSuiteResult;
use Dujana\ArabicNlp\Evaluation\RootEvaluateSuiteRunner;
use Illuminate\Console\Command;
use InvalidArgumentException;

final class RootEvaluateSuiteCommand extends Command
{
    protected $signature = 'dujana:root:evaluate-suite
        {suite : TSV file with word<TAB>root rows}
        {--db= : Optional lexicon SQLite database path}
        {--json : Output JSON}
        {--show-failures : Show failed rows}';

    protected $description = 'Evaluate root extraction against a TSV suite.';

    public function handle(RootEvaluateSuiteRunner $runner): int
    {
        try {
            $result = $runner->run(
                suitePath: (string) $this->argument('suite'),
                databasePath: $this->option('db') ? (string) $this->option('db') : null,
            );
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ((bool) $this->option('json')) {
            $this->line(json_encode(
                $result->toArray(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ));

            return $result->failed === 0 ? self::SUCCESS : self::FAILURE;
        }

        $this->renderResult($result, (bool) $this->option('show-failures'));

        return $result->failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function renderResult(RootEvaluateSuiteResult $result, bool $showFailures): void
    {
        $this->table(
            ['Metric', 'Value'],
            [
                ['suite', $result->suitePath],
                ['total', $result->total],
                ['passed', $result->passed],
                ['failed', $result->failed],
                ['pass_rate', $result->passRate()],
            ],
        );

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

        $this->warn('Failures');
        $this->table(
            ['Word', 'Expected', 'Actual', 'Source', 'Confidence'],
            array_map(
                static fn (array $row): array => [
                    $row['word'],
                    $row['expected_root'],
                    $row['actual_root'] ?? '',
                    $row['source'] ?? '',
                    $row['confidence'],
                ],
                $failures,
            ),
        );
    }
}
