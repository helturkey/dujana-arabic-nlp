<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicStemmer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

beforeEach(function (): void {
    $this->stemmer = ArabicStemmer::make();
});

it('stems light Arabic article forms conservatively', function (string $word, string $expected): void {
    expect($this->stemmer->stem($word, StemmerModeEnum::Light))->toBe($expected);
})->with([
    ['الكتاب', 'كتاب'],
    ['والكتاب', 'كتاب'],
    ['فالكتاب', 'كتاب'],
    ['والمدرسة', 'مدرسة'],
    ['بالمدرسة', 'مدرسة'],
]);

it('stems moderate article prefix suffix and pronoun forms', function (string $word, string $expected): void {
    expect($this->stemmer->stem($word, StemmerModeEnum::Moderate))->toBe($expected);
})->with([
    ['والكتاب', 'كتاب'],
    ['فالكتاب', 'كتاب'],
    ['بالمدرسة', 'مدرس'],
    ['كتابه', 'كتاب'],
    ['وكتابهم', 'كتاب'],
    ['بمدرستهم', 'مدرس'],
    ['المسلمون', 'مسلم'],
    ['المسلمين', 'مسلم'],
    ['مسلمون', 'مسلم'],
    ['مسلمات', 'مسلم'],
]);

it('stems arrays sentences and text output', function (): void {
    expect($this->stemmer->stemMultiple(['والكتاب', 'كتابه']))->toBe(['كتاب', 'كتاب'])
        ->and($this->stemmer->stemSentence('والكتاب، كتابه.'))->toBe(['كتاب', 'كتاب'])
        ->and($this->stemmer->stemSentenceAsString('والكتاب، كتابه.'))->toBe('كتاب كتاب');

    if (method_exists($this->stemmer, 'stemText')) {
        expect($this->stemmer->stemText('والكتاب، كتابه.'))->toBe('كتاب كتاب');
    }
});

it('returns structured analysis result', function (): void {
    $analysis = $this->stemmer->analyze('وكتابهم');

    expect($analysis->stem)->toBe('كتاب')
        ->and($analysis->proclitics)->toBe(['و'])
        ->and($analysis->enclitics)->toBe(['هم'])
        ->and($analysis->toArray())->toHaveKeys([
            'original',
            'normalized',
            'stem',
            'mode',
            'confidence',
            'trace',
        ]);
});
