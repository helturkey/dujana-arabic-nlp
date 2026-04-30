<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Candidates\CoreCandidateGenerator;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('does not generate false final kaf suffix candidate for structural kaf', function (string $word, string $expectedCore): void {
    $candidates = (new CoreCandidateGenerator)->generate($word, StemmerModeEnum::Moderate, $word);

    $cores = array_map(
        static fn (CoreCandidate $candidate): string => $candidate->core,
        $candidates,
    );

    expect($cores)->toContain($expectedCore)
        ->and($cores)->not->toContain(mb_substr($expectedCore, 0, -1));
})->with([
    ['واهتلاك', 'اهتلاك'],
    ['كالملاك', 'ملاك'],
]);

it('does not generate false final ya suffix candidate for structural ya', function (string $word, string $expectedCore): void {
    $candidates = (new CoreCandidateGenerator)->generate($word, StemmerModeEnum::Moderate, $word);

    $cores = array_map(
        static fn (CoreCandidate $candidate): string => $candidate->core,
        $candidates,
    );

    expect($cores)->toContain($expectedCore)
        ->and($cores)->not->toContain(mb_substr($expectedCore, 0, -1));
})->with([
    ['المشتهي', 'مشتهي'],
    ['المنتهي', 'منتهي'],
    ['المهتدي', 'مهتدي'],
]);

it('keeps root-final taa when suffix is a pronoun and taa is not feminine taa', function (string $word, string $expectedCore): void {
    $candidates = (new CoreCandidateGenerator)->generate($word, StemmerModeEnum::Moderate, $word);

    $cores = array_map(
        static fn (CoreCandidate $candidate): string => $candidate->core,
        $candidates,
    );

    expect($cores)->toContain($expectedCore);
})->with([
    ['صوته', 'صوت'],
    ['وقته', 'وقت'],
    ['نباتها', 'نبات'],
]);

it('drops likely feminine taa before pronoun suffix only when policy allows it', function (string $word, string $expectedCore): void {
    $candidates = (new CoreCandidateGenerator)->generate($word, StemmerModeEnum::Moderate, $word);

    $cores = array_map(
        static fn (CoreCandidate $candidate): string => $candidate->core,
        $candidates,
    );

    expect($cores)->toContain($expectedCore);
})->with([
    ['مدرسته', 'مدرس'],
    ['رحلتها', 'رحل'],
]);
