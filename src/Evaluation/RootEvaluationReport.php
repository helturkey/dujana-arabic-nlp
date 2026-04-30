<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Evaluation;

final readonly class RootEvaluationReport
{
    /**
     * @param  list<RootEvaluationResult>  $results
     */
    public function __construct(public array $results) {}

    /**
     * @return array<string,mixed>
     */
    public function summary(): array
    {
        $total = count($this->results);

        $correctReliable = $this->countStatus('correct_reliable');
        $correctUnreliable = $this->countStatus('correct_unreliable');
        $wrongReliable = $this->countStatus('wrong_reliable');
        $wrongUnreliable = $this->countStatus('wrong_unreliable');

        $correct = $correctReliable + $correctUnreliable;
        $wrong = $wrongReliable + $wrongUnreliable;

        return [
            'total' => $total,
            'correct' => $correct,
            'wrong' => $wrong,
            'correct_reliable' => $correctReliable,
            'correct_unreliable' => $correctUnreliable,
            'wrong_reliable' => $wrongReliable,
            'wrong_unreliable' => $wrongUnreliable,
            'accuracy' => $total > 0 ? round($correct / $total, 4) : 0.0,
            'danger_rate' => $total > 0 ? round($wrongReliable / $total, 4) : 0.0,
            'by_category' => $this->byCategory(),
        ];
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function rows(): array
    {
        return array_map(
            static fn (RootEvaluationResult $result): array => $result->toArray(),
            $this->results,
        );
    }

    private function countStatus(string $status): int
    {
        return count(array_filter(
            $this->results,
            static fn (RootEvaluationResult $result): bool => $result->status() === $status,
        ));
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function byCategory(): array
    {
        $groups = [];

        foreach ($this->results as $result) {
            $category = $result->case->category ?? 'uncategorized';

            $groups[$category] ??= [
                'total' => 0,
                'correct' => 0,
                'wrong' => 0,
                'wrong_reliable' => 0,
            ];

            $groups[$category]['total']++;

            if ($result->isCorrect) {
                $groups[$category]['correct']++;
            } else {
                $groups[$category]['wrong']++;
            }

            if (! $result->isCorrect && $result->isReliable) {
                $groups[$category]['wrong_reliable']++;
            }
        }

        foreach ($groups as $category => $stats) {
            $groups[$category]['accuracy'] = round($stats['correct'] / $stats['total'], 4);
        }

        ksort($groups);

        return $groups;
    }
}
