<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Suffix;

final class ArabicSuffixPolicy
{
    /**
     * @var list<string>
     */
    public const MODERATE_SUFFIXES = [
        'تين',
        'يات',
        'ون',
        'ين',
        'ات',
        'ان',
        'هم',
        'ها',
        'كم',
        'نا',
        'هن',
        'ة',
        'ه',
        'ك',
        'ي',
    ];

    /**
     * These suffixes may follow a feminine ة that was normalized/stored as ت.
     *
     * @var list<string>
     */
    private const FEMININE_TAA_SUFFIXES = [
        'ان',
        'ين',
        'تين',
        'ه',
        'ها',
        'هم',
        'هن',
        'كم',
        'نا',
        'ك',
        'ي',
    ];

    public function shouldKeepSuffix(string $word, string $suffix): bool
    {
        $word = $this->surfaceShape($word);

        return match ($suffix) {
            'ك' => $this->shouldKeepFinalKaf($word),
            'ي' => $this->shouldKeepFinalYa($word),
            'ه' => $this->shouldKeepFinalHa($word),
            'ون' => $this->shouldKeepFinalWawNun($word),
            'ين' => $this->shouldKeepFinalYaNun($word),
            'ات' => $this->shouldKeepFinalAlifTaa($word),
            'ان' => $this->shouldKeepFinalAlifNun($word),
            default => false,
        };
    }

    public function shouldDropFeminineTaaAfterSuffix(
        string $candidate,
        string $word,
        string $suffix,
        string $originalSurface,
    ): bool {
        if (
            ! str_ends_with($candidate, 'ت')
            || ! in_array($suffix, self::FEMININE_TAA_SUFFIXES, true)
        ) {
            return false;
        }

        /*
         * Example:
         * مدرستهم => candidate: مدرست, suffix: هم
         * رحلتها  => candidate: رحلت, suffix: ها
         */
        if ($this->looksLikeFeminineTaaStem($candidate)) {
            return true;
        }

        if ($this->hasBoundTaaBeforeSuffix($word, $suffix)) {
            return true;
        }

        return $this->hasBoundTaaBeforeSuffix($originalSurface, $suffix);
    }

    public function hasBoundTaaBeforeSuffix(string $surface, string $suffix): bool
    {
        $surface = $this->surfaceShape($surface);

        if ($suffix === '') {
            return false;
        }

        if (str_ends_with($surface, 'ة'.$suffix)) {
            return true;
        }

        if (! str_ends_with($surface, 'ت'.$suffix)) {
            return false;
        }

        $beforeSuffix = mb_substr($surface, 0, -mb_strlen($suffix));

        return $this->looksLikeFeminineTaaStem($beforeSuffix);
    }

    public function looksLikeFeminineTaaStem(string $word): bool
    {
        $word = $this->surfaceShape($word);

        if (! str_ends_with($word, 'ت')) {
            return false;
        }

        $base = mb_substr($word, 0, -1);

        if (mb_strlen($base) < 3) {
            return false;
        }

        /*
         * Protect root-final ت words:
         * صوت، وقت، موت، بيت
         */
        if (preg_match('/^[\p{Arabic}]{2}[اوي][\p{Arabic}]?$/u', $word) === 1) {
            return false;
        }

        /*
         * Protect فعال-like nouns ending with ت:
         * نبات، ثبات، شتات، رفات
         */
        if (preg_match('/^[\p{Arabic}]{2}ات$/u', $word) === 1) {
            return false;
        }

        return true;
    }

    public function shouldKeepFinalKaf(string $word): bool
    {
        /*
         * افتعال-like masdars ending with original ك:
         * اهتلاك، اشتراك، ارتباك، احتكاك
         */
        if (preg_match('/^ا[\p{Arabic}]ت[\p{Arabic}]اك$/u', $word) === 1) {
            return true;
        }

        if (preg_match('/^ا[\p{Arabic}]ت[\p{Arabic}]{2}اك$/u', $word) === 1) {
            return true;
        }

        /*
         * فعال-like nominal surfaces ending with original ك:
         * ملاك، هلاك، شباك
         */
        if (preg_match('/^[\p{Arabic}][\p{Arabic}]اك$/u', $word) === 1) {
            return true;
        }

        return false;
    }

    public function shouldKeepFinalYa(string $word): bool
    {
        /*
         * Derived noun/adjective/participle-like forms:
         * مشتهي، منتهي، مهتدي، مقتدي، مرتجي
         */
        if (preg_match('/^م[\p{Arabic}]{3,}ي$/u', $word) === 1) {
            return true;
        }

        /*
         * Defective active participle-like nouns:
         * قاضي، رامي، هادي، داعي، ساعي
         */
        if (preg_match('/^[\p{Arabic}]{2,}ا[\p{Arabic}]ي$/u', $word) === 1) {
            return true;
        }

        return false;
    }

    public function shouldKeepFinalHa(string $word): bool
    {
        /*
         * Do not protect final ه blindly.
         *
         * For short original words such as:
         * وجه، فقه، شبه، إله
         *
         * stripping ه would leave less than the minimum stem length in candidate
         * generation / moderate stemming, so the length guard already protects them.
         *
         * But possessive forms must still strip:
         * صوته => صوت
         * وقته => وقت
         */
        return false;
    }

    public function shouldKeepFinalWawNun(string $word): bool
    {
        /*
         * Structural ون, not plural suffix:
         * قانون، ليمون، زيتون
         *
         * Keep this intentionally lexical/narrow because ون is commonly
         * a sound masculine plural suffix:
         * مسلمون => مسلم
         * كاتبون => كاتب
         */
        if (in_array($word, ['قانون', 'ليمون', 'زيتون'], true)) {
            return true;
        }

        /*
         * مفعول-like short forms:
         * مجنون، مفتون، مديون
         *
         * Keep this narrow. Do NOT match مسلمون.
         */
        if (preg_match('/^م[\p{Arabic}]{2}ون$/u', $word) === 1) {
            return true;
        }

        return false;
    }

    public function shouldKeepFinalYaNun(string $word): bool
    {
        /*
         * سكين، يقين، حنين، أنين، كمين، يمين
         */
        return preg_match('/^[\p{Arabic}]{2,}ين$/u', $word) === 1
            && mb_strlen($word) <= 5;
    }

    public function shouldKeepFinalAlifTaa(string $word): bool
    {
        /*
         * ثبات، نبات، شتات، رفات
         */
        return preg_match('/^[\p{Arabic}]{2}ات$/u', $word) === 1;
    }

    public function shouldKeepFinalAlifNun(string $word): bool
    {
        /*
         * نقصان، غفران، حرمان، جريان، دوران، غليان
         */
        return preg_match('/^[\p{Arabic}]{2,}ان$/u', $word) === 1
            && mb_strlen($word) <= 6;
    }

    public function shouldKeepInitialAlifAsPatternLetter(string $surface): bool
    {
        $surface = $this->surfaceShape($surface);

        return str_starts_with($surface, 'است')
            || str_starts_with($surface, 'ان')
            || preg_match('/^ا[\p{Arabic}]ت[\p{Arabic}]{2,}$/u', $surface) === 1
            || preg_match('/^ا[تدطظزصض][\p{Arabic}]{2}$/u', $surface) === 1;
    }

    public function surfaceShape(string $word): string
    {
        $word = preg_replace(
            '/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u',
            '',
            $word
        ) ?? $word;

        return str_replace('ـ', '', $word);
    }
}
