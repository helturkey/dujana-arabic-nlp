<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicStemmer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('keeps ArabicStemmer wrapper aligned with ArabicAnalyzer', function (): void {
    $stemmer = ArabicStemmer::make();

    expect($stemmer->stem('وكتابهم'))->toBe('كتاب')
        ->and($stemmer->stem('أكبر', StemmerModeEnum::Root))->toBe('كبر')
        ->and($stemmer->stemMultiple(['والكتاب', 'كتابه']))->toBe(['كتاب', 'كتاب'])
        ->and($stemmer->stemSentence('والكتاب، كتابه.'))->toBe(['كتاب', 'كتاب'])
        ->and($stemmer->stemSentenceAsString('والكتاب، كتابه.'))->toBe('كتاب كتاب');
});

it('returns arrayable root analysis from wrapper analysis', function (): void {
    $analysis = ArabicStemmer::make()->analyze('أكبر', StemmerModeEnum::Root)->toArray();

    expect($analysis['root'])->toBe('كبر')
        ->and($analysis['root_analysis'])->toBeArray()
        ->and($analysis['root_analysis']['source'])->not->toBeIn(['manual_lexicon', 'database']);
});
