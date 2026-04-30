# قاعدة القاموس

قاعدة القاموس SQLite اختيارية. تعمل دُجانة دونها، لكن وضع Root يصبح أقوى عند وجود جذور يدوية ومدخلات قاموسية.

## البناء من ملف جذور يدوي

```bash
vendor/bin/dujana-lexicon-build \
  --manual=/absolute/path/manual-roots.tsv \
  --output=/absolute/path/dujana-lexicon.sqlite
```

في Laravel:

```bash
php artisan dujana:lexicon:build \
  --manual=storage/app/dujana/lexicon/manual-roots.tsv \
  --output=storage/app/dujana/dujana-lexicon.sqlite
```

## البناء مع Qabas

```bash
vendor/bin/dujana-lexicon-build \
  --qabas=/absolute/path/qabas.csv \
  --manual=/absolute/path/manual-roots.tsv \
  --output=/absolute/path/dujana-lexicon.sqlite
```

## البناء مع Arramooz

```bash
vendor/bin/dujana-lexicon-build \
  --arramooz=/absolute/path/arramooz.sqlite \
  --manual=/absolute/path/manual-roots.tsv \
  --output=/absolute/path/dujana-lexicon.sqlite
```

## البحث في القاموس

```bash
vendor/bin/dujana-lexicon-lookup تنزف /absolute/path/dujana-lexicon.sqlite --json
```

في Laravel:

```bash
php artisan dujana:lexicon:lookup تنزف storage/app/dujana/dujana-lexicon.sqlite --json
```
