<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Contracts\Morphology\RootExtractorContract;
use Dujana\ArabicNlp\Lexicon\Database\LexiconLookup;
use Dujana\ArabicNlp\Lexicon\Database\LexiconTrustPolicy;
use Dujana\ArabicNlp\Text\ArabicNormalizationVariants;

final readonly class DatabaseRootExtractor implements RootExtractorContract
{
    public function __construct(
        private LexiconLookup $lookup,
        private LexiconTrustPolicy $trustPolicy = new LexiconTrustPolicy,
        private ArabicNormalizationVariants $variants = new ArabicNormalizationVariants,
    ) {}

    public function extract(CoreCandidate $candidate): array
    {
        $roots = [];
        $originalForms = $this->variants->forLexiconLookup($candidate->normalized);

        foreach ($this->lookupForms($candidate) as $form) {
            $isOriginalLookup = in_array($form, $originalForms, true);

            foreach ($this->lookup->lookup($form) as $entry) {
                if (! $this->trustPolicy->shouldUseEntry($form, $entry)) {
                    continue;
                }

                foreach ($entry->sources as $source) {
                    if ($source->source !== 'manual' || $source->sourceRoot === null || $source->sourceRoot === '') {
                        continue;
                    }

                    $manualReasons = [
                        'lookup_form:'.$form,
                        'normalized_form:'.$entry->normalizedForm,
                        'manual_source_root',
                        'trust:manual_source_authoritative',
                        $isOriginalLookup
                            ? 'trust:original_surface_lookup_bonus'
                            : 'trust:core_lookup',
                    ];

                    if (mb_strlen($form) < $this->trustPolicy->minLookupFormLength()) {
                        $manualReasons[] = 'trust:manual_short_form_override';
                    }

                    $roots[] = new RootCandidate(
                        root: $source->sourceRoot,
                        confidence: $isOriginalLookup ? 0.99 : 0.98,
                        source: 'manual_lexicon',
                        reasons: $manualReasons,
                    );
                }

                if ($entry->root === null || $entry->root === '') {
                    continue;
                }

                /*
                 * Do not allow compound roots like بوع|بيع to become one
                 * authoritative database root. Split them into alternatives below.
                 */
                if (str_contains($entry->root, '|')) {
                    foreach (explode('|', $entry->root) as $compoundRoot) {
                        $compoundRoot = trim($compoundRoot);

                        if ($compoundRoot === '') {
                            continue;
                        }

                        $roots[] = new RootCandidate(
                            root: $compoundRoot,
                            confidence: $this->trustPolicy->alternativeConfidence($entry->confidence),
                            source: 'database_alternative',
                            reasons: [
                                'lookup_form:'.$form,
                                'normalized_form:'.$entry->normalizedForm,
                                'database:compound_root_split',
                                'trust:alternative_root',
                                'not_authoritative_root',
                            ],
                        );
                    }

                    continue;
                }

                $confidence = $this->trustPolicy->confidenceFor($entry);

                if ($isOriginalLookup) {
                    $confidence = min(0.99, $confidence + 0.02);
                }

                $roots[] = new RootCandidate(
                    root: $entry->root,
                    confidence: $confidence,
                    source: $this->trustPolicy->hasManualSource($entry)
                        ? 'manual_lexicon'
                        : 'database',
                    reasons: array_merge([
                        'lookup_form:'.$form,
                        'normalized_form:'.$entry->normalizedForm,
                        'lemma:'.($entry->lemma ?? 'unknown'),
                        'pos_cat:'.($entry->posCat ?? 'unknown'),
                        'sources:'.implode(',', array_map(
                            static fn ($source): string => $source->source,
                            $entry->sources,
                        )),
                        $isOriginalLookup
                            ? 'trust:original_surface_lookup_bonus'
                            : 'trust:core_lookup',
                    ], $this->trustPolicy->reasonsFor($entry)),
                );

                foreach ($entry->alternatives as $alternative) {
                    $roots[] = new RootCandidate(
                        root: (string) $alternative['root'],
                        confidence: $this->trustPolicy->alternativeConfidence((float) $alternative['confidence']),
                        source: 'database_alternative',
                        reasons: [
                            'lookup_form:'.$form,
                            'normalized_form:'.$entry->normalizedForm,
                            'alternative_sources:'.implode(',', $alternative['sources']),
                            'trust:alternative_root',
                        ],
                    );
                }
            }
        }

        return $roots;
    }

    /**
     * @return list<string>
     */
    private function lookupForms(CoreCandidate $candidate): array
    {
        return array_values(array_unique(array_merge(
            $this->variants->forLexiconLookup($candidate->normalized),
            $this->variants->forLexiconLookup($candidate->core),
        )));
    }
}
