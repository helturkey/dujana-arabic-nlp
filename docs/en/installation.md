# Installation

## Plain PHP

```bash
composer require helturkey/dujana-arabic-nlp
```

Then use the public classes directly:

```php
use Dujana\ArabicNlp\ArabicAnalyzer;

$analyzer = ArabicAnalyzer::make();
```

## Laravel

```bash
composer require helturkey/dujana-arabic-nlp
php artisan vendor:publish --tag=dujana-arabic-nlp
```

The published config contains paths for stop words, protected names, non-stemmable terms, and the optional SQLite lexicon database.

Use the Laravel container in app code:

```php
$analyzer = app(\Dujana\ArabicNlp\ArabicAnalyzer::class);
```

This ensures the analyzer uses your published Laravel configuration.
