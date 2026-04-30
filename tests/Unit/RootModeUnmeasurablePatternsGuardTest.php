<?php

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

test('it does not guess ajwaf weak past roots without database evidence', function (string $word): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($sources)
        ->not->toContain('rule:verb_triliteral_ajwaf_past')
        ->not->toContain('rule:verb_triliteral_weak_past');
})->with([
    'قال',
    'باع',
    'قام',
    'سار',
    'نام',
]);

test('it does not guess naqis weak past roots without database evidence', function (string $word): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($sources)
        ->not->toContain('rule:verb_triliteral_naqis_past')
        ->not->toContain('rule:verb_triliteral_weak_final_past');
})->with([
    'دعا',
    'غزا',
    'رمى',
    'سعى',
    'بكى',
]);

test('weak ifaala masdar candidates remain non authoritative', function (string $word): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $matches = collect($analysis->rootAnalysis?->candidates ?? [])
        ->filter(fn ($candidate) => $candidate->source === 'rule:masdar_triliteral_ifaala_weak')
        ->all();

    foreach ($matches as $candidate) {
        expect($candidate->reasons)->toContain('not_authoritative_root');
    }
})->with([
    'إقامة',
    'إجابة',
    'إفادة',
    'إطالة',
]);

test('it does not guess hamzated roots through placeholder rules', function (string $word): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($sources)
        ->not->toContain('rule:hamzated_root_guess')
        ->not->toContain('rule:masdar_hamzated_guess')
        ->not->toContain('rule:active_participle_hamzated_guess');
})->with([
    'قراءة',
    'قارئ',
    'سائل',
    'مأكول',
    'مأخوذ',
]);
