<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Candidates;

use Dujana\ArabicNlp\Clitics\AffixRule;

final readonly class CoreCandidate
{
    /*
     * originalSurface keeps the raw input token before morphology normalization.
     * It is needed for morphology rules that depend on full harakat.
     */

    /**
     * @param  list<AffixRule>  $prefixes
     * @param  list<AffixRule>  $suffixes
     * @param  list<string>  $reasons
     */
    public function __construct(
        public string $originalSurface,
        public string $normalized,
        public string $core,
        public array $prefixes = [],
        public array $suffixes = [],
        public float $score = 0.0,
        public array $reasons = [],
    ) {
        $this->proclitics = array_map(static fn (AffixRule $rule): string => $rule->value, $this->prefixes);
        $this->enclitics = array_map(static fn (AffixRule $rule): string => $rule->value, $this->suffixes);
    }

    /** @var list<string> */
    public array $proclitics;

    /** @var list<string> */
    public array $enclitics;

    public function strippedPrefix(): ?string
    {
        return $this->proclitics === [] ? null : implode('', $this->proclitics);
    }

    public function strippedSuffix(): ?string
    {
        return $this->enclitics === [] ? null : implode('', $this->enclitics);
    }

    /** @param list<string> $reasons */
    public function withScore(float $score, array $reasons = []): self
    {
        return new self(
            originalSurface: $this->originalSurface,
            normalized: $this->normalized,
            core: $this->core,
            prefixes: $this->prefixes,
            suffixes: $this->suffixes,
            score: $score,
            reasons: $reasons,
        );
    }
}
