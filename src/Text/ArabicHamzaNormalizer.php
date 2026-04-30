<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Text;

use Dujana\ArabicNlp\Enums\HamzaStrategyEnum;

final class ArabicHamzaNormalizer
{
    public function normalize(string $text, HamzaStrategyEnum $strategy): string
    {
        return match ($strategy) {
            HamzaStrategyEnum::PreserveSeat => $text,

            HamzaStrategyEnum::Search => $this->search($text),

            HamzaStrategyEnum::Morphology => $this->morphology($text),

            HamzaStrategyEnum::UnifyHamza => $this->unify($text),
        };
    }

    public function search(string $text): string
    {
        return str_replace(
            ['أ', 'إ', 'آ', 'ٱ', 'ؤ', 'ئ'],
            ['ا', 'ا', 'ا', 'ا', 'و', 'ي'],
            $text,
        );
    }

    public function morphology(string $text): string
    {
        return str_replace(
            ['أ', 'إ', 'آ', 'ٱ'],
            ['ا', 'ا', 'ا', 'ا'],
            $text,
        );
    }

    public function unify(string $text): string
    {
        return str_replace(
            ['أ', 'إ', 'آ', 'ٱ', 'ؤ', 'ئ'],
            ['ء', 'ء', 'ء', 'ء', 'ء', 'ء'],
            $text,
        );
    }
}
