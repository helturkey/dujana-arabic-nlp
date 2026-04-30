<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;
use Dujana\ArabicNlp\Lexicon\Database\LexiconLookup;

it('looks up entries with sources and alternatives data shape', function (): void {
    $path = sys_get_temp_dir().'/dujana-lookup-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);

    $builder->add('qabas', 'أبواب', 'باب', 'أبواب', confidence: 0.94);
    $builder->add('manual', 'ابواب', 'بوب', 'ابواب', confidence: 0.98);

    $builder->write();

    $entries = (new LexiconLookup($database))->lookup('ابواب');

    expect($entries)->toHaveCount(1);

    $array = $entries[0]->toArray();

    expect($array['normalized_form'])->toBe('ابواب')
        ->and($array['root'])->toBe('بوب')
        ->and($array['sources'])->toHaveCount(2)
        ->and($array['alternatives'])->not->toBeEmpty()
        ->and($array['alternatives'][0]['root'])->toBe('باب');
});
