<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Candidates;

use Dujana\ArabicNlp\Enums\AffixCategoryEnum;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

final class CoreCandidateRanker
{
    /** @param list<CoreCandidate> $candidates */
    public function best(array $candidates, StemmerModeEnum $mode = StemmerModeEnum::Moderate): CoreCandidate
    {
        return $this->rank($candidates, $mode)[0];
    }

    /** @param list<CoreCandidate> $candidates @return list<CoreCandidate> */
    public function rank(array $candidates, StemmerModeEnum $mode = StemmerModeEnum::Moderate): array
    {
        $ranked = array_map(
            fn (CoreCandidate $candidate): CoreCandidate => $this->score($candidate, $mode),
            $candidates
        );

        usort(
            $ranked,
            static fn (CoreCandidate $a, CoreCandidate $b): int => $b->score <=> $a->score
                    ?: mb_strlen($b->core) <=> mb_strlen($a->core)
                    ?: strcmp($a->core, $b->core)
        );

        return $ranked;
    }

    private function score(CoreCandidate $candidate, StemmerModeEnum $mode): CoreCandidate
    {
        $score = 1.0;
        $reasons = [];

        $prefix = $candidate->strippedPrefix();
        $suffix = $candidate->strippedSuffix();
        $coreLength = mb_strlen($candidate->core);

        /*
         * Prefer useful stemming, but do not require a scale matcher.
         *
         * Examples:
         * والكتاب => كتاب
         * وكتابهم => كتاب
         * كتابه => كتاب
         */
        if ($prefix === null) {
            $score += 0.20;
            $reasons[] = 'no_prefix';
        } else {
            $score += 0.35;
            $reasons[] = 'prefix:'.$prefix;
            $score += mb_strlen($prefix) * 0.08;
        }

        if ($suffix !== null) {
            $score += 0.45;
            $reasons[] = 'suffix:'.$suffix;
            $score += mb_strlen($suffix) * 0.06;
        }

        /*
         * Definite article and compound article prefixes are strong evidence of
         * a real removable prefix.
         */
        if ($candidate->prefixes !== []) {
            $category = $candidate->prefixes[0]->category;

            if (in_array($category, [
                AffixCategoryEnum::DefiniteArticle,
                AffixCategoryEnum::ConjunctionDefiniteArticle,
                AffixCategoryEnum::PrepositionDefiniteArticle,
                AffixCategoryEnum::ConjunctionPrepositionDefiniteArticle,
            ], true)) {
                $score += 0.80;
                $reasons[] = 'bonus:article_prefix';
            }
        }

        /*
         * Penalize candidates that leave a likely proclitic attached when the
         * generator also produced stripped alternatives.
         *
         * This is what fixes:
         * وكتابهم => كتاب, not وكتاب
         */
        if (
            $prefix === null
            && $coreLength > 4
            && preg_match('/^[وفبلكس]/u', $candidate->core) === 1
        ) {
            $score -= 0.35;
            $reasons[] = 'penalty:leading_proclitic_left_attached';
        }

        /*
         * Avoid over-stripping into very short cores.
         */
        if ($coreLength < 3) {
            $score -= 1.0;
            $reasons[] = 'penalty:too_short_core';
        } elseif ($coreLength === 3) {
            $score += 0.10;
            $reasons[] = 'bonus:triliteral_core';
        } elseif ($coreLength <= 6) {
            $score += 0.08;
            $reasons[] = 'bonus:reasonable_core_length';
        }

        /*
         * Existing safety guards for risky compound prefixes before ت.
         */
        if (in_array($prefix, ['وب', 'وك', 'ول', 'فب', 'فك', 'فل'], true) && str_starts_with($candidate->core, 'ت')) {
            $score -= 2.0;
            $reasons[] = 'penalty:dangerous_compound_prefix';
        }

        if (in_array($prefix, ['ب', 'ك', 'ل'], true) && str_starts_with($candidate->core, 'ت')) {
            $score -= 2.5;
            $reasons[] = 'penalty:lexical_initial_risk';
        }

        /*
         * Light mode should avoid suffix stripping.
         */
        if ($mode === StemmerModeEnum::Light && $suffix !== null) {
            $score -= 3.0;
            $reasons[] = 'penalty:light_suffix';
        }

        return $candidate->withScore($score, $reasons);
    }
}
