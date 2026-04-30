<?php

it('keeps masdar rules out of verb rule classes', function (): void {
    $files = glob(__DIR__.'/../../src/Morphology/Root/Rules/Verbs/*.php') ?: [];

    foreach ($files as $file) {
        expect(file_get_contents($file))
            ->not->toContain("source: 'rule:masdar");
    }
});
