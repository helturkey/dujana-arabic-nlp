<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('keeps unknown db-miss fallback unreliable', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('ززززز', StemmerModeEnum::Root);

    expect($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->reliable())->toBeFalse()
        ->and($analysis->rootAnalysis->best->source)->toBe('fallback_core');
});

it('does not expose removed scale candidates for hamzated db-miss forms', function (string $word): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->rootAnalysis)->not->toBeNull();

    $sources = collect($analysis->rootAnalysis->candidates)
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($sources)->not->toContain('scale');
})->with([
    'يبدأ',
    'يأخذ',
]);

it('exposes clear diagnostics for fallback core candidates', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('ززززز', StemmerModeEnum::Root);

    expect($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('fallback_core')
        ->and($analysis->rootAnalysis->best->confidence)->toBe(0.10)
        ->and($analysis->rootAnalysis->best->isAuthoritative())->toBeFalse()
        ->and($analysis->rootAnalysis->best->reasons)->toContain(
            'fallback:no_reliable_root_candidate',
            'fallback:returned_core_as_safe_unreliable_value',
            'fallback:not_authoritative',
        );
});
