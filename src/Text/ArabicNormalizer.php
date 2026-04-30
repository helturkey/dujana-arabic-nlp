<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Text;

use Dujana\ArabicNlp\Enums\HamzaStrategyEnum;
use Dujana\ArabicNlp\Enums\NormalizationProfileEnum;
use Normalizer as IntlNormalizer;

final class ArabicNormalizer
{
    private const DIACRITICS_PATTERN = '/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u';

    /**
     * Arabic diacritics except shadda U+0651.
     *
     * Shadda is morphologically significant in root analysis:
     * علّم، درّس، احمرّ، اخضرّ.
     */
    private const DIACRITICS_EXCEPT_SHADDA_PATTERN = '/[\x{0610}-\x{061A}\x{064B}-\x{0650}\x{0652}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u';

    private const EDGE_PUNCTUATION_PATTERN = '/^[\s\p{P}\p{S}،؛؟«»“”‘’]+|[\s\p{P}\p{S}،؛؟«»“”‘’]+$/u';

    public function __construct(
        private readonly NormalizationProfileEnum $defaultProfile = NormalizationProfileEnum::Search,
        private readonly bool $unicodeNfc = true,
        private readonly bool $removeTatweel = true,
        private readonly bool $removeDiacritics = true,
        private readonly bool $normalizeAlefMaqsura = true,
        private readonly bool $normalizeWhitespace = true,
        private readonly ?bool $normalizeTaMarbuta = null,
        private readonly ?HamzaStrategyEnum $hamzaStrategy = null,
        private readonly ArabicHamzaNormalizer $hamzaNormalizer = new ArabicHamzaNormalizer,
    ) {}

    public function normalize(string $text, ?NormalizationProfileEnum $profile = null): string
    {
        $profile ??= $this->defaultProfile;

        /*
         * First trim is only for fast empty detection.
         * Final trim happens after whitespace normalization.
         */
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        if ($this->unicodeNfc && class_exists(IntlNormalizer::class)) {
            $text = IntlNormalizer::normalize($text, IntlNormalizer::FORM_C) ?: $text;
        }

        if ($this->removeTatweel) {
            $text = $this->removeTatweel($text);
        }

        if ($this->removeDiacritics) {
            $text = $this->removeDiacritics($text, $profile);
        }

        $text = $this->removeInvisibleMarks($text);
        $text = $this->hamzaNormalizer->normalize($text, $this->resolveHamzaStrategy($profile));

        if ($this->normalizeAlefMaqsura) {
            $text = str_replace('ى', 'ي', $text);
        }

        $taMarbuta = $this->resolveTaMarbutaNormalization($profile);

        if ($taMarbuta !== null) {
            $text = str_replace('ة', $taMarbuta, $text);
        }

        if ($this->normalizeWhitespace) {
            $text = $this->normalizeWhitespace($text);
        }

        return trim($text);
    }

    public function normalizeToken(string $token, ?NormalizationProfileEnum $profile = null): string
    {
        return $this->normalize(
            $this->trimTokenEdges($token),
            $profile,
        );
    }

    private function trimTokenEdges(string $token): string
    {
        return preg_replace(self::EDGE_PUNCTUATION_PATTERN, '', $token) ?? $token;
    }

    public function normalizeForSearch(string $text): string
    {
        return $this->normalize($text, NormalizationProfileEnum::Search);
    }

    public function normalizeForStemming(string $text): string
    {
        return $this->normalize($text, NormalizationProfileEnum::Stemming);
    }

    public function normalizeForMorphology(string $text): string
    {
        return $this->normalize($text, NormalizationProfileEnum::Morphology);
    }

    private function removeTatweel(string $text): string
    {
        return str_replace('ـ', '', $text);
    }

    private function removeDiacritics(string $text, NormalizationProfileEnum $profile): string
    {
        $pattern = $profile === NormalizationProfileEnum::Morphology
            ? self::DIACRITICS_EXCEPT_SHADDA_PATTERN
            : self::DIACRITICS_PATTERN;

        return preg_replace($pattern, '', $text) ?? $text;
    }

    private function removeInvisibleMarks(string $text): string
    {
        return str_replace(
            [
                "\u{200C}",
                "\u{200D}",
                "\u{200E}",
                "\u{200F}",
                "\u{202A}",
                "\u{202B}",
                "\u{202C}",
                "\u{202D}",
                "\u{202E}",
            ],
            '',
            $text,
        );
    }

    private function resolveHamzaStrategy(NormalizationProfileEnum $profile): HamzaStrategyEnum
    {
        if ($this->hamzaStrategy !== null) {
            return $this->hamzaStrategy;
        }

        return match ($profile) {
            NormalizationProfileEnum::Raw => HamzaStrategyEnum::PreserveSeat,
            NormalizationProfileEnum::Search => HamzaStrategyEnum::Search,
            NormalizationProfileEnum::Stemming => HamzaStrategyEnum::Search,
            NormalizationProfileEnum::Morphology => HamzaStrategyEnum::Morphology,
        };
    }

    private function resolveTaMarbutaNormalization(NormalizationProfileEnum $profile): ?string
    {
        if ($this->normalizeTaMarbuta !== null) {
            return $this->normalizeTaMarbuta ? 'ه' : null;
        }

        /*
         * Keep ة by default.
         * Removing or rewriting it is a stemming decision, not a global
         * normalization decision.
         */
        return null;
    }

    private function normalizeWhitespace(string $text): string
    {
        return preg_replace('/\s+/u', ' ', $text) ?? $text;
    }
}
