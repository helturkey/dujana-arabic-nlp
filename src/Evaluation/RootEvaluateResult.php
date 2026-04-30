<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Evaluation;

use Dujana\ArabicNlp\ArabicAnalysis;

final readonly class RootEvaluateResult
{
    public function __construct(
        public string $word,
        public string $expectedRoot,
        public ArabicAnalysis $analysis,
        public bool $passed,
    ) {}

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'word' => $this->word,
            'expected_root' => $this->expectedRoot,
            'actual_root' => $this->analysis->root,
            'passed' => $this->passed,
            'source' => $this->analysis->rootAnalysis?->best?->source,
            'confidence' => $this->analysis->confidence,
            'analysis' => $this->analysis->toArray(),
        ];
    }
}
