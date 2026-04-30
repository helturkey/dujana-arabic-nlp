<?php

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

test('it exposes vocalized fa3alaan action and state noun candidates', function (string $word, string $root, string $source): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $matches = collect($analysis->rootAnalysis?->candidates ?? [])
        ->filter(fn ($candidate) => $candidate->source === $source)
        ->map(fn ($candidate) => $candidate->root)
        ->all();

    expect($matches)->toContain($root);
})->with([
    ['نَقَصان', 'نقص', 'rule:action_state_fa3alaan'],
    ['رَجَفان', 'رجف', 'rule:action_state_fa3alaan'],
    ['خَفَقان', 'خفق', 'rule:action_state_fa3alaan'],

    ['غَلَيان', 'غلي', 'rule:action_state_fa3alaan_weak'],
    ['جَرَيان', 'جري', 'rule:action_state_fa3alaan_weak'],
    ['دَوَران', 'دور', 'rule:action_state_fa3alaan_weak'],
]);

test('it does not extract fa3alaan action state noun without haraka evidence', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('نقصان', StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($sources)->not->toContain('rule:action_state_fa3alaan');
});

test('it exposes vocalized fu3laan action and state noun candidates', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $matches = collect($analysis->rootAnalysis?->candidates ?? [])
        ->filter(fn ($candidate) => $candidate->source === 'rule:action_state_fu3laan')
        ->map(fn ($candidate) => $candidate->root)
        ->all();

    expect($matches)->toContain($root);
})->with([
    ['غُفْران', 'غفر'],
    ['خُسْران', 'خسر'],
    ['رُجْحان', 'رجح'],
    ['نُقْصان', 'نقص'],
]);

test('it does not extract fu3laan action state noun without haraka evidence', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('غفران', StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($sources)->not->toContain('rule:action_state_fu3laan');
});

test('it exposes vocalized fi3laan action and state noun candidates', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $matches = collect($analysis->rootAnalysis?->candidates ?? [])
        ->filter(fn ($candidate) => $candidate->source === 'rule:action_state_fi3laan')
        ->map(fn ($candidate) => $candidate->root)
        ->all();

    expect($matches)->toContain($root);
})->with([
    ['حِرْمان', 'حرم'],
    ['وِجْدان', 'وجد'],
    ['نِسْيان', 'نسي'],
]);

test('it does not extract fi3laan action state noun without haraka evidence', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('حرمان', StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($sources)->not->toContain('rule:action_state_fi3laan');
});

test('it exposes vocalized fu3l action and state noun candidates', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $matches = collect($analysis->rootAnalysis?->candidates ?? [])
        ->filter(fn ($candidate) => $candidate->source === 'rule:action_state_fu3l')
        ->map(fn ($candidate) => $candidate->root)
        ->all();

    expect($matches)->toContain($root);
})->with([
    ['جُرْحٌ', 'جرح'],
    ['حُزْنٌ', 'حزن'],
    ['شُكْرٌ', 'شكر'],
    ['كُفْرٌ', 'كفر'],
]);

test('it does not extract fu3l action state noun without haraka evidence', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('جرح', StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($sources)->not->toContain('rule:action_state_fu3l');
});
