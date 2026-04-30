<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon\Database;

use InvalidArgumentException;
use PDO;

final class LexiconStatsRunner
{
    public function stats(string $databasePath): LexiconStatsResult
    {
        if (! is_file($databasePath) || ! is_readable($databasePath)) {
            throw new InvalidArgumentException("Lexicon database not found or not readable: {$databasePath}");
        }

        $database = new LexiconDatabase($databasePath);
        $pdo = $database->pdo();

        return new LexiconStatsResult(
            databasePath: $databasePath,
            totals: [
                'entries' => $this->count($pdo, 'SELECT COUNT(*) FROM lexical_entries'),
                'sources' => $this->count($pdo, 'SELECT COUNT(*) FROM lexical_sources'),
                'with_root' => $this->count($pdo, 'SELECT COUNT(*) FROM lexical_entries WHERE root IS NOT NULL AND root != ""'),
                'without_root' => $this->count($pdo, 'SELECT COUNT(*) FROM lexical_entries WHERE root IS NULL OR root = ""'),
            ],
            sources: $this->rows($pdo, '
                SELECT source, COUNT(*) AS count
                FROM lexical_sources
                GROUP BY source
                ORDER BY count DESC, source ASC
            '),
            posCategories: $this->rows($pdo, '
                SELECT COALESCE(pos_cat, "") AS pos_cat, COUNT(*) AS count
                FROM lexical_entries
                GROUP BY pos_cat
                ORDER BY count DESC, pos_cat ASC
            '),
            languages: $this->rows($pdo, '
                SELECT COALESCE(language, "") AS language, COUNT(*) AS count
                FROM lexical_entries
                GROUP BY language
                ORDER BY count DESC, language ASC
            '),
        );
    }

    private function count(PDO $pdo, string $sql): int
    {
        return (int) $pdo->query($sql)->fetchColumn();
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function rows(PDO $pdo, string $sql): array
    {
        return array_map(
            static fn (array $row): array => array_map(
                static fn ($value) => is_numeric($value) ? (int) $value : (string) $value,
                $row,
            ),
            $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [],
        );
    }
}
