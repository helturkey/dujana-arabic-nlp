<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Clitics;

use Dujana\ArabicNlp\Enums\AffixCategoryEnum;
use Dujana\ArabicNlp\Enums\AffixTypeEnum;

final class SuffixRules
{
    /** @return list<AffixRule> */
    public static function light(): array
    {
        return [];
    }

    /** @return list<AffixRule> */
    public static function moderate(): array
    {
        return [
            new AffixRule('تين', AffixTypeEnum::Suffix, AffixCategoryEnum::Dual, 'dual_feminine', 3, 10),
            new AffixRule('يات', AffixTypeEnum::Suffix, AffixCategoryEnum::Plural, 'plural_yat', 3, 10),
            new AffixRule('كما', AffixTypeEnum::Suffix, AffixCategoryEnum::Pronoun, 'pronoun_kuma', 3, 20),
            new AffixRule('ون', AffixTypeEnum::Suffix, AffixCategoryEnum::Plural, 'plural_wun', 3, 30),
            new AffixRule('ين', AffixTypeEnum::Suffix, AffixCategoryEnum::Plural, 'plural_yin', 3, 30),
            new AffixRule('ات', AffixTypeEnum::Suffix, AffixCategoryEnum::Plural, 'plural_at', 3, 30),
            new AffixRule('ان', AffixTypeEnum::Suffix, AffixCategoryEnum::Dual, 'dual_an', 3, 30),
            new AffixRule('هم', AffixTypeEnum::Suffix, AffixCategoryEnum::Pronoun, 'pronoun_hum', 3, 40),
            new AffixRule('ها', AffixTypeEnum::Suffix, AffixCategoryEnum::Pronoun, 'pronoun_ha', 3, 40),
            new AffixRule('هن', AffixTypeEnum::Suffix, AffixCategoryEnum::Pronoun, 'pronoun_hunna', 3, 40),
            new AffixRule('كم', AffixTypeEnum::Suffix, AffixCategoryEnum::Pronoun, 'pronoun_kum', 3, 40),
            new AffixRule('كن', AffixTypeEnum::Suffix, AffixCategoryEnum::Pronoun, 'pronoun_kunna', 3, 40),
            new AffixRule('نا', AffixTypeEnum::Suffix, AffixCategoryEnum::Pronoun, 'pronoun_na', 3, 40),
            // new AffixRule('ة', AffixTypeEnum::Suffix, AffixCategoryEnum::Feminine, 'taa_marbuta', 3, 90),
            new AffixRule('ه', AffixTypeEnum::Suffix, AffixCategoryEnum::Pronoun, 'pronoun_h', 3, 100),
            new AffixRule('ك', AffixTypeEnum::Suffix, AffixCategoryEnum::Pronoun, 'pronoun_k', 3, 100),
            new AffixRule('ي', AffixTypeEnum::Suffix, AffixCategoryEnum::Pronoun, 'pronoun_y', 3, 100),
        ];
    }
}
