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
and \'\'\'[[Special:ListUsers/reviewer|$2]]\'\'\' {{PLURAL:$2|user|users}} with [[{{MediaWiki:Validationpage}}|Reviewer]] rights.

Editors and Reviewers are established users that can spot check revisions to pages.',
	'validationstatistics-time'   => '\'\'The following data was last updated on $5 at $6.\'\'

Edits that have been checked by established users are considered to be reviewed.

The average wait for edits by \'\'users that have not logged in\'\' to be reviewed is \'\'\'$1\'\'\'; the median is \'\'\'$3\'\'\'. 
$4
The average lag for [[Special:OldReviewedPages|pages with unreviewed edits pending]] is \'\'\'$2\'\'\'.
These pages are considered \'\'outdated\'\'. Likewise, pages are considered \'\'synchronized\'\' if the [[{{MediaWiki:Validationpage}}|stable version]] is also the current draft version.
The stable version of a page is the newest revision that has been approved to show by default to all readers.',
	'validationstatistics-table'  => "Statistics for each namespace are shown below, ''excluding'' redirect pages.",
	'validationstatistics-ns'     => 'Namespace',
	'validationstatistics-total'  => 'Pages',
	'validationstatistics-stable' => 'Reviewed',
	'validationstatistics-latest' => 'Synced',
	'validationstatistics-synced' => 'Synced/Reviewed',
	'validationstatistics-old'    => 'Outdated',
	'validationstatistics-utable' => 'Below is the list of top 5 reviewers in the last hour.',
	'validationstatistics-user'   => 'User',
	'validationstatistics-reviews' => 'Reviews',
);

/** Message documentation (Message documentation)
 * @author Fryed-peach
 * @author Jon Harald Søby
 * @author Raymond
 * @author Umherirrender
 */
