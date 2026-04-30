<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('extracts geminated bare triliteral roots only when shadda is visible', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = array_map(
        static fn ($candidate): string => $candidate->source,
        $analysis->rootAnalysis?->candidates ?? [],
    );

    expect($analysis->root)->toBe($root)
        ->and($sources)->toContain('rule:verb_triliteral_f3ll')
        ->and($analysis->rootAnalysis?->reliable())->toBeTrue();
})->with([
    ['مدّ', 'مدد'],
    ['شدّ', 'شدد'],
    ['ردّ', 'ردد'],
    ['عدّ', 'عدد'],
    ['فرّ', 'فرر'],
    ['مرّ', 'مرر'],
    ['حلّ', 'حلل'],
    ['ضمّ', 'ضمم'],
    ['سرّ', 'سرر'],
]);

it('extracts geminated present triliteral roots only when shadda is visible', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = array_map(
        static fn ($candidate): string => $candidate->source,
        $analysis->rootAnalysis?->candidates ?? [],
    );

    expect($analysis->root)->toBe($root)
        ->and($sources)->toContain('rule:verb_triliteral_yf3ll')
        ->and($analysis->rootAnalysis?->reliable())->toBeTrue();
})->with([
    ['يمدّ', 'مدد'],
    ['يشدّ', 'شدد'],
    ['يردّ', 'ردد'],
    ['يفرّ', 'فرر'],
    ['يمرّ', 'مرر'],
    ['يحلّ', 'حلل'],
    ['يضمّ', 'ضمم'],
]);
