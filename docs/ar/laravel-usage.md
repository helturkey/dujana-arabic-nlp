# الاستخدام في Laravel

بعد التثبيت، انشر ملفات الحزمة:

```bash
php artisan vendor:publish --tag=dujana-arabic-nlp
```

ثم استخدم الخدمات من حاوية Laravel:

```php
use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\ArabicClassifier;
use Dujana\ArabicNlp\ArabicTokenizer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

$tokens = app(ArabicTokenizer::class)->tokenize('أَحْلَامُهُمْ كَثِيرَةٌ.');
$classification = app(ArabicClassifier::class)->classify('أَحْلَامَهُمْ');
$analysis = app(ArabicAnalyzer::class)->analyze('أَحْلَامَهُمْ', StemmerModeEnum::Root);
```

أو

```php
use DujanaArabicNlp;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

$analysis = DujanaArabicNlp::analyze('أَحْلَامَهُمْ', StemmerModeEnum::Root);
$tokens = DujanaArabicNlp::tokenize('أحلامهم كثيرة');
$classification = DujanaArabicNlp::classify('أَحْلَامَهُمْ');
```

لا تستخدم `ArabicAnalyzer::make()` داخل كود التطبيق إلا إذا أردت صراحة تجاهل إعدادات Laravel.

## بناء قاعدة القاموس في Laravel

```bash
php artisan dujana:lexicon:build --manual=storage/app/dujana/lexicon/manual-roots.tsv --qabas=/path/to/Qabas-dataset.csv --arramooz=/path/to/arramooz/arabicdictionary.sqlite --output=storage/app/dujana/dujana-lexicon.sqlite
```
