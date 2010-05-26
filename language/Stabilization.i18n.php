<?php
/**
 * Internationalisation file for FlaggedRevs extension, section Stabilization
 *
 * @addtogroup Extensions
 */

$messages = array();

$messages['en'] = array(
	'stabilization-tab' => 'vet',
	'stabilization' => 'Page stabilization',
	'stabilization-text' => '\'\'\'Change the settings below to adjust how the accepted version of [[:$1|$1]] is selected and displayed.\'\'\'

\'\'\'Note:\'\'\' changing the \'\'accepted version selection\'\' to prefer "quality" or "pristine" versions will have no effect if there are no such versions. Also, note that a "quality" version is also considered a "checked" version and so on.',
	'stabilization-perm' => 'Your account does not have permission to change the accepted version configuration.
Here are the current settings for [[:$1|$1]]:',
	'stabilization-page' => 'Page name:',
	'stabilization-leg' => 'Confirm accepted version settings',
	'stabilization-select' => 'Accepted version selection precedence',
	'stabilization-select1' => 'Latest quality revision; then latest sighted one',
	'stabilization-select2' => 'Latest checked revision',
	'stabilization-select3' => 'Latest pristine revision; then latest quality one; then latest sighted one',
	'stabilization-def' => 'Revision displayed on default page view',
	'stabilization-def1' => 'The accepted revision; if not present, then the current/draft one',
	'stabilization-def2' => 'The current/draft revision',
	'stabilization-restrict' => 'Review/auto-review restrictions',
	'stabilization-restrict-none' => 'No extra restrictions',
	'stabilization-submit' => 'Confirm',
	'stabilization-notexists' => 'There is no page called "[[:$1|$1]]".
No configuration is possible.',
	'stabilization-notcontent' => 'The page "[[:$1|$1]]" cannot be reviewed.
No configuration is possible.',
	'stabilization-comment' => 'Reason:',
	'stabilization-otherreason' => 'Other reason:',
	'stabilization-expiry' => 'Expires:',
	'stabilization-othertime' => 'Other time:',
	'stabilization-sel-short' => 'Precedence',
	'stabilization-sel-short-0' => 'Quality',
	'stabilization-sel-short-1' => 'None',
	'stabilization-sel-short-2' => 'Pristine',
	'stabilization-def-short' => 'Default',
	'stabilization-def-short-0' => 'Current',
	'stabilization-def-short-1' => 'Accepted',
    'stabilize_page_invalid'       => 'The target page title is invalid.',
    'stabilize_page_notexists'     => 'The target page does not exist.',
    'stabilize_page_unreviewable'  => 'The target page is not in reviewable namespace.',
    'stabilize_invalid_precedence' => 'Invalid version precedence.',
    'stabilize_invalid_autoreview' => 'Invalid autoreview restriction.',
    'stabilize_invalid_level'      => 'Invalid protection level.',
	'stabilize_expiry_invalid'     => 'Invalid expiration date.',
	'stabilize_expiry_old'         => 'This expiration time has already passed.',
    'stabilize_denied'             => 'Permission denied.',
    'stabilize_protect_quota'      => 'The maximum number of currently flag-protected pages has already been reached.', # do not translate
	'stabilize-expiring'           => 'expires $1 (UTC)',
	'stabilization-review'         => 'Mark the current revision checked',
);

/** Message documentation (Message documentation)
 * @author EugeneZelenko
 * @author Fryed-peach
 * @author Jon Harald Søby
 * @author Purodha
 * @author Raymond
 * @author Robby
 * @author SPQRobin
 * @author Saper
 * @author Umherirrender
 */
$messages['qqq'] = array(
	'stabilization-tab' => '{{Flagged Revs-small}}

Some skins (e.g. standard/classic) display an additional tab to control visibility of the page revisions, e.g. whether last revision should be included or perhaps the last sighted or accepted version.',
	'stabilization' => '{{Flagged Revs-small}}
Page title of Special:Stabilization.',
	'stabilization-text' => '{{Flagged Revs-small}}

Information displayed on Special:Stabilization.

"stable version selection" is the same as {{msg-mw|Stabilization-select}}.',
	'stabilization-perm' => '{{Flagged Revs-small}}
Used on Special:Stabilization when the user has not the permission to change the settings.',
	'stabilization-page' => '{{Flagged Revs}}
{{Identical|Page name}}',
	'stabilization-leg' => '{{Flagged Revs}}',
	'stabilization-select' => '{{Flagged Revs}}',
	'stabilization-select1' => '{{Flagged Revs-small}}
Used on Special:Stabilization as an option for "How the accepted version is selected".',
	'stabilization-select2' => '{{Flagged Revs-small}}
Used on Special:Stabilization as an option for "How the accepted version is selected".',
	'stabilization-select3' => '{{Flagged Revs}}',
	'stabilization-def' => '{{Flagged Revs}}',
	'stabilization-def1' => '{{Flagged Revs-small}}
Used on Special:Stabilization as an option for "Revision displayed on default page view".

This option has sub-options, see "How the accepted version is selected".',
	'stabilization-def2' => '{{Flagged Revs-small}}
Used on Special:Stabilization as an option for "Revision displayed on default page view".',
	'stabilization-restrict' => '{{Flagged Revs}}
This means: "restrictions on automatic reviews" (\'\'it does not mean: 
"automatically review the restrictions")

See http://en.labs.wikimedia.org/wiki/Special:Stabilization/Main_Page for more information (you can give yourself review rights)',
	'stabilization-restrict-none' => '{{Flagged Revs}}',
	'stabilization-submit' => '{{Flagged Revs}}
{{Identical|Confirm}}',
	'stabilization-notexists' => '{{Flagged Revs}}',
	'stabilization-notcontent' => '{{Flagged Revs}}',
	'stabilization-comment' => '{{Flagged Revs}}
{{Identical|Reason}}',
	'stabilization-otherreason' => '{{Flagged Revs}}
{{Identical|Other reason}}',
	'stabilization-expiry' => '{{Flagged Revs}}
{{Identical|Expires}}',
	'stabilization-othertime' => '{{Flagged Revs}}',
	'stabilization-sel-short' => '{{Flagged Revs}}',
	'stabilization-sel-short-0' => '{{Flagged Revs}}',
	'stabilization-sel-short-1' => '{{Flagged Revs}}
{{Identical|None}}',
	'stabilization-sel-short-2' => '{{Flagged Revs}}',
	'stabilization-def-short' => '{{Flagged Revs}}
{{Identical|Default}}',
	'stabilization-def-short-0' => '{{Flagged Revs}}
{{Identical|Current}}',
	'stabilization-def-short-1' => '{{Flagged Revs}}
{{Identical|Stable}}',
	'stabilize_expiry_invalid' => '{{Flagged Revs}}',
	'stabilize_expiry_old' => '{{Flagged Revs}}',
	'stabilize-expiring' => "{{Flagged Revs}}
Used to indicate when something expires.
$1 is a time stamp in the wiki's content language.
$2 is the corresponding date in the wiki's content language.
$3 is the corresponding time in the wiki's content language.

{{Identical|Expires $1 (UTC)}}",
	'stabilization-review' => '{{Flagged Revs}}',
);

/** Afrikaans (Afrikaans)
 * @author Arnobarnard
 * @author Naudefj
 */
$messages['af'] = array(
	'stabilization' => 'Bladsy-stabilisasie',
	'stabilization-page' => 'Bladsynaam:',
	'stabilization-def2' => 'Die huidige/werkweergawe',
	'stabilization-restrict-none' => 'Geen addisionele beperkinge',
	'stabilization-submit' => 'Bevestig',
	'stabilization-notexists' => 'Daar is geen bladsy genaamd "[[:$1|$1]]" nie.
Geen konfigurasie is moontlik nie.',
	'stabilization-comment' => 'Rede:',
	'stabilization-otherreason' => 'Ander rede:',
	'stabilization-expiry' => 'Verval:',
	'stabilization-othertime' => 'Ander tyd:',
	'stabilization-sel-short' => 'Voorrang',
	'stabilization-sel-short-0' => 'Kwaliteit',
	'stabilization-sel-short-1' => 'Geen',
	'stabilization-sel-short-2' => 'Ongerep',
	'stabilization-def-short' => 'Standaard',
	'stabilization-def-short-0' => 'Huidig',
	'stabilization-def-short-1' => 'Gepubliseer',
	'stabilize_expiry_invalid' => 'Ongeldige vervaldatum.',
	'stabilize_expiry_old' => 'Die vervaldatum is reeds verby.',
	'stabilize-expiring' => 'verval $1 (UTC)',
);

/** Gheg Albanian (Gegë)
 * @author Mdupont
 */
$messages['aln'] = array(
	'stabilization-tab' => 'veteriner',
	'stabilization' => 'stabilizimin e faqes',
	'stabilization-text' => "''' Ndryshimi parametrat e mëposhtëm për të rregulluar si versionin e publikuar i [[:\$1|\$1]] është zgjedhur dhe të shfaqet. '''

'''Shënim:''' ndryshim ''botuar versionin e përzgjedhjes'' të preferojnë \"cilësisë\" apo \"i pacenuar\" versione do të ketë efekt në qoftë se nuk ka versione të tilla. Gjithashtu, theksohet se një \"cilësi\" version është konsideruar gjithashtu një \"kontrolluar\" versionin e kështu me radhë.",
	'stabilization-perm' => 'Llogaria juaj nuk ka leje për të ndryshuar konfigurimin versionin e botuar. Këtu janë parametrat aktual për [[:$1|$1]]:',
	'stabilization-page' => 'Emri i faqes:',
	'stabilization-leg' => 'Paneli i Konfirmo publikuar versionin',
	'stabilization-select' => 'Publikuar zgjedhjen version përparësi',
	'stabilization-select1' => 'rishikimin e fundit të cilësisë; pastaj e fundit të kthjellët',
	'stabilization-select2' => 'version i fundit i zgjedhur',
	'stabilization-select3' => 'version i fundit i pacenuar; pastaj të fundit një cilësi, atëherë e fundit të kthjellët',
	'stabilization-def' => 'Revision shfaqet në faqe të parë default',
	'stabilization-def1' => 'Versioni i publikuar, e nëse nuk është i pranishëm, atëherë / draftin aktual',
	'stabilization-def2' => 'Aktuale / rishikim projekt',
	'stabilization-restrict' => 'Rishikimi / auto-përmbledhje kufizime',
	'stabilization-restrict-none' => 'Nuk ka kufizime shtesë',
	'stabilization-submit' => 'Konfirmoj',
	'stabilization-notexists' => 'Nuk ka asnjë faqe quhet "[[:$1|$1]] ". Nuk konfigurimit është e mundur.',
	'stabilization-notcontent' => 'Faqja "[[:$1|$1]] "nuk mund të rishikohet. Nr konfigurimit është e mundur.',
	'stabilization-comment' => 'Arsyeja:',
	'stabilization-otherreason' => 'arsye të tjera:',
	'stabilization-expiry' => 'Skadon:',
	'stabilization-othertime' => 'kohë të tjera:',
	'stabilization-sel-short' => 'Përparësi',
	'stabilization-sel-short-0' => 'Cilësi',
	'stabilization-sel-short-1' => 'Asnjë',
	'stabilization-sel-short-2' => 'I pacenuar',
	'stabilization-def-short' => 'Default',
	'stabilization-def-short-0' => 'Aktual',
	'stabilization-def-short-1' => 'Publikuar',
	'stabilize_page_invalid' => 'Faqja e objektivit titull është i pavlefshëm.',
	'stabilize_page_notexists' => 'Faqja objektiv nuk ekziston.',
	'stabilize_page_unreviewable' => 'Faqja objektivi nuk është në hapësirën rishikueshme.',
	'stabilize_invalid_precedence' => 'përparësi e pavlefshme version.',
	'stabilize_invalid_autoreview' => 'kufizimin e pavlefshme autoreview',
	'stabilize_invalid_level' => 'nivelin e pavlefshme mbrojtje.',
	'stabilize_expiry_invalid' => 'data e skadimit pavlefshme.',
	'stabilize_expiry_old' => 'Kjo kohë ka kaluar skadimit tashmë.',
	'stabilize-expiring' => 'kalon $1 (UTC)',
	'stabilization-review' => 'Mark versionin e fundit kontrolluar',
);

/** Amharic (አማርኛ)
 * @author Codex Sinaiticus
 */
$messages['am'] = array(
	'stabilization-comment' => 'ማጠቃለያ፦',
	'stabilization-def-short-0' => 'ያሁኑኑ',
);

/** Aragonese (Aragonés)
 * @author Juanpabl
 */
$messages['an'] = array(
	'stabilization-tab' => '(compreb)',
	'stabilization' => "Estabilizazión d'a pachina",
	'stabilization-text' => "'''Si quiere achustar cómo se triga y amuestra a bersión estable de [[:$1|$1]] cambee a confegurazión más tabaixo.'''",
	'stabilization-perm' => "A suya cuenta no tiene premisos ta cambiar a confegurazión d'a bersión estable. Os achustes autuals ta [[:$1|$1]] s'amuestran aquí:",
	'stabilization-page' => "Nombre d'a pachina:",
	'stabilization-leg' => "Confirmar a confegurazión d'a bersión estable",
	'stabilization-select' => "Triga d'a bersión estable",
	'stabilization-select1' => "A zaguera bersión de calidat; si no bi'n ha, alabez a zaguera bersión superbisata",
	'stabilization-select2' => 'A zaguera bersión rebisata',
	'stabilization-select3' => "A zaguera bersión zanzera; si bi'n ha, alabez a zaguera bersión de calidat u rebisata.",
	'stabilization-def' => "A rebisión s'amuestra en a pachina de bisualizazión por defeuto",
	'stabilization-def1' => "A bersión estable; si no bi'n ha, alabez a bersión autual",
	'stabilization-def2' => 'A bersión autual',
	'stabilization-submit' => 'Confirmar',
	'stabilization-notexists' => 'No bi ha garra pachina tetulata "[[:$1|$1]]". 
No ye posible confegurar-la.',
	'stabilization-notcontent' => 'A pachina "[[:$1|$1]]" no se puede rebisar.
No ye posible confegurar-la.',
	'stabilization-comment' => 'Razón:',
	'stabilization-otherreason' => 'Atra razón:',
	'stabilization-expiry' => 'Zircunduze:',
	'stabilization-sel-short' => 'Prezendenzia',
	'stabilization-sel-short-0' => 'Calidat',
	'stabilization-sel-short-1' => 'Denguna',
	'stabilization-sel-short-2' => 'Zanzera',
	'stabilization-def-short' => 'Por defeuto',
	'stabilization-def-short-0' => 'Autual',
	'stabilization-def-short-1' => 'Estable',
	'stabilize_expiry_invalid' => 'A calendata de zircunduzión no ye conforme.',
	'stabilize_expiry_old' => 'Ista calendata de zircunduzión ya ye pasata.',
	'stabilize-expiring' => 'zircunduze o $1 (UTC)',
);

/** Arabic (العربية)
 * @author Alnokta
 * @author Meno25
 * @author OsamaK
 */
$messages['ar'] = array(
	'stabilization-tab' => 'تج',
	'stabilization' => 'تثبيت الصفحة',
	'stabilization-text' => "'''غير الإعدادات بالأسفل لضبط الكيفية التي بها النسخة المنشورة من [[:\$1|\$1]] يتم اختيارها وعرضها.'''

عند تغيير ضبط ''اختيار النسخة المنشورة'' لاستخدام مراجعات \"الجودة\" أو \"الفائقة\" افتراضيا،
تأكد من التحقق من وجود مراجعات كهذه في الصفحة، وإلا فإن التغيير سيكون له تأثير ضعيف.",
	'stabilization-perm' => 'حسابك لا يمتلك الصلاحية لتغيير إعدادات النسخة المنشورة.
هنا الإعدادات الحالية ل[[:$1|$1]]:',
	'stabilization-page' => 'اسم الصفحة:',
	'stabilization-leg' => 'تأكيد إعدادات النسخة المنشورة',
	'stabilization-select' => 'سابقة اختيار النسخة المنشورة',
	'stabilization-select1' => 'آخر مراجعة جودة؛ لو غير موجودة، إذا آخر واحدة منظورة',
	'stabilization-select2' => 'آخر مراجعة مراجعة (بعض النظر عن مستوى التحقيق)',
	'stabilization-select3' => 'آخر مراجعة فائقة؛ لو غير موجودة، إذا آخر مراجعة جودة أو منظورة',
	'stabilization-def' => 'المراجعة المعروضة عند رؤية الصفحة افتراضيا',
	'stabilization-def1' => 'المراجعة المنشورة؛ لو غير موجودة، إذا المراجعة الحالية/المسودة',
	'stabilization-def2' => 'المراجعة الحالية/المسودة',
	'stabilization-restrict' => 'ضوابط المراجعة التلقائية',
	'stabilization-restrict-none' => 'لا ضوابط إضافية',
	'stabilization-submit' => 'تأكيد',
	'stabilization-notexists' => 'لا توجد صفحة بالاسم "[[:$1|$1]]".
لا ضبط ممكن.',
	'stabilization-notcontent' => 'الصفحة "[[:$1|$1]]" لا يمكن مراجعتها.
لا ضبط ممكن.',
	'stabilization-comment' => 'السبب:',
	'stabilization-otherreason' => 'سبب آخر:',
	'stabilization-expiry' => 'تنتهي:',
	'stabilization-othertime' => 'وقت آخر:',
	'stabilization-sel-short' => 'تنفيذ',
	'stabilization-sel-short-0' => 'جودة',
	'stabilization-sel-short-1' => 'لا شيء',
	'stabilization-sel-short-2' => 'فائقة',
	'stabilization-def-short' => 'افتراضي',
	'stabilization-def-short-0' => 'حالي',
	'stabilization-def-short-1' => 'منشورة',
	'stabilize_expiry_invalid' => 'تاريخ انتهاء غير صحيح.',
	'stabilize_expiry_old' => 'تاريخ الانتهاء هذا مر بالفعل.',
	'stabilize-expiring' => 'تنتهي في $1 (UTC)',
	'stabilization-review' => 'راجع النسخة الحالية',
);

/** Aramaic (ܐܪܡܝܐ)
 * @author Basharh
 */
$messages['arc'] = array(
	'stabilization-page' => 'ܫܡܐ ܕܦܐܬܐ:',
);

/** Egyptian Spoken Arabic (مصرى)
 * @author Dudi
 * @author Meno25
 * @author Ramsis II
 */
$messages['arz'] = array(
	'stabilization-tab' => 'تج',
	'stabilization' => 'تثبيت الصفحة',
	'stabilization-text' => "'''غيّر التظبيطات اللى تحت علشان تظبط ازاى تختار و تبيّن النسخه المنشوره بتاعة [[:\$1|\$1]].'''

لما تغيّر الظبطه بتاعة ''اختيار النسخه المنشوره'' علشان تستعمل مراجعات ليها \"جوده\" او \"نقيّه\" فى الظبطه الاساسيه,
اتأكد لو كان فعلا موجود مراجعات زى كده فى الصفحه, ياإما التغيير حيبقى ليه تأثير قليل.",
	'stabilization-perm' => 'حسابك ما عندوش اذن علشان يغيّر ظبطة النسخه المنشوره.
هنا التظبيطات بتاعة دلوقتى لـ [[:$1|$1]]:',
	'stabilization-page' => 'اسم الصفحة:',
	'stabilization-leg' => 'اتأكيد من تظبيطات النسخه المنشوره',
	'stabilization-select' => 'اختيار النسخه المنشوره اللى فبل كده',
	'stabilization-select1' => 'اجدد مراجعة جوده; بعدين اجدد واحد متشافه',
	'stabilization-select2' => 'آخر مراجعه مراجعه (بعض النظر عن مستوى التحقيق)',
	'stabilization-select3' => 'اجدد مراجعه نقيّه; بعدين اجدد واحده جوده; بعدين اجدد واحده متشافه',
	'stabilization-def' => 'المراجعه المعروضه عند رؤيه الصفحه افتراضيا',
	'stabilization-def1' => 'المراجعه المنشوره; لو مش موجوده, يبقى المراجعه بتاعة دلوقتى/المسوده',
	'stabilization-def2' => 'المراجعه الحالية/المسودة',
	'stabilization-restrict' => 'ضوابط المراجعه التلقائية',
	'stabilization-restrict-none' => 'لا ضوابط إضافية',
	'stabilization-submit' => 'تأكيد',
	'stabilization-notexists' => 'لا توجد صفحه بالاسم "[[:$1|$1]]".
لا ضبط ممكن.',
	'stabilization-notcontent' => 'الصفحه "[[:$1|$1]]" لا يمكن مراجعتها.
لا ضبط ممكن.',
	'stabilization-comment' => 'السبب:',
	'stabilization-otherreason' => 'سبب آخر:',
	'stabilization-expiry' => 'تنتهي:',
	'stabilization-othertime' => 'وقت آخر:',
	'stabilization-sel-short' => 'تنفيذ',
	'stabilization-sel-short-0' => 'جودة',
	'stabilization-sel-short-1' => 'لا شيء',
	'stabilization-sel-short-2' => 'فائقة',
	'stabilization-def-short' => 'افتراضي',
	'stabilization-def-short-0' => 'حالي',
	'stabilization-def-short-1' => 'منشوره',
	'stabilize_expiry_invalid' => 'تاريخ انتهاء غير صحيح.',
	'stabilize_expiry_old' => 'تاريخ الانتهاء هذا مر بالفعل.',
	'stabilize-expiring' => 'تنتهى فى $1 (UTC)',
	'stabilization-review' => 'راجع النسخه الحالية',
);

/** Asturian (Asturianu)
 * @author Esbardu
 */
$messages['ast'] = array(
	'stabilization-tab' => '(aq)',
	'stabilization' => 'Estabilización de páxines',
	'stabilization-text' => "'''Camudar la configuración d'embaxo p'axustar cómo se seleiciona y s'amuesa la versión estable de [[:$1|$1]].'''",
	'stabilization-perm' => 'La to cuenta nun tienen permisos pa camudar la configuración de la versión estable.
Esta ye la configuración de [[:$1|$1]]:',
	'stabilization-page' => 'Nome de la páxina:',
	'stabilization-leg' => 'Confirmar la configuración de la versión estable',
	'stabilization-select' => 'Seleición de la versión estable',
	'stabilization-select1' => 'La cabera revisión calidable; si nun la hai, entós la cabera vista',
	'stabilization-select2' => 'La cabera revisión revisada',
	'stabilization-def' => 'Revisión amosada na vista de páxina por defeutu',
	'stabilization-def1' => "La revisión estable; si nun la hai, entós l'actual",
	'stabilization-def2' => 'La revisión actual',
	'stabilization-submit' => 'Confirmar',
	'stabilization-notexists' => 'Nun esiste la páxina "[[:$1|$1]]". Nun ye posible la configuración.',
	'stabilization-notcontent' => 'La páxina "[[:$1|$1]]" nun pue ser revisada. Nun ye posible la configuración.',
	'stabilization-comment' => 'Comentariu:',
	'stabilization-expiry' => 'Caduca:',
	'stabilization-sel-short' => 'Prioridá',
	'stabilization-sel-short-0' => 'Calidable',
	'stabilization-sel-short-1' => 'Nenguna',
	'stabilization-def-short' => 'Por defeutu',
	'stabilization-def-short-0' => 'Actual',
	'stabilization-def-short-1' => 'Estable',
	'stabilize_expiry_invalid' => 'Fecha de caducidá non válida.',
	'stabilize_expiry_old' => 'Esta caducidá yá tien pasao.',
	'stabilize-expiring' => "caduca'l $1 (UTC)",
);

/** Bavarian (Boarisch)
 * @author Man77
 */
$messages['bar'] = array(
	'stabilization-submit' => 'Bestäting',
	'stabilization-comment' => 'Grund:',
	'stabilization-otherreason' => 'Ãndara Grund:',
	'stabilization-expiry' => 'Güit bis:',
	'stabilization-othertime' => 'Ãndare Zeid:',
);

/** Southern Balochi (بلوچی مکرانی)
 * @author Mostafadaneshvar
 */
$messages['bcc'] = array(
	'stabilization-tab' => 'وت',
	'stabilization' => '‏ثبات کتن صفحه',
	'stabilization-text' => "''''عوض کن تنظیمات جهلی په شرکتن شی چه چطورکا نسخه ثابت  [[:$1|$1]]  انتخاب و پیش دارگ بیت''''",
	'stabilization-perm' => 'شمی حساب اجازت به عوض کتن تنظیمات نسخه ثابت نیست.
ادان هنوکین تنظیمات په  [[:$1|$1]]:',
	'stabilization-page' => 'نام صفحه:',
	'stabilization-leg' => 'تنظیمات نسخه ثابت تایید کن',
	'stabilization-select' => 'انتخاب نسخه ثابت',
	'stabilization-select1' => 'آهری بازبینی کیفیت؛اگر نیست؛اچه اهرین رویت بیتگن',
	'stabilization-select2' => 'آهری دیستگین بازبینی',
	'stabilization-select3' => 'آهری بازبینی دست نوارتگین، اگه نیست، رندا آهری کیفیت یا رویتء',
	'stabilization-def' => 'بازبینی ته پیش فرضین دیستن جاهکیت',
	'stabilization-def1' => 'ثابتین بازبینی; اگر نیست، گوڈء هنوکین',
	'stabilization-def2' => 'هنوکین بازبینی',
	'stabilization-submit' => 'تایید',
	'stabilization-notexists' => 'صفحه ای په نام "[[:$1|$1]]" نیست.
هچ تنظیمی ممکن نهنت.',
	'stabilization-notcontent' => 'صفحه "[[:$1|$1]]" نه تونیت باز بینی بیت.
هچ تنظیمی ممکن نهنت.',
	'stabilization-comment' => 'نظر:',
	'stabilization-expiry' => 'هلیت:',
	'stabilization-sel-short' => 'تقدم',
	'stabilization-sel-short-0' => 'کیفیت',
	'stabilization-sel-short-1' => 'هچ یک',
	'stabilization-sel-short-2' => 'اولین',
	'stabilization-def-short' => 'پیش فرض',
	'stabilization-def-short-0' => 'هنوکین',
	'stabilization-def-short-1' => 'ثابت',
	'stabilize_expiry_invalid' => 'نامعتبرین تاریخ هلگ',
	'stabilize_expiry_old' => 'ای زمان انقضا هنو هلتت.',
	'stabilize-expiring' => 'وهدی هلیت  $1 (UTC)',
);

/** Belarusian (Taraškievica orthography) (Беларуская (тарашкевіца))
 * @author EugeneZelenko
 * @author Jim-by
 */
$messages['be-tarask'] = array(
	'stabilization-tab' => 'Бачная вэрсія старонкі',
	'stabilization' => 'Стабілізацыя старонкі',
	'stabilization-text' => "'''Зьмяніце ўстаноўкі ніжэй, якім чынам павінна выбірацца і паказвацца апублікаваная вэрсія старонкі [[:$1|$1]].'''

'''Заўвага:''' Калі будзеце зьмяняць устаноўкі ''выбару апублікаванай вэрсіі'' для выкарыстаньня па змоўчваньні «якаснай» альбо «першапачатковай» вэрсіі, упэўніцеся што старонка мае такія вэрсіі, у адваротным выпадку зьмены не прынясуць значнага эфэкту. Таксама заўважце, што «правераныя» вэрсіі лічацца «якаснымі» аўтаматычна.",
	'stabilization-perm' => 'Ваш рахунак ня мае правоў для зьмены канфігурацыі апублікаванай вэрсіі.
Тут пададзеныя цяперашнія ўстаноўкі для [[:$1|$1]]:',
	'stabilization-page' => 'Назва старонкі:',
	'stabilization-leg' => 'Пацьвердзіць устаноўкі апублікаванай вэрсіі',
	'stabilization-select' => 'Парадак выбару апублікаванай вэрсіі',
	'stabilization-select1' => 'Апошняя якасная вэрсія; калі яе няма, то самая апошняя з прагледжаных',
	'stabilization-select2' => 'Апошняя правераная вэрсія',
	'stabilization-select3' => 'Апошняя першапачатковая вэрсія; калі яе няма, то апошняя якасная альбо прагледжаная',
	'stabilization-def' => 'Вэрсія, якая паказваецца па змоўчваньні',
	'stabilization-def1' => 'Апублікаваная вэрсія; калі яе не існуе, то цяперашняя/чарнавая',
	'stabilization-def2' => 'Цяперашняя/чарнавая вэрсія',
	'stabilization-restrict' => 'Абмежаваньні праверкі/аўтаматычнай праверкі',
	'stabilization-restrict-none' => 'Няма дадатковых абмежаваньняў',
	'stabilization-submit' => 'Пацьвердзіць',
	'stabilization-notexists' => 'Не існуе старонкі з назвай «[[:$1|$1]]».
Немагчыма зьмяніць устаноўкі.',
	'stabilization-notcontent' => 'Старонка «[[:$1|$1]]» ня можа быць правераная.
Немагчыма зьмяніць устаноўкі.',
	'stabilization-comment' => 'Прычына:',
	'stabilization-otherreason' => 'Іншая прычына:',
	'stabilization-expiry' => 'Тэрмін:',
	'stabilization-othertime' => 'Іншы час:',
	'stabilization-sel-short' => 'Першаснасьць',
	'stabilization-sel-short-0' => 'Якасьць',
	'stabilization-sel-short-1' => 'Няма',
	'stabilization-sel-short-2' => 'Першапачатковая',
	'stabilization-def-short' => 'Па змоўчваньні',
	'stabilization-def-short-0' => 'Цяперашняя',
	'stabilization-def-short-1' => 'Апублікаваная',
	'stabilize_page_invalid' => 'Няслушная назва мэтавай старонкі.',
	'stabilize_page_notexists' => 'Мэтавая старонка не існуе.',
	'stabilize_page_unreviewable' => 'Мэтавай старонкі няма ў прасторы назваў, якую можна рэцэнзаваць.',
	'stabilize_invalid_precedence' => 'Няслушны прыярытэт вэрсіяў.',
	'stabilize_invalid_autoreview' => 'Няслушнае абмежаваньне аўтаматычнага рэцэнзаваньня',
	'stabilize_invalid_level' => 'Няслушны ўзровень абароны.',
	'stabilize_expiry_invalid' => 'Няслушны тэрмін.',
	'stabilize_expiry_old' => 'Час сканчэньня ўжо прайшоў.',
	'stabilize_denied' => 'Доступ забаронены.',
	'stabilize-expiring' => 'канчаецца $1 (UTC)',
	'stabilization-review' => 'Пазначыць цяперашнюю вэрсію як правераную',
);

/** Bulgarian (Български)
 * @author Borislav
 * @author DCLXVI
 * @author Turin
 */
$messages['bg'] = array(
	'stabilization' => 'Устойчивост на страницата',
	'stabilization-page' => 'Име на страницата:',
	'stabilization-leg' => 'Потвърждение на настройките за устойчива версия',
	'stabilization-select1' => 'Последната качествена версия; ако няма такава, тогава последната прегледана',
	'stabilization-select2' => 'Последната рецензирана версия',
	'stabilization-def1' => 'Устойчивата версия; ако няма такава, тогава текущата',
	'stabilization-def2' => 'Текущата версия или чернова',
	'stabilization-restrict-none' => 'Няма допълнителни ограничения',
	'stabilization-submit' => 'Потвърждаване',
	'stabilization-notexists' => 'Не съществува страница „[[:$1|$1]]“. Не е възможно конфигуриране.',
	'stabilization-comment' => 'Причина:',
	'stabilization-otherreason' => 'Друга причина:',
	'stabilization-expiry' => 'Изтича на:',
	'stabilization-othertime' => 'Друго време:',
	'stabilization-sel-short' => 'Предимство',
	'stabilization-sel-short-0' => 'Качество',
	'stabilization-sel-short-1' => 'Никоя',
	'stabilization-def-short' => 'По подразбиране',
	'stabilization-def-short-0' => 'Текуща',
	'stabilization-def-short-1' => 'Устойчива',
	'stabilize_expiry_invalid' => 'Невалидна дата на изтичане.',
	'stabilize_expiry_old' => 'Дата на изтичане вече е отминала.',
	'stabilize-expiring' => 'изтича на $1 (UTC)',
);

/** Bengali (বাংলা)
 * @author Bellayet
 * @author Zaheen
 */
