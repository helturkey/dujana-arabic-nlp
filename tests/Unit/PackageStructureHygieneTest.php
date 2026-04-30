<?php

declare(strict_types=1);

it('does not contain macOS metadata files', function (): void {
    $root = realpath(__DIR__.'/../..');

    expect($root)->not->toBeFalse();

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root)
    );

    foreach ($files as $file) {
        if (! $file->isFile()) {
            continue;
        }

        expect($file->getFilename(), $file->getPathname())->not->toBe('.DS_Store');
    }
});

it('keeps Laravel integration isolated from PHP-first core directories', function (): void {
    $allowedLaravelPaths = [
        DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Laravel'.DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Console'.DIRECTORY_SEPARATOR,
    ];

    $root = realpath(__DIR__.'/../../src');

    expect($root)->not->toBeFalse();

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root)
    );

    foreach ($files as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $path = $file->getPathname();
        $content = file_get_contents($path) ?: '';

        $usesLaravel = str_contains($content, 'Illuminate\\')
            || str_contains($content, 'storage_path(')
            || str_contains($content, 'base_path(')
            || str_contains($content, 'config(')
            || str_contains($content, 'app(');

        if (! $usesLaravel) {
            continue;
        }

        $isAllowed = false;

        foreach ($allowedLaravelPaths as $allowedPath) {
            if (str_contains($path, $allowedPath)) {
                $isAllowed = true;
                break;
            }
        }

        expect($isAllowed, "Laravel dependency found outside integration layer: {$path}")->toBeTrue();
    }
});
