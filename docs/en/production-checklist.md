# Production checklist

Before release:

```bash
composer dump-autoload
vendor/bin/pest
vendor/bin/phpstan analyse src
vendor/bin/pint --test
```

Check binaries:

```bash
php -l bin/dujana-lexicon-build
php -l bin/dujana-lexicon-lookup
php -l bin/dujana-lexicon-stats
php -l bin/dujana-root-evaluate
php -l bin/dujana-root-evaluate-suite
```

Build and query a lexicon database:

```bash
vendor/bin/dujana-lexicon-build --manual=resources/lexicon/manual-roots.tsv --output=/tmp/dujana-lexicon.sqlite
vendor/bin/dujana-lexicon-lookup تنزف /tmp/dujana-lexicon.sqlite --json
```

## SQLite lexicon database security

## أمان قاعدة SQLite

ينبغي أن تبقى قاعدة القاموس الاختيارية خارج جذر الويب.

المسار الافتراضي في Laravel:

```txt
storage/app/dujana/dujana-lexicon.sqlite
```

لا تضع القاعدة داخل:

```txt
public/
storage/app/public/
public/storage/
```

صلاحيات مقترحة:

```txt
chmod 750 storage/app/dujana
chmod 640 storage/app/dujana/dujana-lexicon.sqlite
```

If your application uses php artisan storage:link, make sure it does not expose storage/app/dujana.