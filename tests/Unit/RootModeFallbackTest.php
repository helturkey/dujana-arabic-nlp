<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('falls back safely for unknown unpatterned words', function (): void {
    $analysis = ArabicAnalyzer::make()
        ->analyze('ززززز', StemmerModeEnum::Root);

    expect($analysis->stem)->toBe('ززززز')
        ->and($analysis->root)->toBe('ززززز')
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('fallback_core')
        ->and($analysis->rootAnalysis->best->confidence)->toBe(0.10)
        ->and($analysis->rootAnalysis->best->isAuthoritative())->toBeFalse()
        ->and($analysis->rootAnalysis->reliable())->toBeFalse();
});

it('exposes reliability flags in root analysis array', function (): void {
    $array = ArabicAnalyzer::make()
        ->analyze('ززززز', StemmerModeEnum::Root)
        ->toArray();

    expect($array['root_analysis']['reliable'])->toBeFalse()
        ->and($array['root_analysis']['candidates'][0]['authoritative'])->toBeFalse()
        ->and($array['root_analysis']['source'])->toBe('fallback_core')
        ->and($array['root_analysis']['candidates'][0]['reasons'])->toContain('not_authoritative_root');
});

it('marks explicit high-confidence roots as reliable', function (): void {
    $analysis = ArabicAnalyzer::make()
        ->analyze('أكبر', StemmerModeEnum::Root);

    expect($analysis->root)->toBe('كبر')
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->reliable())->toBeTrue()
        ->and($analysis->rootAnalysis->best->isAuthoritative())->toBeTrue();
});

it('exposes root analysis reliability reason', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('ززززز', StemmerModeEnum::Root);

    $array = $analysis->toArray();

    expect($array['root_analysis']['reliable'])->toBeFalse()
        ->and($array['root_analysis']['reason'])->toBe('best_candidate_not_authoritative')
        ->and($array['root_analysis']['source'])->toBe('fallback_core')
        ->and($array['root_analysis']['candidates'][0]['authoritative'])->toBeFalse();
});
