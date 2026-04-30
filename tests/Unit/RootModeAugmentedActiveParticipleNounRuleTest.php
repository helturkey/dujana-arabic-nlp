<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('extracts safe augmented active participle roots without database', function (string $word, string $root, string $source): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe($source)
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();
})->with([
    ['مستخرج', 'خرج', 'rule:active_participle_mstf3l'],
    ['مستعمل', 'عمل', 'rule:active_participle_mstf3l'],
    ['مستقبل', 'قبل', 'rule:active_participle_mstf3l'],

    ['متعلم', 'علم', 'rule:active_participle_mtf3l'],
    ['متكبر', 'كبر', 'rule:active_participle_mtf3l'],
    ['متحسن', 'حسن', 'rule:active_participle_mtf3l'],

    ['متقاتل', 'قتل', 'rule:active_participle_mtfaa3l'],
    ['متشارك', 'شرك', 'rule:active_participle_mtfaa3l'],
    ['متباعد', 'بعد', 'rule:active_participle_mtfaa3l'],

    ['منكسر', 'كسر', 'rule:active_participle_mnf3l'],
    ['منفتح', 'فتح', 'rule:active_participle_mnf3l'],
    ['منقطع', 'قطع', 'rule:active_participle_mnf3l'],

    ['مجتمع', 'جمع', 'rule:active_participle_mft3l'],
    ['مقترب', 'قرب', 'rule:active_participle_mft3l'],
    ['مختلف', 'خلف', 'rule:active_participle_mft3l'],
]);

it('does not classify hamza-heavy active participle-like forms as safe faael', function (string $word): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = array_map(
        static fn ($candidate): string => $candidate->source,
        $analysis->rootAnalysis?->candidates ?? [],
    );

    expect($sources)->not->toContain('rule:active_participle_faa3l');
})->with([
    'مآخذ',
    'قارئ',
    'سائل',
]);
