<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Classification;

use Dujana\ArabicNlp\Enums\ArabicTokenTypeEnum;

final class ArabicTokenClassifier
{
    public function classify(string $token): ArabicTokenClassification
    {
        $token = trim($token);

        if ($token === '') {
            return new ArabicTokenClassification(
                token: $token,
                type: ArabicTokenTypeEnum::Empty,
                protected: true,
                reason: 'empty_token',
            );
        }

        if (preg_match('/^\p{P}+$/u', $token) === 1) {
            return new ArabicTokenClassification(
                token: $token,
                type: ArabicTokenTypeEnum::Punctuation,
                protected: true,
                reason: 'punctuation',
            );
        }

        if (preg_match('/^\d+$/u', $token) === 1) {
            return new ArabicTokenClassification(
                token: $token,
                type: ArabicTokenTypeEnum::Number,
                protected: true,
                reason: 'number',
            );
        }

        if ($token === 'ال') {
            return new ArabicTokenClassification(
                token: $token,
                type: ArabicTokenTypeEnum::DefiniteArticle,
                protected: true,
                reason: 'definite_article',
            );
        }

        if (ArabicFunctionWords::isSingleLetterParticle($token)) {
            return new ArabicTokenClassification(
                token: $token,
                type: ArabicTokenTypeEnum::SingleLetterParticle,
                protected: true,
                reason: 'single_letter_particle',
            );
        }

        if (ArabicFunctionWords::isParticle($token)) {
            return new ArabicTokenClassification(
                token: $token,
                type: ArabicTokenTypeEnum::Particle,
                protected: true,
                reason: 'particle',
            );
        }

        if (mb_strlen($token) < 3) {
            return new ArabicTokenClassification(
                token: $token,
                type: ArabicTokenTypeEnum::ShortUnknown,
                protected: true,
                reason: 'short_unknown',
            );
        }

        return new ArabicTokenClassification(
            token: $token,
            type: ArabicTokenTypeEnum::Word,
            protected: false,
            reason: null,
        );
    }
}
