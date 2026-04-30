# التثبيت

## في مشروع PHP

```bash
composer require helturkey/dujana-arabic-nlp
```

ثم استخدم الكائنات العامة مباشرة:

```php
use Dujana\ArabicNlp\ArabicAnalyzer;

$analyzer = ArabicAnalyzer::make();
```

## في Laravel

```bash
composer require helturkey/dujana-arabic-nlp
php artisan vendor:publish --tag=dujana-arabic-nlp
```

تنشر الحزمة ملف الإعدادات وموارد القاموس وقوائم الكلمات المحمية.

داخل تطبيق Laravel استخدم الحاوية:

```php
$analyzer = app(\Dujana\ArabicNlp\ArabicAnalyzer::class);
```

بهذا يقرأ المحلّل إعدادات Laravel المنشورة، ومنها مسار قاعدة القاموس الاختيارية.
