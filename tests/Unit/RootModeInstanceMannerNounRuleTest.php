<?php

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

test('it exposes vocalized f3la instance and manner noun candidates', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $matches = collect($analysis->rootAnalysis?->candidates ?? [])
        ->filter(fn ($candidate) => $candidate->source === 'rule:instance_manner_f3lah')
        ->map(fn ($candidate) => $candidate->root)
        ->all();

    expect($matches)->toContain($root);
})->with([
    ['ضَرْبة', 'ضرب'],
    ['جَلْسة', 'جلس'],
    ['نَظْرة', 'نظر'],
]);

test('it does not extract f3la instance and manner noun without haraka evidence', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('ضربة', StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($sources)->not->toContain('rule:instance_manner_f3lah');
});

test('it exposes vocalized fi3la manner noun candidates', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $matches = collect($analysis->rootAnalysis?->candidates ?? [])
        ->filter(fn ($candidate) => $candidate->source === 'rule:instance_manner_fi3lah')
        ->map(fn ($candidate) => $candidate->root)
        ->all();

    expect($matches)->toContain($root);
})->with([
    ['جِلْسة', 'جلس'],
    ['نِظْرة', 'نظر'],
    ['ضِحْكة', 'ضحك'],
]);

test('it does not extract fi3la manner noun without haraka evidence', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('جلسة', StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($sources)->not->toContain('rule:instance_manner_fi3lah');
});
