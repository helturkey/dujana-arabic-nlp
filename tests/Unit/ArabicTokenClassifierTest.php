<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Classification\ArabicTokenClassifier;
use Dujana\ArabicNlp\Enums\ArabicTokenTypeEnum;

it('classifies function words and short tokens', function (string $token, ArabicTokenTypeEnum $type, bool $protected, ?string $reason): void {
    $classification = (new ArabicTokenClassifier)->classify($token);

    expect($classification->token)->toBe(trim($token))
        ->and($classification->type)->toBe($type)
        ->and($classification->protected)->toBe($protected)
        ->and($classification->reason)->toBe($reason);
})->with([
    ['', ArabicTokenTypeEnum::Empty, true, 'empty_token'],
    ['،', ArabicTokenTypeEnum::Punctuation, true, 'punctuation'],
    ['123', ArabicTokenTypeEnum::Number, true, 'number'],
    ['ال', ArabicTokenTypeEnum::DefiniteArticle, true, 'definite_article'],
    ['و', ArabicTokenTypeEnum::SingleLetterParticle, true, 'single_letter_particle'],
    ['ف', ArabicTokenTypeEnum::SingleLetterParticle, true, 'single_letter_particle'],
    ['من', ArabicTokenTypeEnum::Particle, true, 'particle'],
    ['في', ArabicTokenTypeEnum::Particle, true, 'particle'],
    ['مد', ArabicTokenTypeEnum::ShortUnknown, true, 'short_unknown'],
    ['كتاب', ArabicTokenTypeEnum::Word, false, null],
]);

it('serializes token classification to array', function (): void {
    $classification = (new ArabicTokenClassifier)->classify('من');

    expect($classification->toArray())->toBe([
        'token' => 'من',
        'type' => 'particle',
        'protected' => true,
        'reason' => 'particle',
    ]);
});
