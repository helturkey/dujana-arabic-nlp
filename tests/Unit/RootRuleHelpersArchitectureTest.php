<?php

declare(strict_types=1);
use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Morphology\Root\Rules\Concerns\RootRuleHelpers;

it('provides shared root rule helpers as a PHP-first trait', function (): void {
    expect(trait_exists(RootRuleHelpers::class))->toBeTrue();
});

it('keeps passive participle rule using shared helpers without changing behavior', function (): void {
    $analysis = ArabicAnalyzer::make()
        ->analyze('معلّم', StemmerModeEnum::Root);

    expect($analysis->root)->toBe('علم')
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('rule:passive_participle_mf33l');
});
