# Dujana Arabic NLP

**Dujana Arabic NLP** is a morphology-aware PHP package for Arabic text processing.  
It is designed as a small Arabic NLP toolkit, not only as a stemmer.

It provides practical layers for:

- Tokenization
- Classification
- Light and moderate stemming
- Morphology-aware analysis
- Optional lexicon-backed root exploration

---

## Installation

### Plain PHP / Composer

```bash
composer require helturkey/dujana-arabic-nlp
```

```php
use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\ArabicClassifier;
use Dujana\ArabicNlp\ArabicTokenizer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

$tokens = ArabicTokenizer::make()->tokenize('أَحْلَامُهُمْ كَثِيرَةٌ.');

$classification = ArabicClassifier::make()->classify('أَحْلَامَهُمْ');

$analysis = ArabicAnalyzer::make()->analyze('أَحْلَامَهُمْ', StemmerModeEnum::Root);
```

### Laravel

Publish the configuration and resources:

```bash
php artisan vendor:publish --tag=dujana-arabic-nlp
```

Use the Laravel container so the package can read your published configuration and optional lexicon database path:

```php
use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\ArabicClassifier;
use Dujana\ArabicNlp\ArabicTokenizer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

$tokens = app(ArabicTokenizer::class)->tokenize('أَحْلَامُهُمْ كَثِيرَةٌ.');

$classification = app(ArabicClassifier::class)->classify('أَحْلَامَهُمْ');

$analysis = app(ArabicAnalyzer::class)->analyze('أَحْلَامَهُمْ', StemmerModeEnum::Root);
```

Avoid using `ArabicAnalyzer::make()` inside Laravel application code unless you intentionally want to bypass Laravel configuration.

---

## Public API Layers

Dujana exposes four main public layers:

```php
$analyzer->tokenize($text);                         // Tokenization
$analyzer->classify($word);                         // Classification
$analyzer->stem($word, StemmerModeEnum::Moderate);  // Stemming
$analyzer->analyze($word, StemmerModeEnum::Root);   // Full analysis
```

Specialized APIs are also available:

```php
ArabicTokenizer::make()->tokenize($text);
ArabicClassifier::make()->classify($word);
ArabicStemmer::make()->stem($word, StemmerModeEnum::Moderate);
ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);
```

---

## Documentation

- [English documentation](docs/en/README.md)
- [التوثيق العربي](docs/ar/README.md)
- [References, dictionaries, and morphology sources](docs/references/books-and-dictionaries.md)

---

## Why “Dujana”?

The name **Dujana** is an authentic Arabic name with several meanings rooted in Arabic language and nature.  
The meaning chosen for this package is **the great, abundant rain**: rain that falls heavily, spreads widely, and covers the earth.

This image fits the purpose of the package. Dujana was created to serve Arabic text broadly: to help normalize, tokenize, classify, stem, and analyze Arabic words in a way that can benefit many projects, not only one private codebase.

### لماذا اسم “دُجانة”؟

اسم **دُجانة** اسم عربي أصيل، له أكثر من معنى في اللغة. والمعنى الذي اخترناه أساسًا للتسمية هو **المطر العظيم**؛ أي المطر الشديد الواسع الذي يغمر الأرض وينتشر خيره.

وهذا المعنى يلائم غاية الحزمة؛ فقد أُنشئت دُجانة لتخدم النص العربي على نطاق واسع: تطبيعًا، وتقطيعًا، وتصنيفًا، واشتقاقًا، وتحليلًا صرفيًا، بحيث ينتفع بها أكثر من مشروع، ولا تبقى حبيسة شيفرة داخلية خاصة.

---

## Origin

Dujana Arabic NLP started as part of the core Arabic text-processing code behind [Poetspedia — موسوعة الشعراء](https://poetspedia.com), a platform dedicated to Arabic poetry, poets, rhyme, and literary exploration.

It was originally built to support real production needs in a large Arabic poetry platform: normalization, tokenization, stemming, classification, lexicon-backed analysis, and morphology-aware root exploration for classical and modern Arabic poetry.

After being tested and refined inside Poetspedia, Dujana was extracted into a standalone open-source package so Arabic developers, researchers, linguists, and NLP engineers can reuse it, improve it, and build on top of it.

Dujana is not a closed internal tool. It is a contribution from Poetspedia to the Arabic open-source ecosystem, with the hope that it helps make Arabic NLP tooling more accessible, practical, and production-ready.

### النشأة

بدأت دُجانة بوصفها جزءًا من الشيفرة الأساسية لمعالجة النصوص العربية في [موسوعة الشعراء](https://poetspedia.com)، وهي منصة متخصصة في الشعر العربي والشعراء والقافية والاستكشاف الأدبي.

نشأت الحاجة إليها داخل مشروع عربي حقيقي يتعامل مع الشعر العربي على نطاق واسع؛ من التطبيع والتقطيع والاشتقاق والتصنيف، إلى التحليل المعجمي واستكشاف الجذور بطريقة تراعي طبيعة العربية وأوزانها وسياقاتها في الشعر القديم والحديث.

وبعد استخدامها وتطويرها داخل موسوعة الشعراء، فُصلت دُجانة في حزمة مستقلة مفتوحة المصدر، لتكون متاحة للمطورين والباحثين واللغويين والمهتمين بالمعالجة الحاسوبية للغة العربية.

ليست دُجانة أداة داخلية مغلقة، بل مساهمة من موسوعة الشعراء في إثراء البرمجيات العربية مفتوحة المصدر، على أمل أن تساعد في جعل أدوات معالجة العربية أقرب إلى الواقع، وأسهل استخدامًا، وأصلح للتطبيقات الإنتاجية.
