<?php

declare(strict_types=1);

it('keeps manual overrides separate from root evaluation fixtures', function (): void {
    $root = dirname(__DIR__, 2);

    $manualPath = $root.'/resources/lexicon/manual-roots.tsv';
    $evaluationPath = $root.'/tests/Fixtures/root-evaluation.alpha.tsv';

    expect(file_exists($manualPath))->toBeTrue('Missing manual overrides file.')
        ->and(file_exists($evaluationPath))->toBeTrue('Missing root evaluation fixture.');

    $manual = (string) file_get_contents($manualPath);
    $evaluation = (string) file_get_contents($evaluationPath);

    expect($manual)->not->toStartWith('word')
        ->and($evaluation)->toStartWith('word');
});

it('contains manual overrides for known dangerous reliable errors', function (): void {
    $root = dirname(__DIR__, 2);
    $manual = (string) file_get_contents($root.'/resources/lexicon/manual-roots.tsv');

    expect($manual)->toContain("أسماء\tاسم")
        ->and($manual)->toContain("عباسي\tعباس")
        ->and($manual)->toContain("العباسي\tعباس");
});
