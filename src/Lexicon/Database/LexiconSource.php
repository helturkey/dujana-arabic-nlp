<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon\Database;

final readonly class LexiconSource
{
    public function __construct(
        public string $source,
        public ?string $sourceLemma,
        public ?string $sourceRoot,
        public ?string $sourcePos = null,
        public ?string $sourcePayload = null,
        public float $confidence = 0.80,
    ) {}

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'source_lemma' => $this->sourceLemma,
            'source_root' => $this->sourceRoot,
            'source_pos' => $this->sourcePos,
            'source_payload' => $this->sourcePayload,
            'confidence' => $this->confidence,
        ];
    }
}