$messages['qqq'] = array(
	'validationstatistics' => '{{Flagged Revs}}',
	'validationstatistics-users' => '{{Flagged Revs}}',
	'validationstatistics-time' => '{{FlaggedRevs}}
This message is shown on [http://de.wikipedia.org/wiki/Spezial:Markierungsstatistik?uselang={{UILANGCODE}} Special:ValidationStatistics].

* $1: the average time in hhmmss
* $2: average lag in hhmmss
* $3: the median in hhmmss
* $4: a table in HTML syntax.
* $5: the date of the last update
* $6: the time of the last update',
	'validationstatistics-table' => '{{Flagged Revs}}',
	'validationstatistics-ns' => '{{Flagged Revs}}
{{Identical|Namespace}}',
	'validationstatistics-total' => '{{Flagged Revs}}
{{Identical|Pages}}',
	'validationstatistics-stable' => '{{Flagged Revs}}',
	'validationstatistics-latest' => '{{Flagged Revs}}',
	'validationstatistics-synced' => '{{Flagged Revs}}',
	'validationstatistics-old' => '{{Flagged Revs}}',
	'validationstatistics-utable' => '{{FlaggedRevs}}',
	'validationstatistics-user' => '{{FlaggedRevs}}
{{Identical|User}}',
	'validationstatistics-reviews' => '{{FlaggedRevs}}',
);

/** Afrikaans (Afrikaans)
 * @author Naudefj
 */
$messages['af'] = array(
	'validationstatistics-ns' => 'Naamruimte',
	'validationstatistics-total' => 'Bladsye',
	'validationstatistics-latest' => 'Gesinchroniseerd',
	'validationstatistics-old' => 'Verouderd',
	'validationstatistics-user' => 'Gebruiker',
	'validationstatistics-reviews' => 'Beoordelings',
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
	'validationstatistics-time' => "''البيانات التالية مخزنة وربما لا تكون محدثة.''

الانتظار المتوسط للتعديلات بواسطة ''المستخدمين الذين لم يسجلوا الدخول'' هو '''$1'''؛ المتوسط العام هو '''$3'''.
$4
التأخر المتوسط [[Special:OldReviewedPages|للصفحات ذات التعديلات غير المراجعة قيد الانتظار]] هو '''$2'''.
هذه الصفحات تعتبر ''مخزنة''. وبالمثل، الصفحات تعتبر ''محدثة'' إذا كانت [[{{MediaWiki:Validationpage}}|النسخة المستقرة]] هي أيضا نسخة المسودة الحالية.
النسخ المستقرة هي نسخ الصفحات التي تم التحقق منها بواسطة مستخدم موثوق واحد على الأقل.",
	'validationstatistics-table' => "الإحصاءات لكل نطاق معروضة بالأسفل، ''ولا يشمل ذلك'' صفحات التحويل.",
	'validationstatistics-ns' => 'النطاق',
	'validationstatistics-total' => 'الصفحات',
	'validationstatistics-stable' => 'مراجع',
	'validationstatistics-latest' => 'محدث',
	'validationstatistics-synced' => 'تم تحديثه/تمت مراجعته',
	'validationstatistics-old' => 'قديمة',
	'validationstatistics-utable' => 'بالأسفل قائمة بأعلى 5 مراجعين في الساعة الأخيرة.',
	'validationstatistics-user' => 'المستخدم',
	'validationstatistics-reviews' => 'مراجعات',
);

/** Aramaic (ܐܪܡܝܐ)
 * @author Basharh
 */
$messages['arc'] = array(
	'validationstatistics-ns' => 'ܚܩܠܐ',
	'validationstatistics-total' => 'ܦܐܬܬ̈ܐ',
);

/** Egyptian Spoken Arabic (مصرى)
 * @author Ghaly
 * @author Meno25
 * @author Ramsis II
 */
$messages['arz'] = array(
	'validationstatistics' => 'إحصاءات التحقق',
	'validationstatistics-users' => "'''{{SITENAME}}''' لديه حاليا '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|مستخدم|مستخدم}} بصلاحيات [[{{MediaWiki:Validationpage}}|محرر]]
و '''$2''' {{PLURAL:$2|مستخدم|مستخدم}} بصلاحيات [[{{MediaWiki:Validationpage}}|مراجع]].",
	'validationstatistics-time' => "متوسط انتظار التعديلات لـ''اليوزرات اللى ماسجلوش دخولهم'' هو '''$1'''. 
متوسط التأخير لـ [[Special:OldReviewedPages|الصفحات القديمه]] هو '''$2'''.",
	'validationstatistics-table' => "الاحصائيات لكل نطاق معروضه تحت ، ''من غير'' صفحات التحويل.
الصفحات ''القديمه''  هى اللى بتبقا فيها تعديلات احدث من النسخه الثابته.
لو النسخه الثابته هى نفسها اخر نسخه ، ساعتها الصفحه دى بتبقا ''متحدثه''.

''خد بالك: البيانات اللى تحت دى بتبقا متخزنه كذا ساعه و ممكن ما تكونش متحدثه.''",
	'validationstatistics-ns' => 'النطاق',
	'validationstatistics-total' => 'الصفحات',
	'validationstatistics-stable' => 'مراجع',
	'validationstatistics-latest' => 'محدث',
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
	'validationstatistics-users' => "'''{{SITENAME}}''' цяпер налічвае '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|удзельніка|удзельнікаў|удзельнікаў}} з правамі [[{{MediaWiki:Validationpage}}|«рэдактара»]] і [[Special:ListUsers/reviewer|$2]]  {{PLURAL:$2|удзельніка|удзельнікаў|удзельнікаў}} з правамі [[{{MediaWiki:Validationpage}}|«рэцэнзэнта»]].

Рэдактары і рэцэнзэнты — удзельнікі са стажам, якія могуць правяраць зьмены старонак.",
	'validationstatistics-time' => "''Наступныя зьвесткі апошні раз абнаўляліся $5 у $6.''

Рэдагаваньні, якія былі правераны адпаведнымі ўдзельнікамі прыняты як рэцэнзаваныя.

Сярэдняя затрымка паміж рэдагаваньнем і рэцэнзаваньнем  для ''ананімных удзельнікаў'' складае '''$1''', а мэдыяна '''$3'''.
$4
Сярэдняя затрымка для [[Special:OldReviewedPages|старонак з не рэцэнзаванымі рэдагаваньнямі]] складае '''$2'''.
Гэтыя старонкі лічацца ''састарэлымі''. У сваю чаргу, старонкі лічацца ''сынхранізаванымі'' калі  [[{{MediaWiki:Validationpage}}|стабільная вэрсія]] адначасова зьяўляецца цяперашняй чарнавой вэрсіяй.
Стабільнымі вэрсіямі лічацца вэрсіі старонак зацьверджаныя хаця б адным удзельнікам з адпаведнымі правамі.",
	'validationstatistics-table' => "Статыстыка для кожнай прасторы назваў пададзеная ніжэй, за ''выключэньнем'' старонак-перанакіраваньняў.",
	'validationstatistics-ns' => 'Прастора назваў',
	'validationstatistics-total' => 'Старонак',
	'validationstatistics-stable' => 'Правераных',
	'validationstatistics-latest' => 'Сынхранізаваных',
	'validationstatistics-synced' => 'Паўторна правераных',
	'validationstatistics-old' => 'Састарэлых',
	'validationstatistics-utable' => 'Ніжэй пададзены сьпіс з 5 самых актыўных рэцэнзэнтаў за апошнюю гадзіну.',
	'validationstatistics-user' => 'Удзельнік',
	'validationstatistics-reviews' => 'Рэцэнзіяў',
);

/** Bulgarian (Български)
 * @author DCLXVI
 */
$messages['bg'] = array(
	'validationstatistics-ns' => 'Именно пространство',
	'validationstatistics-total' => 'Страници',
	'validationstatistics-user' => 'Потребител',
);

/** Breton (Brezhoneg)
 * @author Fulup
 */
$messages['br'] = array(
	'validationstatistics' => 'Stadegoù kadarnaat',
	'validationstatistics-users' => "Evit ar poent, war'''{{SITENAME}}''' ez eus '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|implijer|implijer}} gantañ gwirioù [[{{MediaWiki:Validationpage}}|Aozer]]
ha '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|implijer|implijer}} gantañ gwirioù [[{{MediaWiki:Validationpage}}|Adlenner]].

Editors and Reviewers are established users that can spot check revisions to pages.",
	'validationstatistics-time' => "''Hizivaet eo bet ar roadennoù da-heul d'an $5 da $6 da ziwezhañ.''

Sellet e vez ouzh ar c'hemmoù bet gwiriet gant implijerien oberiant evel ouzh kemmoù bet adwelet.

Amzer adlenn keitat ar c'hemmoù dre ''implijerien anluget'' da vezañ adwelet zo '''$1'''; '''$3''' eo an dalvoudenn greiz.
$4
An dale keitat evit ar [[Special:OldReviewedPages|pajennoù enno kemmoù da vezañ adlennet]] zo '''$2'''.
Sellet a reer ouzh ar pajennoù-se evel ouzh pajennoù ''dispredet''. Heñveldra, sellet e vez ouzh ar pajennoù evel ouzh pajennoù ''sinkronelaet'' mard eo [[{{MediaWiki:Validationpage}}|ar stumm stabil]] ar stumm brouilhed red.
Stumm stabil ur bajenn eo an adweladenn diwezhañ zo bet aprouet da vezañ diskouezet dre ziouer d'an holl lennerien.",
	'validationstatistics-table' => "A-is emañ diskouezet ar stadegoù evit pep esaouenn anv, ''nemet'' evit ar pajennoù adkas.",
	'validationstatistics-ns' => 'Esaouenn anv',
	'validationstatistics-total' => 'Pajennoù',
	'validationstatistics-stable' => 'Adwelet',
	'validationstatistics-latest' => 'Sinkronelaet',
	'validationstatistics-synced' => 'Sinkronelaet/Adwelet',
	'validationstatistics-old' => 'Dispredet',
	'validationstatistics-utable' => 'A-is emañ roll ar 5 adlenner gwellañ en eurvezh diwezhañ.',
	'validationstatistics-user' => 'Implijer',
	'validationstatistics-reviews' => 'Adweladennoù',
);

/** Bosnian (Bosanski)
 * @author CERminator
 */
$messages['bs'] = array(
	'validationstatistics' => 'Statistike provjera',
	'validationstatistics-users' => "'''{{SITENAME}}''' trenutno ima '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|korisnika|korisnika}} sa pravima [[{{MediaWiki:Validationpage}}|urednika]] i '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$1|korisnika|korisnika}} sa pravima [[{{MediaWiki:Validationpage}}|provjere]].

Urednici i provjerivači su potvrđeni korisnici koji mogu izvršavati provjere revizija stranice.",
	'validationstatistics-time' => "''Slijedeći podaci su posljednji put ažurirani dana $5 u $6 sati.''

Izmjene koje trebaju provjeriti potvrđeni korisnici smatraju se neprovjerenim.

Prosječno čekanje na izmjenu od strane ''korisnika koji se nisu prijavili'' a izmjena čeka na pregled je '''$1'''; prosjek je '''$3'''.
$4
Prosječno kašnjenje za [[Special:OldReviewedPages|stranice sa nepregledanim izmjenama]] je '''$2'''.
Ove stranice se smatraju ''zastarijelim''. Isto tako, stranice se smatraju ''sinhronizovanim'', ako je [[{{MediaWiki:Validationpage}}|stabilna verzija]] ujedno i trenutna verzija.
Stabilna verzija je najnovija revizija stranice koja je provjerena i prikazuje se po prepostavljenom svim čitaocima.",
	'validationstatistics-table' => "Statistike za svaki imenski prostor su prikazane ispod, ''isključujući'' stranice preusmjeravanja.",
	'validationstatistics-ns' => 'Imenski prostor',
	'validationstatistics-total' => 'Stranice',
	'validationstatistics-stable' => 'Provjereno',
	'validationstatistics-latest' => 'Sinhronizirano',
	'validationstatistics-synced' => 'Sinhronizirano/provjereno',
	'validationstatistics-old' => 'Zastarijelo',
	'validationstatistics-utable' => 'Ispod je spisak 5 najboljih ocjenjivača u zadnjih sat vremena.',
	'validationstatistics-user' => 'Korisnik',
	'validationstatistics-reviews' => 'Pregledi',
);

/** Catalan (Català)
 * @author Aleator
 * @author SMP
 * @author Solde
 */
$messages['ca'] = array(
	'validationstatistics' => 'Estadístiques de validació',
	'validationstatistics-ns' => "Nom d'espai",
	'validationstatistics-total' => 'Pàgines',
	'validationstatistics-stable' => "S'ha revisat",
	'validationstatistics-latest' => 'Sincronitzat',
	'validationstatistics-synced' => 'Sincronitzat/Revisat',
);

/** Czech (Česky)
 * @author Kuvaly
 */
$messages['cs'] = array(
	'validationstatistics-total' => 'Stránky',
	'validationstatistics-user' => 'Uživatel',
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
und '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|Benutzer|Benutzer}} mit [[{{MediaWiki:Validationpage}}|Prüferrecht]].

Sichter und Prüfer sind anerkannte Benutzer, die Versionen einer Seite überprüfen können.",
	'validationstatistics-time' => "''Die folgenden Daten wurden zuletzt am $5 um $6 Uhr aktualisiert.''

Die durchschnittliche Wartezeit für Bearbeitungen, die von nicht angemeldeten Benutzern stammen, beträgt '''$1'''; der Median beträgt '''$3'''.
$4
Der durchschnittliche Rückstand auf [[Special:OldReviewedPages|veraltete Seiten]] beträgt '''$2'''.
''Veraltete'' Seiten sind Seiten mit Bearbeitungen, die neuer als die [[{{MediaWiki:Validationpage}}|markierte Version]] sind.
Wenn die markierte Version auch die letzte Version ist, ist die Seite ''synchronisiert''.
Die stabile Version einer Seite ist die neueste Version, die durch einen Sichter bestätigt wurde und als Standard allen Lesern angezeigt wird.",
	'validationstatistics-table' => "Statistiken für jeden Namensraum, ''ausgenommen'' sind Weiterleitungen.",
	'validationstatistics-ns' => 'Namensraum',
	'validationstatistics-total' => 'Seiten gesamt',
	'validationstatistics-stable' => 'Mindestens eine Version gesichtet',
	'validationstatistics-latest' => 'Anzahl Seiten, die in der aktuellen Version gesichtet sind',
	'validationstatistics-synced' => 'Prozentsatz an Seiten, die in der aktuellen Version gesichtet sind',
	'validationstatistics-old' => 'Seiten mit ungesichteten Versionen',
	'validationstatistics-utable' => 'Nachfolgend die Liste der 5 Benutzer, die in der letzten Stunde die meisten Markierungen gesetzt haben.',
	'validationstatistics-user' => 'Benutzer',
	'validationstatistics-reviews' => 'Markierungen',
);

/** Zazaki (Zazaki)
 * @author Aspar
 * @author Xoser
 */
$messages['diq'] = array(
	'validationstatistics' => 'Îstatîstîksê onay kerdişî',
	'validationstatistics-users' => "'''{{SITENAME}}''' de nika '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|karber|karberan}} pê heqê [[{{MediaWiki:Validationpage}}|Editor]]î
u '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|karber|karberan}} pê heqê [[{{MediaWiki:Validationpage}}|Kontrol kerdoğ]]î estê.

Editorî u kontrol kerdoğî karberanê kihanyerê ke eşkenî pelan revize bike.",
	'validationstatistics-time' => "''Aşağıdaki veri en son $5 $6 tarihinde güncellenmiştir.''

Belirli kullanıcılar tarafından kontrol edilen değişiklikler, gözden geçirilmiş sayılırlar.

''Giriş yapmamış kullanıcılar'' tarafından yapılan gözden geçirilecek değişiklikler için ortalama bekleme süresi '''$1'''; orta değer '''$3'''.
$4
[[Special:OldReviewedPages|Gözden geçirilmemiş değişikliğe sahip sayfalar]] için ortalama gecikme '''$2'''.
Bu sayfalar ''eskimiş'' sayılırlar. Aynı şekilde, eğer [[{{MediaWiki:Validationpage}}|kararlı sürüm]] aynı zamanda güncel taslaksa, sayfa ''senkron'' sayılır.
Kararlı sürümler, sayfaların en az bir belirli kullanıcı tarafından kontrol edilmiş revizyonlarıdır.",
	'validationstatistics-table' => "Ser her cayênameyî rê îstatistiks bin de mucnayîyo, pelanê redreksiyonî ''nimucniyo\".",
	'validationstatistics-ns' => 'Cayênameyî',
	'validationstatistics-total' => 'Ripelî',
	'validationstatistics-stable' => 'Kontrol biyo',
	'validationstatistics-latest' => 'Rocaniye biyo',
	'validationstatistics-synced' => 'Rocaniye biyo/Kontrol biyo',
	'validationstatistics-old' => 'Hin nihebitiyeno',
	'validationstatistics-utable' => 'Cor de yew liste est ke seatê penî de panc kontrol kerdoğî mucneno.',
	'validationstatistics-user' => 'Karber',
	'validationstatistics-reviews' => 'Revizyonî',
);

/** Lower Sorbian (Dolnoserbski)
 * @author Michawiki
 */
$messages['dsb'] = array(
	'validationstatistics' => 'Pógódnośeńska statistika',
	'validationstatistics-users' => "'''{{SITENAME}}''' ma tuchylu '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|wužywarja|wužywarjowu|wužywarjow|wužywarjow}} z [[{{MediaWiki:Validationpage}}|pšawami wobźěłowarja]]
a '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|wužywarja|wužywarjowu|wužywarjow|wužywarjow}} z [[{{MediaWiki:Validationpage}}|pšawami pśeglědowarja]].

Wobźěłowarje a pséglědowarje su etablěrowane wužiwarje, kótarež mógu wersije bokow pśeglědaś.",
	'validationstatistics-time' => "''Slědujuce daty su se $5 $6 slědny raz zaktualizěrowali.''

Změny, kótarež su se pśekontrolowali wót etablěrowanych wužywarjow naglědaju se ako pśeglědane.

Pśerězne cakanje za změny wót ''wužywarjow, kótarež njejsu pśizjawjone'', kótarež dej se pśeglědaś, jo '''$1'''; mediana gódnota jo '''$3'''.
$4
Pśerězne wokomuźenje za [[Special:OldReviewedPages|boki z njepśeglědanymi změnami]] jo '''$2'''.
Toś te boki maju se za ''zestarjone''. Teke boki maju se za ''synchronizěrowane'', jolic [[{{MediaWiki:Validationpage}}|stabilna wersija]] jo teke aktualna nacerjeńska wersija.
Stabilna wersija boka jo nejnowša wersija, kótaraž jo se wobkšuśiła ako wersija, kótaraž se pó standarźe wšym cytarjam pokazujo.",
	'validationstatistics-table' => "Slěduju statistiki za kuždy mjenjowy rum, ''bźez'' dalejpósrědnjenjow.",
	'validationstatistics-ns' => 'Mjenjowy rum',
	'validationstatistics-total' => 'Boki',
	'validationstatistics-stable' => 'Pśeglědane',
	'validationstatistics-latest' => 'Synchronizěrowany',
	'validationstatistics-synced' => 'Synchronizěrowane/Pśeglědane',
	'validationstatistics-old' => 'Zestarjone',
	'validationstatistics-utable' => 'Dołojce jo lisćina 5 nejcesćejšych pśeglědowarjow w slědnej gózinje.',
	'validationstatistics-user' => 'Wužywaŕ',
	'validationstatistics-reviews' => 'Pśeglědanja',
);

/** Greek (Ελληνικά)
 * @author Crazymadlover
 * @author Dead3y3
 * @author Omnipaedista
 */
$messages['el'] = array(
	'validationstatistics' => 'Στατιστικά επικύρωσης',
	'validationstatistics-users' => "Ο ιστότοπος '''{{SITENAME}}''' αυτή τη στιγμή έχει '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|χρήστη|χρήστες}} με δικαιώματα [[{{MediaWiki:Validationpage}}|Συντάκτη]]
και '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|χρήστη|χρήστες}} με δικαιώματα [[{{MediaWiki:Validationpage}}|Κριτικού]].

Οι Συντάκτες και οι Κριτικοί είναι καθιερωμένοι χρήστες που μπορούν να ελέγχουν τις αναθεωρήσεις μίας σελίδας.",
	'validationstatistics-time' => "''Τα ακόλουθα δεδομένα είναι λανθάνοντα και ενδέχεται να μην είναι ενημερωμένα.''

Ο μέσος χρόνος αναμονής για επεξεργασίες από ''χρήστες που δεν έχουν συνδεθεί'' είναι '''$1'''· o διάμεσος είναι '''$3'''.
$4
Η μέση χρονική υστέρηση για [[Special:OldReviewedPages|σελίδες με επεξεργασίες που εκκρεμμούν κριτικής]] είναι '''$2'''.
Αυτές οι σελίδες θεωρούνται ''μη ενημερωμένες''. Παρομοίως, οι σελίδες θεωρούνται ''συγχρονισμένες'' εάν η [[{{MediaWiki:Validationpage}}|σταθερή έκδοση]] είναι επίσης η τρέχουσα πρὀχειρη έκδοση.
Η σταθερή έκδοση μίας σελίδας είναι η νεότερη αναθεώρηση που εγκρίθηκε να εμφανίζεται κατά προεπιλογή σε όλους τους αναγνώστες.",
	'validationstatistics-table' => "Τα στατιστικά για κάθε περιοχή ονομάτων εμφανίζονται παρακάτω, των σελίδων ανακατεύθυνσης ''εξαιρουμένων''.",
	'validationstatistics-ns' => 'Περιοχή ονομάτων',
	'validationstatistics-total' => 'Σελίδες',
	'validationstatistics-stable' => 'Κρίθηκαν',
	'validationstatistics-latest' => 'Συγχρονισμένος',
	'validationstatistics-synced' => 'Συγχρονισμένες/Κρίθηκαν',
	'validationstatistics-old' => 'Παρωχημένες',
	'validationstatistics-utable' => 'Παρακάτω βρίσκεται η λίστα με τους 5 κορυφαίους επιθεωρητές κατά την τελευταία μία ώρα.',
	'validationstatistics-user' => 'Χρήστης',
	'validationstatistics-reviews' => 'Επιθεωρήσεις',
);

/** Esperanto (Esperanto)
 * @author Yekrats
 */
$messages['eo'] = array(
	'validationstatistics' => 'Validigadaj statistikoj',
	'validationstatistics-users' => "'''{{SITENAME}}''' nun havas '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|uzanton|uzantojn}} kun
[[{{MediaWiki:Validationpage}}|Redaktanto]]-rajtoj
kaj '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|uzanton|uzantojn}} kun [[{{MediaWiki:Validationpage}}|Kontrolanto]]-rajtoj.

Redaktantoj kaj Kontrolantoj estas establitaj uzantoj kiuj povas kontroli ŝanĝojn al paĝojn.",
	'validationstatistics-time' => "''La jenaj datenoj estis laste ĝisdatitaj je $5, $6.''

Redaktoj kontrolitaj de longdaŭraj uzantoj estas konsiderataj esti kontrolitaj.

La averaĝa atendotempo por kontrolendaj redaktoj de ''nesalutita uzantoj'' estas '''$1'''; la mediano estas '''$3'''.
$4
La averaĝa atendotempo por [[Special:OldReviewedPages|paĝoj kun nekontrolitaj redaktoj farontaj]] estas '''$2'''.
Ĉi tiuj paĝoj estas konsiderataj kiel ''malfreŝaj''. Ankaŭ, paĝoj estas konsiderata ''sinkrona'' se la 
[[{{MediaWiki:Validationpage}}|stabila revizio]] ankaŭ estas la nuna neta versio.
Stabila revizio de paĝo estas la plej nova revizio aprobita por montri al ĉiuj legantoj.",
	'validationstatistics-table' => "Statistikoj por ĉiu nomspaco estas jene montritaj, ''krom'' alidirektiloj.",
	'validationstatistics-ns' => 'Nomspaco',
	'validationstatistics-total' => 'Paĝoj',
	'validationstatistics-stable' => 'Paĝoj kun almenaŭ unu revizio',
	'validationstatistics-latest' => 'Sinkronigita',
	'validationstatistics-synced' => 'Ĝisdatigitaj/Reviziitaj',
	'validationstatistics-old' => 'Malfreŝaj',
	'validationstatistics-utable' => 'Jen listo de la plej aktivaj kontrolantoj dum la lasta horo.',
	'validationstatistics-user' => 'Uzanto',
	'validationstatistics-reviews' => 'Kontrolaĵoj',
);

/** Spanish (Español)
 * @author Crazymadlover
 * @author Imre
 */
$messages['es'] = array(
	'validationstatistics' => 'Estadísticas de validación',
	'validationstatistics-users' => "'''{{SITENAME}}''' actualmente hay '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|usuario|usuarios}} con derechos de [[{{MediaWiki:Validationpage}}|Editor]] y '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|usuario|usuarios}} con derechos de [[{{MediaWiki:Validationpage}}|Revisor]].
Editores and Revisores son usuarios establecidos que pueden verificar las revisiones a las páginas.",
	'validationstatistics-time' => "''los siguientes datos fueron actualizados por última vez en $5 en $6.''

Las ediciones que han sido verificadas por usuarios establecidos son consideradas revisadas.

La espera promedio para ediciones de ''usuarios que no han iniciado sesión'' a ser revisado es '''$1'''; La media es '''$3'''.  
$4
El intervalo promedio para [[Special:OldReviewedPages|páginas con ediciones sin revisar pendientes]] es '''$2'''.
Estas páginas son consideradas ''desactualizadas''. De igual modo, las páginas son consideradas ''sincronizadas'' si la [[{{MediaWiki:Validationpage}}|versión estable]] es también la versión borrador actual.
La versión estable de una página es la revisión más nueva que ha sido aprobada para mostrar por defecto a todos los lectores.",
	'validationstatistics-table' => "Estadísticas para cada nombre de sitio son mostradas debajo, ''excluyendo'' páginas de redireccionamiento.",
	'validationstatistics-ns' => 'Espacio de nombres',
	'validationstatistics-total' => 'Páginas',
	'validationstatistics-stable' => 'Revisado',
	'validationstatistics-latest' => 'Sincronizado',
	'validationstatistics-synced' => 'Sincronizado/Revisado',
	'validationstatistics-old' => 'desactualizado',
	'validationstatistics-utable' => 'Debajo está un lista de los 5 revisores top en la última hora.',
	'validationstatistics-user' => 'Usuario',
	'validationstatistics-reviews' => 'Revisiones',
);

/** Estonian (Eesti)
 * @author Avjoska
 * @author KalmerE.
 */
$messages['et'] = array(
	'validationstatistics' => 'Kinnitatud statistika',
	'validationstatistics-ns' => 'Nimeruum',
	'validationstatistics-total' => 'Leheküljed',
	'validationstatistics-user' => 'Kasutaja',
);

/** Basque (Euskara)
 * @author Kobazulo
 */
$messages['eu'] = array(
	'validationstatistics' => 'Balioztatzeko estatistikak',
	'validationstatistics-total' => 'Orrialdeak',
	'validationstatistics-old' => 'Deseguneratua',
	'validationstatistics-user' => 'Lankidea',
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
 * @author Cimon Avaro
 * @author Crt
 * @author Str4nd
 * @author Vililikku
 */
$messages['fi'] = array(
	'validationstatistics' => 'Validointitilastot',
	'validationstatistics-table' => "Alla on tilastot kaikille nimiavaruuksille ''lukuun ottamatta'' ohjaussivuja.",
	'validationstatistics-ns' => 'Nimiavaruus',
	'validationstatistics-total' => 'Sivut',
	'validationstatistics-stable' => 'Arvioitu',
	'validationstatistics-latest' => 'Näkyvä sivu, joka on tarkastettu',
	'validationstatistics-old' => 'Vanhentunut',
	'validationstatistics-utable' => 'Alla on lista viidestä ahkerimmasta arvioijasta edellisen tunnin aikana.',
	'validationstatistics-user' => 'Käyttäjä',
);

/** French (Français)
 * @author Crochet.david
 * @author Grondin
 * @author IAlex
 * @author McDutchie
 * @author PieRRoMaN
 * @author Verdy p
 * @author Zetud
 */
$messages['fr'] = array(
	'validationstatistics' => 'Statistiques de validation',
	'validationstatistics-users' => "'''{{SITENAME}}''' dispose actuellement de '''[[Special:ListUsers/editor|$1]]''' utilisateur{{PLURAL:$1||s}} avec les droits de [[{{MediaWiki:Validationpage}}|contributeur]] et de '''[[Special:ListUsers/reviewer|$2]]''' utilisateur{{PLURAL:$2||s}} avec les droits de [[{{MediaWiki:Validationpage}}|relecteur]].

Les contributeurs et relecteurs sont des utilisateurs établis qui peuvent vérifier les révisions des pages.",
	'validationstatistics-time' => "''Les données suivantes sont en cache et ont été mise à jour pour la dernière fois le $5 à $6.''

Les modifications qui ont été vérifiées par des utilisateurs établis sont considérées comme relues.

Le temps moyen de relecture des modifications par ''des utilisateurs non connectés'' est '''$1''' ; la valeur médiane est '''$3'''.
$4
Le délai moyen pour les [[Special:OldReviewedPages|pages qui contiennent des modifications non relues en cours]] est '''$2'''.
Ces pages sont considérées ''périmées''. De même, les pages sont déclarées ''synchronisées'' si la [[{{MediaWiki:Validationpage}}|version stable]] est aussi la version de brouillon courante.
Les versions stables sont des versions de pages vérifiées par au moins un utilisateur régulier.",
	'validationstatistics-table' => "Les statistiques pour chaque espace de noms sont affichées ci-dessous, à ''l’exclusion'' des pages de redirection.",
	'validationstatistics-ns' => 'Espace de noms',
	'validationstatistics-total' => 'Pages',
	'validationstatistics-stable' => 'Révisée',
	'validationstatistics-latest' => 'Synchronisée',
	'validationstatistics-synced' => 'Synchronisée/Révisée',
	'validationstatistics-old' => 'Périmée',
	'validationstatistics-utable' => 'Ci-dessous figure les 5 meilleurs relecteurs dans la dernière heure.',
	'validationstatistics-user' => 'Utilisateur',
	'validationstatistics-reviews' => 'Relecteurs',
);

/** Franco-Provençal (Arpetan)
 * @author ChrisPtDe
 */
$messages['frp'] = array(
	'validationstatistics' => 'Statistiques de validacion.',
	'validationstatistics-users' => "'''{{SITENAME}}''' at ora '''[[Special:ListUsers/editor|$1]]''' utilisator{{PLURAL:$1||s}} avouéc los drêts de [[{{MediaWiki:Validationpage}}|contributor]] et '''[[Special:ListUsers/reviewer|$2]]''' utilisator{{PLURAL:$2||s}} avouéc los drêts de [[{{MediaWiki:Validationpage}}|rèvisor]].

Los contributors et los rèvisors sont des utilisators ètablis que pôvont controlar les rèvisions de les pâges.",
	'validationstatistics-time' => "''Cetes balyês sont en cache et ont étâ betâs a jorn por lo dèrriér côp lo $5 a $6.''

Los changements qu’ont étâ controlâs per des utilisators ètablis sont considèrâs coment revus.

Lo temps moyen de rèvision des changements per des ''utilisators pas branchiês'' est '''$1''' ; la valor du méten est '''$3'''.
$4
Lo dèlê moyen por les [[Special:OldReviewedPages|pâges que contegnont des changements que sont aprés étre revus]] est '''$2'''.
Celes pâges sont considèrâs ''dèpassâs''. De mémo, les pâges sont dècllarâs ''sincronisâs'' se la [[{{MediaWiki:Validationpage}}|vèrsion stâbla]] est asse-ben la vèrsion de brolyon d’ora.
Les vèrsions stâbles sont des vèrsions de pâges controlâs per u muens yon utilisator règuliér.",
	'validationstatistics-table' => "Les statistiques por châque èspâço de noms sont montrâs ce-desot, a l’''èxcllusion'' de les pâges de redirèccion.",
	'validationstatistics-ns' => 'Èspâço de noms',
	'validationstatistics-total' => 'Pâges',
	'validationstatistics-stable' => 'Revua',
	'validationstatistics-latest' => 'Sincronisâ',
	'validationstatistics-synced' => 'Sincronisâ / Revua',
	'validationstatistics-old' => 'Dèpassâ',
	'validationstatistics-utable' => 'Vê-que la lista des 5 mèlyors rèvisors dens l’hora passâ.',
	'validationstatistics-user' => 'Utilisator',
	'validationstatistics-reviews' => 'Rèvisions',
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
e '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|usuario|usuarios}} con dereitos de [[{{MediaWiki:Validationpage}}|revisor]].

Os editores e revisores son usuarios autoconfirmados que poden comprobar revisións de páxinas.",
	'validationstatistics-time' => "''Os seguintes datos foron actualizados o $5 ás $6.''

As edicións que foron comprobadas polos usuarios autoconfirmados considéranse revisadas.

O promedio de espera de revisión para as edicións feitas polos ''usuarios que non accederon ao sistema'' é de '''$1'''; a media é de '''$3'''.  
$4
O promedio de atraso para as [[Special:OldReviewedPages|páxinas con edicións sen revisión]] é de '''$2'''.
Estas páxinas son consideradas ''obsoletas''. Do mesmo xeito, as páxinas son consideradas ''sincronizadas'' se a
[[{{MediaWiki:Validationpage}}|versión estable]] é tamén a versión do borrador actual.
A versión estable dunha páxina é a revisión máis nova que foi aprobada para mostrarlla por defecto a todos os lectores.",
	'validationstatistics-table' => "A continuación amósanse as estatísticas para cada espazo de nomes, ''excluíndo'' as páxinas de redirección.",
	'validationstatistics-ns' => 'Espazo de nomes',
	'validationstatistics-total' => 'Páxinas',
	'validationstatistics-stable' => 'Revisado',
	'validationstatistics-latest' => 'Sincronizado',
	'validationstatistics-synced' => 'Sincronizado/Revisado',
	'validationstatistics-old' => 'Obsoleto',
	'validationstatistics-utable' => 'A continuación está a lista cos 5 revisores máis activos na última hora.',
	'validationstatistics-user' => 'Usuario',
	'validationstatistics-reviews' => 'Revisións',
);

/** Ancient Greek (Ἀρχαία ἑλληνικὴ)
 * @author Crazymadlover
 * @author Omnipaedista
 */
$messages['grc'] = array(
	'validationstatistics' => 'Στατιστικὰ ἐπικυρώσεων',
	'validationstatistics-users' => "Τὸ '''{{SITENAME}}''' νῦν ἔχει '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|χρὠμενον|χρωμένους}} μετὰ δικαιωμάτων [[{{MediaWiki:Validationpage}}|μεταγραφέως]] 
καὶ '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|χρὠμενον|χρωμένους}} μετὰ δικαιωμάτων [[{{MediaWiki:Validationpage}}|ἐπιθεωρητοῦ]].

Μεταγραφεῖς καὶ ἐπιθεωρηταὶ καθιερωμένοι χρώμενοι εἰσὶν δυνάμενοι τὰς τῶν δέλτων ἀναθεωρήσεις ἐλέγχειν.",
	'validationstatistics-table' => "Στατιστικὰ δεδομένα διὰ πᾶν ὀνοματεῖον κάτωθι εἰσί, δέλτων ἀναδιευθύνσεως ''ἐξαιρουμένων''.",
	'validationstatistics-ns' => 'Ὀνοματεῖον',
	'validationstatistics-total' => 'Δέλτοι',
	'validationstatistics-stable' => 'Ἀνατεθεωρημένη',
	'validationstatistics-latest' => 'Συγκεχρονισμένη',
	'validationstatistics-synced' => 'Συγκεχρονισμένη/Ἐπιτεθεωρημένη',
	'validationstatistics-old' => 'Ἀπηρχαιωμένη',
	'validationstatistics-utable' => 'Κάτωθι ἐστὶ ὁ κατάλογος τῶν 5 κορυφαίων ἐπιθεωρητῶν τῇ ὑστάτη μίᾳ ὥρᾳ.',
	'validationstatistics-user' => 'Χρώμενος',
	'validationstatistics-reviews' => 'Ἐπιθεωρήσεις',
);

/** Swiss German (Alemannisch)
 * @author Als-Holder
 */
$messages['gsw'] = array(
	'validationstatistics' => 'Markierigsstatischtik',
	'validationstatistics-users' => "'''{{SITENAME}}''' het '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|Benutzer|Benutzer}} mit [[{{MediaWiki:Validationpage}}|Sichterrächt]] un '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|Benutzer|Benutzer}} mit [[{{MediaWiki:Validationpage}}|Prieferrächt]].

Sichter un Priefer sin Benutzer, wu Syte as prieft chenne markiere.",
	'validationstatistics-time' => "''Die Date sin s letscht Mol aktualisiert woren am $5 am $6.''

Bearbeitige, wu dur etablierti Benutzer aagluegt wore sin, sotte prieft wäre.

Di durschnittlig Wartezyt fir Bearbeitige vu ''Benutzer, wu nit aagmäldet sin'', wu no aagluegt sotte wäre, isch '''$1'''; dr Mittelwärt isch '''$3'''.
$4
Dr durschnittlig Ruckstand uf [[Special:OldReviewedPages|veralteti Syten]] isch '''$2'''.
Die Syte gälten as ''veraltet''. Ebeso gälte Syten as ''zytglych'', wänn di [[{{MediaWiki:Validationpage}}|aagluegt Version]] au di letscht Fassig isch.
Aagluegti Versione sin Versione vu Syte, wu vu zmindescht eim etablierte Benutzer aagluegt wore sin.",
	'validationstatistics-table' => "Statischtike fir jede Namensruum wäre do unter zeigt, dervu ''usgnuu'' sin Wyterleitige.",
	'validationstatistics-ns' => 'Namensruum',
	'validationstatistics-total' => 'Syte insgsamt',
	'validationstatistics-stable' => 'Zmindescht ei Version isch vum Fäldhieter gsäh.',
	'validationstatistics-latest' => 'Zytglychi',
	'validationstatistics-synced' => 'Prozäntsatz vu dr Syte, wu vum Fäldhieter gsäh sin.',
	'validationstatistics-old' => 'Syte mit Versione, wu nit vum Fäldhieter gsäh sin.',
	'validationstatistics-utable' => 'Unte findsch e Lischt mit dr Top 5 Priefer in dr letschte Stund.',
	'validationstatistics-user' => 'Benutzer',
	'validationstatistics-reviews' => 'Priefige',
);

/** Gujarati (ગુજરાતી)
 * @author Dineshjk
 */
$messages['gu'] = array(
	'validationstatistics-total' => 'પાનાં',
	'validationstatistics-stable' => 'પરામર્શિત',
);

/** Hebrew (עברית)
 * @author Agbad
 * @author DoviJ
 * @author Erel Segal
 * @author Rotemliss
 * @author YaronSh
 */
$messages['he'] = array(
	'validationstatistics' => 'סטיסטיקת אישורים',
	'validationstatistics-users' => "ב'''{{grammar:תחילית|{{SITENAME}}}}''' יש כרגע {{PLURAL:$1|משתמש '''[[Special:ListUsers/editor|אחד]]'''|'''[[Special:ListUsers/editor|$1]]''' משתמשים}} עם הרשאת [[{{MediaWiki:Validationpage}}|עורך]]
ו{{PLURAL:$2|משתמש '''[[Special:ListUsers/reviewer|אחד]]'''|־'''[[Special:ListUsers/reviewer|$2]]''' משתמשים}} עם הרשאת [[{{MediaWiki:Validationpage}}|בודק דפים]].

עורכים ובודקי דפים הם משתמשים ותיקים שיכולים לבצע בדיקה מהירה של גרסאות ושל דפים.",
	'validationstatistics-time' => "'''המידע הבא עודכן לאחרונה ב־$6, $5.'''

עריכות שנבדקו על ידי משתמשים ותיקים נחשבות לבדוקות.

ההמתנה הממוצעת לבדיקת עריכות של ''משתמשים שלא נכנסו לחשבון'' היא '''$1'''; חציון ההמתנה הוא '''$3'''. 
$4
ההמתנה הממוצעת עבור [[Special:OldReviewedPages|דפים עם עריכות ממתינות שלא נבדקו]] היא '''$2'''.
דפים אלה נחשבים כ'''דפים ישנים'''. בדומה לכך, דפים נחשבים '''מסונכרנים''' אם ה[[{{MediaWiki:Validationpage}}|גרסה היציבה]] היא גם גרסת הטיוטה הנוכחית.
הגרסה היציבה של דף היא הגרסה החדשה ביותר שאושרה להצגה כברירת מחדל לכל הקוראים.",
	'validationstatistics-table' => "סטטיסטיקות לכל מרחב שם מוצגות להלן, תוך '''התעלמות''' מדפי הפניה.",
	'validationstatistics-ns' => 'מרחב שם',
	'validationstatistics-total' => 'דפים',
	'validationstatistics-stable' => 'עבר ביקורת',
	'validationstatistics-latest' => 'מסונכרן',
	'validationstatistics-synced' => 'סונכרנו/נבדקו',
	'validationstatistics-old' => 'פג תוקף',
	'validationstatistics-utable' => 'להלן רשימה של חמשת המשתמשים שבדקו הכי הרבה דפים בשעה האחרונה.',
	'validationstatistics-user' => 'משתמש',
	'validationstatistics-reviews' => 'בדיקות',
);

/** Hiligaynon (Ilonggo)
 * @author Tagimata
 */
$messages['hil'] = array(
	'validationstatistics-total' => 'Mga Pahina',
	'validationstatistics-user' => 'Naga-Usar',
);

/** Croatian (Hrvatski)
 * @author Dalibor Bosits
 * @author Suradnik13
 */
$messages['hr'] = array(
	'validationstatistics' => 'Statistika pregledavanja',
	'validationstatistics-users' => "'''{{SITENAME}}''' trenutačno ima '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|suradnika|suradnika}} s [[{{MediaWiki:Validationpage}}|uredničkim]] pravima
i '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|suradnika|suradnika}} s [[{{MediaWiki:Validationpage}}|ocjenjivačkim]] pravima.

Urednici i ocjenjivači su dokazani suradnici koji mogu provjeriti inačice stranice.",
	'validationstatistics-time' => "''Sljedeći podaci su iz međuspremnika i možda nisu ažurirani.''

Izmjene koje su provjerili dokazani suradnici smatraju se ocijenjenima.

Prosječno čekanje da izmjene ''neprijavljenih suradnika'' budu ocijenjene je '''$1'''; srednja vrijednost je '''$3'''.  
$4
Prosječno zaostajanje za [[Special:OldReviewedPages|stranice s neriješenim neocijenjenim izmjenama]] je '''$2'''.
Te se stranice smatraju ''zastarjelima''. Isto tako, stranice se smatraju ''sinkroniziranima'' ako je [[{{MediaWiki:Validationpage}}|važeća inačica]] ujedno i trenutačna inačica u radu.
Važeća inačica stranice je najnovija inačica koja je odobrena kako bi se prikazivala kao zadana za sve čitatelje.",
	'validationstatistics-table' => "Statistike za svaki imenski prostor prikazane su u nastavku, ''ne uključujući'' stranice za preusmjeravanje.",
	'validationstatistics-ns' => 'Imenski prostor',
	'validationstatistics-total' => 'Stranice',
	'validationstatistics-stable' => 'Ocijenjeno',
	'validationstatistics-latest' => 'Sinkronizirano',
	'validationstatistics-synced' => 'Usklađeno/Ocijenjeno',
	'validationstatistics-old' => 'Zastarjelo',
	'validationstatistics-utable' => 'Ispod je popis top 5 ocjenjivača u zadnjih sat vremena.',
	'validationstatistics-user' => 'Suradnik',
	'validationstatistics-reviews' => 'Ocijene',
);

/** Upper Sorbian (Hornjoserbsce)
 * @author Michawiki
 */
$messages['hsb'] = array(
	'validationstatistics' => 'Pohódnoćenska statistika',
	'validationstatistics-users' => "'''{{SITENAME}}''' ma tuchwilu '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|wužiwarja|wužiwarjow|wužiwarjow|wužiwarjow}} z [[{{MediaWiki:Validationpage}}|prawami wobdźěłowarja]]
a '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|wužiwarja|wužiwarjow|wužiwarjow|wužiwarjow}} z [[{{MediaWiki:Validationpage}}|prawami přepruwowarja]].

Wobdźěłowarjo a přepruwowarjo su etablěrowani wužiwarjo, kotřiž móžeja wersije stronow kontrolować.",
	'validationstatistics-time' => "''Slědowace daty su so a $5 $6 posledni raz zaktualizwoali.''

Změny, kotrež buchu wot etablěrowanych wužiwarjow skontrolowane, maja so za přepruwowane.

Přerězne čakanje za změny wot ''wužiwarjow, kotřiž njejsu přizjewjeni'', kotrež dyrbi so pruwować, je '''$1'''; srjedźna hódnota je '''$3'''.
$4
Přerězne komdźenje za [[Special:OldReviewedPages|strony z njepřepruwowanymi změnami]] je '''$2'''.
Tute strony maja so za ''zestarjene''. Tohorunja maja so strony za ''synchronizowane'', jeli [[{{MediaWiki:Validationpage}}|stabilna wersija]] je tež aktualna naćiskowa wersija.
Stabilna wersija strony je najnowša wersija, kotraž je so wobkrućena, zo by so po standardźe wšěm čitarjam pokazuje.",
	'validationstatistics-table' => "Slěduja statistiki za kóždy mjenowy rum ''bjez'' daleposrědkowanjow.",
	'validationstatistics-ns' => 'Mjenowy rum',
	'validationstatistics-total' => 'Strony',
	'validationstatistics-stable' => 'Skontrolowane',
	'validationstatistics-latest' => 'Synchronizowany',
	'validationstatistics-synced' => 'Synchronizowane/Skontrolowane',
	'validationstatistics-old' => 'Zestarjene',
	'validationstatistics-utable' => 'Deleka je lisćina 5 najčasćišich přepruwowarjow w poslednjej hodźinje.',
	'validationstatistics-user' => 'Wužiwar',
	'validationstatistics-reviews' => 'Přepruwowanja',
);

/** Hungarian (Magyar)
 * @author Bdamokos
 * @author Dani
 * @author Dorgan
 * @author Glanthor Reviol
 * @author Samat
 */
$messages['hu'] = array(
	'validationstatistics' => 'Ellenőrzési statisztika',
	'validationstatistics-users' => "A(z) '''{{SITENAME}}''' wikinek jelenleg '''[[Special:ListUsers/editor|{{PLURAL:$1|egy|$1}}]]''' [[{{MediaWiki:Validationpage}}|járőrjoggal]], valamint '''[[Special:ListUsers/reviewer|{{PLURAL:$2|egy|$2}}]]''' [[{{MediaWiki:Validationpage}}|lektorjoggal]] rendelkező szerkesztője van.

A járőrök és lektorok olyan tapasztalt szerkesztők, akik ellenőrizhetik a lapok változatait.",
	'validationstatistics-time' => "''Az alábbi adatok utolsó frissítése: $5 $6''

Azok a szerkesztések, amelyeket tapasztalt szerkesztők ellenőriztek, lektoráltnak tekinthetőek.

Az átlagos várakozási idő ''nem bejelentkezett szerkesztők'' szerkesztéseinek ellenőrzésére '''$1''', a medián '''$3'''.
$4
Az átlagos átfutási idő [[Special:OldReviewedPages|megtekintésre váró szerkesztésekkel rendelkező lapoknál]] '''$2'''.
Ezek a lapok ''elavultnak'' tekintendőek. Hasonlóképpen, a lapok ''szinkronizáltnak'' tekintendőek, ha a [[{{MediaWiki:Validationpage}}|stabil változat]] egyben a jelenlegi nem ellenőrzött változat (a lap utolsó változata).
A lap stabil változata a legújabb elfogadott változat, amit alapértelmezetten látnak az olvasók.",
	'validationstatistics-table' => "Ezen az oldalon a névterekre bontott statisztika látható, az átirányítások ''nélkül''.",
	'validationstatistics-ns' => 'Névtér',
	'validationstatistics-total' => 'Lapok',
	'validationstatistics-stable' => 'Ellenőrizve',
	'validationstatistics-latest' => 'Szinkronizálva',
	'validationstatistics-synced' => 'Szinkronizálva/ellenőrizve',
	'validationstatistics-old' => 'Elavult',
	'validationstatistics-utable' => 'Az alábbi lista az elmúlt óra öt legtöbbet ellenőrző szerkesztőjét mutatja.',
	'validationstatistics-user' => 'Szerkesztő',
	'validationstatistics-reviews' => 'Ellenőrzések',
);

/** Interlingua (Interlingua)
 * @author McDutchie
 */
$messages['ia'] = array(
	'validationstatistics' => 'Statisticas de validation',
	'validationstatistics-users' => "'''{{SITENAME}}''' ha al momento '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|usator|usatores}} con privilegios de [[{{MediaWiki:Validationpage}}|Redactor]] e '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|usator|usatores}} con privilegios de [[{{MediaWiki:Validationpage}}|Revisor]].

Le Redactores e Revisores es usatores establite qui pote selectivemente verificar versiones de paginas.",
	'validationstatistics-time' => "''Le sequente datos ha essite actualisate le $5 a $6.''

Le modificationes que ha essite verificate per usatores establite es considerate como revidite.

Le periodo medie de attender revision pro le modificationes facite per ''usatores non identificate'' es '''$1'''; le mediana es '''$3'''.
$4
Le retardo medie pro le [[Special:OldReviewedPages|paginas con modificationes attendente revision]] es '''$2'''.
Iste paginas es considerate ''obsolete''. Similarmente, le paginas es considerate ''synchronisate'' si le [[{{MediaWiki:Validationpage}}|version stabile]] es tamben le version provisori actual.
Le version stabile de un pagina es le version le plus nove que ha essite approbate como le version a monstrar como standard a tote le lectores.",
	'validationstatistics-table' => "Le statisticas pro cata spatio de nomines es monstrate infra, ''excludente'' le paginas de redirection.",
	'validationstatistics-ns' => 'Spatio de nomines',
	'validationstatistics-total' => 'Paginas',
	'validationstatistics-stable' => 'Revidite',
	'validationstatistics-latest' => 'Synchronisate',
	'validationstatistics-synced' => 'Synchronisate/Revidite',
	'validationstatistics-old' => 'Obsolete',
	'validationstatistics-utable' => 'Infra es le lista del 5 revisores le plus active del ultime hora.',
	'validationstatistics-user' => 'Usator',
	'validationstatistics-reviews' => 'Revisiones',
);

/** Indonesian (Bahasa Indonesia)
 * @author Bennylin
 * @author Irwangatot
 * @author Rex
 */
$messages['id'] = array(
	'validationstatistics' => 'Statistik validasi',
	'validationstatistics-users' => "'''{{SITENAME}}''' saat ini memiliki '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|pengguna|pengguna}} dengan hak akses [[{{MediaWiki:Validationpage}}|Editor]] dan
'''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|pengguna|pengguna}} dengan hak akses [[{{MediaWiki:Validationpage}}|Peninjau]].

