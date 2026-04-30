<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp;

/**
 * Backward-compatible semantic alias for consumers who prefer stemmer wording.
 */
final readonly class StemResult
{
    public function __construct(public ArabicAnalysis $analysis) {}

    public function __get(string $name): mixed
    {
        return $this->analysis->{$name};
    }

    /** @return array<string,mixed> */
    public function toArray(bool $includeTrace = true): array
    {
        return $this->analysis->toArray($includeTrace);
    }
}
