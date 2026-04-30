# التصنيف Classification

التصنيف في دُجانة واجهة مستقلة، وليس مجرد حقل جانبي داخل التحليل. الغرض منه وصف المدخل قبل الاشتقاق أو استخراج الجذر.

```php
use Dujana\ArabicNlp\ArabicClassifier;

$classification = ArabicClassifier::make()->classify('أَحْلَامَهُمْ');
```

ومن خلال المحلّل العام:

```php
$classification = ArabicAnalyzer::make()->classify('أَحْلَامَهُمْ');
```
مثال عملي:

```php
Dujana\ArabicNlp\Classification\ArabicTokenClassification {#3289 ▼
  +token: "من"
  +type: 
Dujana\ArabicNlp\Enums\ArabicTokenTypeEnum
 {#3264 ▼
    +name: "Particle"
    +value: "particle"
  }
  +protected: true
  +reason: "particle"
}
```

يساعد التصنيف في الإجابة عن أسئلة مثل:

- هل المدخل كلمة عربية؟
- هل هو رقم أو رمز أو علامة ترقيم؟
- هل ينبغي حمايته من الاشتقاق؟
- هل عومل صرفيًا كاسم أو فعل؟

التصنيف لا يساوي استخراج الجذر. قد تُصنَّف الكلمة بوصفها اسمًا، ومع ذلك لا يرجع وضع الجذر جذرًا موثوقًا إذا لم يجد قاعدة أو مدخلًا قاموسيًا كافيًا.
