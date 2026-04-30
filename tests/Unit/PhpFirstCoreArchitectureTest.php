<?php

declare(strict_types=1);

use PHPUnit\Framework\Assert;

it('keeps src core free from Laravel global helpers', function (): void {
    $root = realpath(__DIR__.'/../../src');

    Assert::assertNotFalse($root);

    $forbidden = [
        'app(',
        'base_path(',
        'storage_path(',
        'config(',
        'resource_path(',
        'database_path(',
    ];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root)
    );

    foreach ($files as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $path = $file->getPathname();

        if (
            str_contains($path, DIRECTORY_SEPARATOR.'Laravel'.DIRECTORY_SEPARATOR)
            || str_contains($path, DIRECTORY_SEPARATOR.'Console'.DIRECTORY_SEPARATOR)
        ) {
            continue;
        }

        $content = file_get_contents($path);

        foreach ($forbidden as $helper) {
            Assert::assertStringNotContainsString(
                $helper,
                $content,
                "Forbidden Laravel helper [{$helper}] found in [{$path}]"
            );
        }
    }
});
