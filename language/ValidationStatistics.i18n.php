<?php
/**
 * Internationalisation file for FlaggedRevs extension, section ValidationStatistics
 *
 * @addtogroup Extensions
 */

$messages = array();

$messages['en'] = array(
	'validationstatistics'        => 'Validation statistics',
	'validationstatistics-users'  => '\'\'\'{{SITENAME}}\'\'\' currently has \'\'\'[[Special:ListUsers/editor|$1]]\'\'\' {{PLURAL:$1|user|users}} with [[{{MediaWiki:Validationpage}}|Editor]] rights
and \'\'\'[[Special:ListUsers/reviewer|$2]]\'\'\' {{PLURAL:$2|user|users}} with [[{{MediaWiki:Validationpage}}|Reviewer]] rights.',
	'validationstatistics-time'   => 'The average wait for edits by \'\'users that have not logged in\'\' is \'\'\'$1\'\'\'. 
The average lag for [[Special:OldReviewedPages|outdated pages]] is \'\'\'$2\'\'\'.',
	'validationstatistics-table'  => "Statistics for each namespace are shown below, ''excluding'' redirect pages.
''Outdated'' pages are those with edits newer than the stable version.
If the stable version is also the latest version, then the page is ''synchronized''.

''Note: the following data is cached for several hours and may not be up to date.''",
	'validationstatistics-ns'     => 'Namespace',
	'validationstatistics-total'  => 'Pages',
	'validationstatistics-stable' => 'Reviewed',
	'validationstatistics-latest' => 'Latest reviewed',
	'validationstatistics-synced' => 'Synced/Reviewed',
	'validationstatistics-old'    => 'Outdated',
	'validationstatistics-nbr'    => '$1%', # only translate this message to other languages if you have to change it
);

/** Message documentation (Message documentation)
 * @author Jon Harald Søby
 * @author Raymond
 */
$messages['qqq'] = array(
	'validationstatistics-ns' => '{{Identical|Namespace}}',
	'validationstatistics-total' => '{{Identical|Pages}}',
	'validationstatistics-nbr' => 'Used for the percent numbers in the table of [http://en.wikinews.org/wiki/Special:ValidationStatistics Special:ValidationStatistics]',
);

/** Amharic (አማርኛ)
 * @author Codex Sinaiticus
 */
$messages['am'] = array(
	'validationstatistics-ns' => 'ክፍለ-ዊኪ',
);

/** Arabic (العربية)
 * @author Meno25
 */
$messages['ar'] = array(
	'validationstatistics' => 'إحصاءات التحقق',
	'validationstatistics-users' => "'''{{SITENAME}}''' لديه حاليا '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|مستخدم|مستخدم}} بصلاحيات [[{{MediaWiki:Validationpage}}|محرر]]
و '''$2''' {{PLURAL:$2|مستخدم|مستخدم}} بصلاحيات [[{{MediaWiki:Validationpage}}|مراجع]].",
	'validationstatistics-time' => "الانتظار المتوسط للتعديلات بواسطة ''المستخدمين الذين لم يسجلوا الدخول'' هو '''$1'''.  
التأخر المتوسط [[Special:OldReviewedPages|للصفحات القديمة]] هو '''$2'''.",
	'validationstatistics-table' => "الإحصاءات لكل نطاق معروضة بالأسفل، ''ولا يشمل ذلك'' صفحات التحويل.
الصفحات ''القديمة'' هي تلك ذات تعديلات أجدد من النسخة المستقرة.
لو أن النسخة المستقرة هي أيضا أحدث نسخة، فالصفحة إذا ''محدثة''.

''ملاحظة:'' البيانات التالية مخزنة لعدة ساعات وربما لا تكون محدثة.",
	'validationstatistics-ns' => 'النطاق',
	'validationstatistics-total' => 'الصفحات',
	'validationstatistics-stable' => 'مراجع',
	'validationstatistics-latest' => 'مراجع أخيرا',
	'validationstatistics-synced' => 'تم تحديثه/تمت مراجعته',
	'validationstatistics-old' => 'قديمة',
);

/** Egyptian Spoken Arabic (مصرى)
 * @author Meno25
 * @author Ramsis II
 */
$messages['arz'] = array(
	'validationstatistics' => 'إحصاءات التحقق',
	'validationstatistics-users' => "'''{{SITENAME}}''' لديه حاليا '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|مستخدم|مستخدم}} بصلاحيات [[{{MediaWiki:Validationpage}}|محرر]]
و '''$2''' {{PLURAL:$2|مستخدم|مستخدم}} بصلاحيات [[{{MediaWiki:Validationpage}}|مراجع]].",
	'validationstatistics-time' => "متوسط الانتظار فى التعديلات لـ''اليوزرات اللى ماسجلوش دخولهم'' هوه '''$1'''. 
متوسط التأخير لـ [[Special:OldReviewedPages|الصفحات القديمه]] هوه '''$2'''.",
	'validationstatistics-table' => "الاحصائيات لكل نطاق معروضه تحت ، ''من غير'' صفحات التحويل.
الصفحات ''القديمه''  هى اللى بتبقا فيها تعديلات احدث من النسخه الثابته.
لو النسخه الثابته هى نفسها اخر نسخه ، ساعتها الصفحه دى بتبقا ''متحدثه''.

''خد بالك: البيانات اللى تحت دى بتبقا متخزنه كذا ساعه و ممكن ما تكونش متحدثه.''",
	'validationstatistics-ns' => 'النطاق',
	'validationstatistics-total' => 'الصفحات',
	'validationstatistics-stable' => 'مراجع',
	'validationstatistics-latest' => 'مراجع أخيرا',
	'validationstatistics-synced' => 'تم تحديثه/تمت مراجعته',
	'validationstatistics-old' => 'قديمة',
);

/** Asturian (Asturianu)
 * @author Esbardu
 */
$messages['ast'] = array(
	'validationstatistics-ns' => 'Espaciu de nomes',
	'validationstatistics-total' => 'Páxines',
);

/** Belarusian (Taraškievica orthography) (Беларуская (тарашкевіца))
 * @author EugeneZelenko
 * @author Jim-by
 * @author Red Winged Duck
 */
$messages['be-tarask'] = array(
	'validationstatistics' => 'Статыстыка праверак',
	'validationstatistics-users' => "'''{{SITENAME}}''' цяпер налічвае '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|удзельніка|удзельнікі|удзельнікаў}} з правамі [[{{MediaWiki:Validationpage}}|«рэдактара»]] і '''$2'''  {{PLURAL:$2|удзельніка|удзельнікі|удзельнікаў}} з правамі [[{{MediaWiki:Validationpage}}|«правяраючага»]].",
	'validationstatistics-table' => "Статыстыка для кожнай прасторы назваў пададзеная ніжэй, за выключэньнем старонак-перанакіраваньняў.

'''Заўвага:''' наступныя зьвесткі кэшуюцца на некалькі гадзінаў і могуць не адпавядаць цяперашнім.",
	'validationstatistics-ns' => 'Прастора назваў',
	'validationstatistics-total' => 'Старонак',
	'validationstatistics-stable' => 'Правераных',
	'validationstatistics-latest' => 'Нядаўна правераных',
	'validationstatistics-synced' => 'Паўторна правераных',
	'validationstatistics-old' => 'Састарэлых',
);

/** Bulgarian (Български)
 * @author DCLXVI
 */
$messages['bg'] = array(
	'validationstatistics-ns' => 'Именно пространство',
	'validationstatistics-total' => 'Страници',
);

/** Bosnian (Bosanski)
 * @author CERminator
 */
$messages['bs'] = array(
	'validationstatistics-ns' => 'Imenski prostor',
	'validationstatistics-total' => 'Stranice',
);

/** Church Slavic (Словѣ́ньскъ / ⰔⰎⰑⰂⰡⰐⰠⰔⰍⰟ)
 * @author ОйЛ
 */
$messages['cu'] = array(
	'validationstatistics-total' => 'страни́цѧ',
);

/** German (Deutsch)
 * @author ChrisiPK
 * @author Melancholie
 * @author Umherirrender
 */
