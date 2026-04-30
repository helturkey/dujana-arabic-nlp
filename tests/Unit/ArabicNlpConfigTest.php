<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('creates config from laravel-style package array', function (): void {
    $config = ArabicNlpConfig::fromArray([
        'default_mode' => 'root',
        'min_word_length' => 2,
        'min_confidence' => 1.10,
        'fallback_to_normalized_on_low_confidence' => false,
        'protect_stop_words' => false,
        'protect_proper_names' => false,
        'protect_non_stemmable' => false,
        'expose_trace' => true,
        'lexicon' => [
            'stop_words' => 'resources/vendor/dujana-arabic-nlp/stopwords.txt',
            'proper_names' => 'resources/vendor/dujana-arabic-nlp/proper-names.txt',
            'non_stemmable' => 'resources/vendor/dujana-arabic-nlp/non-stemmable.txt',
        ],
        'lexicon_database' => 'storage/app/dujana/dujana-lexicon.sqlite',
    ]);

    expect($config->defaultMode)->toBe(StemmerModeEnum::Root)
        ->and($config->minWordLength)->toBe(2)
        ->and($config->minConfidence)->toBe(1.10)
        ->and($config->fallbackToNormalizedOnLowConfidence)->toBeFalse()
        ->and($config->protectStopWords)->toBeFalse()
        ->and($config->protectProperNames)->toBeFalse()
        ->and($config->protectNonStemmable)->toBeFalse()
        ->and($config->exposeTrace)->toBeTrue()
        ->and($config->stopWordsPath)->toBe('resources/vendor/dujana-arabic-nlp/stopwords.txt')
        ->and($config->properNamesPath)->toBe('resources/vendor/dujana-arabic-nlp/proper-names.txt')
        ->and($config->nonStemmablePath)->toBe('resources/vendor/dujana-arabic-nlp/non-stemmable.txt')
        ->and($config->lexiconDatabasePath)->toBe('storage/app/dujana/dujana-lexicon.sqlite');
});

it('uses safe defaults for missing config values', function (): void {
    $config = ArabicNlpConfig::fromArray([]);

    expect($config->defaultMode)->toBe(StemmerModeEnum::Moderate)
        ->and($config->minWordLength)->toBe(3)
        ->and($config->minConfidence)->toBe(1.25)
        ->and($config->fallbackToNormalizedOnLowConfidence)->toBeTrue()
        ->and($config->protectStopWords)->toBeTrue()
        ->and($config->protectProperNames)->toBeTrue()
        ->and($config->protectNonStemmable)->toBeTrue()
        ->and($config->exposeTrace)->toBeFalse()
        ->and($config->stopWordsPath)->toBeNull()
        ->and($config->properNamesPath)->toBeNull()
        ->and($config->nonStemmablePath)->toBeNull()
        ->and($config->lexiconDatabasePath)->toBeNull();
});

it('falls back to moderate mode when default mode is invalid', function (): void {
    $config = ArabicNlpConfig::fromArray([
        'default_mode' => 'invalid-mode',
    ]);

    expect($config->defaultMode)->toBe(StemmerModeEnum::Moderate);
});

it('never allows min word length below one', function (): void {
    $config = ArabicNlpConfig::fromArray([
        'min_word_length' => 0,
    ]);

    expect($config->minWordLength)->toBe(1);
});
