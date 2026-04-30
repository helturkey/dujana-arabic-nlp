<?php

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

test('it exposes af3aal broken plural candidates conservatively', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $matches = collect($analysis->rootAnalysis?->candidates ?? [])
        ->filter(fn ($candidate) => $candidate->source === 'rule:broken_plural_af3aal')
        ->map(fn ($candidate) => $candidate->root)
        ->all();

    expect($matches)->toContain($root);
})->with([
    ['أقلام', 'قلم'],
    ['أشعار', 'شعر'],
    ['ألوان', 'لون'],
]);

test('af3aal broken plural does not become authoritative by default', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('أقلام', StemmerModeEnum::Root);

    $match = collect($analysis->rootAnalysis?->candidates ?? [])
        ->first(fn ($candidate) => $candidate->source === 'rule:broken_plural_af3aal');

    expect($match)->not->toBeNull()
        ->and($match->confidence)->toBeLessThan(0.60)
        ->and($match->reasons)->toContain('not_authoritative_root');
});

test('it exposes vocalized fi3aal broken plural candidates', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $matches = collect($analysis->rootAnalysis?->candidates ?? [])
        ->filter(fn ($candidate) => $candidate->source === 'rule:broken_plural_fi3aal')
        ->map(fn ($candidate) => $candidate->root)
        ->all();

    expect($matches)->toContain($root);
})->with([
    ['جِبال', 'جبل'],
    ['رِجال', 'رجل'],
    ['كِلاب', 'كلب'],
]);

test('it exposes vocalized fu3aal broken plural candidates', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $matches = collect($analysis->rootAnalysis?->candidates ?? [])
        ->filter(fn ($candidate) => $candidate->source === 'rule:broken_plural_fu3aal')
        ->map(fn ($candidate) => $candidate->root)
        ->all();

    expect($matches)->toContain($root);
})->with([
    ['رُكاب', 'ركب'],
]);

test('it does not extract fi3aal or fu3aal broken plural without haraka evidence', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('جبال', StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($sources)
        ->not->toContain('rule:broken_plural_fi3aal')
        ->not->toContain('rule:broken_plural_fu3aal');
});

test('it exposes mfaa3il broken plural candidates conservatively', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $matches = collect($analysis->rootAnalysis?->candidates ?? [])
        ->filter(fn ($candidate) => $candidate->source === 'rule:broken_plural_mfaa3il')
        ->map(fn ($candidate) => $candidate->root)
        ->all();

    expect($matches)->toContain($root);
})->with([
    ['مساجد', 'سجد'],
    ['مكاتب', 'كتب'],
    ['مدارس', 'درس'],
]);

test('it exposes mfaa3eel broken plural candidates conservatively', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $matches = collect($analysis->rootAnalysis?->candidates ?? [])
        ->filter(fn ($candidate) => $candidate->source === 'rule:broken_plural_mfaa3eel')
        ->map(fn ($candidate) => $candidate->root)
        ->all();

    expect($matches)->toContain($root);
})->with([
    ['مفاتيح', 'فتح'],
    ['مصابيح', 'صبح'],
]);

test('it exposes fa3aa2il broken plural candidates conservatively', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $matches = collect($analysis->rootAnalysis?->candidates ?? [])
        ->filter(fn ($candidate) => $candidate->source === 'rule:broken_plural_fa3aa2il')
        ->map(fn ($candidate) => $candidate->root)
        ->all();

    expect($matches)->toContain($root);
})->with([
    ['قبائل', 'قبل'],
    ['رسائل', 'رسل'],
    ['عجائب', 'عجب'],
    ['غرائب', 'غرب'],
    ['صحائف', 'صحف'],
]);

test('fa3aa2il broken plural does not become authoritative by default', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('قبائل', StemmerModeEnum::Root);

    $match = collect($analysis->rootAnalysis?->candidates ?? [])
        ->first(fn ($candidate) => $candidate->source === 'rule:broken_plural_fa3aa2il');

    expect($match)->not->toBeNull()
        ->and($match->confidence)->toBeLessThan(0.60)
        ->and($match->reasons)->toContain('not_authoritative_root');
});

test('it exposes vocalized fu3alaa2 broken plural candidates conservatively', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $matches = collect($analysis->rootAnalysis?->candidates ?? [])
        ->filter(fn ($candidate) => $candidate->source === 'rule:broken_plural_fu3alaa2')
        ->map(fn ($candidate) => $candidate->root)
        ->all();

    expect($matches)->toContain($root);
})->with([
    ['عُلَماء', 'علم'],
    ['فُقَراء', 'فقر'],
    ['كُرَماء', 'كرم'],
    ['شُعَراء', 'شعر'],
]);

test('it does not extract fu3alaa2 broken plural without haraka evidence', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('علماء', StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($sources)->not->toContain('rule:broken_plural_fu3alaa2');
});
