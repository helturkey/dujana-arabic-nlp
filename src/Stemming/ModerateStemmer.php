<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Stemming;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Morphology\Suffix\ArabicSuffixPolicy;

final class ModerateStemmer
{
    public function __construct(
        private readonly ArabicSuffixPolicy $suffixPolicy = new ArabicSuffixPolicy,
    ) {}

    public function stem(CoreCandidate $candidate): string
    {
        $core = $candidate->core;

        $core = $this->stripDefiniteArticle($core);
        $core = $this->stripModerateSuffix($core, $candidate->originalSurface);
        $core = $this->stripDefiniteArticle($core);

        return $core;
    }

    private function stripDefiniteArticle(string $word): string
    {
        return mb_strlen($word) > 4 && str_starts_with($word, 'ال')
            ? mb_substr($word, 2)
            : $word;
    }

    private function stripModerateSuffix(string $word, string $originalSurface): string
    {
        foreach (ArabicSuffixPolicy::MODERATE_SUFFIXES as $suffix) {
            if (! str_ends_with($word, $suffix)) {
                continue;
            }

            if ($this->suffixPolicy->shouldKeepSuffix($word, $suffix)) {
                continue;
            }

            $candidate = mb_substr($word, 0, -mb_strlen($suffix));

            if (mb_strlen($candidate) < 3) {
                continue;
            }

            if ($this->suffixPolicy->shouldDropFeminineTaaAfterSuffix(
                candidate: $candidate,
                word: $word,
                suffix: $suffix,
                originalSurface: $originalSurface,
            )) {
                $candidate = mb_substr($candidate, 0, -1);
            }

            return $candidate;
        }

        return $word;
    }
}
