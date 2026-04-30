<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\ArabicTokenTypeEnum;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('attaches token classification to analysis results', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('كتاب', StemmerModeEnum::Root);

    expect($analysis->classification)->not->toBeNull()
        ->and($analysis->classification->type)->toBe(ArabicTokenTypeEnum::Word)
        ->and($analysis->classification->protected)->toBeFalse()
        ->and($analysis->toArray())->toHaveKey('classification')
        ->and($analysis->toArray()['classification']['type'])->toBe('word');
});

it('classifies function words in analyzer output', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('من', StemmerModeEnum::Moderate);

    expect($analysis->classification)->not->toBeNull()
        ->and($analysis->classification->type)->toBe(ArabicTokenTypeEnum::Particle)
        ->and($analysis->classification->protected)->toBeTrue()
        ->and($analysis->classification->reason)->toBe('particle');
});

it('keeps short unknown classification visible in root mode', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('مد', StemmerModeEnum::Root);

    expect($analysis->classification)->not->toBeNull()
        ->and($analysis->classification->type)->toBe(ArabicTokenTypeEnum::ShortUnknown)
        ->and($analysis->classification->protected)->toBeTrue();
});
