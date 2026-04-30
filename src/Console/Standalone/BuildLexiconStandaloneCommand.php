<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Console\Standalone;

use Dujana\ArabicNlp\Lexicon\Database\LexiconBuildResult;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuildRunner;
use Dujana\ArabicNlp\Support\SafePath;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'build',
    description: 'Build an optional Dujana unified lexicon SQLite database from local dictionary files.',
)]
final class BuildLexiconStandaloneCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('qabas', null, InputOption::VALUE_REQUIRED, 'Path to Qabas CSV')
            ->addOption('arramooz', null, InputOption::VALUE_REQUIRED, 'Path to Arramooz SQLite')
            ->addOption('manual', null, InputOption::VALUE_REQUIRED, 'Path to manual TSV: form<TAB>root<TAB>pos_cat<TAB>pos<TAB>language')
            ->addOption('no-manual', null, InputOption::VALUE_NONE, 'Do not auto-import manual roots')
            ->addOption('output', null, InputOption::VALUE_REQUIRED, 'Output SQLite path')
            ->addOption('db', null, InputOption::VALUE_REQUIRED, 'Alias for --output')
            ->addOption('all-languages', null, InputOption::VALUE_NONE, 'Include non-Fusha rows from Qabas')
            ->addOption('append', null, InputOption::VALUE_NONE, 'Append to existing DB instead of clearing it');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $safeBase = getenv('DUJANA_SAFE_OUTPUT_DIR') ?: getcwd();

        try {
            $manual = $input->getOption('no-manual')
                ? null
                : ($this->stringOption($input, 'manual') ?: $this->defaultManualPath());

            $qabas = $this->stringOption($input, 'qabas');
            $arramooz = $this->stringOption($input, 'arramooz');

            if ($manual !== null) {
                $manual = SafePath::assertReadableFile($manual, 'manual TSV');
            }

            if ($qabas !== null) {
                $qabas = SafePath::assertReadableFile($qabas, 'Qabas CSV');
            }

            if ($arramooz !== null) {
                $arramooz = SafePath::assertReadableFile($arramooz, 'Arramooz SQLite');
            }

            $outputPath = $this->stringOption($input, 'output')
                ?: $this->stringOption($input, 'db')
                ?: $this->defaultOutputPath();

            $validatedOutputPath = SafePath::assertInsideDirectory(
                path: $outputPath,
                baseDirectory: $safeBase,
                label: 'output database path',
            );

            $result = (new LexiconBuildRunner)->build(
                qabasPath: $qabas,
                arramoozPath: $arramooz,
                manualPath: $manual,
                outputPath: $validatedOutputPath,
                allLanguages: (bool) $input->getOption('all-languages'),
                append: (bool) $input->getOption('append'),
            );
        } catch (InvalidArgumentException $exception) {
            $output->writeln('<comment>'.$exception->getMessage().'</comment>');

            return Command::INVALID;
        }

        if ($manual !== null) {
            $output->writeln("Manual roots: {$manual}");
        }

        $this->renderResult($output, $result);

        return Command::SUCCESS;
    }

    private function stringOption(InputInterface $input, string $name): ?string
    {
        $value = $input->getOption($name);

        if ($value === null || $value === false || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private function defaultOutputPath(): string
    {
        return getcwd().DIRECTORY_SEPARATOR.'dujana-lexicon.sqlite';
    }

    private function defaultManualPath(): ?string
    {
        $paths = [
            getcwd().DIRECTORY_SEPARATOR.'resources/lexicon/manual-roots.tsv',
            dirname(__DIR__, 3).DIRECTORY_SEPARATOR.'resources/lexicon/manual-roots.tsv',
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function renderResult(OutputInterface $output, LexiconBuildResult $result): void
    {
        $output->writeln("<info>Dujana lexicon database built:</info> {$result->outputPath}");

        $table = new Table($output);
        $table
            ->setHeaders(['Source', 'Imported rows'])
            ->setRows(array_map(
                static fn (string $source, int $count): array => [$source, (string) $count],
                array_keys($result->imported),
                $result->imported,
            ))
            ->render();

        $output->writeln("<info>Unified entries:</info> {$result->entries}");
    }
}