$messages['bn'] = array(
	'stabilization-tab' => '(qa)',
	'stabilization' => 'পাতা স্থিতিকরণ',
	'stabilization-page' => 'পাতার নাম:',
	'stabilization-def2' => 'বর্তমান/খসড়া সংশোধন',
	'stabilization-submit' => 'নিশ্চিত করো',
	'stabilization-comment' => 'কারণ:',
	'stabilization-expiry' => 'মেয়াদ উত্তীর্ণ:',
	'stabilization-othertime' => 'অন্য সময়:',
	'stabilization-sel-short' => 'অগ্রাধিকার',
	'stabilization-sel-short-0' => 'গুণ',
	'stabilization-sel-short-1' => 'কিছু না',
	'stabilization-def-short' => 'পূর্বনির্ধারিত',
	'stabilization-def-short-0' => 'বর্তমান',
	'stabilization-def-short-1' => 'সুদৃঢ়',
	'stabilize_expiry_invalid' => 'অবৈধ মেয়াদ উত্তীর্ণের তারিখ।',
	'stabilize_expiry_old' => 'মেয়াদ উত্তীর্ণের সময় পার হয়ে গেছে।',
	'stabilize-expiring' => 'মেয়াদ উত্তীর্ণ হবে $1 (UTC)',
);

/** Breton (Brezhoneg)
 * @author Fohanno
 * @author Fulup
 * @author Y-M D
 */
$messages['br'] = array(
	'stabilization-tab' => 'argas',
	'stabilization' => 'Stabiladur ar bajenn',
	'stabilization-text' => "'''Cheñch ar c'hefluniadur dindan da spisaat an doare ma vez diuzet ha diskwelet stumm embannet [[:\$1|\$1]].'''

'''Notenn :''' pa vez cheñchet ''diuzadenn ar stumm embannet'' da gavout gwell stummoù \"perzhded\" pe \"deraouiñ\" ne vez tamm efed ebet ma n'eus ket eus ar stummoù-se. Notit ivez ervat e seller ouzh ur stumm \"perzhded\" evel ouzh stumm \"gwiriet\" ha kement zo.",
	'stabilization-perm' => "N'eo ket aotreet ho kont da gemmañ arventennoù ar stumm embannet.
Setu an arventennoù red eus [[:$1|$1]] :",
	'stabilization-page' => 'Anv ar bajenn :',
	'stabilization-leg' => 'Kadarnaat arventennoù ar stumm embannet',
	'stabilization-select' => 'kentwir diuzadur ar stumm embannet',
	'stabilization-select1' => 'An adweladenn ziwezhañ a galite, mod all ar stumm bet gwelet da ziwezhañ',
	'stabilization-select2' => 'An adweladenn ziwezhañ bet gwiriet',
	'stabilization-select3' => "Stumm klok diwezhañ; ma n'ues ket, neuze an hini mat diwezhañ pe adlennet da ziwezhañ",
	'stabilization-def' => 'Stumm diskwelet er mod diskwel dre ziouer',
	'stabilization-def1' => 'Ar stumm embannet ma vez; a-hend-all lakaat ar stumm red pe ar brouilhed',
	'stabilization-def2' => 'Ar stumm red pe ar brouilhed',
	'stabilization-restrict' => 'Strishadurioù adlenn/adlenn emgefre',
	'stabilization-restrict-none' => 'Strishadurioù ouzhpenn ebet',
	'stabilization-submit' => 'Kadarnaat',
	'stabilization-notexists' => 'N\'eus pajenn ebet anvet "[[:$1|$1]]".
N\'haller ket kefluniañ netra.',
	'stabilization-notcontent' => 'N\'hall ket ar bajenn "[[:$1|$1]]" bezañ adwelet.
N\'haller ket kefluniañ netra.',
	'stabilization-comment' => 'Abeg :',
	'stabilization-otherreason' => 'Abeg all :',
	'stabilization-expiry' => "A ya d'e dermen",
	'stabilization-othertime' => 'Mare all :',
	'stabilization-sel-short' => 'Kentwir',
	'stabilization-sel-short-0' => 'Perzhded',
	'stabilization-sel-short-1' => 'Hini ebet',
	'stabilization-sel-short-2' => 'Anterin',
	'stabilization-def-short' => 'Dre ziouer',
	'stabilization-def-short-0' => 'Red',
	'stabilization-def-short-1' => 'Embannet',
	'stabilize_page_invalid' => 'Fall eo titl ar bajenn buket.',
	'stabilize_page_notexists' => "N'eus ket eus ar bajenn buket.",
	'stabilize_page_unreviewable' => "N'emañ ket ar bajenn buket en un esaouenn anv a c'haller adwelet",
	'stabilize_invalid_precedence' => 'Urzh ar stumm direizh',
	'stabilize_invalid_autoreview' => 'Strishadur adlenn emgefre direizh',
	'stabilize_invalid_level' => 'Live gwareziñ direizh.',
	'stabilize_expiry_invalid' => 'Direizh eo an deiziad termen.',
	'stabilize_expiry_old' => 'Tremenet eo dija an amzer termen-se.',
	'stabilize_denied' => "Aotre nac'het.",
	'stabilize-expiring' => "Termenet d'an $1 (UTC)",
	'stabilization-review' => 'Merkañ ar stumm red evel adwelet.',
);

/** Bosnian (Bosanski)
 * @author CERminator
 */
$messages['bs'] = array(
	'stabilization-tab' => 'konfig',
	'stabilization' => 'Stabilizacija stranice',
	'stabilization-text' => "'''Promijenite postavke ispod da biste podesili kako će se stabilna verzija stranice [[:\$1|\$1]] odabrati i prikazati.'''

Kada mijenjate konfiguraciju ''odabir stabilne verzije'' za korištenje \"kvalitetnih\" ili \"starih\" revizija po prepostavljenom, provjerite da li zaista postoje takve revizije stranice, u suprotnom će promjena imati malo uticaja.",
	'stabilization-perm' => '!Vaš račun nema dopuštenje da mijenja konfiguraciju objavljenje verzije.
Ovdje su trenutne postavke za [[:$1|$1]]:',
	'stabilization-page' => 'Naslov stranice:',
	'stabilization-leg' => 'Potvrdite postavke objavljene verzije',
	'stabilization-select' => 'Prioritet odabira objavljene verzije',
	'stabilization-select1' => 'Posljednja kvalitetna revizija, ako je nema, onda zadnja provjerena',
	'stabilization-select2' => 'Posljednja pregledana revizija, bez obzira na nivo provjere',
	'stabilization-select3' => 'Posljednja stara revizija, ako je nema, onda posljednja kvalitetna ili pregledana',
	'stabilization-def' => 'Revizija prikazana kao pretpostavljena stranica',
	'stabilization-def1' => 'Objavljena revizija, ako je nema, onda trenutna/radna verzija',
	'stabilization-def2' => 'Trenutna/radna revizija',
	'stabilization-restrict' => 'Ograničenja za automatske preglede',
	'stabilization-restrict-none' => 'Bez posebnih ograničenja',
	'stabilization-submit' => 'Potvrdi',
	'stabilization-notexists' => 'Nema stranice pod nazivom "[[:$1|$1]]".
Nije moguća konfiguracija.',
	'stabilization-notcontent' => 'Stranica "[[:$1|$1]]" ne može biti provjerena.
Nije moguća konfiguracija.',
	'stabilization-comment' => 'Razlog:',
	'stabilization-otherreason' => 'Ostali razlozi:',
	'stabilization-expiry' => 'Ističe:',
	'stabilization-othertime' => 'Ostali period:',
	'stabilization-sel-short' => 'Prvenstvo',
	'stabilization-sel-short-0' => 'Kvalitet',
	'stabilization-sel-short-1' => 'nema',
	'stabilization-sel-short-2' => 'Zastarijelo',
	'stabilization-def-short' => 'Standardno',
	'stabilization-def-short-0' => 'Trenutna',
	'stabilization-def-short-1' => 'Objavljeno',
	'stabilize_expiry_invalid' => 'Nevaljan datum isticanja.',
	'stabilize_expiry_old' => 'Ovo vrijeme isticanja je već prošlo.',
	'stabilize-expiring' => 'ističe $1 (UTC)',
	'stabilization-review' => 'Provjerite trenutnu verziju',
);

/** Catalan (Català)
 * @author Aleator
 * @author Jordi Roqué
 * @author Paucabot
 * @author Qllach
 * @author Toniher
 */
$messages['ca'] = array(
	'stabilization-page' => 'Nom de la pàgina:',
	'stabilization-def2' => 'La revisió actual/esborrany',
	'stabilization-submit' => 'Confirma',
	'stabilization-notexists' => 'No hi ha cap pàgina que s\'anomeni "[[:$1|$1]]".
No és possible fer cap configuració.',
	'stabilization-comment' => 'Motiu:',
	'stabilization-expiry' => 'Venç:',
	'stabilization-sel-short' => 'Precedència',
	'stabilization-sel-short-0' => 'Qualitat',
	'stabilization-sel-short-1' => 'Cap',
	'stabilization-def-short' => 'Per defecte',
	'stabilization-def-short-0' => 'Actual',
	'stabilization-def-short-1' => 'Publicat',
	'stabilize_expiry_invalid' => 'La data de venciment no és vàlida.',
	'stabilize_expiry_old' => 'Aquesta data de venciment ja ha passat.',
	'stabilize-expiring' => 'expira $1 (UTC)',
);

/** Czech (Česky)
 * @author Danny B.
 * @author Li-sung
 * @author Matěj Grabovský
 * @author Mormegil
 */
$messages['cs'] = array(
	'stabilization-tab' => 'stabilizace',
	'stabilization' => 'Stabilizace stránky',
	'stabilization-text' => "'''Změňte nastavení níže pro přizpůsobení toho, jak se vybírá stabilní verze stránky [[:$1|$1]] a co se zobrazí.'''

Při změně nastavení ''přednost výběru stabilní verze'', aby se standardně používaly „kvalitní“ nebo „čisté“ revize se ujistěte, že skutečně existuje taková revize stránky, jinak se nastavení neprojeví.",
	'stabilization-perm' => 'Tento účet nemá povoleno měnit nastavení stabilní verze. Níže je současné nastavení stránky [[:$1|$1]]:',
	'stabilization-page' => 'Jméno stránky:',
	'stabilization-leg' => 'Potvrdit nastavení stabilní verze',
	'stabilization-select' => 'Přednost výběru stabilní verze',
	'stabilization-select1' => 'Poslední kvalitní verze; pokud není k dispozici pak poslední prohlédnutá',
	'stabilization-select2' => 'Poslední posouzená verze (bez ohledu na úroveň oveření)',
	'stabilization-select3' => 'Poslední čistá revize; pokud neexistuje, nejnovější kvalitní nebo prověřená',
	'stabilization-def' => 'Verze zobrazená jako výchozí',
	'stabilization-def1' => 'Stabilní revize; pokud neexistuje, je to současná revize/návrh',
	'stabilization-def2' => 'Současná/návrhová verze',
	'stabilization-restrict' => 'Omezení automatického posuzování',
	'stabilization-restrict-none' => 'Žádná další omezení',
	'stabilization-submit' => 'Potvrdit',
	'stabilization-notexists' => 'Neexistuje stránka "[[:$1|$1]]". Nastavení není možné.',
	'stabilization-notcontent' => 'Stránka „[[:$1|$1]]“ nemůže být posouzena. Nastavení není možné.',
	'stabilization-comment' => 'Důvod:',
	'stabilization-otherreason' => 'Jiný důvod:',
	'stabilization-expiry' => 'Vyprší:',
	'stabilization-othertime' => 'Jiný čas:',
	'stabilization-sel-short' => 'Váha',
	'stabilization-sel-short-0' => 'kvalitní',
	'stabilization-sel-short-1' => 'žádná',
	'stabilization-sel-short-2' => 'čistá',
	'stabilization-def-short' => 'výchozí',
	'stabilization-def-short-0' => 'současná',
	'stabilization-def-short-1' => 'stabilní',
	'stabilize_expiry_invalid' => 'Datum vypršení je chybné.',
	'stabilize_expiry_old' => 'Čas vypršení již minul.',
	'stabilize-expiring' => 'vyprší $1 (UTC)',
	'stabilization-review' => 'Posoudit aktuální verzi',
);

/** Danish (Dansk)
 * @author Jon Harald Søby
 */
$messages['da'] = array(
	'stabilization-submit' => 'Bekræft',
	'stabilization-expiry' => 'Udløb:',
	'stabilization-sel-short-1' => 'Ingen',
	'stabilization-def-short' => 'Standard',
	'stabilization-def-short-0' => 'Nuværende',
	'stabilize-expiring' => 'til $1 (UTC)',
);

/** German (Deutsch)
 * @author Als-Holder
 * @author Giftpflanze
 * @author Kghbln
 * @author Metalhead64
 * @author Purodha
 * @author Steef389
 * @author Umherirrender
 */
$messages['de'] = array(
	'stabilization-tab' => 'Konfig.',
	'stabilization' => 'Seitenkonfiguration',
	'stabilization-text' => "'''Ändere die folgenden Einstellungen, um festzulegen, wie die zu veröffentlichende Version von „[[:$1|$1]]“ ausgewählt und angezeigt werden soll.'''

'''Hinweis:''' Die Änderung der Konfiguration hinsichtlich der standardmäßig anzuzeigenden Version, auf „geprüft“ oder „neueste markierte“, hat keinerlei Auswirkungen, sofern derartige Versionen nicht vorhanden sind. Bedenke, dass in diesem Zusammenhang eine „markierte“ Version als „geprüfte“ Version angesehen wird.",
	'stabilization-perm' => 'Du hast nicht die erforderliche Berechtigung, um die Einstellungen der markierten Version zu ändern.
Die aktuellen Einstellungen für „[[:$1|$1]]“ sind:',
	'stabilization-page' => 'Seitenname:',
	'stabilization-leg' => 'Bestätige die Einstellungen bezüglich der zu veröffentlichenden Version',
	'stabilization-select' => 'Vorzugsweise letzte stabile Version auswählen',
	'stabilization-select1' => 'Die letzte geprüfte Version; wenn keine vorhanden ist, dann die letzte gesichtete Version',
	'stabilization-select2' => 'letzte markierte Version',
	'stabilization-select3' => 'Die letzte ursprüngliche Version; wenn keine vorhanden ist, dann die letzte gesichtete oder geprüfte Version',
	'stabilization-def' => 'Angezeigte Version in der normalen Seitenansicht',
	'stabilization-def1' => 'Die veröffentlichte Version. Sofern keine vorhanden ist, die aktuelle Version/der aktuelle Entwurf',
	'stabilization-def2' => 'Die aktuelle Version/der Entwurf',
	'stabilization-restrict' => 'Einschränkungen bezüglich des Markierens/des automatischen Markierens',
	'stabilization-restrict-none' => 'Keine zusätzlichen Einschränkungen',
	'stabilization-submit' => 'Bestätigen',
	'stabilization-notexists' => 'Es gibt keine Seite „[[:$1|$1]]“. Keine Einstellungen möglich.',
	'stabilization-notcontent' => 'Die Seite „[[:$1|$1]]“ kann nicht markiert werden. Konfiguration ist nicht möglich.',
	'stabilization-comment' => 'Grund:',
	'stabilization-otherreason' => 'Anderer Grund:',
	'stabilization-expiry' => 'Gültig bis:',
	'stabilization-othertime' => 'Andere Zeit:',
	'stabilization-sel-short' => 'Priorität',
	'stabilization-sel-short-0' => 'Qualität',
	'stabilization-sel-short-1' => 'keine',
	'stabilization-sel-short-2' => 'ursprünglich',
	'stabilization-def-short' => 'Standard',
	'stabilization-def-short-0' => 'Aktuell',
	'stabilization-def-short-1' => 'stabile Version',
	'stabilize_page_invalid' => 'Der gewählte Seitentitel ist ungültig.',
	'stabilize_page_notexists' => 'Die gewählte Seite existiert nicht.',
	'stabilize_page_unreviewable' => 'Die gewählte Seite befindet sich nicht in einem Namensraum, in dem Markierungen gesetzt werden können.',
	'stabilize_invalid_precedence' => 'Ungültige Versionspriorität.',
	'stabilize_invalid_autoreview' => 'Ungültige Einschränkung bezüglich automatischer Markierungen.',
	'stabilize_invalid_level' => 'Ungültige Seitenschutzstufe.',
	'stabilize_expiry_invalid' => 'Ungültiges Ablaufdatum.',
	'stabilize_expiry_old' => 'Das Ablaufdatum wurde überschritten.',
	'stabilize_denied' => 'Zugriff verweigert.',
	'stabilize-expiring' => 'erlischt am $2, $3 Uhr (UTC)',
	'stabilization-review' => 'Markiere die aktuelle Version',
);

/** German (formal address) (Deutsch (Sie-Form))
 * @author Umherirrender
 */
$messages['de-formal'] = array(
	'stabilization-perm' => 'Sie haben nicht die erforderliche Berechtigung, um die Einstellungen der markierten Version zu ändern.
Die aktuellen Einstellungen für „[[:$1|$1]]“ sind:',
);

/** Zazaki (Zazaki)
 * @author Aspar
 * @author Belekvor
 * @author Xoser
 */
$messages['diq'] = array(
	'stabilization-tab' => 'vet',
	'stabilization' => 'istiqrar kerdışê peli',
	'stabilization-text' => "'''[[:\$1|\$1]] için kararlı sürümün nasıl seçilip görüntüleneceğini ayarlamak için ayarları değiştirin.'''

Varsayılan olarak \"kalite\" ya da \"asıl\" revizyonlarını kullanmak için ''kararlı sürüm seçimi'' yapılandırmasını değiştirirken, sayfada böyle revizyonların olduğunu kontrol ettiğinize emin olun, aksi halde değişikliğin etkisi küçük olacaktır.",
	'stabilization-perm' => 'Hesabê tu rê destur çini yo ke stable versiyon confugration bivurne.
Tiya de eyaranê penîyî qe [[:$1|$1]] esto:',
	'stabilization-page' => 'Nameyê pelî:',
	'stabilization-leg' => 'Eyaranê stable versionî testiq bike',
	'stabilization-select' => 'Seleksiyonê stable versionî evelî',
	'stabilization-select1' => 'Revizyonê kaliteyî tewr penî',
	'stabilization-select2' => 'Revizyonê ke tewr peni de kontrol biyo (seviyeyê tewtiqî rê diket nikeno)',
	'stabilization-select3' => 'Revizyinê tewr penî ke hewlo; tewr penî qalite ra; tewr penî sight ra',
	'stabilization-def' => 'Vînayişê pelî de revizyon mucnayiyo',
	'stabilization-def1' => 'Revizyonê stableyî; eka çini yo, peniyo/draftî',
	'stabilization-def2' => 'Revizyonê penî/draftî',
	'stabilization-restrict' => 'Restriksyonşê oto-kontrolî',
	'stabilization-restrict-none' => 'Restriksiyonê bînî çini yo',
	'stabilization-submit' => 'Konfirme bike',
	'stabilization-notexists' => 'Yew pel ser "[[:$1|$1]]" çini yo. 
Konfugure ni beno.',
	'stabilization-notcontent' => 'Pel"[[:$1|$1]]" kontrol nibeno. 
Konfugure ni beno.',
	'stabilization-comment' => 'Sebeb:',
	'stabilization-otherreason' => 'Sebebê bîn:',
	'stabilization-expiry' => 'Qediyeno:',
	'stabilization-othertime' => 'Wextê bîn:',
	'stabilization-sel-short' => 'Ornek',
	'stabilization-sel-short-0' => 'Qelite',
	'stabilization-sel-short-1' => 'çino',
	'stabilization-sel-short-2' => 'Hewl',
	'stabilization-def-short' => 'Eyaranê tewr vernî',
	'stabilization-def-short-0' => 'Penî',
	'stabilization-def-short-1' => 'Sebit',
	'stabilize_expiry_invalid' => 'Wextê qedîyayîş raşt niyo.',
	'stabilize_expiry_old' => 'Wextê qedîyayîş penî de mend.',
	'stabilize-expiring' => '$1 (UTC) de qediyeno',
	'stabilization-review' => 'Versiyonê penî kontrol bike',
);

/** Lower Sorbian (Dolnoserbski)
 * @author Michawiki
 */
$messages['dsb'] = array(
	'stabilization-tab' => 'pśekontrolowaś',
	'stabilization' => 'Stabilizacija boka',
	'stabilization-text' => "'''Změń slědujuce nastajenja, aby póstajił, kak se wózjawjona wersija wót [[:\$1|\$1]] wuběra a zwobraznjujo.'''

'''Glědaj:''' změnjenje ''wuběrka wózjawjoneje wersije'', aby se \"kwalitne\" abo \"spócetne\" wersije preferěrowali, njezmějo žedne wustatkowanje, jolic take wersije njejsu. Glědaj teke, až \"kwalitna\" wersija teke naglěda se ako \"pśekontrolěrowana\" wersija atd.",
	'stabilization-perm' => 'Twójo konto njama pšawo, aby změniło konfiguraciju wózjawjoneje wersije. How su aktualne nastajenja za [[:$1|$1]]:',
	'stabilization-page' => 'Mě boka:',
	'stabilization-leg' => 'Nastajenja wózjawjoneje wersije wobkšuśiś',
	'stabilization-select' => 'Wuběrańska prědnosć wózjawjoneje wersije',
	'stabilization-select1' => 'Aktualna kwalitna wersija; jolic žedna njejo, ga slědna pśeglědana wersija',
	'stabilization-select2' => 'Slědna pśekontrolěrowana wersija',
	'stabilization-select3' => 'Slědna spócetna wersija; jolic žedna njejo, ga slědna kwalitna abo pśeglědana wersija',
	'stabilization-def' => 'Zwobraznjona wersija w standardnem bocnem naglěźe',
	'stabilization-def1' => 'Wózjawjona wersija; jolic žedna njejo, ga aktualna wersija/nacerjenje',
	'stabilization-def2' => 'Aktualna wersija/nacerjenje',
	'stabilization-restrict' => 'Wobgranicowanja pśeglědanjow/awtomatiskich pséglědanjow',
	'stabilization-restrict-none' => 'Žedne pśidatne wobgranicowanja',
	'stabilization-submit' => 'Wobkšuśiś',
	'stabilization-notexists' => 'Njejo bok z mjenim "[[:$1|$1]]".
Žedna konfiguracija móžno.',
	'stabilization-notcontent' => 'Bok "[[:$1|$1]]" njedajo se pśeglědaś.
Žedna konfiguracija móžno.',
	'stabilization-comment' => 'Pśicyna:',
	'stabilization-otherreason' => 'Druga pśicyna:',
	'stabilization-expiry' => 'Pśepadnjo:',
	'stabilization-othertime' => 'Drugi cas:',
	'stabilization-sel-short' => 'Priorita',
	'stabilization-sel-short-0' => 'Kwalita',
	'stabilization-sel-short-1' => 'Žedna',
	'stabilization-sel-short-2' => 'Spócetny',
	'stabilization-def-short' => 'Standard',
	'stabilization-def-short-0' => 'Aktualny',
	'stabilization-def-short-1' => 'Wózjawjony',
	'stabilize_page_invalid' => 'Titel celowego boka jo njepłaśiwy.',
	'stabilize_page_notexists' => 'Celowy bok njeeksistěrujo.',
	'stabilize_page_unreviewable' => 'Celowy bok njejo w pśeglědujobnem mjenjowem rumje.',
	'stabilize_invalid_precedence' => 'Njepłaśiwa wersijowa priorita.',
	'stabilize_invalid_autoreview' => 'Njepłaśiwe wobgranicowanje awtomatiskich pśeglědanjow.',
	'stabilize_invalid_level' => 'Njepłaśiwy šćitowy schojźeńk.',
	'stabilize_expiry_invalid' => 'Njpłaśiwy datum pśepadnjenja.',
	'stabilize_expiry_old' => 'Toś ten cas pśepadnjenja jo se južo minuł.',
	'stabilize_denied' => 'Pšawo wótpokazane.',
	'stabilize-expiring' => 'pśepadnjo $1 (UTC)',
	'stabilization-review' => 'Aktualnu wersiju ako pśekontrolěrowanu markěrowaś',
);

/** Greek (Ελληνικά)
 * @author Badseed
 * @author Consta
 * @author Crazymadlover
 * @author Dead3y3
 * @author Omnipaedista
 * @author ZaDiak
 */
$messages['el'] = array(
	'stabilization-tab' => 'εξωνυχιστικός έλεγχος',
	'stabilization' => 'Σταθεροποίηση σελίδας',
	'stabilization-text' => "'''Αλλάξτε τις ρυθμίσεις παρακάτω για να ρυθμίσετε το πως η σταθερή έκδοση της σελίδας [[:\$1|\$1]] επιλέγεται και εμφανίζεται.'''

Κατά την αλλαγή της διαμόρφωσης της ''επιλογής σταθερής έκδοσης'' για την χρήση ως προκαθορισμένων των αναθεωρήσεων \"ποιοτική\" ή \"αρχική\", βεβαιωθείτε ότι υπάρχουν πραγματικά τέτοιες αναθεωρήσεις στην σελίδα, ειδάλλως η αλλαγή δεν θα έχει μεγάλο αντίκτυπο.",
	'stabilization-perm' => 'Ο λογαριασμός σας δεν έχει δικαίωμα να αλλάξει την ρύθμιση σταθερής έκδοσης.
Εδώ είναι οι τρέχουσες ρυθμίσεις για τη σελίδα [[:$1|$1]]:',
	'stabilization-page' => 'Όνομα σελίδας:',
	'stabilization-leg' => 'Επιβεβαιώστε ρυθμίσεις σταθερής έκδοσης',
	'stabilization-select' => 'Προτεραιότητα επιλογής σταθερής έκδοσης',
	'stabilization-select1' => 'Η τελευταία αναθεώρηση ποιότητας· αν δεν είναι παρούσα, τότε η τελευταία ιδωμένη',
	'stabilization-select2' => 'Η τελευταία κριθείσα αναθεώρηση, ανεξάρτητα από το επίπεδο της επικύρωσης',
	'stabilization-select3' => 'Η τελευταία μη αλλοιωμένη αναθεώρηση· αν δεν είναι παρούσα, τότε η τελευταία ποιότητας ή ιδωμένη',
	'stabilization-def' => 'Αναθεώρηση εμφανιζόμενη στην προεπιλεγμένη εμφάνιση σελίδας',
	'stabilization-def1' => 'Η σταθερή αναθεώρηση· αν δεν είναι παρούσα, τότε η τρέχουσα/πρόχειρη',
	'stabilization-def2' => 'Η τρέχουσα/πρόχειρη αναθεώρηση',
	'stabilization-restrict' => 'Περιορισμοί αυτόματης επιθεώρησης',
	'stabilization-restrict-none' => 'Κανένας επιπλέον περιορισμός',
	'stabilization-submit' => 'Επιβεβαίωση',
	'stabilization-notexists' => 'Δεν υπάρχει σελίδα αποκαλούμενη "[[:$1|$1]]".<br />
Δεν είναι δυνατή καμία ρύθμιση.',
	'stabilization-notcontent' => 'Η σελίδα "[[:$1|$1]]" δεν μπορεί να κριθεί.<br />
Δεν είναι δυνατή καμία ρύθμιση.',
	'stabilization-comment' => 'Λόγος:',
	'stabilization-otherreason' => 'Άλλος λόγος:',
	'stabilization-expiry' => 'Λήγει:',
	'stabilization-othertime' => 'Άλλη ώρα:',
	'stabilization-sel-short' => 'Προτεραιότητα',
	'stabilization-sel-short-0' => 'Ποιότητα',
	'stabilization-sel-short-1' => 'Τίποτα',
	'stabilization-sel-short-2' => 'Μη αλλοίωση',
	'stabilization-def-short' => 'Προεπιλογή',
	'stabilization-def-short-0' => 'Τρέχουσα',
	'stabilization-def-short-1' => 'Σταθερή',
	'stabilize_expiry_invalid' => 'Άκυρη ημερομηνία λήξης.',
	'stabilize_expiry_old' => 'Η ημερομηνία λήξης έχει ήδη περάσει.',
	'stabilize-expiring' => 'λήγει στις $1 (UTC)',
	'stabilization-review' => 'Επιθεωρήστε τη τρέχουσα έκδοση',
);

/** British English (British English)
 * @author Reedy
 */
$messages['en-gb'] = array(
	'stabilization' => 'Page stabilisation',
);

/** Esperanto (Esperanto)
 * @author Yekrats
 */
$messages['eo'] = array(
	'stabilization-tab' => 'kontroli',
	'stabilization' => 'Paĝa stabiligado',
	'stabilization-text' => "'''Ŝanĝu la jenajn agordojn por modifi kiel la publikigita versio de [[:\$1|\$1]] estas elektita kaj montrita.'''

Notu: ŝanĝante la konfiguro ''elekto de stabila versio'' por preferi \"bonkvalita\" aŭ \"netega\" revizioj defaŭlte ne efikos se ne ekzistas tiaj versioj. Ankaŭ, notu ke '''bonkvalita''' versio estas ankaŭ konsiderata kiel '''kontrolita''' versio, ktp.",
	'stabilization-perm' => 'Via konto ne rajtas ŝanĝi la konfiguron de publikigita versio.
Jen la nunaj agordoj por [[:$1|$1]]:',
	'stabilization-page' => 'Paĝnomo:',
	'stabilization-leg' => 'Konfirmi agordojn de publikigitaj versioj',
	'stabilization-select' => 'Elektita prioritato de publikigita versio',
	'stabilization-select1' => 'La lasta bonkvalita versio; se ĝi ne ekzistas, tiel la lasta reviziita versio.',
	'stabilization-select2' => 'Plej lasta kontrolita revizio',
	'stabilization-select3' => 'La lasta netega versio; se ne estanta, la lasta bonkvalita aŭ reviziita versio.',
	'stabilization-def' => 'Versio montrita en defaŭlta paĝa vido',
	'stabilization-def1' => 'La publikigita versio; se ĝi ne ekzistas, la nuna aŭ malneta versio',
	'stabilization-def2' => 'La nuna/malneta revizio',
	'stabilization-restrict' => 'Limigoj pri kontrolado aŭ aŭtomata kontrolado',
	'stabilization-restrict-none' => 'Neniuj pliaj limigoj',
	'stabilization-submit' => 'Konfirmi',
	'stabilization-notexists' => 'Neniu paĝo estas nomata "[[:$1|$1]]".
Neniu konfiguro estas farebla.',
	'stabilization-notcontent' => 'La paĝo "[[:$1|$1]]" ne estas kontrolebla.
Neniu konfiguro eblas.',
	'stabilization-comment' => 'Kialo:',
	'stabilization-otherreason' => 'Alia kialo:',
	'stabilization-expiry' => 'Fintempo:',
	'stabilization-othertime' => 'Alia tempo:',
	'stabilization-sel-short' => 'Prioritato',
	'stabilization-sel-short-0' => 'Kvalito',
	'stabilization-sel-short-1' => 'Neniu',
	'stabilization-sel-short-2' => 'Netega',
	'stabilization-def-short' => 'Defaŭlta',
	'stabilization-def-short-0' => 'Nuna',
	'stabilization-def-short-1' => 'Publikigita',
	'stabilize_page_invalid' => 'La titolo de la cela paĝo estas malvalida.',
	'stabilize_page_notexists' => 'La cela paĝo ne ekzistas.',
	'stabilize_expiry_invalid' => 'Malvalida findato.',
	'stabilize_expiry_old' => 'Ĉi tiu findato jam estas pasita.',
	'stabilize_denied' => 'Malpermesita.',
	'stabilize-expiring' => 'findato $1 (UTC)',
	'stabilization-review' => 'Marki la nunan revizion kiel kontrolitan',
);

/** Spanish (Español)
 * @author Crazymadlover
 * @author Dferg
 * @author Drini
 * @author Imre
 * @author Kobazulo
 * @author Manuelt15
 * @author Peter17
 * @author Sanbec
 * @author Translationista
 */