$messages['de'] = array(
	'validationstatistics' => 'Markierungsstatistik',
	'validationstatistics-users' => "'''{{SITENAME}}''' hat '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|Benutzer|Benutzer}} mit [[{{MediaWiki:Validationpage}}|Sichterrecht]]
und '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|Benutzer|Benutzer}} mit [[{{MediaWiki:Validationpage}}|Prüferrecht]].",
	'validationstatistics-time' => "Die durchschnittliche Wartezeit für Bearbeitungen, die von nicht angemeldeten Benutzern stammen, ist '''$1'''.
Der durchschnittliche Rückstand auf [[Special:OldReviewedPages|veraltete Seiten]] ist '''$2'''.",
	'validationstatistics-table' => "Statistiken für jeden Namensraum, ''ausgenommen'' sind Weiterleitungen.
''Veraltete'' Seiten sind Seiten mit Bearbeitungen, die neuer als die markierte Version sind.
Wenn die markierte Version auch die letzte Version ist, ist die Seite ''synchronisiert''.

''Hinweis: Die folgenden Daten werden jeweils für mehrere Stunden zwischengespeichert und sind daher nicht immer aktuell.''",
	'validationstatistics-ns' => 'Namensraum',
	'validationstatistics-total' => 'Seiten gesamt',
	'validationstatistics-stable' => 'Mindestens eine Version gesichtet',
	'validationstatistics-latest' => 'Anzahl Seiten, die in der aktuellen Version gesichtet sind',
	'validationstatistics-synced' => 'Prozentsatz an Seiten, die in der aktuellen Version gesichtet sind',
	'validationstatistics-old' => 'Seiten mit ungesichteten Versionen',
	'validationstatistics-nbr' => '$1&nbsp;%',
);

/** Lower Sorbian (Dolnoserbski)
 * @author Michawiki
 */
$messages['dsb'] = array(
	'validationstatistics' => 'Pógódnośeńska statistika',
	'validationstatistics-users' => "'''{{SITENAME}}''' ma tuchylu '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|wužywarja|wužywarjowu|wužywarjow|wužywarjow}} z [[{{MediaWiki:Validationpage}}|pšawami wobźěłowarja]]
a '''$2''' {{PLURAL:$2|wužywarja|wužywarjowu|wužywarjow|wužywarjow}} z [[{{MediaWiki:Validationpage}}|pšawami pśeglědowarja]].",
	'validationstatistics-time' => "Pśerězne cakanje za změny wót ''wužywarjow, kótarež njejsu pśizjawjone'', jo '''$1'''.
Pśerězne wokomuźenje za [[Special:OldReviewedPages|zestarjone boki]] jo '''$2'''.",
	'validationstatistics-table' => "Slěduju statistiki za kuždy mjenjowy rum, ''bźez'' dalejpósrědnjenjow. ''Zestarjone'' boki su te ze změnami, kótarež su nowše ako stabilna wersija. Jolic stabilna wersija jo teke slědna wersija, ga bok jo ''synchronizěrowany''.

''Glědaj: slědujuce daty su na někotare goźiny pufrowane a mógu togodla njeaktualne byś.''",
	'validationstatistics-ns' => 'Mjenjowy rum',
	'validationstatistics-total' => 'Boki',
	'validationstatistics-stable' => 'Pśeglědane',
	'validationstatistics-latest' => 'Tuchylu pśeglědane',
	'validationstatistics-synced' => 'Synchronizěrowane/Pśeglědane',
	'validationstatistics-old' => 'Zestarjone',
);

/** Esperanto (Esperanto)
 * @author Yekrats
 */
$messages['eo'] = array(
	'validationstatistics' => 'Validigadaj statistikoj',
	'validationstatistics-users' => "'''{{SITENAME}}''' nun havas '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|uzanton|uzantojn}} kun
[[{{MediaWiki:Validationpage}}|Revizianto]]-rajtoj
kaj '''$2''' {{PLURAL:$2|uzanton|uzantojn}} kun [[{{MediaWiki:Validationpage}}|Kontrolanto]]-rajtoj.",
	'validationstatistics-table' => "Statistikoj por ĉiu nomspaco estas jene montritaj, krom alidirektiloj.

'''Notu:''' la jenaj datenoj estas en kaŝmemoro dum multaj horoj kaj eble ne estas ĝisdataj.",
	'validationstatistics-ns' => 'Nomspaco',
	'validationstatistics-total' => 'Paĝoj',
	'validationstatistics-stable' => 'Paĝoj kun almenaŭ unu revizio',
	'validationstatistics-latest' => 'Laste reviziita',
	'validationstatistics-synced' => 'Ĝisdatigitaj/Reviziitaj',
	'validationstatistics-old' => 'Malfreŝaj',
);

/** Spanish (Español)
 * @author Crazymadlover
 * @author Imre
 */
$messages['es'] = array(
	'validationstatistics' => 'Estadísticas de validación',
	'validationstatistics-users' => "'''{{SITENAME}}''' actualmente hay '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|usuario|usuarios}} con derechos de [[{{MediaWiki:Validationpage}}|Editor]] y '''$2''' {{PLURAL:$2|usuario|usuarios}} con derechos de [[{{MediaWiki:Validationpage}}|Revisor]].",
	'validationstatistics-table' => "Estadísticas para cada nombre de sitio son mostradas debajo, ''excluyendo'' páginas de redireccionamiento.
''desactualizada'' aquellas páginas con ediciones más nuevas que la versión estable.
Sila versión estable es también la última versión, entonces la página está ''sincronizada''.
'''Nota:''' los siguientes datos son almacenados por varias horas y pueden no estar actualizados.",
	'validationstatistics-ns' => 'Espacio de nombres',
	'validationstatistics-total' => 'Páginas',
	'validationstatistics-stable' => 'Revisado',
	'validationstatistics-latest' => 'El último revisado',
	'validationstatistics-synced' => 'Sincronizado/Revisado',
	'validationstatistics-old' => 'desactualizado',
);

/** Basque (Euskara)
 * @author Kobazulo
 */
$messages['eu'] = array(
	'validationstatistics' => 'Balioztatzeko estatistikak',
	'validationstatistics-total' => 'Orrialdeak',
	'validationstatistics-old' => 'Deseguneratua',
);

/** Persian (فارسی)
 * @author Huji
 */
$messages['fa'] = array(
	'validationstatistics' => 'آمار معتبرسازی',
	'validationstatistics-users' => "'''{{SITENAME}}''' در حال حاضر '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|کاربر|کاربر}} با اختیارات [[{{MediaWiki:Validationpage}}|ویرایشگر]] و '''$2''' {{PLURAL:$2|کاربر|کاربر}} با اختیارات[[{{MediaWiki:Validationpage}}|مرورگر]] دارد.",
	'validationstatistics-table' => "'''نکته:''' داده‌هایی که در ادامه می‌آید برای چندین ساعت در میان‌گیر ذخیره شده‌اند و ممکن است به روز نباشند.",
	'validationstatistics-ns' => 'فضای نام',
	'validationstatistics-total' => 'صفحه‌ها',
	'validationstatistics-stable' => 'بازبینی شده',
	'validationstatistics-latest' => 'آخرین بازبینی',
	'validationstatistics-synced' => 'به روز شده/بازبینی شده',
	'validationstatistics-old' => 'تاریخ گذشته',
);

/** Finnish (Suomi)
 * @author Crt
 * @author Str4nd
 * @author Vililikku
 */
$messages['fi'] = array(
	'validationstatistics' => 'Validointitilastot',
	'validationstatistics-ns' => 'Nimiavaruus',
	'validationstatistics-total' => 'Sivut',
	'validationstatistics-stable' => 'Arvioitu',
	'validationstatistics-old' => 'Vanhentunut',
);

/** French (Français)
 * @author Grondin
 * @author IAlex
 * @author McDutchie
 * @author Verdy p
 * @author Zetud
 */
$messages['fr'] = array(
	'validationstatistics' => 'Statistiques de validation',
	'validationstatistics-users' => "'''{{SITENAME}}''' dispose actuellement de '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|utilisateur|utilisateurs}} avec les droits d’[[{{MediaWiki:Validationpage}}|éditeur]] et de '''$2''' {{PLURAL:$2|utilisateur|utilisateurs}} avec les droits de [[{{MediaWiki:Validationpage}}|relecteur]].",
	'validationstatistics-time' => "Le temps moyen pour les modifications faites par ''des utilisateurs qui ne sont pas connectés'' est de '''$1'''.
Le temps de retard moyen des [[Special:OldReviewedPages|pages obsolètes]] est de '''$2'''.",
	'validationstatistics-table' => "Les statistiques pour chaque espace de nom sont affichées ci-dessous, à ''l’exclusion'' des pages de redirection.
Les pages ''dépassées'' sont celles avec des modifications plus récente que la version stable.
Si la version stable est la dernière version, alors la page est ''synchronisée''.

''Note : les données suivantes sont cachées pendant plusieurs heures et ne peuvent pas être mises à jour.''",
	'validationstatistics-ns' => 'Espace de noms',
	'validationstatistics-total' => 'Pages',
	'validationstatistics-stable' => 'Relu',
	'validationstatistics-latest' => 'Relu en tout dernier lieu',
	'validationstatistics-synced' => 'Synchronisé/Relu',
	'validationstatistics-old' => 'Désuet',
	'validationstatistics-nbr' => '$1&nbsp;%',
);

