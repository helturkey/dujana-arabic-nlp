<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Text\ArabicNormalizationVariants;

it('builds search and morphology variants for lexicon lookup', function (): void {
    $variants = new ArabicNormalizationVariants;

    expect($variants->forLexiconLookup('أسئلة'))->toContain(
        'أسئلة',
        'اسيلة',
        'اسئلة',
    );

    expect($variants->forLexiconLookup('مسؤول'))->toContain(
        'مسؤول',
        'مسوول',
    );

    expect($variants->forLexiconLookup('مبادئ'))->toContain(
        'مبادئ',
        'مبادي',
    );
});
