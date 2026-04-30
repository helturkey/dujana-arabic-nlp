<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

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
