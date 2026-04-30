<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Evaluation;

use RuntimeException;

final class RootEvaluationLoader
{
    /**
     * @return list<RootEvaluationCase>
     */
    public function load(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("Root evaluation fixture not found [{$path}].");
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException("Unable to read root evaluation fixture [{$path}].");
        }

        $cases = [];
        $lineNumber = 0;

        while (($row = fgetcsv(
            stream: $handle,
            length: null,
            separator: "\t",
            enclosure: '"',
            escape: '\\',
        )) !== false) {
            $lineNumber++;

            if ($lineNumber === 1 && isset($row[0]) && trim($row[0]) === 'word') {
                continue;
            }

            $case = RootEvaluationCase::fromColumns($row);

            if ($case !== null) {
                $cases[] = $case;
            }
        }

        fclose($handle);

        return $cases;
    }
}
