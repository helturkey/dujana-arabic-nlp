<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('extracts triliteral ifaal masdar roots without database', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = array_map(
        static fn ($candidate): string => $candidate->source,
        $analysis->rootAnalysis?->candidates ?? [],
    );

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('rule:masdar_triliteral_if3al')
        ->and($sources)->toContain('rule:masdar_triliteral_if3al')
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();
})->with([
    ['إكرام', 'كرم'],
    ['اكرام', 'كرم'],
    ['إخراج', 'خرج'],
    ['اخراج', 'خرج'],
    ['إدخال', 'دخل'],
    ['ادخال', 'دخل'],
    ['إحسان', 'حسن'],
    ['احسان', 'حسن'],
    ['إعلان', 'علن'],
    ['اعلان', 'علن'],
]);

test('it exposes weak ifaala masdar waw and ya candidates', function (string $word, array $roots): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $matches = collect($analysis->rootAnalysis?->candidates ?? [])
        ->filter(fn ($candidate) => $candidate->source === 'rule:masdar_triliteral_ifaala_weak')
        ->map(fn ($candidate) => $candidate->root)
        ->all();

    foreach ($roots as $root) {
        expect($matches)->toContain($root);
    }
})->with([
    ['إقامة', ['قوم', 'قيم']],
    ['إجابة', ['جوب', 'جيب']],
    ['إفادة', ['فود', 'فيد']],
    ['إطالة', ['طول', 'طيل']],
]);
