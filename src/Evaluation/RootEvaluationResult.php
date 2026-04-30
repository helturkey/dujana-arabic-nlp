<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Evaluation;

final readonly class RootEvaluationResult
{
    public function __construct(
        public RootEvaluationCase $case,
        public ?string $actualRoot,
        public ?string $source,
        public float $confidence,
        public bool $isReliable,
        public bool $isCorrect,
        public ?string $reliabilityReason = null,
    ) {}

    public function status(): string
    {
        return match (true) {
            $this->isCorrect && $this->isReliable => 'correct_reliable',
            $this->isCorrect && ! $this->isReliable => 'correct_unreliable',
            ! $this->isCorrect && $this->isReliable => 'wrong_reliable',
            default => 'wrong_unreliable',
        };
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'word' => $this->case->word,
            'expected_root' => $this->case->expectedRoot,
            'actual_root' => $this->actualRoot,
            'category' => $this->case->category,
            'source' => $this->source,
            'confidence' => $this->confidence,
            'is_reliable' => $this->isReliable,
            'reliability_reason' => $this->reliabilityReason,
            'is_correct' => $this->isCorrect,
            'status' => $this->status(),
            'note' => $this->case->note,
        ];
    }
}
