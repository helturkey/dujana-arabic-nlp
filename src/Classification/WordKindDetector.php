<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Classification;

use Dujana\ArabicNlp\Enums\WordKindEnum;

final class WordKindDetector
{
    public function detect(string $word): WordKindEnum
    {
        /*
         * Lightweight fallback only.
         *
         * In Root mode, ArabicAnalyzer should prefer morphology/root-analysis
         * sources through WordKindFromRootSourceResolver.
         */
        return mb_strlen($word) >= 4
            && preg_match('/^[يتن][\p{Arabic}]{3,}$/u', $word) === 1
                ? WordKindEnum::Verb
                : WordKindEnum::Noun;
    }
}
