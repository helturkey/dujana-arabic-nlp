<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Evaluation\RootEvaluationLoader;

it('loads root evaluation TSV fixtures', function (): void {
    $path = sys_get_temp_dir().'/dujana-root-eval-'.uniqid().'.tsv';

    file_put_contents($path, implode("\n", [
        "word\texpected_root\tcategory\tnote",
        "بحور\tبحر\tbroken_plural\tok",
        "ززززز\t\tfallback\tshould be unreliable",
        "# comment\t\t\t",
    ]));

    $cases = (new RootEvaluationLoader)->load($path);

    expect($cases)->toHaveCount(2)
        ->and($cases[0]->word)->toBe('بحور')
        ->and($cases[0]->expectedRoot)->toBe('بحر')
        ->and($cases[1]->word)->toBe('ززززز')
        ->and($cases[1]->expectedRoot)->toBeNull();
});
