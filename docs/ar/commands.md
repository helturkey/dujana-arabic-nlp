# الأوامر

أوامر Composer المتاحة:

```bash
vendor/bin/dujana-lexicon-build
vendor/bin/dujana-lexicon-lookup
vendor/bin/dujana-lexicon-stats
vendor/bin/dujana-root-evaluate
vendor/bin/dujana-root-evaluate-suite
```

مثال:

```bash
vendor/bin/dujana-lexicon-lookup قلب storage/app/dujana/dujana-lexicon.sqlite --json
```

أوامر Laravel:

```bash
php artisan dujana:lexicon:build
php artisan dujana:lexicon:lookup
php artisan dujana:lexicon:stats
php artisan dujana:root:evaluate
php artisan dujana:root:evaluate-suite
```

مثال:

```bash
php artisan dujana:lexicon:lookup قلب storage/app/dujana/dujana-lexicon.sqlite --json
```

استخدم `--json` إذا أردت دمج الأوامر في CI أو scripts.
