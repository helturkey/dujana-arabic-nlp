<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Candidates;

use Dujana\ArabicNlp\Clitics\AffixRule;
use Dujana\ArabicNlp\Clitics\PrefixRules;
use Dujana\ArabicNlp\Clitics\SuffixRules;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Morphology\Suffix\ArabicSuffixPolicy;

final class CoreCandidateGenerator
{
    public function __construct(
        private readonly ArabicSuffixPolicy $suffixPolicy = new ArabicSuffixPolicy,
    ) {}

    /**
     * @return list<CoreCandidate>
     */
    public function generate(
        string $word,
        StemmerModeEnum $mode = StemmerModeEnum::Moderate,
        ?string $originalSurface = null,
    ): array {
        $originalSurface ??= $word;

        $prefixRules = $mode === StemmerModeEnum::Light
            ? PrefixRules::light()
            : PrefixRules::moderate();

        $suffixRules = $mode === StemmerModeEnum::Light
            ? SuffixRules::light()
            : SuffixRules::moderate();

        $prefixCandidates = $this->prefixCandidates($word, $prefixRules);
        $candidates = [];

        foreach ($prefixCandidates as [$afterPrefix, $prefixes]) {
            $candidates[] = new CoreCandidate(
                originalSurface: $originalSurface,
                normalized: $word,
                core: $afterPrefix,
                prefixes: $prefixes,
                suffixes: [],
            );

            foreach ($suffixRules as $suffixRule) {
                if (! str_ends_with($afterPrefix, $suffixRule->value)) {
                    continue;
                }

                if ($this->suffixPolicy->shouldKeepSuffix($afterPrefix, $suffixRule->value)) {
                    continue;
                }

                $core = mb_substr($afterPrefix, 0, -mb_strlen($suffixRule->value));

                if (mb_strlen($core) < $suffixRule->minStemLength) {
                    continue;
                }

                $core = $this->normalizeBoundTaa(
                    core: $core,
                    originalSurface: $originalSurface,
                    surfaceBeforeSuffix: $afterPrefix,
                    suffixRule: $suffixRule,
                );

                $candidates[] = new CoreCandidate(
                    originalSurface: $originalSurface,
                    normalized: $word,
                    core: $core,
                    prefixes: $prefixes,
                    suffixes: [$suffixRule],
                );
            }
        }

        return $this->unique($candidates);
    }

    /**
     * @param  list<AffixRule>  $prefixRules
     * @return list<array{0:string,1:list<AffixRule>}>
     */
    private function prefixCandidates(string $word, array $prefixRules): array
    {
        $candidates = [[$word, []]];

        foreach ($prefixRules as $prefixRule) {
            if (! str_starts_with($word, $prefixRule->value)) {
                continue;
            }

            if ($this->shouldKeepPrefixAsPatternLetter($word, $prefixRule)) {
                continue;
            }

            $afterPrefix = mb_substr($word, mb_strlen($prefixRule->value));

            if (mb_strlen($afterPrefix) < $prefixRule->minStemLength) {
                continue;
            }

            $candidates[] = [$afterPrefix, [$prefixRule]];
        }

        return $candidates;
    }

    private function shouldKeepPrefixAsPatternLetter(string $surface, AffixRule $prefixRule): bool
    {
        if ($prefixRule->value !== 'ا') {
            return false;
        }

        return $this->suffixPolicy->shouldKeepInitialAlifAsPatternLetter($surface);
    }

    private function normalizeBoundTaa(
        string $core,
        string $originalSurface,
        string $surfaceBeforeSuffix,
        AffixRule $suffixRule,
    ): string {
        if (! str_ends_with($core, 'ت')) {
            return $core;
        }

        if (! $this->suffixPolicy->shouldDropFeminineTaaAfterSuffix(
            candidate: $core,
            word: $surfaceBeforeSuffix,
            suffix: $suffixRule->value,
            originalSurface: $originalSurface,
        )) {
            return $core;
        }

        $normalized = mb_substr($core, 0, -1);

        return mb_strlen($normalized) >= $suffixRule->minStemLength
            ? $normalized
            : $core;
    }

    /**
     * @param  list<CoreCandidate>  $candidates
     * @return list<CoreCandidate>
     */
    private function unique(array $candidates): array
    {
        $seen = [];
        $unique = [];

        foreach ($candidates as $candidate) {
            $key = $candidate->core.'|'.$candidate->strippedPrefix().'|'.$candidate->strippedSuffix();

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $unique[] = $candidate;
        }

        return $unique;
    }
}
