<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Clitics;

use Dujana\ArabicNlp\Enums\AffixCategoryEnum;
use Dujana\ArabicNlp\Enums\AffixTypeEnum;

final class PrefixRules
{
    /** @return list<AffixRule> */
    public static function light(): array
    {
        return self::moderate();
    }

    /** @return list<AffixRule> */
    public static function moderate(): array
    {
        return [
            new AffixRule('وبال', AffixTypeEnum::Prefix, AffixCategoryEnum::ConjunctionPrepositionDefiniteArticle, 'waw_ba_al', 3, 5),
            new AffixRule('وكال', AffixTypeEnum::Prefix, AffixCategoryEnum::ConjunctionPrepositionDefiniteArticle, 'waw_kaf_al', 3, 5),
            new AffixRule('فبال', AffixTypeEnum::Prefix, AffixCategoryEnum::ConjunctionPrepositionDefiniteArticle, 'fa_ba_al', 3, 5),
            new AffixRule('فكال', AffixTypeEnum::Prefix, AffixCategoryEnum::ConjunctionPrepositionDefiniteArticle, 'fa_kaf_al', 3, 5),
            new AffixRule('وال', AffixTypeEnum::Prefix, AffixCategoryEnum::ConjunctionDefiniteArticle, 'waw_al', 3, 10),
            new AffixRule('فال', AffixTypeEnum::Prefix, AffixCategoryEnum::ConjunctionDefiniteArticle, 'fa_al', 3, 10),
            new AffixRule('بال', AffixTypeEnum::Prefix, AffixCategoryEnum::PrepositionDefiniteArticle, 'ba_al', 3, 10),
            new AffixRule('كال', AffixTypeEnum::Prefix, AffixCategoryEnum::PrepositionDefiniteArticle, 'ka_al', 3, 10),
            new AffixRule('لال', AffixTypeEnum::Prefix, AffixCategoryEnum::PrepositionDefiniteArticle, 'lam_al', 3, 10),
            new AffixRule('ال', AffixTypeEnum::Prefix, AffixCategoryEnum::DefiniteArticle, 'definite_article', 3, 20),
            new AffixRule('لل', AffixTypeEnum::Prefix, AffixCategoryEnum::PrepositionDefiniteArticle, 'lam_lam', 3, 20),
            new AffixRule('وب', AffixTypeEnum::Prefix, AffixCategoryEnum::ConjunctionPreposition, 'waw_ba', 4, 30),
            new AffixRule('وك', AffixTypeEnum::Prefix, AffixCategoryEnum::ConjunctionPreposition, 'waw_kaf', 4, 30),
            new AffixRule('ول', AffixTypeEnum::Prefix, AffixCategoryEnum::ConjunctionPreposition, 'waw_lam', 4, 30),
            new AffixRule('فب', AffixTypeEnum::Prefix, AffixCategoryEnum::ConjunctionPreposition, 'fa_ba', 4, 30),
            new AffixRule('فك', AffixTypeEnum::Prefix, AffixCategoryEnum::ConjunctionPreposition, 'fa_kaf', 4, 30),
            new AffixRule('فل', AffixTypeEnum::Prefix, AffixCategoryEnum::ConjunctionPreposition, 'fa_lam', 4, 30),
            new AffixRule('و', AffixTypeEnum::Prefix, AffixCategoryEnum::Conjunction, 'waw', 4, 50),
            new AffixRule('ف', AffixTypeEnum::Prefix, AffixCategoryEnum::Conjunction, 'fa', 4, 50),
            new AffixRule('ب', AffixTypeEnum::Prefix, AffixCategoryEnum::Preposition, 'ba', 4, 60),
            new AffixRule('ك', AffixTypeEnum::Prefix, AffixCategoryEnum::Preposition, 'kaf', 4, 60),
            new AffixRule('ل', AffixTypeEnum::Prefix, AffixCategoryEnum::Preposition, 'lam', 4, 60),
        ];
    }
}
