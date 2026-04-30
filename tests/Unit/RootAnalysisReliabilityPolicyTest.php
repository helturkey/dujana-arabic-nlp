<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Morphology\Root\RootAnalysis;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;

it('is reliable when the best candidate is authoritative', function (): void {
    $candidate = new RootCandidate(
        root: 'خرج',
        confidence: 0.95,
        source: 'rule:active_participle_mstf3l',
        reasons: ['test:authoritative_rule'],
    );

    $analysis = new RootAnalysis(
        word: 'مستخرج',
        best: $candidate,
        candidates: [$candidate],
    );

    expect($analysis->reliable())->toBeTrue()
        ->and($analysis->status())->toBe('reliable')
        ->and($analysis->reason())->toBe('reliable_authoritative_candidate');
});

it('is not reliable when the best candidate is scale even with high confidence', function (): void {
    $candidate = new RootCandidate(
        root: 'كتب',
        confidence: 0.99,
        source: 'scale',
        reasons: ['test:scale'],
    );

    $analysis = new RootAnalysis(
        word: 'كاتب',
        best: $candidate,
        candidates: [$candidate],
    );

    expect($analysis->reliable())->toBeFalse()
        ->and($analysis->status())->toBe('unreliable')
        ->and($analysis->reason())->toBe('best_candidate_not_authoritative');
});

it('is not reliable when the best candidate is fallback core', function (): void {
    $candidate = new RootCandidate(
        root: 'مجهول',
        confidence: 0.10,
        source: 'fallback_core',
        reasons: ['test:fallback'],
    );

    $analysis = new RootAnalysis(
        word: 'مجهول',
        best: $candidate,
        candidates: [$candidate],
    );

    expect($analysis->reliable())->toBeFalse()
        ->and($analysis->status())->toBe('unreliable')
        ->and($analysis->reason())->toBe('best_candidate_not_authoritative');
});

it('is not reliable when the best candidate is database alternative', function (): void {
    $candidate = new RootCandidate(
        root: 'بءر',
        confidence: 0.95,
        source: 'database_alternative',
        reasons: ['test:alternative'],
    );

    $analysis = new RootAnalysis(
        word: 'بار',
        best: $candidate,
        candidates: [$candidate],
    );

    expect($analysis->reliable())->toBeFalse()
        ->and($analysis->status())->toBe('unreliable')
        ->and($analysis->reason())->toBe('best_candidate_not_authoritative');
});

it('is not reliable when there is no best candidate', function (): void {
    $analysis = new RootAnalysis(
        word: 'مجهول',
        best: null,
        candidates: [],
    );

    expect($analysis->reliable())->toBeFalse()
        ->and($analysis->status())->toBe('no_candidate')
        ->and($analysis->reason())->toBe('no_root_candidate');
});

it('exposes root analysis array contract', function (): void {
    $candidate = new RootCandidate(
        root: 'خرج',
        confidence: 0.95,
        source: 'rule:active_participle_mstf3l',
        reasons: ['test:authoritative_rule'],
    );

    $analysis = new RootAnalysis(
        word: 'مستخرج',
        best: $candidate,
        candidates: [$candidate],
    );

    expect($analysis->toArray())->toHaveKeys([
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
        ->and($analysis->toArray()['word'])->toBe('مستخرج')
        ->and($analysis->toArray()['root'])->toBe('خرج')
        ->and($analysis->toArray()['source'])->toBe('rule:active_participle_mstf3l')
        ->and($analysis->toArray()['status'])->toBe('reliable')
        ->and($analysis->toArray()['reliable'])->toBeTrue()
        ->and($analysis->toArray()['candidates_count'])->toBe(1);
});
