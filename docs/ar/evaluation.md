# التقييم

تقييم كلمة واحدة:

```bash
vendor/bin/dujana-root-evaluate تنزف نزف --db=/absolute/path/dujana-lexicon.sqlite
```

تقييم مجموعة TSV:

```bash
vendor/bin/dujana-root-evaluate-suite /absolute/path/root-suite.tsv \
  --db=/absolute/path/dujana-lexicon.sqlite \
  --show-failures
```

صيغة الملف:

```tsv
word	root
تنزف	نزف
كاتب	كتب
أحلام	حلم
```