$messages['es'] = array(
	'stabilization-tab' => 'vetar',
	'stabilization' => 'Estabilización de página',
	'stabilization-text' => "'''Cambiar las configuraciones de abajo para ajustar cómo la versión estable de [[:\$1|\$1]] es seleccionada y mostrada.'''

'''Nota:''' Al cambiar la ''selección de versión publicada'' para usar revisiones de \"calidad\" o \"prístina\" esto no tendrá efecto si no existe tal versión. Además, nota que una versión de \"calidad\" es también considerada una versión \"revisada\" y viceversa.",
	'stabilization-perm' => 'Su cuenta no tiene permiso para cambiar la configuración de la versión publicada.
La configuración actual es [[:$1|$1]]:',
	'stabilization-page' => 'Nombre de la página:',
	'stabilization-leg' => 'Confirmar la configuración de la versión publicada',
	'stabilization-select' => 'Precedencia de selección de versión publicada',
	'stabilization-select1' => 'La última revisión de calidad; si no está presente, entonces la última observada',
	'stabilization-select2' => 'Última versión verificada',
	'stabilization-select3' => 'La última revisión prístina; si no está presente, entonces la última de calidad u observada',
	'stabilization-def' => 'Revisión mostrada en la vista de página por defecto',
	'stabilization-def1' => 'La revisión publicada; si no está presente, entonces la actual/borrador',
	'stabilization-def2' => 'La revisión actual/borrador',
	'stabilization-restrict' => 'Restricciones de revisión/autorevisión',
	'stabilization-restrict-none' => 'Sin restricciones extra',
	'stabilization-submit' => 'Confirmar',
	'stabilization-notexists' => 'No hay una página llamada "[[:$1|$1]]".
La configuración no es posible.',
	'stabilization-notcontent' => 'La página "[[:$1|$1]]" no puede ser revisada.
La configuración no es posible.',
	'stabilization-comment' => 'Razón:',
	'stabilization-otherreason' => 'Otra razón:',
	'stabilization-expiry' => 'Expira:',
	'stabilization-othertime' => 'Otra vez:',
	'stabilization-sel-short' => 'Precedencia',
	'stabilization-sel-short-0' => 'Calidad',
	'stabilization-sel-short-1' => 'Ninguno',
	'stabilization-sel-short-2' => 'Prístina',
	'stabilization-def-short' => 'Por defecto',
	'stabilization-def-short-0' => 'Actual',
	'stabilization-def-short-1' => 'Publicado',
	'stabilize_page_invalid' => 'El título de la página de destino es inválido.',
	'stabilize_page_notexists' => 'La página de destino es no existe.',
	'stabilize_page_unreviewable' => 'La página de destino no está en un espacio de nombre en el que sea posible una revisión.',
	'stabilize_invalid_precedence' => 'Precedencia de versión inválida.',
	'stabilize_invalid_autoreview' => 'Restricciión e autorevisión inválida.',
	'stabilize_invalid_level' => 'Nivel de protección inválido.',
	'stabilize_expiry_invalid' => 'La fecha de caducidad no es válida.',
	'stabilize_expiry_old' => 'Este tiempo de expiración ya ha pasado',
	'stabilize_denied' => 'Permiso denegado.',
	'stabilize-expiring' => 'caduca el $1 (UTC)',
	'stabilization-review' => 'Marcar la versión actual verificada',
);

/** Estonian (Eesti)
 * @author Avjoska
 * @author KalmerE.
 * @author Pikne
 */
$messages['et'] = array(
	'stabilization-page' => 'Lehekülje nimi:',
	'stabilization-submit' => 'Kinnita',
	'stabilization-comment' => 'Põhjus:',
	'stabilization-otherreason' => 'Muu põhjus:',
	'stabilization-expiry' => 'Aegub:',
	'stabilization-othertime' => 'Muu aeg:',
	'stabilization-sel-short' => 'Tähtsus',
	'stabilization-sel-short-0' => 'Kvaliteet',
	'stabilization-sel-short-2' => 'Algne',
	'stabilization-def-short' => 'Vaikimisi',
	'stabilization-def-short-0' => 'Praegune',
	'stabilization-def-short-1' => 'Stabiilne',
	'stabilize_expiry_invalid' => 'Vigane aegumistähtaeg.',
	'stabilize_expiry_old' => 'See aegumistähtaeg on juba möödunud.',
	'stabilize-expiring' => 'aegumistähtajaga $1 (UTC)',
);

/** Basque (Euskara)
 * @author An13sa
 * @author Kobazulo
 */
$messages['eu'] = array(
	'stabilization' => 'Orrialdearen egonkortzea',
	'stabilization-page' => 'Orrialdearen izenburua:',
	'stabilization-leg' => 'Argitaratutako bertsioaren konfigurazioa berretsi',
	'stabilization-select' => 'Argitaratutako bertsioaren aukeraketa',
	'stabilization-submit' => 'Baieztatu',
	'stabilization-comment' => 'Arrazoia:',
	'stabilization-otherreason' => 'Beste arrazoirik:',
	'stabilization-expiry' => 'Epemuga:',
	'stabilization-othertime' => 'Beste denbora:',
	'stabilization-sel-short-0' => 'Kalitatea',
	'stabilization-sel-short-1' => 'Bat ere',
	'stabilization-def-short' => 'Lehenetsia',
	'stabilization-def-short-0' => 'Oraingoa',
	'stabilization-def-short-1' => 'Argitaratua',
	'stabilize_expiry_invalid' => 'Iraungipen-data okerra.',
	'stabilize-expiring' => 'iraungipen-data: $1 (UTC)',
);

/** Extremaduran (Estremeñu)
 * @author Better
 */
$messages['ext'] = array(
	'stabilization-page' => 'Nombri la páhina:',
	'stabilization-submit' => 'Confirmal',
	'stabilization-sel-short-1' => 'Dengunu',
	'stabilization-def-short' => 'Defeutu',
	'stabilization-def-short-0' => 'Atual',
);

/** Persian (فارسی)
 * @author Huji
 * @author Momeni
 */
$messages['fa'] = array(
	'stabilization-tab' => '(کک)',
	'stabilization' => 'پایدارسازی صفحه‌ها',
	'stabilization-text' => "'''تغییر تنظیمات زیر به منظور تعیین این که نسخه پایدار [[:$1|$1]] چگونه انتخاب و نمایش داده می‌شود.'''",
	'stabilization-perm' => 'حساب شما اجازه تغییر تنظیمات نسخه پایدار را ندارد.
تنظیمات فعلی برای [[:$1|$1]] چنین هستند:',
	'stabilization-page' => 'نام صفحه:',
	'stabilization-leg' => 'تایید تنظیمات نسخهٔ پایدار',
	'stabilization-select' => 'انتخاب نسخهٔ پایدار',
	'stabilization-select1' => 'آخرین نسخه با کیفیت، یا در صورت عدم وجود آن، آخرین نسخه بررسی شده',
	'stabilization-select2' => 'آخرین نسخه بررسی شده',
	'stabilization-select3' => 'آخرین نسخهٔ دست نخورده؛ در صورت عدم وجود، آخرین نسخهٔ با کیفیت یا بررسی شده',
	'stabilization-def' => 'نسخه‌ای که در حالت پیش‌فرض نمایش داده می‌شود',
	'stabilization-def1' => 'نسخه پایدار، یا در صورت عدم وجود، نسخه فعلی',
	'stabilization-def2' => 'نسخه فعلی',
	'stabilization-submit' => 'تائید',
	'stabilization-notexists' => 'صفحه‌ای با عنوان «[[:$1|$1]]» وجود ندارد. تنظیمات ممکن نیست.',
	'stabilization-notcontent' => 'صفحه «[[:$1|$1]]» قابل بررسی نیست. تنظیمات ممکن نیست.',
	'stabilization-comment' => 'توضیح:',
	'stabilization-otherreason' => 'دلیل دیگر',
	'stabilization-expiry' => 'انقضا:',
	'stabilization-othertime' => 'زمان دیگر',
	'stabilization-sel-short' => 'تقدم',
	'stabilization-sel-short-0' => 'با کیفیت',
	'stabilization-sel-short-1' => 'هیچ',
	'stabilization-sel-short-2' => 'دست نخورده',
	'stabilization-def-short' => 'پیش‌فرض',
	'stabilization-def-short-0' => 'فعلی',
	'stabilization-def-short-1' => 'پایدار',
	'stabilize_expiry_invalid' => 'تاریخ انقضای غیرمجاز',
	'stabilize_expiry_old' => 'این تاریخ انقضا همینک سپری شده‌است.',
	'stabilize-expiring' => 'در $1 (UTC) منقضی می‌شود.',
);

/** Finnish (Suomi)
 * @author Cimon Avaro
 * @author Crt
 * @author Nike
 * @author Str4nd
 * @author ZeiP
 */
$messages['fi'] = array(
	'stabilization-tab' => 'tarkistus',
	'stabilization' => 'Sivun vakaus',
	'stabilization-perm' => 'Tunnuksellasi ei ole oikeutta muuttaa julkaistujen versioiden kokoonpanoa.
Tässä ovat nykyiset asetukset tunnukselle [[:$1|$1]]:',
	'stabilization-page' => 'Sivun nimi',
	'stabilization-leg' => 'Vahvista julkaistujen versioiden asetukset',
	'stabilization-select' => 'Julkaistun version valintajärjestys',
	'stabilization-select1' => 'Uusin laadukas versio; sitten uusin silmäilty',
	'stabilization-select2' => 'Viimeisin tarkastettu versio',
	'stabilization-def' => 'Versio, joka näytetään oletusarvoisesti',
	'stabilization-def1' => 'Julkaistu versio; jos sellaista ei ole, ajantasainen- tai luonnosversio',
	'stabilization-def2' => 'Luonnos- tai nykyinen versio',
	'stabilization-restrict-none' => 'Ei lisärajauksia',
	'stabilization-submit' => 'Vahvista',
	'stabilization-comment' => 'Syy:',
	'stabilization-otherreason' => 'Muu syy',
	'stabilization-expiry' => 'Vanhenee:',
	'stabilization-othertime' => 'Muu aika',
	'stabilization-sel-short' => 'Järjestys',
	'stabilization-sel-short-0' => 'Laatu',
	'stabilization-sel-short-1' => 'Ei mitään',
	'stabilization-sel-short-2' => 'Koskematon',
	'stabilization-def-short' => 'Oletus',
	'stabilization-def-short-0' => 'Nykyinen',
	'stabilization-def-short-1' => 'Julkaistu',
	'stabilize_expiry_invalid' => 'Virheellinen erääntymispäivä.',
	'stabilize_expiry_old' => 'Tämä erääntymisaika on jo mennyt.',
	'stabilize-expiring' => 'vanhenee $1 (UTC)',
	'stabilization-review' => 'Tarkista nykyinen versio',
);

/** French (Français)
 * @author ChrisPtDe
 * @author Dereckson
 * @author Dodoïste
 * @author Grondin
 * @author IAlex
 * @author Juanpabl
 * @author Peter17
 * @author PieRRoMaN
 * @author Purodha
 * @author Sherbrooke
 * @author Verdy p
 */
$messages['fr'] = array(
	'stabilization-tab' => '(aq)',
	'stabilization' => 'Stabilisation de la page',
	'stabilization-text' => "'''Modifiez les paramètres ci-dessous pour définir la façon dont la version publiée de [[:$1|$1]] est sélectionnée et affichée.'''

'''Note:''' modifier la ''sélection de la version publiée'' pour utiliser les révisions « de qualité » ou « initiales » n'aura aucun effet si ces versions n'existent pas. Notez aussi que les versions de « qualité » sont considérées comme « vérifiées » et ainsi de suite.",
	'stabilization-perm' => "Votre compte n'a pas les droits pour changer les paramètres de la version publiée.
Voici les paramètres actuels de [[:$1|$1]] :",
	'stabilization-page' => 'Nom de la page :',
	'stabilization-leg' => 'Confirmer le paramétrage de la version publiée',
	'stabilization-select' => 'Priorité de sélection de version publiée',
	'stabilization-select1' => 'La dernière version de qualité, sinon la dernière version vue',
	'stabilization-select2' => 'Dernière version révisée',
	'stabilization-select3' => 'La dernière version intacte ; en cas d’absence, la dernière de qualité ou relue.',
	'stabilization-def' => "Version affichée lors de l'affichage par défaut de la page",
	'stabilization-def1' => "La révision publiée ; s'il n'y en a pas, alors la courante ou le brouillon en cours",
	'stabilization-def2' => 'La révision courante ou le brouillon en cours',
	'stabilization-restrict' => 'Restrictions de relecture (automatique)',
	'stabilization-restrict-none' => 'Pas de restriction supplémentaire',
	'stabilization-submit' => 'Confirmer',
	'stabilization-notexists' => "Il n'y a pas de page « [[:$1|$1]] », pas de paramétrage possible",
	'stabilization-notcontent' => 'La page « [[:$1|$1]] » ne peut être révisée, pas de paramétrage possible',
	'stabilization-comment' => 'Raison :',
	'stabilization-otherreason' => 'Autre raison :',
	'stabilization-expiry' => 'Expire :',
	'stabilization-othertime' => 'Autre temps :',
	'stabilization-sel-short' => 'Priorité',
	'stabilization-sel-short-0' => 'Qualité',
	'stabilization-sel-short-1' => 'Nulle',
	'stabilization-sel-short-2' => 'Intacte',
	'stabilization-def-short' => 'Défaut',
	'stabilization-def-short-0' => 'Courante',
	'stabilization-def-short-1' => 'Publié',
	'stabilize_page_invalid' => 'Le titre de la page cible est incorrect',
	'stabilize_page_notexists' => "La page cible n'existe pas.",
	'stabilize_page_unreviewable' => "La page cible n'est pas dans un espace de noms qui peut être relu.",
	'stabilize_invalid_precedence' => 'Priorité de version invalide.',
	'stabilize_invalid_autoreview' => 'Restriction de relecture automatique invalide',
	'stabilize_invalid_level' => 'Niveau de protection invalide.',
	'stabilize_expiry_invalid' => "Date d'expiration invalide.",
	'stabilize_expiry_old' => "Cette durée d'expiration est déjà écoulée.",
	'stabilize_denied' => 'Permission refusée.',
	'stabilize-expiring' => 'Expire le $1 (UTC)',
	'stabilization-review' => 'Marquer la version actuelle comme vérifiée',
);

/** Franco-Provençal (Arpetan)
 * @author ChrisPtDe
 */
$messages['frp'] = array(
	'stabilization-tab' => 'Controlar',
	'stabilization' => 'Stabilisacion de la pâge.',
	'stabilization-text' => "'''Changiéd los paramètres ce-desot por dèfenir la façon que la vèrsion publeyê de [[:$1|$1]] est chouèsia et montrâ.'''

Quand vos configurâd lo ''chouèx de la vèrsion publeyê'' por utilisar les vèrsions « de qualitât » ou ben « sen tache » per dèfôt,
assurâd-vos qu’y at verément de tâles vèrsions dens la pâge, ôtrament los changements aront gins de rèsultat.",
	'stabilization-perm' => 'Voutron compto at pas los drêts por changiér los paramètres de la vèrsion publeyê.
Vê-que los paramètres d’ora de [[:$1|$1]] :',
	'stabilization-page' => 'Nom de la pâge :',
	'stabilization-leg' => 'Confirmar los paramètres de la vèrsion publeyê',
	'stabilization-select' => 'Prioritât de chouèx de la vèrsion publeyê',
	'stabilization-select1' => 'La dèrriére vèrsion de qualitât, ôtrament la dèrriére vèrsion revua',
	'stabilization-select2' => 'La dèrriére vèrsion revua, sen tegnir compto du nivél de validacion',
	'stabilization-select3' => 'La dèrriére vèrsion sen tache, ôtrament la dèrriére vèrsion de qualitât ou ben revua',
	'stabilization-def' => 'Vèrsion montrâ pendent la visualisacion per dèfôt de la pâge',
	'stabilization-def1' => 'La vèrsion publeyê ; s’y en at pas, adonc cela d’ora ou ben lo brolyon',
	'stabilization-def2' => 'La vèrsion d’ora ou ben lo brolyon',
	'stabilization-restrict' => 'Rèstriccions sur les rèvisions ôtomatiques',
	'stabilization-restrict-none' => 'Gins de rèstriccion de ples',
	'stabilization-submit' => 'Confirmar',
	'stabilization-notexists' => 'Y at gins de pâge « [[:$1|$1]] »,
gins de configuracion possibla.',
	'stabilization-notcontent' => 'La pâge « [[:$1|$1]] » pôt pas étre revua,
gins de configuracion possibla.',
	'stabilization-comment' => 'Rêson :',
	'stabilization-otherreason' => 'Ôtra rêson :',
	'stabilization-expiry' => 'Èxpire :',
	'stabilization-othertime' => 'Ôtro temps :',
	'stabilization-sel-short' => 'Prioritât',
	'stabilization-sel-short-0' => 'De qualitât',
	'stabilization-sel-short-1' => 'Niona',
	'stabilization-sel-short-2' => 'Sen tache',
	'stabilization-def-short' => 'Dèfôt',
	'stabilization-def-short-0' => 'D’ora',
	'stabilization-def-short-1' => 'Publeyê',
	'stabilize_expiry_invalid' => 'Dâta d’èxpiracion envalida.',
	'stabilize_expiry_old' => 'Cél temps d’èxpiracion est ja passâ.',
	'stabilize-expiring' => 'èxpire lo $1 (UTC)',
	'stabilization-review' => 'Revêre la vèrsion d’ora',
);

/** Western Frisian (Frysk)
 * @author Snakesteuben
 */
$messages['fy'] = array(
	'stabilization-page' => 'Sidenamme:',
	'stabilization-comment' => 'Oanmerking:',
	'stabilization-sel-short-1' => 'Gjin',
	'stabilization-def-short' => 'Standert',
);

/** Irish (Gaeilge)
 * @author Alison
 */
$messages['ga'] = array(
	'stabilization-comment' => 'Nóta tráchta:',
	'stabilization-sel-short-1' => 'Faic',
);

/** Galician (Galego)
 * @author Alma
 * @author Toliño
 * @author Xosé
 */
$messages['gl'] = array(
	'stabilization-tab' => '(qa)',
	'stabilization' => 'Estabilización da páxina',
	'stabilization-text' => "'''Mude a configuración a continuación para axustar a forma na que a versión publicada de \"[[:\$1|\$1]]\" se selecciona e mostra.'''

'''Nota:''' o cambio de ''selección da versión publicada'' para usar as revisións de \"calidade\" ou \"previas\" non terá ningún efecto se non existen as devanditas versións. Teña en conta, ademais, que unha versión de \"calidade\" considérase tamén como \"comprobada\".",
	'stabilization-perm' => 'A súa conta non ten os permisos necesarios para mudar a configuración da versión publicada.
Velaquí está a configuración actual de "[[:$1|$1]]":',
	'stabilization-page' => 'Nome da páxina:',
	'stabilization-leg' => 'Confirmar as configuración da versión publicada',
	'stabilization-select' => 'Prioridade de selección da versión publicada',
	'stabilization-select1' => 'A última revisión de calidade; se non existe, entón a última revisada',
	'stabilization-select2' => 'Última revisión comprobada',
	'stabilization-select3' => 'A última revisión previa; se non existe, entón a última de calidade ou revisada',
	'stabilization-def' => 'Revisión que aparece por defecto na vista da páxina',
	'stabilization-def1' => 'A revisión publicada; se non existe, entón a actual ou o borrador',
	'stabilization-def2' => 'A revisión actual ou o borrador',
	'stabilization-restrict' => 'Restricións de revisión/revisión automática',
	'stabilization-restrict-none' => 'Sen restricións extra',
	'stabilization-submit' => 'Confirmar',
	'stabilization-notexists' => 'Non hai unha páxina chamada "[[:$1|$1]]". A non configuración é posíbel.',
	'stabilization-notcontent' => 'A páxina "[[:$1|$1]]" non pode ser revisada. A non configuración é posíbel.',
	'stabilization-comment' => 'Motivo:',
	'stabilization-otherreason' => 'Outro motivo:',
	'stabilization-expiry' => 'Caducidade:',
	'stabilization-othertime' => 'Outro tempo:',
	'stabilization-sel-short' => 'Precedencia',
	'stabilization-sel-short-0' => 'Calidade',
	'stabilization-sel-short-1' => 'Ningún',
	'stabilization-sel-short-2' => 'Intacto',
	'stabilization-def-short' => 'Por defecto',
	'stabilization-def-short-0' => 'Actual',
	'stabilization-def-short-1' => 'Publicada',
	'stabilize_page_invalid' => 'O título da páxina de destino non é correcto.',
	'stabilize_page_notexists' => 'A páxina de destino non existe.',
	'stabilize_page_unreviewable' => 'A páxina de destino non está nun espazo de nomes que se poida revisar.',
	'stabilize_invalid_precedence' => 'Prioridade de versión incorrecta.',
	'stabilize_invalid_autoreview' => 'Restrición de revisión automática incorrecta',
	'stabilize_invalid_level' => 'Nivel de protección incorrecto.',
	'stabilize_expiry_invalid' => 'Data de caducidade non válida.',
	'stabilize_expiry_old' => 'O tempo de caducidade xa pasou.',
	'stabilize_denied' => 'Permisos rexeitados.',
	'stabilize-expiring' => 'caduca o $2 ás $3 (UTC)',
	'stabilization-review' => 'Marcar a revisión actual como comprobada',
);

/** Ancient Greek (Ἀρχαία ἑλληνικὴ)
 * @author Crazymadlover
 * @author Omnipaedista
 */
$messages['grc'] = array(
	'stabilization-tab' => 'ἐλεγχ',
	'stabilization' => 'Σταθεροποίησις δέλτου',
	'stabilization-page' => 'Ὄνομα δέλτου:',
	'stabilization-submit' => 'Κυροῦν',
	'stabilization-comment' => 'Αἰτία:',
	'stabilization-otherreason' => 'Ἑτέρα αἰτία:',
	'stabilization-expiry' => 'Λήγει:',
	'stabilization-sel-short' => 'Προτεραιότης',
	'stabilization-sel-short-0' => 'ποιοτικὴ',
	'stabilization-sel-short-1' => 'Οὐδέν',
	'stabilization-sel-short-2' => 'Ἀνέπαφος',
	'stabilization-def-short' => 'Προκαθωρισμένη',
	'stabilization-def-short-0' => 'Τρέχουσα',
	'stabilization-def-short-1' => 'Σταθερά',
	'stabilize-expiring' => 'λήγει $1 (UTC)',
);

/** Swiss German (Alemannisch)
 * @author Als-Holder
 */
$messages['gsw'] = array(
	'stabilization-tab' => 'Konfig.',
	'stabilization' => 'Sytekonfiguration',
	'stabilization-text' => "'''Tue d Yystellige ändere fir zum feschtzlege, wie di vereffetligt Version vu „[[:\$1|\$1]]“ usgwehlt un aazeigt soll wäre.'''

'''Gib Acht:''' D Konfiguration vu dr ''Uuswahl vu dr vereffetligte Versione'' ändere go \"priefti\" oder \"reini\" Versione as Standard z neh, het kei Effäkt, wänn s keini sonige Versione het. Bitte gib druf Acht, ass e \"priefti\" Version au ne \"aagluegti\" Version isch.
 sicher, ass es aktuäll sonigi Versione git, sunscht het s keini großi Uuswirkig.
 sicher, ass es aktuäll sonigi Versione git, sunscht het s keini großi Uuswirkig.",
	'stabilization-perm' => 'Du hesch nid d Berächtigung, zum die Yystellige vu dr vereffetligte Version z ändere.
Di aktuällen Yystellige fir „[[:$1|$1]]“ sin:',
	'stabilization-page' => 'Sytename:',
	'stabilization-leg' => 'Yystellige vu dr vereffetligte Version fir e Syte',
	'stabilization-select' => 'Dr Vorrang fir vereffetligti Versione feschtlege',
	'stabilization-select1' => 'Di letscht prieft Version; wänn s keini het, no di letscht gsichtet Version',
	'stabilization-select2' => 'Di letscht Version, wu vum Fäldhieter gsäh ischuuabhängig vu dr Validierugsebeni',
	'stabilization-select3' => 'Di letscht urspringlig Version; wänn s keini het, derno di letscht Version, wu vum Fäldhieter gsäh oder prieft isch',
	'stabilization-def' => 'Version, wu in dr normale Syteaasicht aazeigt wird',
	'stabilization-def1' => 'Di vereffetligt Version; wänn s keini het, derno di aktuäll Version/d Entwurfsversion',
	'stabilization-def2' => 'Di aktuäll Version',
	'stabilization-restrict' => 'Priefig/Automatischi Priefig-Yyschränkige',
	'stabilization-restrict-none' => 'Keini extra Yyschränkige',
	'stabilization-submit' => 'Bstätige',
	'stabilization-notexists' => 'Es git kei Syte „[[:$1|$1]]“. Kei Yystellige megli.',
	'stabilization-notcontent' => 'D Syte „[[:$1|$1]]“ cha nit vum Fäldhieter gsäh wäre. E Konfiguration isch nid megli.',
	'stabilization-comment' => 'Grund:',
	'stabilization-otherreason' => 'Andere Grund:',
	'stabilization-expiry' => 'Giltig bis:',
	'stabilization-othertime' => 'Anderi Zyt:',
	'stabilization-sel-short' => 'Priorität',
	'stabilization-sel-short-0' => 'Qualität',
	'stabilization-sel-short-1' => 'keini',
	'stabilization-sel-short-2' => 'urspringli',
	'stabilization-def-short' => 'Standard',
	'stabilization-def-short-0' => 'Aktuäll',
	'stabilization-def-short-1' => 'Vereffetligt',
	'stabilize_page_invalid' => 'Dää Sytename isch nit giltig.',
	'stabilize_page_notexists' => 'Die gwehlt Syte git s nit.',
	'stabilize_page_unreviewable' => 'Die gwehlt Syte git snit in eme Namensruum, wu Markierige chenne gsetzt wäre.',
	'stabilize_invalid_precedence' => 'Nit giltigi Versionsprioritet.',
	'stabilize_invalid_autoreview' => 'Nit giltigi Yyschränkig vu dr automatische Markierig.',
	'stabilize_invalid_level' => 'Nit giltigi Syteschitzstapfle.',
	'stabilize_expiry_invalid' => 'Nid giltigs Ablaufdatum.',
	'stabilize_expiry_old' => 'S Ablaufdatum isch iberschritte wore.',
	'stabilize_denied' => 'Zuegriff verweigeret.',
	'stabilize-expiring' => 'erlischt $1 (UTC)',
	'stabilization-review' => 'Di aktuäll Version as aagluegt markiere',
);

/** Hawaiian (Hawai`i)
 * @author Kalani
 */
$messages['haw'] = array(
	'stabilization-def-short' => 'Paʻamau',
);

/** Hebrew (עברית)
 * @author DoviJ
 * @author Ori229
 * @author Rotemliss
 * @author YaronSh
 */
$messages['he'] = array(
	'stabilization-tab' => 'נבדק',
	'stabilization' => 'התייצבות הדף',
	'stabilization-text' => "'''שנו את ההגדרות שלהלן כדי לשנות את אופני בחירתה והצגתה של הגרסה היציבה של [[:\$1|\$1]].'''

בעת השינוי ההגדרות של '''בחירת גרסה יציבה''' כך שייעשה שימוש בגרסאות \"איכותיות\" או \"מושלמות\" כברירת מחדל,
אנא ודאו שבאמת קיימות גרסאות כאלה בדף, אחרת לא תהיה לכך השפעה רבה.",
	'stabilization-perm' => 'אין לכם הרשאה לשנות את תצורת הגרסה היציבה.
להלן ההגדרות הנוכחיות עבור [[:$1|$1]]:',
	'stabilization-page' => 'שם הדף:',
	'stabilization-leg' => 'אנא אשרו את הגדרות הגרסה היציבה',
	'stabilization-select' => 'סדר העדיפויות בבחירת גרסה יציבה',
	'stabilization-select1' => 'הגרסה האיכותית האחרונה; אם לא קיימת, הגרסה הנצפית האחרונה',
	'stabilization-select2' => 'הגרסה האחרונה שנבדקה, ללא קשר לרמת האימות',
	'stabilization-select3' => 'הגרסה המושלמת האחרונה; אם לא קיימת, הגרסה האיכותית או הנצפית האחרונה',
	'stabilization-def' => 'הגרסה המופיעה כברירת מחדל',
	'stabilization-def1' => 'הגרסה היציבה; אם לא קיימת, הגרסה הנוכחית/טיוטה',
	'stabilization-def2' => 'הגרסה הנוכחית/טיוטה',
	'stabilization-restrict' => 'הגבלות על בדיקה אוטומטית',
	'stabilization-restrict-none' => 'אין הגבלות נוספות',
	'stabilization-submit' => 'אישור',
	'stabilization-notexists' => 'אין דף בשם "[[:$1|$1]]".
לא ניתן לבצע תצורה.',
	'stabilization-notcontent' => 'אין אפשרות לבדוק את הדף "[[:$1|$1]]".
לא ניתן לבצע תצורה.',
	'stabilization-comment' => 'סיבה:',
	'stabilization-otherreason' => 'סיבה אחרת:',
	'stabilization-expiry' => 'פקיעה:',
	'stabilization-othertime' => 'זמן פקיעה אחר:',
	'stabilization-sel-short' => 'קדימות',
	'stabilization-sel-short-0' => 'איכות',
	'stabilization-sel-short-1' => 'לא קיים',
	'stabilization-sel-short-2' => 'מושלם',
	'stabilization-def-short' => 'ברירת מחדל',
	'stabilization-def-short-0' => 'נוכחי',
	'stabilization-def-short-1' => 'יציב',
	'stabilize_expiry_invalid' => 'תאריך הפקיעה אינו תקין.',
	'stabilize_expiry_old' => 'תאריך הפקיעה כבר עבר.',
	'stabilize-expiring' => 'פקיעה: $1 (UTC)',
	'stabilization-review' => 'בדיקת הגרסה הנוכחית',
);

/** Hindi (हिन्दी)
 * @author Kaustubh
 */
$messages['hi'] = array(
	'stabilization-tab' => 'व्हेट',
	'stabilization' => 'लेख स्थ्रिर करें',
	'stabilization-text' => "'''[[:$1|$1]] का स्थिर अवतरण किस प्रकार चुना या दर्शाया जाये इस के लिये निम्नलिखित सेटिंग बदलें।'''",
	'stabilization-perm' => 'आपको स्थिर अवतरण बदलनेकी अनुमति नहीं हैं।
[[:$1|$1]]का अभीका सेटींग इस प्रकार हैं:',
	'stabilization-page' => 'पृष्ठ नाम:',
	'stabilization-leg' => 'स्थिर अवतरण सेटिंग निश्चित करें',
	'stabilization-select' => 'स्थिर अवतरण का चुनाव',
	'stabilization-select1' => 'नवीनतम गुणवत्तापूर्ण अवतरण;
अगर उपलब्ध नहीं हैं, तो नवीनतम चुना हुआ अवतरण',
	'stabilization-select2' => 'नवीनतम परिक्षण हुआ अवतरण',
	'stabilization-select3' => 'नवीनतम उत्कृष्ठ अवतरण; अगर उपलब्ध नहीं हैं, तो नवीनतम गुणवत्तापूर्ण या चुना हुआ अवतरण',
	'stabilization-def' => 'डिफॉल्ट पन्ने के साथ बदलाव दर्शायें गयें हैं',
	'stabilization-def1' => 'स्थिर अवतरण;
अगर नहीं हैं, तो सद्य',
	'stabilization-def2' => 'सद्य अवतरण',
	'stabilization-submit' => 'निश्चित करें',
	'stabilization-notexists' => '"[[:$1|$1]]" इस नामका पृष्ठ अस्तित्वमें नहीं हैं।
बदलाव नहीं किये जा सकतें।',
	'stabilization-notcontent' => '"[[:$1|$1]]" यह पृष्ठ जाँचा नहीं जा सकता।
बदलाव नहीं किये जा सकतें।',
	'stabilization-comment' => 'टिप्पणी:',
	'stabilization-expiry' => 'समाप्ति:',
	'stabilization-sel-short' => 'अनुक्रम',
	'stabilization-sel-short-0' => 'गुणवत्ता',
	'stabilization-sel-short-1' => 'बिल्कुल नहीं',
	'stabilization-sel-short-2' => 'उत्कृष्ठ',
	'stabilization-def-short' => 'डिफॉल्ट',
	'stabilization-def-short-0' => 'सद्य',
	'stabilization-def-short-1' => 'स्थिर',
	'stabilize_expiry_invalid' => 'गलत समाप्ति तिथी।',
	'stabilize_expiry_old' => 'यह समाप्ति तिथी गुजर चुकी हैं।',
	'stabilize-expiring' => '$1 (UTC) को समाप्ति',
);

/** Croatian (Hrvatski)
 * @author Dalibor Bosits
 * @author Dnik
 * @author Ex13
 * @author SpeedyGonsales
 */
