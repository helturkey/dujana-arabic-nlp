<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Classification;

final class ArabicFunctionWords
{
    public const SINGLE_LETTER_PARTICLES = [
        'و',
        'ف',
        'ب',
        'ك',
        'ل',
        'س',
    ];

    public const PARTICLES = [
        'من',
        'إلى',
        'الى',
        'عن',
        'على',
        'في',
        'ثم',
        'بل',
        'لا',
        'ما',
        'لم',
        'لن',
        'إن',
        'ان',
        'أن',
        'قد',
        'هل',
        'يا',
        'أو',
        'او',
    ];

    public static function isSingleLetterParticle(string $token): bool
    {
        return in_array($token, self::SINGLE_LETTER_PARTICLES, true);
    }

    public static function isParticle(string $token): bool
    {
        return in_array($token, self::PARTICLES, true);
    }
}
