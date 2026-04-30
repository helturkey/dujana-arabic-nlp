<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root;

final readonly class RootAnalysis
{
    /**
     * @param  list<RootCandidate>  $candidates
     */
    public function __construct(
        public string $word,
        public ?RootCandidate $best,
        public array $candidates = [],
    ) {}

    public function rootOr(string $fallback): string
    {
        return $this->best->root ?? $fallback;
    }

    public function reliable(): bool
    {
        return $this->best?->isAuthoritative() ?? false;
    }

    public function status(): string
    {
        if ($this->best === null) {
            return 'no_candidate';
        }

        return $this->reliable()
            ? 'reliable'
            : 'unreliable';
    }

    public function reason(): string
    {
        if ($this->best === null) {
            return 'no_root_candidate';
        }

        return $this->reliable()
            ? 'reliable_authoritative_candidate'
            : 'best_candidate_not_authoritative';
    }

    /**
     * @return array{
     *     word:string,
     *     root:string|null,
     *     source:string|null,
     *     confidence:float|null,
     *     status:string,
     *     reliable:bool,
     *     reason:string,
     *     candidates_count:int,
     *     candidates:list<array<string,mixed>>
     * }
     */
    public function toArray(): array
    {
        return [
            'word' => $this->word,
            'root' => $this->best?->root,
            'source' => $this->best?->source,
            'confidence' => $this->best?->confidence,
            'status' => $this->status(),
            'reliable' => $this->reliable(),
            'reason' => $this->reason(),
            'candidates_count' => count($this->candidates),
            'candidates' => array_map(
                static fn (RootCandidate $candidate): array => $candidate->toArray(),
                $this->candidates,
            ),
        ];
    }
}
