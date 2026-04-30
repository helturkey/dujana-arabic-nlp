<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('does not produce reliable wrong roots for lexical or weak no-db forms', function (string $word, string $badSource): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = array_map(
        static fn ($candidate): string => $candidate->source,
        $analysis->rootAnalysis?->candidates ?? [],
    );

    expect($sources)->not->toContain($badSource);
})->with([
    ['مصرية', 'rule:masdar_quadriliteral_f3lla'],
    ['مدرسة', 'rule:masdar_quadriliteral_f3lla'],
    ['أندلسي', 'rule:verb_inf3la'],
    ['يدعون', 'rule:verb_quadriliteral_present'],
    ['يرمون', 'rule:verb_quadriliteral_present'],
]);

it('keeps safe quadriliteral falala masdars', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis?->best?->source)->toBe('rule:masdar_quadriliteral_f3lla');
})->with([
    ['زلزلة', 'زلزل'],
    ['وسوسة', 'وسوس'],
]);

it('keeps safe infaala verbs', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis?->best?->source)->toBe('rule:verb_inf3la');
})->with([
    ['انكسر', 'كسر'],
    ['انفتح', 'فتح'],
    ['انقطع', 'قطع'],
]);

it('does not classify risky iftaala-like forms as the safe assimilated iftaala rule', function (string $word): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = array_map(
        static fn ($candidate): string => $candidate->source,
        $analysis->rootAnalysis?->candidates ?? [],
    );

    expect($sources)
        ->not->toContain('rule:verb_ift3la_assimilated')
        ->not->toContain('rule:verb_yift3l_assimilated');
})->with([
    'اتخذ',
    'يتخذ',
    'ادّعى',
    'يدّعي',
    'اتقى',
    'يتقي',
]);

it('keeps safe assimilated iftaala forms reliable without database', function (string $word, string $root, string $source): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe($source)
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();
})->with([
    ['اصطبر', 'صبر', 'rule:verb_ift3la_assimilated'],
    ['اضطرب', 'ضرب', 'rule:verb_ift3la_assimilated'],
    ['ازدحم', 'زحم', 'rule:verb_ift3la_assimilated'],
    ['اتصل', 'وصل', 'rule:verb_ift3la_assimilated'],
    ['اتصف', 'وصف', 'rule:verb_ift3la_assimilated'],

    ['يصطبر', 'صبر', 'rule:verb_yift3l_assimilated'],
    ['يضطرب', 'ضرب', 'rule:verb_yift3l_assimilated'],
    ['يزدحم', 'زحم', 'rule:verb_yift3l_assimilated'],
    ['يتصل', 'وصل', 'rule:verb_yift3l_assimilated'],
    ['يتصف', 'وصف', 'rule:verb_yift3l_assimilated'],
]);
