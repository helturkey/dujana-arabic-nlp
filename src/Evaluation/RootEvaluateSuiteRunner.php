<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Evaluation;

use InvalidArgumentException;

final class RootEvaluateSuiteRunner
{
    public function run(string $suitePath, ?string $databasePath = null): RootEvaluateSuiteResult
    {
        if (! is_file($suitePath) || ! is_readable($suitePath)) {
            throw new InvalidArgumentException("Evaluation suite not found or not readable: {$suitePath}");
        }

        $runner = new RootEvaluateRunner;
        $rows = [];

        foreach ($this->readRows($suitePath) as $row) {
            $word = $row['word'];
            $expected = $row['root'];

            $result = $runner->evaluate(
                word: $word,
                expectedRoot: $expected,
                databasePath: $databasePath,
            );

            $rows[] = [
                'word' => $word,
                'expected_root' => $expected,
                'actual_root' => $result->analysis->root,
                'passed' => $result->passed,
                'source' => $result->analysis->rootAnalysis?->best?->source,
                'confidence' => $result->analysis->confidence,
            ];
        }

        $passed = count(array_filter($rows, static fn (array $row): bool => (bool) $row['passed']));

        return new RootEvaluateSuiteResult(
            suitePath: $suitePath,
            total: count($rows),
            passed: $passed,
            failed: count($rows) - $passed,
            rows: $rows,
        );
    }

    /**
     * Expects TSV with at least:
     * word<TAB>root
     *
     * @return list<array{word:string,root:string}>
     */
    private function readRows(string $path): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return [];
        }

        $rows = [];

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $parts = explode("\t", $line);

            if (count($parts) < 2) {
                continue;
            }

            if ($parts[0] === 'word' && $parts[1] === 'root') {
                continue;
            }

            $rows[] = [
                'word' => $parts[0],
                'root' => $parts[1],
            ];
        }

        fclose($handle);

        return $rows;
    }
}
