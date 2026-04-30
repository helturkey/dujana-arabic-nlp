<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\ArabicTokenizer;

it('exposes tokenizer as a standalone public API', function (): void {
    $tokens = ArabicTokenizer::make()->tokenize('أحلامهم كثيرة.');

    expect($tokens)->toContain('أحلامهم');
});

it('exposes tokenizer through analyzer', function (): void {
    $tokens = ArabicAnalyzer::make()->tokenize('أحلامهم كثيرة.');

    expect($tokens)->toContain('أحلامهم');
});
