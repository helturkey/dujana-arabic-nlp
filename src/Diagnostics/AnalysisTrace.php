<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Diagnostics;

final class AnalysisTrace
{
    /** @var list<array<string,mixed>> */
    private array $items = [];

    /** @param array<string,mixed> $context */
    public function add(string $step, string $decision, array $context = []): void
    {
        $this->items[] = [
            'step' => $step,
            'decision' => $decision,
            'context' => $context,
        ];
    }

    /** @return list<array<string,mixed>> */
    public function toArray(): array
    {
        return $this->items;
    }
}