/** Irish (Gaeilge)
 * @author Alison
 */
$messages['ga'] = array(
	'validationstatistics-ns' => 'Ainmspás',
	'validationstatistics-total' => 'Leathanaigh',
);

/** Galician (Galego)
 * @author Toliño
 */
$messages['gl'] = array(
	'validationstatistics' => 'Estatísticas de validación',
	'validationstatistics-users' => "Actualmente, '''{{SITENAME}}''' ten '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|usuario|usuarios}} con
dereitos de [[{{MediaWiki:Validationpage}}|editor]]
e '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|usuario|usuarios}} con dereitos de [[{{MediaWiki:Validationpage}}|revisor]].",
	'validationstatistics-time' => "O promedio de espera para as edicións dos ''non-usuarios'' é de '''$1'''.  
O promedio de retraso para as [[Special:OldReviewedPages|páxinas obsoletas]] é de '''$2'''.",
	'validationstatistics-table' => "Emabixo amósanse as estatísticas para cada espazo de nomes, ''excluíndo'' as páxinas de redirección. As páxinas ''obsoletas'' son aquelas que teñen edicións máis novas cá versión estábel. Se a versión estábel é tamén a última versión, a páxina está entón ''sincronizada''.

''Nota: os seguintes datos están na memoria caché durante varias horas e poden non estar actualizados.''",
	'validationstatistics-ns' => 'Espazo de nomes',
	'validationstatistics-total' => 'Páxinas',
	'validationstatistics-stable' => 'Revisado',
	'validationstatistics-latest' => 'Última revisión',
	'validationstatistics-synced' => 'Sincronizado/Revisado',
	'validationstatistics-old' => 'Anticuado',
);

/** Ancient Greek (Ἀρχαία ἑλληνικὴ)
 * @author Crazymadlover
 * @author Omnipaedista
 */
$messages['grc'] = array(
	'validationstatistics' => 'Στατιστικὰ ἐπικυρώσεων',
	'validationstatistics-users' => "Τὸ '''{{SITENAME}}''' νῦν ἔχει '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|χρὠμενον|χρωμένους}} μετὰ δικαιωμάτων [[{{MediaWiki:Validationpage}}|μεταγραφέως]] καὶ '''$2''' {{PLURAL:$2|χρὠμενον|χρωμένους}} μετὰ δικαιωμάτων [[{{MediaWiki:Validationpage}}|ἐπιθεωρητοῦ]].",
	'validationstatistics-table' => "Στατιστικὰ δεδομένα διὰ πᾶν ὀνοματεῖον κάτωθι εἰσί, δέλτων ἀναδιευθύνσεως ''ἐξαιρουμένων''.
''Μὴ ἐνημερωμέναι'' δέλτοι εἰσὶ αἱ δέλτοι αἱ ἔχουσαι μεταγραφἀς ὀλιγωτέρας τῆς σταθερᾶς ἐκδόσεως.
Εἰ ἡ σταθερὰ ἔκδοσις ἐστὶ ταυτοχρόνως ἡ πλέον πρόσφατος, ὅτε ἥδε θεωρεῖται ''συνεχρονισμένη''.

''Σημείωσις:'' τὰ ἀκόλουθα δεδομένα κρυπτὰ εἰσὶ ἐπὶ ὥρας τινὰς καὶ ἐνδεχομένως μὴ ἐνήμερα εἰσί.''",
	'validationstatistics-ns' => 'Ὀνοματεῖον',
	'validationstatistics-total' => 'Δέλτοι',
	'validationstatistics-stable' => 'Ἀναθεωρημένη',
	'validationstatistics-latest' => 'Ὑστάτη ἀναθεωρημένη',
	'validationstatistics-synced' => 'Συγχρονισμένη/Ἐπιθεωρημένη',
	'validationstatistics-old' => 'Ἀπηρχαιωμένη',
);

/** Swiss German (Alemannisch)
 * @author Als-Holder
 */
$messages['gsw'] = array(
	'validationstatistics' => 'Markierigsstatischtik',
	'validationstatistics-users' => "{{SITENAME}} het '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|Benutzer|Benutzer}} mit [[{{MediaWiki:Validationpage}}|Sichterrächt]] un '''$2''' {{PLURAL:$2|Benutzer|Benutzer}} mit [[{{MediaWiki:Validationpage}}|Prieferrächt]].",
	'validationstatistics-time' => "Di durschnittlig Wartezyt fir Bearbeitige, wu nit vum Benutzer stamme, isch '''$1'''.
Dr durschnittlig Ruckstand uf [[Special:OldReviewedPages|veralteti Syten]] isch '''$2'''.",
	'validationstatistics-table' => "Statischtike fir jede Namensruum, dervu ''usgnuu'' sin Wyterleitige. ''Veralteti'' Syte sin diejenige mit Bearbeitige, wu nejer sin wie di aagluegt Version. Wänn di aagluegt Version au di letscht Version isch, no isch d Syte ''zytglych''.

'''Wichtig:''' Die Date wäre als fir e paar Stund in Zwischespicher abglait und sin wäg däm vilicht nid alliwyl aktuäll.",
	'validationstatistics-ns' => 'Namensruum',
	'validationstatistics-total' => 'Syte insgsamt',
	'validationstatistics-stable' => 'Zmindescht ei Version isch vum Fäldhieter gsäh.',
	'validationstatistics-latest' => 'Syte, wu di letscht Version vum Fäldhieter gsäh isch.',
	'validationstatistics-synced' => 'Prozäntsatz vu dr Syte, wu vum Fäldhieter gsäh sin.',
	'validationstatistics-old' => 'Syte mit Versione, wu nit vum Fäldhieter gsäh sin.',
);

/** Hebrew (עברית)
 * @author Agbad
 * @author DoviJ
 * @author Erel Segal
 * @author Rotemliss
 */
$messages['he'] = array(
	'validationstatistics' => 'סטיסטיקת אישורים',
	'validationstatistics-users' => "'''יש כרגע {{PLURAL:$1|משתמש '''[[Special:ListUsers/editor|אחד]]'''|'''[[Special:ListUsers/editor|$1]]''' משתמשים}} ב{{SITENAME}} עם הרשאת [[{{MediaWiki:Validationpage}}|עורך]] ו{{PLURAL:$2|משתמש '''[[Special:ListUsers/reviewer|אחד]]'''|־'''[[Special:ListUsers/reviewer|$2]]''' משתמשים}} עם הרשאת [[{{MediaWiki:Validationpage}}|בודק דפים]].'''",
	'validationstatistics-time' => "ההמתנה הממוצעת עבור עריכות של ''משתמשים שלא נכנסו לחשבון'' היא '''$1'''. 
ההמתנה הממוצעת עבור [[Special:OldReviewedPages|דפים בדוקים ישנים]] היא '''$2'''.",
	'validationstatistics-table' => "סטטיסטיקות לכל מרחב שם מוצגות להלן, תוך '''התעלמות''' מדפי הפניה.
דפים '''ישנים''' הם אלה עם עריכות חדשות יותר מהגרסה היציבה.
אם הגרסה היציבה היא גם הגרסה האחרונה, הדף '''מסונכרן'''.

'''הערה: הנתונים הבאים נשמרים למשך כמה שעות, וייתכן שאינם עדכניים.'''",
	'validationstatistics-ns' => 'מרחב שם',
	'validationstatistics-total' => 'דפים',
	'validationstatistics-stable' => 'עבר ביקורת',
	'validationstatistics-latest' => 'בדיקות אחרונות',
	'validationstatistics-synced' => 'סונכרנו/נבדקו',
	'validationstatistics-old' => 'פג תוקף',
);

/** Croatian (Hrvatski)
 * @author Dalibor Bosits
 */
