<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('keeps lexical and samaai forms non-reliable without database', function (string $word): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->rootAnalysis?->reliable() ?? false)->toBeFalse();
})->with([
    'قال',
    'باع',
    'أقلام',
    'أسماء',
    'مسؤول',
    'مصري',
    'عباسي',
    'أندلسي',
]);
