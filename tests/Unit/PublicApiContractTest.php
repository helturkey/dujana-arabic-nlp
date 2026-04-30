<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\ArabicStemmer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('keeps the analyzer public api stable', function (): void {
    $analyzer = ArabicAnalyzer::make();

    expect($analyzer->normalize('أحمد'))->toBe('احمد')
        ->and($analyzer->stem('والكتاب'))->toBe('كتاب')
        ->and($analyzer->stem('أكبر', StemmerModeEnum::Root))->toBe('كبر')
        ->and($analyzer->stemMultiple(['والكتاب', 'كتابه']))->toBe(['كتاب', 'كتاب']);

    $analysis = $analyzer->analyze('أكبر', StemmerModeEnum::Root);

    expect($analysis->original)->toBe('أكبر')
        ->and($analysis->normalized)->toBe('اكبر')
        ->and($analysis->stem)->toBe('كبر')
        ->and($analysis->root)->toBe('كبر')
        ->and($analysis->mode)->toBe(StemmerModeEnum::Root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();

    $array = $analysis->toArray();

    expect($array)->and($array['root_analysis'])->toHaveKeys([
        'word',
        'root',
        'source',
        'confidence',
        'status',
        'reliable',
        'reason',
        'candidates_count',
        'candidates',
    ])
        ->and($array['root_analysis'])->not->toHaveKey('best')
        ->and($array['root_analysis'])->not->toHaveKey('is_reliable')
        ->and($array['root_analysis'])->not->toHaveKey('reliability_reason');
});

it('keeps the stemmer wrapper public api stable', function (): void {
    $stemmer = ArabicStemmer::make();

    expect($stemmer->stem('والكتاب'))->toBe('كتاب')
        ->and($stemmer->stem('أكبر', StemmerModeEnum::Root))->toBe('كبر')
        ->and($stemmer->stemMultiple(['والكتاب', 'كتابه']))->toBe(['كتاب', 'كتاب'])
        ->and($stemmer->stemSentence('والكتاب، كتابه.'))->toBe(['كتاب', 'كتاب'])
        ->and($stemmer->stemSentenceAsString('والكتاب، كتابه.'))->toBe('كتاب كتاب');
});
