<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\RootCandidateRanker;

it('does not treat database alternatives as authoritative', function (): void {
    $candidate = new RootCandidate(
        root: 'سرو|سري',
        confidence: 0.99,
        source: 'database_alternative',
    );

    expect($candidate->isAuthoritative())->toBeFalse();
});

it('keeps root candidates with same root but different sources', function (): void {
    $ranked = (new RootCandidateRanker)->rank([
        new RootCandidate(
            root: 'باب',
            confidence: 0.90,
            source: 'database',
        ),
        new RootCandidate(
            root: 'باب',
            confidence: 0.82,
            source: 'database_alternative',
        ),
    ]);

    expect($ranked)->toHaveCount(2)
        ->and(array_column(array_map(
            static fn (RootCandidate $candidate): array => $candidate->toArray(),
            $ranked,
        ), 'source'))->toContain('database', 'database_alternative');
});

it('uses source priority when confidence is tied', function (): void {
    $ranked = (new RootCandidateRanker)->rank([
        new RootCandidate('كتب', 0.90, 'scale'),
        new RootCandidate('كتب', 0.90, 'database_alternative'),
        new RootCandidate('كتب', 0.90, 'database'),
        new RootCandidate('كتب', 0.90, 'fallback_core'),
    ]);

    expect($ranked)->toHaveCount(4)
        ->and($ranked[0]->source)->toBe('database')
        ->and($ranked[1]->source)->toBe('database_alternative')
        ->and($ranked[2]->source)->toBe('scale')
        ->and($ranked[3]->source)->toBe('fallback_core');
});

it('keeps only the strongest candidate for the same root and source', function (): void {
    $ranked = (new RootCandidateRanker)->rank([
        new RootCandidate('كتب', 0.50, 'database'),
        new RootCandidate('كتب', 0.90, 'database'),
        new RootCandidate('كتب', 0.70, 'database'),
    ]);

    expect($ranked)->toHaveCount(1)
        ->and($ranked[0]->root)->toBe('كتب')
        ->and($ranked[0]->source)->toBe('database')
        ->and($ranked[0]->confidence)->toBe(0.90);
});
