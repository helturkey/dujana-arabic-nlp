<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('extracts selected place and time noun roots without database', function (string $word, string $root, string $source): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe($source);
})->with([
    ['مكتب', 'كتب', 'rule:place_time_mf3l'],
    ['ملعب', 'لعب', 'rule:place_time_mf3l'],
    ['مخرج', 'خرج', 'rule:place_time_mf3l'],
    ['مدخل', 'دخل', 'rule:place_time_mf3l'],
    ['مجلس', 'جلس', 'rule:place_time_mf3l'],
]);
