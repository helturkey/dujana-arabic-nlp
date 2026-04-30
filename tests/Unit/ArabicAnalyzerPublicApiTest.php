<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('normalizes tokenizes stems and analyzes from analyzer core', function (): void {
    $analyzer = ArabicAnalyzer::make();

    expect($analyzer->normalize('أحمد'))->toBe('احمد')
        ->and($analyzer->tokenize('والكتاب، كتابه.'))->toBe(['والكتاب', 'كتابه'])
        ->and($analyzer->stem('والكتاب'))->toBe('كتاب')
        ->and($analyzer->stem('أكبر', StemmerModeEnum::Root))->toBe('كبر');
});

it('keeps stable analysis array keys', function (): void {
    $array = ArabicAnalyzer::make()
        ->analyze('أكبر', StemmerModeEnum::Root)
        ->toArray();

    expect(array_keys($array))->toBe([
        'original',
        'normalized',
        'stem',
        'root',
        'mode',
        'protected',
        'protection_reason',
        'proclitics',
        'enclitics',
        'word_kind',
        'root_analysis',
        'confidence',
        'classification',
        'trace',
    ]);
});
