<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon\Database;

use Dujana\ArabicNlp\Enums\NormalizationProfileEnum;
use InvalidArgumentException;

final class LexiconLookupRunner
{
    public function lookup(
        string $word,
        string $databasePath,
        NormalizationProfileEnum $profile = NormalizationProfileEnum::Search,
    ): LexiconLookupResult {
        $word = trim($word);

        if ($word === '') {
            throw new InvalidArgumentException('Word cannot be empty.');
        }

        if (! is_file($databasePath) || ! is_readable($databasePath)) {
            throw new InvalidArgumentException("Lexicon database not found or not readable: {$databasePath}");
        }

        $lookup = new LexiconLookup(new LexiconDatabase($databasePath));

        $rows = $lookup->lookupWithForms($word, $profile);

        return new LexiconLookupResult(
            word: $word,
            databasePath: $databasePath,
            profile: $profile,
            lookupForms: $lookup->lookupForms($word, $profile),
            results: array_map(
                static fn (array $row): array => [
                    'lookup_form' => $row['lookup_form'],
                    'entry' => $row['entry']->toArray(),
                ],
                $rows,
            ),
        );
    }
}
