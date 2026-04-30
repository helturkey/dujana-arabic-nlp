<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;
use Dujana\ArabicNlp\Lexicon\Importers\ManualTsvImporter;

it('uses manual lexicon overrides for selected hamza and irregular roots', function (string $word, string $root): void {
    $manual = dirname(__DIR__, 2).'/resources/lexicon/manual-roots.tsv';

    if (! is_file($manual)) {
        $this->markTestSkipped('manual-roots.tsv was not found.');
    }

    $db = sys_get_temp_dir().'/dujana-manual-roots-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($db);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);

    (new ManualTsvImporter($builder))->import($manual);

    $builder->write(clear: false);

    $analysis = ArabicAnalyzer::make(new ArabicNlpConfig(
        lexiconDatabasePath: $db,
    ))->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBeIn(['manual_lexicon', 'database']);
})->with([
    ['تساؤل', 'سأل'],
    ['مبادئ', 'بدأ'],
    ['إيمان', 'أمن'],
    ['رئيس', 'رأس'],
    ['شؤون', 'شأن'],
    ['مآخذ', 'أخذ'],
    ['قارئ', 'قرأ'],
]);
