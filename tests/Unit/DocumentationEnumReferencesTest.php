<?php

declare(strict_types=1);

it('does not reference old enum namespaces in documentation', function (): void {
    $root = dirname(__DIR__, 2);

    $files = array_filter([
        $root.'/README.md',
        ...glob($root.'/docs/*.md'),
    ], static fn (string $file): bool => is_file($file));

    $forbidden = [
        'Dujana\\ArabicNlp\\StemmerMode',
        'Dujana\\ArabicNlp\\Classification\\WordKind',
        'Dujana\\ArabicNlp\\Text\\NormalizationProfile',
        'Dujana\\ArabicNlp\\Text\\HamzaStrategy',
        'StemmerMode::',
        'WordKind::',
        'NormalizationProfile::',
        'HamzaStrategy::',
    ];

    foreach ($files as $file) {
        $content = (string) file_get_contents($file);

        foreach ($forbidden as $needle) {
            expect($content)
                ->not->toContain($needle, "Forbidden enum reference [{$needle}] found in [{$file}].");
        }
    }
});