$messages['hr'] = array(
	'stabilization-tab' => 'vet',
	'stabilization' => 'Stalnost stranice',
	'stabilization-text' => "'''Promijenite postavke kako biste prilagodili kako će važeća inačica [[:\$1|\$1]] biti odabrana i prikazana.'''

Kada mijenjate postavku ''odabir važeće inačice'' kako bi se inačice \"kvalitetno\" ili \"zastarjelo\" rabile kao zadano, provjerite da li stvarno postoje takve inačice stranice, inače će promjene imati mali učinak.",
	'stabilization-perm' => 'Vaš suradnički račun nema prava mijenjanja važeće inačice članka.
Slijede važeće postavke za [[:$1|$1]]:',
	'stabilization-page' => 'Ime stranice:',
	'stabilization-leg' => 'Potvrdi postavke važeće inačice',
	'stabilization-select' => 'Odabir važeće inačice',
	'stabilization-select1' => 'Posljednja ocjena kvalitete; ukoliko je nije bilo, posljednje pregledavanje',
	'stabilization-select2' => 'Posljednja ocijenjena inačica, bez obzira na stupanj provjere valjanosti',
	'stabilization-select3' => 'Najnovija zastarjela inačica; ako ne postoji, tada najnoviju ocjenjenu ili pregledanu',
	'stabilization-def' => 'Inačica koja se prikazuje kao zadana',
	'stabilization-def1' => 'Stabilna inačica; ako je nema, trenutna',
	'stabilization-def2' => 'Trenutačna inačica',
	'stabilization-restrict' => 'Samoocjenjivačka ograničenja',
	'stabilization-restrict-none' => 'Nema dodatnih ograničenja',
	'stabilization-submit' => 'Potvrdite',
	'stabilization-notexists' => 'Ne postoji stranica "[[:$1|$1]]". Namještanje postavki nije moguće.',
	'stabilization-notcontent' => 'Stranica "[[:$1|$1]]" ne može biti ocijenjena. Namještanje postavki nije moguće.',
	'stabilization-comment' => 'Razlog:',
	'stabilization-otherreason' => 'Drugi razlog:',
	'stabilization-expiry' => 'Istječe:',
	'stabilization-othertime' => 'Drugo vrijeme:',
	'stabilization-sel-short' => 'Prvenstvo',
	'stabilization-sel-short-0' => 'Kvaliteta',
	'stabilization-sel-short-1' => 'Nema',
	'stabilization-sel-short-2' => 'Zastarjelo',
	'stabilization-def-short' => 'Zadano',
	'stabilization-def-short-0' => 'Trenutačno',
	'stabilization-def-short-1' => 'Važeća inačica',
	'stabilize_expiry_invalid' => 'Neispravan datum isticanja.',
	'stabilize_expiry_old' => 'Ovo vrijeme isticanja je već prošlo.',
	'stabilize-expiring' => 'ističe $1 (UTC)',
	'stabilization-review' => 'Ocijenite trenutačnu inačicu',
);

/** Upper Sorbian (Hornjoserbsce)
 * @author Michawiki
 */
$messages['hsb'] = array(
	'stabilization-tab' => '(Kwalitne zawěsćenje)',
	'stabilization' => 'Stabilizacija strony',
	'stabilization-text' => "'''Změń slědowace nastajenja, zo by postajił, kak so wozjewjena wersija wot [[:\$1|\$1]] wuběra a zwobraznja.'''

'''Kedźbu:''' změnjenje ''wuběra wozjewjeneje wersije'', zo by so \"kwalitna\" abo \"prěnjotna\" wersija preferowała, njebudźe so wuskutkować, jeli tajke wersije njejsu. Wobkedźbujće tež, zo maja \"kwalitnu\" wersiju za \"skontrolowanu\" wersiju atd.",
	'stabilization-perm' => 'Twoje wužiwarske konto nima trěbne prawo, zo by nastajenja wozjewjeneje wersije změniło.
Aktualne nastajenja za „[[:$1|$1]]“ su:',
	'stabilization-page' => 'Mjeno strony:',
	'stabilization-leg' => 'Nastajenja za wozjewjenu wersiju potwjerdźić',
	'stabilization-select' => 'Porjad wuběra wozjewjeneje wersije',
	'stabilization-select1' => 'Poslednja pruwowana wersija; jeli žana njeje, potom poslednja přehladana wersija',
	'stabilization-select2' => 'Najnowša skontrolowana wersija',
	'stabilization-select3' => 'Poslednja prěnjotna wersija; jeli njeeksistuje, da poslednja přepruwowana abo přehladana wersiaj',
	'stabilization-def' => 'Wersija zwobraznjena w normalnym napohledźe strony',
	'stabilization-def1' => 'Wozjewjena wersija; jeli žana njeeksistuje, da aktualna wersija abo naćisk',
	'stabilization-def2' => 'Aktualna wersija/naćisk',
	'stabilization-restrict' => 'Wobmjezowanja přepruwowanjow/awtomatiskich přepruwowanjow',
	'stabilization-restrict-none' => 'Žane přidatne wobmjezowanja',
	'stabilization-submit' => 'Potwjerdźić',
	'stabilization-notexists' => 'Njeje strona „[[:$1|$1]]“. Žana konfiguracija móžno.',
	'stabilization-notcontent' => 'Strona "[[:$1|$1]]" njeda so pruwować. Žana konfiguracija móžno.',
	'stabilization-comment' => 'Přičina:',
	'stabilization-otherreason' => 'Druha přičina:',
	'stabilization-expiry' => 'Spadnje:',
	'stabilization-othertime' => 'Druhi čas:',
	'stabilization-sel-short' => 'Priorita',
	'stabilization-sel-short-0' => 'Kwalita',
	'stabilization-sel-short-1' => 'Žana',
	'stabilization-sel-short-2' => 'Prěnjotny',
	'stabilization-def-short' => 'Standard',
	'stabilization-def-short-0' => 'Aktualny',
	'stabilization-def-short-1' => 'Wozjewjeny',
	'stabilize_page_invalid' => 'Titul ciloweje strony je njepłaćiwy.',
	'stabilize_page_notexists' => 'Cilowa strona njeeksistuje.',
	'stabilize_page_unreviewable' => 'Cilowa strona w přepruwujomnym mjenowym rumje njeje.',
	'stabilize_invalid_precedence' => 'Njepłaćiwa wersijowa priorita.',
	'stabilize_invalid_autoreview' => 'Njepłaćiwe wobmjezowanje awtomatiskeho přepruwowanja',
	'stabilize_invalid_level' => 'Njepłaćiwy škitny schodźenk.',
	'stabilize_expiry_invalid' => 'Njepłaćiwy datum spadnjenja.',
	'stabilize_expiry_old' => 'Tutón čas spadnjenja je hižo zańdźeny.',
	'stabilize_denied' => 'Prawo zapowědźene.',
	'stabilize-expiring' => 'spadnje $1 hodź. (UTC)',
	'stabilization-review' => 'Aktualnu wersiju jako skontrolowanu markěrować',
);

/** Hungarian (Magyar)
 * @author Dani
 * @author Glanthor Reviol
 * @author Gondnok
 * @author KossuthRad
 * @author Samat
 */
$messages['hu'] = array(
	'stabilization-tab' => 'megjelenítési beállítás',
	'stabilization' => 'Lap jelölt változatainak beállítása',
	'stabilization-text' => "'''Az alábbi beállítások módosításával adhatod meg a(z) [[:$1|$1]] lap közzétett változatának kiválasztási és megjelenítési módját.'''

'''Megjegyzés:''' a ''közzétett változat kiválasztása'' beállítás alapértelmezésének módosítása „minőségi” vagy a „kiemelkedő” változatokra nincs hatással olyan lapokra, amelyeknek nincsenek ilyen változatai. Valamint vedd figyelembe, hogy a „minőségi” változat egyúttal „ellenőrzött” is, és így tovább.",
	'stabilization-perm' => 'Nincs jogosultságod megváltoztatni a közzétett változat beállításait.
A(z) [[:$1|$1]] lapra vonatkozó jelenlegi beállítások:',
	'stabilization-page' => 'A lap címe:',
	'stabilization-leg' => 'Közzétett változat beállításainak megerősítése',
	'stabilization-select' => 'A közzétett változat kiválasztásának sorrendje',
	'stabilization-select1' => 'A legutolsó minőségi változat; ha nincs, akkor a legutolsó ellenőrzött',
	'stabilization-select2' => 'A legutolsó ellenőrzött változat',
	'stabilization-select3' => 'A legutolsó kiemelkedő változat; ha nincs, akkor a legutolsó minőségi vagy ellenőrzött',
	'stabilization-def' => 'Az alapértelmezettként megjelenített változat',
	'stabilization-def1' => 'A közzétett változat; ha nincs, akkor a jelenlegi legutolsó',
	'stabilization-def2' => 'A jelenlegi, még nem ellenőrzött változat',
	'stabilization-restrict' => 'Ellenőrzés/automatikus ellenőrzés korlátozásai',
	'stabilization-restrict-none' => 'Nincsenek külön megkötések',
	'stabilization-submit' => 'Megerősítés',
	'stabilization-notexists' => 'Nincs „[[:$1|$1]]” című lap.
Nem lehet a beállításokat módosítani.',
	'stabilization-notcontent' => 'A(z) „[[:$1|$1]]” lapot nem lehet ellenőrizni.
Nem lehet a beállításokat módosítani.',
	'stabilization-comment' => 'Indok:',
	'stabilization-otherreason' => 'Egyéb indok:',
	'stabilization-expiry' => 'Lejárat:',
	'stabilization-othertime' => 'Más időpont:',
	'stabilization-sel-short' => 'Elsőbbség',
	'stabilization-sel-short-0' => 'minőségi',
	'stabilization-sel-short-1' => 'nincs',
	'stabilization-sel-short-2' => 'kiemelkedő',
	'stabilization-def-short' => 'alapértelmezett',
	'stabilization-def-short-0' => 'jelenlegi',
	'stabilization-def-short-1' => 'közzétett',
	'stabilize_expiry_invalid' => 'Hibás lejárati idő.',
	'stabilize_expiry_old' => 'A megadott lejárati idő már elmúlt.',
	'stabilize-expiring' => 'lejár $1-kor (UTC szerint)',
	'stabilization-review' => 'Aktuális változat ellenőrzöttnek jelölése',
);

/** Interlingua (Interlingua)
 * @author McDutchie
 */
$messages['ia'] = array(
	'stabilization-tab' => 'qualitate',
	'stabilization' => 'Stabilisation de paginas',
	'stabilization-text' => "'''Cambia le configurationes hic infra pro adjustar como le version publicate de [[:\$1|\$1]] es seligite e monstrate.'''

'''Nota:''' cambiar le ''selection de version publicate'' a preferer le versiones \"de qualitate\" o \"pristine\" habera nulle effecto si tal versiones non existe. In ultra, nota que un version de \"qualitate\" es etiam considerate un version \"verificate\", et cetera.",
	'stabilization-perm' => 'Tu conto non ha le permission de cambiar le configuration del version publicate.
Ecce le configurationes actual pro [[:$1|$1]]:',
	'stabilization-page' => 'Nomine del pagina:',
	'stabilization-leg' => 'Confirmar configuration del version publicate',
	'stabilization-select' => 'Prioritate de selection del version publicate',
	'stabilization-select1' => 'Le ultime version de qualitate; si non presente, le ultime version mirate',
	'stabilization-select2' => 'Ultime version verificate',
	'stabilization-select3' => 'Le ultime version pristine; si non presente, le ultime version de qualitate o mirate',
	'stabilization-def' => 'Version monstrate in le visualisation predefinite del pagina',
	'stabilization-def1' => 'Le version publicate; si non presente, le version actual/provisori',
	'stabilization-def2' => 'Le version actual/provisori',
	'stabilization-restrict' => 'Restrictiones de revision/auto-revision',
	'stabilization-restrict-none' => 'Nulle restriction extra',
	'stabilization-submit' => 'Confirmar',
	'stabilization-notexists' => 'Non existe un pagina con titulo "[[:$1|$1]]".
Nulle configuration es possibile.',
	'stabilization-notcontent' => 'Le pagina "[[:$1|$1]]" non pote esser revidite.
Nulle configuration es possibile.',
	'stabilization-comment' => 'Motivo:',
	'stabilization-otherreason' => 'Altere motivo:',
	'stabilization-expiry' => 'Expira:',
	'stabilization-othertime' => 'Altere duration:',
	'stabilization-sel-short' => 'Precedentia',
	'stabilization-sel-short-0' => 'Qualitate',
	'stabilization-sel-short-1' => 'Nulle',
	'stabilization-sel-short-2' => 'Pristine',
	'stabilization-def-short' => 'Predefinition',
	'stabilization-def-short-0' => 'Actual',
	'stabilization-def-short-1' => 'Publicate',
	'stabilize_page_invalid' => 'Le titulo del pagina de destination es invalide.',
	'stabilize_page_notexists' => 'Le pagina de destination non existe.',
	'stabilize_page_unreviewable' => 'Le pagina de destination non es in un spatio de nomines revisibile.',
	'stabilize_invalid_precedence' => 'Precedentia de versiones invalide.',
	'stabilize_invalid_autoreview' => 'Restriction de autorevision invalide.',
	'stabilize_invalid_level' => 'Nivello de protection invalide.',
	'stabilize_expiry_invalid' => 'Data de expiration invalide.',
	'stabilize_expiry_old' => 'Iste tempore de expiration ha ja passate.',
	'stabilize_denied' => 'Permission refusate.',
	'stabilize-expiring' => 'expira le $1 (UTC)',
	'stabilization-review' => 'Marcar le version actual como verificate',
);

/** Indonesian (Bahasa Indonesia)
 * @author Bennylin
 * @author Irwangatot
 * @author Kenrick95
 * @author Rex
 */
$messages['id'] = array(
	'stabilization-tab' => 'cek',
	'stabilization' => 'Pengaturan versi stabil halaman',
	'stabilization-text' => "'''Ubah seting berikut untuk mengatur versi stabil dari [[:\$1|\$1]] telah dipilih dan ditampilkan.'''

Saat mengubah  konfigurasi ''pilihan versi stabil'' gunakan revisi \"berkualitas\" atau \"murni\" secara default,  
pastikan untuk memeriksa apakah ada yang benar-benar seperti revisi di halaman, jika ada perubahan akan sedikit berpengaruh.",
	'stabilization-perm' => 'Akun Anda tak memiliki hak untuk mengganti konfigurasi versi stabil. Berikut konfigurasi terkini dari [[:$1|$1]]:',
	'stabilization-page' => 'Nama halaman:',
	'stabilization-leg' => 'Konfirmasi konfigurasi versi stabil',
	'stabilization-select' => 'Pemilihan versi stabil sebelumnya',
	'stabilization-select1' => 'Revisi layak terakhir; jika tak ada, versi terperiksa terakhir',
	'stabilization-select2' => 'Revisi stabil terakhir, tanpa memandang tingkat validasi',
	'stabilization-select3' => 'Revisi asli terakhir; jika tidak ada, versi layak atau terperiksa terakhir',
	'stabilization-def' => 'Revisi yang ditampilkan sebagai tampilan baku halaman',
	'stabilization-def1' => 'Revisi stabil; jika tak ada, maka terkini/konsep',
	'stabilization-def2' => 'Revisi terkini/konsep',
	'stabilization-restrict' => 'Pembatasan auto-peninjau',
	'stabilization-restrict-none' => 'Tidak ada tambahan pembatasan',
	'stabilization-submit' => 'Konfirmasi',
	'stabilization-notexists' => 'Tak ada halaman berjudul "[[:$1|$1]]".
Konfigurasi tak dapat diterapkan.',
	'stabilization-notcontent' => 'Halaman "[[:$1|$1]]" tak dapat ditinjau.
Konfigurasi tak dapat diterapkan.',
	'stabilization-comment' => 'Alasan:',
	'stabilization-otherreason' => 'Alasan lain:',
	'stabilization-expiry' => 'Kadaluwarsa:',
	'stabilization-othertime' => 'Waktu lain:',
	'stabilization-sel-short' => 'Pengutamaan',
	'stabilization-sel-short-0' => 'Layak',
	'stabilization-sel-short-1' => 'Tidak ada',
	'stabilization-sel-short-2' => 'Asli',
	'stabilization-def-short' => 'Baku',
	'stabilization-def-short-0' => 'Terkini',
	'stabilization-def-short-1' => 'Stabil',
	'stabilize_expiry_invalid' => 'Tanggal kadaluwarsa tak valid.',
	'stabilize_expiry_old' => 'Tanggal kadaluwarsa telah terlewati.',
	'stabilize_denied' => 'Hak ases ditolak',
	'stabilize-expiring' => 'kadaluwarsa $1 (UTC)',
	'stabilization-review' => 'Tinjau versi sekarang',
);

/** Igbo (Igbo)
 * @author Ukabia
 */
$messages['ig'] = array(
	'stabilization-comment' => 'Mgbaghaputa:',
	'stabilization-sel-short-1' => 'O digị',
);

/** Ido (Ido)
 * @author Malafaya
 */
$messages['io'] = array(
	'stabilization-comment' => 'Motivo:',
	'stabilization-otherreason' => 'Altra motivo:',
	'stabilization-othertime' => 'Altra tempo:',
);

/** Icelandic (Íslenska)
 * @author S.Örvarr.S
 */
$messages['is'] = array(
	'stabilization-page' => 'Titill síðu:',
	'stabilization-submit' => 'Staðfesta',
	'stabilization-comment' => 'Athugasemd:',
	'stabilization-sel-short-0' => 'Gæði',
);

/** Italian (Italiano)
 * @author Darth Kule
 * @author Gianfranco
 * @author Melos
 * @author Nemo bis
 * @author Pietrodn
 */
$messages['it'] = array(
	'stabilization' => 'Stabilizzazione pagina',
	'stabilization-text' => "'''Modifica le impostazioni sotto per regolare come la versione stabile di [[:\$1|\$1]] è selezionata e visualizzata.'''

Quando cambi la configurazione ''selezione versione stabile'' per usare di default le revisioni \"qualità\" o \"immacolata\",
assicurati di controllare se effettivamente ci siano nella pagina tali revisioni, altrimenti la modifica non avrà molto effetto.",
	'stabilization-perm' => "L'utente non dispone dei permessi necessari a cambiare la configurazione della versione stabile.
Qui ci sono le impostazioni attuali per [[:$1|$1]]:",
	'stabilization-page' => 'Nome della pagina:',
	'stabilization-leg' => 'Conferma le impostazioni della versione stabile',
	'stabilization-select' => 'Priorità per la selezione della versione stabile',
	'stabilization-select1' => "L'ultima versione di qualità; se non presente, allora l'ultima visionata",
	'stabilization-select2' => "L'ultima versione revisionata, indipendentemente dal livello di validazione",
	'stabilization-def' => 'Revisione visualizzata di default alla visita della pagina',
	'stabilization-def1' => 'La versione stabile; se non disponibile, quella attuale o la bozza',
	'stabilization-def2' => 'La revisione/bozza attuale',
	'stabilization-restrict' => "Restrizioni sull'auto-revisione",
	'stabilization-restrict-none' => "Nessun'ulteriore restrizione",
	'stabilization-submit' => 'Conferma',
	'stabilization-notexists' => 'Non ci sono pagine col titolo "[[:$1|$1]]".
Non è possibile effettuare la configurazione.',
	'stabilization-notcontent' => 'La pagina "[[:$1|$1]]" non può essere revisionata.
Non è possibile effettuare la configurazione.',
	'stabilization-comment' => 'Motivo:',
	'stabilization-otherreason' => 'Altro motivo:',
	'stabilization-expiry' => 'Scadenza:',
	'stabilization-othertime' => 'Altra durata:',
	'stabilization-sel-short' => 'Precedenza',
	'stabilization-sel-short-0' => 'Qualità',
	'stabilization-sel-short-1' => 'Nessuna',
	'stabilization-sel-short-2' => 'Immacolata',
	'stabilization-def-short' => 'Default',
	'stabilization-def-short-0' => 'Attuale',
	'stabilization-def-short-1' => 'Stabile',
	'stabilize_expiry_invalid' => 'Data di scadenza non valida.',
	'stabilize_expiry_old' => 'La data di scadenza è già passata.',
	'stabilize-expiring' => 'scadenza: $1 (UTC)',
	'stabilization-review' => 'Revisiona la versione corrente',
);

/** Japanese (日本語)
 * @author Aotake
 * @author Fryed-peach
 * @author Hosiryuhosi
 * @author JtFuruhata
 * @author Whym
 * @author 青子守歌
 */
$messages['ja'] = array(
	'stabilization-tab' => '固定',
	'stabilization' => '表示ページの固定',
	'stabilization-text' => "'''以下で [[:$1|$1]] の公開版の選択方法と表示方法を変更できます。'''

'''注意:'''「{{int:stabilization-select}}」設定にて{{int:stabilization-sel-short-0}}もしくは{{int:stabilization-sel-short-2}}を既定とする場合は、ページに該当する版が実際に存在することを確認してください。また、{{int:stabilization-sel-short-0}}版は「査読済み」版と見なされます。",
	'stabilization-perm' => 'あなたのアカウントには公開版の設定を変更する権限がありません。現在の [[:$1|$1]] における設定は以下の通りです:',
	'stabilization-page' => 'ページ名:',
	'stabilization-leg' => '公開版の設定確認',
	'stabilization-select' => '公開版の選択の優先順位',
	'stabilization-select1' => '最新の{{int:revreview-lev-quality}}版、それがない場合は、最新の{{int:revreview-lev-basic}}版',
	'stabilization-select2' => '最新の査読済み版',
	'stabilization-select3' => '最新の{{int:revreview-lev-pristine}}版、それがない場合は、最新の{{int:revreview-lev-quality}}版もしくは{{int:revreview-lev-basic}}版',
	'stabilization-def' => 'ページに既定で表示する版',
	'stabilization-def1' => '公開版、それがない場合は、最新または候補版',
	'stabilization-def2' => '最新または候補版',
	'stabilization-restrict' => '査読および自動査読の制限',
	'stabilization-restrict-none' => '追加制限なし',
	'stabilization-submit' => '設定',
	'stabilization-notexists' => '「[[:$1|$1]]」というページは存在しないため、設定できません。',
	'stabilization-notcontent' => 'ページ「[[:$1|$1]]」は査読対象ではないため、設定できません。',
	'stabilization-comment' => '理由:',
	'stabilization-otherreason' => 'その他の理由:',
	'stabilization-expiry' => '有効期限:',
	'stabilization-othertime' => 'その他の日時:',
	'stabilization-sel-short' => '優先度',
	'stabilization-sel-short-0' => '{{int:revreview-lev-quality}}',
	'stabilization-sel-short-1' => '不問',
	'stabilization-sel-short-2' => '{{int:revreview-lev-pristine}}',
	'stabilization-def-short' => '既定表示',
	'stabilization-def-short-0' => '最新版',
	'stabilization-def-short-1' => '公開済み',
	'stabilize_page_invalid' => '指定したページ名が無効です。',
	'stabilize_page_notexists' => '指定したページ名が存在しません。',
	'stabilize_page_unreviewable' => '指定したページは査読可能な名前空間にありません。',
	'stabilize_invalid_precedence' => '無効なバージョン優先度。',
	'stabilize_invalid_autoreview' => '無効な自動査読の制限。',
	'stabilize_invalid_level' => '不正な保護レベル。',
	'stabilize_expiry_invalid' => '有効期限に不正な日時が設定されました。',
	'stabilize_expiry_old' => '有効期限に指定された日時を過ぎています。',
	'stabilize_denied' => '許可されていません。',
	'stabilize-expiring' => '有効期限: $1 (UTC)',
	'stabilization-review' => '現在の版を査読済みとする',
);

/** Jutish (Jysk)
 * @author Huslåke
 * @author Ælsån
 */
$messages['jut'] = array(
	'stabilization-tab' => 'vet',
	'stabilization-page' => 'Pægenavn:',
	'stabilization-def' => 'Reviisje displayen åp somår pæger sigt',
	'stabilization-def1' => 'Æ ståbiil reviisje;
als ekke er, dan æ nuværende',
	'stabilization-def2' => 'Æ nuværende reviisje',
	'stabilization-submit' => 'Konfirmær',
	'stabilization-notexists' => 'Her har ekke pæge nåm "[[:$1|$1]]".
Ekke konfiguråsje er mågleg.',
	'stabilization-notcontent' => 'Æ pæge "[[:$1|$1]]" ken ekke være sæn.
Ekke konfiguråsje er mågleg.',
	'stabilization-comment' => 'Bemærkenge:',
	'stabilization-expiry' => 'Duråsje:',
	'stabilization-sel-short' => 'Præsedens',
	'stabilization-sel-short-0' => 'Kwalitæ',
	'stabilization-sel-short-1' => 'Ekke',
	'stabilization-def-short' => 'Åtåmatisk',
	'stabilization-def-short-0' => 'Nuværende',
	'stabilization-def-short-1' => 'Stabiil',
	'stabilize_expiry_invalid' => 'Ugyldegt duråsje dåt æller tiid.',
	'stabilize_expiry_old' => 'Dette duråsje tiid er ål passærn.',
	'stabilize-expiring' => 'durær biis $1 (UTC)',
);

/** Javanese (Basa Jawa)
 * @author Meursault2004
 */
$messages['jv'] = array(
	'stabilization-sel-short-0' => 'Kwalitas',
);

/** Georgian (ქართული)
 * @author BRUTE
 * @author გიორგიმელა
 */
$messages['ka'] = array(
	'stabilization-page' => 'გვერდის სახელი:',
	'stabilization-restrict-none' => 'არც-ერთი დამატებითი აკრძალვა',
	'stabilization-submit' => 'დამოწმება',
	'stabilization-notexists' => 'არ არსებობს გვერდი სახელით "[[:$1|$1]]".
კონფიგურაცია შეუძლებელია.',
	'stabilization-notcontent' => 'გვერდი «[[:$1|$1]]» ვერ შემოწმდება. კონფიგურაცია შეუძლებელია.',
	'stabilization-comment' => 'მიზეზი:',
	'stabilization-otherreason' => 'სხვა მიზეზი:',
	'stabilization-expiry' => 'ვადა:',
	'stabilization-othertime' => 'სხვა დრო:',
	'stabilization-sel-short' => 'პრიორიტეტი:',
	'stabilization-sel-short-0' => 'რეცენზირებული',
	'stabilization-sel-short-1' => 'არაფერი',
	'stabilization-sel-short-2' => 'შესანიშნავი',
	'stabilization-def-short' => 'თავდაპირველი',
	'stabilization-def-short-0' => 'მიმდინარე',
	'stabilization-def-short-1' => 'გამოქვეყნებული',
	'stabilize_expiry_invalid' => 'ვადის გასვლის არასწორი თარიღი.',
	'stabilize_expiry_old' => 'მოქმედების ვადა გავიდა.',
	'stabilize-expiring' => 'ვადა გასდის: $1 (UTC)',
	'stabilization-review' => 'მონიშნეთ ამჟამინდელი ცვლილება შემოწმებულად',
);

/** Kazakh (Arabic script) (‫قازاقشا (تٴوتە)‬) */
$messages['kk-arab'] = array(
	'stabilization-tab' => '(سق)',
	'stabilization' => 'بەتتى تىياناقتاۋ',
	'stabilization-text' => 'تومەندەگى باپتالىمداردى وزگەرتكەندە [[:$1|$1]] دەگەننىڭ تىياناقتى نۇسقاسى قالاي بولەكتەنۋى مەن كورسەتىلۋى تۇزەتىلەدى.',
	'stabilization-perm' => 'تىركەلگىڭىزگە تىياناقتى نۇسقانىڭ باپتالىمىن وزگەرتۋگە رۇقسات بەرىلمەگەن.
[[:$1|$1]] ٴۇشىن اعىمداعى باپتاۋلار مىندا كەلتىرىلەدى:',
	'stabilization-page' => 'بەت اتاۋى:',
	'stabilization-leg' => 'بەت ٴۇشىن تىياناقتى نۇسقانى باپتاۋ',
	'stabilization-select' => 'تىياناقتى نۇسقا قالاي بولەكتەنەدى',
	'stabilization-select1' => 'ەڭ سوڭعى ساپالى نۇسقاسى; ەگەر جوق بولسا, ەڭ سوڭعى شولىنعانداردىڭ بىرەۋى بولادى',
	'stabilization-select2' => 'ەڭ سوڭعى سىن بەرىلگەن نۇسقا',
	'stabilization-def' => 'بەتتىڭ ادەپكى كورىنىسىندە كەلتىرىلەتىن نۇسقا',
	'stabilization-def1' => 'تىياناقتى نۇسقاسى; ەگەر جوق بولسا, اعىمداعىلاردىڭ بىرەۋى بولادى',
	'stabilization-def2' => 'اعىمدىق نۇسقاسى',
	'stabilization-submit' => 'قۇپتاۋ',
	'stabilization-notexists' => '«[[:$1|$1]]» دەپ اتالعان ەش بەت جوق. ەش باپتالىم رەتتەلمەيدى.',
	'stabilization-notcontent' => '«[[:$1|$1]]» دەگەن بەتكە سىن بەرىلمەيدى. ەش باپتالىم رەتتەلمەيدى.',
	'stabilization-comment' => 'ماندەمە:',
	'stabilization-sel-short' => 'ارتىقشىلىق',
	'stabilization-sel-short-0' => 'ساپالى',
	'stabilization-sel-short-1' => 'ەشقانداي',
	'stabilization-def-short' => 'ادەپكى',
	'stabilization-def-short-0' => 'اعىمدىق',
	'stabilization-def-short-1' => 'تىياناقتى',
);

/** Kazakh (Cyrillic) (Қазақша (Cyrillic)) */
$messages['kk-cyrl'] = array(
	'stabilization-tab' => '(сқ)',
	'stabilization' => 'Бетті тиянақтау',
	'stabilization-text' => 'Төмендегі бапталымдарды өзгерткенде [[:$1|$1]] дегеннің тиянақты нұсқасы қалай бөлектенуі мен көрсетілуі түзетіледі.',
	'stabilization-perm' => 'Тіркелгіңізге тиянақты нұсқаның бапталымын өзгертуге рұқсат берілмеген.
[[:$1|$1]] үшін ағымдағы баптаулар мында келтіріледі:',
	'stabilization-page' => 'Бет атауы:',
	'stabilization-leg' => 'Бет үшін тиянақты нұсқаны баптау',
	'stabilization-select' => 'Тиянақты нұсқа қалай бөлектенеді',
	'stabilization-select1' => 'Ең соңғы сапалы нұсқасы; егер жоқ болса, ең соңғы шолынғандардың біреуі болады',
	'stabilization-select2' => 'Ең соңғы сын берілген нұсқа',
	'stabilization-def' => 'Беттің әдепкі көрінісінде келтірілетін нұсқа',
	'stabilization-def1' => 'Тиянақты нұсқасы; егер жоқ болса, ағымдағылардың біреуі болады',
	'stabilization-def2' => 'Ағымдық нұсқасы',
	'stabilization-submit' => 'Құптау',
	'stabilization-notexists' => '«[[:$1|$1]]» деп аталған еш бет жоқ. Еш бапталым реттелмейді.',
	'stabilization-notcontent' => '«[[:$1|$1]]» деген бетке сын берілмейді. Еш бапталым реттелмейді.',
	'stabilization-comment' => 'Мәндеме:',
	'stabilization-sel-short' => 'Артықшылық',
	'stabilization-sel-short-0' => 'Сапалы',
	'stabilization-sel-short-1' => 'Ешқандай',
	'stabilization-def-short' => 'Әдепкі',
	'stabilization-def-short-0' => 'Ағымдық',
	'stabilization-def-short-1' => 'Тиянақты',
);

/** Kazakh (Latin) (Қазақша (Latin)) */
$messages['kk-latn'] = array(
	'stabilization-tab' => '(sq)',
	'stabilization' => 'Betti tïyanaqtaw',
	'stabilization-text' => 'Tömendegi baptalımdardı özgertkende [[:$1|$1]] degenniñ tïyanaqtı nusqası qalaý bölektenwi men körsetilwi tüzetiledi.',
	'stabilization-perm' => 'Tirkelgiñizge tïyanaqtı nusqanıñ baptalımın özgertwge ruqsat berilmegen.
[[:$1|$1]] üşin ağımdağı baptawlar mında keltiriledi:',
	'stabilization-page' => 'Bet atawı:',
	'stabilization-leg' => 'Bet üşin tïyanaqtı nusqanı baptaw',
	'stabilization-select' => 'Tïyanaqtı nusqa qalaý bölektenedi',
	'stabilization-select1' => 'Eñ soñğı sapalı nusqası; eger joq bolsa, eñ soñğı şolınğandardıñ birewi boladı',
	'stabilization-select2' => 'Eñ soñğı sın berilgen nusqa',
	'stabilization-def' => 'Bettiñ ädepki körinisinde keltiriletin nusqa',
	'stabilization-def1' => 'Tïyanaqtı nusqası; eger joq bolsa, ağımdağılardıñ birewi boladı',
	'stabilization-def2' => 'Ağımdıq nusqası',
	'stabilization-submit' => 'Quptaw',
	'stabilization-notexists' => '«[[:$1|$1]]» dep atalğan eş bet joq. Eş baptalım rettelmeýdi.',
	'stabilization-notcontent' => '«[[:$1|$1]]» degen betke sın berilmeýdi. Eş baptalım rettelmeýdi.',
	'stabilization-comment' => 'Mändeme:',
	'stabilization-sel-short' => 'Artıqşılıq',
	'stabilization-sel-short-0' => 'Sapalı',
	'stabilization-sel-short-1' => 'Eşqandaý',
	'stabilization-def-short' => 'Ädepki',
	'stabilization-def-short-0' => 'Ağımdıq',
	'stabilization-def-short-1' => 'Tïyanaqtı',
);

