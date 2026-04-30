<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('extracts safe shadda-backed passive participle roots without database', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('rule:passive_participle_mf33l')
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();
})->with([
    ['معلّم', 'علم'],
    ['محسّن', 'حسن'],
    ['مكرّم', 'كرم'],
    ['مدرّس', 'درس'],
]);
