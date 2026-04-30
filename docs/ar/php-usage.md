# الاستخدام في PHP

## تقطيع النص

```php
use Dujana\ArabicNlp\ArabicTokenizer;

$tokens = ArabicTokenizer::make()->tokenize('أَحْلَامُهُمْ كَثِيرَةٌ.');
```

## تصنيف كلمة

```php
use Dujana\ArabicNlp\ArabicClassifier;

$classification = ArabicClassifier::make()->classify('أَحْلَامَهُمْ');
$array = $classification->toArray();
```

## الاشتقاق

```php
use Dujana\ArabicNlp\ArabicStemmer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

$stem = ArabicStemmer::make()->stem('أَحْلَامَهُمْ', StemmerModeEnum::Moderate);
```

## التحليل الشامل

```php
use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

$analysis = ArabicAnalyzer::make()->analyze('أَحْلَامَهُمْ', StemmerModeEnum::Root);
```

## استخدام قاعدة قاموس

```php
use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;

$analyzer = ArabicAnalyzer::make(new ArabicNlpConfig(
    lexiconDatabasePath: __DIR__.'/dujana-lexicon.sqlite',
));
```
