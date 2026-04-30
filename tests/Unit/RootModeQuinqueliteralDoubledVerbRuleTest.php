<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('extracts quinqueliteral tafaala doubled past roots without database', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = array_map(
        static fn ($candidate): string => $candidate->source,
        $analysis->rootAnalysis?->candidates ?? [],
    );

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('rule:verb_tf33la')
        ->and($sources)->toContain('rule:verb_tf33la')
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();
})->with([
    ['تعلّم', 'علم'],
    ['تكبّر', 'كبر'],
    ['تحسّن', 'حسن'],
    ['تصغّر', 'صغر'],
]);

it('extracts quinqueliteral tafaala doubled present roots without database', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = array_map(
        static fn ($candidate): string => $candidate->source,
        $analysis->rootAnalysis?->candidates ?? [],
    );

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('rule:verb_ytf33l')
        ->and($sources)->toContain('rule:verb_ytf33l')
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();
})->with([
    ['يتعلّم', 'علم'],
    ['يتكبّر', 'كبر'],
    ['يتحسّن', 'حسن'],
    ['يتصغّر', 'صغر'],
]);

it('extracts quinqueliteral tafaaala past verb roots without database', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = array_map(
        static fn ($candidate): string => $candidate->source,
        $analysis->rootAnalysis?->candidates ?? [],
    );

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($sources)->toContain('rule:verb_tfa3la')
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();
})->with([
    ['تقاتل', 'قتل'],
    ['تشارك', 'شرك'],
    ['تباعد', 'بعد'],
    ['تخاصم', 'خصم'],
]);

it('extracts quinqueliteral tafaaala present verb roots without database', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = array_map(
        static fn ($candidate): string => $candidate->source,
        $analysis->rootAnalysis?->candidates ?? [],
    );

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($sources)->toContain('rule:verb_ytfa3l')
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();
})->with([
    ['يتقاتل', 'قتل'],
    ['يتشارك', 'شرك'],
    ['يتباعد', 'بعد'],
    ['يتخاصم', 'خصم'],
]);

it('does not treat hamza-heavy forms as safe tafaaala verbs', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('تساؤل', StemmerModeEnum::Root);

    $sources = array_map(
        static fn ($candidate): string => $candidate->source,
        $analysis->rootAnalysis?->candidates ?? [],
    );

    expect($sources)
        ->not->toContain('rule:verb_tfaa3la')
        ->not->toContain('rule:verb_ytfaa3l');
});

it('extracts safe assimilated iftaala past verb roots without database', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('rule:verb_ift3la_assimilated')
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();
})->with([
    ['اصطبر', 'صبر'],
    ['اصطدم', 'صدم'],

    ['اضطرب', 'ضرب'],
    ['اضطرم', 'ضرم'],

    ['ازدحم', 'زحم'],

    ['اتصل', 'وصل'],
    ['اتصف', 'وصف'],
]);

it('extracts safe assimilated iftaala present verb roots without database', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('rule:verb_yift3l_assimilated')
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();
})->with([
    ['يصطبر', 'صبر'],
    ['يصطدم', 'صدم'],

    ['يضطرب', 'ضرب'],
    ['يضطرم', 'ضرم'],

    ['يزدحم', 'زحم'],

    ['يتصل', 'وصل'],
    ['يتصف', 'وصف'],
]);

it('does not make risky assimilated iftaala-like weak or irregular forms reliable without database', function (string $word): void {
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
