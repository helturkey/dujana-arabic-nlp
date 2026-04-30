<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp;

use Dujana\ArabicNlp\Classification\ArabicTokenClassification;
use Dujana\ArabicNlp\Classification\ArabicTokenClassifier;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Text\ArabicNormalizer;

final readonly class ArabicClassifier
{
    public function __construct(
        private ArabicTokenClassifier $classifier = new ArabicTokenClassifier,
        private ArabicNormalizer $normalizer = new ArabicNormalizer,
    ) {}

    public static function make(?ArabicNlpConfig $config = null): self
    {
        /*
         * Keep config accepted for API symmetry.
         * Later, classification may use protected word lists or config flags.
         */
        return new self;
    }

    public function classify(string $word): ArabicTokenClassification
    {
        $normalized = $this->normalizer->normalize($word);

        return $this->classifier->classify($normalized);
    }

    /**
     * @return array<string,mixed>
     */
    public function classifyToArray(string $word): array
    {
        return $this->classify($word)->toArray();
    }
}
