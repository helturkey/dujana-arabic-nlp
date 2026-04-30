<?php

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

test('it extracts af3la augmented triliteral verbs', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('rule:verb_triliteral_af3la');
})->with([
    ['أكرم', 'كرم'],
    ['أخرج', 'خرج'],
    ['أدخل', 'دخل'],
    ['أحسن', 'حسن'],
]);

test('it extracts fa3la augmented triliteral verbs when haraka evidence is visible', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('rule:verb_triliteral_fa3la');
})->with([
    ['قاتَلَ', 'قتل'],
    ['شارَكَ', 'شرك'],
    ['باعَدَ', 'بعد'],
    ['جادَلَ', 'جدل'],
]);

test('it does not promote unvocalized faa3l surfaces to fa3la verb by default', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('قاتل', StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($analysis->root)->toBe('قتل')
        ->and($analysis->rootAnalysis?->best?->source)->toBe('rule:active_participle_faa3l')
        ->and($sources)->not->toContain('rule:verb_triliteral_fa3la');
});

test('vocalized fa3la verbs do not get promoted as active participles', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('قاتَلَ', StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($analysis->root)->toBe('قتل')
        ->and($analysis->rootAnalysis?->best?->source)->toBe('rule:verb_triliteral_fa3la')
        ->and($sources)->not->toContain('rule:active_participle_faa3l');
});

test('unvocalized faa3l surfaces remain active participle by default', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('قاتل', StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($analysis->root)->toBe('قتل')
        ->and($analysis->rootAnalysis?->best?->source)->toBe('rule:active_participle_faa3l')
        ->and($sources)->not->toContain('rule:verb_triliteral_fa3la');
});

test('af3la verbs remain preferred over ambiguous af3l adjective reading', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis?->best?->source)->toBe('rule:verb_triliteral_af3la')
        ->and($sources)->toContain('rule:adjective_af3l');
})->with([
    ['أكرم', 'كرم'],
    ['أخرج', 'خرج'],
    ['أدخل', 'دخل'],
    ['أحسن', 'حسن'],
]);
