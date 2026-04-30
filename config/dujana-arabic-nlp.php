<?php

declare(strict_types=1);

return [
    'default_mode' => 'moderate',

    'min_word_length' => 2,

    'min_confidence' => 1.25,

    'fallback_to_normalized_on_low_confidence' => true,

    'protect_stop_words' => true,

    'protect_proper_names' => true,

    'protect_non_stemmable' => true,

    'expose_trace' => env('DUJANA_ARABIC_NLP_EXPOSE_TRACE', false),

    'max_input_length' => 10000,
    'max_token_length' => 128,

    /*
|--------------------------------------------------------------------------
| Optional unified lexicon database
|--------------------------------------------------------------------------
|
| This database is generated locally by:
|
| php artisan dujana:lexicon:build
|
| It is optional. If the file does not exist, Dujana will continue using
| the built-in rule-based root extractors and safe fallback candidates.
|
*/

    'lexicon_database' => env(
        'DUJANA_ARABIC_NLP_LEXICON_DB',
        function_exists('storage_path')
            ? storage_path('app/dujana/dujana-lexicon.sqlite')
            : null
    ),

    'lexicon' => [
        'stop_words' => function_exists('resource_path')
            ? resource_path('vendor/dujana-arabic-nlp/stopwords.txt')
            : null,

        'proper_names' => function_exists('resource_path')
            ? resource_path('vendor/dujana-arabic-nlp/proper-names.txt')
            : null,

        'non_stemmable' => function_exists('resource_path')
            ? resource_path('vendor/dujana-arabic-nlp/non-stemmable.txt')
            : null,
    ],
];
