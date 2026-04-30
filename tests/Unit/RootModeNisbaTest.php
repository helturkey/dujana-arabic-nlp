<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;

it('keeps nisba forms lexical/manual, not no-db reliable stripping', function (string $word): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->rootAnalysis?->best?->source)->not->toBeIn(['manual_lexicon', 'database']);
})->with([
    'مصري', 'مصرية', 'المصري', 'عباسي', 'عباسية', 'العباسي',
    'بغدادي', 'شامي', 'عراقي', 'حجازي', 'أندلسي', 'كوفي', 'بصري',
]);

it('resolves selected nisba roots through manual database entries', function (string $word, string $root): void {
    $path = sys_get_temp_dir().'/dujana-nisba-manual-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);
    $builder->add('manual', $word, $root, $word, posCat: 'اسم', pos: 'نسبة', confidence: 0.98);
    $builder->write(clear: false);

    $analysis = ArabicAnalyzer::make(new ArabicNlpConfig(lexiconDatabasePath: $path))
        ->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBeIn(['manual_lexicon', 'database']);
})->with([
    ['مصري', 'مصر'],
    ['مصرية', 'مصر'],
    ['المصري', 'مصر'],
    ['عباسي', 'عباس'],
    ['عباسية', 'عباس'],
    ['العباسي', 'عباس'],
    ['بغدادي', 'بغداد'],
    ['شامي', 'شام'],
    ['عراقي', 'عراق'],
    ['حجازي', 'حجاز'],
    ['أندلسي', 'اندلس'],
    ['كوفي', 'كوفة'],
    ['بصري', 'بصرة'],
]);

it('does not use a generic final ya stripping rule for normal words', function (string $word): void {
    expect(ArabicAnalyzer::make()->stem($word, StemmerModeEnum::Root))->not->toBe('');
})->with([
    'قوي',
    'سعي',
    'رمي',
    'كتابي',
]);
