# Dujana Arabic NLP Documentation

Dujana Arabic NLP is a PHP package for processing Arabic text. It exposes independent layers for tokenization, classification, stemming, and morphology-aware analysis.

## Quick install

```bash
composer require helturkey/dujana-arabic-nlp
```

## First example

```php
use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\ArabicClassifier;
use Dujana\ArabicNlp\ArabicTokenizer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

$text = 'أَحْلَامُهُمْ كَثِيرَةٌ.';
$word = 'أَحْلَامَهُمْ';

$tokens = ArabicTokenizer::make()->tokenize($text);
$classification = ArabicClassifier::make()->classify($word);
$light = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Light);
$moderate = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Moderate);
$root = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);
```

## Pages

- [Installation](installation.md)
- [Plain PHP usage](php-usage.md)
- [Laravel usage](laravel-usage.md)
- [Tokenizer API](tokenizer.md)
- [Classification API](classification.md)
- [Stemming and analysis](analysis-and-stemming.md)
- [Response object and parsing](response-object.md)
- [Lexicon database](lexicon-database.md)
- [Commands](commands.md)
- [Manual roots](manual-roots.md)
- [Root mode policy](root-mode-policy.md)
- [Evaluation](evaluation.md)
- [Contributing](contributing.md)
- [Sponsors](sponsors.md)
- [Credits](credits.md)
- [Troubleshooting](troubleshooting.md)
- [Production checklist](production-checklist.md)