$messages['hr'] = array(
	'validationstatistics' => 'Statistika pregledavanja',
	'validationstatistics-ns' => 'Imenski prostor',
	'validationstatistics-total' => 'Stranice',
	'validationstatistics-stable' => 'Ocijenjeno',
	'validationstatistics-latest' => 'Nedavno ocijenjeno',
	'validationstatistics-synced' => 'Usklađeno/Ocijenjeno',
	'validationstatistics-old' => 'Zastarjelo',
);

/** Upper Sorbian (Hornjoserbsce)
 * @author Michawiki
 */
$messages['hsb'] = array(
	'validationstatistics' => 'Pohódnoćenska statistika',
	'validationstatistics-users' => "'''{{SITENAME}}''' ma tuchwilu '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|wužiwarja|wužiwarjow|wužiwarjow|wužiwarjow}} z [[{{MediaWiki:Validationpage}}|prawami wobdźěłowarja]]
a '''$2''' {{PLURAL:$2|wužiwarja|wužiwarjow|wužiwarjow|wužiwarjow}} z [[{{MediaWiki:Validationpage}}|prawami kontrolera]].",
	'validationstatistics-time' => "Přerězne čakanje za změny wot ''wužiwarjow, kotřiž njejsu přizjewjeni'', je '''$1'''.
Přerězne komdźenje za [[Special:OldReviewedPages|zestarjene strony]] je '''$2'''.",
	'validationstatistics-table' => "Slěduja statistiki za kóždy mjenowy rum ''bjez'' daleposrědkowanjow. ''Zestarjene'' strony su te ze změnami, kotrež su nowše hač stabilna wersija. Jeli stabilna wersija je tež poslednja wersija, to strona je ''sychronizowana''.

''Kedźbu: slědowace daty su za někotre hodźiny pufrowane a móžeja njeaktualne być.''",
	'validationstatistics-ns' => 'Mjenowy rum',
	'validationstatistics-total' => 'Strony',
	'validationstatistics-stable' => 'Skontrolowane',
	'validationstatistics-latest' => 'Poslednje skontrolowane',
	'validationstatistics-synced' => 'Synchronizowane/Skontrolowane',
	'validationstatistics-old' => 'Zestarjene',
);

/** Hungarian (Magyar)
 * @author Bdamokos
 * @author Dani
 * @author Samat
 */
$messages['hu'] = array(
	'validationstatistics' => 'Ellenőrzési statisztika',
	'validationstatistics-users' => "A(z) '''{{SITENAME}}''' wikinek jelenleg '''{{PLURAL:$1|egy|$1}}''' [[{{MediaWiki:Validationpage}}|járőrjoggal]], valamint '''{{PLURAL:$2|egy|$2}}''' [[{{MediaWiki:Validationpage}}|lektorjoggal]] rendelkező szerkesztője van.",
	'validationstatistics-table' => "Lent a névterekre bontott statisztika látható, az átirányítások nincsenek beleszámolva.

'''Megjegyzés:''' az adatok néhány órás időközönként gyorsítótárazva vannak, így nem feltétlenül pontosak.",
	'validationstatistics-ns' => 'Névtér',
	'validationstatistics-total' => 'Oldalak',
	'validationstatistics-stable' => 'Ellenőrzött',
	'validationstatistics-latest' => 'Legutóbb ellenőrzött',
	'validationstatistics-synced' => 'Szinkronizálva/ellenőrizve',
	'validationstatistics-old' => 'Elavult',
);

/** Interlingua (Interlingua)
 * @author McDutchie
 */
$messages['ia'] = array(
	'validationstatistics' => 'Statisticas de validation',
	'validationstatistics-users' => "'''{{SITENAME}}''' ha al momento '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|usator|usatores}} con derectos de [[{{MediaWiki:Validationpage}}|Contributor]] e '''$2''' {{PLURAL:$2|usator|usatores}} con derectos de [[{{MediaWiki:Validationpage}}|Revisor]].",
	'validationstatistics-table' => "Le statisticas pro cata spatio de nomines es monstrate infra, excludente le paginas de redirection.

'''Nota:''' le sequente datos es extrahite de un copia ''cache'' del base de datos, non actualisate in tempore real.",
	'validationstatistics-ns' => 'Spatio de nomines',
	'validationstatistics-total' => 'Paginas',
	'validationstatistics-stable' => 'Revidite',
	'validationstatistics-latest' => 'Ultime revidite',
	'validationstatistics-synced' => 'Synchronisate/Revidite',
	'validationstatistics-old' => 'Obsolete',
	'validationstatistics-nbr' => '$1%',
);

/** Indonesian (Bahasa Indonesia)
 * @author Rex
 */
$messages['id'] = array(
	'validationstatistics' => 'Statistik validasi',
	'validationstatistics-users' => "'''{{SITENAME}}''' saat ini memiliki '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|pengguna|pengguna}} dengan hak akses [[{{MediaWiki:Validationpage}}|Editor]] dan
'''$2''' {{PLURAL:$2|pengguna|pengguna}} dengan hak akses [[{{MediaWiki:Validationpage}}|Peninjau]].",
	'validationstatistics-table' => "Statistik untuk setiap ruang nama ditampilkan di bawah ini, kecuali halaman pengalihan.

'''Catatan''': Data di bawah ini diambil dari tembolok beberapa jam yang lalu dan mungkin belum mencakup data terbaru.",
	'validationstatistics-ns' => 'Ruang nama',
	'validationstatistics-total' => 'Halaman',
	'validationstatistics-stable' => 'Telah ditinjau',
	'validationstatistics-latest' => 'Terakhir ditinjau',
	'validationstatistics-synced' => 'Sinkron/Tertinjau',
	'validationstatistics-old' => 'Usang',
);

/** Italian (Italiano)
 * @author Darth Kule
 */
$messages['it'] = array(
	'validationstatistics' => 'Statistiche di convalidazione',
	'validationstatistics-users' => "Al momento, su '''{{SITENAME}}''' {{PLURAL:$1|c'è '''[[Special:ListUsers/editor|$1]]''' utente|ci sono '''[[Special:ListUsers/editor|$1]]''' utenti}} con i diritti di [[{{MediaWiki:Validationpage}}|Editore]] e '''$2''' {{PLURAL:$2|utente|utenti}} con i diritti di [[{{MediaWiki:Validationpage}}|Revisore]].",
	'validationstatistics-table' => "Le statistiche per ciascun namespace sono mostrate di seguito, ''a esclusione'' delle pagine di redirect. Le pagine ''non aggiornate'' sono quelle con edit più recenti della versione stabile. Se la versione stabile è anche la più recente alla la pagina è ''sincronizzata''.

''Nota: i dati che seguono sono estratti da una copia ''cache'' del database, non aggiornati in tempo reale.''",
	'validationstatistics-ns' => 'Namespace',
	'validationstatistics-total' => 'Pagine',
	'validationstatistics-stable' => 'Revisionate',
	'validationstatistics-latest' => 'Ultime revisionate',
	'validationstatistics-old' => 'Non aggiornate',
);

/** Japanese (日本語)
 * @author Fryed-peach
 * @author Hosiryuhosi
 */
$messages['ja'] = array(
	'validationstatistics' => '判定統計',
	'validationstatistics-users' => "'''{{SITENAME}}''' には現在、[[{{MediaWiki:Validationpage}}|編集者]]権限をもつ利用者が '''[[Special:ListUsers/editor|$1]]'''人、[[{{MediaWiki:Validationpage}}|査読者]]権限をもつ利用者が '''$2'''人います。",
	'validationstatistics-time' => "未登録利用者による編集の平均待ち時間は '''$1'''です。
[[Special:OldReviewedPages|古くなったページ]]の平均遅延時間は '''$2'''です。",
	'validationstatistics-table' => "名前空間別の統計を以下に表示します。リダイレクトページは除いています。「最新版未査読」とは安定版以降に編集があったものです。安定版がまた最新版である場合、そのページは「最新版査読済」となります。

'''注:''' データは数時間ほどキャッシュされるため、以下は最新のものではない可能性があります。",
	'validationstatistics-ns' => '名前空間',
	'validationstatistics-total' => 'ページ数',
	'validationstatistics-stable' => '査読済',
	'validationstatistics-latest' => '最新版査読済',
	'validationstatistics-synced' => '最新版査読済/全査読済',
	'validationstatistics-old' => '最新版未査読',
);

/** Javanese (Basa Jawa)
 * @author Pras
 */
