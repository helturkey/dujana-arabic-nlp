<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon;

use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Text\ArabicNormalizer;

final class ArabicLexicon
{
    /** @var array<string,true>|null */
    private ?array $stopWords = null;

    /** @var array<string,true>|null */
    private ?array $properNames = null;

    /** @var array<string,true>|null */
    private ?array $nonStemmable = null;

    public function __construct(
        private readonly ArabicNlpConfig $config,
        private readonly ArabicNormalizer $normalizer,
        private readonly WordListLoader $loader,
    ) {}

    public static function make(ArabicNlpConfig $config, ArabicNormalizer $normalizer): self
    {
        return new self($config, $normalizer, new WordListLoader($normalizer));
    }

    public function isStopWord(string $normalizedWord): bool
    {
        return $this->config->protectStopWords && isset($this->stopWordMap()[$normalizedWord]);
    }

    public function isProperName(string $normalizedWord): bool
    {
        return $this->config->protectProperNames && isset($this->properNameMap()[$normalizedWord]);
    }

    public function isNonStemmable(string $normalizedWord): bool
    {
        return $this->config->protectNonStemmable && isset($this->nonStemmableMap()[$normalizedWord]);
    }

    /** @return array<string,true> */
    private function stopWordMap(): array
    {
        return $this->stopWords ??= $this->toMap(array_merge(
            StopWords::WORDS,
            $this->loader->load($this->config->stopWordsPath),
        ));
    }

    /** @return array<string,true> */
    private function properNameMap(): array
    {
        return $this->properNames ??= $this->toMap(array_merge(
            ProperNames::WORDS,
            $this->loader->load($this->config->properNamesPath),
        ));
    }

    /** @return array<string,true> */
    private function nonStemmableMap(): array
    {
        return $this->nonStemmable ??= $this->toMap($this->loader->load($this->config->nonStemmablePath));
    }

    /** @param iterable<string> $words @return array<string,true> */
    private function toMap(iterable $words): array
    {
        $map = [];

        foreach ($words as $word) {
            $normalized = $this->normalizer->normalize($word);

            if ($normalized !== '') {
                $map[$normalized] = true;
            }
        }

        return $map;
    }
}
