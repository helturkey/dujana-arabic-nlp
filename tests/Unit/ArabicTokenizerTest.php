<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Text\ArabicTokenizer;

beforeEach(function (): void {
    $this->tokenizer = new ArabicTokenizer;
});

it('tokenizes Arabic words and ignores punctuation', function (): void {
    expect($this->tokenizer->tokenize('والكتاب، كتابه.'))->toBe(['والكتاب', 'كتابه'])
        ->and($this->tokenizer->tokenize('قال: والكتاب؛ ثم كتابه؟'))->toBe(['قال', 'والكتاب', 'ثم', 'كتابه']);
});

it('tokenizes mixed text without returning Latin or numbers', function (): void {
    expect($this->tokenizer->tokenize('123 والكتاب HTML كتابه!'))->toBe(['والكتاب', 'كتابه']);
});

it('keeps Arabic diacritics inside Arabic token matches', function (): void {
    expect($this->tokenizer->tokenize('قَالَ: كِتَابٌ'))->toBe(['قَالَ', 'كِتَابٌ']);
});
