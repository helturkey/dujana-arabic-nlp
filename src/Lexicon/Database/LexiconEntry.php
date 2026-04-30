<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon\Database;

final readonly class LexiconEntry
{
    /**
     * @param  list<LexiconSource>  $sources
     * @param  list<array{root:string,confidence:float,sources:list<string>}>  $alternatives
     */
    public function __construct(
        public string $normalizedForm,
        public ?string $lemma,
        public ?string $normalizedLemma,
        public ?string $root,
        public ?string $posCat = null,
        public ?string $pos = null,
        public ?string $language = null,
        public float $confidence = 0.80,
        public int $sourceCount = 1,
        public array $sources = [],
        public array $alternatives = [],
    ) {}

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'normalized_form' => $this->normalizedForm,
            'lemma' => $this->lemma,
            'normalized_lemma' => $this->normalizedLemma,
            'root' => $this->root,
            'pos_cat' => $this->posCat,
            'pos' => $this->pos,
            'language' => $this->language,
            'confidence' => $this->confidence,
            'source_count' => $this->sourceCount,
            'sources' => array_map(
                static fn (LexiconSource $source): array => $source->toArray(),
                $this->sources
            ),
            'alternatives' => $this->alternatives,
        ];
    }
}
