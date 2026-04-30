<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Evaluation\RootEvaluationCase;
use Dujana\ArabicNlp\Evaluation\RootEvaluator;

it('evaluates root cases and summarizes danger rate', function (): void {
    $report = (new RootEvaluator(ArabicAnalyzer::make()))->evaluate([
        new RootEvaluationCase('أكبر', 'كبر', 'comparative'),
        new RootEvaluationCase('ززززز', null, 'fallback'),
    ]);

    $summary = $report->summary();

    expect($summary['total'])->toBe(2)
        ->and($summary['correct'])->toBe(2)
        ->and($summary['wrong_reliable'])->toBe(0)
        ->and($summary['danger_rate'])->toBe(0.0)
        ->and($summary['by_category'])->toHaveKeys(['comparative', 'fallback']);
});

it('includes reliability reason in evaluation rows', function (): void {
    $report = (new RootEvaluator(ArabicAnalyzer::make()))->evaluate([
        new RootEvaluationCase('ززززز', null, 'fallback'),
    ]);

    $rows = $report->rows();

    expect($rows[0])->toHaveKey('reliability_reason')
        ->and($rows[0]['reliability_reason'])->toBe('best_candidate_not_authoritative');
});
