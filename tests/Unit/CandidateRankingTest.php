<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Candidates\CoreCandidateGenerator;
use Dujana\ArabicNlp\Candidates\CoreCandidateRanker;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

beforeEach(function (): void {
    $this->generator = new CoreCandidateGenerator;
    $this->ranker = new CoreCandidateRanker;
});

it('prefers not deleting lexical initial kaf or ba when unsafe', function (string $word, string $core): void {
    $winner = $this->ranker->best($this->generator->generate($word), StemmerModeEnum::Moderate);

    expect($winner->core)->toBe($core);
})->with([
    ['كتابهم', 'كتاب'],
    ['كبير', 'كبير'],
    ['بديع', 'بديع'],
]);

it('returns ranked candidates with scores and reasons', function (): void {
    $ranked = $this->ranker->rank(
        $this->generator->generate('وكتابهم'),
        StemmerModeEnum::Moderate
    );

    expect($ranked)->not->toBeEmpty()
        ->and($ranked[0]->score)->toBeGreaterThan(0)
        ->and($ranked[0]->reasons)->not->toBeEmpty();
});
