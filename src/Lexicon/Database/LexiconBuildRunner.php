<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon\Database;

use Dujana\ArabicNlp\Lexicon\Importers\ArramoozImporter;
use Dujana\ArabicNlp\Lexicon\Importers\ManualTsvImporter;
use Dujana\ArabicNlp\Lexicon\Importers\QabasImporter;
use InvalidArgumentException;

final class LexiconBuildRunner
{
    public function build(
        ?string $qabasPath = null,
        ?string $arramoozPath = null,
        ?string $manualPath = null,
        string $outputPath = 'dujana-lexicon.sqlite',
        bool $allLanguages = false,
        bool $append = false,
    ): LexiconBuildResult {
        $qabasPath = $this->nullablePath($qabasPath);
        $arramoozPath = $this->nullablePath($arramoozPath);
        $manualPath = $this->nullablePath($manualPath);

        if ($qabasPath === null && $arramoozPath === null && $manualPath === null) {
            throw new InvalidArgumentException(
                'No input source provided. Use --qabas, --arramooz, --manual, or keep packaged manual roots.'
            );
        }

        $this->ensureOutputDirectoryExists($outputPath);

        $database = new LexiconDatabase($outputPath);
        $builder = new LexiconBuilder($database);

        $builder->begin(clear: ! $append);

        $imported = [];

        if ($qabasPath !== null) {
            $this->assertReadableFile($qabasPath, 'Qabas CSV');

            $imported['qabas'] = (new QabasImporter(
                builder: $builder,
                modernOnly: ! $allLanguages,
            ))->import($qabasPath);
        }

        if ($arramoozPath !== null) {
            $this->assertReadableFile($arramoozPath, 'Arramooz SQLite');

            $imported['arramooz'] = (new ArramoozImporter($builder))
                ->import($arramoozPath);
        }

        if ($manualPath !== null) {
            $this->assertReadableFile($manualPath, 'Manual roots TSV');

            $imported['manual'] = (new ManualTsvImporter($builder))
                ->import($manualPath);
        }

        /*
         * Important:
         * begin() already handled clear/append.
         * write(false) only finalizes/commits the streaming build.
         */
        $entries = $builder->write(clear: false);

        return new LexiconBuildResult(
            outputPath: $outputPath,
            imported: $imported,
            entries: $entries,
        );
    }

    private function nullablePath(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $path = trim($path);

        return $path === '' ? null : $path;
    }

    private function ensureOutputDirectoryExists(string $outputPath): void
    {
        $directory = dirname($outputPath);

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new InvalidArgumentException("Could not create output directory: {$directory}");
        }
    }

    private function assertReadableFile(string $path, string $label): void
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new InvalidArgumentException("{$label} not found or not readable: {$path}");
        }
    }
}
