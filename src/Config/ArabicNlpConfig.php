<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Config;

use Dujana\ArabicNlp\Enums\StemmerModeEnum;

final readonly class ArabicNlpConfig
{
    public function __construct(
        public StemmerModeEnum $defaultMode = StemmerModeEnum::Moderate,
        public int $minWordLength = 3,
        public float $minConfidence = 1.25,
        public bool $fallbackToNormalizedOnLowConfidence = true,
        public bool $protectStopWords = true,
        public bool $protectProperNames = true,
        public bool $protectNonStemmable = true,
        public bool $exposeTrace = false,
        public int $maxInputLength = 10000,
        public int $maxTokenLength = 128,
        public ?string $stopWordsPath = null,
        public ?string $properNamesPath = null,
        public ?string $nonStemmablePath = null,
        public ?string $lexiconDatabasePath = null,
    ) {}

    /**
     * @param  array<string,mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            defaultMode: StemmerModeEnum::tryFrom((string) ($data['default_mode'] ?? 'moderate'))
                ?? StemmerModeEnum::Moderate,

            minWordLength: max(1, (int) ($data['min_word_length'] ?? 3)),
            minConfidence: (float) ($data['min_confidence'] ?? 1.25),
            fallbackToNormalizedOnLowConfidence: (bool) ($data['fallback_to_normalized_on_low_confidence'] ?? true),

            protectStopWords: (bool) ($data['protect_stop_words'] ?? true),
            protectProperNames: (bool) ($data['protect_proper_names'] ?? true),
            protectNonStemmable: (bool) ($data['protect_non_stemmable'] ?? true),

            exposeTrace: (bool) ($data['expose_trace'] ?? false),

            maxInputLength: max(1, (int) ($data['max_input_length'] ?? 10000)),
            maxTokenLength: max(1, (int) ($data['max_token_length'] ?? 128)),

            stopWordsPath: $data['stop_words_path']
                ?? $data['stopWordsPath']
                ?? ($data['lexicon']['stop_words'] ?? null),

            properNamesPath: $data['proper_names_path']
                ?? $data['properNamesPath']
                ?? ($data['lexicon']['proper_names'] ?? null),

            nonStemmablePath: $data['non_stemmable_path']
                ?? $data['nonStemmablePath']
                ?? ($data['lexicon']['non_stemmable'] ?? null),

            lexiconDatabasePath: $data['lexicon_database_path']
                ?? $data['lexicon_database']
                ?? $data['lexiconDatabasePath']
                ?? null,
        );
    }
}