Editor dan Peninjau adalah para pengguna terpercaya yang dapat melakukan pemeriksaan revisi di setiap halaman.",
	'validationstatistics-time' => "''Data berikut ini diperbaharui pada $5 saat $6.'' 

Suntingan yang telah diperiksa oleh pengguna terpercaya dianggap telah ditinjau. 

Rata-rata menunggu suntingan oleh ''pengguna yang belum masuk log'' untuk ditinjau adalah '' '$1'''; paling banyak adalah '''$3'''. 
$4 
Lag rata-rata untuk [[Special:OldReviewedPages|halaman dengan suntingan penundaan pemeriksaan]] adalah '''$2'''. 
Halaman ini dianggap ''belum diperbaharui''. Demikian juga, halaman akan dianggap telah ''disinkronkan'' jika  [[{{MediaWiki:Validationpage}}|versi stabil]] merupakan versi rancangan saat ini. 
Versi stabil suatu halaman adalah revisi terbaru yang telah disetujui untuk ditunjukkan secara default kepada semua pembaca.",
	'validationstatistics-table' => "Statistik untuk setiap ruang nama ditampilkan di bawah ini, ''kecuali'' halaman pengalihan.",
	'validationstatistics-ns' => 'Ruang nama',
	'validationstatistics-total' => 'Halaman',
	'validationstatistics-stable' => 'Telah ditinjau',
	'validationstatistics-latest' => 'Tersinkron',
	'validationstatistics-synced' => 'Sinkron/Tertinjau',
	'validationstatistics-old' => 'Usang',
	'validationstatistics-utable' => 'Di bawah ini adalah daftar 5 peninjau selama satu jam terakhir.',
	'validationstatistics-user' => 'Pengguna',
	'validationstatistics-reviews' => 'Tinjauan',
);

