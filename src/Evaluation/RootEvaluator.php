<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Evaluation;

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

final readonly class RootEvaluator
{
    public function __construct(private ArabicAnalyzer $analyzer) {}

    /**
     * @param  list<RootEvaluationCase>  $cases
     */
    public function evaluate(array $cases): RootEvaluationReport
    {
        $results = [];

        foreach ($cases as $case) {
            $analysis = $this->analyzer->analyze($case->word, StemmerModeEnum::Root);

            $rootAnalysis = $analysis->rootAnalysis;
            $best = $rootAnalysis?->best;

            $actualRoot = $analysis->root;
            $expectedRoot = $case->expectedRoot;

            $isCorrect = $expectedRoot === null
                ? $actualRoot === null || ! ($rootAnalysis?->reliable() ?? false)
                : $actualRoot === $expectedRoot;

            $results[] = new RootEvaluationResult(
                case: $case,
                actualRoot: $actualRoot,
                source: $best?->source,
                confidence: $best->confidence ?? 0.0,
                isReliable: $rootAnalysis?->reliable() ?? false,
                reliabilityReason: $rootAnalysis?->reason(),
                isCorrect: $isCorrect,
            );
        }

        return new RootEvaluationReport($results);
    }
}
