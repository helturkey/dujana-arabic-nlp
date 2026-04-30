<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon\Manual;

final readonly class ManualRootEntry
{
    public function __construct(
        public string $form,
        public string $root,
        public ?string $lemma = null,
        public ?string $posCat = null,
        public ?string $pos = null,
        public ?string $language = null,
        public float $confidence = 0.98,
    ) {}
}
