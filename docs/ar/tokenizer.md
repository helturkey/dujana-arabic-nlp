# التقطيع Tokenizer

يمكن استخدام التقطيع وحده دون تحليل صرفي.

```php
use Dujana\ArabicNlp\ArabicTokenizer;

$tokens = ArabicTokenizer::make()->tokenize('أَحْلَامُهُمْ كَثِيرَةٌ.');
```

ومن خلال المحلّل العام:

```php
$tokens = ArabicAnalyzer::make()->tokenize('أَحْلَامُهُمْ كَثِيرَةٌ.');
```

مثال مبسط للناتج:

```php
['أَحْلَامُهُمْ', 'كَثِيرَةٌ']
```

يفيد التقطيع قبل التصنيف، والفهرسة، وبناء واجهات الشرح، ومعالجة أبيات الشعر.
