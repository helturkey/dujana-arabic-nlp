<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Laravel;

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\ArabicClassifier;
use Dujana\ArabicNlp\ArabicStemmer;
use Dujana\ArabicNlp\ArabicTokenizer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Console\BuildLexiconCommand;
use Dujana\ArabicNlp\Console\LexiconLookupCommand;
use Dujana\ArabicNlp\Console\LexiconStatsCommand;
use Dujana\ArabicNlp\Console\RootEvaluateCommand;
use Dujana\ArabicNlp\Console\RootEvaluateSuiteCommand;
use Illuminate\Support\ServiceProvider;

final class DujanaArabicNlpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/dujana-arabic-nlp.php',
            'dujana-arabic-nlp',
        );

        $this->app->singleton(ArabicNlpConfig::class, function (): ArabicNlpConfig {
            return ArabicNlpConfig::fromArray(config('dujana-arabic-nlp', []));
        });

        $this->app->singleton(ArabicAnalyzer::class, function (): ArabicAnalyzer {
            return ArabicAnalyzer::make(
                $this->app->make(ArabicNlpConfig::class),
            );
        });

        $this->app->singleton(ArabicStemmer::class, function (): ArabicStemmer {
            return new ArabicStemmer(
                $this->app->make(ArabicAnalyzer::class),
            );
        });

        $this->app->alias(ArabicAnalyzer::class, 'dujana-arabic-nlp');
        $this->app->alias(ArabicStemmer::class, 'dujana-arabic-stemmer');

        $this->app->singleton(ArabicClassifier::class, function (): ArabicClassifier {
            return ArabicClassifier::make(
                $this->app->make(ArabicNlpConfig::class),
            );
        });

        $this->app->singleton(ArabicTokenizer::class, function (): ArabicTokenizer {
            return ArabicTokenizer::make();
        });

        $this->app->alias(ArabicClassifier::class, 'dujana-arabic-classifier');
        $this->app->alias(ArabicTokenizer::class, 'dujana-arabic-tokenizer');
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../../config/dujana-arabic-nlp.php' => config_path('dujana-arabic-nlp.php'),
        ], 'dujana-arabic-nlp-config');

        $this->publishes([
            __DIR__.'/../../resources/lexicon' => storage_path('app/dujana/lexicon'),
        ], 'dujana-arabic-nlp-lexicon');

        $this->publishes([
            __DIR__.'/../../resources/evaluation' => storage_path('app/dujana/evaluation-suite'),
        ], 'dujana-arabic-nlp-evaluation');

        $this->publishes([
            __DIR__.'/../../config/dujana-arabic-nlp.php' => config_path('dujana-arabic-nlp.php'),
            __DIR__.'/../../resources/lexicon' => storage_path('app/dujana/lexicon'),
            __DIR__.'/../../resources/evaluation' => storage_path('app/dujana/evaluation-suite'),
        ], 'dujana-arabic-nlp');

        $this->commands([
            BuildLexiconCommand::class,
            LexiconStatsCommand::class,
            LexiconLookupCommand::class,
            RootEvaluateCommand::class,
            RootEvaluateSuiteCommand::class,
        ]);
    }
}
