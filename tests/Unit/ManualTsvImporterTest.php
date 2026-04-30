<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;
use Dujana\ArabicNlp\Lexicon\Importers\ManualTsvImporter;

it('imports manual roots tsv into the lexicon database as trusted manual entries', function (): void {
    $tsv = tempManualTsvImporterPath('manual');
    $db = tempManualTsvImporterDatabasePath('manual');

    file_put_contents($tsv, implode(PHP_EOL, [
        '# form	root	lemma	pos_cat	pos	language	confidence',
        'مد	مدد	مد	فعل	فعل	فصحى	0.99',
        'أقلام	قلم	قلم	اسم	جمع تكسير	فصحى	0.98',
    ]));

    $database = new LexiconDatabase($db);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);

    $imported = (new ManualTsvImporter($builder))->import($tsv);

    $entries = $builder->write(clear: false);

    expect($imported)->toBe(2)
        ->and($entries)->toBeGreaterThanOrEqual(2);

    $analyzer = ArabicAnalyzer::make(new ArabicNlpConfig(
        lexiconDatabasePath: $db,
    ));

    $short = $analyzer->analyze('مد', StemmerModeEnum::Root);
    $plural = $analyzer->analyze('أقلام', StemmerModeEnum::Root);

    expect($short->root)->toBe('مدد')
        ->and($short->rootAnalysis?->best?->source)->toBe('manual_lexicon')
        ->and($short->rootAnalysis?->reliable())->toBeTrue()
        ->and($plural->root)->toBe('قلم')
        ->and($plural->rootAnalysis?->best?->source)->toBe('manual_lexicon')
        ->and($plural->rootAnalysis?->reliable())->toBeTrue();
});

it('returns the number of imported manual rows', function (): void {
    $tsv = tempManualTsvImporterPath('count');
    $db = tempManualTsvImporterDatabasePath('count');

    file_put_contents($tsv, implode(PHP_EOL, [
        'قال	قول',
        'باع	بيع',
        'رد	ردد',
    ]));

    $database = new LexiconDatabase($db);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);

    $imported = (new ManualTsvImporter($builder))->import($tsv);

    $builder->write(clear: false);

    expect($imported)->toBe(3);
});

it('propagates invalid manual tsv rows from the reader', function (): void {
    $tsv = tempManualTsvImporterPath('invalid');
    $db = tempManualTsvImporterDatabasePath('invalid');

    file_put_contents($tsv, "مد\n");

    $database = new LexiconDatabase($db);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);

    expect(fn () => (new ManualTsvImporter($builder))->import($tsv))
        ->toThrow(InvalidArgumentException::class);
});

function tempManualTsvImporterPath(string $suffix): string
{
    return sys_get_temp_dir().'/dujana-manual-tsv-importer-'.$suffix.'-'.uniqid().'.tsv';
}

function tempManualTsvImporterDatabasePath(string $suffix): string
{
    return sys_get_temp_dir().'/dujana-manual-tsv-importer-'.$suffix.'-'.uniqid().'.sqlite';
}
