# References, dictionaries, and morphology sources  
# المراجع والمعاجم ومصادر الصرف

This page lists resources that influenced Dujana's design or can be used as external lexicon sources.

تجمع هذه الصفحة الموارد التي أثّرت في تصميم دُجانة، أو يمكن استخدامها مصادرَ معجمية خارجية عند بناء قاعدة القاموس الاختيارية.

> Dujana does not copy these works into its source code. They are listed as references for understanding Arabic morphology, validating examples, and building optional lexicon databases. Always review each resource’s license before redistribution or commercial use.  
>
> لا تنسخ دُجانة هذه الكتب أو القواميس داخل شفرتها المصدرية. تُذكر هذه الموارد للاستئناس العلمي، وفهم المنطق الصرفي، والتحقق من الأمثلة، وبناء القاموس الاختياري عند الحاجة. راجع دائمًا ترخيص كل مصدر قبل إعادة التوزيع أو الاستخدام التجاري.

---

## Classical and educational morphology references  
## مراجع الصرف العربية

These references are useful when reviewing Arabic morphology concepts and pattern logic. They helped shape the way Dujana organizes some of its rule families, especially around patterns, derived forms, verbal forms, broken plurals, nisba, weak roots, hamza, and suffix behavior.

هذه المراجع مفيدة عند مراجعة مفاهيم الصرف العربي ومنطق الأوزان. وقد استفدنا من منهجها العام في تنظيم بعض عائلات القواعد داخل دُجانة، ولا سيما في أبواب الأوزان، والمشتقات، والأفعال المزيدة، وجمع التكسير، والنسبة، والإعلال، والهمز، وسلوك اللواحق.

### شذا العرف في فن الصرف — الشيخ أحمد بن محمد الحملاوي  
### Shadha al-ʿArf fī Fann al-Ṣarf — Aḥmad al-Ḥamlāwī

- Archive.org: https://archive.org/details/Shada3rf
- PDF copy hosted by University of Anbar: https://qecollege.uoanbar.edu.iq/catalog/file/000book%202023/3.pdf

من أشهر الكتب التعليمية في الصرف العربي، وفيه عرض منظم للمجرد والمزيد، والأوزان، والإعلال، والإبدال، والمشتقات. استفدنا من طريقته العامة في ترتيب الأبواب الصرفية والتفريق بين الصيغ.

A widely used educational morphology reference covering triliteral and augmented forms, patterns, weak-letter changes, substitution, and derived forms.

### جامع الدروس العربية — الشيخ مصطفى الغلاييني  
### Jāmiʿ al-Durūs al-ʿArabiyyah — Muṣṭafā al-Ghalāyīnī

- Archive.org: https://archive.org/details/skrdieh_lau_20160829_0708

مرجع تعليمي واسع في العربية، يجمع مباحث النحو والصرف بأسلوب واضح. يفيد في ضبط المصطلحات العامة وربط التصنيف الصرفي بسياق الاسم والفعل والمشتقات.

A broad Arabic grammar reference that includes morphology sections and helps connect morphological labels with general Arabic grammar concepts.

### التطبيق الصرفي — الدكتور عبده الراجحي  
### al-Taṭbīq al-Ṣarfī — ʿAbduh al-Rājiḥī

- Archive.org: https://archive.org/details/Heliopolis1957_gmail_20180503_1537

كتاب معاصر ذو طابع تطبيقي، نافع في ربط القاعدة الصرفية بالأمثلة العملية. استفدنا من روحه التطبيقية عند تحويل القواعد إلى اختبارات قابلة للقياس.

A practical modern morphology reference. It is useful when turning morphology concepts into testable examples and rule families.

---

## Other useful morphology and lexicographic materials  
## مواد صرفية ومعجمية نافعة

The following categories may also help when validating examples or expanding Dujana's lexicon database:

يمكن أيضًا الرجوع إلى الأنواع الآتية من المصادر عند التحقق من الأمثلة أو توسيع قاعدة دُجانة المعجمية:

- University morphology handouts and Arabic morphology course notes.
- Classical Arabic dictionaries, when checking lexical roots and inherited meanings.
- Digitized Arabic lexicons, when their licenses allow research or import.
- Morphologically annotated corpora, when available under suitable terms.
- كتب ومقررات الصرف العربي المتاحة من الجامعات أو المكتبات الرقمية.
- المعاجم العربية التراثية عند التحقق من الجذور والمعاني.
- المعاجم العربية المرقمنة، إذا سمحت تراخيصها بالبحث أو الاستيراد.
- المدونات العربية الموسومة صرفيًا، متى كانت متاحة بترخيص مناسب.

---

## How these sources relate to Dujana  
## كيف ترتبط هذه المصادر بدُجانة؟

Dujana uses these resources in two different ways:

تعامل دُجانة هذه الموارد بطريقتين مختلفتين:

1. **Scientific and linguistic guidance**  
   Books of Arabic morphology help us reason about forms, patterns, and rule families. They do not become executable code by themselves, but they influence how rules are named, grouped, tested, and documented.

   **استئناس علمي ولغوي**  
   تساعد كتب الصرف في فهم الأوزان، والصيغ، وعائلات القواعد. وهي لا تتحول إلى شيفرة مباشرة، لكنها تؤثر في تسمية القواعد، وتجميعها، واختبارها، وشرحها.

2. **Optional lexicon imports**  
   External dictionaries such as Qabas or Arramooz may be imported by the user into an optional SQLite lexicon database. This database can improve root decisions, especially in weak, hamzated, rare, or highly lexical forms.

   **استيراد معجمي اختياري**  
   يمكن للمستخدم استيراد بعض القواميس الخارجية مثل Qabas أو Arramooz إلى قاعدة SQLite اختيارية. وتفيد هذه القاعدة في تحسين قرارات الجذر، ولا سيما في الكلمات المعتلة، والمهموزة، والنادرة، أو التي يغلب عليها الطابع المعجمي.

---

## License and redistribution notes  
## ملاحظات الترخيص وإعادة التوزيع

Dujana does not bundle Qabas, Arramooz, or the listed morphology books as package data unless explicitly permitted by their licenses.

لا تضمّن دُجانة Qabas أو Arramooz أو كتب الصرف المذكورة بوصفها ملفات مرفقة بالحزمة، إلا إذا سمح الترخيص الأصلي بذلك صراحة.

Before redistributing any imported database, verify:

قبل إعادة توزيع أي قاعدة مستوردة، تحقّق من الآتي:

- The license of the original dictionary or book.
- Whether commercial use is allowed.
- Whether redistribution of derived SQLite databases is allowed.
- Whether attribution is required.
- ترخيص المصدر الأصلي.
- هل يسمح بالاستخدام التجاري؟
- هل يسمح بإعادة توزيع قاعدة SQLite مشتقة؟
- هل يشترط ذكر المصدر أو المؤلفين؟