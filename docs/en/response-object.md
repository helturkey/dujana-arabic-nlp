# Response object and parsing

`ArabicAnalyzer::analyze()` returns an `ArabicAnalysis` object.

Important fields:

| Field | Meaning |
|---|---|
| `original` | The original input token. |
| `normalized` | The normalized token. |
| `stem` | The selected stem for the chosen mode. |
| `root` | Root if available in root mode. |
| `mode` | Light, moderate, or root. |
| `wordKind` | Broad kind such as noun or verb. |
| `protected` | Whether the token was protected from stemming. |
| `protectionReason` | Why it was protected, if any. |
| `proclitics` | Stripped prefixes/clitics. |
| `enclitics` | Stripped suffixes/clitics. |
| `classification` | Token-level classification result. |
| `rootAnalysis` | Root candidates and best candidate, when root mode runs. |
| `trace` | Diagnostic steps, when enabled. |

## Parse as object

```php
$analysis = ArabicAnalyzer::make()->analyze('أَحْلَامَهُمْ');

$stem = $analysis->stem;
$kind = $analysis->wordKind->value;
$isProtected = $analysis->protected;
```

## Parse as array

```php
$array = $analysis->toArray();

$stem = $array['stem'];
$kind = $array['word_kind'];
$classification = $array['classification'];
```

## Example shape

```php
[
    'original' => 'أَحْلَامَهُمْ',
    'normalized' => 'احلامهم',
    'stem' => 'احلام',
    'root' => null,
    'mode' => 'moderate',
    'word_kind' => 'noun',
    'protected' => false,
    'protection_reason' => null,
    'proclitics' => [],
    'enclitics' => ['هم'],
    'classification' => [
        'type' => 'word',
    ],
]
```

Treat root results as confidence-aware. Prefer `rootAnalysis.best.source`, `confidence`, and reliability flags when building production workflows.
