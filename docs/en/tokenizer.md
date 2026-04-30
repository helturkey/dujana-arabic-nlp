# Tokenizer API

The tokenizer is available independently.

```php
use Dujana\ArabicNlp\ArabicTokenizer;

$tokens = ArabicTokenizer::make()->tokenize('أَحْلَامُهُمْ كَثِيرَةٌ.');
```

Through the analyzer:

```php
$tokens = ArabicAnalyzer::make()->tokenize('أَحْلَامُهُمْ كَثِيرَةٌ.');
```

Typical output:

```php
['أَحْلَامُهُمْ', 'كَثِيرَةٌ']
```

Tokenization is useful before classification, annotation, search indexing, and poetry-facing interfaces.
