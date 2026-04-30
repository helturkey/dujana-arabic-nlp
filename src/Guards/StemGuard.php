<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Guards;

use Dujana\ArabicNlp\Classification\ArabicTokenClassification;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\ArabicTokenTypeEnum;
use Dujana\ArabicNlp\Enums\GuardReasonEnum;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\ArabicLexicon;

final readonly class StemGuard
{
    public function __construct(
        private ArabicNlpConfig $config,
        private ArabicLexicon $lexicon,
    ) {}

    public function check(
        string $original,
        string $normalized,
        ?StemmerModeEnum $mode = null,
        ?ArabicTokenClassification $classification = null,
    ): GuardResult {
        if (trim($original) === '' || $normalized === '') {
            return GuardResult::protect(GuardReasonEnum::Empty);
        }

        if ($classification !== null) {
            $classificationGuard = $this->checkClassification($classification, $mode);

            if ($classificationGuard->protected) {
                return $classificationGuard;
            }
        }

        if (
            mb_strlen($normalized) < $this->config->minWordLength
            && $mode !== StemmerModeEnum::Root
        ) {
            return GuardResult::protect(GuardReasonEnum::TooShort);
        }

        if ($this->lexicon->isStopWord($normalized)) {
            return GuardResult::protect(GuardReasonEnum::StopWord);
        }

        if ($this->lexicon->isProperName($normalized)) {
            return GuardResult::protect(GuardReasonEnum::ProperName);
        }

        if ($this->lexicon->isNonStemmable($normalized)) {
            return GuardResult::protect(GuardReasonEnum::NonStemmable);
        }

        return GuardResult::pass();
    }

    private function checkClassification(
        ArabicTokenClassification $classification,
        ?StemmerModeEnum $mode,
    ): GuardResult {
        if (! $classification->protected) {
            return GuardResult::pass();
        }

        /*
         * Short unknown tokens may be valid root-mode lexical forms:
         *
         * مد => مدد
         * شد => شدد
         * رد => ردد
         *
         * So root mode must allow them to reach DB/manual root lookup.
         */
        if (
            $mode === StemmerModeEnum::Root
            && $classification->type === ArabicTokenTypeEnum::ShortUnknown
        ) {
            return GuardResult::pass();
        }

        return GuardResult::protect(
            GuardReasonEnum::tryFrom($classification->type->value) ?? GuardReasonEnum::TooShort
        );
    }
}