/** Ido (Ido)
 * @author Malafaya
 */
$messages['io'] = array(
	'validationstatistics-user' => 'Uzanto',
);

/** Icelandic (Íslenska)
 * @author Spacebirdy
 */
$messages['is'] = array(
	'validationstatistics-ns' => 'Nafnrými',
);

/** Italian (Italiano)
 * @author Darth Kule
 * @author Gianfranco
 * @author Melos
 * @author Pietrodn
 */
$messages['it'] = array(
	'validationstatistics' => 'Statistiche di convalidazione',
	'validationstatistics-users' => "'''{{SITENAME}}''' ha attualmente '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|utente|utenti}} con diritti di [[{{MediaWiki:Validationpage}}|Editore]] e '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|utente|utenti}} con diritti di [[{{MediaWiki:Validationpage}}|Revisore]].",
	'validationstatistics-time' => "''Ultimo aggiornamento dei dati seguenti effettuato il $5 alle $6.''

Gli edit che sono stati controllati da utenti navigati sono considerati come verificati.

Il tempo medio di attesa per la revisione degli edit di ''utenti non loggati'' è di '''$1'''; la media è di '''$3'''.  
$4
L'attesa media per [[Special:OldReviewedPages|pagine con edit non ancora controllati]] è di '''$2'''.
Queste pagine sono considerate ''non aggiornate''. Allo stesso modo, le pagine sono considerate ''sincronizzate'' se la loro [[{{MediaWiki:Validationpage}}|versione stabile]] coincide con l'attuale versione di bozza.
La versione stabile di una pagina è la revisione più recente fra quelle che sono state approvate per la visualizzazione a tutti i lettori.",
	'validationstatistics-table' => "Le statistiche per ciascun namespace sono mostrate di seguito, ''a esclusione'' delle pagine di redirect.",
	'validationstatistics-ns' => 'Namespace',
	'validationstatistics-total' => 'Pagine',
	'validationstatistics-stable' => 'Revisionate',
	'validationstatistics-latest' => 'Sincronizzate',
	'validationstatistics-synced' => 'Sincronizzate/Revisionate',
	'validationstatistics-old' => 'Non aggiornate',
	'validationstatistics-user' => 'Utente',
);

/** Japanese (日本語)
 * @author Aotake
 * @author Fryed-peach
 * @author Hosiryuhosi
 */
$messages['ja'] = array(
	'validationstatistics' => '判定統計',
	'validationstatistics-users' => "'''{{SITENAME}}''' には現在、[[{{MediaWiki:Validationpage}}|編集者]]権限をもつ利用者が '''[[Special:ListUsers/editor|$1]]'''{{PLURAL:$1|人}}、[[{{MediaWiki:Validationpage}}|査読者]]権限をもつ利用者が '''[[Special:ListUsers/reviewer|$2]]'''{{PLURAL:$2|人}}います。

編集者と査読者はページの各版に対して抜き取り検査を行うことを認められた利用者です。",
	'validationstatistics-time' => "''以下の情報は$5 $6に最終更新されました。''

信頼されている利用者によって検査された編集は「査読済」とされます。

未登録利用者による編集の平均査読待ち時間は '''$1'''、中央値は '''$3'''です。
$4
[[Special:OldReviewedPages|未査読の編集が保留となっているページ]]の平均遅延時間は '''$2'''です。このようなページは「最新版未査読」とされています。[[{{MediaWiki:Validationpage}}|固定版]]がまた最新版である場合、そのページは「最新版査読済」となります。ページの固定版とは、全読者に対して既定で表示することが承認された、最も新しい版のことです。",
	'validationstatistics-table' => '名前空間別の統計を以下に表示します。リダイレクトページは除いています。',
	'validationstatistics-ns' => '名前空間',
	'validationstatistics-total' => 'ページ数',
	'validationstatistics-stable' => '査読済',
	'validationstatistics-latest' => '最新版査読済',
	'validationstatistics-synced' => '最新版査読済/全査読済',
	'validationstatistics-old' => '最新版未査読',
	'validationstatistics-utable' => '以下は最近1時間において最も活動的な査読者5人の一覧です。',
	'validationstatistics-user' => '利用者',
	'validationstatistics-reviews' => '査読回数',
);

/** Javanese (Basa Jawa)
 * @author Pras
 */
$messages['jv'] = array(
	'validationstatistics' => 'Statistik validasi',
	'validationstatistics-users' => "'''{{SITENAME}}''' wektu iki duwé '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|panganggo|panganggo}} kanthi hak aksès [[{{MediaWiki:Validationpage}}|Editor]] lan '''[[Special:ListUsers/pamriksa|$2]]''' {{PLURAL:$2|panganggo|panganggo}} kanthi hak aksès [[{{MediaWiki:Validationpage}}|Pamriksa]].

Editor lan Pamriksa iku panganggo mapan sing bisa mriksa langsung owah-owahan kaca.",
	'validationstatistics-table' => "Statistik kanggo saben bilik jeneng ditampilaké ing ngisor iki, ''kajaba'' kaca pangalihan.",
	'validationstatistics-ns' => 'Bilik jeneng',
	'validationstatistics-total' => 'Kaca',
	'validationstatistics-stable' => 'Wis dipriksa',
	'validationstatistics-latest' => 'Wis disinkronaké',
	'validationstatistics-synced' => 'Wis disinkronaké/Wis dipriksa',
	'validationstatistics-old' => 'Lawas',
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
 * @author Klutzy
 * @author Kwj2772
 * @author Yknok29
 */
$messages['ko'] = array(
	'validationstatistics' => '검토 통계',
	'validationstatistics-users' => "'''{{SITENAME}}'''에는 [[Special:ListUsers/editor|$1]]명의 [[{{MediaWiki:Validationpage}}|편집자]] 권한을 가진 사용자와 [[Special:ListUsers/reviewer|$2]]명의 [[{{MediaWiki:Validationpage}}|평론가]] 권한을 가진 사용자가 있습니다.

편집자와 평론자가 문서를 검토할 수 있습니다.",
	'validationstatistics-time' => "'''다음의 수치는 $5 $6에 마지막으로 업데이트되었습니다.'''

숙련된 사용자가 확인한 편집을 검토된 편집으로 간주합니다.

'''로그인하지 않은 사용자'''의 편집의 평균 대기 시간은 '''$1'''이고 중앙값은 '''$3'''입니다.
$4
[[Special:OldReviewedPages|검토되지 않은 편집이 있는 문서]]의 검토 평균 대기 시간은 '''$2'''입니다.
이 문서는 오래 전에 검토되었으며, [[{{MediaWiki:Validationpage}}|안정 버전]]이 현재 버전과 일치할 때 동기화되었다고 표현합니다.
문서의 안정 버전이 모든 독자에게 기본적으로 보여질 것입니다.",
	'validationstatistics-table' => "넘겨주기 문서를 '''제외한''' 문서의 검토 통계가 이름공간별로 보여지고 있습니다.",
	'validationstatistics-ns' => '이름공간',
	'validationstatistics-total' => '문서 수',
	'validationstatistics-user' => '사용자',
);

/** Ripoarisch (Ripoarisch)
 * @author Purodha
 */
$messages['ksh'] = array(
	'validationstatistics' => 'Shtatistike vun de Beschtätijunge för Sigge',
	'validationstatistics-users' => "De '''{{SITENAME}}''' hät em Momang [[Special:ListUsers/editor|{{PLURAL:$1|'''eine''' Metmaacher|'''$1''' Metmaacher|'''keine''' Metmaacher}}]] met däm Rääsch, der [[{{MediaWiki:Validationpage}}|{{int:group-editor-member}}]] ze maache, un [[Special:ListUsers/reviewer|{{PLURAL:$2|'''eine''' Metmaacher|'''$2''' Metmaacher|'''keine''' Metmaacher}}]] met däm Rääsch, der [[{{MediaWiki:Validationpage}}|{{int:group-reviewer-member}}]] ze maache.

{{int:group-editor}}, un {{int:group-reviewer}}, sin doför aanerkannte un extra ußjesöhk Metmaacher, di Versione vun Sigge beshtäteje künne.",
	'validationstatistics-time' => "'''Opjepaß:''' ''De Date hee noh sen vum $5 öm $6 Uhr, se künnte alsu nit janz de Neuste sin.''

Dä Meddelwäät för de Zick för op Änderunge vun namelose Metmaachere ze waade, es '''$1'''<!-- Shtunde, Menutte, un Sekunde-->. Dä Medianwäät es '''$3'''<!-- Shtunde, Menutte, un Sekunde-->.
$4
Der Dorschnitt vun de Zick, wo [[Special:OldReviewedPages|ahl Sigge]] hengerher hingke, es '''$2'''<!-- Shtunde, Menutte, un Sekunde-->.
Di Sigge sen ''{{lcfirst:{{int:validationstatistics-old}}}}''.

Dommet zopaß, donn Sigge als ''{{lcfirst:{{int:validationstatistics-latest}}}}'' jellde, wann de [[{{MediaWiki:Validationpage}}|{{int:stablepages-stable}}]] och dä aktoälle Äntworf es. De {{int:stablepages-stable}} es de neuste Version vun en Sigg, di winnischßdens vun einem Metmaacher en Beschtäätejung hät.",
	'validationstatistics-table' => 'Statistike för jedes Appachtemang (oohne de Sigge met Ömleijdunge)',
	'validationstatistics-ns' => 'Appachtemang',
	'validationstatistics-total' => 'Sigge ensjesamp',
	'validationstatistics-stable' => 'Aanjekik',
	'validationstatistics-latest' => '<span style="white-space:nowrap">A-juur</span>',
	'validationstatistics-synced' => '{{int:validationstatistics-stable}} un {{int:validationstatistics-latest}}',
	'validationstatistics-old' => 'Övverhollt',
	'validationstatistics-utable' => 'Hee dronger shteiht de Leß met de aktievste 5 unger de {{int:reviewer}} en de läzte Shtond.',
	'validationstatistics-user' => 'Metmaacher',
	'validationstatistics-reviews' => 'Mohlde en Sigg beshtätesh',
);

/** Cornish (Kernowek)
 * @author Kernoweger
 * @author Kw-Moon
 */
$messages['kw'] = array(
	'validationstatistics-total' => 'Folennow',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Les Meloures
 * @author Robby
 */
$messages['lb'] = array(
	'validationstatistics' => 'Statistike vun de Validatiounen',
	'validationstatistics-users' => "'''{{SITENAME}}''' huet elo '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|Benotzer|Benotzer}} mat [[{{MediaWiki:Validationpage}}|Editeursrechter]] an '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|Benotzer|Benotzer}} mat [[{{MediaWiki:Validationpage}}|Validatiounsrechter]].

Editeuren a Valideure si confirméiert Benotzer déi iwwerpréifte Versioune vu Säiten derbäisetze kënnen.",
	'validationstatistics-time' => "''Dës Donnéeë goufe fir d'lescht den $5 ëm $6 Auer aktualiséiert.''

Déi duerchschnettlech Waardezäit fir Ännerungen, déi vun net ugemellte Benotzer kommen, ass '''$1'''; de Median ass '''$3'''.
$4
Den duerchschnettleche Réckstand op [[Special:OldReviewedPages|vereelste Säiten]] ass '''$2'''.
Dës Säite ginn als ''vereelst'' ugesinn. Säite ginn als ''synchroniséiert'' wann déi  [[{{MediaWiki:Validationpage}}|stabil Versioun]] och déi aktuell Virbereedung ass.
Déi stabil Versioun vun enger Säit ass déi neiste Versioun, déi durch en Editeur confirméiert guf an als Standard alle Lieser gewise gëtt.",
	'validationstatistics-table' => 'Statistike fir jiddwer Nummraum sinn hei ënnendrënner, Viruleedungssäite sinn net berécksichtegt.',
	'validationstatistics-ns' => 'Nummraum',
	'validationstatistics-total' => 'Säiten',
	'validationstatistics-stable' => 'Validéiert',
	'validationstatistics-latest' => 'Synchroniséiert',
	'validationstatistics-synced' => 'Synchroniséiert/Nogekuckt',
	'validationstatistics-old' => 'Ofgelaf',
	'validationstatistics-utable' => "Hei ënnendrënner ass d'Lëscht mat de 5 Benotzer, déi an der leschter Stonn am meeschte Bewäertunge gemaach hunn.",
	'validationstatistics-user' => 'Benotzer',
	'validationstatistics-reviews' => 'Bewäertungen',
);

/** Eastern Mari (Олык Марий)
 * @author Сай
 */
$messages['mhr'] = array(
	'validationstatistics-ns' => 'Лӱм-влакын кумдыкышт',
);

/** Macedonian (Македонски)
 * @author Bjankuloski06
 * @author Brest
 */
$messages['mk'] = array(
	'validationstatistics' => 'Потврдни статистики',
	'validationstatistics-users' => "{{SITENAME}}''' во моментов има '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|корисник|корисници}} со [[{{MediaWiki:Validationpage}}|уредувачки]] права и [[Special:ListUsers/reviewer|$2]] {{PLURAL:$2|корисник|корисници}} со [[{{MediaWiki:Validationpage}}|проверувачки]] права.

Уредниците и проверувачите се докажани корисници кои можат да вршат проверки на верзии на страници.",
	'validationstatistics-time' => "''Следниве податоци се ажурирани на $5 во $6.''

Уредувањата кои се проверени од утврдени корисници се сметаат за проверени.

Просечното чекање за уредувања направени од ''ненајавени корисници'' коишто треба да се прегледаат изнесува '''$1'''; средната вредност е '''$3'''.  
$4
Просечното задоцнување за [[Special:OldReviewedPages|страници со непроверени уредувања во исчекување]] изнесува '''$2'''.
Овие страници се сметаат за ''застарени''. Наспроти тоа, страниците се сметаат за ''синхронизирани'' ако [[{{MediaWiki:Validationpage}}|стабилната верзија]] е едновремено и моментална работна верзија.
Стабилната верзија на една страница е најновата верзија која е одобрена за прикажување на сите читатели по основно.",
	'validationstatistics-table' => "Подолу се прикажани статистики за секој именски простор, ''освен'' страници за пренасочување.",
	'validationstatistics-ns' => 'Именски простор',
	'validationstatistics-total' => 'Страници',
	'validationstatistics-stable' => 'Прегледани',
	'validationstatistics-latest' => 'Синхронизирано',
	'validationstatistics-synced' => 'Синхронизирани/Прегледани',
	'validationstatistics-old' => 'Застарени',
	'validationstatistics-utable' => 'Еве листа на 5 најактивни прегледувачи во последниов час.',
	'validationstatistics-user' => 'Корисник',
	'validationstatistics-reviews' => 'Прегледи',
);

