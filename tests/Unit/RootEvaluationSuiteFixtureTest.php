<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Evaluation\RootEvaluationLoader;

it('provides multiple root evaluation suite fixtures', function (): void {
    $root = dirname(__DIR__, 2);
    $suite = $root.'/resources/evaluation';

    expect(is_dir($suite))->toBeTrue();

    $files = glob($suite.'/*.tsv') ?: [];

    expect(count($files))->toBeGreaterThanOrEqual(2);

    $loader = new RootEvaluationLoader;

    $total = 0;

    foreach ($files as $file) {
        $total += count($loader->load($file));
    }

    expect($total)->toBeGreaterThanOrEqual(6);
});
