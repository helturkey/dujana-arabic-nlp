<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Evaluation\RootEvaluationLoader;

it('contains expanded root evaluation fixtures with at least one hundred cases', function (): void {
    $root = dirname(__DIR__, 2);
    $suite = $root.'/resources/evaluation';

    $files = glob($suite.'/*.tsv') ?: [];

    expect($files)->not->toBeEmpty();

    $loader = new RootEvaluationLoader;

    $total = 0;
    $categories = [];

    foreach ($files as $file) {
        foreach ($loader->load($file) as $case) {
            $total++;
            $categories[$case->category ?? 'uncategorized'] = true;
        }
    }

    expect($total)->toBeGreaterThanOrEqual(100)
        ->and(array_keys($categories))->toContain(
            'weak_context',
            'broken_plural',
            'hamza',
            'derived_verb',
            'nisba',
            'noun_pattern',
            'conflict_review',
        );
});

it('keeps expanded fixtures as evaluation files with headers', function (): void {
    $root = dirname(__DIR__, 2);
    $files = glob($root.'/resources/evaluation/*.tsv') ?: [];

    foreach ($files as $file) {
        $content = (string) file_get_contents($file);

        expect($content)
            ->toStartWith('word', "Evaluation fixture [{$file}] must start with the header row.");
    }
});
