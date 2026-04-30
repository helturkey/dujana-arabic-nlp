<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;
use Dujana\ArabicNlp\Lexicon\Database\LexiconLookup;
use Dujana\ArabicNlp\Morphology\Root\DatabaseRootExtractor;

it('reads roots from the generated dujana sqlite lexicon', function (): void {
    $path = getcwd().'/storage/app/dujana/dujana-lexicon.sqlite';

    if (! file_exists($path)) {
        test()->markTestSkipped("Lexicon DB does not exist at [{$path}]. Run dujana:lexicon:build first.");
    }

    $lookup = new LexiconLookup(new LexiconDatabase($path));

    $entries = $lookup->lookup('مدارس');

    expect($entries)->not->toBeEmpty();

    $extractor = new DatabaseRootExtractor($lookup);

    $candidates = $extractor->extract(new CoreCandidate(originalSurface: 'مدارس', normalized: 'مدارس', core: 'مدارس'));

    expect($candidates)->not->toBeEmpty()
        ->and($candidates[0]->root)->toBeString();
});
