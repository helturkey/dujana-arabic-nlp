<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('extracts selected passive participle noun roots without database', function (string $word, string $root, string $source): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe($source);
})->with([
    ['مكتوب', 'كتب', 'rule:passive_participle_mf3ol'],
    ['معلوم', 'علم', 'rule:passive_participle_mf3ol'],
    ['مفهوم', 'فهم', 'rule:passive_participle_mf3ol'],
    ['مكسور', 'كسر', 'rule:passive_participle_mf3ol'],
    ['مفتوح', 'فتح', 'rule:passive_participle_mf3ol'],
]);
