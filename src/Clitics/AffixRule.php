<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Clitics;

use Dujana\ArabicNlp\Enums\AffixCategoryEnum;
use Dujana\ArabicNlp\Enums\AffixTypeEnum;

final readonly class AffixRule
{
    public function __construct(
        public string $value,
        public AffixTypeEnum $type,
        public AffixCategoryEnum $category,
        public string $name,
        public int $minStemLength = 3,
        public int $priority = 100,
    ) {}
}
