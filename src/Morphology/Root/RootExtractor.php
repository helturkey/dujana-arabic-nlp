<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Contracts\Morphology\RootExtractorContract;
use Dujana\ArabicNlp\Lexicon\Database\LexiconLookup;
use Dujana\ArabicNlp\Morphology\Root\Rules\Nouns\ActionStateNounRule;
use Dujana\ArabicNlp\Morphology\Root\Rules\Nouns\ActiveParticipleNounRule;
use Dujana\ArabicNlp\Morphology\Root\Rules\Nouns\BrokenPluralNounRule;
use Dujana\ArabicNlp\Morphology\Root\Rules\Nouns\DerivedAdjectiveNounRule;
use Dujana\ArabicNlp\Morphology\Root\Rules\Nouns\InstanceMannerNounRule;
use Dujana\ArabicNlp\Morphology\Root\Rules\Nouns\InstrumentNounRule;
use Dujana\ArabicNlp\Morphology\Root\Rules\Nouns\MasdarNounRule;
use Dujana\ArabicNlp\Morphology\Root\Rules\Nouns\NominalSurfaceNounRule;
use Dujana\ArabicNlp\Morphology\Root\Rules\Nouns\PassiveParticipleNounRule;
use Dujana\ArabicNlp\Morphology\Root\Rules\Nouns\PlaceTimeNounRule;
use Dujana\ArabicNlp\Morphology\Root\Rules\Nouns\RelativeNisbaNounRule;
use Dujana\ArabicNlp\Morphology\Root\Rules\RuleBasedRootExtractor;
use Dujana\ArabicNlp\Morphology\Root\Rules\Verbs\QuadriliteralVerbPatternRule;
use Dujana\ArabicNlp\Morphology\Root\Rules\Verbs\QuinqueliteralVerbPatternRule;
use Dujana\ArabicNlp\Morphology\Root\Rules\Verbs\SextiliteralVerbPatternRule;
use Dujana\ArabicNlp\Morphology\Root\Rules\Verbs\TriliteralVerbPatternRule;

final class RootExtractor
{
    /**
     * @param  list<RootExtractorContract>  $extractors
     */
    public function __construct(
        private readonly array $extractors = [],
        private readonly RootCandidateRanker $ranker = new RootCandidateRanker,
    ) {}

    public static function make(?LexiconLookup $lookup = null): self
    {
        /** @var list<RootExtractorContract> $extractors */
        $extractors = [];

        if ($lookup !== null) {
            $extractors[] = new DatabaseRootExtractor($lookup);
        }

        /*
         * Classical verb classes:
         * ثلاثي، رباعي، خماسي، سداسي.
         */
        $extractors[] = new RuleBasedRootExtractor([
            new TriliteralVerbPatternRule,

            new MasdarNounRule,
            new ActiveParticipleNounRule,
            new DerivedAdjectiveNounRule,
            new InstanceMannerNounRule,
            new ActionStateNounRule,
            new RelativeNisbaNounRule,
            new BrokenPluralNounRule,
            new PassiveParticipleNounRule,
            new InstrumentNounRule,
            new PlaceTimeNounRule,

            new QuadriliteralVerbPatternRule,
            new QuinqueliteralVerbPatternRule,
            new SextiliteralVerbPatternRule,
            new NominalSurfaceNounRule,
        ]);

        $extractors[] = new FallbackRootExtractor;

        return new self($extractors);
    }

    public function extract(CoreCandidate $candidate): RootAnalysis
    {
        $candidates = [];

        foreach ($this->extractors as $extractor) {
            array_push($candidates, ...$extractor->extract($candidate));
        }

        $ranked = $this->ranker->rank($candidates);

        return new RootAnalysis(
            word: $candidate->core,
            best: $ranked[0] ?? null,
            candidates: $ranked,
        );
    }
}