/** Malayalam (മലയാളം)
 * @author Praveenp
 * @author Sadik Khalid
 */
$messages['ml'] = array(
	'validationstatistics' => 'സ്ഥിരീകരണ കണക്കുകള്‍',
	'validationstatistics-users' => "'''{{SITENAME}}''' പദ്ധതിയില്‍ '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|ഉപയോക്താവ്|ഉപയോക്താക്കള്‍}} [[{{MediaWiki:Validationpage}}|സംശോധകര്‍]] അധികാരമുള്ളവരും '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|ഉപയോക്താവ്|ഉപയോക്താക്കള്‍}} [[{{MediaWiki:Validationpage}}|പരിശോധകര്‍]] അധികാരമുള്ളവരും ആണ്.",
	'validationstatistics-table' => "ഓരോ നാമമേഖലയിലേയും സ്ഥിതിവിവരക്കണക്കുകൾ താഴെ കൊടുക്കുന്നു, തിരിച്ചുവിടൽ താളുകൾ ''ഒഴിവാക്കുന്നു''.",
	'validationstatistics-ns' => 'നാമമേഖല',
	'validationstatistics-total' => 'താളുകള്‍',
	'validationstatistics-stable' => 'പരിശോധിച്ചവ',
	'validationstatistics-latest' => 'ഏകതാനമാക്കിയിരിക്കുന്നു',
	'validationstatistics-synced' => 'ഏകകാലികമാക്കിയവ/പരിശോധിച്ചവ',
	'validationstatistics-old' => 'കാലഹരണപ്പെട്ടവ',
	'validationstatistics-utable' => 'കഴിഞ്ഞ മണിക്കൂറിലെ ആദ്യ അഞ്ച് സംശോധകരുടെ പട്ടികയാണ് താഴെയുള്ളത്.',
	'validationstatistics-user' => 'ഉപയോക്താവ്',
	'validationstatistics-reviews' => 'സംശോധനങ്ങൾ',
);

/** Mongolian (Монгол)
 * @author Chinneeb
 */
$messages['mn'] = array(
	'validationstatistics-ns' => 'Нэрний зай',
);

