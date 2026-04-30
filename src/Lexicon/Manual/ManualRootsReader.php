<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon\Manual;

use InvalidArgumentException;
use RuntimeException;

final class ManualRootsReader
{
    /**
     * Expected TSV columns:
     *
     * form	root	lemma	pos_cat	pos	language	confidence
     *
     * Minimal accepted row:
     *
     * form	root
     *
     * Comments start with #.
     *
     * @return list<ManualRootEntry>
     */
    public function read(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("Manual roots file not found: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            throw new RuntimeException("Unable to read manual roots file: {$path}");
        }

        $entries = [];

        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $columns = array_map('trim', explode("\t", $line));

            if (count($columns) < 2) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid manual roots row at %s:%d. Expected at least form and root.',
                    $path,
                    $lineNumber + 1,
                ));
            }

            [$form, $root] = $columns;

            if ($form === '' || $root === '') {
                throw new InvalidArgumentException(sprintf(
                    'Invalid manual roots row at %s:%d. Form and root are required.',
                    $path,
                    $lineNumber + 1,
                ));
            }

            $confidence = isset($columns[6]) && $columns[6] !== ''
                ? (float) $columns[6]
                : 0.98;

            $entries[] = new ManualRootEntry(
                form: $form,
                root: $root,
                lemma: $columns[2] ?? $form,
                posCat: $columns[3] ?? null,
                pos: $columns[4] ?? null,
                language: $columns[5] ?? 'فصحى',
                confidence: $confidence,
            );
        }

        return $entries;
    }
}
