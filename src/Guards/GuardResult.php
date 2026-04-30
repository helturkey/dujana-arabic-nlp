<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Guards;

use Dujana\ArabicNlp\Enums\GuardReasonEnum;

final readonly class GuardResult
{
    public function __construct(
        public bool $protected,
        public ?GuardReasonEnum $reason = null,
    ) {}

    public static function pass(): self
    {
        return new self(false);
    }

    public static function protect(GuardReasonEnum $reason): self
    {
        return new self(true, $reason);
    }
}
