# Plain PHP usage

## Tokenize

```php
use Dujana\ArabicNlp\ArabicTokenizer;

$tokens = ArabicTokenizer::make()->tokenize('أَحْلَامُهُمْ كَثِيرَةٌ.');
```

## Classify

```php
use Dujana\ArabicNlp\ArabicClassifier;

$classification = ArabicClassifier::make()->classify('أَحْلَامَهُمْ');
$array = $classification->toArray();
```

## Stem

```php
use Dujana\ArabicNlp\ArabicStemmer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

$stem = ArabicStemmer::make()->stem('أَحْلَامَهُمْ', StemmerModeEnum::Moderate);
```

## Analyze

```php
use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

$analysis = ArabicAnalyzer::make()->analyze('أَحْلَامَهُمْ', StemmerModeEnum::Root);
```

## Use a lexicon database

```php
use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;

$analyzer = ArabicAnalyzer::make(new ArabicNlpConfig(
    lexiconDatabasePath: __DIR__.'/dujana-lexicon.sqlite',
));
```
