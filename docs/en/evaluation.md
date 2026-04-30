# Evaluation

Evaluate a single word:

```bash
vendor/bin/dujana-root-evaluate تنزف نزف --db=/absolute/path/dujana-lexicon.sqlite
```

Evaluate a TSV suite:

```bash
vendor/bin/dujana-root-evaluate-suite /absolute/path/root-suite.tsv \
  --db=/absolute/path/dujana-lexicon.sqlite \
  --show-failures
```

TSV format:

```tsv
word	root
تنزف	نزف
كاتب	كتب
أحلام	حلم
```
