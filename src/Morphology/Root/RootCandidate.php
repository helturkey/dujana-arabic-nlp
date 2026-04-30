<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root;

final readonly class RootCandidate
{
    /** @param list<string> $reasons */
    public function __construct(
        public string $root,
        public float $confidence,
        public string $source,
        public array $reasons = [],
    ) {}

    public function isAuthoritative(): bool
    {
        return (new RootSourcePolicy)->isAuthoritative(
            source: $this->source,
            confidence: $this->confidence,
        );
    }

    /**
     * @return array{
     *     root:string,
     *     confidence:float,
     *     source:string,
     *     reasons:list<string>,
     *     authoritative:bool
     * }
     */
    public function toArray(): array
    {
        return [
            'root' => $this->root,
            'source' => $this->source,
            'confidence' => $this->confidence,
            'authoritative' => $this->isAuthoritative(),
            'reasons' => $this->reasons,
        ];
    }
}
