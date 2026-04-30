<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp;

use Dujana\ArabicNlp\Candidates\CoreCandidateGenerator;
use Dujana\ArabicNlp\Candidates\CoreCandidateRanker;
use Dujana\ArabicNlp\Classification\ArabicTokenClassification;
use Dujana\ArabicNlp\Classification\ArabicTokenClassifier;
use Dujana\ArabicNlp\Classification\WordKindDetector;
use Dujana\ArabicNlp\Classification\WordKindFromRootSourceResolver;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Diagnostics\AnalysisTrace;
use Dujana\ArabicNlp\Enums\NormalizationProfileEnum;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Enums\WordKindEnum;
use Dujana\ArabicNlp\Guards\StemGuard;
use Dujana\ArabicNlp\Lexicon\ArabicLexicon;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;
use Dujana\ArabicNlp\Lexicon\Database\LexiconLookup;
use Dujana\ArabicNlp\Stemming\LightStemmer;
use Dujana\ArabicNlp\Stemming\ModerateStemmer;
use Dujana\ArabicNlp\Stemming\RootStemmer;
use Dujana\ArabicNlp\Text\ArabicNormalizer;
use Dujana\ArabicNlp\Text\ArabicTokenizer;
use InvalidArgumentException;

final class ArabicAnalyzer
{
    private readonly ArabicTokenClassifier $tokenClassifier;

    public function __construct(
        private readonly ArabicNlpConfig $config,
        private readonly ArabicNormalizer $normalizer,
        private readonly ArabicTokenizer $tokenizer,
        private readonly WordKindDetector $wordKindDetector,
        private readonly StemGuard $guard,
        private readonly CoreCandidateGenerator $candidateGenerator,
        private readonly CoreCandidateRanker $candidateRanker,
        private readonly LightStemmer $lightStemmer,
        private readonly ModerateStemmer $moderateStemmer,
        private readonly RootStemmer $rootStemmer,
        ?ArabicTokenClassifier $tokenClassifier = null,
        private readonly WordKindFromRootSourceResolver $wordKindFromRootSourceResolver = new WordKindFromRootSourceResolver,
    ) {
        $this->tokenClassifier = $tokenClassifier ?? new ArabicTokenClassifier;
    }

    public static function make(?ArabicNlpConfig $config = null): self
    {
        $config ??= new ArabicNlpConfig(
            stopWordsPath: dirname(__DIR__).'/resources/lexicon/stopwords.txt',
            properNamesPath: dirname(__DIR__).'/resources/lexicon/proper-names.txt',
            nonStemmablePath: dirname(__DIR__).'/resources/lexicon/non-stemmable.txt',
        );

        $normalizer = new ArabicNormalizer;
        $lexicon = ArabicLexicon::make($config, $normalizer);

        $lookup = null;

        if (
            $config->lexiconDatabasePath !== null
            && is_file($config->lexiconDatabasePath)
        ) {
            $lookup = new LexiconLookup(
                new LexiconDatabase($config->lexiconDatabasePath)
            );
        }

        return new self(
            config: $config,
            normalizer: $normalizer,
            tokenizer: new ArabicTokenizer,
            wordKindDetector: new WordKindDetector,
            guard: new StemGuard($config, $lexicon),
            candidateGenerator: new CoreCandidateGenerator,
            candidateRanker: new CoreCandidateRanker,
            lightStemmer: new LightStemmer,
            moderateStemmer: new ModerateStemmer,
            rootStemmer: RootStemmer::make($lookup),
        );
    }

    public function normalize(string $text, ?NormalizationProfileEnum $profile = null): string
    {
        return $this->normalizer->normalize(
            $text,
            $profile ?? NormalizationProfileEnum::Search,
        );
    }

    /** @return list<string> */
    public function tokenize(string $text): array
    {
        $this->assertTextLength($text);

        return $this->tokenizer->tokenize($text);
    }

    public function stem(string $word, ?StemmerModeEnum $mode = null): string
    {
        return $this->analyze($word, $mode)->stem;
    }

