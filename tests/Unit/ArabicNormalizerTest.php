<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\NormalizationProfileEnum;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Text\ArabicNormalizer;

it('preserves shadda in morphology normalization', function (): void {
    $normalizer = new ArabicNormalizer;

    expect($normalizer->normalize('اِحْمَرَّ', NormalizationProfileEnum::Morphology))
        ->toBe('احمرّ')
        ->and($normalizer->normalize('عَلَّمَ', NormalizationProfileEnum::Morphology))
        ->toBe('علّم');
});

it('removes shadda in search and stemming normalization', function (): void {
    $normalizer = new ArabicNormalizer;

    expect($normalizer->normalize('اِحْمَرَّ', NormalizationProfileEnum::Search))
        ->toBe('احمر')
        ->and($normalizer->normalize('عَلَّمَ', NormalizationProfileEnum::Stemming))
        ->toBe('علم');
});

it('uses search-friendly normalization by default', function (): void {
    expect(new ArabicNormalizer()->normalize('أحمد'))->toBe('احمد')
        ->and(new ArabicNormalizer()->normalize('إيمان'))->toBe('ايمان')
        ->and(new ArabicNormalizer()->normalize('آثار'))->toBe('اثار')
        ->and(new ArabicNormalizer()->normalize('مسؤول'))->toBe('مسوول')
        ->and(new ArabicNormalizer()->normalize('رئيس'))->toBe('رييس')
        ->and(new ArabicNormalizer()->normalize('أسئلة'))->toBe('اسيلة');
});

it('supports morphology-aware normalization profile', function (): void {
    expect(new ArabicNormalizer()->normalize('أحمد', NormalizationProfileEnum::Morphology))->toBe('احمد')
        ->and(new ArabicNormalizer()->normalize('إيمان', NormalizationProfileEnum::Morphology))->toBe('ايمان')
        ->and(new ArabicNormalizer()->normalize('مسؤول', NormalizationProfileEnum::Morphology))->toBe('مسؤول')
        ->and(new ArabicNormalizer()->normalize('رئيس', NormalizationProfileEnum::Morphology))->toBe('رئيس')
        ->and(new ArabicNormalizer()->normalize('أسئلة', NormalizationProfileEnum::Morphology))->toBe('اسئلة');
});

it('supports raw normalization profile preserving hamza seats', function (): void {
    expect(new ArabicNormalizer()->normalize('أحمد', NormalizationProfileEnum::Raw))->toBe('أحمد')
        ->and(new ArabicNormalizer()->normalize('مسؤول', NormalizationProfileEnum::Raw))->toBe('مسؤول')
        ->and(new ArabicNormalizer()->normalize('رئيس', NormalizationProfileEnum::Raw))->toBe('رئيس');
});

it('supports explicit stemming normalization profile', function (): void {
    expect(new ArabicNormalizer()->normalize('أحمد', NormalizationProfileEnum::Stemming))->toBe('احمد')
        ->and(new ArabicNormalizer()->normalize('مدرسة', NormalizationProfileEnum::Stemming))->toBe('مدرسة');
});

it('normalizes alef maqsura', function (): void {
    expect(new ArabicNormalizer()->normalize('موسى'))->toBe('موسي')
        ->and(new ArabicNormalizer()->normalize('موسى', NormalizationProfileEnum::Morphology))->toBe('موسي');
});

it('removes diacritics tatweel and invisible marks', function (): void {
    expect(new ArabicNormalizer()->normalize('كِتَابٌ'))->toBe('كتاب')
        ->and(new ArabicNormalizer()->normalize('مــدرســة'))->toBe('مدرسة')
        ->and(new ArabicNormalizer()->normalize("ك\u{200F}تاب"))->toBe('كتاب');
});

it('normalizes tokens by trimming punctuation boundaries', function (): void {
    expect(new ArabicNormalizer()->normalizeToken('،أحمد،'))->toBe('احمد')
        ->and(new ArabicNormalizer()->normalizeToken('،مسؤول،', NormalizationProfileEnum::Morphology))->toBe('مسؤول');
});

it('does not corrupt utf8 letters when trimming Arabic punctuation', function (): void {
    expect(new ArabicNormalizer()->normalizeToken('،أحمد،'))->toBe('احمد')
        ->and(new ArabicNormalizer()->normalizeToken('؟إيمان؟'))->toBe('ايمان')
        ->and(new ArabicNormalizer()->normalizeToken('«آثار»'))->toBe('اثار')
        ->and(new ArabicNormalizer()->normalizeToken('،أسئلة،', NormalizationProfileEnum::Morphology))->toBe('اسئلة');
});

it('uses morphology normalization in root mode without relying on legacy hamza extractors', function (): void {
    $analysis = ArabicAnalyzer::make()
        ->analyze('أسئلة', StemmerModeEnum::Root);

    expect($analysis->normalized)->toBe('اسئلة')
        ->and($analysis->rootAnalysis?->best?->source)->not->toBeIn(['manual_lexicon', 'database']);
});
