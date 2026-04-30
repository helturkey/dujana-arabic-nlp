# كائن الاستجابة وقراءته

ترجع `ArabicAnalyzer::analyze()` كائنًا من نوع `ArabicAnalysis`.

أهم الحقول:

| الحقل | المعنى |
|---|---|
| `original` | الكلمة كما أُدخلت. |
| `normalized` | الكلمة بعد التطبيع. |
| `stem` | الجذع المختار بحسب الوضع. |
| `root` | الجذر إن أمكن الوصول إليه في وضع Root. |
| `mode` | الوضع المستخدم: light أو moderate أو root. |
| `wordKind` | النوع العام، مثل اسم أو فعل. |
| `protected` | هل حُميت الكلمة من الاشتقاق؟ |
| `protectionReason` | سبب الحماية إن وجد. |
| `proclitics` | البادئات أو اللواصق التي أزيلت من أول الكلمة. |
| `enclitics` | اللواحق أو الضمائر التي أزيلت من آخر الكلمة. |
| `classification` | نتيجة التصنيف. |
| `rootAnalysis` | مرشحات الجذر عند تشغيل وضع Root. |
| `trace` | خطوات تشخيصية عند تفعيلها. |

## القراءة ككائن

```php
$analysis = ArabicAnalyzer::make()->analyze('أَحْلَامَهُمْ');

$stem = $analysis->stem;
$kind = $analysis->wordKind->value;
$isProtected = $analysis->protected;
```

## القراءة كمصفوفة

```php
$array = $analysis->toArray();

$stem = $array['stem'];
$kind = $array['word_kind'];
$classification = $array['classification'];
```

## مثال مبسط

```php
[
    'original' => 'أَحْلَامَهُمْ',
    'normalized' => 'احلامهم',
    'stem' => 'احلام',
    'root' => null,
    'mode' => 'moderate',
    'word_kind' => 'noun',
    'protected' => false,
    'proclitics' => [],
    'enclitics' => ['هم'],
    'classification' => [
        'type' => 'word',
    ],
]
```