/** Khmer (ភាសាខ្មែរ)
 * @author Chhorran
 * @author Lovekhmer
 * @author Thearith
 * @author គីមស៊្រុន
 */
$messages['km'] = array(
	'stabilization-page' => 'ឈ្មោះទំព័រ៖',
	'stabilization-def2' => 'ការពិនិត្យឡើងវិញពេលបច្ចុប្បន្ន',
	'stabilization-submit' => 'បញ្ជាក់ទទួលស្គាល់',
	'stabilization-comment' => 'មូលហេតុ៖',
	'stabilization-expiry' => 'ផុតកំណត់៖',
	'stabilization-sel-short-0' => 'គុណភាព',
	'stabilization-sel-short-1' => 'ទទេ',
	'stabilization-def-short' => 'លំនាំដើម',
	'stabilization-def-short-0' => 'បច្ចុប្បន្ន',
	'stabilization-def-short-1' => 'ឋិតថេរ',
	'stabilize_expiry_invalid' => 'កាលបរិច្ឆេទផុតកំណត់មិនត្រឹមត្រូវ។',
	'stabilize-expiring' => 'ផុតកំណត់ម៉ោង $1 (UTC)',
);

/** Kannada (ಕನ್ನಡ)
 * @author Nayvik
 */
$messages['kn'] = array(
	'stabilization-comment' => 'ಕಾರಣ:',
);

/** Korean (한국어)
 * @author Devunt
 * @author Kwj2772
 */
$messages['ko'] = array(
	'stabilization-tab' => '검토',
	'stabilization' => '문서 배포 설정',
	'stabilization-text' => "'''[[:\$1|\$1]] 문서의 배포판을 어떻게 선택되어 보여질 지에 대한 설정을 아래 양식을 통해 바꿀 수 있습니다.'''

'''참고:''' ''배포판 선택''을 \"고품질판\"이나 \"깨끗한 판\"으로 바꾸는 것은 그런 판이 없다면 아무런 효과가 없습니다. 그리고 \"고품질판\"이나 \"깨끗한 판\" 역시 \"검토된 판\"으로 간주됩니다.",
	'stabilization-perm' => '당신의 계정은 게시 설정 변경을 할 수 있는 권한이 없습니다.
[[:$1|$1]]에 현재 설정이 있습니다',
	'stabilization-page' => '문서 이름:',
	'stabilization-leg' => '게시 설정 확인',
	'stabilization-select' => '게시 버전 선택 우선순위',
	'stabilization-select1' => '최근 품질 판; 마지막 확인된 판',
	'stabilization-select2' => '마지막 확인된 판',
	'stabilization-select3' => '마지막 원본 판; 마지막 풀질 판, 마지막 확인된 판',
	'stabilization-def' => '기본 문서 보기에서 판 표시',
	'stabilization-def1' => '게시 판; 현재 판이 아니라면, 현재/임시 판',
	'stabilization-def2' => '현재/임시 판',
	'stabilization-restrict' => '검토/자동 검토 제한',
	'stabilization-restrict-none' => '추가 제한 없음',
	'stabilization-submit' => '확인',
	'stabilization-notexists' => '"[[:$1|$1]]" 문서가 존재하지 않습니다.
설정이 불가능합니다.',
	'stabilization-notcontent' => '"[[:$1|$1]]" 문서는 검토할 수 없습니다.
설정이 불가능합니다.',
	'stabilization-comment' => '이유:',
	'stabilization-otherreason' => '다른 이유:',
	'stabilization-expiry' => '기한:',
	'stabilization-othertime' => '다른 시간:',
	'stabilization-sel-short' => '우선 순위',
	'stabilization-sel-short-0' => '품질',
	'stabilization-sel-short-1' => '없음',
	'stabilization-sel-short-2' => '원본',
	'stabilization-def-short' => '기본 설정',
	'stabilization-def-short-0' => '현재',
	'stabilization-def-short-1' => '게시',
	'stabilize_page_invalid' => '문서 이름이 잘못되었습니다.',
	'stabilize_page_notexists' => '문서가 존재하지 않습니다.',
	'stabilize_page_unreviewable' => '문서가 검토 가능한 이름공간에 존재하지 않습니다',
	'stabilize_invalid_precedence' => '잘못된 우선순위.',
	'stabilize_invalid_autoreview' => '잘못된 자동 검토 제한',
	'stabilize_invalid_level' => '잘못된 보호 수준.',
	'stabilize_expiry_invalid' => '기한을 잘못 입력하였습니다.',
	'stabilize_expiry_old' => '기한을 과거로 입력하였습니다.',
	'stabilize_denied' => '권한 없음',
	'stabilize-expiring' => '$1 (UTC)에 만료',
	'stabilization-review' => '현재 판을 확인한 것으로 표시',
);

/** Colognian (Ripoarisch)
 * @author Purodha
 */
$messages['ksh'] = array(
	'stabilization-tab' => 'Qualliteit',
	'stabilization' => 'Enshtellunge för beschtändijje Sigge',
	'stabilization-text' => "'''Donn de Enshtellunge onge aanpasse, öm faßzelääje, wi de {{int:stablepages-stable}} vun [[:$1|$1]] ußjesöhk un aanjezeijsch weedt.'''

Wann De de Enshtellung „{{int:stabilization-select}}“ änders, dat shtandattmääßej_en {{int:revreview-lev-quality}} udder en {{int:revreview-lev-pristine}} jenumme weedt, dann jiv drop aach, dat di Sigg och su en Version hät. Söns weedt Ding Änderung wall winnisch ußmaache <!-- ( --> :-)",
	'stabilization-perm' => 'Dir fäählt et Rääsch, de Enshtellunge för de beshtändijje Versione vun Sigge ze verändere. Dat hee sin de aktoälle Enshtellunge för di Sigg „[[:$1|$1]]“:',
	'stabilization-page' => 'Name fun dä Sigg:',
	'stabilization-leg' => 'Enshtellunge för de {{int:stablepages-stable}} vun en Sigg beschtäätejje',
	'stabilization-select' => 'Dä Vörrang för de {{int:stablepages-stable}} faßlääje',
	'stabilization-select1' => 'De neußte {{int:revreview-lev-quality}}, un wann et di nit jitt, dann donn de neußte {{int:revreview-lev-basic}} nämme',
	'stabilization-select2' => 'De neuste nohjekik Version, onafhängesch vun de Zoot Beschtäätejung',
	'stabilization-select3' => 'De letzte {{int:revreview-lev-pristine}}, un wann et di nit jitt, dann donn de neußte {{int:revreview-lev-quality}} nämme udder de neußte {{int:revreview-lev-basic}}',
	'stabilization-def' => 'De Version, di shtanndatmääßesch aanjezeisch weed, wann Eine en Sigg opröhf',
	'stabilization-def1' => 'De {{int:stablepages-stable}}, un wann et kein jitt, dann dä aktoälle Äntworf.',
	'stabilization-def2' => 'Dä aktoälle Äntworf',
	'stabilization-restrict' => 'Ennschrängkunge för et automattesch als nohjekik Makeere',
	'stabilization-restrict-none' => 'Kein zohsäzlejje Beschränkunge',
	'stabilization-submit' => 'Bestätije',
	'stabilization-notexists' => 'Mer han kein Sigg met dämm Tittel „[[:$1|$1]]“.
Et jit nix enzestelle.',
	'stabilization-notcontent' => 'De Sigg met dämm Tittel „[[:$1|$1]]“ kam_mer nit nohkike.
Et jidd och nix ennzeshtelle.',
	'stabilization-comment' => 'Jrond:',
	'stabilization-otherreason' => 'Ene andere Jrond:',
	'stabilization-expiry' => 'Leuf uß:',
	'stabilization-othertime' => 'En ander Zick:',
	'stabilization-sel-short' => 'Weeschteschkeit',
	'stabilization-sel-short-0' => 'Qualliteit',
	'stabilization-sel-short-1' => 'Kein',
	'stabilization-sel-short-2' => 'Orshprönglesche',
	'stabilization-def-short' => 'Shtandatt',
	'stabilization-def-short-0' => 'Von jetz',
	'stabilization-def-short-1' => 'Beshtändesch',
	'stabilize_expiry_invalid' => 'Dat Affloufdattum es nit jöltisch.',
	'stabilize_expiry_old' => 'Dat Affloufdattum es ald förbei.',
	'stabilize-expiring' => 'leuf uß, am $2 öm $3 Uhr (UTC)',
	'stabilization-review' => 'Donn de aktoälle Version nohkike',
);

/** Latin (Latina)
 * @author SPQRobin
 */
