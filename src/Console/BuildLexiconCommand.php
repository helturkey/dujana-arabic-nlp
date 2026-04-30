<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Console;

use Dujana\ArabicNlp\Lexicon\Database\LexiconBuildResult;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuildRunner;
use Dujana\ArabicNlp\Support\SafePath;
use Illuminate\Console\Command;
use InvalidArgumentException;

final class BuildLexiconCommand extends Command
{
    protected $signature = 'dujana:lexicon:build
        {--qabas= : Path to Qabas CSV}
        {--arramooz= : Path to Arramooz SQLite}
        {--manual= : Path to manual TSV: form<TAB>root<TAB>pos_cat<TAB>pos<TAB>language}
        {--no-manual : Do not auto-import manual roots}
        {--output= : Output SQLite path}
        {--db= : Alias for --output}
        {--all-languages : Include non-Fusha rows from Qabas}
        {--append : Append to existing DB instead of clearing it}';

    protected $description = 'Build an optional Dujana unified lexicon SQLite database from local dictionary files.';

    public function handle(LexiconBuildRunner $runner): int
    {
        $safeBase = storage_path('app/dujana');

        try {
            $manual = $this->option('no-manual')
                ? null
                : ($this->stringOption('manual') ?: $this->defaultManualPath());

            $qabas = $this->stringOption('qabas');
            $arramooz = $this->stringOption('arramooz');

            if ($manual !== null) {
                $manual = SafePath::assertReadableFile($manual, 'manual TSV');
            }

            if ($qabas !== null) {
                $qabas = SafePath::assertReadableFile($qabas, 'Qabas CSV');
            }

            if ($arramooz !== null) {
                $arramooz = SafePath::assertReadableFile($arramooz, 'Arramooz SQLite');
            }

            $outputPath = $this->stringOption('output')
                ?: $this->stringOption('db')
                ?: storage_path('app/dujana/dujana-lexicon.sqlite');

            $validatedOutputPath = SafePath::assertInsideDirectory(
                path: $outputPath,
                baseDirectory: $safeBase,
                label: 'output database path',
            );

            $result = $runner->build(
                qabasPath: $qabas,
                arramoozPath: $arramooz,
                manualPath: $manual,
                outputPath: $validatedOutputPath,
                allLanguages: (bool) $this->option('all-languages'),
                append: (bool) $this->option('append'),
            );
        } catch (InvalidArgumentException $exception) {
            $this->warn($exception->getMessage());

            return self::INVALID;
        }

        if ($manual !== null) {
            $this->line("Manual roots: {$manual}");
        }

        $this->renderResult($result);

        return self::SUCCESS;
    }

    private function stringOption(string $name): ?string
    {
        $value = $this->option($name);

        if ($value === null || $value === false || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private function renderResult(LexiconBuildResult $result): void
    {
        $this->info("Dujana lexicon database built: {$result->outputPath}");

        $this->table(
            ['Source', 'Imported rows'],
            array_map(
                static fn (string $source, int $count): array => [$source, $count],
                array_keys($result->imported),
                $result->imported,
            ),
        );

        $this->info("Unified entries: {$result->entries}");
    }

    private function defaultManualPath(): ?string
    {
        $storagePath = storage_path('app/dujana/lexicon/manual-roots.tsv');

        if (is_file($storagePath)) {
            return $storagePath;
        }

        $packagePath = dirname(__DIR__, 2).'/resources/lexicon/manual-roots.tsv';

        if (is_file($packagePath)) {
            return $packagePath;
        }

        return null;
    }
}
