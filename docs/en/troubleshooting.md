# Troubleshooting

## Laravel does not use the lexicon database

Use the container:

```php
$analyzer = app(\Dujana\ArabicNlp\ArabicAnalyzer::class);
```

Do not use `ArabicAnalyzer::make()` in Laravel app code unless you intentionally bypass config.

## A suffix was stripped incorrectly

Check whether the word is being analyzed in light, moderate, or root mode. Moderate stemming strips more suffixes than light mode. Structural suffix-like letters should be handled by suffix policy tests.

## Root is null

Root mode may return null when no reliable root is found. Add a manual root or lexicon entry for production-critical words.
