<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('exposes root analysis candidates in root mode', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('أكبر', StemmerModeEnum::Root);

    expect($analysis->root)->toBe('كبر')
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->root)->toBe('كبر')
        ->and($analysis->rootAnalysis->best->source)->not->toBeIn(['manual_lexicon', 'database'])
        ->and($analysis->rootAnalysis->candidates)->not->toBeEmpty();

    $array = $analysis->toArray();

    expect($array)->toHaveKey('root_analysis')
        ->and($array['root_analysis'])->toBeArray()
        ->and($array['root_analysis']['root'])->toBe('كبر')
        ->and($array['root_analysis'])->toHaveKeys([
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
        ->and($array['root_analysis']['candidates'])->not->toBeEmpty()
        ->and($array['root_analysis']['candidates'][0])->toHaveKeys([
            'root',
            'confidence',
            'source',
            'authoritative',
            'reasons',
        ]);
});

it('does not expose root analysis in moderate mode', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('والكتاب', StemmerModeEnum::Moderate);

    expect($analysis->root)->toBeNull()
        ->and($analysis->rootAnalysis)->toBeNull()
        ->and($analysis->toArray()['root_analysis'])->toBeNull();
});
