<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Evaluation;

final readonly class RootEvaluateSuiteResult
{
    /**
     * @param  list<array<string,mixed>>  $rows
     */
    public function __construct(
        public string $suitePath,
        public int $total,
        public int $passed,
        public int $failed,
        public array $rows,
    ) {}

    public function passRate(): float
    {
        return $this->total === 0 ? 0.0 : round($this->passed / $this->total, 4);
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'suite_path' => $this->suitePath,
            'total' => $this->total,
            'passed' => $this->passed,
            'failed' => $this->failed,
            'pass_rate' => $this->passRate(),
            'rows' => $this->rows,
        ];
    }
}
