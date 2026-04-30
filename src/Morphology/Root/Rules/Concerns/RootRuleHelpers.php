<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root\Rules\Concerns;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;

trait RootRuleHelpers
{
    private const HAMZA_PATTERN = '/[أإآؤئء]/u';

    /**
     * Arabic harakat except shadda.
     *
     * Shadda is preserved because several rules use it as surface evidence:
     * مدّ، معلّم، متعلّم.
     */
    private const HARAKAT_EXCEPT_SHADDA_PATTERN = '/[\x{064B}-\x{0650}\x{0652}]/u';

    /**
     * All Arabic harakat including shadda.
     */
    private const HARAKAT_PATTERN = '/[\x{064B}-\x{0652}]/u';

    /**
     * Letters that make a root unsafe for ordinary strong-root no-db rules.
     */
    private const WEAK_OR_HAMZA_PATTERN = '/[اأإآؤئءوىي]/u';

    /**
     * Less strict pattern: allows و / ي, but still rejects hamza, alif, and alif-maqsura.
     */
    private const HAMZA_OR_ALEF_PATTERN = '/[اأإآؤئءى]/u';

    /**
     * @return list<string>
     */
    private function candidateWords(CoreCandidate $candidate): array
    {
        return $this->uniqueStrings([
            $candidate->core,
            $candidate->normalized,
        ]);
    }

    /**
     * @param  list<string|null>  $values
     * @return list<string>
     */
    private function uniqueStrings(array $values): array
    {
        return array_values(array_unique(array_filter(
            $values,
            static fn (?string $value): bool => $value !== null && $value !== ''
        )));
    }

    private function containsHamza(string $word): bool
    {
        return preg_match(self::HAMZA_PATTERN, $word) === 1;
    }

    private function stripHarakat(string $word): string
    {
        return preg_replace(self::HARAKAT_PATTERN, '', $word) ?? $word;
    }

    private function stripHarakatKeepShadda(string $word): string
    {
        return preg_replace(self::HARAKAT_EXCEPT_SHADDA_PATTERN, '', $word) ?? $word;
    }

    private function hasNonShaddaHaraka(string $word): bool
    {
        return preg_match(self::HARAKAT_EXCEPT_SHADDA_PATTERN, $word) === 1;
    }

    private function surfaceShape(string $surface): string
    {
        return $this->stripHarakat($surface);
    }

    private function arabicLength(string $word): int
    {
        return mb_strlen($this->surfaceShape($word));
    }

    private function isTriliteralRootShape(string $root): bool
    {
        return preg_match('/^[\p{Arabic}]{3}$/u', $root) === 1;
    }

    private function isQuadriliteralRootShape(string $root): bool
    {
        return preg_match('/^[\p{Arabic}]{4}$/u', $root) === 1;
    }

    /**
     * Strict strong triliteral root.
     *
     * Use for authoritative no-db rules where weak/hamza roots should not be guessed.
     *
     * Rejects:
     * - hamza
     * - alif
     * - waw
     * - ya
     * - alif-maqsura
     */
    private function isStrongTriliteralRoot(string $root): bool
    {
        return $this->isTriliteralRootShape($root)
            && preg_match(self::WEAK_OR_HAMZA_PATTERN, $root) !== 1;
    }

    /**
     * Weak-aware triliteral root.
     *
     * Allows و / ي, but rejects hamza, alif, and alif-maqsura.
     * Use only when a rule explicitly supports weak roots.
     */
    private function isWeakAwareTriliteralRoot(string $root): bool
    {
        return $this->isTriliteralRootShape($root)
            && preg_match(self::HAMZA_OR_ALEF_PATTERN, $root) !== 1;
    }

    private function isBareQuadriliteralRoot(string $root): bool
    {
        return $this->isQuadriliteralRootShape($root)
            && preg_match(self::WEAK_OR_HAMZA_PATTERN, $root) !== 1;
    }

    private function looksLikeReduplicatedQuadriliteral(string $word): bool
    {
        $letters = preg_split('//u', $this->surfaceShape($word), -1, PREG_SPLIT_NO_EMPTY);

        return is_array($letters)
            && count($letters) === 4
            && $letters[0] === $letters[2]
            && $letters[1] === $letters[3];
    }

    private function looksLikeNisbaSurface(string $surface): bool
    {
        $surface = $this->surfaceShape($surface);

        return str_ends_with($surface, 'ي')
            || str_ends_with($surface, 'ية');
    }

    private function looksLikeFeminineNisbaSurface(string $surface): bool
    {
        return str_ends_with($this->surfaceShape($surface), 'ية');
    }

    private function hasDerivedMeemPrefix(string $word): bool
    {
        $word = $this->surfaceShape($word);

        return str_starts_with($word, 'مت')
            || str_starts_with($word, 'مست');
    }

    /**
     * Avoid م + فوع-like plural shapes that can look like place/time or instrument forms.
     */
    private function looksLikeFuulPluralAfterMeem(string $word): bool
    {
        return preg_match('/^م[\p{Arabic}]و[\p{Arabic}]$/u', $this->surfaceShape($word)) === 1;
    }

    /**
     * أفعال broken plural pattern:
     * أقلام، أبواب، أشعار، ألوان...
     *
     * These belong in manual-roots.tsv / database, not authoritative no-db rules.
     */
    private function looksLikeAfalBrokenPlural(string $surface): bool
    {
        return preg_match('/^أ[\p{Arabic}]{2}ا[\p{Arabic}]$/u', $this->surfaceShape($surface)) === 1;
    }

    /**
     * Safe root check for assimilated افتعل rules.
     *
     * Allows initial waw only:
     * اتصل => وصل
     * اتصف => وصف
     */
    private function isAssimilatedIftaalaRoot(string $root): bool
    {
        if (! $this->isTriliteralRootShape($root)) {
            return false;
        }

        if ($this->containsHamza($root)) {
            return false;
        }

        if (str_starts_with($root, 'و')) {
            return preg_match('/[اوىي]$/u', $root) !== 1;
        }

        return preg_match('/[اوىي]/u', $root) !== 1;
    }

    /**
     * @param  list<RootCandidate>  $candidates
     * @return list<RootCandidate>
     */
    private function uniqueRootCandidates(array $candidates): array
    {
        $unique = [];

        foreach ($candidates as $candidate) {
            $key = $candidate->root.'|'.$candidate->source;

            if (! isset($unique[$key]) || $candidate->confidence > $unique[$key]->confidence) {
                $unique[$key] = $candidate;
            }
        }

        return array_values($unique);
    }
}
