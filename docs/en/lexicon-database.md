# Lexicon database

The SQLite lexicon database is optional. Dujana works without it, but root mode becomes more useful when database entries and manual roots are available.

## Build from manual roots

```bash
vendor/bin/dujana-lexicon-build \
  --manual=/absolute/path/manual-roots.tsv \
  --output=/absolute/path/dujana-lexicon.sqlite
```

Laravel:

```bash
php artisan dujana:lexicon:build \
  --manual=storage/app/dujana/lexicon/manual-roots.tsv \
  --output=storage/app/dujana/dujana-lexicon.sqlite
```

## Build with Qabas

```bash
vendor/bin/dujana-lexicon-build \
  --qabas=/absolute/path/qabas.csv \
  --manual=/absolute/path/manual-roots.tsv \
  --output=/absolute/path/dujana-lexicon.sqlite
```

## Build with Arramooz

```bash
vendor/bin/dujana-lexicon-build \
  --arramooz=/absolute/path/arramooz.sqlite \
  --manual=/absolute/path/manual-roots.tsv \
  --output=/absolute/path/dujana-lexicon.sqlite
```

## Lookup

```bash
vendor/bin/dujana-lexicon-lookup تنزف /absolute/path/dujana-lexicon.sqlite --json
```

Laravel:

```bash
php artisan dujana:lexicon:lookup تنزف storage/app/dujana/dujana-lexicon.sqlite --json
```

## Stats

```bash
vendor/bin/dujana-lexicon-stats /absolute/path/dujana-lexicon.sqlite
```
