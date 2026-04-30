<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\RootCandidateRanker;

it('prefers manual lexicon candidates over database and rules', function (): void {
    $best = RootCandidateRanker::best([
        new RootCandidate(
            root: 'كتب',
            confidence: 0.97,
            source: 'rule:verb_triliteral_f3l',
            reasons: ['test:rule'],
        ),
        new RootCandidate(
            root: 'كتب',
            confidence: 0.98,
            source: 'database',
            reasons: ['test:database'],
        ),
        new RootCandidate(
            root: 'كتب',
            confidence: 0.95,
            source: 'manual_lexicon',
            reasons: ['test:manual'],
        ),
    ]);

    expect($best)->not->toBeNull()
        ->and($best->source)->toBe('manual_lexicon');
});

it('prefers database candidates over rule candidates when both exist', function (): void {
    $best = RootCandidateRanker::best([
        new RootCandidate(
            root: 'كتب',
            confidence: 0.99,
            source: 'rule:verb_triliteral_f3l',
            reasons: ['test:rule'],
        ),
        new RootCandidate(
            root: 'كتب',
            confidence: 0.90,
            source: 'database',
            reasons: ['test:database'],
        ),
    ]);

    expect($best)->not->toBeNull()
        ->and($best->source)->toBe('database');
});

it('prefers high-priority rules over scale even if scale has higher confidence', function (): void {
    $best = RootCandidateRanker::best([
        new RootCandidate(
            root: 'كتب',
            confidence: 0.99,
            source: 'scale',
            reasons: ['test:scale'],
        ),
        new RootCandidate(
            root: 'كتب',
            confidence: 0.90,
            source: 'rule:verb_triliteral_f3l',
            reasons: ['test:rule'],
        ),
    ]);

    expect($best)->not->toBeNull()
        ->and($best->source)->toBe('rule:verb_triliteral_f3l');
});

it('keeps scale above fallback core', function (): void {
    $best = RootCandidateRanker::best([
        new RootCandidate(
            root: 'كتب',
            confidence: 0.10,
            source: 'fallback_core',
            reasons: ['test:fallback'],
        ),
        new RootCandidate(
            root: 'كتب',
            confidence: 0.40,
            source: 'scale',
            reasons: ['test:scale'],
        ),
    ]);

    expect($best)->not->toBeNull()
        ->and($best->source)->toBe('scale');
});

it('uses confidence as tie breaker inside the same source family', function (): void {
    $best = RootCandidateRanker::best([
        new RootCandidate(
            root: 'خرج',
            confidence: 0.91,
            source: 'rule:active_participle_mstf3l',
            reasons: ['test:lower'],
        ),
        new RootCandidate(
            root: 'خرج',
            confidence: 0.96,
            source: 'rule:active_participle_mstf3l',
            reasons: ['test:higher'],
        ),
    ]);

    expect($best)->not->toBeNull()
        ->and($best->confidence)->toBe(0.96);
});

it('does not make database alternatives authoritative even when they rank above scale', function (): void {
    $best = RootCandidateRanker::best([
        new RootCandidate(
            root: 'بءر',
            confidence: 0.95,
            source: 'database_alternative',
            reasons: ['test:alternative'],
        ),
        new RootCandidate(
            root: 'بار',
            confidence: 0.55,
            source: 'scale',
            reasons: ['test:scale'],
        ),
    ]);

    expect($best)->not->toBeNull()
        ->and($best->source)->toBe('database_alternative')
        ->and($best->isAuthoritative())->toBeFalse();
});
