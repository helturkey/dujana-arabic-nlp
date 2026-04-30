<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon\Database;

use PDO;
use RuntimeException;

final class LexiconDatabase
{
    private PDO $pdo;

    public function __construct(private readonly string $path)
    {
        if (! in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            throw new RuntimeException(
                'Dujana lexicon database requires the SQLite PDO driver. Enable pdo_sqlite for the PHP CLI.'
            );
        }

        $this->pdo = $this->connect($path);
    }

    public static function open(?string $path): ?self
    {
        if ($path === null || $path === '' || ! is_file($path)) {
            return null;
        }

        return new self($path);
    }

    public function path(): string
    {
        return $this->path;
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function migrate(): void
    {
        $this->pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS lexical_entries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    normalized_form TEXT NOT NULL,
    lemma TEXT NULL,
    normalized_lemma TEXT NULL,
    root TEXT NULL,
    pos_cat TEXT NULL,
    pos TEXT NULL,
    language TEXT NULL,
    confidence REAL NOT NULL DEFAULT 0.80,
    source_count INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NULL,
    updated_at TEXT NULL
);
SQL);

        $this->pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS lexical_sources (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    entry_id INTEGER NOT NULL,
    source TEXT NOT NULL,
    source_lemma TEXT NULL,
    source_root TEXT NULL,
    source_pos TEXT NULL,
    source_payload TEXT NULL,
    confidence REAL NOT NULL DEFAULT 0.80,
    FOREIGN KEY(entry_id) REFERENCES lexical_entries(id) ON DELETE CASCADE
);
SQL);

        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_lexical_entries_form ON lexical_entries(normalized_form)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_lexical_entries_root ON lexical_entries(root)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_lexical_entries_pos ON lexical_entries(pos_cat, pos)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_lexical_sources_entry ON lexical_sources(entry_id)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_lexical_sources_source ON lexical_sources(source)');
    }

    public function clear(): void
    {
        $this->pdo->exec('DELETE FROM lexical_sources');
        $this->pdo->exec('DELETE FROM lexical_entries');
    }

    private function connect(string $path): PDO
    {
        $dir = dirname($path);

        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new RuntimeException("Unable to create lexicon database directory [{$dir}].");
        }

        $pdo = new PDO('sqlite:'.$path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $pdo->exec('PRAGMA foreign_keys = ON');

        return $pdo;
    }
}
