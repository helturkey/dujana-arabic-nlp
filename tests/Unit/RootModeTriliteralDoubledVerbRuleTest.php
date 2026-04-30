<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('extracts doubled augmented triliteral past verb roots without database', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = array_map(
        static fn ($candidate): string => $candidate->source,
        $analysis->rootAnalysis?->candidates ?? [],
    );

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('rule:verb_triliteral_f33l')
        ->and($sources)->toContain('rule:verb_triliteral_f33l')
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();
})->with([
    ['كبّر', 'كبر'],
    ['صغّر', 'صغر'],
    ['علّم', 'علم'],
    ['حسّن', 'حسن'],
]);

it('extracts doubled augmented triliteral present verb roots without database', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('rule:verb_triliteral_yf33l')
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();
})->with([
    ['يكبّر', 'كبر'],
    ['يصغّر', 'صغر'],
    ['يعلّم', 'علم'],
    ['يحسّن', 'حسن'],
]);
