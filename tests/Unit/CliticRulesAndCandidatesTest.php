<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Candidates\CoreCandidateGenerator;
use Dujana\ArabicNlp\Candidates\CoreCandidateRanker;
use Dujana\ArabicNlp\Clitics\PrefixRules;
use Dujana\ArabicNlp\Clitics\SuffixRules;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

beforeEach(function (): void {
    $this->generator = new CoreCandidateGenerator;
    $this->ranker = new CoreCandidateRanker;
});

it('keeps prefix rules ordered with compound article prefixes first', function (): void {
    $values = array_map(static fn ($rule): string => $rule->value, PrefixRules::moderate());

    expect($values)->toContain('وبال', 'وكال', 'فبال', 'وال', 'فال', 'بال', 'كال', 'ال', 'و', 'ف', 'ب', 'ك', 'ل')
        ->and(array_search('وبال', $values, true))->toBeLessThan(array_search('وال', $values, true))
        ->and(array_search('وال', $values, true))->toBeLessThan(array_search('و', $values, true));
});

it('keeps light suffix rules empty by design', function (): void {
    expect(SuffixRules::light())->toBe([]);
});

it('keeps moderate suffix rules for pronouns plurals and duals', function (): void {
    $values = array_map(static fn ($rule): string => $rule->value, SuffixRules::moderate());

    expect($values)->toContain('هم', 'ها', 'كم', 'نا', 'ون', 'ين', 'ات', 'ان', 'تين', 'ه', 'ي')
        ->and($values)->not->toContain('ة');
});

it('chooses safe candidate for article prefixes', function (string $word, string $core, array $proclitics): void {
    $winner = $this->ranker->rank(
        $this->generator->generate($word, StemmerModeEnum::Moderate),
        StemmerModeEnum::Moderate
    )[0];

    expect($winner->core)->toBe($core)
        ->and($winner->proclitics)->toBe($proclitics);
})->with([
    ['والكتاب', 'كتاب', ['وال']],
    ['فالكتاب', 'كتاب', ['فال']],
    ['بالمدرسة', 'مدرسة', ['بال']],
    ['وبالكتاب', 'كتاب', ['وبال']],
]);

it('does not treat lexical kaf as removable prefix', function (string $word, string $expectedCore): void {
    $winner = $this->ranker->rank(
        $this->generator->generate($word, StemmerModeEnum::Moderate),
        StemmerModeEnum::Moderate
    )[0];

    expect($winner->core)->toBe($expectedCore);
})->with([
    ['كتابه', 'كتاب'],
    ['كتابهم', 'كتاب'],
    ['كريم', 'كريم'],
    ['كبير', 'كبير'],
]);

it('normalizes bound taa before pronoun and dual suffixes in candidates', function (string $word, string $core, array $enclitics): void {
    $winner = $this->ranker->rank(
        $this->generator->generate($word, StemmerModeEnum::Moderate),
        StemmerModeEnum::Moderate
    )[0];

    expect($winner->core)->toBe($core)
        ->and($winner->enclitics)->toBe($enclitics);
})->with([
    ['بمدرستهم', 'مدرس', ['هم']],
    ['مدرستهم', 'مدرس', ['هم']],
    ['مدرستان', 'مدرس', ['ان']],
    ['مدرستين', 'مدرس', ['تين']],
]);

it('light candidate generation does not remove suffixes', function (): void {
    $winner = $this->ranker->rank(
        $this->generator->generate('والمدرسة', StemmerModeEnum::Light),
        StemmerModeEnum::Light
    )[0];

    expect($winner->core)->toBe('مدرسة')
        ->and($winner->proclitics)->toBe(['وال'])
        ->and($winner->enclitics)->toBe([]);
});
