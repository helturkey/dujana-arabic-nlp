<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;

it('keeps hamza-heavy lexical forms out of no-db reliable legacy extraction', function (string $word): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->rootAnalysis?->best?->source)->not->toBeIn(['manual_lexicon', 'database']);
})->with([
    'قراءة', 'قارئ', 'مقروء', 'يقرأ',
    'مسؤول', 'سائل', 'سؤال', 'أسئلة',
    'بدأ', 'بداية', 'مبتدأ',
    'أخذ', 'مأخوذ', 'أكل', 'مأكول',
]);

it('resolves selected hamza-heavy roots through manual database entries', function (string $word, string $root): void {
    $path = sys_get_temp_dir().'/dujana-hamza-manual-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);
    $builder->add('manual', $word, $root, $word, posCat: 'سماعي', pos: 'سماعي', confidence: 0.98);
    $builder->write(clear: false);

    $analysis = ArabicAnalyzer::make(new ArabicNlpConfig(lexiconDatabasePath: $path))
        ->analyze($word, StemmerModeEnum::Root);

    expect($analysis->stem)->toBe($root)
        ->and($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBeIn(['manual_lexicon', 'database']);
})->with([
    ['قراءة', 'قرأ'],
    ['قارئ', 'قرأ'],
    ['مقروء', 'قرأ'],
    ['يقرأ', 'قرأ'],
    ['مسؤول', 'سأل'],
    ['سائل', 'سأل'],
    ['سؤال', 'سأل'],
    ['أسئلة', 'سأل'],
    ['بدأ', 'بدأ'],
    ['بداية', 'بدأ'],
    ['مبتدأ', 'بدأ'],
    ['أخذ', 'أخذ'],
    ['مأخوذ', 'أخذ'],
    ['أكل', 'أكل'],
    ['مأكول', 'أكل'],
]);

it('keeps manual hamza root analysis source visible in toArray', function (): void {
    $path = sys_get_temp_dir().'/dujana-hamza-array-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);
    $builder->add('manual', 'مسؤول', 'سأل', 'مسؤول', posCat: 'سماعي', pos: 'سماعي', confidence: 0.98);
    $builder->write(clear: false);

    $array = ArabicAnalyzer::make(new ArabicNlpConfig(lexiconDatabasePath: $path))
        ->analyze('مسؤول', StemmerModeEnum::Root)
        ->toArray();

    expect($array['root'])->toBe('سأل')
        ->and($array['root_analysis']['source'])->toBeIn(['manual_lexicon', 'database']);
});
