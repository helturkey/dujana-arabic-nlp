<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\RootSourcePolicy;

it('prioritizes trusted manual and database sources above rules and fallbacks', function (): void {
    $policy = new RootSourcePolicy;

    expect($policy->priority('manual_lexicon'))->toBeGreaterThan($policy->priority('database'))
        ->and($policy->priority('database'))->toBeGreaterThan($policy->priority('rule:masdar_triliteral_tf3eel'))
        ->and($policy->priority('rule:masdar_triliteral_tf3eel'))->toBeGreaterThan($policy->priority('scale'))
        ->and($policy->priority('scale'))->toBeGreaterThan($policy->priority('fallback_core'));
});

it('treats manual database and rule candidates as authoritative when confidence is high enough', function (string $source): void {
    $policy = new RootSourcePolicy;

    expect($policy->isAuthoritative($source, 0.90))->toBeTrue();
})->with([
    'manual_lexicon',
    'database',
    'rule:verb_tf33la',
    'rule:masdar_triliteral_tf3eel',
    'rule:active_participle_mstf3l',
    'rule:passive_participle_mf33l',
    'rule:instrument_mf3la',
    'rule:place_time_mf3l',
]);

it('does not treat fallbacks or alternatives as authoritative even with high confidence', function (string $source): void {
    $policy = new RootSourcePolicy;

    expect($policy->isAuthoritative($source, 0.95))->toBeFalse();
})->with([
    'scale',
    'fallback_core',
    'database_alternative',
]);

it('does not treat low-confidence rule candidates as authoritative', function (): void {
    $policy = new RootSourcePolicy;

    expect($policy->isAuthoritative('rule:verb_triliteral_f3l', 0.89))->toBeFalse()
        ->and($policy->isAuthoritative('rule:verb_triliteral_f3l', 0.90))->toBeTrue();
});

it('exposes authoritative status through root candidate', function (): void {
    $ruleCandidate = new RootCandidate(
        root: 'كتب',
        confidence: 0.90,
        source: 'rule:verb_triliteral_f3l',
        reasons: [],
    );

    $scaleCandidate = new RootCandidate(
        root: 'كتب',
        confidence: 0.95,
        source: 'scale',
        reasons: [],
    );

    expect($ruleCandidate->isAuthoritative())->toBeTrue()
        ->and($scaleCandidate->isAuthoritative())->toBeFalse();
});
