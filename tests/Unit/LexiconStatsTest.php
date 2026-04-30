<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;
use Dujana\ArabicNlp\Lexicon\Database\LexiconStatsRunner;

it('returns lexicon database statistics summary', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'dujana_stats_').'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);

    $builder->begin();

    $builder->add(
        source: 'manual',
        form: 'كتب',
        root: 'كتب',
        lemma: 'كتب',
        posCat: 'verb',
        pos: 'فعل',
        language: 'fusha',
        confidence: 1.0,
    );

    $builder->add(
        source: 'manual',
        form: 'كتاب',
        root: 'كتب',
        lemma: 'كتاب',
        posCat: 'noun',
        pos: 'اسم',
        language: 'fusha',
        confidence: 0.95,
    );

    $builder->write(clear: false);

    $stats = (new LexiconStatsRunner)->stats($path);

    expect($stats->databasePath)->toBe($path)
        ->and($stats->totals['entries'])->toBe(2)
        ->and($stats->totals['sources'])->toBe(2)
        ->and($stats->totals['with_root'])->toBe(2)
        ->and($stats->totals['without_root'])->toBe(0)
        ->and($stats->sources)->toContain([
            'source' => 'manual',
            'count' => 2,
        ])
        ->and($stats->posCategories)->toContain([
            'pos_cat' => 'verb',
            'count' => 1,
        ])
        ->and($stats->posCategories)->toContain([
            'pos_cat' => 'noun',
            'count' => 1,
        ])
        ->and($stats->languages)->toContain([
            'language' => 'fusha',
            'count' => 2,
        ]);

    @unlink($path);
});
