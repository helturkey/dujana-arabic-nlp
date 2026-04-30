<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;

function dujanaWeakContextAnalyzerFor(string $word, string $root): ArabicAnalyzer
{
    $path = sys_get_temp_dir().'/dujana-weak-manual-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);
    $builder->add('manual', $word, $root, $word, posCat: 'فعل', pos: 'فعل', confidence: 0.98);
    $builder->write(clear: false);

    return ArabicAnalyzer::make(new ArabicNlpConfig(lexiconDatabasePath: $path));
}

it('keeps selected weak-context forms lexical/manual, not legacy no-db extraction', function (string $word): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->rootAnalysis?->best?->source)->not->toBeIn(['manual_lexicon', 'database']);
})->with([
    'قالوا', 'قالت', 'قلنا', 'يقولون',
    'باعوا', 'بعت', 'بعنا', 'يبيعون',
    'دعوا', 'دعونا', 'دعوت', 'يدعون',
    'رموا', 'رميت', 'رمينا', 'يرمون',
    'سعوا', 'سعيت', 'سعينا', 'يسعون',
]);

it('resolves selected hollow weak roots through manual database entries', function (string $word, string $root): void {
    $analysis = dujanaWeakContextAnalyzerFor($word, $root)->analyze($word, StemmerModeEnum::Root);

    expect($analysis->stem)->toBe($root)
        ->and($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBeIn(['manual_lexicon', 'database']);
})->with([
    ['قالوا', 'قول'],
    ['قالت', 'قول'],
    ['قلنا', 'قول'],
    ['يقولون', 'قول'],
    ['باعوا', 'بيع'],
    ['بعت', 'بيع'],
    ['بعنا', 'بيع'],
    ['يبيعون', 'بيع'],
]);

it('resolves selected defective weak roots through manual database entries', function (string $word, string $root): void {
    $analysis = dujanaWeakContextAnalyzerFor($word, $root)->analyze($word, StemmerModeEnum::Root);

    expect($analysis->stem)->toBe($root)
        ->and($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBeIn(['manual_lexicon', 'database']);
})->with([
    ['دعوا', 'دعو'],
    ['دعونا', 'دعو'],
    ['دعوت', 'دعو'],
    ['يدعون', 'دعو'],
    ['رموا', 'رمي'],
    ['رميت', 'رمي'],
    ['رمينا', 'رمي'],
    ['يرمون', 'رمي'],
    ['سعوا', 'سعي'],
    ['سعيت', 'سعي'],
    ['سعينا', 'سعي'],
    ['يسعون', 'سعي'],
]);

it('keeps manual weak-context root analysis source visible in toArray', function (): void {
    $array = dujanaWeakContextAnalyzerFor('يبيعون', 'بيع')
        ->analyze('يبيعون', StemmerModeEnum::Root)
        ->toArray();

    expect($array['root'])->toBe('بيع')
        ->and($array['root_analysis']['source'])->toBeIn(['manual_lexicon', 'database']);
});