/** Malay (Bahasa Melayu)
 * @author Aviator
 * @author Kurniasan
 */
$messages['ms'] = array(
	'validationstatistics' => 'Statistik pengesahan',
	'validationstatistics-users' => "'''{{SITENAME}}''' kini mempunyai {{PLURAL:$1|seorang|'''[[Special:ListUsers/editor|$1]]''' orang}} pengguna dengan hak [[{{MediaWiki:Validationpage}}|Penyunting]] dan {{PLURAL:$2|seorang|'''$2''' orang}} pengguna '''[[Special:ListUsers/reviewer|$2]]''' dengan hak [[{{MediaWiki:Validationpage}}|Penyemak]].

Penyunting dan Penyemak adalah pengguna-pengguna berhak yang boleh memeriksa semakan-semakan kepada laman-laman.",
	'validationstatistics-table' => "Statistik bagi setiap ruang nama ditunjukkan di bawah, ''melainkan'' halaman lencongan.",
	'validationstatistics-ns' => 'Ruang nama',
	'validationstatistics-total' => 'Laman',
	'validationstatistics-stable' => 'Diperiksa',
	'validationstatistics-latest' => 'Diselaras',
	'validationstatistics-synced' => 'Diselaras/Disemak',
	'validationstatistics-old' => 'Lapuk',
	'validationstatistics-utable' => 'Di bawah adalah senarai 5 penyemak teratas dalam jam terakhir.',
	'validationstatistics-user' => 'Pengguna',
	'validationstatistics-reviews' => 'Semakan',
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
	'validationstatistics-users' => "'''{{SITENAME}}''' heeft op het moment '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|gebruiker|gebruikers}} in de rol van [[{{MediaWiki:Validationpage}}|Redacteur]] en '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|gebruiker|gebruikers}} met de rol [[{{MediaWiki:Validationpage}}|Eindredacteur]].

Redacteuren en eindredacteuren zijn gebruikers die zich bewezen hebben en versies van pagina's als gecontroleerd mogen markeren.",
	'validationstatistics-time' => "'''De volgende gegevens zijn voor het laatst bijgewerkt op $5 om $6.'''

Van een bewerking wordt aangenomen dat eindredactie is uitgevoerd als deze is gecontroleerd door een ervaren gebruiker.

De gemiddelde wachttijd voor bewerkingen door ''gebruikers die niet aangemeld zijn'' is '''$1'''; de mediaan is '''$3'''.
$4
De gemiddelde achterstand voor [[Special:OldReviewedPages|verouderde pagina's]] is '''$2'''.
Deze pagina's worden beschouwd als ''verouderd''.
Pagina's worden beschouwd als ''gesynchroniseerd'' als de [[{{MediaWiki:Validationpage}}|stabiele versie]] ook de werkversie is.
Stabiele versies zijn versies van pagina's die tenminste door een eindredacteur zijn gekeurd.
De stabiele versie van een pagina is de meeste recente versie waarvoor is aangegeven dat die standaard aan alle gebruikers aangeboden kan worden.",
	'validationstatistics-table' => "Hieronder staan statistieken voor iedere naamruimte, ''exclusief'' doorverwijzingen.",
	'validationstatistics-ns' => 'Naamruimte',
	'validationstatistics-total' => "Pagina's",
	'validationstatistics-stable' => 'Eindredactie afgerond',
	'validationstatistics-latest' => 'Gesynchroniseerd',
	'validationstatistics-synced' => 'Gesynchroniseerd/Eindredactie',
	'validationstatistics-old' => 'Verouderd',
	'validationstatistics-utable' => 'In de onderstaande lijst worden de vijf meest actieve eindredacteuren.',
	'validationstatistics-user' => 'Gebruiker',
	'validationstatistics-reviews' => 'Beoordelingen',
);

/** Norwegian Nynorsk (‪Norsk (nynorsk)‬)
 * @author Gunnernett
 * @author Harald Khan
 */
$messages['nn'] = array(
	'validationstatistics' => 'Valideringsstatistikk',
	'validationstatistics-users' => "'''{{SITENAME}}''' har på noverande tidspunkt {{PLURAL:$1|'''éin''' brukar|'''[[Special:ListUsers/editor|$1]]''' brukarar}} med [[{{MediaWiki:Validationpage}}|skribentrettar]] og {{PLURAL:$1|'''éin''' brukar|'''$2''' brukarar}} med [[{{MediaWiki:Validationpage}}|meldarrettar]].",
	'validationstatistics-time' => "''Fylgjande data er mellomlagra og kan vera uaktuell.''

Gjennomsnittleg ventetid for endringar av ''uinnlogga brukarar'' til gjennomsyn er '''$1'''; medianen er '''$3'''.
$4
Gjennomsnittleg forseinking for [[Special:OldReviewedPages|sider med endringar som ikkje er sett gjennom]] er '''$2'''.

Desse sidene vert sett på som ''forelda''. På same vis vert sider rekna som ''synkronisert'' om den [[{{MediaWiki:Validationpage}}|stabile versjonen]] òg er den noverande utkast-versjonen.
Stabile versjonar er sideversjonar for sider som er sjekka av minst ein etablert brukar.",
	'validationstatistics-table' => "Statistikk for kvart namnerom er synt nedanfor, ''utanom'' omdirigeringssider.",
	'validationstatistics-ns' => 'Namnerom',
	'validationstatistics-total' => 'Sider',
	'validationstatistics-stable' => 'Vurdert',
	'validationstatistics-latest' => 'Synkronisert',
	'validationstatistics-synced' => 'Synkronisert/Vurdert',
	'validationstatistics-old' => 'Utdatert',
	'validationstatistics-user' => 'Brukar',
);

/** Norwegian (bokmål)‬ (‪Norsk (bokmål)‬)
 * @author Jon Harald Søby
 * @author Nghtwlkr
 */
$messages['no'] = array(
	'validationstatistics' => 'Valideringsstatistikk',
	'validationstatistics-users' => "'''{{SITENAME}}''' har på nåværende tidspunkt '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|bruker|brukere}} med [[{{MediaWiki:Validationpage}}|skribentrettigheter]] og ''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|bruker|brukere}} med [[{{MediaWiki:Validationpage}}|anmelderrettigheter]].

Skribenter og Anmeldere er etablerte brukere som kan punktsjekke revisjoner på siden.",
	'validationstatistics-time' => "''Følgende data er mellomlagret og kan være utdatert.''

Endringer som har blitt sjekket av etablerte brukere blir ansett som vurderte.

Gjennomsnittlig ventetid for endringer av ''uinnlogga brukere'' til vurdering er '''$1'''; medianen er '''$3'''.
$4
Gjennomsnittlig forsinking for [[Special:OldReviewedPages|sider med endringer som ikke er vurdert]] er '''$2'''.

Disse sidene blir sett på som ''foreldet''. På same måte blir sider regnet som ''synkronisert'' om den [[{{MediaWiki:Validationpage}}|stabile versjonen]] også er den nåværende utkast-versjonen.
Stabile versjoner er sideversjoner for sider som er sjekket av minst en etablert bruker.",
	'validationstatistics-table' => "Statistikk for hvert navnerom vises nedenfor, ''utenom'' omdirigeringssider.",
	'validationstatistics-ns' => 'Navnerom',
	'validationstatistics-total' => 'Sider',
	'validationstatistics-stable' => 'Anmeldt',
	'validationstatistics-latest' => 'Synkronisert',
	'validationstatistics-synced' => 'Synkronisert/Anmeldt',
	'validationstatistics-old' => 'Foreldet',
	'validationstatistics-utable' => 'Under er en liste med de topp 5 anmelderne den siste timen.',
	'validationstatistics-user' => 'Bruker',
	'validationstatistics-reviews' => 'Anmeldelser',
);

/** Occitan (Occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'validationstatistics' => 'Estatisticas de validacion',
	'validationstatistics-users' => "'''{{SITENAME}}''' dispausa actualament de '''[[Special:ListUsers/editor|$1]]''' utilizaire{{PLURAL:$1||s}} amb los dreches de [[{{MediaWiki:Validationpage}}|contributor]] e de '''[[Special:ListUsers/reviewer|$2]]''' utilizaire{{PLURAL:$2||s}} amb los dreches de [[{{MediaWiki:Validationpage}}|relector]].

Los contributors e relectors son d'utilizaires establits que pòdon verificar las revisions de las paginas.",
	'validationstatistics-time' => "'''Las donadas seguentas son en amagatal e son estadas mesas a jorn pel darrièr còp lo $5 a $6.''

Las modificacions que son estadas verificadas per d'utilizaires establits son consideradas coma relegidas.

Lo temps de relectura mejan de las modificacions per ''d'utilizaires que son pas connectats'' es '''$1''' ; la valor mediana es '''$3'''.
$4
Lo relambi mejan per las [[Special:OldReviewedPages|paginas que contenon de modificacions pas relegidas en cors]] es '''$2'''.
Aquelas paginas son consideradas ''perimidas''. Amai, las paginas son declaradas ''sincronizadas'' se la [[{{MediaWiki:Validationpage}}|version establa]] es tanben la version de borrolhon correnta.
Las versions establas son de versions de paginas verificadas per al mens un utilizaire regular.",
	'validationstatistics-table' => "Las estatisticas per cada espaci de noms son afichadas çaijós, a ''l’exclusion'' de las paginas de redireccion.",
	'validationstatistics-ns' => 'Nom de l’espaci',
	'validationstatistics-total' => 'Paginas',
	'validationstatistics-stable' => 'Relegit',
	'validationstatistics-latest' => 'Sincronizada',
	'validationstatistics-synced' => 'Sincronizat/Relegit',
	'validationstatistics-old' => 'Desuet',
	'validationstatistics-utable' => 'Çaijós figuran los 5 melhors relectors dins la darrièra ora.',
	'validationstatistics-user' => 'Utilizaire',
	'validationstatistics-reviews' => 'Relectors',
);

/** Deitsch (Deitsch)
 * @author Xqt
 */
$messages['pdc'] = array(
	'validationstatistics-ns' => 'Blatznaame',
	'validationstatistics-total' => 'Bledder',
	'validationstatistics-user' => 'Yuuser',
);

/** Polish (Polski)
 * @author Jwitos
 * @author Leinad
 * @author Sp5uhe
 * @author ToSter
 * @author Wpedzich
 */
$messages['pl'] = array(
	'validationstatistics' => 'Statystyki oznaczania',
	'validationstatistics-users' => "W '''{{GRAMMAR:MS.lp|{{SITENAME}}}}''' zarejestrowanych jest obecnie  '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|użytkownik|użytkowników}} z uprawnieniami [[{{MediaWiki:Validationpage}}|redaktora]] oraz  '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|użytkownik|użytkowników}} z uprawnieniami [[{{MediaWiki:Validationpage}}|weryfikatora]].

Redaktorzy i weryfikatorzy to doświadczeni użytkownicy, którzy mogą oznaczać dowolne wersje stron.",
	'validationstatistics-time' => "''Ostatnia aktualizacja danych na tej stronie miała miejsce $5 o godzinie $6.''

Edycje wykonane przez doświadczonych użytkowników są uznawane za przejrzane.

Średni czas oczekiwania na sprawdzenie edycji wykonanych przez ''niezalogowanych użytkowników'' wynosi '''$1''', a mediana '''$3'''.
$4
Średnie opóźnienie dla [[Special:OldReviewedPages|oczekujących na przejrzenie edycji]] wynosi '''$2'''.
Strony z takimi edycjami uznawane są za ''zdezaktualizowane''. Za ''niezsynchronizowane'' uznawane są strony, jeśli posiadają w historii [[{{MediaWiki:Validationpage}}|wersję oznaczoną]], ale ostatnio wykonana edycja jest wersją roboczą.
Wersja oznaczona strony to najnowsza wersja, która została zaakceptowana jako domyślnie prezentowana wszystkim czytelnikom.",
	'validationstatistics-table' => "Poniżej znajdują się statystyki dla każdej przestrzeni nazw, ''z wyłączeniem'' przekierowań.",
	'validationstatistics-ns' => 'Przestrzeń nazw',
	'validationstatistics-total' => 'Stron',
	'validationstatistics-stable' => 'Przejrzanych',
	'validationstatistics-latest' => 'Z ostatnią edycją oznaczoną jako przejrzana',
	'validationstatistics-synced' => 'Zsynchronizowanych lub przejrzanych',
	'validationstatistics-old' => 'Zdezaktualizowane',
	'validationstatistics-utable' => 'Poniżej znajduje się lista 5 użytkowników najaktywniej oznaczających strony w ciągu ostatniej godziny.',
	'validationstatistics-user' => 'Użytkownik',
	'validationstatistics-reviews' => 'Liczba oznaczeń',
);

/** Piedmontese (Piemontèis)
 * @author Dragonòt
 */
$messages['pms'] = array(
	'validationstatistics' => 'Statìstiche ëd validassion',
	'validationstatistics-users' => "'''{{SITENAME}}''' al moment a l'ha '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|utent|utent}} con drit d'[[{{MediaWiki:Validationpage}}|Editor]] 
e '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|utent|utent}} con drit ëd [[{{MediaWiki:Validationpage}}|Revisor]].

Editor e Revisor a son utent che a peulo controlé le revision a le pàgine.",
	'validationstatistics-time' => "''Ij dat sota a son stàit modificà l'ùltima vira ël $5 a $6.''

Le modìfiche che a son ëstàite controlà da utent intrà a son considrà da esse revisionà.

L'atesa media ëd le modìfiche fàite da j'''utent pa intrà'' për esse revisionà a l'é '''$1'''; la median-a a l'é '''$3'''.
$4
L'artard medi për [[Special:OldReviewedPages|pàgine con modìfiche pa revisionà pendente]] a l'é '''$2'''. Le pàgine a son considrà '''veje'''. Ant la midema manera, le pàgine a son considrà '''sincronisà''' se la [[{{MediaWiki:Validationpage}}|vërsion stàbila]] a l'é ëdcò la vërsion sbòssa curenta.
La vërsion stàbila ëd la pàgina a l'é la revision pì neuva che a l'é stàita aprovà për mosté a tùit ij visitador.",
	'validationstatistics-table' => "Lë statìstiche për minca spassi nominal a son mostà sota, ''an gavand'' le pàgine ëd rediression.",
	'validationstatistics-ns' => 'Spassi nominal',
	'validationstatistics-total' => 'Pàgine',
	'validationstatistics-stable' => 'Revisionà',
	'validationstatistics-latest' => 'Sincronisà',
	'validationstatistics-synced' => 'Sincronisà/Revisionà',
	'validationstatistics-old' => 'Veje',
	'validationstatistics-utable' => "Sota a-i é la lista dij prim 5 revisor ëd l'ùltima ora.",
	'validationstatistics-user' => 'Utent',
	'validationstatistics-reviews' => 'Revisor',
);

/** Pashto (پښتو)
 * @author Ahmed-Najib-Biabani-Ibrahimkhel
 */
$messages['ps'] = array(
	'validationstatistics-ns' => 'نوم-تشيال',
	'validationstatistics-total' => 'مخونه',
	'validationstatistics-user' => 'کارن',
);

/** Portuguese (Português)
 * @author 555
 * @author Giro720
 * @author Hamilton Abreu
 * @author Lijealso
 * @author Malafaya
 * @author Waldir
 */
$messages['pt'] = array(
	'validationstatistics' => 'Estatísticas de validações',
	'validationstatistics-users' => "A '''{{SITENAME}}''' possui, neste momento, '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|utilizador|utilizadores}} com privilégios de [[{{MediaWiki:Validationpage}}|Editor]] 
e '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|utilizador|utilizadores}} com privilégios de [[{{MediaWiki:Validationpage}}|Crítico]].

Editores e Críticos são utilizadores estabelecidos que podem verificar detalhadamente revisões de páginas.",
	'validationstatistics-time' => "''Os seguintes dados foram atualizados pela última vez a $5 às $6.''

Edições que foram verificadas por utilizadores estabelecidos são consideradas como revistas.

O tempo médio de espera para edições feitas por ''utilizadores não autenticados'' serem revistas é '''$1'''; a mediana é '''$3'''.  
$4
O atraso médio para [[Special:OldReviewedPages|páginas com edições não revistas em espera]] é '''$2'''.
Estas páginas são consideradas ''desatualizadas''. Igualmente, as páginas são consideradas ''sincronizadas'' se a [[{{MediaWiki:Validationpage}}|versão estável]] for também a versão rascunho atual.
A versão estável de uma página é a revisão mais recente que foi aprovada para ser apresentada por defeito a todos os leitores.",
	'validationstatistics-table' => "São apresentadas abaixo estatísticas para cada espaço nominal, '''excluindo''' páginas de redireccionamento.",
	'validationstatistics-ns' => 'Espaço nominal',
	'validationstatistics-total' => 'Páginas',
	'validationstatistics-stable' => 'Analisadas',
	'validationstatistics-latest' => 'Sincronizada',
	'validationstatistics-synced' => 'Sincronizadas/Analisadas',
	'validationstatistics-old' => 'Desactualizadas',
	'validationstatistics-utable' => 'Abaixo está a lista dos 5 revisores mais ativos na última hora.',
	'validationstatistics-user' => 'Utilizador',
	'validationstatistics-reviews' => 'Revisões',
);

/** Brazilian Portuguese (Português do Brasil)
 * @author Eduardo.mps
 */
$messages['pt-br'] = array(
	'validationstatistics' => 'Estatísticas de validações',
	'validationstatistics-users' => "'''{{SITENAME}}''' possui, no momento, '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|utilizador|utilizadores}} com privilégios de [[{{MediaWiki:Validationpage}}|Editor]]  
e '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|utilizador|utilizadores}} com privilégios de [[{{MediaWiki:Validationpage}}|Crítico]].

Editores e Críticos são utilizadores estabelecidos que podem verificar detalhadamente revisões de páginas.",
	'validationstatistics-time' => "''Os seguintes dados estão em cache e podem não estar atualizados.''

Edições que foram verificadas por utilizadores estabelecidos são consideradas como revistas.

O tempo médio de espera para edições feitas por ''utilizadores não autenticados'' serem revistas é '''$1'''; a mediana é '''$3'''.   
$4
O atraso médio para [[Special:OldReviewedPages|páginas com edições não revistas em espera]] é '''$2'''.
Estas páginas são consideradas ''desatualizadas''. Igualmente, as páginas são consideradas ''sincronizadas'' se a [[{{MediaWiki:Validationpage}}|versão estável]] for também a versão rascunho atual.
A versão estável de uma página é a revisão mais recente que foi aprovada para ser apresentada por padrão a todos os leitores.",
	'validationstatistics-table' => "As estatísticas de cada domínio são exibidas a seguir, '''excetuando-se''' as páginas de redirecionamento.",
	'validationstatistics-ns' => 'Espaço nominal',
	'validationstatistics-total' => 'Páginas',
	'validationstatistics-stable' => 'Analisadas',
	'validationstatistics-latest' => 'Sincronizada',
	'validationstatistics-synced' => 'Sincronizadas/Analisadas',
	'validationstatistics-old' => 'Desatualizadas',
	'validationstatistics-utable' => 'Abaixo está uma lista dos 5 maiores analisadores na última hora.',
	'validationstatistics-user' => 'Utilizador',
	'validationstatistics-reviews' => 'Análises',
);

/** Romanian (Română)
 * @author Firilacroco
 * @author KlaudiuMihaila
 * @author Mihai
 */
$messages['ro'] = array(
	'validationstatistics-users' => "'''{{SITENAME}}''' are în prezent '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|utilizator|utilizatori}} cu drepturi de [[{{MediaWiki:Validationpage}}|editare]]
şi '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|utilizator|utilizatori}} cu drepturi de [[{{MediaWiki:Validationpage}}|recenzie]].

Editorii şi recenzorii sunt utilizatori stabiliţi care pot verifica modificările din pagini.",
	'validationstatistics-ns' => 'Spaţiu de nume',
	'validationstatistics-total' => 'Pagini',
	'validationstatistics-user' => 'Utilizator',
);

/** Tarandíne (Tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'validationstatistics' => 'Statisteche de validazione',
	'validationstatistics-users' => "'''{{SITENAME}}''' jndr'à quiste mumende tène '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|utende|utinde}} cu le deritte de [[{{MediaWiki:Validationpage}}|cangiatore]] e '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|utende|utinde}} cu le deritte de[[{{MediaWiki:Validationpage}}|revisione]].

Le cangiature e le revisure onne state stabbelite da l'utinde ca ponne verificà le revisiune de le pàggene.",
	'validationstatistics-time' => "'''A seguende date ha state aggiornate l'urtema vote 'u $5 a le $6.''

Le cangiaminde ca onne state verificate da l'utinde stabbelite onne state conziderate riviste.

'A medie attese pe le cangiaminde da ''utinde ca non ge sonde collegate'' jè '''$1'''; 'a medie jè '''$3'''.
$4
'U timbe medie pe le [[Special:OldReviewedPages|pàggene non aggiornete]] jè '''$2'''.
Ste pàggene sonde conziderate ''scadute''. Pò, stonne le pàggene ca sonde conziderate ''singronizzate'' ce 'a [[{{MediaWiki:Validationpage}}|versiona secure]] jè ppure 'a versiona attuale a bozza.
Le versiune secure sonde revisiune de pàggene verificate da almene 'n'utende stabbilite.",
	'validationstatistics-table' => "Le statisteche pe ogne namespace sonde mostrete aqquà sotte, 'scludenne le pàggene de redirezionaminde.",
	'validationstatistics-ns' => 'Neimspeise',
	'validationstatistics-total' => 'Pàggene',
	'validationstatistics-stable' => 'Riviste',
	'validationstatistics-latest' => 'Singronizzate',
	'validationstatistics-synced' => 'Singronizzete/Riviste',
	'validationstatistics-old' => "Non g'è aggiornete",
	'validationstatistics-utable' => "Sotte ste 'na liste de le 5 cchiù 'mbortande revisure de l'urtema ore.",
	'validationstatistics-user' => 'Utende',
	'validationstatistics-reviews' => 'Reviste',
);

/** Russian (Русский)
 * @author Ahonc
 * @author AlexSm
 * @author Claymore
 * @author Ferrer
 * @author Lockal
 * @author Putnik
 * @author Sergey kudryavtsev
 * @author Александр Сигачёв
 */
$messages['ru'] = array(
	'validationstatistics' => 'Статистика проверок',
	'validationstatistics-users' => "В проекте {{SITENAME}} на данный момент '''[[Special:ListUsers/editor|$1]]''' {{plural:$1|участник имееет|участника имеют|участников имеют}} права [[{{MediaWiki:Validationpage}}|«редактора»]] и '''[[Special:ListUsers/reviewer|$2]]''' {{plural:$2|участник имеет|участника имеют|участников имеют}} права [[{{MediaWiki:Validationpage}}|«проверяющего»]].

«Редакторы» и «проверяющие» — это определённые участники, имеющие возможность делать выборочную проверку конкретных версий страниц.",
	'validationstatistics-time' => "''Приведённая ниже информация была обновлена $5 в $6.''

Правки, отмеченные определёнными участниками, считаются проверенными.

Среднее ожидание проверки правок ''непредставившихся участников'' равно '''$1'''; медиана равна '''$3'''.
$4
Средняя задержка для [[Special:OldReviewedPages|страниц с недосмотренными правками]] равна '''$2'''.
Данные страницы считаются ''устаревшими''. В свою очередь, страницы считаются ''синхронизированными'', если [[{{MediaWiki:Validationpage}}|стабильная версия]] одновременно является текущей черновой версией.
Стабильные версии — это наиболее новые версии страниц, из тех, которые были утверждены для показа по умолчанию всем читателям.",
	'validationstatistics-table' => "Ниже представлена статистика по каждому пространству имён, ''исключая'' страницы перенаправлений.",
	'validationstatistics-ns' => 'Пространство',
	'validationstatistics-total' => 'Страниц',
	'validationstatistics-stable' => 'Проверенные',
	'validationstatistics-latest' => 'Перепроверенные',
	'validationstatistics-synced' => 'Доля перепроверенных в проверенных',
	'validationstatistics-old' => 'Устаревшие',
	'validationstatistics-utable' => 'Ниже приведен список из 5 наиболее активных выверяющих за последний час.',
	'validationstatistics-user' => 'Участник',
	'validationstatistics-reviews' => 'Проверок',
);

/** Yakut (Саха тыла)
 * @author HalanTul
 */
$messages['sah'] = array(
	'validationstatistics' => 'Тургутуу статиистиката',
	'validationstatistics-table' => "Аллара утаарыылартан ''ураты'' ааттар далларын статиистиката бэриллибит.",
	'validationstatistics-ns' => 'Аат дала',
	'validationstatistics-total' => 'Сирэй',
	'validationstatistics-stable' => 'Тургутуллубут',
	'validationstatistics-latest' => 'Хат тургутуллубут',
	'validationstatistics-synced' => 'Хат тургутуллубуттар тургутуллубуттар истэригэр бырыаннара',
	'validationstatistics-old' => 'Эргэрбит',
	'validationstatistics-utable' => 'Бүтэһик чааска ордук көхтөөх 5 тургутааччы тиһигэ көстөр.',
	'validationstatistics-user' => 'Кыттааччы',
	'validationstatistics-reviews' => 'Бэрэбиэркэ',
);

/** Slovak (Slovenčina)
 * @author Helix84
 */
$messages['sk'] = array(
	'validationstatistics' => 'Štatistiky overenia',
	'validationstatistics-users' => "'''{{SITENAME}}''' má momentálne '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|používateľa|používateľov}} s právami [[{{MediaWiki:Validationpage}}|redaktor]] a '''[[Special:ListUsers/reviewer|$2]]'' {{PLURAL:$2|používateľa|používateľov}} s právami [[{{MediaWiki:Validationpage}}|kontrolór]].",
	'validationstatistics-time' => "''Nasledovné údaje boli naposledy aktualizované $5 o $6.''

Priemerné čakanie na úpravy ''anonymných používateľov'' je '''$1'''; medián je '''$3'''.  
$4
Priemerné oneskorenie [[Special:OldReviewedPages|zastaralých stránok]] je '''$2'''.
Tieto stránky sa považujú za ''zastaralé''. Podobne, stránky sa považujú za ''synchronizované'' ak je [[{{MediaWiki:Validationpage}}|stabilná verzia]] zároveň súčasný návrh.
Stabilná verzia je revízia stránky, ktorú skontroloval aspoň jeden zo zavedených používateľov.",
	'validationstatistics-table' => "Dolu sú zobrazené štatistiky pre každý menný priestor ''okrem'' presmerovacích stránok.",
	'validationstatistics-ns' => 'Menný priestor',
	'validationstatistics-total' => 'Stránky',
	'validationstatistics-stable' => 'Skontrolované',
	'validationstatistics-latest' => 'Synchronizovaná',
	'validationstatistics-synced' => 'Synchronizované/skontrolované',
	'validationstatistics-old' => 'Zastaralé',
	'validationstatistics-utable' => 'Dolu je zoznam 5 naj kontrolórov za poslednú hodinu.',
	'validationstatistics-user' => 'Používateľ',
	'validationstatistics-reviews' => 'Kontroly',
);

/** Albanian (Shqip)
 * @author Puntori
 */
$messages['sq'] = array(
	'validationstatistics-total' => 'Faqet',
);

/** Serbian Cyrillic ekavian (Српски (ћирилица))
 * @author Михајло Анђелковић
 * @author Обрадовић Горан
 */
$messages['sr-ec'] = array(
	'validationstatistics-table' => "Статистике за сваки именски простор су приказане испод, ''искључујући'' странице преусмерења.",
	'validationstatistics-ns' => 'Именски простор',
	'validationstatistics-total' => 'Странице',
	'validationstatistics-latest' => 'Синхронизовано',
	'validationstatistics-old' => 'Застарело',
);

/** Serbian Latin ekavian (Srpski (latinica))
 * @author Michaello
 */
$messages['sr-el'] = array(
	'validationstatistics-table' => "Statistike za svaki imenski prostor su prikazane ispod, ''isključujući'' stranice preusmerenja.",
	'validationstatistics-ns' => 'Imenski prostor',
	'validationstatistics-total' => 'Stranice',
	'validationstatistics-latest' => 'Sinhronizovano',
	'validationstatistics-old' => 'Zastarelo',
);

/** Swedish (Svenska)
 * @author Boivie
 * @author M.M.S.
 * @author Rotsee
 * @author Skalman
 */
$messages['sv'] = array(
	'validationstatistics' => 'Valideringsstatistik',
	'validationstatistics-users' => "'''{{SITENAME}}''' har just nu '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|användare|användare}} med [[{{MediaWiki:Validationpage}}|redaktörsrättigheter]] och '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|användare|användare}} med [[{{MediaWiki:Validationpage}}|granskningsrättigheter]].

Redaktörer och granskare är etablerade användare som kan granska sidversioner.",
	'validationstatistics-time' => "''Följande data är cachad och kan vara inaktuell.''

Genomsnittlig väntan för redigeringar av ''oinloggade användare'' för granskning är '''$1'''; medianen är '''$3'''.
$4
Genomsnittlig lag för [[Special:OldReviewedPages|Föråldrade granskade sidor]] är '''$2'''.
Dessa sidor anses ''föråldrade''. Likaså anses sidor ''synkade'' om den [[{{MediaWiki:Validationpage}}|stabila versionen]] också är den nuvarande draft-versionen.
Stabila versioner är sidversioner för sidor som kollats av minst en etablerad användare.",
	'validationstatistics-table' => "Statistik för varje namnrymd visas nedan, ''förutom'' omdirigeringssidor.",
	'validationstatistics-ns' => 'Namnrymd',
	'validationstatistics-total' => 'Sidor',
	'validationstatistics-stable' => 'Granskad',
	'validationstatistics-latest' => 'Synkad',
	'validationstatistics-synced' => 'Synkad/Granskad',
	'validationstatistics-old' => 'Föråldrad',
	'validationstatistics-utable' => 'Nedan listas de fem flitigaste granskarna den senaste timmen.',
	'validationstatistics-user' => 'Användare',
	'validationstatistics-reviews' => 'Granskningar',
);

/** Tamil (தமிழ்)
 * @author Ulmo
 */
$messages['ta'] = array(
	'validationstatistics-ns' => 'பெயர்வெளி',
	'validationstatistics-total' => 'பக்கங்கள்',
);

/** Telugu (తెలుగు)
 * @author Kiranmayee
 * @author Veeven
 */
$messages['te'] = array(
	'validationstatistics' => 'సరిచూత గణాంకాలు',
	'validationstatistics-ns' => 'నేంస్పేసు',
	'validationstatistics-total' => 'పేజీలు',
	'validationstatistics-stable' => 'రివ్యూడ్',
	'validationstatistics-latest' => 'సింకుడు',
	'validationstatistics-synced' => 'సింకుడు/రివ్యూడ్',
	'validationstatistics-old' => 'పాతవి',
	'validationstatistics-user' => 'వాడుకరి',
	'validationstatistics-reviews' => 'సమీక్షలు',
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
	'validationstatistics-time' => "''Ang sumusunod na dato ay nakatago at maaaring wala na sa panahon.''

Ang karaniwang panahon ng paghihintay para sa mga pagbabago ng ''mga tagagamit na hindi lumalagdang papasok'' na susuriin pa ay '''$1'''; ang panggitnaan ay '''$3'''. 
$4
Ang karaniwang panahon ng pagkakaiwan para sa [[Special:OldReviewedPages|mga pahinang may nakabinbing pagsusuri ng mga pagbabago]] ay '''$2'''.
Ang mga pahinang ito itinuturing na ''wala na sa panahon''. Gayun din, ang mga pahina ay itinuturing na ''naisabay na'' kapag ang [[{{MediaWiki:Validationpage}}|matatag na bersyon]] ay siya ring pangkasalukuyang balangkas na bersyon.
Ang matatatag na mga bersyon ay mga rebisyon ng mga pahinang nasuri ng kahit na isang kinikilalang tagagamit.",
	'validationstatistics-table' => "Ipinapakita sa ibaba ang mga estadistika para sa bawat espasyo ng pangalan, ''hindi kasama'' ang mga pahinang tumuturo papunta sa ibang pahina.",
	'validationstatistics-ns' => 'Espasyo ng pangalan',
	'validationstatistics-total' => 'Mga pahina',
	'validationstatistics-stable' => 'Nasuri na',
	'validationstatistics-latest' => 'Napagsabay na',
	'validationstatistics-synced' => 'Pinagsabay-sabay/Nasuri nang muli',
	'validationstatistics-old' => 'Wala na sa panahon (luma)',
);

/** Turkish (Türkçe)
 * @author Joseph
 */
$messages['tr'] = array(
	'validationstatistics' => 'Doğrulama istatistikleri',
	'validationstatistics-users' => "'''{{SITENAME}}''' sitesinde şuanda [[{{MediaWiki:Validationpage}}|Editör]] yetkisine sahip '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|kullanıcı|kullanıcı}} ve [[{{MediaWiki:Validationpage}}|İnceleyici]] yetkisine sahip '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|kullanıcı|kullanıcı}} bulunmaktadır.

Editörler ve İnceleyiciler, sayfalara kontrol revizyonu atayabilen belirli kullanıcılardır.",
	'validationstatistics-time' => "''Aşağıdaki veri en son $5 $6 tarihinde güncellenmiştir.''

Belirli kullanıcılar tarafından kontrol edilen değişiklikler, gözden geçirilmiş sayılırlar.

''Giriş yapmamış kullanıcılar'' tarafından yapılan gözden geçirilecek değişiklikler için ortalama bekleme süresi '''$1'''; orta değer '''$3'''.
$4
[[Special:OldReviewedPages|Gözden geçirilmemiş değişikliğe sahip sayfalar]] için ortalama gecikme '''$2'''.
Bu sayfalar ''eskimiş'' sayılırlar. Aynı şekilde, eğer [[{{MediaWiki:Validationpage}}|kararlı sürüm]] aynı zamanda güncel taslaksa, sayfa ''senkron'' sayılır.
Kararlı sürümler, sayfaların en az bir belirli kullanıcı tarafından kontrol edilmiş revizyonlarıdır.",
	'validationstatistics-table' => "Her bir ad alanı için istatistikler aşağıda gösterilmiştir, yönlendirme sayfaları ''hariç''.",
	'validationstatistics-ns' => 'Ad alanı',
	'validationstatistics-total' => 'Sayfalar',
	'validationstatistics-stable' => 'Gözden geçirilmiş',
	'validationstatistics-latest' => 'Senkronize edildi',
	'validationstatistics-synced' => 'Eşitlenmiş/Gözden geçirilmiş',
	'validationstatistics-old' => 'Eski',
	'validationstatistics-utable' => 'Aşağıdaki, son bir saatteki top 5 inceleyicinin listesidir.',
	'validationstatistics-user' => 'Kullanıcı',
	'validationstatistics-reviews' => 'İncelemeler',
);

/** Ukrainian (Українська)
 * @author Ahonc
 */
$messages['uk'] = array(
	'validationstatistics' => 'Статистика перевірок',
	'validationstatistics-users' => "У проекті {{SITENAME}} зараз '''[[Special:ListUsers/editor|$1]]''' {{plural:$1|користувач має|користувачі мають|користувачів мають}} права [[{{MediaWiki:Validationpage}}|«редактор»]] і '''[[Special:ListUsers/reviewer|$2]]''' {{plural:$2|користувач має|користувачі мають|користувачів мають}} права [[{{MediaWiki:Validationpage}}|«рецензент»]].

«Редактори» і «рецензенти» — користувачі, що мають можливість робити вибіркову перевірку конкретних версій сторінок.",
	'validationstatistics-time' => "''Нижченаведені дані взяті з кешу і можуть бути застарілими.''

Редагування, позначені спеціальними користувачами, вважаються перевіреними.

Середнє очікування редагувань від ''незареєстрованих користувачів'' рівне '''$1'''; медіана рівна '''$3'''.
$4
Середня затримка для [[Special:OldReviewedPages|сторінок з непереглянутими редагуваннями]] рівна '''$2'''.
Ці сторінки вважаються ''застарілими''. Так само, сторінка вважається ''синхронізованою'', якщо її
поточна версія є [[{{MediaWiki:Validationpage}}|стабільною]].
Стабільні версії — версії, перевірені принаймні одним користувачем.",
	'validationstatistics-table' => "Статистика для кожного простору назв показана нижче. ''Перенаправлення'' не враховані.",
	'validationstatistics-ns' => 'Простір назв',
	'validationstatistics-total' => 'Сторінок',
	'validationstatistics-stable' => 'Перевірені',
	'validationstatistics-latest' => 'Синхронізовані',
	'validationstatistics-synced' => 'Повторно перевірені',
	'validationstatistics-old' => 'Застарілі',
	'validationstatistics-utable' => "Нижче наведений список із п'яти найбільш активних редакторів за останню годину.",
	'validationstatistics-user' => 'Користувач',
	'validationstatistics-reviews' => 'Перевірок',
);

/** Vèneto (Vèneto)
 * @author Candalua
 */
$messages['vec'] = array(
	'validationstatistics' => 'Statìsteghe de validassion',
	'validationstatistics-ns' => 'Namespace',
	'validationstatistics-total' => 'Pagine',
	'validationstatistics-latest' => 'Sincronizà',
	'validationstatistics-user' => 'Utente',
);

/** Veps (Vepsan kel')
 * @author Игорь Бродский
 */
$messages['vep'] = array(
	'validationstatistics' => 'Kodvindoiden statistik',
	'validationstatistics-users' => "{{SITENAME}}-projektas nügüd' '''[[Special:ListUsers/editor|$1]]''' {{plural:$1|kävutajal|kävutajil}} 
oma [[{{MediaWiki:Validationpage}}|«redaktoran»]] oiktused, i '''[[Special:ListUsers/reviewer|$2]]''' {{plural:$1|kävutajal|kävutajil}} 
oma [[{{MediaWiki:Validationpage}}|«kodvijan»]] oiktused.",
	'validationstatistics-time' => "''Alemba anttud informacii om ottud kešaspäi i sikš voib olda vanhtunuden.''

Keskvarastuz ''registriruimatomiden kävutajiden'' redakcijoiden täht om '''\$1'''; median om'''\$3'''.
\$4
Keskpidestuz [[Special:OldReviewedPages|lehtpoliden kodvmatomiden redakcijoidenke]] täht om '''\$2'''.
Nened lehtpoled lugedas \"vanhtunuzil\". Ezmäks, lehtpoled lugedas \"sinhroniziruidud\", ku [[{{MediaWiki:Validationpage}}|stabiline versii]] om mugažo joksijan kodvversijan.
Stabiližed versijad oma lehtpolen kaikiš udembad versijad, kudambad oma vahvištoittud, miše ozutada kaikile kävutajile augotižjärgendusen mödhe.",
	'validationstatistics-table' => "Alemba om anttud statistikad kaikuččen nimiavarusen täht. Läbioigendad oma heittud neciš statistikaspäi.
''Vanhtunuzikš'' kuctas lehtpolid, kudambad oma redaktiruidud jäl'ges stabilišt versijad.
Ku stabiline versii om jäl'gmäine, ka se kucuse ''sinhroniziruidud''.

'''Homičend.''' Nece lehtpol' keširuiše. Andmusiden nägu voib olda vanhtunuden.",
	'validationstatistics-ns' => 'Nimiavaruz',
	'validationstatistics-total' => "Lehtpol't",
	'validationstatistics-stable' => 'Kodvdud',
	'validationstatistics-latest' => 'Tantoi kodvdud',
	'validationstatistics-synced' => 'Kodvdud udes',
	'validationstatistics-old' => 'Vanhtunuded',
	'validationstatistics-utable' => "Alemba oma ozutadud 5 päarvostelijad jäl'gmäižes časus",
	'validationstatistics-user' => 'Kävutai',
	'validationstatistics-reviews' => 'Redakcijad',
);

/** Vietnamese (Tiếng Việt)
 * @author Minh Nguyen
 * @author Vinhtantran
 */
$messages['vi'] = array(
	'validationstatistics' => 'Thống kê phê chuẩn',
	'validationstatistics-users' => "Hiện nay, có '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|thành viên|thành viên}} tại '''{{SITENAME}}''' có quyền [[{{MediaWiki:Validationpage}}|Chủ bút]] và ''''''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|thành viên|thành viên}} có quyền [[{{MediaWiki:Validationpage}}|Người duyệt]].

Những Chủ bút và Người duyệt là người dùng từ lâu có khả năng kiểm tra nhanh các thay đổi tại trang.",
	'validationstatistics-time' => "'''Dữ liệu sau được lưu vào bộ đệm vào $5 lúc $6.'''

Sửa đổi được xem là “đã xuyệt” sau khi thành viên không mới tham gia kiểm tra nó.

Thời gian chờ duyệt trung bình cho sửa đổi của ''thành viên không đăng nhập'' là '''$1'''; trung vị là '''$3'''.
$4
Độ trễ trung bình đối với [[Special:OldReviewedPages|trang có sửa đổi chưa duyệt]] là '''$2'''.
Những trang sau được xem là ''lỗi thời''. Tương tự, những trang được xem là ''đã đồng bộ'' nếu
[[{{MediaWiki:Validationpage}}|phiên bản ổn định]] cũng là phiên bản nháp hiện tại.
Bản ổn định của một trang là phiên bản của trang đã được chấp nhận để hiển thị cho các độc giả theo mặc định.",
	'validationstatistics-table' => "Đây có thống kê về các không gian tên, ''trừ'' các trang đổi hướng.",
	'validationstatistics-ns' => 'Không gian tên',
	'validationstatistics-total' => 'Số trang',
	'validationstatistics-stable' => 'Được duyệt',
	'validationstatistics-latest' => 'Đã đồng bộ',
	'validationstatistics-synced' => 'Cập nhật/Duyệt',
	'validationstatistics-old' => 'Lỗi thời',
	'validationstatistics-utable' => 'Đây là danh sách top 5 người duyệt trong giờ qua.',
	'validationstatistics-user' => 'Người dùng',
	'validationstatistics-reviews' => 'Bản duyệt',
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
	'validationstatistics-user' => 'באַניצער',
);

/** Simplified Chinese (‪中文(简体)‬)
 * @author Bencmq
 */
$messages['zh-hans'] = array(
	'validationstatistics' => '审核统计',
);

/** Traditional Chinese (‪中文(繁體)‬)
 * @author Tomchiukc
 */
$messages['zh-hant'] = array(
	'validationstatistics' => '判定統計',
	'validationstatistics-users' => "'''{{SITENAME}}'''現時有'''[[Special:ListUsers/editor|$1]]'''個用戶具有[[{{MediaWiki:Validationpage}}|編輯]]的權限，而'''[[Special:ListUsers/reviewer|$2]]'''個用戶有[[{{MediaWiki:Validationpage}}|審定]]的權限。

編輯及審定皆為已確認的用戶，並可以檢查各頁面的修定。",
);

