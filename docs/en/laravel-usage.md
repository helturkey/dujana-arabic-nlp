# Laravel usage

After publishing resources:

```bash
php artisan vendor:publish --tag=dujana-arabic-nlp
```

Use the container:

```php
use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\ArabicClassifier;
use Dujana\ArabicNlp\ArabicTokenizer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

$tokens = app(ArabicTokenizer::class)->tokenize('أَحْلَامُهُمْ كَثِيرَةٌ.');
$classification = app(ArabicClassifier::class)->classify('أَحْلَامَهُمْ');
$analysis = app(ArabicAnalyzer::class)->analyze('أَحْلَامَهُمْ', StemmerModeEnum::Root);
```

or

```php
use DujanaArabicNlp;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

$analysis = DujanaArabicNlp::analyze('أَحْلَامَهُمْ', StemmerModeEnum::Root);
$tokens = DujanaArabicNlp::tokenize('أحلامهم كثيرة');
$classification = DujanaArabicNlp::classify('أَحْلَامَهُمْ');
```

Do not instantiate the analyzer with `ArabicAnalyzer::make()` in application code unless you intentionally want to ignore Laravel config.

## Build the Laravel lexicon database

```bash
php artisan dujana:lexicon:build \
  --manual=storage/app/dujana/lexicon/manual-roots.tsv \
  --output=storage/app/dujana/dujana-lexicon.sqlite
```

Then use:

```php
$analysis = app(ArabicAnalyzer::class)->analyze('تَنْزِفُ', StemmerModeEnum::Root);
```
