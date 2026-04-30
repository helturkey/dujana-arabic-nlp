<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Console;

use Dujana\ArabicNlp\Evaluation\RootEvaluateResult;
use Dujana\ArabicNlp\Evaluation\RootEvaluateRunner;
use Illuminate\Console\Command;

final class RootEvaluateCommand extends Command
{
    protected $signature = 'dujana:root:evaluate
        {word : Word/form to analyze}
        {root : Expected root}
        {--db= : Optional lexicon SQLite database path}
        {--json : Output JSON}';

    protected $description = 'Evaluate root extraction for a single word.';

    public function handle(RootEvaluateRunner $runner): int
    {
        $result = $runner->evaluate(
            word: (string) $this->argument('word'),
            expectedRoot: (string) $this->argument('root'),
            databasePath: $this->option('db') ? (string) $this->option('db') : null,
        );

        if ((bool) $this->option('json')) {
            $this->line(json_encode(
                $result->toArray(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ));

            return $result->passed ? self::SUCCESS : self::FAILURE;
        }

        $this->renderResult($result);

        return $result->passed ? self::SUCCESS : self::FAILURE;
    }

    private function renderResult(RootEvaluateResult $result): void
    {
        $this->table(
            ['Field', 'Value'],
            [
                ['word', $result->word],
                ['expected_root', $result->expectedRoot],
                ['actual_root', $result->analysis->root ?? ''],
                ['passed', $result->passed ? 'yes' : 'no'],
                ['source', $result->analysis->rootAnalysis->best->source ?? ''],
                ['confidence', $result->analysis->confidence],
            ],
        );
    }
}
