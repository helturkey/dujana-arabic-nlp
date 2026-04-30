<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Support;

use InvalidArgumentException;

final class SafePath
{
    public static function assertInsideDirectory(string $path, string $baseDirectory, string $label = 'path'): string
    {
        $path = trim($path);
        $baseDirectory = rtrim(trim($baseDirectory), DIRECTORY_SEPARATOR);

        if ($path === '') {
            throw new InvalidArgumentException("{$label} cannot be empty.");
        }

        if ($baseDirectory === '') {
            throw new InvalidArgumentException('Safe base directory cannot be empty.');
        }

        self::ensureDirectory($baseDirectory, 'safe base directory');

        $baseReal = realpath($baseDirectory);

        if ($baseReal === false || ! is_dir($baseReal)) {
            throw new InvalidArgumentException("Cannot resolve safe base directory: {$baseDirectory}");
        }

        /*
         * Relative output paths are resolved inside the safe base directory.
         * Absolute output paths are allowed only if they are already inside it.
         */
        $candidatePath = self::isAbsolutePath($path)
            ? $path
            : $baseReal.DIRECTORY_SEPARATOR.$path;

        $filename = basename($candidatePath);

        if ($filename === '' || $filename === '.' || $filename === '..') {
            throw new InvalidArgumentException("{$label} must include a valid filename.");
        }

        $directory = dirname($candidatePath);

        self::ensureDirectory($directory, 'output directory');

        $directoryReal = realpath($directory);

        if ($directoryReal === false || ! is_dir($directoryReal)) {
            throw new InvalidArgumentException("Cannot resolve output directory: {$directory}");
        }

        if (! self::isInside($directoryReal, $baseReal)) {
            throw new InvalidArgumentException("{$label} must be inside safe directory: {$baseReal}");
        }

        return $directoryReal.DIRECTORY_SEPARATOR.$filename;
    }

    public static function assertReadableFile(string $path, string $label = 'file'): string
    {
        $path = trim($path);

        if ($path === '') {
            throw new InvalidArgumentException("{$label} cannot be empty.");
        }

        if (! is_file($path) || ! is_readable($path)) {
            throw new InvalidArgumentException("{$label} is not readable: {$path}");
        }

        $real = realpath($path);

        if ($real === false || ! is_file($real)) {
            throw new InvalidArgumentException("Cannot resolve {$label}: {$path}");
        }

        return $real;
    }

    private static function ensureDirectory(string $directory, string $label): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (! mkdir($directory, 0770, true) && ! is_dir($directory)) {
            throw new InvalidArgumentException("Cannot create {$label}: {$directory}");
        }
    }

    private static function isInside(string $path, string $baseDirectory): bool
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $baseDirectory = rtrim($baseDirectory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        return str_starts_with($path, $baseDirectory);
    }

    private static function isAbsolutePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        /*
         * Unix absolute path.
         */
        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return true;
        }

        /*
         * Windows absolute path, e.g. C:\path or C:/path.
         */
        return preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1;
    }
}