    public function analyze(string $word, ?StemmerModeEnum $mode = null): ArabicAnalysis
    {
        $this->assertTokenLength($word);

        $mode ??= $this->config->defaultMode;
        $original = trim($word);
        $trace = new AnalysisTrace;

        $normalized = $this->normalizer->normalize(
            $word,
            $mode === StemmerModeEnum::Root
                ? NormalizationProfileEnum::Morphology
                : NormalizationProfileEnum::Stemming
        );

        $trace->add('normalize', 'applied', [
            'original' => $original,
            'normalized' => $normalized,
        ]);

        $classification = $this->tokenClassifier->classify($normalized);

        $trace->add('classification', 'resolved', [
            'type' => $classification->type->value,
            'protected' => $classification->protected,
            'reason' => $classification->reason,
        ]);

        $guard = $this->guard->check(
            original: $original,
            normalized: $normalized,
            mode: $mode,
            classification: $classification,
        );

        if ($guard->protected) {
            $trace->add('guard', 'protected', [
                'reason' => $guard->reason?->value,
            ]);

            return new ArabicAnalysis(
                original: $original,
                normalized: $normalized,
                stem: $normalized,
                mode: $mode,
                protected: true,
                protectionReason: $guard->reason?->value,
                proclitics: [],
                enclitics: [],
                wordKind: WordKindEnum::Particle,
                pattern: null,
                root: null,
                verbPattern: null,
                confidence: 1.0,
                trace: $trace,
                rootAnalysis: null,
                classification: $classification,
            );
        }

        $candidates = $this->candidateGenerator->generate($normalized, $mode, $original);
        $best = $this->candidateRanker->best($candidates, $mode);
        $wordKind = null;

        $rootAnalysis = null;
        $resolvedRoot = null;

        if ($mode === StemmerModeEnum::Root) {
            $rootAnalysis = $this->rootStemmer->analyze($best);

            $wordKind = $this->wordKindFromRootSourceResolver->resolve($rootAnalysis->best)
                ?? $this->wordKindDetector->detect($best->core);

            /*
             * Keep rootOr() for backward compatibility.
             *
             * It allows legacy supported cases such as:
             * كتاب => كتب
             * يكتب => كتب
             * يقول => قول
             *
             * Some of these may be useful but not "authoritative" under the newer
             * reliability policy, so do not rely only on reliable() here.
             */
            $resolvedRoot = $rootAnalysis->rootOr($best->core);
            $stem = $resolvedRoot;
        } else {
            $wordKind = $this->wordKindDetector->detect($best->core);

            $stem = $mode === StemmerModeEnum::Light
                ? $this->lightStemmer->stem($best)
                : $this->moderateStemmer->stem($best);
        }

        if (
            $this->config->fallbackToNormalizedOnLowConfidence
            && in_array($mode, [StemmerModeEnum::Moderate], true)
            && $best->score < $this->config->minConfidence
        ) {
            $trace->add('fallback', 'low_confidence_normalized', [
                'score' => $best->score,
                'threshold' => $this->config->minConfidence,
                'candidate_stem' => $stem,
            ]);

            $stem = $normalized;
        }

        $trace->add('candidate', 'selected', [
            'core' => $best->core,
            'proclitics' => $best->proclitics,
            'enclitics' => $best->enclitics,
            'score' => $best->score,
            'reasons' => $best->reasons,
        ]);

        $trace->add('stem', 'resolved', [
            'mode' => $mode->value,
            'stem' => $stem,
        ]);

        if ($mode === StemmerModeEnum::Root) {
            $trace->add('root', 'resolved', [
                'root' => $rootAnalysis->best->root,
                'source' => $rootAnalysis->best->source,
                'confidence' => $rootAnalysis->best->confidence,
                'reliable' => $rootAnalysis->reliable(),
                'candidates_count' => count($rootAnalysis->candidates ?: []),
            ]);
        }

        return new ArabicAnalysis(
            original: $original,
            normalized: $normalized,
            stem: $stem,
            root: $mode === StemmerModeEnum::Root ? $resolvedRoot : null,
            mode: $mode,
            protected: false,
            protectionReason: null,
            proclitics: $best->proclitics,
            enclitics: $best->enclitics,
            wordKind: $wordKind,
            confidence: $mode === StemmerModeEnum::Root
                ? ($rootAnalysis?->best->confidence ?? $best->score)
                : $best->score,
            trace: $trace,
            rootAnalysis: $rootAnalysis,
            classification: $classification,
        );
    }

    public function classify(string $word): ArabicTokenClassification
    {
        $normalized = $this->normalizer->normalize($word);

        return $this->tokenClassifier->classify($normalized);
    }

    public function detectWordKind(string $word): WordKindEnum
    {
        $normalized = $this->normalizer->normalize($word);

        return $this->wordKindDetector->detect($normalized);
    }

    /** @param list<string> $words @return list<string> */
    public function stemMultiple(array $words, ?StemmerModeEnum $mode = null): array
    {
        return array_map(fn (string $word): string => $this->stem($word, $mode), $words);
    }

    /** @return list<string> */
    public function stemSentence(string $sentence, ?StemmerModeEnum $mode = null): array
    {
        return $this->stemMultiple($this->tokenizer->tokenize($sentence), $mode);
    }

    public function stemText(string $text, ?StemmerModeEnum $mode = null): string
    {
        return $this->stemSentenceAsString($text, $mode);
    }

    public function stemSentenceAsString(string $sentence, ?StemmerModeEnum $mode = null): string
    {
        return implode(' ', $this->stemSentence($sentence, $mode));
    }

    private function assertTextLength(string $text): void
    {
        if (mb_strlen($text) > $this->config->maxInputLength) {
            throw new InvalidArgumentException(sprintf(
                'Input exceeds the configured maximum length of %d characters.',
                $this->config->maxInputLength,
            ));
        }
    }

    private function assertTokenLength(string $token): void
    {
        if (mb_strlen($token) > $this->config->maxTokenLength) {
            throw new InvalidArgumentException(sprintf(
                'Token exceeds the configured maximum length of %d characters.',
                $this->config->maxTokenLength,
            ));
        }
    }
}
