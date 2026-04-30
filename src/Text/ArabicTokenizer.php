<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Text;

final class ArabicTokenizer
{
    /** @return list<string> */
    public function tokenize(string $text): array
    {
        $result = preg_match_all('/[\p{Arabic}]+/u', $text, $matches);

        if ($result === false || $result === 0) {
            return [];
        }

        /** @var list<string> $tokens */
        $tokens = $matches[0];

        return array_values(array_filter(array_map(
            static fn (string $token): string => preg_replace(
                '/^[\s\p{P}\p{S}]+|[\s\p{P}\p{S}]+$/u',
                '',
                $token,
            ) ?? '',
            $tokens,
        )));
    }

    /** @return list<string> */
    public function tokens(string $text): array
    {
        return $this->tokenize($text);
    }
}
