<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('documents current hamza-heavy root behavior', function (string $word): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->stem)->toBeString()
        ->and($analysis->rootAnalysis)->not->toBeNull();
})->with([
    'قراءة',
    'مسؤول',
    'سائل',
]);
