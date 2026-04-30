<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Enums;

enum StemmerModeEnum: string
{
    case Light = 'light';
    case Moderate = 'moderate';
    case Root = 'root';

    public static function fromLegacy(string $level): self
    {
        return match ($level) {
            'light' => self::Light,
            'root' => self::Root,
            default => self::Moderate,
        };
    }
}
