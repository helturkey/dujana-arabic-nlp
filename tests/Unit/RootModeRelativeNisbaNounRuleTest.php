<?php

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

test('it exposes nisba ya candidates conservatively', function (string $word, string $root, string $source): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $matches = collect($analysis->rootAnalysis?->candidates ?? [])
        ->filter(fn ($candidate) => $candidate->source === $source)
        ->map(fn ($candidate) => $candidate->root)
        ->all();

    expect($matches)->toContain($root);
})->with([
    ['مصري', 'مصر', 'rule:relative_nisba_y'],
    ['عربي', 'عرب', 'rule:relative_nisba_y'],
    ['مدني', 'مدن', 'rule:relative_nisba_y'],

    ['مصرية', 'مصر', 'rule:relative_nisba_feminine'],
    ['عربية', 'عرب', 'rule:relative_nisba_feminine'],
    ['مدنية', 'مدن', 'rule:relative_nisba_feminine'],
]);

test('nisba candidates are extracted from original surface', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('مصري', StemmerModeEnum::Root);

    $matches = collect($analysis->rootAnalysis?->candidates ?? [])
        ->filter(fn ($candidate) => $candidate->source === 'rule:relative_nisba_y')
        ->map(fn ($candidate) => $candidate->root)
        ->all();

    expect($matches)->toContain('مصر');
});

test('f3eel adjectives are not treated as nisba by default', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('كريم', StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($analysis->root)->toBe('كرم')
        ->and($analysis->rootAnalysis?->best?->source)->toBe('rule:adjective_f3eel')
        ->and($sources)->not->toContain('rule:relative_nisba_y');
});

test('it exposes extended feminine nisba base candidates conservatively', function (string $word, string $base): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $matches = collect($analysis->rootAnalysis?->candidates ?? [])
        ->filter(fn ($candidate) => $candidate->source === 'rule:relative_nisba_feminine_extended')
        ->map(fn ($candidate) => $candidate->root)
        ->all();

    expect($matches)->toContain($base);
})->with([
    ['عباسية', 'عباس'],
    ['بغدادية', 'بغداد'],
]);

test('it does not extract weak-heavy extended feminine nisba bases yet', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('أموية', StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($sources)->not->toContain('rule:relative_nisba_feminine_extended');
});
