<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Support\SafePath;

it('allows output paths inside the safe base directory', function (): void {
    $base = sys_get_temp_dir().'/dujana_safe_'.bin2hex(random_bytes(4));

    $path = SafePath::assertInsideDirectory('lexicon.sqlite', $base, 'output');

    expect($path)->toStartWith(realpath($base))
        ->and($path)->toEndWith('lexicon.sqlite');

    @rmdir($base);
});

it('rejects output paths outside the safe base directory', function (): void {
    $base = sys_get_temp_dir().'/dujana_safe_'.bin2hex(random_bytes(4));

    expect(fn () => SafePath::assertInsideDirectory('../evil.sqlite', $base, 'output'))
        ->toThrow(InvalidArgumentException::class);

    @rmdir($base);
});
