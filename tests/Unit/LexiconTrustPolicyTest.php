<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;
use Dujana\ArabicNlp\Lexicon\Database\LexiconLookup;
use Dujana\ArabicNlp\Lexicon\Database\LexiconTrustPolicy;

function makeTrustPolicyEntry(array $rows): object
{
    $path = sys_get_temp_dir().'/dujana-trust-policy-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);

    foreach ($rows as $row) {
        $builder->add(
            source: $row['source'],
            form: $row['form'],
            root: $row['root'],
            lemma: $row['lemma'] ?? $row['form'],
            posCat: $row['pos_cat'] ?? 'اسم',
            pos: $row['pos'] ?? 'اسم',
            language: $row['language'] ?? 'فصحى',
            confidence: $row['confidence'] ?? 0.80,
        );
    }

    $builder->write(clear: false);

    return (new LexiconLookup($database))->lookup($rows[0]['form'])[0];
}

it('rejects very short forms for database root lookup', function (): void {
    $policy = new LexiconTrustPolicy;

    expect($policy->shouldUseForm('ان'))->toBeFalse()
        ->and($policy->shouldUseForm('اس'))->toBeFalse()
        ->and($policy->shouldUseForm('اب'))->toBeFalse()
        ->and($policy->shouldUseForm('بحور'))->toBeTrue();
});

it('adds manual and multiple source trust reasons', function (): void {
    $entry = makeTrustPolicyEntry([
        ['source' => 'qabas', 'form' => 'بحور', 'root' => 'بحر', 'confidence' => 0.88],
        ['source' => 'manual', 'form' => 'بحور', 'root' => 'بحر', 'confidence' => 0.98],
    ]);

    $policy = new LexiconTrustPolicy;

    expect($policy->confidenceFor($entry))->toBe(0.99)
        ->and($policy->reasonsFor($entry))->toContain('trust:manual_source_bonus')
        ->and($policy->reasonsFor($entry))->toContain('trust:multiple_sources_bonus');
});

it('penalizes conflicting alternatives', function (): void {
    $entry = makeTrustPolicyEntry([
        ['source' => 'qabas', 'form' => 'ابواب', 'root' => 'باب', 'confidence' => 0.80],
        ['source' => 'arramooz', 'form' => 'ابواب', 'root' => 'بوب', 'confidence' => 0.80],
    ]);

    $policy = new LexiconTrustPolicy;

    expect($entry->alternatives)->not->toBeEmpty()
        ->and($policy->reasonsFor($entry))->toContain('trust:alternatives_penalty')
        ->and($policy->confidenceFor($entry))->toBeLessThan(0.83);
});

it('reports conflicting alternatives in trust reasons', function (): void {
    $entry = makeTrustPolicyEntry([
        ['source' => 'qabas', 'form' => 'ابواب', 'root' => 'باب', 'confidence' => 0.94],
        ['source' => 'manual', 'form' => 'ابواب', 'root' => 'بوب', 'confidence' => 0.98],
    ]);

    $policy = new LexiconTrustPolicy;

    expect($entry->alternatives)->not->toBeEmpty()
        ->and($policy->reasonsFor($entry))->toContain('trust:alternatives_penalty');
});

it('keeps database alternative confidence below reliable threshold', function (): void {
    $policy = new LexiconTrustPolicy;

    expect($policy->alternativeConfidence(0.98))->toBe(0.79)
        ->and($policy->alternativeConfidence(0.94))->toBe(0.79)
        ->and($policy->alternativeConfidence(0.88))->toBe(0.73)
        ->and($policy->alternativeConfidence(0.33))->toBe(0.30);
});
