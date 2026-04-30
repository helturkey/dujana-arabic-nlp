<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Classification;

use Dujana\ArabicNlp\Enums\ArabicTokenTypeEnum;

final readonly class ArabicTokenClassification
{
    public function __construct(
        public string $token,
        public ArabicTokenTypeEnum $type,
        public bool $protected,
        public ?string $reason = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'type' => $this->type->value,
            'protected' => $this->protected,
            'reason' => $this->reason,
        ];
    }
}
