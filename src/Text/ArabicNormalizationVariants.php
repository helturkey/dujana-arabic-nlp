<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Text;

use Dujana\ArabicNlp\Enums\NormalizationProfileEnum;

final readonly class ArabicNormalizationVariants
{
    public function __construct(
        private ArabicNormalizer $normalizer = new ArabicNormalizer,
        private ArabicHamzaNormalizer $hamzaNormalizer = new ArabicHamzaNormalizer,
    ) {}

    /**
     * @return list<string>
     */
    public function forLexiconLookup(string $word): array
    {
        return $this->unique([
            $word,
            $this->normalizer->normalize($word, NormalizationProfileEnum::Search),
            $this->normalizer->normalize($word, NormalizationProfileEnum::Morphology),
            $this->hamzaNormalizer->search($word),
            $this->hamzaNormalizer->morphology($word),
        ]);
    }

    /**
     * @param  list<string|null>  $values
     * @return list<string>
     */
    private function unique(array $values): array
    {
        $clean = [];

        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }

            $value = trim($value);

            if ($value === '') {
                continue;
            }

            $clean[] = $value;
        }

        return array_values(array_unique($clean));
    }
}
