<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;

it('keeps comparative forms lexical/manual, not legacy no-db extraction', function (string $word): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->rootAnalysis?->best?->source)->not->toBeIn(['manual_lexicon', 'database']);
})->with([
    'أكبر', 'أحسن', 'أجمل', 'أفضل', 'أطول', 'أقصر', 'أكرم', 'أعلم', 'أشعر',
]);

it('resolves selected comparative and superlative roots through manual database entries', function (string $word, string $root): void {
    $path = sys_get_temp_dir().'/dujana-comparative-manual-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);
    $builder->add('manual', $word, $root, $word, posCat: 'اسم', pos: 'اسم تفضيل', confidence: 0.98);
    $builder->write(clear: false);

    $analysis = ArabicAnalyzer::make(new ArabicNlpConfig(lexiconDatabasePath: $path))
        ->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBeIn(['manual_lexicon', 'database']);
})->with([
    ['أكبر', 'كبر'],
    ['أحسن', 'حسن'],
    ['أجمل', 'جمل'],
    ['أفضل', 'فضل'],
    ['أطول', 'طول'],
    ['أقصر', 'قصر'],
    ['أكرم', 'كرم'],
    ['أعلم', 'علم'],
    ['أشعر', 'شعر'],
]);
