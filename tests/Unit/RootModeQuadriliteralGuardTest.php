<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('does not treat common noun-like four-letter forms as bare quadriliteral verbs', function (string $word): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = array_map(
        static fn ($candidate): string => $candidate->source,
        $analysis->rootAnalysis?->candidates ?? [],
    );

    expect($sources)->not->toContain('rule:verb_quadriliteral_f3ll');
})->with([
    'شهور',
    'نجوم',
    'جذور',
    'ملوك',
    'سرية',
]);

it('still exposes safe bare quadriliteral verb candidates', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $candidates = array_map(
        static fn ($candidate): array => $candidate->toArray(),
        $analysis->rootAnalysis?->candidates ?? [],
    );

    $matchingCandidate = collect($candidates)->first(
        fn (array $candidate): bool => $candidate['source'] === 'rule:verb_quadriliteral_f3ll'
            && $candidate['root'] === $root
    );

    expect($matchingCandidate)->not->toBeNull();
})->with([
    ['وسوس', 'وسوس'],
    ['دحرج', 'دحرج'],
    ['زلزل', 'زلزل'],
]);
