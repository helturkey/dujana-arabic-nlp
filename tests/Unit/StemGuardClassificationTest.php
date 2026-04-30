<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Classification\ArabicTokenClassifier;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Guards\StemGuard;
use Dujana\ArabicNlp\Lexicon\ArabicLexicon;
use Dujana\ArabicNlp\Lexicon\WordListLoader;
use Dujana\ArabicNlp\Text\ArabicNormalizer;

function testStemGuard(): StemGuard
{
    $config = new ArabicNlpConfig;
    $normalizer = new ArabicNormalizer;

    return new StemGuard(
        config: $config,
        lexicon: new ArabicLexicon(
            config: $config,
            normalizer: $normalizer,
            loader: new WordListLoader($normalizer),
        ),
    );
}

it('protects classified particles before stemming', function (string $word, string $reason): void {
    $classification = (new ArabicTokenClassifier)->classify($word);

    $guard = testStemGuard()->check(
        original: $word,
        normalized: $word,
        mode: StemmerModeEnum::Moderate,
        classification: $classification,
    );

    expect($guard->protected)->toBeTrue()
        ->and($guard->reason?->value)->toBe($reason);
})->with([
    ['من', 'particle'],
    ['في', 'particle'],
    ['و', 'single_letter_particle'],
    ['ال', 'definite_article'],
]);

it('protects short unknown tokens outside root mode', function (): void {
    $classification = (new ArabicTokenClassifier)->classify('مد');

    $guard = testStemGuard()->check(
        original: 'مد',
        normalized: 'مد',
        mode: StemmerModeEnum::Moderate,
        classification: $classification,
    );

    expect($guard->protected)->toBeTrue()
        ->and($guard->reason?->value)->toBe('short_unknown');
});

it('allows short unknown tokens in root mode for manual database lookup', function (): void {
    $classification = (new ArabicTokenClassifier)->classify('مد');

    $guard = testStemGuard()->check(
        original: 'مد',
        normalized: 'مد',
        mode: StemmerModeEnum::Root,
        classification: $classification,
    );

    expect($guard->protected)->toBeFalse()
        ->and($guard->reason)->toBeNull();
});

it('still protects empty input', function (): void {
    $guard = testStemGuard()->check(
        original: '',
        normalized: '',
        mode: StemmerModeEnum::Moderate,
    );

    expect($guard->protected)->toBeTrue()
        ->and($guard->reason?->value)->toBe('empty');
});
