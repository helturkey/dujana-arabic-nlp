<?php

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

test('it extracts af3l adjective roots', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($analysis->root)->toBe($root)
        ->and($sources)->toContain('rule:adjective_af3l');
})->with([
    ['أحمر', 'حمر'],
    ['أخضر', 'خضر'],
    ['أصفر', 'صفر'],
    ['أكبر', 'كبر'],
    ['أصغر', 'صغر'],
    ['أفضل', 'فضل'],
    ['أجمل', 'جمل'],
]);

test('it extracts f33aal exaggeration roots when shadda is visible', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($analysis->root)->toBe($root)
        ->and($sources)->toContain('rule:exaggeration_f33aal');
})->with([
    ['غفّار', 'غفر'],
    ['ضرّاب', 'ضرب'],
    ['قتّال', 'قتل'],
    ['علّام', 'علم'],
]);

test('it extracts f3ol exaggeration roots', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($analysis->root)->toBe($root)
        ->and($sources)->toContain('rule:exaggeration_f3ol');
})->with([
    ['صبور', 'صبر'],
    ['شكور', 'شكر'],
    ['غفور', 'غفر'],
    ['حسود', 'حسد'],
]);

test('it extracts f3eel adjective roots', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($analysis->root)->toBe($root)
        ->and($sources)->toContain('rule:adjective_f3eel');
})->with([
    ['كريم', 'كرم'],
    ['عليم', 'علم'],
    ['رحيم', 'رحم'],
    ['جميل', 'جمل'],
    ['كبير', 'كبر'],
    ['صغير', 'صغر'],
]);

test('it extracts f3laa feminine color and defect adjective roots', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($analysis->root)->toBe($root)
        ->and($sources)->toContain('rule:adjective_f3laa');
})->with([
    ['حمراء', 'حمر'],
    ['خضراء', 'خضر'],
    ['صفراء', 'صفر'],
    ['عرجاء', 'عرج'],
]);

test('it extracts vocalized f3l color plural adjective roots', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($analysis->root)->toBe($root)
        ->and($sources)->toContain('rule:adjective_f3l_color_plural');
})->with([
    ['حُمْر', 'حمر'],
    ['خُضْر', 'خضر'],
    ['صُفْر', 'صفر'],
    ['عُرْج', 'عرج'],
]);

test('it does not extract f3l color plural without haraka evidence', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('حمر', StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($sources)->not->toContain('rule:adjective_f3l_color_plural');
});
