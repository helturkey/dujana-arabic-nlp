# Classification API

Classification is a standalone public API. It describes the input token without requiring stemming or root extraction.

```php
use Dujana\ArabicNlp\ArabicClassifier;

$classification = ArabicClassifier::make()->classify('أَحْلَامَهُمْ');
```

Through the analyzer:

```php
$classification = ArabicAnalyzer::make()->classify('أَحْلَامَهُمْ');
```

Classification can help answer:

- Is this token Arabic text?
- Is it a word, number, symbol, or punctuation?
- Should it be protected from stemming?
- Was the token interpreted as a noun or a verb?

Classification is not the same as root extraction. A token may be classified as a noun while root mode still has no authoritative root.
