<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('extracts selected active participle noun roots without database', function (string $word, string $root, string $source): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe($source);
})->with([
    ['كاتب', 'كتب', 'rule:active_participle_faa3l'],
    ['عالم', 'علم', 'rule:active_participle_faa3l'],
    ['فاتح', 'فتح', 'rule:active_participle_faa3l'],

    ['مستخرج', 'خرج', 'rule:active_participle_mstf3l'],
    ['مستعمل', 'عمل', 'rule:active_participle_mstf3l'],
    ['مستقبل', 'قبل', 'rule:active_participle_mstf3l'],

    ['متعلم', 'علم', 'rule:active_participle_mtf3l'],
    ['متكبر', 'كبر', 'rule:active_participle_mtf3l'],
    ['متحسن', 'حسن', 'rule:active_participle_mtf3l'],

    ['متقاتل', 'قتل', 'rule:active_participle_mtfaa3l'],
    ['متشارك', 'شرك', 'rule:active_participle_mtfaa3l'],
    ['متباعد', 'بعد', 'rule:active_participle_mtfaa3l'],
]);

test('it exposes mf3l active participle candidate without outranking place time', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($analysis->root)->toBe($root)
        ->and($sources)->toContain('rule:active_participle_mf3l');
})->with([
    ['مكرم', 'كرم'],
    ['مخرج', 'خرج'],
    ['مدخل', 'دخل'],
    ['محسن', 'حسن'],
]);