$messages['la'] = array(
	'stabilization-page' => 'Nomen paginae:',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 */
$messages['lb'] = array(
	'stabilization-tab' => 'Astellung',
	'stabilization' => 'Stabilisatioun vun der Säit',
	'stabilization-text' => "'''Ännert d'Astellungen ënnendrënner fir anzestellen wéi déi publizéiert Versioun vu(n) [[:\$1|\$1]] erausgesicht an ugewise gëtt.'''

'''Opgepasst:''' Wann Dir d'Astellung fir ''d'Eraussiche vun der stabiler Versioun'' esou astellt datt Versioune \"Qualitéit'' oder ''Intakt'' als Standard ugewise ginn, da vergewëssert Iech dat et wierklech esou Versioune gëtt fir déi Säit, soss huet d'Ännerung wéineg Effekt. Dent och drun datt eng ''Qualitéits''-Versioun och also nogekuckte Versioun gëllt a sou weider.",
	'stabilization-perm' => "Äre Benotzerkont huet net d'Recht fir d'Astellung vun der publizéierter Versioun z'änneren.
Hei sinn déi aktuell Astellunge fir [[:$1|$1]]:",
	'stabilization-page' => 'Säitennumm:',
	'stabilization-leg' => "Confirméiert d'publizéiert-Versiouns-Astellungen",
	'stabilization-select' => 'Prioritéit vun der Auswiel vun der publizéierter Versioun',
	'stabilization-select1' => 'Déi lescht Qualitéitsversioun; wann net, dann déi lescht gepréifte Versioun',
	'stabilization-select2' => 'Déi lescht nogekuckte Versioun',
	'stabilization-select3' => 'Déi lescht intakt Versioun; duerno déi lescht Qualitéitsversioun; duerno déi lescht nogekuckte Versioun',
	'stabilization-def' => 'Versioun déi als Standard beim Weise vun der Säit gewise gëtt',
	'stabilization-def1' => 'Déi publizéiert Versioun; oder wann et keng gëtt, déi aktuell/Virbereedung',
	'stabilization-def2' => 'Déi aktuell Versioun',
	'stabilization-restrict' => 'Limitatioune vum Nokucken/automatesche Nokucken',
	'stabilization-restrict-none' => 'Keng speziell Restriktiounen',
	'stabilization-submit' => 'Confirméieren',
	'stabilization-notexists' => 'D\'Säit "[[:$1|$1]]" gëtt et net.
Keng Astellunge méiglech.',
	'stabilization-notcontent' => 'D\'Säit "[[:$1|$1]]" kann net nogekuckt ginn.
Et ass keng Konfiguratioun méiglech.',
	'stabilization-comment' => 'Grond:',
	'stabilization-otherreason' => 'Anere Grond:',
	'stabilization-expiry' => 'Valabel bis:',
	'stabilization-othertime' => 'Aner Zäit:',
	'stabilization-sel-short' => 'Priorititéit',
	'stabilization-sel-short-0' => 'Qualitéit',
	'stabilization-sel-short-1' => 'Keng',
	'stabilization-sel-short-2' => 'Intakt',
	'stabilization-def-short' => 'Standard',
	'stabilization-def-short-0' => 'Aktuell',
	'stabilization-def-short-1' => 'Publizéiert',
	'stabilize_page_invalid' => 'Den Titel vun der Zilsäit ass net valabel.',
	'stabilize_page_notexists' => "D'Zilsäit gëtt et net",
	'stabilize_page_unreviewable' => "D'Zilsäit ass net an engem Nummraum wou Säite kënnen nogekuckt ginn.",
	'stabilize_expiry_invalid' => 'Net valabele Schlussdatum',
	'stabilize_expiry_old' => 'Den Oflafdatum ass schonn eriwwer.',
	'stabilize_denied' => 'Erlaabnes refuséiert',
	'stabilize-expiring' => 'bis $1 (UTC)',
	'stabilization-review' => 'Déi aktuell Versioun als nogekuckt markéieren',
);

/** Limburgish (Limburgs)
 * @author Matthias
 * @author Ooswesthoesbes
 */
$messages['li'] = array(
	'stabilization-tab' => '(kb)',
	'stabilization' => 'Paginastabilisatie',
	'stabilization-text' => "'''Wijzig de onderstaande instellingen om aan te passen hoe de stabiele versie van [[:$1|$1]] geselecteerd is en getoond wordt.'''",
	'stabilization-perm' => 'Uw account heeft niet de toelating om de stabiele versie te wijzigen.
Dit zijn de huidige instellingen voor [[:$1|$1]]:',
	'stabilization-page' => 'Pazjenanaam:',
	'stabilization-leg' => "Stebiel versie van 'ne pazjena aanpasse",
	'stabilization-select' => 'Wie de stebiel versie wörd geselecteerd',
	'stabilization-select1' => "De letste kwaliteitsversie; es dae d'r neet is, den de letste bedoordeilde versie",
	'stabilization-select2' => 'De letste beoordeilde versie',
	'stabilization-def' => 'Versie dae standerd getuund wörd',
	'stabilization-def1' => 'De stebiel verzie',
	'stabilization-def2' => 'De hujig versie',
	'stabilization-submit' => 'Bevestige',
	'stabilization-notexists' => 'd\'r Is geine pazjena "[[:$1|$1]]". Instelle is neet meugelik.',
	'stabilization-notcontent' => 'De pazjena "[[:$1|$1]]" kin neet beoordeild waere. Instelle is neet meugelik.',
	'stabilization-comment' => 'Opmerking:',
	'stabilization-expiry' => 'Verloup:',
	'stabilization-sel-short' => 'Veurrang',
	'stabilization-sel-short-0' => 'Kwaliteit',
	'stabilization-sel-short-1' => 'Gein',
	'stabilization-def-short' => 'Standerd',
	'stabilization-def-short-0' => 'Hujig',
	'stabilization-def-short-1' => 'Stabiel',
	'stabilize_expiry_invalid' => 'Ongeldige verloopdatum.',
	'stabilize_expiry_old' => 'Deze verloopdatum is al verstreke.',
	'stabilize-expiring' => 'verloopt $1 (UTC)',
);

/** Lithuanian (Lietuvių)
 * @author Matasg
 */
$messages['lt'] = array(
	'stabilization-page' => 'Puslapio pavadinimas:',
	'stabilization-submit' => 'Patvirtinti',
	'stabilization-comment' => 'Komentaras:',
	'stabilization-sel-short-0' => 'Kokybė',
	'stabilization-def-short' => 'Standartinis',
	'stabilization-def-short-0' => 'Esamas',
);

/** Eastern Mari (Олык Марий)
 * @author Сай
 */
$messages['mhr'] = array(
	'stabilization-page' => 'Лаштыкын лӱмжӧ:',
	'stabilization-def-short' => 'Ойлыде',
);

/** Macedonian (Македонски)
 * @author Bjankuloski06
 * @author Brest
 */
$messages['mk'] = array(
	'stabilization-tab' => 'конфиг.',
	'stabilization' => 'Стабилизација на страница',
	'stabilization-text' => "'''Изменете ги поставките подолу за да прилагодите како се одбира и прикажува објавената верзија на [[:$1|$1]].'''

'''Напомена:''' ако го смените параметарот за ''избор на објавена верзија'' во „квалитетна“ или „неменувана“, ова нема да има ефект ако не постојат такви верзии. Имајте на ум дека „квалитетните“ верзии се сметаат за „проверени“ верзии и така натаму.",
	'stabilization-perm' => 'Вашата сметка нема дозвола за промена на конфигурацијата на објавената верзија.
Еве ги моменталните нагодувања за [[:$1|$1]]:',
	'stabilization-page' => 'Име на страница:',
	'stabilization-leg' => 'Потврди нагодувања за објавена верзија',
	'stabilization-select' => 'Редослед на избор на објавена верзија',
	'stabilization-select1' => 'Последната квалитетна верзија; ако не постои, тогаш последната прегледана',
	'stabilization-select2' => 'Последна проверена ревизија',
	'stabilization-select3' => 'Последната неменувана верзија; ако не постои, тогаш последната квалитетна или прегледана.',
	'stabilization-def' => 'Верзија прикажана по основно при преглед на страница',
	'stabilization-def1' => 'Објавената ревизија; ако не постои, тогаш моменталната/работната',
	'stabilization-def2' => 'Моментална/работната верзија',
	'stabilization-restrict' => 'Ограничувања на прегледување/автопрегледување',
	'stabilization-restrict-none' => 'Нема дополнителни ограничувања',
	'stabilization-submit' => 'Потврди',
	'stabilization-notexists' => 'Нема страница насловена како "[[:$1|$1]]".
Не е можно нагодување.',
	'stabilization-notcontent' => 'Страницата "[[:$1|$1]]" не може да се проверува.
Не е можно нагодување.',
	'stabilization-comment' => 'Причина:',
	'stabilization-otherreason' => 'Друга причина:',
	'stabilization-expiry' => 'Истекува:',
	'stabilization-othertime' => 'Друго време:',
	'stabilization-sel-short' => 'Исклучок',
	'stabilization-sel-short-0' => 'Квалитет',
	'stabilization-sel-short-1' => 'Ништо',
	'stabilization-sel-short-2' => 'Нерасипана',
	'stabilization-def-short' => 'Основно',
	'stabilization-def-short-0' => 'Моментално',
	'stabilization-def-short-1' => 'Објавена',
	'stabilize_page_invalid' => 'Целната страница е неважечка.',
	'stabilize_page_notexists' => 'Целната страница не постои.',
	'stabilize_page_unreviewable' => 'Целната страница не е во проверлив именски простор.',
	'stabilize_invalid_precedence' => 'Неважечко првенство на верзија.',
	'stabilize_invalid_autoreview' => 'Неважечко ограничување на автопрегледот',
	'stabilize_invalid_level' => 'Неважечко ниво на заштита.',
	'stabilize_expiry_invalid' => 'Погрешен датум на важност.',
	'stabilize_expiry_old' => 'Времето на важност веќе е поминато.',
	'stabilize_denied' => 'Пристапот е забранет.',
	'stabilize-expiring' => 'истекува $1 (UTC)',
	'stabilization-review' => 'Обележи ја тековната верзија како проверена',
);

/** Malayalam (മലയാളം)
 * @author Praveenp
 * @author Shijualex
 */
$messages['ml'] = array(
	'stabilization-tab' => 'സ്ഥിരത',
	'stabilization' => 'താളിന്റെ സ്ഥിരീകരണം',
	'stabilization-perm' => 'പ്രസിദ്ധീകരിക്കപ്പെട്ട പതിപ്പിന്റെ ക്രമീകരണം മാറ്റുന്നതിനുള്ള അവകാശം താങ്കളുടെ അംഗത്വത്തിനില്ല. [[:$1|$1]]ന്റെ നിലവിലുള്ള ക്രമീകരണം ഇവിടെ കാണാം:',
	'stabilization-page' => 'താളിന്റെ പേര്‌:',
	'stabilization-leg' => 'പ്രസിദ്ധീകരിക്കപ്പെട്ട പതിപ്പിന്റെ ക്രമീകരണങ്ങൾ സ്ഥിരീകരിക്കുക',
	'stabilization-select' => 'പ്രസിദ്ധീകരിച്ച പതിപ്പിന്റെ തിരഞ്ഞെടുക്കൽ മുൻഗണന',
	'stabilization-select1' => 'ഒടുവിലത്തെ ഗുണനിലവാരമുള്ള നാൾപ്പതിപ്പ്, പിന്നീട് ഒടുവിൽ ദർശിച്ച പതിപ്പ്',
	'stabilization-select2' => 'അവസാനം പരിശോധിക്കപ്പെട്ട നാൾപ്പതിപ്പ്',
	'stabilization-def' => 'താളിന്റെ സ്വതവേയുള്ള നിലയിൽ പ്രദർശിപ്പിക്കുന്ന പതിപ്പ്',
	'stabilization-def1' => 'പ്രസിദ്ധീകരിക്കപ്പെട്ട പതിപ്പ്;
അതില്ലെങ്കിൽ നിലവിലുള്ള/കരട് പതിപ്പ്',
	'stabilization-def2' => 'നിലവിലുള്ള/കരട് പതിപ്പ്',
	'stabilization-restrict' => 'സംശോധന/സ്വയം-സംശോധന പരിമിതപ്പെടുത്തലുകൾ',
	'stabilization-restrict-none' => 'കൂടുതൽ പരിമിതപ്പെടുത്തലുകളില്ല',
	'stabilization-submit' => 'സ്ഥിരീകരിക്കുക',
	'stabilization-notexists' => '"[[:$1|$1]]". എന്ന ഒരു താൾ നിലവിലില്ല. ക്രമീകരണങ്ങൾ നടത്തുന്നതിനു സാദ്ധ്യമല്ല.',
	'stabilization-notcontent' => '"[[:$1|$1]]" എന്ന താൾ സം‌ശോധനം ചെയ്യുന്നതിനു സാദ്ധ്യമല്ല. ക്രമീകരണം അനുവദനീയമല്ല.',
	'stabilization-comment' => 'കാരണം:',
	'stabilization-otherreason' => 'മറ്റു കാരണം:',
	'stabilization-expiry' => 'കാലാവധി:',
	'stabilization-othertime' => 'മറ്റ് കാലയളവ്:',
	'stabilization-sel-short' => 'മുൻഗണന',
	'stabilization-sel-short-0' => 'ഉന്നത നിലവാരം',
	'stabilization-sel-short-1' => 'ഒന്നുമില്ല',
	'stabilization-def-short' => 'സ്വതവെ',
	'stabilization-def-short-0' => 'നിലവിലുള്ളത്',
	'stabilization-def-short-1' => 'പ്രസിദ്ധീകരിക്കപ്പെട്ടത്',
	'stabilize_page_invalid' => 'താളിനു ലക്ഷ്യമിട്ട പേര് അസാധുവാണ്.',
	'stabilize_page_notexists' => 'ലക്ഷ്യമിട്ട താൾ നിലവിലില്ല.',
	'stabilize_page_unreviewable' => 'ലക്ഷ്യമിട്ട താൾ സംശോധനം ചെയ്യാവുന്ന നാമമേഖലയിലല്ല.',
	'stabilize_invalid_autoreview' => 'അസാധുവായ സ്വയംസംശോധന പരിമിതപ്പെടുത്തൽ',
	'stabilize_invalid_level' => 'അസാധുവായ സംരക്ഷണ മാനം.',
	'stabilize_expiry_invalid' => 'അസാധുവായ കാലാവധി തീയതി.',
	'stabilize_expiry_old' => 'ഈ കാലാവധി സമയം കഴിഞ്ഞു പോയി.',
	'stabilize-expiring' => 'കാലാവധി തീരുന്നത് - $1 (UTC)',
	'stabilization-review' => 'ഇപ്പോഴുള്ള പതിപ്പ് പരിശോധിച്ചതായി അടയാളപ്പെടുത്തുക',
);

/** Marathi (मराठी)
 * @author Kaustubh
 * @author Mahitgar
 */
$messages['mr'] = array(
	'stabilization-tab' => 'व्हेट',
	'stabilization' => 'पान स्थिर करा',
	'stabilization-text' => "'''[[:$1|$1]] ची स्थिर आवृत्ती कशा प्रकारे निवडली अथवा दाखविली जाईल या साठी खालील सेटिंग बदला.'''",
	'stabilization-perm' => 'तुम्हाला स्थिर आवृत्ती बदलण्याची परवानगी नाही.
[[:$1|$1]]चे सध्याचे सेटींग खालीलप्रमाणे:',
	'stabilization-page' => 'पृष्ठ नाव:',
	'stabilization-leg' => 'स्थिर आवृत्ती सेटिंग निश्चित करा',
	'stabilization-select' => 'स्थिर आवृत्तीची निवड',
	'stabilization-select1' => 'नवीनतम गुणवत्तापूर्ण आवृत्ती;
जर उपलब्ध नसेल, तर नवीनतम निवडलेली आवृत्ती',
	'stabilization-select2' => 'नवीनतम तपासलेली आवृत्ती',
	'stabilization-select3' => 'नवीनतम सर्वोत्कृष्ठ आवृत्ती; जर उपलब्ध नसेल, तर नवीनतम गुणवत्तापूर्ण किंवा निवडलेली',
	'stabilization-def' => 'मूळ प्रकारे पानावर बदल दाखविलेले आहेत',
	'stabilization-def1' => 'स्थिर आवृत्ती;
जर उपलब्ध नसेल, तर सध्याची',
	'stabilization-def2' => 'सध्याची आवृत्ती',
	'stabilization-submit' => 'सहमती द्या',
	'stabilization-notexists' => '"[[:$1|$1]]" या नावाचे पृष्ठ अस्तित्वात नाही.
बदल करू शकत नाही.',
	'stabilization-notcontent' => '"[[:$1|$1]]" हे पान तपासू शकत नाही.
बदल करता येणार नाहीत.',
	'stabilization-comment' => 'शेरा:',
	'stabilization-expiry' => 'रद्द होते:',
	'stabilization-sel-short' => 'अनुक्रम',
	'stabilization-sel-short-0' => 'दर्जा',
	'stabilization-sel-short-1' => 'काहीही नाही',
	'stabilization-sel-short-2' => 'सर्वोत्कृष्ठ',
	'stabilization-def-short' => 'मूळ (अविचल)',
	'stabilization-def-short-0' => 'सद्य',
	'stabilization-def-short-1' => 'स्थीर',
	'stabilize_expiry_invalid' => 'चुकीचा रद्दीकरण दिनांक.',
	'stabilize_expiry_old' => 'ही रद्दीकरण वेळ उलटून गेलेली आहे.',
	'stabilize-expiring' => '$1 (UTC) ला रद्द होते',
);

/** Malay (Bahasa Melayu)
 * @author Aurora
 * @author Aviator
 * @author Kurniasan
 */
$messages['ms'] = array(
	'stabilization-tab' => 'periksa',
	'stabilization' => 'Penstabilan laman',
	'stabilization-text' => "'''Ubah tetapan di bawah untuk mengawal bagaimana versi stabil bagi [[:$1|$1]] dipilih dan dipaparkan.'''",
	'stabilization-perm' => 'Anda tidak mempunyai keizinan untuk mengubah tetapan versi stabil ini.
Yang berikut ialah tetapan bagi [[:$1|$1]]:',
	'stabilization-page' => 'Nama laman:',
	'stabilization-leg' => 'Sahkan tetapan versi stabil',
	'stabilization-select' => 'Pemilihan versi stabil',
	'stabilization-select1' => 'Semakan bermutu terakhir; jika tiada, semakan dijenguk terakhir',
	'stabilization-select2' => 'Semakan diperiksa terakhir',
	'stabilization-select3' => 'Semakan asli terakhir; jika tiada, semakan bermutu atau semakan dijenguk terakhir',
	'stabilization-def' => 'Semakan yang dipaparkan ketika lalai',
	'stabilization-def1' => 'Semakan stabil; jika tiada, semakan semasa',
	'stabilization-def2' => 'Semakan semasa',
	'stabilization-restrict' => 'Swa-semak pengehadan',
	'stabilization-restrict-none' => 'Tiada pengehadan tambahan',
	'stabilization-submit' => 'Sahkan',
	'stabilization-notexists' => 'Tiada laman dengan nama "[[:$1|$1]]".
Tetapan tidak boleh dibuat.',
	'stabilization-notcontent' => 'Laman "[[:$1|$1]]" tidak boleh diperiksa.
Tetapan tidak boleh dibuat.',
	'stabilization-comment' => 'Alasan:',
	'stabilization-otherreason' => 'Sebab lain:',
	'stabilization-expiry' => 'Tamat pada:',
	'stabilization-othertime' => 'Waktu lain',
	'stabilization-sel-short' => 'Keutamaan',
	'stabilization-sel-short-0' => 'Mutu',
	'stabilization-sel-short-1' => 'Tiada',
	'stabilization-sel-short-2' => 'Asli',
	'stabilization-def-short' => 'Lalai',
	'stabilization-def-short-0' => 'Semasa',
	'stabilization-def-short-1' => 'Stabil',
	'stabilize_expiry_invalid' => 'Tarikh tamat tidak sah.',
	'stabilize_expiry_old' => 'Waktu tamat telah pun berlalu.',
	'stabilize-expiring' => 'tamat pada $1 (UTC)',
	'stabilization-review' => 'Semak versi terkini',
);

/** Erzya (Эрзянь)
 * @author Amdf
 * @author Botuzhaleny-sodamo
 */
$messages['myv'] = array(
	'stabilization-page' => 'Лопань лем:',
	'stabilization-submit' => 'Кемекстамс',
);

/** Nahuatl (Nāhuatl)
 * @author Fluence
 */
$messages['nah'] = array(
	'stabilization-page' => 'Zāzanilli ītōcā:',
	'stabilization-def2' => 'Āxcān in tlachiyaliztli',
	'stabilization-expiry' => 'Tlamiliztli:',
	'stabilization-sel-short-0' => 'Cuallōtl',
	'stabilization-sel-short-1' => 'Ahtlein',
	'stabilization-def-short' => 'Ic default',
	'stabilization-def-short-0' => 'Āxcān',
	'stabilize-expiring' => 'motlamīz $1 (UTC)',
);

/** Low German (Plattdüütsch)
 * @author Slomox
 */
$messages['nds'] = array(
	'stabilization-page' => 'Siedennaam:',
	'stabilization-comment' => 'Grund:',
	'stabilization-expiry' => 'Löppt ut:',
	'stabilization-sel-short-0' => 'Qualität',
	'stabilization-sel-short-1' => 'Keen',
	'stabilize-expiring' => 'löppt $1 ut (UTC)',
);

/** Dutch (Nederlands)
 * @author SPQRobin
 * @author Siebrand
 */
$messages['nl'] = array(
	'stabilization-tab' => '(er)',
	'stabilization' => 'Paginastabilisatie',
	'stabilization-text' => "'''Wijzig de onderstaande instellingen om aan te passen hoe de gepubliceerde versie van [[:\$1|\$1]] geselecteerd en weergegeven wordt.'''

'''Let op:''' het wijzigen van de '''gepubliceerde versieselectie''' in \"kwaliteitsversie\" of \"ongerepte versie\" heeft geen gevolgen als die versies niet bestaan.
Een kwaliteitsversie is ook een gecontroleerde versie, enzovoorts.",
	'stabilization-perm' => 'U hebt geen rechten om de instellingen voor de gepubliceerde versie te wijzigen.
Dit zijn de huidige instellingen voor [[:$1|$1]]:',
	'stabilization-page' => 'Paginanaam:',
	'stabilization-leg' => 'Instellingen gepubliceerde versie bevestigen',
	'stabilization-select' => 'Voorkeuren gepubliceerde versieselectie',
	'stabilization-select1' => 'De laatste kwaliteitsversie;
als die er niet is, dan de laatste gecontroleerde versie',
	'stabilization-select2' => 'Laatste gecontroleerde versie',
	'stabilization-select3' => 'De laatste ongerepte versie.
Als deze niet beschikbaar is, dan de laatste kwaliteitsversie of gecontroleerde versie',
	'stabilization-def' => 'Versie die standaard weergegeven wordt',
	'stabilization-def1' => 'De gepubliceerde versie;
als die er niet is, dan de huidige/werkversie',
	'stabilization-def2' => 'De huidige/werkversie',
	'stabilization-restrict' => 'Beperkingen op (automatisch) gecontroleerd markeren',
	'stabilization-restrict-none' => 'Geen additionele beperkingen',
	'stabilization-submit' => 'Bevestigen',
	'stabilization-notexists' => 'Er is geen pagina "[[:$1|$1]]".
Instellen is niet mogelijk.',
	'stabilization-notcontent' => 'U kunt de pagina "[[:$1|$1]]" niet controleren.
Instellen is niet mogelijk.',
	'stabilization-comment' => 'Reden:',
	'stabilization-otherreason' => 'Andere reden:',
	'stabilization-expiry' => 'Vervallen:',
	'stabilization-othertime' => 'Andere tijd:',
	'stabilization-sel-short' => 'Voorrang',
	'stabilization-sel-short-0' => 'Kwaliteit',
	'stabilization-sel-short-1' => 'Geen',
	'stabilization-sel-short-2' => 'Ongerept',
	'stabilization-def-short' => 'Standaard',
	'stabilization-def-short-0' => 'Huidig',
	'stabilization-def-short-1' => 'Gepubliceerd',
	'stabilize_page_invalid' => 'De naam van de doelpagina is ongeldig.',
	'stabilize_page_notexists' => 'De doelpagina bestaat niet.',
	'stabilize_page_unreviewable' => 'De doelpagina is bevindt zich niet in een te controleren naamruimte.',
	'stabilize_invalid_precedence' => 'Ongeldig versievoorvoegsel.',
	'stabilize_invalid_autoreview' => 'Ongeldige beperking voor automatische controle',
	'stabilize_invalid_level' => 'Ongeldig beschermingsniveau.',
	'stabilize_expiry_invalid' => 'Ongeldige vervaldatum.',
	'stabilize_expiry_old' => 'Deze vervaldatum is al verstreken.',
	'stabilize_denied' => 'Geen toegang.',
	'stabilize-expiring' => 'vervalt $1 (UTC)',
	'stabilization-review' => 'Huidige versie als gecontroleerd markeren',
);

/** Norwegian Nynorsk (‪Norsk (nynorsk)‬)
 * @author Gunnernett
 * @author Harald Khan
 * @author Jon Harald Søby
 */
$messages['nn'] = array(
	'stabilization-tab' => 'kvalitet',
	'stabilization' => 'Sidestabilisering',
	'stabilization-text' => "'''Endra innstillingane nedanfor for å velja korleis den stabile versjonen av [[:$1|$1]] skal verta vald og synt.'''

Når ein endrar oppsettet for ''valet av stabil versjon'' slik at det nyttar «{{int:revreview-lev-quality}}» eller «{{int:revreview-lev-pristine}}» som standard,
må ein gjera seg viss om at sida har slike versjonar, elles vil endringa ha liten verknad.",
	'stabilization-perm' => 'Brukarkontoen din har ikkje løyve til å endra innstillingane for stabile versjonar.
Her er dei noverande innstillingane for [[:$1|$1]]:',
	'stabilization-page' => 'Sidenamn:',
	'stabilization-leg' => 'Stadfest innstillingane for stabile versjonar',
	'stabilization-select' => 'Val av stabil versjon',
	'stabilization-select1' => 'Den siste kvalitetsversjonen om han finst; om ikkje, den siste vurderte versjonen',
	'stabilization-select2' => 'Den siste vurderte versjonen, same kva kvalitetsnivå.',
	'stabilization-select3' => 'Den siste urørde versjonen av sida. Om han ikkje finst, ta den siste kvalitetsversjonen eller den siste vurderte versjonen',
	'stabilization-def' => 'Sideversjonen som skal verta nytta som standardvising',
	'stabilization-def1' => 'Den stabile versjonen om han finst; om ikkje, den siste versjonen',
	'stabilization-def2' => 'Den siste versjonen',
	'stabilization-restrict' => 'Avgrensing på automelding',
	'stabilization-restrict-none' => 'Ingen ekstra avgrensingar',
	'stabilization-submit' => 'Stadfest',
	'stabilization-notexists' => 'Det finst inga sida med tittelen «[[:$1|$1]]».
Ingen innstillingar kan verta gjort.',
	'stabilization-notcontent' => 'Sida «[[:$1|$1]]» kan ikkje verta vurdert.
Ingen innstillingar kan verta gjorde.',
	'stabilization-comment' => 'Årsak:',
	'stabilization-otherreason' => 'Anna årsak',
	'stabilization-expiry' => 'Endar:',
	'stabilization-othertime' => 'Anna tid',
	'stabilization-sel-short' => 'Prioritet',
	'stabilization-sel-short-0' => 'Kvalitet',
	'stabilization-sel-short-1' => 'Ingen',
	'stabilization-sel-short-2' => 'Urørd',
	'stabilization-def-short' => '(standard)',
	'stabilization-def-short-0' => 'Noverande',
	'stabilization-def-short-1' => 'Stabil',
	'stabilize_expiry_invalid' => 'Ugyldig sluttdato.',
	'stabilize_expiry_old' => 'Sluttdatoen har alt vore.',
	'stabilize-expiring' => 'endar $1 (UTC)',
	'stabilization-review' => 'Vurder den noverande versjonen',
);

/** Norwegian (bokmål)‬ (‪Norsk (bokmål)‬)
 * @author H92
 * @author Jon Harald Søby
 * @author Nghtwlkr
 */
$messages['no'] = array(
	'stabilization-tab' => 'kvalitet',
	'stabilization' => 'Sidestabilisering',
	'stabilization-text' => "'''Endre innstillingene nedenfor for å bestemme hvordan den publiserte versjonen av [[:$1|$1]] skal velges og vises.'''

'''Merk:''' å endre ''valg av publisert versjon'' til å foretrekke «kvalitets»- eller «urørt»-versjoner vil ikke ha noen effekt om slike ikke finnes. Merk også at en «kvalitets»-versjon anses som en «kontrollert» versjon og så videre.",
	'stabilization-perm' => 'Din brukerkonto har ikke tillatelse til å endre innstillinger for publiserte versjoner.
Her er de nåværende innstillingene for [[:$1|$1]]:',
	'stabilization-page' => 'Sidenavn:',
	'stabilization-leg' => 'Bekreft innstillinger for publiserte versjoner',
	'stabilization-select' => 'Valg av publisert versjon har forrang',
	'stabilization-select1' => 'Den siste kvalitetsrevisjonen hvis den finnes, ellers den siste synede versjonen',
	'stabilization-select2' => 'Siste kontrollerte revisjon',
	'stabilization-select3' => 'Den siste urørte versjonen av denne siden; om det ikke finnes, det siste kvalitetsversjonen eller den siste sjekkede versjonen',
	'stabilization-def' => 'Sideversjonen som skal brukes som standardvisning',
	'stabilization-def1' => 'Den publiserte revisjonen; om den ikke finnes, utkast eller siste revisjon',
	'stabilization-def2' => 'Utkast eller siste versjon',
	'stabilization-restrict' => 'Begrensninger av revidering/auto-revidering',
	'stabilization-restrict-none' => 'Ingen ekstra begrensinger',
	'stabilization-submit' => 'Bekreft',
	'stabilization-notexists' => 'Det er ingen side med tittelen «[[:$1|$1]]». Ingen innstillinger kan gjøres.',
	'stabilization-notcontent' => 'Siden «[[:$1|$1]]» kan ikke bli undersøkt. Ingen innstillinger kan gjøres.',
	'stabilization-comment' => 'Årsak:',
	'stabilization-otherreason' => 'Annen årsak:',
	'stabilization-expiry' => 'Utgår:',
	'stabilization-othertime' => 'Annen tid:',
	'stabilization-sel-short' => 'Presedens',
	'stabilization-sel-short-0' => 'Kvalitet',
	'stabilization-sel-short-1' => 'Ingen',
	'stabilization-sel-short-2' => 'Urørt',
	'stabilization-def-short' => 'Standard',
	'stabilization-def-short-0' => 'Nåværende',
	'stabilization-def-short-1' => 'Publisert',
	'stabilize_page_invalid' => 'Målsidetittelen er ugyldig.',
	'stabilize_page_notexists' => 'Målsiden finnes ikke.',
	'stabilize_page_unreviewable' => 'Målsiden er ikke i et reviderbart navnerom.',
	'stabilize_invalid_precedence' => 'Ugyldig versjonsforrang.',
	'stabilize_invalid_autoreview' => 'Ugyldig autorevideringsbegrensning',
	'stabilize_invalid_level' => 'Ugyldig beskyttelsesnivå.',
	'stabilize_expiry_invalid' => 'Ugyldig varighet.',
	'stabilize_expiry_old' => 'Varigheten har allerede utløpt.',
	'stabilize-expiring' => 'utgår $1 (UTC)',
	'stabilization-review' => 'Merk den nåværende revisjonen som kontrollert',
);

/** Novial (Novial)
 * @author Malafaya
 */
$messages['nov'] = array(
	'stabilization-comment' => 'Resone:',
);

/** Northern Sotho (Sesotho sa Leboa)
 * @author Mohau
 */
$messages['nso'] = array(
	'stabilization-page' => 'Leina la letlakala:',
	'stabilize-expiring' => 'fetatšatši $1 (UTC)',
);

/** Occitan (Occitan)
 * @author Cedric31
 * @author ChrisPtDe
 * @author Juanpabl
 */
$messages['oc'] = array(
	'stabilization-tab' => '(qa)',
	'stabilization' => 'Estabilizacion de la pagina',
	'stabilization-text' => "'''Modificatz los paramètres çaijós per definir lo biais dont la version establa de [[:$1|$1]] es seleccionada e afichada.'''

Quand configuratz la ''seleccion de la version publicada'' per utilizar las revisions « de qualitat » o « inicialas » per defaut, asseguratz-vos qu'i a efièchament de talas revisions dins la pagina, siquenon las modificacions auràn pas d'incidéncia.",
	'stabilization-perm' => 'Vòstre compte a pas los dreches per cambiar los paramètres de la version publicada.
Aquí los paramètres actuals de [[:$1|$1]] :',
	'stabilization-page' => 'Nom de la pagina :',
	'stabilization-leg' => 'Confirmar lo parametratge de la version publicada',
	'stabilization-select' => 'Proprietat de seleccion de la version publicada',
	'stabilization-select1' => 'La darrièra version de qualitat, siquenon la darrièra version vista',
	'stabilization-select2' => 'La darrièra revision revisada, sens téner compte del nivèl de validacion',
	'stabilization-select3' => 'La version mai anciana ; en cas d’abséncia, la darrièra de qualitat o visada.',
	'stabilization-def' => "Version afichada al moment de l'afichatge per defaut de la pagina",
	'stabilization-def1' => "La revision publicada ; se'n i a pas, alara la correnta o lo borrolhon en cors",
	'stabilization-def2' => 'La revision correnta o lo borrolhon en cors',
	'stabilization-restrict' => 'Tornar veire automaticament las restriccions',
	'stabilization-restrict-none' => 'Pas de restriccion suplementària',
	'stabilization-submit' => 'Confirmar',
	'stabilization-notexists' => 'I a pas de pagina « [[:$1|$1]] », pas de parametratge possible',
	'stabilization-notcontent' => 'La pagina « [[:$1|$1]] » pòt pas èsser revisada, pas de parametratge possible',
	'stabilization-comment' => 'Rason :',
	'stabilization-otherreason' => 'Autra rason :',
	'stabilization-expiry' => 'Expira :',
	'stabilization-othertime' => 'Autre temps :',
	'stabilization-sel-short' => 'Prioritat',
	'stabilization-sel-short-0' => 'Qualitat',
	'stabilization-sel-short-1' => 'Nula',
	'stabilization-sel-short-2' => 'Primitiva',
	'stabilization-def-short' => 'Defaut',
	'stabilization-def-short-0' => 'Correnta',
	'stabilization-def-short-1' => 'Publicada',
	'stabilize_expiry_invalid' => "Data d'expiracion invalida.",
	'stabilize_expiry_old' => "Lo temps d'expiracion ja es passat.",
	'stabilize-expiring' => 'expira $1 (UTC)',
	'stabilization-review' => 'Tornar veire la version correnta',
);

/** Ossetic (Иронау)
 * @author Amikeco
 */
$messages['os'] = array(
	'stabilization-sel-short-1' => 'Нæй',
);

/** Deitsch (Deitsch)
 * @author Xqt
 */
$messages['pdc'] = array(
	'stabilization-page' => 'Naame vum Blatt:',
	'stabilization-comment' => 'Grund:',
	'stabilization-otherreason' => 'Annerer Grund:',
	'stabilization-sel-short-1' => 'ken',
);

/** Polish (Polski)
 * @author Derbeth
 * @author Leinad
 * @author McMonster
 * @author Saper
 * @author Sp5uhe
 * @author ToSter
 */
$messages['pl'] = array(
	'stabilization-tab' => 'Widoczne wersje strony',
	'stabilization' => 'Widoczna wersja strony',
	'stabilization-text' => "'''Ustaw poniżej, w jaki sposób ma być wybierana i wyświetlana opublikowana wersja strony [[:$1|$1]].'''

'''Uwaga''' Po zmianie sposobu ''wyboru wersji opublikowanej'', aby preferowała domyślnie wersję „zweryfikowaną” lub „sprzed zmian” należy się upewnić, że strona posiada tego typu wersje, w przeciwnym wypadku zmiana nie da żadnego efektu. Zauważ, że wersja „zweryfikowana” jest również uznawana za „oznaczoną” itd.",
	'stabilization-perm' => 'Nie masz wystarczających uprawnień, aby zmienić konfigurację wersji opublikowanej.
Aktualne ustawienia dla strony [[:$1|$1]]:',
	'stabilization-page' => 'Nazwa strony:',
	'stabilization-leg' => 'Zatwierdź konfigurację wersji opublikowanej',
	'stabilization-select' => 'Pierwszeństwo wyboru wersji opublikowanej',
	'stabilization-select1' => 'Ostatnia wersja zweryfikowana, a jeśli nie istnieje, to ostatnia wersja przejrzana',
	'stabilization-select2' => 'Ostatnia wersja oznaczona',
	'stabilization-select3' => 'Ostatnia nienaruszona wersja, a jeśli nie istnieje, to ostatnia wersja zweryfikowana lub przejrzana',
	'stabilization-def' => 'Wersja strony wyświetlana domyślnie',
	'stabilization-def1' => 'Wersja opublikowana, a jeśli nie istnieje, to wersja bieżąca lub robocza',
	'stabilization-def2' => 'Wersja bieżąca lub robocza',
	'stabilization-restrict' => 'Ograniczenia ręcznego i automatycznego przeglądania',
	'stabilization-restrict-none' => 'Brak dodatkowych ograniczeń',
	'stabilization-submit' => 'Potwierdź',
	'stabilization-notexists' => 'Brak strony zatytułowanej „[[:$1|$1]]”. Nie jest możliwa jej konfiguracja.',
	'stabilization-notcontent' => 'Strona „[[:$1|$1]]” nie może być oznaczona.
Nie jest możliwa jej konfiguracja.',
	'stabilization-comment' => 'Powód',
	'stabilization-otherreason' => 'Inny powód',
	'stabilization-expiry' => 'Upływa',
	'stabilization-othertime' => 'Inny okres',
	'stabilization-sel-short' => 'Kolejność',
	'stabilization-sel-short-0' => 'Zweryfikowana',
	'stabilization-sel-short-1' => 'Brak',
	'stabilization-sel-short-2' => 'Nienaruszona',
	'stabilization-def-short' => 'Domyślna',
	'stabilization-def-short-0' => 'Bieżąca',
	'stabilization-def-short-1' => 'Opublikowana',
	'stabilize_expiry_invalid' => 'Nieprawidłowa data wygaśnięcia.',
	'stabilize_expiry_old' => 'Czas wygaśnięcia już upłynął.',
	'stabilize-expiring' => 'wygasa $1 (UTC)',
	'stabilization-review' => 'Oznacz jako przejrzaną aktualną wersję',
);

/** Piedmontese (Piemontèis)
 * @author Borichèt
 * @author Bèrto 'd Sèra
 * @author Dragonòt
 */
$messages['pms'] = array(
	'stabilization-tab' => '(c.q.)',
	'stabilization' => 'Stabilisassion dla pàgina',
	'stabilization-text' => "'''Cangé le regolassion ambelessì sota për rangé coma la version publicà ëd [[:\$1|\$1]] a deva esse sernùa e smonùa.'''

'''Nòta:''' an cangiand la ''selession ëd version publicà'' për avèj pi car le revision ëd \"qualità\" o \"inissiaj\", a l'avrà pa efet se a-i son pa cole version. Ch'a nòta ëdcò che na version ëd \"qualità\" a l'é ëdcò considerà na version \"controlà\" e via fòrt.",
	'stabilization-perm' => "Sò cont a l'ha pa ij përmess për cangé la configurassion ëd la version publicà. 
Ambelessì a-i son le regolassion corente për [[:$1|$1]]:",
	'stabilization-page' => 'Nòm dla pàgina:',
	'stabilization-leg' => "Conferma j'ampostassion ëd la version publicà",
	'stabilization-select' => 'Precedensa ëd selession ëd la version publicà',
	'stabilization-select1' => "Ùltima revision ëd qualità; s'a-i é nen cola, pijé l'ùltima controlà",
	'stabilization-select2' => 'Ùltima revision controlà',
	'stabilization-select3' => "Ùltima vërsion imacolà; peui l'ùltima ëd qualità; peui l'ultima vista",
	'stabilization-def' => 'Revision da smon-e coma pàgina sòlita për la vos',
	'stabilization-def1' => "La version publicà; s'a-i é pa, antlora cola corenta/sbòss",
	'stabilization-def2' => 'La revision/sbòss corent',
	'stabilization-restrict' => 'Restrission ëd revision/àuto-revision',
	'stabilization-restrict-none' => 'Pa gnun-e restrission extra',
	'stabilization-submit' => 'Confermé',
	'stabilization-notexists' => 'A-i é pa gnun-a pàgina ch\'as ciama "[[:$1|$1]]". As peul nen regolé lòn ch\'a-i é nen.',
	'stabilization-notcontent' => 'La pàgina "[[:$1|$1]]" as peul pa s-ciairesse. A-i é gnun-a regolassion ch\'as peula fesse.',
	'stabilization-comment' => 'Rason:',
	'stabilization-otherreason' => 'Autra rason:',
	'stabilization-expiry' => 'A finiss:',
	'stabilization-othertime' => 'Autra vira:',
	'stabilization-sel-short' => 'Precedensa',
	'stabilization-sel-short-0' => 'Qualità',
	'stabilization-sel-short-1' => 'Gnun-a',
	'stabilization-sel-short-2' => 'Ancontaminà',
	'stabilization-def-short' => 'Për sòlit',
	'stabilization-def-short-0' => 'version corenta',
	'stabilization-def-short-1' => 'Publicà',
	'stabilize_expiry_invalid' => 'Data fin pa bon-a.',
	'stabilize_expiry_old' => "Sta data fin-sì a l'é già passà",
	'stabilize-expiring' => 'A finiss $1 (UTC)',
	'stabilization-review' => 'Marché la version corenta com controlà',
);

/** Pashto (پښتو)
 * @author Ahmed-Najib-Biabani-Ibrahimkhel
 */
$messages['ps'] = array(
	'stabilization-page' => 'د مخ نوم:',
	'stabilization-comment' => 'سبب:',
	'stabilization-otherreason' => 'بل سبب:',
	'stabilization-othertime' => 'بل وخت:',
	'stabilization-sel-short-1' => 'هېڅ',
	'stabilization-def-short' => 'تلواليز',
	'stabilization-def-short-0' => 'اوسنی',
	'stabilize-expiring' => 'په $1 (UTC) پای ته رسېږي',
);

/** Portuguese (Português)
 * @author 555
 * @author Hamilton Abreu
 * @author Malafaya
 * @author Waldir
 */
$messages['pt'] = array(
	'stabilization-tab' => 'cgq',
	'stabilization' => 'Estabilização de páginas',
	'stabilization-text' => "'''Altere os parâmetros abaixo para ajustar a forma como a versão publicada de [[:\$1|\$1]] é seleccionada e apresentada.'''

'''Nota:''' Alterar a ''precedência na selecção da versão publicada'' para as versões \"qualidade\" ou \"impecável\" não surte efeito se essas versões não existirem. Note também que uma versão \"qualidade\" é também considerada uma versão verificada.",
	'stabilization-perm' => 'A sua conta não tem permissão para alterar a configuração da versão publicada.
Os parâmetros actuais da página [[:$1|$1]] são:',
	'stabilization-page' => 'Nome da página:',
	'stabilization-leg' => 'Confirmar os parâmetros da versão publicada',
	'stabilization-select' => 'Precedência na selecção da versão publicada',
	'stabilization-select1' => 'A última edição de qualidade; depois, a última edição vista',
	'stabilization-select2' => 'A última edição verificada',
	'stabilization-select3' => 'A última edição impecável; depois, a última de qualidade; finalmente, a última vista',
	'stabilization-def' => 'Edição apresentada por omissão',
	'stabilization-def1' => 'A edição publicada; se inexistente, então a edição ou rascunho mais recente',
	'stabilization-def2' => 'A edição ou rascunho actual',
	'stabilization-restrict' => 'Restrições da revisão automática',
	'stabilization-restrict-none' => 'Nenhuma restrição extra',
	'stabilization-submit' => 'Confirmar',
	'stabilization-notexists' => 'A página "[[:$1|$1]]" não existe.
Não é possível configurá-la.',
	'stabilization-notcontent' => 'A página "[[:$1|$1]]" não pode ser revista.
Não é possível configurá-la.',
	'stabilization-comment' => 'Motivo:',
	'stabilization-otherreason' => 'Outro motivo:',
	'stabilization-expiry' => 'Expira:',
	'stabilization-othertime' => 'Outra hora:',
	'stabilization-sel-short' => 'Precedência',
	'stabilization-sel-short-0' => 'Qualidade',
	'stabilization-sel-short-1' => 'Nenhum',
	'stabilization-sel-short-2' => 'Impecável',
	'stabilization-def-short' => 'Padrão',
	'stabilization-def-short-0' => 'Actual',
	'stabilization-def-short-1' => 'Publicada',
	'stabilize_page_invalid' => 'O título da página de destino é inválido.',
	'stabilize_page_notexists' => 'A página de destino não existe.',
	'stabilize_page_unreviewable' => 'A página de destino não está num espaço nominal sujeito a revisão.',
	'stabilize_invalid_precedence' => 'Precedência de versões inválida.',
	'stabilize_invalid_autoreview' => 'Restrição de auto-revisão é inválida',
	'stabilize_invalid_level' => 'Nível de protecção é inválido.',
	'stabilize_expiry_invalid' => 'Data de expiração inválida.',
	'stabilize_expiry_old' => 'Esta data de expiração já passou.',
	'stabilize_denied' => 'Permissão negada.',
	'stabilize-expiring' => 'expira às $1 (UTC)',
	'stabilization-review' => 'Marcar a edição actual como verificada',
);

/** Brazilian Portuguese (Português do Brasil)
 * @author Eduardo.mps
 * @author Luckas Blade
 */
$messages['pt-br'] = array(
	'stabilization-tab' => 'cgq',
	'stabilization' => 'Configurações da Garantia de Qualidade',
	'stabilization-text' => "'''Altere a seguir as configurações de como a versão estável de [[:\$1|\$1]] é selecionada e exibida.'''

Ao mudar a configuração de ''seleção de versão estável'' para utilizar revisões \"confiáveis\" ou \"intocadas\" por padrão, tenha certeza de checar se de fato existem revisões assim na página, pois de outra maneira a mudança terá pouco efeito.",
	'stabilization-perm' => 'Sua conta não possui permissão para alterar as configurações de edições estáveis.
Seguem-se as configurações para [[:$1|$1]]:',
	'stabilization-page' => 'Nome da página:',
	'stabilization-leg' => 'Confirmar a configuração da edição estável',
	'stabilization-select' => 'Seleção da edição estável',
	'stabilization-select1' => 'A última edição analisada como confiável;  
se inexistente, a mais recentemente analisada',
	'stabilization-select2' => 'A revisão mais recentemente analisada, independente do nível de validação',
	'stabilization-select3' => 'A última revisão intocada; se não estiver presente, então a última de qualidade ou analisada',
	'stabilization-def' => 'Edição exibida na visualização padrão de página',
	'stabilization-def1' => 'A edição estável;  
se inexistente, exibir a edição atual',
	'stabilization-def2' => 'A edição atual',
	'stabilization-restrict' => 'Auto-analisar restrições',
	'stabilization-restrict-none' => 'sem restrições extras',
	'stabilization-submit' => 'Confirmar',
	'stabilization-notexists' => 'A página "[[:$1|$1]]" não existe.
Não é possível configurá-la.',
	'stabilization-notcontent' => 'A página "[[:$1|$1]]" não pode ser analisada.
Não é possível configurá-la.',
	'stabilization-comment' => 'Motivo:',
	'stabilization-otherreason' => 'Outro motivo:',
	'stabilization-expiry' => 'Expira em:',
	'stabilization-othertime' => 'Outro tempo',
	'stabilization-sel-short' => 'Precedência',
	'stabilization-sel-short-0' => 'Qualidade',
	'stabilization-sel-short-1' => 'Nenhum',
	'stabilization-sel-short-2' => 'Intocada',
	'stabilization-def-short' => 'Padrão',
	'stabilization-def-short-0' => 'Atual',
	'stabilization-def-short-1' => 'Estável',
	'stabilize_expiry_invalid' => 'Data de expiração inválida.',
	'stabilize_expiry_old' => 'Este tempo de expiração já se encerrou.',
	'stabilize-expiring' => 'expira às $1 (UTC)',
	'stabilization-review' => 'Analisar a versão atual',
);

/** Romanian (Română)
 * @author KlaudiuMihaila
 * @author Mihai
 * @author Stelistcristi
 */
$messages['ro'] = array(
	'stabilization-tab' => 'config.',
	'stabilization-perm' => 'Contul tău nu are permisiunea de a schimba versiunea stabilă a configurației.
Iată configurația curentă pentru [[:$1|$1]]:',
	'stabilization-page' => 'Numele paginii:',
	'stabilization-leg' => 'Confirmați setările versiunii stabile',
	'stabilization-select' => 'Precedenta selecție a versiunii stabile',
	'stabilization-def' => 'Revizie afișată pe vizualizarea paginii implicite',
	'stabilization-def1' => 'Revizia stabilă; dacă nu există, atunci cea curentă',
	'stabilization-def2' => 'Revizia curentă',
	'stabilization-restrict' => 'Restricții pentru revizualizarea automată',
	'stabilization-restrict-none' => 'Nicio restricție suplimentară',
	'stabilization-submit' => 'Confirmă',
	'stabilization-comment' => 'Motiv:',
	'stabilization-otherreason' => 'Alt motiv',
	'stabilization-expiry' => 'Expiră:',
	'stabilization-othertime' => 'Altă dată',
	'stabilization-sel-short' => 'Prioritate',
	'stabilization-sel-short-0' => 'Calitate',
	'stabilization-sel-short-1' => 'Nimic',
	'stabilization-sel-short-2' => 'Intact',
	'stabilization-def-short' => 'Implicit',
	'stabilization-def-short-0' => 'Curent',
	'stabilization-def-short-1' => 'Stabil',
	'stabilize_expiry_invalid' => 'Data expirării incorectă.',
	'stabilize_expiry_old' => 'Această dată de expirare a trecut deja.',
	'stabilize-expiring' => 'expiră $1 (UTC)',
	'stabilization-review' => 'Revizuiește versiunea curentă',
);

/** Tarandíne (Tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'stabilization-tab' => 'vet',
	'stabilization' => 'Pàgene de stabbilizzazione',
	'stabilization-text' => "'''Cange le 'mbostaziune sotte pe aggiustà cumme a 'na versiona pubblecate de [[:\$1|\$1]] ca jè selezionete e visualizzete.'''

Quanne cange 'a configurazione d'a ''seleziona d'a versiona pubblecate'' pe ausà \"qualità\" o \"repristine\" le revisiune pe default, a essere secure ca è condrollete ce stonne jndr'à quidde mumende quacche versiona jndr'à pàgene, ce no 'u cangiamende ave 'n'effette piccinne.",
	'stabilization-perm' => "'U cunde utende tue non ge tène le permesse pe cangià 'a configurazione d'a versione pubblecate.
Chiste sonde le configuraziune corrende pe [[:$1|$1]]:",
	'stabilization-page' => "Nome d'a pàgene:",
	'stabilization-leg' => 'Conferme le configuraziune pe le versiune pubblecate',
	'stabilization-select' => "Selezione d'a versiona pubblecate precedende",
	'stabilization-select1' => "L'urtema versione de qualità; ce non g'è presende, allore vide l'urtema viste",
	'stabilization-select2' => "L'urtema revisione reviste, senza 'nu levèlle de validazione",
	'stabilization-select3' => "L'urtema versione bbone; ce non g'è presende, allore vide l'urtema versione de qualità o viste",
	'stabilization-def' => "Revisiune visualizzete sus 'a viste d'a pàgene de default",
	'stabilization-def1' => "'A revisiona pubblecate; ce non g'è presende, allore vide quedda corrende/bozza",
	'stabilization-def2' => "'A revisiona corrende/bozza",
	'stabilization-restrict' => "Restriziune sus a l'auto revisitazione",
	'stabilization-restrict-none' => 'Nisciuna restriziona de cchiù',
	'stabilization-submit' => 'Conferme',
	'stabilization-notexists' => 'Non ge stè \'na pàgene ca se chieme "[[:$1|$1]]".
Nisciuna configurazione jè possibbele.',
	'stabilization-notcontent' => '\'A pàgene "[[$1|$1]]" non ge pò essere reviste.
Non ge stonne le configurazione.',
	'stabilization-comment' => 'Mutive:',
	'stabilization-otherreason' => 'Otre mutive:',
	'stabilization-expiry' => 'More:',
	'stabilization-othertime' => 'Otre orarije:',
	'stabilization-sel-short' => 'Precedenze',
	'stabilization-sel-short-0' => 'Qualità',
	'stabilization-sel-short-1' => 'Ninde',
	'stabilization-sel-short-2' => 'Bbuene proprie',
	'stabilization-def-short' => 'Defolt',
	'stabilization-def-short-0' => 'Corrende',
	'stabilization-def-short-1' => 'Pubblecate',
	'stabilize_expiry_invalid' => 'Date de scadenze errete.',
	'stabilize_expiry_old' => 'Sta date de scadenze ha già passete.',
	'stabilize-expiring' => "scade 'u $1 (UTC)",
	'stabilization-review' => "Signe 'a revisiona corrende cumme verificate",
);

/** Russian (Русский)
 * @author Claymore
 * @author Drbug
 * @author Ferrer
 * @author Putnik
 * @author Sergey kudryavtsev
 * @author Александр Сигачёв
 */
$messages['ru'] = array(
	'stabilization-tab' => '(кк)',
	'stabilization' => 'Стабилизация страницы',
	'stabilization-text' => "'''С помощью приведённых ниже настроек можно управлять выбором и отображением опубликованной версии страницы [[:$1|$1]].'''

'''Замечание.''' Установка параметра «выбор опубликованной версии» в значения «качества» или «изначальности» не окажет влияния, если такие версии отсутствуют. Примите также во внимание, что все «выверенные» версии автоматически считаются «проверенными».",
	'stabilization-perm' => 'Вашей учётной записи не достаточно полномочий, для изменения настройки опубликованных версий.
Здесь приведены текущие настройки для [[:$1|$1]]:',
	'stabilization-page' => 'Название страницы:',
	'stabilization-leg' => 'Подтверждение настроек опубликованных версии',
	'stabilization-select' => 'Порядок выбора опубликованной версии',
	'stabilization-select1' => 'Самая свежая выверенная версия; если её нет, то самая свежая из досмотренных.',
	'stabilization-select2' => 'Последняя проверенная версия',
	'stabilization-select3' => 'Последняя нетронутая версия; если нет, то последняя выверенная или досмотренная',
	'stabilization-def' => 'Версия, показываемая по умолчанию',
	'stabilization-def1' => 'Опубликованная версия; если нет, то текущая (черновая)',
	'stabilization-def2' => 'Текущая (черновая) версия',
	'stabilization-restrict' => 'Ограничения проверки/самопроверки',
	'stabilization-restrict-none' => 'Нет дополнительных ограничений',
	'stabilization-submit' => 'Подтвердить',
	'stabilization-notexists' => 'Отсутствует страница с названием «[[:$1|$1]]». Настройка невозможна.',
	'stabilization-notcontent' => 'Страница «[[:$1|$1]]» не может быть проверена. Настройка невозможна.',
	'stabilization-comment' => 'Причина:',
	'stabilization-otherreason' => 'Другая причина:',
	'stabilization-expiry' => 'Истекает:',
	'stabilization-othertime' => 'Другое время:',
	'stabilization-sel-short' => 'Порядок следования',
	'stabilization-sel-short-0' => 'выверенная',
	'stabilization-sel-short-1' => 'нет',
	'stabilization-sel-short-2' => 'безупречная',
	'stabilization-def-short' => 'по умолчанию',
	'stabilization-def-short-0' => 'текущая',
	'stabilization-def-short-1' => 'Опубликованная',
	'stabilize_page_invalid' => 'Целевое название страницы ошибочно.',
	'stabilize_page_notexists' => 'Целевой страницы не существует.',
	'stabilize_page_unreviewable' => 'Целевая страница не находится в проверяемом пространстве имён.',
	'stabilize_invalid_precedence' => 'Ошибочный приоритет версий.',
	'stabilize_invalid_autoreview' => 'Ошибочные ограничения автопроверки',
	'stabilize_invalid_level' => 'Ошибочный уровень защиты.',
	'stabilize_expiry_invalid' => 'Ошибочная дата истечения.',
	'stabilize_expiry_old' => 'Указанное время окончания действия уже прошло.',
	'stabilize_denied' => 'Доступ запрещён.',
	'stabilize-expiring' => 'истекает $1 (UTC)',
	'stabilization-review' => 'Отметить текущую версию как проверенную',
);

/** Rusyn (русиньскый язык)
 * @author Gazeb
 */
$messages['rue'] = array(
	'stabilization-page' => 'Назва сторінкы:',
	'stabilization-comment' => 'Причіна:',
	'stabilization-otherreason' => 'Інша причіна:',
	'stabilization-expiry' => 'Кінчіть:',
	'stabilization-othertime' => 'Іншый час:',
	'stabilization-sel-short-0' => 'Квалітна',
	'stabilization-sel-short-1' => 'Жадна',
	'stabilization-def-short' => 'Імпліцітне',
	'stabilization-def-short-0' => 'Актуална',
	'stabilization-def-short-1' => 'Публікована',
	'stabilize-expiring' => 'кінчіть $1 (UTC)',
);

/** Yakut (Саха тыла)
 * @author HalanTul
 */
$messages['sah'] = array(
	'stabilization-tab' => '(хх)',
	'stabilization' => 'Сирэй стабилизацията',
	'stabilization-text' => '\'\'\'Манна баар туруорууларынан [[:$1|$1]] сирэй бигэ барылын талары уонна хайдах көстүөҕүн уларытыахха сөп.\'\'\'

"Бигэ барылы талыыга"  "хаачыстыба" уонна "бастакы" диэн суолталары туруораргар сирэй оннук барыллардааҕын көр, олор суох буоллахтарына туруорууҥ сатаныа суоҕа.',
	'stabilization-perm' => 'Эн аккаунуҥ чистовой торум туруорууларын уларытар кыаҕы биэрбэт.
Манна [[:$1|$1]] билигин үлэлиир туруоруулара көстөллөр:',
	'stabilization-page' => 'Сирэй аата:',
	'stabilization-leg' => 'Сирэй халбаҥнаабат барылын туруорууларын бигэргэтии',
	'stabilization-select' => 'Халбаҥнаабат барылы хайдах талар туһунан',
	'stabilization-select1' => 'Бүтэһик бэрэбиэркэлэммит торум; суох буоллаҕына - бүтэһик көрүллүбүт.',
	'stabilization-select2' => 'Бүтэһик ырытыллыбыт сирэй (бэрэбиэркэ таһымыттан тутулуга суох)',
	'stabilization-select3' => 'Бутэһик тыытыллыбатах барыл; ол суох буоллаҕына бүтэһик бигэргэтиллибит эбэтэр көрүллүбүт.',
	'stabilization-def' => 'Анаан этиллибэтэҕинэ көрдөрүллэр торум',
	'stabilization-def1' => 'Халбаҥнаабат барыл; ол суох буоллаҕына - бүтэһик (харата)',
	'stabilization-def2' => 'Бүтэһик барыл (харата)',
	'stabilization-restrict-none' => 'Эбии хааччах суох',
	'stabilization-submit' => 'Бигэргэтии',
	'stabilization-notexists' => 'Маннык ааттаах сирэй «[[:$1|$1]]» суох. Онон уларытар кыах суох.',
	'stabilization-notcontent' => '«[[:$1|$1]]» сирэй ырытыллыбат. Онон туруорууларын уларытар сатаммат.',
	'stabilization-comment' => 'Төрүөтэ:',
	'stabilization-otherreason' => 'Атын төрүөт:',
	'stabilization-expiry' => 'Болдьоҕо бүтэр:',
	'stabilization-othertime' => 'Атын кэм:',
	'stabilization-sel-short' => 'Бэрээдэгэ',
	'stabilization-sel-short-0' => 'Үрдүк таһымнаах',
	'stabilization-sel-short-1' => 'Суох',
	'stabilization-sel-short-2' => 'Аҕа (эрдэтээҥи)',
	'stabilization-def-short' => 'Анал туруоруута суох',
	'stabilization-def-short-0' => 'Бүтэһик',
	'stabilization-def-short-1' => 'Чистовой',
	'stabilize_expiry_invalid' => 'Болдьох сыыһа туруорулунна.',
	'stabilize_expiry_old' => 'Болдьох этиллибит кэмэ номнуо ааспыт.',
	'stabilize-expiring' => 'Болдьоҕо бүтэр: $1 (UTC)',
	'stabilization-review' => 'Билиҥни барылын көрүү',
);

/** Sardinian (Sardu)
 * @author Andria
 */
$messages['sc'] = array(
	'stabilization-page' => 'Nùmene pàgina:',
	'stabilization-submit' => 'Cunfirma',
	'stabilization-comment' => 'Motivu:',
	'stabilization-otherreason' => 'Àteru motivu:',
	'stabilization-sel-short-0' => 'Calidade',
	'stabilization-sel-short-1' => 'Nudda',
);

/** Slovak (Slovenčina)
 * @author Helix84
 */
$messages['sk'] = array(
	'stabilization-tab' => '(kk)',
	'stabilization' => 'Stabilizácia stránky',
	'stabilization-text' => "'''|Tieto voľby menia spôsob výberu a zobrazenia stabilnej verzie stránky [[:$1|$1]].'''

Pri zmene nastavenia ''výber stabilnej verzie'' aby sa používala štandardne „{{int:revreview-lev-quality}}“ alebo „{{int:revreview-lev-pristine}}“ revízia sa uistite, či skutočne existuje takáto revízia stránky, inak sa nastavenie neprejaví.",
	'stabilization-perm' => 'Váš účet nemá oprávnenie meniť nastavenia stabilnej verzie. Tu sú súčasné nastavenia [[:$1|$1]]:',
	'stabilization-page' => 'Názov stránky:',
	'stabilization-leg' => 'Potvrdiť nastavenia stabilnej verzie',
	'stabilization-select' => 'Precedencia výberu stabilnej verzie',
	'stabilization-select1' => 'Posledná kvalitná revízia; ak nie je prítomná, je to posledná skontrolovaná',
	'stabilization-select2' => 'Posledná skontrolovaná revízia nezávisle na úrovni overenia',
	'stabilization-select3' => 'Najnovšia neskazená revízia; ak neexistuje, najnovšia kvalitná alebo videná',
	'stabilization-def' => 'Revízia, ktorá sa zobrazí pri štandardnom zobrazení stránky',
	'stabilization-def1' => 'Stabilná revízia; ak nie je prítomná, je to aktuálna/návrh',
	'stabilization-def2' => 'Aktuálna revízia/návrh',
	'stabilization-restrict' => 'Obmedzenia automatického overenia',
	'stabilization-restrict-none' => 'Žiadne ďalšie obmedzenia',
	'stabilization-submit' => 'Potvrdiť',
	'stabilization-notexists' => 'Neexistuje stránka s názvom „[[:$1|$1]]“. Konfigurácia nie je možná.',
	'stabilization-notcontent' => 'Stránku „[[:$1|$1]]“ nie je možné skontrolovať. Konfigurácia nie je možná.',
	'stabilization-comment' => 'Dôvod:',
	'stabilization-otherreason' => 'Iný dôvod:',
	'stabilization-expiry' => 'Vyprší:',
	'stabilization-othertime' => 'Iný čas:',
	'stabilization-sel-short' => 'Precedencia',
	'stabilization-sel-short-0' => 'Kvalita',
	'stabilization-sel-short-1' => 'žiadna',
	'stabilization-sel-short-2' => 'neskazená',
	'stabilization-def-short' => 'štandard',
	'stabilization-def-short-0' => 'aktuálna',
	'stabilization-def-short-1' => 'stabilná',
	'stabilize_expiry_invalid' => 'Neplatný dátum vypršania.',
	'stabilize_expiry_old' => 'Čas vypršania už prešiel.',
	'stabilize-expiring' => 'vyprší $1 (UTC)',
	'stabilization-review' => 'Skontrolovať aktuálnu verziu',
);

/** Slovenian (Slovenščina)
 * @author Dbc334
 */
$messages['sl'] = array(
	'stabilization-page' => 'Naslov strani:',
	'stabilization-submit' => 'Potrdi',
	'stabilization-comment' => 'Razlog:',
	'stabilization-otherreason' => 'Drug razlog:',
	'stabilization-expiry' => 'Poteče:',
	'stabilization-othertime' => 'Drugačen čas:',
	'stabilization-sel-short' => 'Prednost',
	'stabilize-expiring' => 'poteče $1 (UTC)',
	'stabilization-review' => 'Označi trenutno redakcijo kot pregledano',
);

/** Albanian (Shqip)
 * @author Puntori
 */
$messages['sq'] = array(
	'stabilization-tab' => 'vet',
	'stabilization' => 'Stabilizimi i faqes',
	'stabilization-page' => 'Emri i faqes:',
	'stabilization-def2' => 'Versioni i tanishëm',
	'stabilization-submit' => 'Konfirmo',
	'stabilization-notexists' => 'Nuk ka faqe me emrin "[[:$1|$1]]".
Asnjë konfigurim nuk është i mundshëm.',
	'stabilization-notcontent' => 'Faqja "[[:$1|$1]]" nuk mund të rishqyrtohet.
Asnjë konfigurim nuk është i mundshëm.',
	'stabilization-comment' => 'Komenti:',
	'stabilization-expiry' => 'Skadon:',
	'stabilization-sel-short-0' => 'Kualiteti',
	'stabilization-sel-short-1' => "S'ka",
	'stabilization-def-short-0' => 'Tani',
	'stabilize_expiry_invalid' => 'Datë jo vlefshme e skadimit.',
	'stabilize_expiry_old' => 'Koha e skadimit tanimë ka kaluar.',
	'stabilize-expiring' => 'skadon $1 (UTC)',
);

/** Serbian Cyrillic ekavian (Српски (ћирилица))
 * @author Millosh
 * @author Sasa Stefanovic
 * @author Михајло Анђелковић
 */
$messages['sr-ec'] = array(
	'stabilization-tab' => 'ветеран',
	'stabilization' => 'Стабилизација стране',
	'stabilization-text' => "'''Измени подешавања испод у циљу намешатања како ће стабилне верзије стране [[:$1|$1]] бити означене и приказане.'''",
	'stabilization-perm' => 'Твој налог нема дозвола за измену подешавања за стабилне верзије. Тренутна подешавања за страну [[:$1|$1]] су:',
	'stabilization-page' => 'Име странице:',
	'stabilization-leg' => 'Потврди подешавања за стабилне верзије.',
	'stabilization-select' => 'Означавање стабилних верзија.',
	'stabilization-select1' => 'Последња квалитетна верзија; ако не постоји, онда ће бити приказана последња прегледана.',
	'stabilization-select2' => 'Последња прегледана верзија.',
	'stabilization-select3' => 'Последња непокрварена верзија; ако не постоји, последња квалитетна или прегледана ће бити приказана.',
	'stabilization-def' => 'Верзија приказана на подразумеваном приказу стране.',
	'stabilization-def1' => 'Стабилна верзија; ако не постоји, биће приказана тренутна.',
	'stabilization-def2' => 'Тренутни нацрт/ревизија',
	'stabilization-restrict-none' => 'Без додатних ограничења',
	'stabilization-submit' => 'Прихвати',
	'stabilization-notexists' => 'Не постоји страна под именом "[[:$1|$1]]". Подешавање није могуће.',
	'stabilization-notcontent' => 'Страна "[[:$1|$1]]" не може бити прегледана. Подешавање није могуће.',
	'stabilization-comment' => 'Разлог:',
	'stabilization-otherreason' => 'Други разлог:',
	'stabilization-expiry' => 'Истиче:',
	'stabilization-sel-short' => 'Изузетак',
	'stabilization-sel-short-0' => 'Квалитет',
	'stabilization-sel-short-1' => 'Ништа',
	'stabilization-sel-short-2' => 'Непоквареност',
	'stabilization-def-short' => 'Основно',
	'stabilization-def-short-0' => 'Тренутно',
	'stabilization-def-short-1' => 'Стабилно',
	'stabilize_expiry_invalid' => 'Лош датум истицања.',
	'stabilize_expiry_old' => 'Време истицања је већ прошло.',
	'stabilize-expiring' => 'истиче $1 (UTC)',
);

/** Serbian Latin ekavian (Srpski (latinica))
 * @author Michaello
 * @author Михајло Анђелковић
 */
$messages['sr-el'] = array(
	'stabilization-tab' => 'veteran',
	'stabilization' => 'Stabilizacija strane',
	'stabilization-perm' => 'Tvoj nalog nema dozvola za izmenu podešavanja za stabilne verzije. Trenutna podešavanja za stranu [[:$1|$1]] su:',
	'stabilization-page' => 'Ime stranice:',
	'stabilization-leg' => 'Potvrdi podešavanja za stabilne verzije.',
	'stabilization-select' => 'Označavanje stabilnih verzija.',
	'stabilization-select1' => 'Poslednja kvalitetna verzija; ako ne postoji, onda će biti prikazana poslednja pregledana.',
	'stabilization-select3' => 'Poslednja nepokrvarena verzija; ako ne postoji, poslednja kvalitetna ili pregledana će biti prikazana.',
	'stabilization-def' => 'Verzija prikazana na podrazumevanom prikazu strane.',
	'stabilization-def1' => 'Stabilna verzija; ako ne postoji, biće prikazana trenutna.',
	'stabilization-def2' => 'Trenutni nacrt/revizija',
	'stabilization-restrict-none' => 'Bez dodatnih ograničenja',
	'stabilization-submit' => 'Prihvati',
	'stabilization-notexists' => 'Ne postoji strana pod imenom "[[:$1|$1]]". Podešavanje nije moguće.',
	'stabilization-notcontent' => 'Strana "[[:$1|$1]]" ne može biti pregledana. Podešavanje nije moguće.',
	'stabilization-comment' => 'Razlog:',
	'stabilization-otherreason' => 'Drugi razlog:',
	'stabilization-expiry' => 'Ističe:',
	'stabilization-sel-short' => 'Izuzetak',
	'stabilization-sel-short-0' => 'Kvalitet',
	'stabilization-sel-short-1' => 'Ništa',
	'stabilization-sel-short-2' => 'Nepokvarenost',
	'stabilization-def-short' => 'Osnovno',
	'stabilization-def-short-0' => 'Trenutno',
	'stabilization-def-short-1' => 'Stabilno',
	'stabilize_expiry_invalid' => 'Loš datum isticanja.',
	'stabilize_expiry_old' => 'Vreme isticanja je već prošlo.',
	'stabilize-expiring' => 'ističe $1 (UTC)',
);

/** Seeltersk (Seeltersk)
 * @author Pyt
 */
$messages['stq'] = array(
	'stabilization-tab' => '(qa)',
	'stabilization' => 'Sieden-Stabilität',
	'stabilization-text' => '\'\'\'Annerje do Ienstaalengen uum fäästtoulääsen, wo ju stoabile Version fon „[[:$1|$1]]“ uutwääld un anwiesd wäide schäl.\'\'\'

Bie ne Annerenge fon ju Konfiguration fon ju standoardmäitich anwiesde Version ap "wröiged" of "uursproangelk", schäl deerap oachted wäide, dät ju Siede so ne Version änthaalt, uursiede häd ju Annerenge neen groote Uutwierkenge.',
	'stabilization-perm' => 'Du hääst nit ju ärfoarderelke Begjuchtigenge, uum do Ienstaalengen fon ju stoabile Version tou annerjen. Do aktuelle Begjuchtigengen foar „[[:$1|$1]]“ sunt:',
	'stabilization-page' => 'Siedennoome:',
	'stabilization-leg' => 'Ienstaalengen fon ju markierde Version foar ne Siede',
	'stabilization-select' => 'Uutwoal fon ju markierde Version',
	'stabilization-select1' => 'Ju lääste wröigede Version; wan neen deer is, dan ju lääste sieuwede Version',
	'stabilization-select2' => 'Ju lääste wröigede Version, uunouhongich fon dän Markierengslevel',
	'stabilization-def' => 'Anwiesde Version in ju normoale Siedenansicht',
	'stabilization-def1' => 'Ju stoabile Version; wan neen deer is, dan ju aktuelle Version.',
	'stabilization-def2' => 'Ju aktuellste Version',
	'stabilization-submit' => 'Bestäätigje',
	'stabilization-notexists' => 'Dät rakt neen Siede „[[:$1|$1]]“. Neen Ienstaalengen muugelk.',
	'stabilization-notcontent' => 'Ju Siede "[[:$1|$1]]" kon nit wröiged wäide. Konfiguration nit muugelk.',
	'stabilization-comment' => 'Gruund:',
	'stabilization-expiry' => 'Gultich bit:',
	'stabilization-sel-short' => 'Priorität',
	'stabilization-sel-short-0' => 'Qualität',
	'stabilization-sel-short-1' => 'neen',
	'stabilization-def-short' => 'Standoard',
	'stabilization-def-short-0' => 'Aktuell',
	'stabilization-def-short-1' => 'Stoabil',
	'stabilize_expiry_invalid' => 'Uungultich Ouloopdoatum.',
	'stabilize_expiry_old' => 'Dät Ouloopdoatum is al foarbie.',
	'stabilize-expiring' => 'lapt ou $1 (UTC)',
);

/** Sundanese (Basa Sunda)
 * @author Irwangatot
 * @author Kandar
 */
$messages['su'] = array(
	'stabilization' => 'Stabilisasi halaman',
	'stabilization-text' => "''Robah seting katut pikeun mengatur vérsi stabil ti [[:\$1|\$1]] geus dipilih sarta ditémbongkeun.'''

Waktu ngarobah konfigurasi  ''pilihan vérsi stabil'' gunakeun revisi \"kualitas\" atawa \"murni\" sacara default,   
pastikan pikeun mariksa naha aya anu bener-bener kawas revisi di kaca, lamun aya parobahan baris saeutik pangaruhna.",
	'stabilization-perm' => 'Rekening anjeun teu boga kawenangan pikeun ngarobah konfigurasi vérsi stabil.
Setélan kiwari pikeun [[:$1|$1]] nyaéta:',
	'stabilization-page' => 'Ngaran kaca:',
	'stabilization-select' => 'Milihan vérsi stabil',
	'stabilization-def1' => 'Vérsi stabil;
mun euweuh, paké vérsi kiwari',
	'stabilization-def2' => 'Révisi kiwari',
	'stabilization-submit' => 'Konfirmasi',
	'stabilization-notexists' => 'Euweuh kaca nu ngaranna “[[:$1|$1]]”.
KOnfigurasi teu bisa dilarapkeun.',
	'stabilization-comment' => 'Alesan:',
	'stabilization-expiry' => 'Kadaluwarsa:',
	'stabilization-def-short' => 'Buhun',
	'stabilization-def-short-0' => 'Kiwari',
	'stabilization-def-short-1' => 'Stabil',
	'stabilize_expiry_invalid' => 'Titimangsa kadaluwarsana salah.',
	'stabilize_expiry_old' => 'Titimangsa kadaluwarsa geus kaliwat.',
	'stabilize-expiring' => 'kadaluwarsa $1 (UTC)',
);

/** Swedish (Svenska)
 * @author Boivie
 * @author Dafer45
 * @author Lejonel
 * @author M.M.S.
 * @author Najami
 * @author Per
 */
$messages['sv'] = array(
	'stabilization-tab' => 'kvalitet',
	'stabilization' => 'Sidstabilisering',
	'stabilization-text' => "'''Ändra inställningarna nedan för att bestämma hur den publicerade versionen av [[:\$1|\$1]] väljs och visas.'''

När konfigurationen för ''val av publicerad version'' ändras till användande av \"kvalitets\" eller \"orörda\" versioner som standard, kontrollera att det faktiskt finns sådana varianter i sidan, annars får ändringen liten effekt.",
	'stabilization-perm' => 'Ditt konto har inte behörighet att ändra inställningen för publicerade sidversioner.
Här visas de nuvarande inställningarna för [[:$1|$1]]:',
	'stabilization-page' => 'Sidnamn:',
	'stabilization-leg' => 'Bekräfta inställningar för publicerade versioner',
	'stabilization-select' => 'Förval för visning av publicerad version',
	'stabilization-select1' => 'Den senaste kvalitetsversionen om den finns, annars den senaste sedda versionen',
	'stabilization-select2' => 'Den senaste granskade versionen, oavsett valideringsnivå',
	'stabilization-select3' => 'Den senaste orörda versionen; sedan senaste kvalitets-; sen senaste synade.',
	'stabilization-def' => 'Sidversion som används som standard när sidan visas',
	'stabilization-def1' => 'Den publicerade versionen; om den saknas, nuvarande/utkast-versionen',
	'stabilization-def2' => 'Nuvarande/utkast-versionen',
	'stabilization-restrict' => 'Begränsningar av automatgranskning',
	'stabilization-restrict-none' => 'Inga extra begränsningar',
	'stabilization-submit' => 'Bekräfta',
	'stabilization-notexists' => 'Det finns ingen sida med titeln "[[:$1|$1]]". Inga inställningar kan göras.',
	'stabilization-notcontent' => 'Sidan "[[:$1|$1]]" kan inte granskas. Inga inställningar kan göras.',
	'stabilization-comment' => 'Anledning:',
	'stabilization-otherreason' => 'Annan anledning:',
	'stabilization-expiry' => 'Varaktighet:',
	'stabilization-othertime' => 'Annan tid:',
	'stabilization-sel-short' => 'Företräde',
	'stabilization-sel-short-0' => 'Kvalitet',
	'stabilization-sel-short-1' => 'Ingen',
	'stabilization-sel-short-2' => 'Orörd',
	'stabilization-def-short' => 'Standard',
	'stabilization-def-short-0' => 'Senaste',
	'stabilization-def-short-1' => 'Publicerad',
	'stabilize_page_invalid' => 'Målsidans titel är ogiltig.',
	'stabilize_page_notexists' => 'Målsidan finns ej.',
	'stabilize_invalid_level' => 'Ogiltig skyddsnivå.',
	'stabilize_expiry_invalid' => 'Ogiltig varaktighet.',
	'stabilize_expiry_old' => 'Varaktigheten har redan löpt ut.',
	'stabilize-expiring' => 'upphör den $1 (UTC)',
	'stabilization-review' => 'Markera den nuvarande revisionen som kontrollerad',
);

/** Silesian (Ślůnski)
 * @author Herr Kriss
 */
$messages['szl'] = array(
	'stabilization-expiry' => 'Wygaso:',
	'stabilization-def-short' => 'Důmyślna',
);

/** Tamil (தமிழ்)
 * @author Ulmo
 */
$messages['ta'] = array(
	'stabilization-page' => 'பக்கப் பெயர்:',
);

/** Telugu (తెలుగు)
 * @author Chaduvari
 * @author Kiranmayee
 * @author Veeven
 */
$messages['te'] = array(
	'stabilization-tab' => 'vet',
	'stabilization' => 'పేజీ స్ధిరీకరణ',
	'stabilization-text' => "'''[[:$1|$1]] యొక్క సుస్థిర కూర్పు ఎలా ఎంచుకోవాలి మరియు చూపించబడాలో సరిదిద్దడానికి క్రింది అమరికలు మార్చండి.'''
''సుస్థిర కూర్పు ఎంపిక'' యొక్క కాంఫిగురేషనుని ''నాణ్యత'' మరియు ''ప్రిస్తినే'' కూర్పులని వాడడము కోసము మారుస్తున్నపుడు, ఇలాంటి కూప్రులు ఇంకా ఏమైనా వున్నాయేమో అని పరీక్షా చేయడం మర్చిపోవద్దు. లేకుంటే, మీ మార్పులు కనిపించవు.",
	'stabilization-perm' => 'మీ ఖాతాకు సుస్థిర కూర్పును మార్చే అనుమతి లేదు. [[:$1|$1]]కి ప్రస్తుత అమరికల ఇవీ:',
	'stabilization-page' => 'పేజీ పేరు:',
	'stabilization-leg' => 'పేజీకి సుస్థిర కూర్పు సెట్టి౦గులని నిర్ధేశించండి',
	'stabilization-select' => 'సుస్థిర కూర్పు ఏ౦పిక',
	'stabilization-select1' => 'చిట్టచివరి నాణ్యమైన కూర్పు; అది లేకపోతే, కనబడిన వాటిలో చిట్టచివరిది',
	'stabilization-select2' => 'చివరి సమీక్షిత కూర్పు',
	'stabilization-def' => 'డిఫాల్టు పేజీ వ్యూలో చూపించే కూర్పు',
	'stabilization-def1' => 'సుస్థిర కూర్పు; అది లేకపోతే, ప్రస్తుత కూర్పు',
	'stabilization-def2' => 'ప్రస్తుత కూర్పు',
	'stabilization-restrict-none' => 'మరిన్ని నిరోధాలు లేవు',
	'stabilization-submit' => 'నిర్ధారించు',
	'stabilization-notexists' => '"[[:$1|$1]]" అనే పేజీ లేదు. స్వరూపణం వీలుపడదు.',
	'stabilization-notcontent' => '"[[:$1|$1]]" అన్న పేజీని సమీక్షించ లేదు. ఎటువంటి స్వరూపణం వీలు కాదు.',
	'stabilization-comment' => 'కారణం:',
	'stabilization-otherreason' => 'ఇతర కారణం:',
	'stabilization-expiry' => 'కాలంచెల్లు తేదీ:',
	'stabilization-othertime' => 'ఇతర సమయం:',
	'stabilization-sel-short' => 'ప్రాధాన్యత',
	'stabilization-sel-short-0' => 'నాణ్యత',
	'stabilization-sel-short-1' => 'ఏమీలేదు',
	'stabilization-def-short' => 'డిఫాల్టు',
	'stabilization-def-short-0' => 'ప్రస్తుత',
	'stabilization-def-short-1' => 'ప్రచురితం',
	'stabilize_expiry_invalid' => 'తప్పుడు కాలపరిమితి తేదీ.',
	'stabilize_expiry_old' => 'ఈ కాలం ఎప్పుడో చెల్లిపోయింది.',
	'stabilize-expiring' => '$1 (UTC) నాడు కాలం చెల్లుతుంది',
);

/** Tetum (Tetun)
 * @author MF-Warburg
 */
$messages['tet'] = array(
	'stabilization-page' => 'Naran pájina nian:',
);

/** Tajik (Cyrillic) (Тоҷикӣ (Cyrillic))
 * @author Ibrahim
 */
$messages['tg-cyrl'] = array(
	'stabilization-tab' => 'санҷиш',
	'stabilization' => 'Пойдорсозии саҳифаҳо',
	'stabilization-text' => "'''Тағйири танзимоти зерин ба манзури таъйини ин, ки нусхаи пойдор аз [[:$1|$1]] чигуна интихоб ва намоиш дода мешавад.'''",
	'stabilization-perm' => 'Ҳисоби шумо иҷозати тағйири танзими нусхаи пойдорро надорад. Танзимоти феълӣ барои [[:$1|$1]]  чунинанд:',
	'stabilization-page' => 'Номи саҳифа:',
	'stabilization-leg' => 'Тасдиқи танзими нусхаи пойдор',
	'stabilization-select' => 'Интихоби нусхаи пойдор',
	'stabilization-select1' => 'Охирин нусхаи бо кайфият; агар он вуҷуд надошта бошад, пас он охирин яке аз баррасидашуда аст',
	'stabilization-select2' => 'Охирин саҳифаи баррасӣ шуда',
	'stabilization-def' => 'Нусхае ки дар ҳолати пешфарз намоиш дода мешавад',
	'stabilization-def1' => 'Нусхаи пойдор; агар он вуҷуд надошта бошад, пас он нусхаи феълӣ аст',
	'stabilization-def2' => 'Нусхаи феълӣ',
	'stabilization-submit' => 'Тасдиқ',
	'stabilization-notexists' => 'Саҳифае бо унвони "[[:$1|$1]]" вуҷуд надорад. Танзимот мумкин нест.',
	'stabilization-notcontent' => 'Саҳифаи "[[:$1|$1]]" қобили баррасӣ нест. Танзимот мумкин нест.',
	'stabilization-comment' => 'Тавзеҳ:',
	'stabilization-expiry' => 'Интиҳо:',
	'stabilization-sel-short' => 'Тақдим',
	'stabilization-sel-short-0' => 'Бо кайфият',
	'stabilization-sel-short-1' => 'Ҳеҷ',
	'stabilization-def-short' => 'Пешфарз',
	'stabilization-def-short-0' => 'Феълӣ',
	'stabilization-def-short-1' => 'Пойдор',
	'stabilize_expiry_invalid' => 'Таърихи интиҳоии ғайримиҷоз.',
	'stabilize_expiry_old' => 'Таърихи интиҳо аллакай сипарӣ шудааст.',
	'stabilize-expiring' => 'Дар $1 (UTC) ба интиҳо мерасад',
);

/** Tajik (Latin) (Тоҷикӣ (Latin))
 * @author Liangent
 */
$messages['tg-latn'] = array(
	'stabilization-tab' => 'sançiş',
	'stabilization' => 'Pojdorsoziji sahifaho',
	'stabilization-perm' => "Hisobi şumo içozati taƣjiri tanzimi nusxai pojdorro nadorad. Tanzimoti fe'lī baroi [[:$1|$1]]  cuninand:",
	'stabilization-page' => 'Nomi sahifa:',
	'stabilization-leg' => 'Tasdiqi tanzimi nusxai pojdor',
	'stabilization-select1' => 'Oxirin nusxai bo kajfijat; agar on vuçud nadoşta boşad, pas on oxirin jake az barrasidaşuda ast',
	'stabilization-def' => 'Nusxae ki dar holati peşfarz namoiş doda meşavad',
	'stabilization-submit' => 'Tasdiq',
	'stabilization-notexists' => 'Sahifae bo unvoni "[[:$1|$1]]" vuçud nadorad. Tanzimot mumkin nest.',
	'stabilization-notcontent' => 'Sahifai "[[:$1|$1]]" qobili barrasī nest. Tanzimot mumkin nest.',
	'stabilization-expiry' => 'Intiho:',
	'stabilization-sel-short' => 'Taqdim',
	'stabilization-sel-short-0' => 'Bo kajfijat',
	'stabilization-sel-short-1' => 'Heç',
	'stabilization-def-short' => 'Peşfarz',
	'stabilization-def-short-0' => "Fe'lī",
	'stabilization-def-short-1' => 'Pojdor',
	'stabilize_expiry_invalid' => "Ta'rixi intihoiji ƣajrimiçoz.",
	'stabilize_expiry_old' => "Ta'rixi intiho allakaj siparī şudaast.",
	'stabilize-expiring' => 'Dar $1 (UTC) ba intiho merasad',
);

/** Thai (ไทย)
 * @author Octahedron80
 * @author Passawuth
 */
$messages['th'] = array(
	'stabilization-page' => 'ชื่อหน้า:',
	'stabilization-submit' => 'ยืนยัน',
	'stabilization-comment' => 'เหตุผล:',
	'stabilization-sel-short-1' => 'ไม่มี',
);

/** Turkmen (Türkmençe)
 * @author Hanberke
 */
$messages['tk'] = array(
	'stabilization-tab' => 'vet',
	'stabilization' => 'Sahypa durnuklaşdyrma',
	'stabilization-text' => "'''[[:\$1|\$1]] üçin durnukly wersiýanyň nähili saýlanmalydygyny we görkezilmelidigini sazlamak üçin sazlamalry üýtgediň.'''

Gaýybana \"hil\" ýa-da \"başky\" wersiýalaryny ulanmak üçin ''durnukly wersiýa saýlamak''konfigurasiýasyny üýtgeden mahalyňyz, sahypada şeýle wersiýalaryň bardygyny barlandygyňyza göz ýetiriň, ýogsam üýtgeşmäniň täsiri az bolar.",
	'stabilization-perm' => 'Hasabyňyzyň durnukly wersiýa konfigurasiýasyny üýtgetmäge rugsady ýok. 
[[:$1|$1]] üçin häzirki sazlamalar:',
	'stabilization-page' => 'Sahypa ady:',
	'stabilization-leg' => 'Durnukly wersiýa sazlamalaryny tassykla',
	'stabilization-select' => 'Durnukly wersiýa saýlama öňürtiligi',
	'stabilization-select1' => 'Iň täze ýokary hilli wersiýa; eger ýok bolsa iň täze äňedilen wersiýa',
	'stabilization-select2' => 'Iň soňky gözden geçirilen wersiýa (barlama derejesine seretmezden)',
	'stabilization-select3' => 'Iň täze başky wersiýa; eger ýok bolsa iň täze ýokary hillisi; eger ol hem ýok bolsa iň täze äňedilen wersiýa',
	'stabilization-def' => 'Gaýybana sahypa görkezişinde görkezilýän wersiýa',
	'stabilization-def1' => 'Durnukly wersiýa; eger ýok bolsa, onda häzirki wersiýa/garalama',
	'stabilization-def2' => 'Häzirki/garalama wersiýa',
	'stabilization-restrict' => 'Awto gözden geçirme çäklendirmeleri',
	'stabilization-restrict-none' => 'Başga goşmaça çäklendirme ýok',
	'stabilization-submit' => 'Tassykla',
	'stabilization-notexists' => '"[[:$1|$1]]" atlandyrylýan sahypa ýok.
Konfigurasiýa mümkin däl.',
	'stabilization-notcontent' => '"[[:$1|$1]]" sahypasyny gözden geçirip bolmaýar.
Konfigurirlemek mümkin däl.',
	'stabilization-comment' => 'Sebäp:',
	'stabilization-otherreason' => 'Başga sebäp:',
	'stabilization-expiry' => 'Gutarýan wagty:',
	'stabilization-othertime' => 'Başga wagt:',
	'stabilization-sel-short' => 'Öňürtilik',
	'stabilization-sel-short-0' => 'Hil',
	'stabilization-sel-short-1' => 'Hiçbiri',
	'stabilization-sel-short-2' => 'Başky',
	'stabilization-def-short' => 'Gaýybana',
	'stabilization-def-short-0' => 'Häzirki',
	'stabilization-def-short-1' => 'Durnukly',
	'stabilize_expiry_invalid' => 'Nädogry gutaryş senesi.',
	'stabilize_expiry_old' => 'Gutaryş möhleti eýýäm geçipdir.',
	'stabilize-expiring' => 'gutarýar $1 (UTC)',
	'stabilization-review' => 'Häzirki wersiýany gözden geçir',
);

/** Tagalog (Tagalog)
 * @author AnakngAraw
 */
$messages['tl'] = array(
	'stabilization-tab' => 'suriing mabuti (masinsinan)',
	'stabilization' => 'Pagpapatatag ng pahina',
	'stabilization-text' => "'''Baguhin ang mga pagtatakda sa ibaba upang mabago ang kung paano napili at napalitaw (naipakita) ang matatag na bersyon ng [[:\$1|\$1]].'''

Kapag binabago ang pagkakaayos ng ''pilian ng matatag na bersyon'' para magamit ang mga pagbabago sa \"antas ng uri\" o \"dalisay\" sa pamamamagitan ng likas na pagtatakda, tiyaking susuriin kung talagang mayroong ganyang mga rebisyon sa loob ng pahina, dahil bahagya lamang ang magiging epekto ng pagbabago kung wala.",
	'stabilization-perm' => 'Walang kapahintulutan ang kuwenta/akawnt mo upang baguhin ang pagkakaayos ng matatag na bersyon.
Narito ang pangkasalukuyang mga katakdaan para sa [[:$1|$1]]:',
	'stabilization-page' => 'Pangalan ng pahina:',
	'stabilization-leg' => 'Tiyakin ang mga pagtatakda para sa matatag na bersyon',
	'stabilization-select' => 'Pagpipilian para sa matatag na bersyon',
	'stabilization-select1' => 'Ang pinakahuling pagbabagong may mataas na uri; kung wala, ang pinakahuling namataang isa na lamang',
	'stabilization-select2' => 'Ang pinakahuling nasuring pagbabago, kahit na anupaman ang antas ng pagpapatunay',
	'stabilization-select3' => 'Ang pinakahuling dalisay (malinis) na pagbabago; kung wala, ang huling may pinakamataas na uri o namataang isa na lamang',
	'stabilization-def' => 'Ang pagbabagong ipinakita sa natatanaw na likas na nakatakdang pahina',
	'stabilization-def1' => 'Ang matatag na pagbabago, kung, ang pangkasalukuyang isa na lamang',
	'stabilization-def2' => 'Ang pangkasalukuyang pagbabago',
	'stabilization-restrict' => 'Mga hangganan ng pagsusuri/kusang pagsusuri',
	'stabilization-restrict-none' => 'Walang karagdagang mga hangganan',
	'stabilization-submit' => 'Tiyakin',
	'stabilization-notexists' => 'Walang pahinang tinatawag na "[[:$1|$1]]".
Walang maaaring maging pagkakaayos (konpigurasyon).',
	'stabilization-notcontent' => 'Hindi masusuri ang "[[:$1|$1]]".
Walang maaaring maging pagkakaayos (konpigurasyon).',
	'stabilization-comment' => 'Dahilan:',
	'stabilization-otherreason' => 'Ibang dahilan:',
	'stabilization-expiry' => 'Magtatapos sa:',
	'stabilization-othertime' => 'Ibang oras:',
	'stabilization-sel-short' => 'Pagkakauna-una (pagkakasunud-sunod)',
	'stabilization-sel-short-0' => 'Kaantasan ng uri (kalidad)',
	'stabilization-sel-short-1' => 'Wala',
	'stabilization-sel-short-2' => 'Dalisay (malinis)',
	'stabilization-def-short' => 'Likas na nakatakda',
	'stabilization-def-short-0' => 'Pangkasalukuyan',
	'stabilization-def-short-1' => 'Nalathala na',
	'stabilize_expiry_invalid' => 'Hindi tanggap na petsa ng pagtatapos.',
	'stabilize_expiry_old' => 'Lagpas na ang oras/panahon ng pagtatapos na ito.',
	'stabilize-expiring' => 'magtatapos sa $1 (UTC)',
);

/** Turkish (Türkçe)
 * @author Erkan Yilmaz
 * @author Joseph
 * @author Karduelis
 * @author Srhat
 */
$messages['tr'] = array(
	'stabilization-tab' => 'vet',
	'stabilization' => 'Sayfa kararlılaştırılması',
	'stabilization-text' => "'''[[:\$1|\$1]] için yayımlanmış sürümün nasıl seçilip görüntüleneceğini ayarlamak için ayarları değiştirin.'''

'''Not:''' ''yayımlanmış sürüm seçimi'' \"kalite\" ya da \"asıl\" sürüm olarak değiştirmek, böyle bir sürüm yoksa etkisiz olacaktır. Ayrıca, unutmayın ki bir \"kaliteli\" sürüm aynı zamanda \"kontrol edilmiş\" sürüm sayılır, vesaire.",
	'stabilization-perm' => 'Hesabınızın yayımlanmış sürüm yapılandırmasını değiştirmeye izni yok.
[[:$1|$1]] için şuanki ayarlar:',
	'stabilization-page' => 'Sayfa adı:',
	'stabilization-leg' => 'Yayımlanmış sürüm ayarlarını onayla',
	'stabilization-select' => 'Yayımlanmış sürüm seçim önceliği',
	'stabilization-select1' => 'En son kaliteli revizyon; eğer yoksa, en son gözlenmiş olan',
	'stabilization-select2' => 'En son kontrol edilen revizyon',
	'stabilization-select3' => 'En son bozulmamış revizyon; eğer yoksa, en son kaliteli ya da gözlenmiş olan',
	'stabilization-def' => 'Varsayılan sayfa görünümünde gösterilen revizyon',
	'stabilization-def1' => 'Yayımlanmış revizyon; eğer yoksa, halihazırda bulunan/karalama',
	'stabilization-def2' => 'Şu anki revizyon/karalama',
	'stabilization-restrict' => 'İnceleme/oto-inceleme kısıtlamaları',
	'stabilization-restrict-none' => 'Başka ilave kısıtlama yok',
	'stabilization-submit' => 'Tespit et',
	'stabilization-notexists' => '"[[:$1|$1]]" adında bir sayfa yok.
Yapılandırma mümkün değil.',
	'stabilization-notcontent' => '"[[:$1|$1]]" sayfası gözden geçirilemiyor.
Yapılandırma mümkün değil.',
	'stabilization-comment' => 'Sebep:',
	'stabilization-otherreason' => 'Diğer sebep:',
	'stabilization-expiry' => 'Süresi bitiyor:',
	'stabilization-othertime' => 'Diğer zaman:',
	'stabilization-sel-short' => 'Öncelik',
	'stabilization-sel-short-0' => 'Kalite',
	'stabilization-sel-short-1' => 'Hiçbiri',
	'stabilization-sel-short-2' => 'Bozulmamış',
	'stabilization-def-short' => 'Varsayılan',
	'stabilization-def-short-0' => 'Şuanki',
	'stabilization-def-short-1' => 'Yayımlandı',
	'stabilize_page_invalid' => 'Hedef sayfa başlığı geçersiz.',
	'stabilize_page_notexists' => 'Hedef sayfa mevcut değil.',
	'stabilize_page_unreviewable' => 'Hedef sayfa incelenebilir ad alanında değil.',
	'stabilize_invalid_precedence' => 'Geçersiz sürüm önceliği.',
	'stabilize_invalid_autoreview' => 'Geçersiz oto-inceleme kısıtlaması',
	'stabilize_invalid_level' => 'Geçersiz koruma seviyesi.',
	'stabilize_expiry_invalid' => 'Geçersiz sona erme tarihi.',
	'stabilize_expiry_old' => 'Sona erme tarihi zaten geçmiş.',
	'stabilize-expiring' => '$1 (UTC) tarihinde sona eriyor',
	'stabilization-review' => 'Geçerli sürümü kontrol edilmiş olarak işaretle',
);

/** Tatar (Cyrillic) (Татарча/Tatarça (Cyrillic))
 * @author Ерней
 */
$messages['tt-cyrl'] = array(
	'stabilization-def-short' => 'Килешү буенча',
);

/** Ukrainian (Українська)
 * @author AS
 * @author Ahonc
 * @author NickK
 * @author Prima klasy4na
 */
$messages['uk'] = array(
	'stabilization-tab' => '(кя)',
	'stabilization' => 'Стабілізація сторінки',
	'stabilization-text' => "'''Змініть наведені нижче налаштування, щоб упорядкувати вибір і відображення опублікованої версії [[:$1|$1]].'''

'''Зауваження:''' зміна параметра ''вибір опублікованої версії'' у значення «якісна» або «чиста» версія не матиме ефекту, якщо фактично немає таких версій. Також зауважте, що «якісна» версія включає «перевірену» версію і так далі.",
	'stabilization-perm' => 'Вашому обліковому запису не вистачає прав для зміни налаштувань опублікованої версії.
Тут наведені поточні налаштування для [[:$1|$1]]:',
	'stabilization-page' => 'Назва сторінки:',
	'stabilization-leg' => 'Підтвердження налаштувань опублікованої версії',
	'stabilization-select' => 'Порядок вибору опублікованої версії',
	'stabilization-select1' => 'Найсвіжіша якісна версія; якщо такої нема, то найсвіжіша переглянута',
	'stabilization-select2' => 'Остання перевірена версія',
	'stabilization-select3' => 'Остання недоторкана версія, якщо такої немає, то остання якісна або переглянута',
	'stabilization-def' => 'Версія, що показується за умовчанням',
	'stabilization-def1' => 'Опублікована версія; якщо такої нема, то поточна/чорнова',
	'stabilization-def2' => 'Поточна/чорнова версія',
	'stabilization-restrict' => 'Обмеження рецензування/авторецензування',
	'stabilization-restrict-none' => 'Без додаткових обмежень',
	'stabilization-submit' => 'Підтвердити',
	'stabilization-notexists' => 'Відсутня сторінка з назвою «[[:$1|$1]]».
Налаштування неможливе.',
	'stabilization-notcontent' => 'Сторінка «[[:$1|$1]]» не може бути перевірена.
Налаштування неможливе.',
	'stabilization-comment' => 'Причина:',
	'stabilization-otherreason' => 'Інша причина:',
	'stabilization-expiry' => 'Закінчується:',
	'stabilization-othertime' => 'Інший час:',
	'stabilization-sel-short' => 'Порядок слідування',
	'stabilization-sel-short-0' => 'Якісна',
	'stabilization-sel-short-1' => 'Нема',
	'stabilization-sel-short-2' => 'Недоторкана',
	'stabilization-def-short' => 'Стандартно',
	'stabilization-def-short-0' => 'Поточна',
	'stabilization-def-short-1' => 'Опублікована',
	'stabilize_expiry_invalid' => 'Помилкова дата закінчення.',
	'stabilize_expiry_old' => 'Зазначений час закінчення пройшов.',
	'stabilize-expiring' => 'закінчується о $1 (UTC)',
	'stabilization-review' => 'Позначити поточну версію перевіреною',
);

/** Vèneto (Vèneto)
 * @author Candalua
 */
$messages['vec'] = array(
	'stabilization-tab' => 'c. q.',
	'stabilization' => 'Stabilizassion de pagina',
	'stabilization-text' => "'''Canbia le inpostassion qua soto par stabilir come la version publicà de [[:\$1|\$1]] la vegna selessionà e mostrà.'''

Co te canbi la configurassion ''selession version publicà'' par doparar de default le revision \"qualità\" o \"primitiva\",
assicùrete de controlar se efetivamente ghe sia ste revision in te la pagina, senò la modifica no la gavarà molto efeto.",
	'stabilization-perm' => 'No ti gà i permessi necessari par canbiar le inpostassion de la version publicà.
Chì ghe xe le inpostassion atuali par [[:$1|$1]]:',
	'stabilization-page' => 'Nome de la pagina:',
	'stabilization-leg' => 'Conferma le inpostassion par la version publicà',
	'stabilization-select' => 'Priorità de selession de la version publicà',
	'stabilization-select1' => "L'ultima version de qualità;
se no ghe n'è, alora l'ultima version rivardà",
	'stabilization-select2' => "L'ultima version riesaminà, indipendentemente dal livèl de validassion",
	'stabilization-select3' => "L'ultima version primitiva; se no ghe n'è, alora l'ultima version de qualità o l'ultima rivardà.",
	'stabilization-def' => 'Version mostrà par default quando se varda la pagina',
	'stabilization-def1' => "La revision publicà; se no ghe n'è, alora la revision o bozza atuale",
	'stabilization-def2' => 'La revision o bozza atuale',
	'stabilization-restrict' => "Restrizioni su l'auto-revision",
	'stabilization-restrict-none' => 'Nissun restrizion èstra',
	'stabilization-submit' => 'Conferma',
	'stabilization-notexists' => 'No ghe xe nissuna pagina che se ciama "[[:$1|$1]]".
Nissuna configurassion xe possibile.',
	'stabilization-notcontent' => 'La pagina "[[:$1|$1]]" no la pode èssar riesaminà.
No se pode canbiar le inpostassion.',
	'stabilization-comment' => 'Motivassion:',
	'stabilization-otherreason' => 'Altro motivo:',
	'stabilization-expiry' => 'Scadensa:',
	'stabilization-othertime' => 'Altra durata:',
	'stabilization-sel-short' => 'Preçedensa',
	'stabilization-sel-short-0' => 'De qualità',
	'stabilization-sel-short-1' => 'Nissuna',
	'stabilization-sel-short-2' => 'Primitiva',
	'stabilization-def-short' => 'Predefinìa',
	'stabilization-def-short-0' => 'Atuale',
	'stabilization-def-short-1' => 'Publicà',
	'stabilize_expiry_invalid' => 'Data de scadensa mìa valida.',
	'stabilize_expiry_old' => 'Sta scadensa la xe zà passà.',
	'stabilize-expiring' => 'scadensa $1 (UTC)',
	'stabilization-review' => 'Verifica la version atuale',
);

/** Veps (Vepsan kel')
 * @author Игорь Бродский
 */
$messages['vep'] = array(
	'stabilization-tab' => 'vet',
	'stabilization' => 'Lehtpolen stabilizacii',
	'stabilization-text' => "'''Toižetagat järgendused, miše pätta, kut pidab valita da ozutelda [[:\$1|\$1]]-lehtpolen stabiline versii.'''

Konz tö toižetat ''stabiližen versijan valičendan'' järgendused, miše kävutada \"laduline\" vai \"puhtaz\" 
järgendusen mödhe, ka kodvgat, om-ik lehtpolel nigomid toižetusid, ika tö et sabustagoi metod.",
	'stabilization-perm' => 'Teile ei ulotu oiktusid, miše toižetada stabiližen versijan ozutamižen järgendused.
Naku oma nügüdläižed järgendused [[:$1|$1]]-lehtpolen täht:',
	'stabilization-page' => 'Lehtpolen nimi:',
	'stabilization-leg' => 'Stabiližen versijan järgendusiden vahvištoitand',
	'stabilization-select' => 'Stabiližen versijan valičendan järgenduz',
	'stabilization-select1' => 'Naku om veresemb kodvdud versii; ku mugošt ei ole, ka veresemb arvosteldud versijoišpäi.',
	'stabilization-select2' => "Jäl'gmäine kodvdud versii, vahvištoitandan tazopindha kacmata",
	'stabilization-select3' => "Jäl'gmäine koskmatoi versii; ku mugošt ei ole, ka jäl'gmäine kodvdud vai arvosteldud versii.",
	'stabilization-def' => 'Versii, kudambad ozutadas augotižjärgendusen mödhe',
	'stabilization-def1' => 'Stabiline versii; ku mugošt ei ole, ka nügüdläine (kodvversii)',
	'stabilization-def2' => 'Nügüdläine versii (kodvversii)',
	'stabilization-restrict' => 'Avtoarvostelendan kaidendused',
	'stabilization-restrict-none' => 'Ei ole ližakaidendusid',
	'stabilization-submit' => 'Vahvištoitta',
	'stabilization-notexists' => 'Ei ole "[[:$1|$1]]"-nimitadud lehtpolen versijad. Ei voi järgeta.',
	'stabilization-notcontent' => '"[[:$1|$1]]"-lehtpol\'t ei voi kodvda.
Ei voi järgeta.',
	'stabilization-comment' => 'Sü:',
	'stabilization-otherreason' => 'Toine sü:',
	'stabilization-expiry' => 'Lopstrok:',
	'stabilization-othertime' => 'Toine aig',
	'stabilization-sel-short' => "Jäl'gendusen järgenduz.",
	'stabilization-sel-short-0' => 'Kodvdud',
	'stabilization-sel-short-1' => 'Ei ole',
	'stabilization-sel-short-2' => 'Koskmatoi',
	'stabilization-def-short' => 'Augotižjärgendusen mödhe',
	'stabilization-def-short-0' => 'Nügüdläine',
	'stabilization-def-short-1' => 'Publikoitud',
	'stabilize_expiry_invalid' => 'Petuzline lopstrok.',
	'stabilize_expiry_old' => 'Nece tegendan lopmižen aig om jo männu.',
	'stabilize-expiring' => 'lopiše aigal $1 (UTC)',
	'stabilization-review' => 'Arvostelda nügüdläine versii',
);

/** Vietnamese (Tiếng Việt)
 * @author Minh Nguyen
 * @author Trần Nguyễn Minh Huy
 * @author Vinhtantran
 */
$messages['vi'] = array(
	'stabilization-tab' => 'vet',
	'stabilization' => 'Ổn định trang',
	'stabilization-text' => "'''Thay đổi thiết lập dưới đây để điều chỉnh cách lựa chọn và hiển thị phiên bản công bố của [[:\$1|\$1]].'''

Khi thay đổi cấu hình ''lựa chọn phiên bản công bố'' để mặc định sử dụng các phiên bản \"chất lượng\" hoặc \"sơ khai\",
hãy nhớ kiểm tra xem thực sự có những phiên bản như vậy trong trang không, nếu không thay đổi đó sẽ có rất ít tác dụng.",
	'stabilization-perm' => 'Tài khoản của bạn không có quyền thay đổi cấu hình phiên bản công bố.
Dưới đây là các thiết lập hiện hành cho [[:$1|$1]]:',
	'stabilization-page' => 'Tên trang:',
	'stabilization-leg' => 'Xác nhận các thiết lập bản công bố',
	'stabilization-select' => 'Thứ tự lựa chọn bản công bố',
	'stabilization-select1' => 'Bản chất lượng mới nhất;
nếu không có, sẽ là bản đã xem qua mới nhất',
	'stabilization-select2' => 'Bản đã duyệt mới nhất, bất kể mức độ phê chuẩn',
	'stabilization-select3' => 'Phiên bản cổ xưa mới nhất; nếu không có, thì bản chất lượng hoặc đã xem qua mới nhất',
	'stabilization-def' => 'Bản được hiển thị mặc định',
	'stabilization-def1' => 'Phiên bản công bố; nếu không có, sẽ là bản hiện hành/bản nháp',
	'stabilization-def2' => 'Phiên bản hiện hành',
	'stabilization-restrict' => 'Hạn chế duyệt tự động',
	'stabilization-restrict-none' => 'Không có hạn chế nào khác',
	'stabilization-submit' => 'Xác nhận',
	'stabilization-notexists' => 'Không có trang nào có tên “[[:$1|$1]]”.
Không thể cấu hình.',
	'stabilization-notcontent' => 'Trang “[[:$1|$1]]” không thể được duyệt.
Không thể cấu hình.',
	'stabilization-comment' => 'Lý do:',
	'stabilization-otherreason' => 'Lý do khác:',
	'stabilization-expiry' => 'Thời hạn:',
	'stabilization-othertime' => 'Thời gian khác:',
	'stabilization-sel-short' => 'Đi trước',
	'stabilization-sel-short-0' => 'Chất lượng',
	'stabilization-sel-short-1' => 'Không có',
	'stabilization-sel-short-2' => 'Bản gốc',
	'stabilization-def-short' => 'Mặc định',
	'stabilization-def-short-0' => 'Hiện hành',
	'stabilization-def-short-1' => 'Ổn định',
	'stabilize_page_invalid' => 'Tên trang đích không hợp lệ',
	'stabilize_page_notexists' => 'Trang đích không tồn tại',
	'stabilize_invalid_level' => 'Mức độ bảo vệ không hợp lệ.',
	'stabilize_expiry_invalid' => 'Thời hạn không hợp lệ.',
	'stabilize_expiry_old' => 'Thời hạn đã qua.',
	'stabilize_denied' => 'Không cho phép.',
	'stabilize-expiring' => 'hết hạn vào $1 (UTC)',
	'stabilization-review' => 'Duyệt phiên bản hiện hành',
);

/** Volapük (Volapük)
 * @author Malafaya
 * @author Smeira
 */
$messages['vo'] = array(
	'stabilization-tab' => '(ka)',
	'stabilization' => 'Fümöfükam pada',
	'stabilization-text' => "'''Votükolös parametis dono ad sludön, lio fomam fümöfik pada: [[:$1|$1]] pavälon e pajonon.'''",
	'stabilization-perm' => 'Kal olik no dälon ad votükön parametemi fomama fümöfik. Is palisedon parametem anuik pro pad: [[:$1|$1]]:',
	'stabilization-page' => 'Nem pada:',
	'stabilization-leg' => 'Fümedön parametis fomama fümöfik',
	'stabilization-select' => 'Väl fomama fümöfik',
	'stabilization-select2' => 'Fomam pekrütöl lätik',
	'stabilization-def' => 'Fomam jonabik pö padilogams kösömik',
	'stabilization-def1' => 'Fomam fümöfik; if no dabinon, tän fomam anuik',
	'stabilization-def2' => 'Fomam anuik',
	'stabilization-submit' => 'Fümedön',
	'stabilization-notexists' => 'Pad tiädü "[[:$1|$1]]" no dabinon. Fomükam no mögon.',
	'stabilization-notcontent' => 'Pad: "[[:$1|$1]]" no kanon pakrütön. Parametem nonik mögon.',
	'stabilization-comment' => 'Kod:',
	'stabilization-expiry' => 'Dul jü:',
	'stabilization-sel-short-0' => 'Kaliet',
	'stabilization-sel-short-1' => 'Nonik',
	'stabilization-def-short-0' => 'Anuik',
	'stabilization-def-short-1' => 'Fümöfik',
	'stabilize_expiry_invalid' => 'Dul no lonöföl.',
	'stabilize-expiring' => 'dulon jü $1 (UTC)',
);

/** Yiddish (ייִדיש)
 * @author פוילישער
 */
$messages['yi'] = array(
	'stabilization-page' => 'בלאט נאמען:',
	'stabilization-comment' => 'אורזאַך:',
	'stabilization-sel-short-1' => 'קיין',
);

/** Cantonese (粵語)
 * @author Shinjiman
 */
$messages['yue'] = array(
	'stabilization-tab' => '查',
	'stabilization' => '穩定頁',
	'stabilization-text' => "'''改下面嘅設定去調節所揀嘅[[:$1|$1]]之穩定版如何顯示。'''",
	'stabilization-perm' => '你嘅戶口無權限去改穩定版設定。
呢度有現時[[:$1|$1]]嘅設定:',
	'stabilization-page' => '版名:',
	'stabilization-leg' => '確認穩定版嘅設定',
	'stabilization-select' => '穩定版選擇',
	'stabilization-select1' => '最近有質素嘅修訂；如果未有，就係最近視察過嘅',
	'stabilization-select2' => '最近複審過嘅修訂',
	'stabilization-select3' => '最近原始嘅修訂；如果未有，就係最近有質素或視察過嘅',
	'stabilization-def' => '響預設版視嘅修訂顯示',
	'stabilization-def1' => '穩定修訂；如果未有，就係現時嘅',
	'stabilization-def2' => '現時嘅修訂',
	'stabilization-submit' => '確認',
	'stabilization-notexists' => '呢度係無一版係叫"[[:$1|$1]]"。
無設定可以改到。',
	'stabilization-notcontent' => '嗰版"[[:$1|$1]]"唔可以複審。
無設定可以改到。',
	'stabilization-comment' => '註解:',
	'stabilization-expiry' => '到期:',
	'stabilization-sel-short' => '優先',
	'stabilization-sel-short-0' => '質素',
	'stabilization-sel-short-1' => '無',
	'stabilization-sel-short-2' => '原始',
	'stabilization-def-short' => '預設',
	'stabilization-def-short-0' => '現時',
	'stabilization-def-short-1' => '穩定',
	'stabilize_expiry_invalid' => '無效嘅到期日。',
	'stabilize_expiry_old' => '到期日已經過咗。',
	'stabilize-expiring' => '於 $1 (UTC) 到期',
);

/** Simplified Chinese (‪中文(简体)‬)
 * @author Bencmq
 * @author Gaoxuewei
 * @author Liangent
 * @author PhiLiP
 */
$messages['zh-hans'] = array(
	'stabilization-tab' => '调查',
	'stabilization' => '稳定页面',
	'stabilization-text' => "'''更改以下的设定去调节所选择的[[:$1|$1]]之稳定版本如何显示。'''",
	'stabilization-perm' => '您的账户并没有权限去更改稳定版本设定。
这是[[:$1|$1]]当前的设定：',
	'stabilization-page' => '页面标题：',
	'stabilization-leg' => '确认稳定版本的设定',
	'stabilization-select' => '稳定版本选择',
	'stabilization-select1' => '最近有质素的修订；如果未有，则是最近视察过的',
	'stabilization-select2' => '最近复审过的修订',
	'stabilization-select3' => '最近原始的修订；如果未有，则是最近有质素或视察过的',
	'stabilization-def' => '在预设页视的修订显示',
	'stabilization-def1' => '稳定修订；如果未有，则是现时的',
	'stabilization-def2' => '现时的修订',
	'stabilization-restrict' => '自动审核限制',
	'stabilization-restrict-none' => '无其他限制',
	'stabilization-submit' => '确认',
	'stabilization-notexists' => '页面"[[:$1|$1]]"不存在。
无法进行设置。',
	'stabilization-notcontent' => '页面"[[:$1|$1]]"不能被审核。
无法进行设置。',
	'stabilization-comment' => '原因：',
	'stabilization-otherreason' => '其他原因：',
	'stabilization-expiry' => '到期：',
	'stabilization-othertime' => '其他时间：',
	'stabilization-sel-short' => '优先级',
	'stabilization-sel-short-0' => '质量',
	'stabilization-sel-short-1' => '无',
	'stabilization-sel-short-2' => '原始',
	'stabilization-def-short' => '默认',
	'stabilization-def-short-0' => '现时',
	'stabilization-def-short-1' => '稳定',
	'stabilize_expiry_invalid' => '到期日设置无效。',
	'stabilize_expiry_old' => '过期时间设置在过去了。',
	'stabilize-expiring' => '失效时间 $1 (UTC)',
);

/** Traditional Chinese (‪中文(繁體)‬)
 * @author Alexsh
 * @author Gaoxuewei
 * @author Horacewai2
 * @author Liangent
 * @author Shinjiman
 */
$messages['zh-hant'] = array(
	'stabilization-tab' => '調查',
	'stabilization' => '穩定頁面',
	'stabilization-text' => "'''更改以下的設定去調節所選擇的[[:$1|$1]]之穩定版本如何顯示。'''

注意：如果那麼沒有這樣的版本，更改出版版本設定去選擇「已審核」或是「原始」版本是不會影響的。而且，有質數的版本是已被檢查的。",
	'stabilization-perm' => '您的賬戶並沒有權限去更改穩定版本設定。
這是[[:$1|$1]]當前的設定：',
	'stabilization-page' => '頁面名稱:',
	'stabilization-leg' => '確認穩定版本的設定',
	'stabilization-select' => '穩定版本選擇',
	'stabilization-select1' => '最近有質素的修訂；如果未有，則是最近視察過的',
	'stabilization-select2' => '最近複審過的修訂',
	'stabilization-select3' => '最近原始的修訂；如果未有，則是最近有質素或視察過的',
	'stabilization-def' => '在預設頁視的修訂顯示',
	'stabilization-def1' => '穩定修訂；如果未有，則是現時或草稿',
	'stabilization-def2' => '!現時的修訂',
	'stabilization-restrict' => '自動審核限制',
	'stabilization-restrict-none' => '無其他限制',
	'stabilization-submit' => '確認',
	'stabilization-notexists' => '頁面"[[:$1|$1]]"不存在。
無法進行設置。',
	'stabilization-notcontent' => '頁面"[[:$1|$1]]"不能被審核。
無法進行設置。',
	'stabilization-comment' => '原因：',
	'stabilization-otherreason' => '其他原因：',
	'stabilization-expiry' => '到期:',
	'stabilization-othertime' => '其他時間：',
	'stabilization-sel-short' => '優先級',
	'stabilization-sel-short-0' => '質量',
	'stabilization-sel-short-1' => '無',
	'stabilization-sel-short-2' => '原始',
	'stabilization-def-short' => '預設',
	'stabilization-def-short-0' => '現時',
	'stabilization-def-short-1' => '穩定',
	'stabilize_page_invalid' => '目標頁面名稱是無效的',
	'stabilize_page_notexists' => '目標頁面不存在',
	'stabilize_page_unreviewable' => '目標頁面的名字空間不是一個需要審查的名字空間。',
	'stabilize_invalid_precedence' => '無效的修訂版本。',
	'stabilize_invalid_autoreview' => '沒有自動複查權限',
	'stabilize_invalid_level' => '無效的保護水平。',
	'stabilize_expiry_invalid' => '無效的到期日。',
	'stabilize_expiry_old' => '到期日已過。',
	'stabilize_denied' => '權限錯誤',
	'stabilize-expiring' => '於 $1 （UTC） 到期',
	'stabilization-review' => '將此當前版本標記為已查閱',
);

