<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('extracts triliteral mufaala masdar roots without database', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = array_map(
        static fn ($candidate): string => $candidate->source,
        $analysis->rootAnalysis?->candidates ?? [],
    );

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('rule:masdar_triliteral_mfaa3la')
        ->and($sources)->toContain('rule:masdar_triliteral_mfaa3la')
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();
})->with([
    ['مقاتلة', 'قتل'],
    ['مشاركة', 'شرك'],
    ['مباعدة', 'بعد'],
]);