$messages['jv'] = array(
	'validationstatistics' => 'Statistik validasi',
	'validationstatistics-users' => "'''{{SITENAME}}''' wektu iki nduwé '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|panganggo|panganggo}} kanthi hak aksès [[{{MediaWiki:Validationpage}}|Editor]] lan '''$2''' {{PLURAL:$2|panganggo|panganggo}} kanthi hak aksès [[{{MediaWiki:Validationpage}}|Pamriksa]].",
	'validationstatistics-table' => "Statistik kanggo saben bilik jeneng ditampilaké ing ngisor, kajaba kaca pangalihan.

'''Cathetan''': Data ing ngisor dijupuk saka ''cache'' sawetara jam kapungkur lan mbokmanawa ora cocog manèh.",
	'validationstatistics-ns' => 'Bilik jeneng',
	'validationstatistics-total' => 'Kaca',
	'validationstatistics-stable' => 'Wis dipriksa',
	'validationstatistics-latest' => 'Pungkasan dipriksa',
	'validationstatistics-synced' => 'Wis disinkronaké/Wis dipriksa',
	'validationstatistics-old' => 'Lawas',
	'validationstatistics-nbr' => '$1%',
);

/** Khmer (ភាសាខ្មែរ)
 * @author Lovekhmer
 * @author Thearith
 */
$messages['km'] = array(
	'validationstatistics-ns' => 'លំហឈ្មោះ',
	'validationstatistics-total' => 'ទំព័រ',
	'validationstatistics-old' => 'ហួសសម័យ',
);

/** Korean (한국어)
 * @author Kwj2772
 */
$messages['ko'] = array(
	'validationstatistics-users' => "'''{{SITENAME}}'''에는 $1명의 [[{{MediaWiki:Validationpage}}|편집자]] 권한을 가진 사용자와 $2명의 [[{{MediaWiki:Validationpage}}|평론가]] 권한을 가진 사용자가 있습니다.",
	'validationstatistics-table' => "넘겨주기 문서를 '''제외한''' 문서의 검토 통계가 이름공간별로 보여지고 있습니다.
'''오래 된 문서'''는 안정 버전 이후에 편집이 있는 문서를 의미합니다.
안정 버전이 마지막 버전이라면, 문서는 동기화될 것입니다.

'''참고:''' 다음 데이터는 몇 시간마다 캐시되며 최신이 아닐 수도 있습니다.",
	'validationstatistics-ns' => '이름공간',
);

/** Ripoarisch (Ripoarisch)
 * @author Purodha
 */
