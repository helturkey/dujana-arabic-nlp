<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('exposes a stable PHP-first analysis object contract', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('مستخرج', StemmerModeEnum::Root);

    expect($analysis)->toHaveProperties([
        'original',
        'normalized',
        'stem',
        'root',
        'protected',
        'protectionReason',
        'rootAnalysis',
    ]);

    expect($analysis->original)->toBe('مستخرج')
        ->and($analysis->normalized)->toBeString()
        ->and($analysis->stem)->toBeString()
        ->and($analysis->root)->toBe('خرج')
        ->and($analysis->protected)->toBeBool()
        ->and($analysis->rootAnalysis)->not->toBeNull();
});

it('exposes a stable root analysis contract', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('مستخرج', StemmerModeEnum::Root);

    expect($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis)->toHaveProperties([
            'best',
            'candidates',
        ]);

    expect($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->toHaveProperties([
            'root',
            'confidence',
            'source',
            'reasons',
        ]);

    expect($analysis->rootAnalysis->best->root)->toBe('خرج')
        ->and($analysis->rootAnalysis->best->source)->toBe('rule:active_participle_mstf3l')
        ->and($analysis->rootAnalysis->best->confidence)->toBeFloat()
        ->and($analysis->rootAnalysis->best->reasons)->toBeArray()
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();
});

it('exposes root candidate arrays with the public keys used by consumers', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('مستخرج', StemmerModeEnum::Root);

    $candidate = $analysis->rootAnalysis?->best?->toArray();

    expect($candidate)->toBeArray()
        ->and($candidate)->toHaveKeys([
            'root',
            'source',
            'confidence',
            'authoritative',
            'reasons',
        ])
        ->and($candidate['root'])->toBe('خرج')
        ->and($candidate['source'])->toBe('rule:active_participle_mstf3l')
        ->and($candidate['authoritative'])->toBeTrue();
});

it('keeps analyzer output root-analysis oriented without scale output', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('استخرج', StemmerModeEnum::Root);

    $array = $analysis->toArray();

    expect($array)
        ->not->toHaveKeys([
            'scale',
            'pattern',
            'verb_pattern',
        ])
        ->and($array)->toHaveKeys([
            'original',
            'normalized',
            'stem',
            'mode',
            'word_kind',
            'root',
            'root_analysis',
            'classification',
        ])
        ->and($array['root_analysis'])->toHaveKeys([
            'root',
            'source',
            'confidence',
            'reliable',
            'candidates_count',
        ]);
});
