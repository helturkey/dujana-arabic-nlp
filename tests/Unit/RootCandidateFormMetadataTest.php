<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Morphology\Root\RootCandidate;

it('keeps morphology form metadata nullable for old candidates', function (): void {
    $candidate = new RootCandidate(
        root: 'كتب',
        confidence: 0.40,
        source: 'scale',
        reasons: ['test:no_form'],
    );

    expect($candidate->form)->toBeNull()
        ->and($candidate->toArray()['form'])->toBeNull();
});
