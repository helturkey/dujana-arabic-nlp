<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp;

use Dujana\ArabicNlp\Classification\ArabicTokenClassification;
use Dujana\ArabicNlp\Diagnostics\AnalysisTrace;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Enums\WordKindEnum;
use Dujana\ArabicNlp\Morphology\Root\RootAnalysis;

final readonly class ArabicAnalysis
{
    /**
     * @param  list<string>  $proclitics
     * @param  list<string>  $enclitics
     */
    public function __construct(
        public string $original,
        public string $normalized,
        public string $stem,
        public ?string $root = null,
        public ?string $pattern = null,
        public ?string $verbPattern = null,
        public float $confidence = 0.0,
        public bool $protected = false,
        public ?string $protectionReason = null,
        public array $proclitics = [],
        public array $enclitics = [],
        public StemmerModeEnum $mode = StemmerModeEnum::Light,
        public ?WordKindEnum $wordKind = null,
        public AnalysisTrace $trace = new AnalysisTrace,
        public ?RootAnalysis $rootAnalysis = null,
        public ?ArabicTokenClassification $classification = null,
    ) {}

    /** @return array<string,mixed> */
    public function toArray(bool $includeTrace = true): array
    {
        $data = [
            'original' => $this->original,
            'normalized' => $this->normalized,
            'stem' => $this->stem,
            'root' => $this->root,
            'mode' => $this->mode->value,
            'protected' => $this->protected,
            'protection_reason' => $this->protectionReason,
            'proclitics' => $this->proclitics,
            'enclitics' => $this->enclitics,
            'word_kind' => $this->wordKind?->value,
            'root_analysis' => $this->rootAnalysis?->toArray(),
            'confidence' => $this->confidence,
            'classification' => $this->classification?->toArray(),
        ];

        if ($includeTrace) {
            $data['trace'] = $this->trace->toArray();
        }

        return $data;
    }
}
