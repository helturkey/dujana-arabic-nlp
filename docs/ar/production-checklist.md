# قائمة فحص الإنتاج

قبل النشر:

```bash
composer dump-autoload
vendor/bin/pest
vendor/bin/phpstan analyse src
vendor/bin/pint --test
```

افحص الأوامر:

```bash
php -l bin/dujana-lexicon-build
php -l bin/dujana-lexicon-lookup
php -l bin/dujana-lexicon-stats
php -l bin/dujana-root-evaluate
php -l bin/dujana-root-evaluate-suite
```

ابنِ قاعدة قاموس وجرب البحث:

```bash
vendor/bin/dujana-lexicon-build --manual=resources/lexicon/manual-roots.tsv --output=/tmp/dujana-lexicon.sqlite
vendor/bin/dujana-lexicon-lookup تنزف /tmp/dujana-lexicon.sqlite --json
```

## SQLite lexicon database security

The optional SQLite lexicon database should stay outside the public web root.

Laravel default path:

```txt
storage/app/dujana/dujana-lexicon.sqlite
```


Do not place the database under:

```txt
public/
storage/app/public/
public/storage/
```

Recommended permissions:

```txt
chmod 750 storage/app/dujana
chmod 640 storage/app/dujana/dujana-lexicon.sqlite
```

إذا كنت تستخدم php artisan storage:link فتأكد أنه لا يتيح مجلد storage/app/dujana عبر الويب.