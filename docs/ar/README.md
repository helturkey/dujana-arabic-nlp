# توثيق دُجانة لمعالجة العربية

دُجانة حزمة PHP لمعالجة النصوص العربية الفصحى. صُمّمت بوصفها أدوات صغيرة ومترابطة، لا مجرّد stemmer.

توفّر دُجانة أربع طبقات عامة:

```php
$analyzer->tokenize($text);   // تقطيع النص إلى كلمات
$analyzer->classify($word);   // تصنيف المدخل
$analyzer->stem($word);       // الاشتقاق الخفيف أو المتوسط
$analyzer->analyze($word);    // التحليل الشامل ومحاولة استخراج الجذر
```

## التثبيت السريع

```bash
composer require helturkey/dujana-arabic-nlp
```

## مثال أول

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

## الصفحات

- [التثبيت](installation.md)
- [الاستخدام في PHP](php-usage.md)
- [الاستخدام في Laravel](laravel-usage.md)
- [التقطيع Tokenizer](tokenizer.md)
- [التصنيف Classification](classification.md)
- [الاشتقاق والتحليل](analysis-and-stemming.md)
- [كائن الاستجابة وقراءته](response-object.md)
- [قاعدة القاموس](lexicon-database.md)
- [الأوامر](commands.md)
- [الجذور اليدوية](manual-roots.md)
- [سياسة وضع الجذر](root-mode-policy.md)
- [التقييم](evaluation.md)
- [المساهمة](contributing.md)
- [الرعاية](sponsors.md)
- [الشكر والاعتمادات](credits.md)
- [حل المشكلات](troubleshooting.md)
- [قائمة فحص الإنتاج](production-checklist.md)
