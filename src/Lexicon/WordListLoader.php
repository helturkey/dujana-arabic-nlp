<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon;

use Dujana\ArabicNlp\Text\ArabicNormalizer;

final class WordListLoader
{
    /** @var array<string,list<string>> */
    private static array $cache = [];

    public function __construct(private readonly ArabicNormalizer $normalizer) {}

    /** @return list<string> */
    public function load(?string $path): array
    {
        if ($path === null || $path === '' || ! is_file($path)) {
            return [];
        }

        $realPath = realpath($path) ?: $path;

        if (isset(self::$cache[$realPath])) {
            return self::$cache[$realPath];
        }

        $lines = file($realPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return self::$cache[$realPath] = [];
        }

        $words = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $normalized = $this->normalizer->normalize($line);

            if ($normalized !== '') {
                $words[$normalized] = true;
            }
        }

        return self::$cache[$realPath] = array_keys($words);
    }

    public static function clearCache(): void
    {
        self::$cache = [];
    }
}
