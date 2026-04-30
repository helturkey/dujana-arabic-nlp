<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon\Database;

final readonly class LexiconTrustPolicy
{
    public function minLookupFormLength(): int
    {
        return 3;
    }

    public function shouldUseForm(string $form): bool
    {
        return mb_strlen(trim($form)) >= $this->minLookupFormLength();
    }

    public function shouldUseEntry(string $form, LexiconEntry $entry): bool
    {
        $form = trim($form);

        if (mb_strlen($form) >= $this->minLookupFormLength()) {
            return true;
        }

        return $this->hasManualSource($entry);
    }

    public function confidenceFor(LexiconEntry $entry): float
    {
        $confidence = $entry->confidence;

        if ($this->hasSource($entry, 'manual')) {
            $confidence += 0.08;
        }

        if ($entry->sourceCount > 1) {
            $confidence += 0.03;
        }

        if ($entry->alternatives !== []) {
            $confidence -= min(0.20, count($entry->alternatives) * 0.04);
        }

        return $this->clamp($confidence);
    }

    /**
     * @return list<string>
     */
    public function reasonsFor(LexiconEntry $entry): array
    {
        $reasons = [];

        if ($this->hasSource($entry, 'manual')) {
            $reasons[] = 'trust:manual_source_bonus';
        }

        if ($entry->sourceCount > 1) {
            $reasons[] = 'trust:multiple_sources_bonus';
        }

        if ($entry->alternatives !== []) {
            $reasons[] = 'trust:alternatives_penalty';
        }

        return $reasons;
    }

    public function alternativeConfidence(float $confidence): float
    {
        return round(max(0.30, min(0.79, $confidence - 0.15)), 2);
    }

    private function hasSource(LexiconEntry $entry, string $source): bool
    {
        foreach ($entry->sources as $entrySource) {
            if ($entrySource->source === $source) {
                return true;
            }
        }

        return false;
    }

    private function clamp(float $confidence): float
    {
        return round(max(0.30, min(0.99, $confidence)), 2);
    }

    public function hasManualSource(LexiconEntry $entry): bool
    {
        return $this->hasSource($entry, 'manual');
    }
}
