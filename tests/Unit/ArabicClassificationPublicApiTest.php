<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\ArabicTokenTypeEnum;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('exposes classification for normal Arabic words', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('كتاب', StemmerModeEnum::Moderate);

    expect($analysis->classification)->not->toBeNull()
        ->and($analysis->classification->type)->toBe(ArabicTokenTypeEnum::Word)
        ->and($analysis->classification->protected)->toBeFalse()
        ->and($analysis->classification->reason)->toBeNull()
        ->and($analysis->toArray()['classification'])->toMatchArray([
            'token' => 'كتاب',
            'type' => 'word',
            'protected' => false,
            'reason' => null,
        ]);
});

it('protects function words before stemming', function (string $word, ArabicTokenTypeEnum $type, string $reason): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Moderate);

    expect($analysis->protected)->toBeTrue()
        ->and($analysis->protectionReason)->toBe($reason)
        ->and($analysis->classification)->not->toBeNull()
        ->and($analysis->classification->type)->toBe($type)
        ->and($analysis->classification->protected)->toBeTrue()
        ->and($analysis->classification->reason)->toBe($reason)
        ->and($analysis->stem)->toBe($analysis->normalized);
})->with([
    ['من', ArabicTokenTypeEnum::Particle, 'particle'],
    ['في', ArabicTokenTypeEnum::Particle, 'particle'],
    ['و', ArabicTokenTypeEnum::SingleLetterParticle, 'single_letter_particle'],
    ['ال', ArabicTokenTypeEnum::DefiniteArticle, 'definite_article'],
]);

it('allows short unknown tokens to reach root mode for manual database lookup', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('مد', StemmerModeEnum::Root);

    expect($analysis->classification)->not->toBeNull()
        ->and($analysis->classification->type)->toBe(ArabicTokenTypeEnum::ShortUnknown)
        ->and($analysis->classification->protected)->toBeTrue();

    /*
     * No DB/manual may still be unreliable, but the analyzer should not stop at
     * classification protection in root mode.
     */
    expect($analysis->rootAnalysis)->not->toBeNull();
});

it('protects particles even in root mode', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('من', StemmerModeEnum::Root);

    expect($analysis->protected)->toBeTrue()
        ->and($analysis->protectionReason)->toBe('particle')
        ->and($analysis->rootAnalysis)->toBeNull()
        ->and($analysis->classification?->type)->toBe(ArabicTokenTypeEnum::Particle);
});
