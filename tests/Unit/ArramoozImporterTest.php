<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;
use Dujana\ArabicNlp\Lexicon\Database\LexiconLookup;
use Dujana\ArabicNlp\Lexicon\Importers\ArramoozImporter;

it('imports nouns and verbs from Arramooz sqlite schema', function (): void {
    $sourcePath = sys_get_temp_dir().'/arramooz-source-'.uniqid().'.sqlite';
    $targetPath = sys_get_temp_dir().'/dujana-target-'.uniqid().'.sqlite';

    $source = new PDO('sqlite:'.$sourcePath);
    $source->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $source->exec(<<<'SQL'
CREATE TABLE nouns (
    id int unique,
    vocalized varchar(30),
    unvocalized varchar(30),
    normalized varchar(30),
    stamped varchar(30),
    wordtype varchar(30),
    root varchar(10),
    wazn varchar(30),
    category varchar(30),
    original varchar(30),
    gender varchar(30),
    feminin varchar(30),
    masculin varchar(30),
    number varchar(30),
    single varchar(30),
    broken_plural varchar(30),
    defined tinyint(1),
    mankous tinyint(1),
    feminable tinyint(1),
    dualable tinyint(1),
    masculin_plural tinyint(1),
    feminin_plural tinyint(1),
    mamnou3_sarf tinyint(1),
    relative tinyint(1),
    w_suffix tinyint(1),
    hm_suffix tinyint(1),
    kal_prefix tinyint(1),
    ha_suffix tinyint(1),
    k_prefix tinyint(1),
    annex tinyint(1),
    definition text,
    note text
);
SQL);

    $source->exec(<<<'SQL'
CREATE TABLE verbs (
    id int unique,
    vocalized varchar(30) not null,
    unvocalized varchar(30) not null,
    root varchar(30),
    normalized varchar(30) not null,
    stamped varchar(30) not null,
    future_type varchar(5),
    triliteral tinyint(1),
    transitive tinyint(1),
    double_trans tinyint(1),
    think_trans tinyint(1),
    unthink_trans tinyint(1),
    reflexive_trans tinyint(1),
    past tinyint(1),
    future tinyint(1),
    imperative tinyint(1),
    passive tinyint(1),
    future_moode tinyint(1),
    confirmed tinyint(1),
    PRIMARY KEY (id)
);
SQL);

    $source->exec("INSERT INTO nouns (id, vocalized, unvocalized, normalized, root, wordtype, category, original, single, broken_plural) VALUES
        (1, 'بُحُورٌ', 'بحور', 'بحور', 'بحر', 'اسم', 'اسم', 'بحور', 'بحر', 'بحور')
    ");

    $source->exec("INSERT INTO verbs (id, vocalized, unvocalized, normalized, root, stamped) VALUES
        (1, 'قَالَ', 'قال', 'قال', 'قول', 'قال')
    ");

    $database = new LexiconDatabase($targetPath);
    $builder = new LexiconBuilder($database);

    $imported = (new ArramoozImporter($builder))->import($sourcePath);
    $written = $builder->write();

    $lookup = new LexiconLookup($database);

    expect($imported)->toBeGreaterThanOrEqual(2)
        ->and($written)->toBeGreaterThanOrEqual(2)
        ->and($lookup->lookup('بحور')[0]->root)->toBe('بحر')
        ->and($lookup->lookup('قال')[0]->root)->toBe('قول');
});
