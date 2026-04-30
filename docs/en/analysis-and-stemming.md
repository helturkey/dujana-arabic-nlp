# Stemming and analysis

Dujana supports three modes.

## Light

Light mode is conservative. It removes obvious clitics and avoids deeper morphology.

## Moderate

Moderate mode is stronger. It can remove common prefixes, definite articles, suffixes, and pronouns while using safety guards to avoid stripping structural letters.

## Root

Root mode is morphology-aware. It uses:

- Optional lexicon/database entries.
- Manual roots.
- Rule-based morphology extractors.
- Conservative fallback candidates.

Root mode is not advertised as fully authoritative for every Arabic word. Weak, hamzated, rare, or highly lexical roots should be backed by the lexicon database when used in production.

## Example: أَحْلَامَهُمْ

```php
use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

$word = 'أَحْلَامَهُمْ';

$light = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Light);
$moderate = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Moderate);
$root = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);
```

Expected general behavior:

| Mode | Purpose | Typical result |
|---|---|---|
| Light | Conservative stemming | keeps more surface structure |
| Moderate | Practical stem | removes possessive suffix when safe |
| Root | Morphology-aware analysis | attempts root through lexicon/rules |

Exact output depends on configuration, lexicon availability, and protected-word lists.