$messages['ksh'] = array(
	'validationstatistics-users' => " De '''{{SITENAME}}''' hät em Momang {{PLURAL:$1|'''eine''' Metmaacher|'''$1''' Metmaachere|'''keine''' Metmaacher}} met Rääsch, ene [[{{MediaWiki:Validationpage}}|Editor]] ze maache, un {{PLURAL:$2|'''eine''' Metmaacher|'''$2''' Metmaacher|'''keine''' Metmaacher}} met däm [[{{MediaWiki:Validationpage}}|Reviewer]]-Rääsch.",
	'validationstatistics-time' => "Die Dorschnitt för de Zick op Änderunge vun Namelose ze Waade, es '''$1'''.
Der Dorschnitt vun de Zick, wo [[Special:OldReviewedPages|ahl Sigge]] hengerher hingke, es '''$2'''.",
	'validationstatistics-table' => "Statistike för jedes Appachtemang (oohne de Sigge met Ömleijdunge)

'''Opjepaß:''' De Date hee noh sen för e paa Stond zweschespeichert, se künnte alsu nit janz de neuste sin.",
	'validationstatistics-ns' => 'Appachtemang',
	'validationstatistics-total' => 'Sigge jesamp',
	'validationstatistics-nbr' => '$1%',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 */
$messages['lb'] = array(
	'validationstatistics' => 'Statistike vun de Validaiounen',
	'validationstatistics-users' => "''{{SITENAME}}''' huet elo '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|Benotzer|Benotzer}} mat [[{{MediaWiki:Validationpage}}|Editeursrechter]]
an '''$2''' {{PLURAL:$2|Benotzer|Benotzer}} mat [[{{MediaWiki:Validationpage}}|Validatiounsrechter]].",
	'validationstatistics-table' => "Statistike fir jidfer Nummraum sinn hei ënnedrënner, Viruleedungssäite sinn net berücksichtegt.
''Outdated''-Säiten sinn déi déi mat Ännerungen déi méi nei sinn wéi déi stabil Versioun.
Wann déi stabil Versioun och déi lescht Versioun ass, dann ass d'Säit ''synchroniséiert''.

'''Bemierkung:''' d'Donnéeën gi jeweils fir e puer Stonnen tësche gespäichert a sin dofir net ëmmer aktuell.",
	'validationstatistics-ns' => 'Nummraum',
	'validationstatistics-total' => 'Säiten',
	'validationstatistics-stable' => 'Validéiert',
);

/** Macedonian (Македонски)
 * @author Brest
 */
$messages['mk'] = array(
	'validationstatistics' => 'Валидациски статистики',
	'validationstatistics-users' => "'''{{SITENAME}}''' во моментов има '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|корисник|корисници}} со [[{{MediaWiki:Validationpage}}|уредувачки]] права и '''$2''' {{PLURAL:$2|корисник|корисници}} со [[{{MediaWiki:Validationpage}}|оценувачки]] права.",
	'validationstatistics-table' => "Статистики за секој именски простор се прикажани подолу (без страници за пренасочување).
'''Забелешка:''' следниве податоци се кеширани пред неколку часа и можеби не се баш најажурни.",
	'validationstatistics-ns' => 'Именски простор',
	'validationstatistics-total' => 'Страници',
	'validationstatistics-stable' => 'Прегледани',
	'validationstatistics-latest' => 'Последно прегледување',
	'validationstatistics-synced' => 'Синхронизирани/Прегледани',
	'validationstatistics-old' => 'Застарени',
);

/** Malayalam (മലയാളം)
 * @author Sadik Khalid
 */
$messages['ml'] = array(
	'validationstatistics' => 'സ്ഥിരീകരണ കണക്കുകള്‍',
	'validationstatistics-users' => "'''{{SITENAME}}''' പദ്ധതിയില്‍ '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|ഉപയോക്താവ്|ഉപയോക്താക്കള്‍}} [[{{MediaWiki:Validationpage}}|സംശോധകര്‍]] അധികാരമുള്ളവരും '''$2''' {{PLURAL:$2|ഉപയോക്താവ്|ഉപയോക്താക്കള്‍}} [[{{MediaWiki:Validationpage}}|പരിശോധകര്‍]] അധികാരമുള്ളവരും നിലവിലുണ്ട്.",
	'validationstatistics-ns' => 'നാമമേഖല',
	'validationstatistics-total' => 'താളുകള്‍',
	'validationstatistics-stable' => 'പരിശോധിച്ചവ',
	'validationstatistics-latest' => 'ഒടുവില്‍ പരിശോധിച്ചവ',
	'validationstatistics-synced' => 'ഏകകാലികമാക്കിയവ/പരിശോധിച്ചവ',
	'validationstatistics-old' => 'കാലഹരണപ്പെട്ടവ',
);

/** Malay (Bahasa Melayu)
 * @author Aviator
 */
$messages['ms'] = array(
	'validationstatistics' => 'Statistik pengesahan',
	'validationstatistics-users' => "'''{{SITENAME}}''' kini mempunyai {{PLURAL:$1|seorang|'''[[Special:ListUsers/editor|$1]]''' orang}} pengguna dengan hak [[{{MediaWiki:Validationpage}}|Penyunting]] dan {{PLURAL:$2|seorang|'''$2''' orang}} pengguna dengan hak [[{{MediaWiki:Validationpage}}|Pemeriksa]].",
	'validationstatistics-table' => "Yang berikut ialah statistik bagi setiap ruang nama, tidak termasuk laman lencongan.

'''Catatan:''' data berikut diambil daripada cache yang disimpan sejak beberapa jam yang lalu dan kemungkinan besar bukan yang terkini.",
	'validationstatistics-ns' => 'Ruang nama',
	'validationstatistics-total' => 'Laman',
	'validationstatistics-stable' => 'Diperiksa',
	'validationstatistics-latest' => 'Pemeriksaan terakhir',
);

/** Erzya (Эрзянь)
 * @author Botuzhaleny-sodamo
 */
$messages['myv'] = array(
	'validationstatistics-ns' => 'Лем потмо',
);

/** Dutch (Nederlands)
 * @author Siebrand
 */
$messages['nl'] = array(
	'validationstatistics' => 'Eindredactiestatistieken',
	'validationstatistics-users' => "'''{{SITENAME}}''' heeft op het moment '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|gebruiker|gebruikers}} in de rol van [[{{MediaWiki:Validationpage}}|Redacteur]] en '''$2''' {{PLURAL:$2|gebruiker|gebruikers}} met de rol [[{{MediaWiki:Validationpage}}|Eindredacteur]].",
	'validationstatistics-time' => "De gemiddelde wachttijd voor bewerkingen door ''gebruikers die niet aangemeld zijn'' is '''$1'''.
De gemiddelde achterstand voor [[Special:OldReviewedPages|verouderde pagina's]] is '''$2'''.",
	'validationstatistics-table' => "Hieronder staan statistieken voor iedere naamruimte, ''exclusief'' doorverwijzingen.
''Verouderde'' pagina's zijn pagina's waarvoor bewerkingen zijn gemaakt na het markeren van de stabiele versie.
Als een stabiele versie ook de laatste versie is, dan is de pagina ''gesynchroniseerd''.

''Let op: de onderstaande gegevens komen uit een cache en kunnen tot enkele uren oud zijn.''",
	'validationstatistics-ns' => 'Naamruimte',
	'validationstatistics-total' => "Pagina's",
	'validationstatistics-stable' => 'Eindredactie afgerond',
	'validationstatistics-latest' => 'Meest recente eindredacties',
	'validationstatistics-synced' => 'Gesynchroniseerd/Eindredactie',
	'validationstatistics-old' => 'Verouderd',
);

/** Norwegian Nynorsk (‪Norsk (nynorsk)‬)
 * @author Harald Khan
 */
$messages['nn'] = array(
	'validationstatistics' => 'Valideringsstatistikk',
	'validationstatistics-users' => "'''{{SITENAME}}''' har på noverande tidspunkt {{PLURAL:$1|'''éin''' brukar|'''[[Special:ListUsers/editor|$1]]''' brukarar}} med [[{{MediaWiki:Validationpage}}|skribentrettar]] og {{PLURAL:$1|'''éin''' brukar|'''$2''' brukarar}} med [[{{MediaWiki:Validationpage}}|meldarrettar]].",
	'validationstatistics-time' => "Gjennomsnittleg ventetid for endringar av ''uinnlogga brukarar'' er '''$1'''.
Gjennomsnittleg forseinking for [[Special:OldReviewedPages|utdaterte sider]] er '''$2'''.",
	'validationstatistics-table' => "Statistikk for kvart namnerom er synt nedanfor, ''utanom'' omdirigeringssider.

''Forelda'' sider er dei som har endringar nyare enn den stabile versjonen.
Om den stabile versjonen òg er den siste versjonen, er sida ''synkronisert''.

''Merk: Fylgjande data vert mellomlagra for fleire timar og kan vera forelda.''",
	'validationstatistics-ns' => 'Namnerom',
	'validationstatistics-total' => 'Sider',
	'validationstatistics-stable' => 'Vurdert',
	'validationstatistics-latest' => 'Sist vurdert',
	'validationstatistics-synced' => 'Synkronisert/Vurdert',
	'validationstatistics-old' => 'Utdatert',
);

/** Norwegian (bokmål)‬ (‪Norsk (bokmål)‬)
 * @author Jon Harald Søby
 */
$messages['no'] = array(
	'validationstatistics' => 'Valideringsstatistikk',
	'validationstatistics-users' => "'''{{SITENAME}}''' har '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|bruker|brukere}} med [[{{MediaWiki:Validationpage}}|skribentrettigheter]] og '''$2''' {{PLURAL:$2|bruker|brukere}} med [[{{MediaWiki:Validationpage}}|anmelderrettigheter]].",
	'validationstatistics-table' => "Statistikk for hvert navnerom vises nedenfor, utenom omdirigeringssider. ''Utdaterte'' sider er sider som or blitt endret siden siste stabile versjon. Om siste endring også er stabil er siden ''à jour''.

'''Merk:''' Følgende data mellomlagres i flere timer og kan være foreldet.",
	'validationstatistics-ns' => 'Navnerom',
	'validationstatistics-total' => 'Sider',
	'validationstatistics-stable' => 'Anmeldt',
	'validationstatistics-latest' => 'Sist anmeldt',
	'validationstatistics-synced' => 'Synkronisert/Anmeldt',
	'validationstatistics-old' => 'Foreldet',
);

/** Occitan (Occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'validationstatistics' => 'Estatisticas de validacion',
	'validationstatistics-users' => "'''{{SITENAME}}''' dispausa actualament de '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|utilizaire|utilizaires}} amb los dreches d’[[{{MediaWiki:Validationpage}}|editor]] e de '''$2''' {{PLURAL:$2|utilizaire|utilizaires}} amb los dreches de [[{{MediaWiki:Validationpage}}|relector]].",
	'validationstatistics-time' => "Lo temps mejan per las modificacions fachas per ''d'utilizaires que son pas connectats'' es de '''$1'''.
Lo temps de retard mejan de las [[Special:OldReviewedPages|paginas obsolètas]] es de '''$2'''.",
	'validationstatistics-table' => "Las estatisticas per cada espaci de nom son afichadas çaijós, a l’exclusion de las paginas de redireccion.
Las paginas ''despassadas'' son las amb de modificacions mai recenta que la version establa.
Se la version establa es la darrièra version, alara la pagina es ''sincronizada''.

'''Nòta :''las donadas seguentas son amagadas pendent maitas oras e pòdon pas èsser mesas a jorn.",
	'validationstatistics-ns' => 'Nom de l’espaci',
	'validationstatistics-total' => 'Paginas',
	'validationstatistics-stable' => 'Relegit',
	'validationstatistics-latest' => 'Relegit en tot darrièr luòc',
	'validationstatistics-synced' => 'Sincronizat/Relegit',
	'validationstatistics-old' => 'Desuet',
);

/** Polish (Polski)
 * @author Jwitos
 * @author Leinad
 * @author Sp5uhe
 * @author Wpedzich
 */
$messages['pl'] = array(
	'validationstatistics' => 'Statystyki oznaczania',
	'validationstatistics-users' => "W '''{{GRAMMAR:MS.lp|{{SITENAME}}}}''' zarejestrowanych jest obecnie  '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|użytkownik|użytkowników}} z uprawnieniami [[{{MediaWiki:Validationpage}}|redaktora]] oraz  '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|użytkownik|użytkowników}} z uprawnieniami [[{{MediaWiki:Validationpage}}|weryfikatora]].",
	'validationstatistics-time' => "Średni czas oczekiwania edycji wykonanych przez ''niezalogowanych użytkowników'' wynosi '''$1'''.
Średnie opóźnienie dla [[Special:OldReviewedPages|zdezaktualizowanych stron]] wynosi '''$2'''.",
	'validationstatistics-table' => "Poniżej znajdują się statystyki dla każdej przestrzeni nazw, ''z wyłączeniem'' przekierowań.
''Zdezaktualizowane'' strony to takie, których najnowsza wersja nie została oznaczona.
Jeśli najnowsza wersja strony jest wersją oznaczoną, wtedy strona jest ''zsynchronizowana''.

'''Uwaga:''' poniższe dane są kopią z pamięci podręcznej sprzed nawet kilku godzin, mogą więc być nieaktualne.",
	'validationstatistics-ns' => 'Przestrzeń nazw',
	'validationstatistics-total' => 'Stron',
	'validationstatistics-stable' => 'Przejrzanych',
	'validationstatistics-latest' => 'Z ostatnią edycją oznaczoną jako przejrzana',
	'validationstatistics-synced' => 'Zsynchronizowanych lub przejrzanych',
	'validationstatistics-old' => 'Zdezaktualizowane',
);

/** Pashto (پښتو)
 * @author Ahmed-Najib-Biabani-Ibrahimkhel
 */
$messages['ps'] = array(
	'validationstatistics-ns' => 'نوم-تشيال',
	'validationstatistics-total' => 'مخونه',
);

/** Portuguese (Português)
 * @author 555
 * @author Malafaya
 * @author Waldir
 */
$messages['pt'] = array(
	'validationstatistics' => 'Estatísticas de validações',
	'validationstatistics-users' => "'''{{SITENAME}}''' possui, no momento, '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|utilizador|utilizadores}} com privilégios de [[{{MediaWiki:Validationpage}}|{{int:group-editor-member}}]] e '''$2''' {{PLURAL:$2|utilizador|utilizadores}} com privilégios de [[{{MediaWiki:Validationpage}}|{{int:group-reviewer-member}}]].",
	'validationstatistics-time' => "O tempo médio de espera de edições por ''utilizadores não registrados'' é '''$1'''.  
O atraso médio para [[Special:OldReviewedPages|páginas desatualizadas]] é '''$2'''.",
	'validationstatistics-table' => "As estatísticas de cada domínio são exibidas a seguir, '''excetuando-se''' as páginas de redirecionamento. Páginas '''desatualizadas''' são as com edições posteriores à versão estável.
Se a versão estável for também a mais recente, a página está '''sincronizada'''.

''Nota: os dados a seguir são armazenados em cache por várias horas e podem não estar atualizados.''",
	'validationstatistics-ns' => 'Espaço nominal',
	'validationstatistics-total' => 'Páginas',
	'validationstatistics-stable' => 'Analisadas',
	'validationstatistics-latest' => 'Mais recente analisada',
	'validationstatistics-synced' => 'Sincronizadas/Analisadas',
	'validationstatistics-old' => 'Desactualizadas',
);

/** Brazilian Portuguese (Português do Brasil)
 * @author Eduardo.mps
 */
$messages['pt-br'] = array(
	'validationstatistics' => 'Estatísticas de validações',
	'validationstatistics-users' => "'''{{SITENAME}}''' possui, no momento, '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|utilizador|utilizadores}} com privilégios de [[{{MediaWiki:Validationpage}}|{{int:group-editor-member}}]] e '''$2''' {{PLURAL:$2|utilizador|utilizadores}} com privilégios de [[{{MediaWiki:Validationpage}}|{{int:group-reviewer-member}}]].",
	'validationstatistics-time' => "O tempo médio de espera de edições por ''utilizadores não registrados'' é '''$1'''.   
O atraso médio para [[Special:OldReviewedPages|páginas desatualizadas]] é '''$2'''.",
	'validationstatistics-table' => "As estatísticas de cada domínio são exibidas a seguir, '''excetuando-se''' as páginas de redirecionamento. Páginas '''desatualizadas''' são as com edições posteriores à versão estável.
Se a versão estável for também a mais recente, a página está '''sincronizada'''.

''Nota: os dados a seguir são armazenados em cache por várias horas e podem não estar atualizados.''",
	'validationstatistics-ns' => 'Espaço nominal',
	'validationstatistics-total' => 'Páginas',
	'validationstatistics-stable' => 'Analisadas',
	'validationstatistics-latest' => 'Mais recente analisada',
	'validationstatistics-synced' => 'Sincronizadas/Analisadas',
	'validationstatistics-old' => 'Desatualizadas',
);

/** Romanian (Română)
 * @author KlaudiuMihaila
 */
$messages['ro'] = array(
	'validationstatistics-ns' => 'Spaţiu de nume',
	'validationstatistics-total' => 'Pagini',
);

/** Tarandíne (Tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'validationstatistics' => 'Statisteche de validazione',
	'validationstatistics-users' => "'''{{SITENAME}}''' jndr'à quiste mumende tène '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|utende|utinde}} cu le deritte de [[{{MediaWiki:Validationpage}}|cangiatore]] e '''$2''' {{PLURAL:$2|utende|utinde}} cu le deritte de[[{{MediaWiki:Validationpage}}|revisione]].",
	'validationstatistics-time' => "'A medie attese pe le cangiaminde da ''utinde ca non ge sonde colleghete'' jè '''$1'''.
'U timbe medie pe le [[Special:OldReviewedPages|pàggene non aggiornete]] jè '''$2'''.",
	'validationstatistics-table' => "Le statisteche pe ogne namespace sonde mostrete aqquà sotte, 'scludenne le pàggene de redirezionaminde.

''Non aggiornete'' sonde le pàggende cu cangiaminde cchiù nuève de chidde d'a versiona secure.
Ce 'a versiona secura jè pure l'urtema versione, allore 'a pàgene jè ''singronizzete''.

'''Vide Bbuene:''' 'u date seguende jè chesciate pe quacche ore e non ge se pò aggiornà a 'na certa date.",
	'validationstatistics-ns' => 'Neimspeise',
	'validationstatistics-total' => 'Pàggene',
	'validationstatistics-stable' => 'Riviste',
	'validationstatistics-latest' => 'Urtema revisione',
	'validationstatistics-synced' => 'Singronizzete/Riviste',
	'validationstatistics-old' => "Non g'è aggiornete",
);

/** Russian (Русский)
 * @author Ahonc
 * @author AlexSm
 * @author Ferrer
 * @author Putnik
 * @author Sergey kudryavtsev
 * @author Александр Сигачёв
 */
$messages['ru'] = array(
	'validationstatistics' => 'Статистика проверок',
	'validationstatistics-users' => "В проекте {{SITENAME}} на данный момент '''[[Special:ListUsers/editor|$1]]''' {{plural:$1|участник|участника|участников}} имеют права [[{{MediaWiki:Validationpage}}|«редактора»]] и '''$2''' {{plural:$2|участник|участника|участников}} имеют права [[{{MediaWiki:Validationpage}}|«проверяющего»]].",
	'validationstatistics-time' => "Среднее ожидание правок от ''участников, которые не авторизовались'' равно '''$1'''.  
Средняя задержка для [[Special:OldReviewedPages|устаревших страниц]] равна '''$2'''.",
	'validationstatistics-table' => "Ниже представлена статистика по каждому пространству имён. Перенаправления из подсчётов исключены. 
''Устаревшими'' называются страницы, имеющие правки после стабильной версии.
Если стабильная версия является последней, то страница называется ''синхронизированной''.

'''Замечание.''' Страница кэшируется. Данные могут отставать на несколько часов.",
	'validationstatistics-ns' => 'Пространство',
	'validationstatistics-total' => 'Страниц',
	'validationstatistics-stable' => 'Проверенные',
	'validationstatistics-latest' => 'Недавно проверенные',
	'validationstatistics-synced' => 'Перепроверенные',
	'validationstatistics-old' => 'Устаревшие',
);

/** Slovak (Slovenčina)
 * @author Helix84
 */
$messages['sk'] = array(
	'validationstatistics' => 'Štatistiky overenia',
	'validationstatistics-users' => "'''{{SITENAME}}''' má momentálne '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|používateľa|používateľov}} s právami [[{{MediaWiki:Validationpage}}|redaktor]] a '''$2''' {{PLURAL:$2|používateľa|používateľov}} s právami [[{{MediaWiki:Validationpage}}|kontrolór]].",
	'validationstatistics-time' => "Priemerné čakanie na úpravy ''anonymných používateľov'' je '''$1'''.  
Priemerné oneskorenie [[Special:OldReviewedPages|zastaralých stránok]] je '''$2'''.",
	'validationstatistics-table' => "Dolu sú zobrazené štatistiky pre každý menný priestor ''okrem'' presmerovacích stránok.
''Zastaralé'' stránky sú tie, ktoré majú úpravy novšie ako stabilná verzia.
Ak je stabilná verzia zároveň najnovšia, stránka sa nazýva ''synchronizovaná''.

''Pozn.: nasledujúce údaje pochádzajú z vyrovnávacej pamäte a môžu byť niekoľko hodín staré.''",
	'validationstatistics-ns' => 'Menný priestor',
	'validationstatistics-total' => 'Stránky',
	'validationstatistics-stable' => 'Skontrolované',
	'validationstatistics-latest' => 'Posledné skontrolované',
	'validationstatistics-synced' => 'Synchronizované/skontrolované',
	'validationstatistics-old' => 'Zastaralé',
);

/** Albanian (Shqip)
 * @author Puntori
 */
$messages['sq'] = array(
	'validationstatistics-total' => 'Faqet',
);

/** Swedish (Svenska)
 * @author Boivie
 * @author M.M.S.
 */
$messages['sv'] = array(
	'validationstatistics' => 'Valideringsstatistik',
	'validationstatistics-users' => "'''{{SITENAME}}''' har just nu '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|användare|användare}} med [[{{MediaWiki:Validationpage}}|redaktörsrättigheter]] och '''$2''' {{PLURAL:$2|användare|användare}} med [[{{MediaWiki:Validationpage}}|granskningsrättigheter]].",
	'validationstatistics-time' => "Genomsnittlig väntan för redigeringar av ''oinloggade användare'' är '''$1'''.
Genomsnittlig lag för [[Special:OldReviewedPages|Föråldrade granskade sidor]] är '''$2'''.",
	'validationstatistics-table' => "Statistik för varje namnrymd visas nedan, ''förutom'' omdirigeringssidor.
''Föråldrade'' sidor är de med nyare redigeringar än den stabila versionen.
Om den stabila versionen också är den senaste versionen, så är sidan ''synkad''.

'''Notera:''' följande data cachas flera timmar och kan vara inaktuell.",
	'validationstatistics-ns' => 'Namnrymd',
	'validationstatistics-total' => 'Sidor',
	'validationstatistics-stable' => 'Granskad',
	'validationstatistics-latest' => 'Senast granskad',
	'validationstatistics-synced' => 'Synkad/Granskad',
	'validationstatistics-old' => 'Föråldrad',
);

/** Tamil (தமிழ்)
 * @author Ulmo
 */
$messages['ta'] = array(
	'validationstatistics-ns' => 'பெயர்வெளி',
	'validationstatistics-total' => 'பக்கங்கள்',
);

/** Telugu (తెలుగు)
 * @author Veeven
 */
$messages['te'] = array(
	'validationstatistics' => 'సరిచూత గణాంకాలు',
	'validationstatistics-total' => 'పేజీలు',
	'validationstatistics-old' => 'పాతవి',
);

/** Tetum (Tetun)
 * @author MF-Warburg
 */
$messages['tet'] = array(
	'validationstatistics-ns' => 'Espasu pájina nian',
);

/** Thai (ไทย)
 * @author Octahedron80
 */
$messages['th'] = array(
	'validationstatistics-ns' => 'เนมสเปซ',
);

/** Tagalog (Tagalog)
 * @author AnakngAraw
 */
$messages['tl'] = array(
	'validationstatistics' => 'Mga estadistika ng pagpapatunay (balidasyon)',
	'validationstatistics-users' => "Ang '''{{SITENAME}}''' ay  pangkasalukuyang may '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|tagagamit|mga tagagamit}} na may karapatan bilang [[{{MediaWiki:Validationpage}}|Patnugot]] 
at '''$2''' {{PLURAL:$2|tagagamit|mga tagagamit}} na may karapatan bilang [[{{MediaWiki:Validationpage}}|Tagapagsuri]].",
	'validationstatistics-time' => "Ang karaniwang panahon ng paghihintay para sa mga pagbabago ng ''hindi-tagagamit'' ay '''$1'''.
Ang karaniwang panahon ng pagkakaiwan para sa [[Special:OldReviewedPages|mga pahinang wala na sa panahon]] ay '''$2'''.",
	'validationstatistics-table' => "Ipinapakita sa ibaba ang mga estadistika para sa bawat espasyo ng pangalan, ''hindi kasama'' ang mga pahinang tumuturo papunta sa ibang pahina (mga ''redirect''). Ang mga pahinang ''wala na sa panahon'' ay iyong mga pagbabagong mas bago pa kaysa matatag na bersyon.  Kung ang matatag na bersyon ang siya ring pinakahuling bersyon, nangangahulugang ''sumasabay'' na ang pahina.

'''Paunawa:''' ang sumusunod na mga dato ay itinatagong nakakubli sa loob ng ilang mga oras at maaaring hindi nasa panahon.",
	'validationstatistics-ns' => 'Espasyo ng pangalan',
	'validationstatistics-total' => 'Mga pahina',
	'validationstatistics-stable' => 'Nasuri na',
	'validationstatistics-latest' => 'Pinakahuling nasuri',
	'validationstatistics-synced' => 'Pinagsabay-sabay/Nasuri nang muli',
	'validationstatistics-old' => 'Wala na sa panahon (luma)',
	'validationstatistics-nbr' => '$1%',
);

/** Turkish (Türkçe)
 * @author Joseph
 */
$messages['tr'] = array(
	'validationstatistics' => 'Doğrulama istatistikleri',
	'validationstatistics-users' => "'''{{SITENAME}}''' sitesinde şuanda [[{{MediaWiki:Validationpage}}|Editor]] yetkisine sahip '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|kullanıcı|kullanıcı}} ve [[{{MediaWiki:Validationpage}}|Reviewer]] yetkisine sahip '''$2''' {{PLURAL:$2|kullanıcı|kullanıcı}} bulunmaktadır.",
	'validationstatistics-time' => "''Giriş yapmamış kullanıcılar'' tarafından değişiklikler için ortalama bekleme süresi '''$1'''.
[[Special:OldReviewedPages|Eskimiş sayfalar]] için ortalama gecikme '''$2'''.",
	'validationstatistics-table' => "Her bir ad alanı için istatistikler aşağıda gösterilmiştir, yönlendirme sayfaları hariç.
''Eskimiş'' sayfalar kararlı sürümden sonra yeni değişikliğe sahip sayfalardır.
Eğer kararlı sürüm aynı zamanda son sürümse, sayfa ''senkron'' olur.

''Not: aşağıdaki veri birkaç saat için önbellektedir ve güncel olmayabilir.''",
	'validationstatistics-ns' => 'Ad alanı',
	'validationstatistics-total' => 'Sayfalar',
	'validationstatistics-stable' => 'Gözden geçirilmiş',
	'validationstatistics-latest' => 'En son gözden geçirilmiş',
	'validationstatistics-synced' => 'Eşitlenmiş/Gözden geçirilmiş',
	'validationstatistics-old' => 'Eski',
	'validationstatistics-nbr' => '%$1',
);

/** Ukrainian (Українська)
 * @author Ahonc
 */
$messages['uk'] = array(
	'validationstatistics' => 'Статистика перевірок',
	'validationstatistics-users' => "У {{grammar:locative|{{SITENAME}}}} зараз '''[[Special:ListUsers/editor|$1]]''' {{plural:$1|користувач має|користувачі мають|користувачів мають}} права [[{{MediaWiki:Validationpage}}|«редактор»]] і '''$2''' {{plural:$2|користувач має|користувачі мають|користувачів мають}} права [[{{MediaWiki:Validationpage}}|«рецензент»]].",
	'validationstatistics-table' => "Нижче наведена статистика по кожному простору назв. Перенаправлення не враховані.
''Застарілими'' називаються сторінки, які мають редагування після встановлення стабільної версії.
Якщо стабільна версія є останньою, то сторінка називається ''синхронізованою''.

''Зауваження: сторінка кешуються, дані можуть відставати на кілька годин.''",
	'validationstatistics-ns' => 'Простір назв',
	'validationstatistics-total' => 'Сторінок',
	'validationstatistics-stable' => 'Перевірені',
	'validationstatistics-latest' => 'Нещодавно перевірені',
	'validationstatistics-synced' => 'Повторно перевірені',
	'validationstatistics-old' => 'Застарілі',
);

/** Vietnamese (Tiếng Việt)
 * @author Minh Nguyen
 */
$messages['vi'] = array(
	'validationstatistics' => 'Thống kê phê chuẩn',
	'validationstatistics-users' => "Hiện nay, '''[[Special:ListUsers/editor|$1]]''' thành viên tại '''{{SITENAME}}''' có quyền [[{{MediaWiki:Validationpage}}|Chủ bút]] và '''$2''' thành viên có quyền [[{{MediaWiki:Validationpage}}|Người duyệt]].",
	'validationstatistics-table' => "Đây có thống kê về các không gian tên, trừ các trang đổi hướng.

'''Chú ý:''' Dữ liệu sau được nhớ đệm vài tiếng đồng hồ và có thể lỗi thời.",
	'validationstatistics-ns' => 'Không gian tên',
	'validationstatistics-total' => 'Số trang',
	'validationstatistics-stable' => 'Được duyệt',
	'validationstatistics-latest' => 'Được duyệt gần đây',
	'validationstatistics-synced' => 'Cập nhật/Duyệt',
	'validationstatistics-old' => 'Lỗi thời',
);

/** Volapük (Volapük)
 * @author Malafaya
 */
$messages['vo'] = array(
	'validationstatistics-ns' => 'Nemaspad',
	'validationstatistics-total' => 'Pads',
);

/** Yiddish (ייִדיש)
 * @author פוילישער
 */
$messages['yi'] = array(
	'validationstatistics-ns' => 'נאמענטייל',
	'validationstatistics-total' => 'בלעטער',
);

