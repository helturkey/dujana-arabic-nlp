<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\ArabicClassifier;
use Dujana\ArabicNlp\Classification\ArabicTokenClassification;

it('exposes classification as a standalone public API', function (): void {
    $classification = ArabicClassifier::make()->classify('أَحْلَامَهُمْ');

    expect($classification)->toBeInstanceOf(ArabicTokenClassification::class);
});

it('exposes classification through the analyzer facade object', function (): void {
    $classification = ArabicAnalyzer::make()->classify('أَحْلَامَهُمْ');

    expect($classification)->toBeInstanceOf(ArabicTokenClassification::class);
});
