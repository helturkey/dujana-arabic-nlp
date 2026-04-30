<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root\Rules\Verbs;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Contracts\Morphology\MorphologicalRuleContract;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\Rules\Concerns\RootRuleHelpers;

final class QuadriliteralVerbPatternRule implements MorphologicalRuleContract
{
    use RootRuleHelpers;

    /**
     * @return list<RootCandidate>
     */
    public function extract(CoreCandidate $candidate): array
    {
        $roots = [];

        foreach ($this->candidateWords($candidate) as $word) {
            array_push($roots, ...$this->extractF3ll($word));
            array_push($roots, ...$this->extractTf3ll($word));
            array_push($roots, ...$this->extractYf3ll($word));
        }

        return $this->uniqueRootCandidates($roots);
    }

    /**
     * فعلل:
     * دحرج، زلزل، وسوس
     *
     * Conservative:
     * bare quadriliteral verbs are ambiguous without context, so non-reduplicated
     * forms are exposed but intentionally low confidence / non-authoritative.
     *
     * Reduplicated quadriliterals are safer and must not be filtered as weak
     * just because they contain و, e.g. وسوس.
     *
     * @return list<RootCandidate>
     */
    private function extractF3ll(string $word): array
    {
        $isReduplicated = $this->looksLikeReduplicatedQuadriliteral($word);

        if (! $isReduplicated && ! $this->isBareQuadriliteralRoot($word)) {
            return [];
        }

        if (! $isReduplicated && $this->looksLikePresentTriliteralSurface($word)) {
            return [];
        }

        if (
            ! $isReduplicated
            && (
                $this->looksLikeFuulPluralSurface($word)
                || $this->looksLikeNisbaSurface($word)
            )
        ) {
            return [];
        }

        return [
            new RootCandidate(
                root: $word,
                confidence: $isReduplicated ? 0.89 : 0.84,
                source: 'rule:verb_quadriliteral_f3ll',
                reasons: array_values(array_filter([
                    'rule_family:verb',
                    'class:رباعي',
                    'form:فعلل',
                    'pattern:quadriliteral_f3ll',
                    'rule:f3ll_quadriliteral',
                    'morphology:quadriliteral_derived_verb',
                    $isReduplicated ? 'pattern:reduplicated_quadriliteral' : null,
                    $isReduplicated ? null : 'caution:f3ll_quadriliteral_is_ambiguous_without_context',
                    $isReduplicated ? null : 'not_authoritative_root',
                ])),
            ),
        ];
    }

    /**
     * يفعلل:
     * يدحرج، يزلزل، يوسوس
     *
     * @return list<RootCandidate>
     */
    private function extractYf3ll(string $word): array
    {
        if (preg_match('/^[يتأن]([\p{Arabic}]{4})$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1];
        $isReduplicated = $this->looksLikeReduplicatedQuadriliteral($root);

        if ($this->looksLikePluralPresentCore($root)) {
            return [];
        }

        if (! $isReduplicated && ! $this->isBareQuadriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: $isReduplicated ? 0.95 : 0.94,
                source: 'rule:verb_quadriliteral_yf3ll',
                reasons: array_values(array_filter([
                    'rule_family:verb',
                    'class:رباعي',
                    'form:يفعلل',
                    'pattern:quadriliteral_yf3ll',
                    'rule:remove_present_prefix',
                    $isReduplicated ? 'pattern:reduplicated_quadriliteral' : null,
                    'morphology:quadriliteral_present_verb',
                ])),
            ),
        ];
    }

    /**
     * تفعلل:
     * تدحرج، تزلزل
     *
     * @return list<RootCandidate>
     */
    private function extractTf3ll(string $word): array
    {
        if (preg_match('/^ت([\p{Arabic}]{4})$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1];
        $isReduplicated = $this->looksLikeReduplicatedQuadriliteral($root);

        if (! $isReduplicated && ! $this->isBareQuadriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: $isReduplicated ? 0.95 : 0.94,
                source: 'rule:verb_quadriliteral_tf3ll',
                reasons: array_values(array_filter([
                    'rule_family:verb',
                    'class:رباعي',
                    'form:تفعلل',
                    'pattern:quadriliteral_tf3ll',
                    'rule:remove_prefix_ت',
                    $isReduplicated ? 'pattern:reduplicated_quadriliteral' : null,
                    'morphology:quadriliteral_derived_verb',
                ])),
            ),
        ];
    }

    /**
     * يكتب، تكتب، نكتب، أكتب، اكتب are not quadriliteral roots.
     */
    private function looksLikePresentTriliteralSurface(string $word): bool
    {
        if (preg_match('/^[يتأناأ]([\p{Arabic}]{3})$/u', $word, $m) !== 1) {
            return false;
        }

        return $this->isStrongTriliteralRoot($m[1]);
    }

    /**
     * Fuul-like four-letter surfaces:
     * شهور، نجوم، جذور...
     *
     * Reduplicated quadriliterals such as وسوس / زلزل are allowed,
     * even when they contain و.
     */
    private function looksLikeFuulPluralSurface(string $word): bool
    {
        if ($this->looksLikeReduplicatedQuadriliteral($word)) {
            return false;
        }

        return preg_match('/^[\p{Arabic}]{2}و[\p{Arabic}]$/u', $word) === 1;
    }

    /**
     * Weak triliteral present plural cores:
     * دعون، رمون، سعان...
     */
    private function looksLikePluralPresentCore(string $core): bool
    {
        return mb_strlen($core) === 4
            && (
                str_ends_with($core, 'ون')
                || str_ends_with($core, 'ين')
                || str_ends_with($core, 'ان')
            );
    }
}
