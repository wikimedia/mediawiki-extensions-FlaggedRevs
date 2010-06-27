<?php
/**
 * Internationalisation file for FlaggedRevs extension, section ValidationStatistics
 *
 * @file
 * @ingroup Extensions
 */

$messages = array();

$messages['en'] = array(
	'validationstatistics'        => 'Page review statistics',
	'validationstatistics-users'  => '\'\'\'{{SITENAME}}\'\'\' currently has \'\'\'[[Special:ListUsers/editor|$1]]\'\'\' {{PLURAL:$1|user|users}} with [[{{MediaWiki:Validationpage}}|Editor]] rights.

Editors are established users that can spot-check revisions to pages.',
    'validationstatistics-lastupdate' => '\'\'The following data was last updated on $1 at $2.\'\'',
	'validationstatistics-pndtime'   => 'Edits that have been checked by established users are considered to be reviewed.

The average delay for [[Special:OldReviewedPages|pages with unreviewed edits pending]] is \'\'\'$1\'\'\'.
These pages are considered \'\'outdated\'\'. Likewise, pages are considered \'\'synced\'\' if there are no edits pending review.',
    'validationstatistics-revtime'   => 'The average wait for edits by \'\'users that have not logged in\'\' to be reviewed is \'\'\'$1\'\'\'; the median is \'\'\'$2\'\'\'. 
$3',
	'validationstatistics-table'  => "Page review statistics for each namespace are shown below, ''excluding'' redirect pages.",
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

/** Aragonese (Aragonés)
 * @author Juanpabl
 */
$messages['an'] = array(
	'validationstatistics-ns' => 'Espacio de nombres',
);

/** Arabic (العربية)
 * @author Meno25
 * @author OsamaK
 */
$messages['ar'] = array(
	'validationstatistics' => 'إحصاءات مراجعة الصفحة',
	'validationstatistics-users' => "'''{{SITENAME}}''' بها حاليا '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|مستخدم|مستخدمون}} بصلاحية [[{{MediaWiki:Validationpage}}|محرر]].

المحررون هم مستخدمون موثوقون يمكنهم التحقق من المراجعات للصفحات.",
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
 * @author Dudi
 * @author Ghaly
 * @author Meno25
 * @author Ramsis II
 */
$messages['arz'] = array(
	'validationstatistics' => 'إحصاءات التحقق',
	'validationstatistics-users' => "'''{{SITENAME}}''' دلوقتى فيه '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|يوزر|يوزر}} بحقوق [[{{MediaWiki:Validationpage}}|محرر]].

المحررين هما يوزرات متعيّنين و يقدرو يشوفو و يشيّكو على مراجعات الصفح.",
	'validationstatistics-table' => "الإحصاءات لكل نطاق معروضه بالأسفل، ''ولا يشمل ذلك'' صفحات التحويل.",
	'validationstatistics-ns' => 'النطاق',
	'validationstatistics-total' => 'الصفحات',
	'validationstatistics-stable' => 'مراجع',
	'validationstatistics-latest' => 'محدث',
	'validationstatistics-synced' => 'تم تحديثه/تمت مراجعته',
	'validationstatistics-old' => 'قديمة',
	'validationstatistics-utable' => 'بالأسفل قائمه بأعلى 5 مراجعين فى الساعه الأخيره.',
	'validationstatistics-user' => 'المستخدم',
	'validationstatistics-reviews' => 'مراجعات',
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
	'validationstatistics' => 'Статыстыка рэцэнзаваньня старонак',
	'validationstatistics-users' => "'''{{SITENAME}}''' цяпер налічвае '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|удзельніка|удзельнікаў|удзельнікаў}} з правамі «[[{{MediaWiki:Validationpage}}|рэдактара]]».

Рэдактары — асобныя удзельнікі, якія могуць правяраць вэрсіі старонак.",
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
 * @author Turin
 */
$messages['bg'] = array(
	'validationstatistics-ns' => 'Именно пространство',
	'validationstatistics-total' => 'Страници',
	'validationstatistics-stable' => 'Рецензирани',
	'validationstatistics-user' => 'Потребител',
);

/** Bengali (বাংলা)
 * @author Bellayet
 */
$messages['bn'] = array(
	'validationstatistics-total' => 'পাতা',
	'validationstatistics-user' => 'ব্যবহারকারী',
);

/** Breton (Brezhoneg)
 * @author Fulup
 * @author Y-M D
 */
$messages['br'] = array(
	'validationstatistics' => 'Stadegoù adlenn ar pajennoù',
	'validationstatistics-users' => "Evit ar poent, war '''{{SITENAME}}''' ez eus '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|implijer gantañ|implijer ganto}} gwirioù [[{{MediaWiki:Validationpage}}|Aozer]]. 

An Aozerien hag an Adlennerien a zo implijerien staliet a c'hell gwiriañ adweladennoù ar pajennoù.",
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
	'validationstatistics' => 'Statistike provjera stranice',
	'validationstatistics-users' => "'''{{SITENAME}}''' trenutno ima '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|korisnika|korisnika}} sa pravima [[{{MediaWiki:Validationpage}}|urednika]].

Urednici su potvrđeni korisnici koji mogu izvršavati provjere revizija stranice.",
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
	'validationstatistics-users' => "'''{{SITENAME}}''' té actualment '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|usuari|usuaris}} amb drets d'[[{{MediaWiki:Validationpage}}|Editor]].

Els Editors són usuaris experimentats que poden validar les revisions de les pàgines.",
	'validationstatistics-time' => "''Les següents dades van ser actualitzades per darrera vegada el dia $5 a les $6.''

Es consideren revisades aquelles edicions que han estat validades per usuaris establerts.

La mitja d'espera de les edicions d'''usuaris no registrats'' per a ser revisades és de '''$1'''; la mitjana és de '''$3'''.
$4
El retard mig per a [[Special:OldReviewedPages|pàgines amb edicions no revisades pendents]] és '''$2'''.
Aquestes pàgines es consideren ''obsoletes''. De la mateixa manera, es consideren com a ''sincronitzades'' quan no hi ha modificacions pendents de revisió.",
	'validationstatistics-ns' => "Nom d'espai",
	'validationstatistics-total' => 'Pàgines',
	'validationstatistics-stable' => "S'ha revisat",
	'validationstatistics-latest' => 'Sincronitzat',
	'validationstatistics-synced' => 'Sincronitzat/Revisat',
);

/** Czech (Česky)
 * @author Kuvaly
 * @author Matěj Grabovský
 */
$messages['cs'] = array(
	'validationstatistics' => 'Statistiky ověřování',
	'validationstatistics-users' => "'''{{SITENAME}}''' má práve teď '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|uživatele|uživatele|uživatelů}} s právy [[{{MediaWiki:Validationpage}}|editora]] a '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$1|uživatele|uživatele|uživatelů}} s právy [[{{MediaWiki:Validationpage}}|posuzovatele]].",
	'validationstatistics-table' => "Níže jsou zobrazeny statistiky pro každý jmenný prostor ''kromě'' přesměrování.",
	'validationstatistics-ns' => 'Jmenný prostor',
	'validationstatistics-total' => 'Stránky',
	'validationstatistics-stable' => 'Prověřeno',
	'validationstatistics-latest' => 'Synchronizováno',
	'validationstatistics-synced' => 'Synchronizováno/prověřeno',
	'validationstatistics-old' => 'Zastaralé',
	'validationstatistics-utable' => 'Níže je seznam 5 největších posuzovatelů za poslední hodinu.',
	'validationstatistics-user' => 'Uživatel',
	'validationstatistics-reviews' => 'Posouzení',
);

/** Church Slavic (Словѣ́ньскъ / ⰔⰎⰑⰂⰡⰐⰠⰔⰍⰟ)
 * @author ОйЛ
 */
$messages['cu'] = array(
	'validationstatistics-total' => 'страни́цѧ',
);

/** German (Deutsch)
 * @author ChrisiPK
 * @author Kghbln
 * @author Melancholie
 * @author Merlissimo
 * @author The Evil IP address
 * @author Umherirrender
 */
$messages['de'] = array(
	'validationstatistics' => 'Markierungsstatistik',
	'validationstatistics-users' => "'''{{SITENAME}}''' hat momentan '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|Benutzer|Benutzer}} mit [[{{MediaWiki:Validationpage}}|Sichterrecht]].

Sichter sind anerkannte Benutzer, die Versionen einer Seite markieren können.",
	'validationstatistics-table' => 'Das sind Statistiken für jeden Namensraum, ausgenommen Weiterleitungen.',
	'validationstatistics-ns' => 'Namensraum',
	'validationstatistics-total' => 'Seiten gesamt',
	'validationstatistics-stable' => 'Mindestens eine Version gesichtet',
	'validationstatistics-latest' => 'Anzahl Seiten, die in der aktuellen Version gesichtet sind',
	'validationstatistics-synced' => 'Prozentsatz an Seiten, die in der aktuellen Version gesichtet sind',
	'validationstatistics-old' => 'Seiten mit ungesichteten Versionen',
	'validationstatistics-utable' => 'Nachfolgend die Liste der fünf Benutzer, die in der letzten Stunde die meisten Markierungen gesetzt haben.',
	'validationstatistics-user' => 'Benutzer',
	'validationstatistics-reviews' => 'Markierungen',
);

/** Zazaki (Zazaki)
 * @author Aspar
 * @author Xoser
 */
$messages['diq'] = array(
	'validationstatistics' => 'Pele istatîstîksê onay biyayisi',
	'validationstatistics-users' => "'''{{SITENAME}}''' de nika '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|karber|karberan}} pê heqê [[{{MediaWiki:Validationpage}}|Editor]]î estê.

Editorî u kontrol kerdoğî karberanê kihanyerê ke eşkenî pelan revize bike.",
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
	'validationstatistics' => 'Statistika pśeglědowanja bokow',
	'validationstatistics-users' => "'''{{SITENAME}}''' ma tuchylu '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|wužywarja|wužywarjowu|wužywarjow|wužywarjow}} z [[{{MediaWiki:Validationpage}}|pšawami wobźěłowarja]].

Wobźěłowarje su etablěrowane wužiwarje, kótarež mógu wersije bokow pśeglědaś.",
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

/** British English (British English)
 * @author Bruce89
 * @author Reedy
 */
$messages['en-gb'] = array(
	'validationstatistics' => 'Page review statistics',
	'validationstatistics-time' => "''The following data was last updated on $5 at $6.''

Edits that have been checked by established users are considered to be reviewed.

The average wait for edits by ''users that have not logged in'' to be reviewed is '''$1'''; the median is '''$3'''.  
$4
The average lag for [[Special:OldReviewedPages|pages with unreviewed edits pending]] is '''$2'''.
These pages are considered ''outdated''. Likewise, pages are considered ''synchronised'' if there are no edits pending review.
The published version of a page is the newest revision that has been approved to show by default to all readers.",
);

/** Esperanto (Esperanto)
 * @author Yekrats
 */
$messages['eo'] = array(
	'validationstatistics' => 'Statistikoj pri paĝkontrolado',
	'validationstatistics-users' => "'''{{SITENAME}}''' nun havas '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|uzanton|uzantojn}} kun rajto [[{{MediaWiki:Validationpage}}|Redaktanto]].

Redaktantoj estas daŭraj uzantoj kiuj povas iufoje kontroli paĝojn.",
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
 * @author Bola
 * @author Crazymadlover
 * @author Dferg
 * @author Imre
 * @author Translationista
 */
$messages['es'] = array(
	'validationstatistics' => 'Estadísticas de revisión de páginas',
	'validationstatistics-users' => "En '''{{SITENAME}}''' actualmente hay '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|usuario|usuarios}} con derechos de [[{{MediaWiki:Validationpage}}|Editor]].
Los editores son usuarios establecidos que pueden verificar las revisiones de las páginas.",
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
 * @author Pikne
 */
$messages['et'] = array(
	'validationstatistics' => 'Ülevaatuse arvandmestik',
	'validationstatistics-users' => "Praegu on '''{{GRAMMAR:inessive|{{SITENAME}}}}''' '''[[Special:ListUsers/editor|$1]]''' [[{{MediaWiki:Validationpage}}|toimetaja]] õigustes {{PLURAL:$1|kasutaja|kasutajat}}.

Toimetajad on kohale määratud kasutajad, kes saavad lehekülgel tehtud muudatused põgusalt üle vaadata.",
	'validationstatistics-table' => "Allpool on toodud arvandmed nimeruumiti, ''välja arvatud'' ümbersuunamisleheküljed.",
	'validationstatistics-ns' => 'Nimeruum',
	'validationstatistics-total' => 'Lehekülgi',
	'validationstatistics-stable' => 'Ülevaadatud',
	'validationstatistics-latest' => 'Ühtiv',
	'validationstatistics-synced' => 'Ühtiv või ülevaadatud',
	'validationstatistics-old' => 'Iganenud',
	'validationstatistics-utable' => 'Allpool on ülevaatajate esiviisik viimase tunni jaoks.',
	'validationstatistics-user' => 'Kasutaja',
	'validationstatistics-reviews' => 'Ülevaatamisi',
);

/** Basque (Euskara)
 * @author An13sa
 * @author Kobazulo
 */
$messages['eu'] = array(
	'validationstatistics' => 'Orrialde berrikuspen estatistikak',
	'validationstatistics-ns' => 'Izen-tartea',
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
 * @author ZeiP
 */
$messages['fi'] = array(
	'validationstatistics' => 'Validointitilastot',
	'validationstatistics-table' => "Alla on tilastot kaikille nimiavaruuksille ''lukuun ottamatta'' ohjaussivuja.",
	'validationstatistics-ns' => 'Nimiavaruus',
	'validationstatistics-total' => 'Sivut',
	'validationstatistics-stable' => 'Arvioitu',
	'validationstatistics-latest' => 'Synkronoitu',
	'validationstatistics-synced' => 'Synkronoitu/arvioitu',
	'validationstatistics-old' => 'Vanhentunut',
	'validationstatistics-utable' => 'Alla on lista viidestä ahkerimmasta arvioijasta edellisen tunnin aikana.',
	'validationstatistics-user' => 'Käyttäjä',
	'validationstatistics-reviews' => 'Arviot',
);

/** French (Français)
 * @author Crochet.david
 * @author Grondin
 * @author IAlex
 * @author McDutchie
 * @author Peter17
 * @author PieRRoMaN
 * @author Verdy p
 * @author Zetud
 */
$messages['fr'] = array(
	'validationstatistics' => 'Statistiques de relecture des pages',
	'validationstatistics-users' => "'''{{SITENAME}}''' dispose actuellement de '''[[Special:ListUsers/editor|$1]]''' utilisateur{{PLURAL:$1||s}} avec les droits de [[{{MediaWiki:Validationpage}}|contributeur]].

Les contributeurs et relecteurs sont des utilisateurs établis qui peuvent vérifier les révisions des pages.",
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
	'validationstatistics-users' => "'''{{SITENAME}}''' at ora '''[[Special:ListUsers/editor|$1]]''' utilisator{{PLURAL:$1||s}} avouéc los drêts de [[{{MediaWiki:Validationpage}}|contributor]].

Los contributors sont des utilisators ètablis que pôvont controlar les vèrsions de les pâges.",
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
	'validationstatistics' => 'Estatísticas de revisión das páxinas',
	'validationstatistics-users' => "Actualmente, '''{{SITENAME}}''' ten '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|usuario|usuarios}} con
dereitos de [[{{MediaWiki:Validationpage}}|editor]].

Os editores son usuarios autoconfirmados que poden comprobar revisións de páxinas.",
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
	'validationstatistics' => 'Statischtik vu dr Sytepriefige',
	'validationstatistics-users' => "'''{{SITENAME}}''' het '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|Benutzer|Benutzer}} mit [[{{MediaWiki:Validationpage}}|Sichterrächt]].

Sichter un Priefer sin Benutzer, wu Syte as prieft chenne markiere.",
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
	'validationstatistics' => 'סטיסטיקות של בדיקת דפים',
	'validationstatistics-users' => "ב'''{{grammar:תחילית|{{SITENAME}}}}''' יש כרגע {{PLURAL:$1|משתמש '''[[Special:ListUsers/editor|אחד]]'''|'''[[Special:ListUsers/editor|$1]]''' משתמשים}} עם הרשאת [[{{MediaWiki:Validationpage}}|עורך]].

עורכים הם משתמשים ותיקים שיכולים לבצע בדיקה מהירה של גרסאות ושל דפים.",
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
 * @author Ex13
 */
$messages['hr'] = array(
	'validationstatistics' => 'Statistike pregledavanja stranice',
	'validationstatistics-users' => "{{SITENAME}}''' trenutačno ima '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|suradnika|suradnika}} s [[{{MediaWiki:Validationpage}}|uredničkim]] pravima.

Urednici su dokazani suradnici koji mogu provjeriti inačice stranice.",
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
	'validationstatistics' => 'Statistika přepruwowanja stronow',
	'validationstatistics-users' => "'''{{SITENAME}}''' ma tuchwilu '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|wužiwarja|wužiwarjow|wužiwarjow|wužiwarjow}} z [[{{MediaWiki:Validationpage}}|wobdźěłowarskimi prawami]].

Wobdźěłowarjo su nazhonići wužiwarjo, kotřiž móžeja wersije stronow kontrolować.",
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
 * @author Hunyadym
 * @author Samat
 */
$messages['hu'] = array(
	'validationstatistics' => 'Ellenőrzési statisztika',
	'validationstatistics-users' => "A(z) '''{{SITENAME}}''' wikinek jelenleg '''[[Special:ListUsers/editor|$1]]''' [[{{MediaWiki:Validationpage}}|járőrjoggal]]  rendelkező szerkesztője van.

A járőrök olyan tapasztalt szerkesztők, akik ellenőrizhetik a lapok változatait.",
	'validationstatistics-table' => "Ezen az oldalon a névterekre bontott statisztika látható, az átirányítások ''nélkül''.",
	'validationstatistics-ns' => 'Névtér',
	'validationstatistics-total' => 'Lapok',
	'validationstatistics-stable' => 'Ellenőrizve',
	'validationstatistics-latest' => 'Szinkronizálva',
	'validationstatistics-synced' => 'Szinkronizálva/ellenőrizve',
	'validationstatistics-old' => 'Elavult',
	'validationstatistics-utable' => 'Az alábbi lista az elmúlt óra öt legtöbbet ellenőrző felhasználóját mutatja.',
	'validationstatistics-user' => 'Felhasználó',
	'validationstatistics-reviews' => 'Ellenőrzések',
);

/** Interlingua (Interlingua)
 * @author McDutchie
 */
$messages['ia'] = array(
	'validationstatistics' => 'Statisticas de revision de paginas',
	'validationstatistics-users' => "'''{{SITENAME}}''' ha al momento '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|usator|usatores}} con privilegios de [[{{MediaWiki:Validationpage}}|Redactor]].

Le Redactores es usatores establite qui pote selectivemente verificar versiones de paginas.",
	'validationstatistics-table' => "Le statisticas de revision de paginas pro cata spatio de nomines es monstrate hic infra, ''excludente'' le paginas de redirection.",
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
 * @author Iwan Novirion
 * @author Kenrick95
 * @author Rex
 */
$messages['id'] = array(
	'validationstatistics' => 'Statistik tinjauan halaman',
	'validationstatistics-users' => "'''{{SITENAME}}''' saat ini memiliki '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|pengguna|pengguna}} dengan hak akses [[{{MediaWiki:Validationpage}}|Penyunting]].

Penyunting adalah para pengguna tetap yang dapat melakukan pemeriksaan perbaikan di setiap halaman.",
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
	'validationstatistics-ns' => 'Nomaro',
	'validationstatistics-total' => 'Pagini',
	'validationstatistics-user' => 'Uzanto',
);

/** Icelandic (Íslenska)
 * @author Spacebirdy
 */
$messages['is'] = array(
	'validationstatistics-ns' => 'Nafnrými',
);

/** Italian (Italiano)
 * @author Beta16
 * @author Darth Kule
 * @author Gianfranco
 * @author Melos
 * @author Pietrodn
 */
$messages['it'] = array(
	'validationstatistics' => 'Statistiche di convalidazione',
	'validationstatistics-users' => "'''{{SITENAME}}''' ha attualmente '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|utente|utenti}} con diritti di [[{{MediaWiki:Validationpage}}|Editore]] e '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|utente|utenti}} con diritti di [[{{MediaWiki:Validationpage}}|Revisore]].",
	'validationstatistics-table' => "Le statistiche per ciascun namespace sono mostrate di seguito, ''a esclusione'' delle pagine di redirect.",
	'validationstatistics-ns' => 'Namespace',
	'validationstatistics-total' => 'Pagine',
	'validationstatistics-stable' => 'Revisionate',
	'validationstatistics-latest' => 'Sincronizzate',
	'validationstatistics-synced' => 'Sincronizzate/Revisionate',
	'validationstatistics-old' => 'Non aggiornate',
	'validationstatistics-utable' => "Di seguito è riportato l'elenco dei primi 5 revisori nell'ultima ora.",
	'validationstatistics-user' => 'Utente',
	'validationstatistics-reviews' => 'Revisioni',
);

/** Japanese (日本語)
 * @author Aotake
 * @author Fryed-peach
 * @author Hosiryuhosi
 * @author 青子守歌
 */
$messages['ja'] = array(
	'validationstatistics' => 'ページのレビュー統計',
	'validationstatistics-users' => "'''{{SITENAME}}''' には現在、[[{{MediaWiki:Validationpage}}|編集者]]権限をもつ利用者が '''[[Special:ListUsers/editor|$1]]'''{{PLURAL:$1|人}}います。

編集者とはページの各版に対して抜き取り検査を行うことを認められた利用者です。",
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

/** Georgian (ქართული)
 * @author BRUTE
 * @author ITshnik
 * @author გიორგიმელა
 */
$messages['ka'] = array(
	'validationstatistics' => 'გვერდების შემოწმების სტატისტიკა',
	'validationstatistics-users' => "პროექტ {{SITENAME}} ამ მომენტისთვის '''[[Special:ListUsers/editor|$1]]''' {{plural:$1|მომხმარებელს აქვს|მომხმარებლებს აქვთ}} [[{{MediaWiki:Validationpage}}|«შემმოწმებლის»]] სტატუსი.


«შემმოწმებლები» — არიან მომხმარებლები, რომლებსაც შეუძლიათ სტატიის კონკრეტული ვერსიების შემოწმება.",
	'validationstatistics-table' => " ქვემოთ ნაჩვენებია სტატისტიკა თითოეული სახელთა სივრცისათვის, ''გარდა'' გადამისამართების გვერდებისა.",
	'validationstatistics-ns' => 'სახელთა სივრცე',
	'validationstatistics-total' => 'გვერდები',
	'validationstatistics-stable' => 'შემოწმებული',
	'validationstatistics-latest' => 'გადამოწმებული',
	'validationstatistics-synced' => 'გადამოწმებულებისა და შემოწმებულების რაოდენობა',
	'validationstatistics-old' => 'მოძველებული',
	'validationstatistics-utable' => 'ქვემოთ მოყვანილია ბოლო საათის განმავლობაში ტოპ 5 გადამმოწმებლის  ჩამონათვალი.',
	'validationstatistics-user' => 'მომხმარებელი',
	'validationstatistics-reviews' => 'გადამოწმებები',
);

/** Khmer (ភាសាខ្មែរ)
 * @author Lovekhmer
 * @author Thearith
 * @author គីមស៊្រុន
 */
$messages['km'] = array(
	'validationstatistics-ns' => 'ប្រភេទ',
	'validationstatistics-total' => 'ទំព័រ',
	'validationstatistics-old' => 'ហួសសម័យ',
);

/** Kannada (ಕನ್ನಡ)
 * @author Nayvik
 */
$messages['kn'] = array(
	'validationstatistics-total' => 'ಪುಟಗಳು',
);

/** Korean (한국어)
 * @author Devunt
 * @author Gapo
 * @author Klutzy
 * @author Kwj2772
 * @author Yknok29
 */
$messages['ko'] = array(
	'validationstatistics' => '페이지의 검토 통계',
	'validationstatistics-users' => "'''{{SITENAME}}'''에는 [[Special:ListUsers/editor|$1]]명의 [[{{MediaWiki:Validationpage}}|편집자]] 권한을 가진 사용자가 있습니다.

편집자가 문서를 검토할 수 있습니다.",
	'validationstatistics-table' => "넘겨주기 문서를 '''제외한''' 문서의 검토 통계가 이름공간별로 보여지고 있습니다.",
	'validationstatistics-ns' => '이름공간',
	'validationstatistics-total' => '문서 수',
	'validationstatistics-stable' => '검토됨',
	'validationstatistics-latest' => '동기화됨',
	'validationstatistics-synced' => '동기화됨/검토됨',
	'validationstatistics-old' => '업데이트 필요함',
	'validationstatistics-utable' => '아래는 최근 1시간 동안의 최고 검토자 5명의 목록입니다',
	'validationstatistics-user' => '사용자',
	'validationstatistics-reviews' => '검토',
);

/** Karachay-Balkar (Къарачай-Малкъар)
 * @author Iltever
 * @author Къарачайлы
 */
$messages['krc'] = array(
	'validationstatistics' => 'Бетлени сынауну статистикасы',
	'validationstatistics-users' => "{{SITENAME}}''' проектде бусагъатда [[{{MediaWiki:Validationpage}}|Редактор]] хакълагъа ие '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|къошулуучу|къошулуучу}} барды.

«Редакторла» —  бетлени белгили версияларын сайлама сынау бардырыргъа эркин къошулуучуладыла.",
);

/** Colognian (Ripoarisch)
 * @author Purodha
 */
$messages['ksh'] = array(
	'validationstatistics' => 'Shtatistike vun de Beschtätijunge för Sigge',
	'validationstatistics-users' => "De '''{{SITENAME}}''' hät em Momang [[Special:ListUsers/editor|{{PLURAL:$1|'''eine''' Metmaacher|'''$1''' Metmaacher|'''keine''' Metmaacher}}]] met däm Rääsch, der [[{{MediaWiki:Validationpage}}|{{int:group-editor-member}}]] ze maache, un [[Special:ListUsers/reviewer|{{PLURAL:$2|'''eine''' Metmaacher|'''$2''' Metmaacher|'''keine''' Metmaacher}}]] met däm Rääsch, der [[{{MediaWiki:Validationpage}}|{{int:group-reviewer-member}}]] ze maache.

{{int:group-editor}}, un {{int:group-reviewer}}, sin doför aanerkannte un extra ußjesöhk Metmaacher, di Versione vun Sigge beshtäteje künne.",
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

/** Cornish (Kernewek)
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
	'validationstatistics' => 'Statistike vun denogekuckte Säiten',
	'validationstatistics-users' => "'''{{SITENAME}}''' huet elo '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|Benotzer|Benotzer}} mat [[{{MediaWiki:Validationpage}}|Editeursrechter]].

Editeure si confirméiert Benotzer déi nogekuckte Versioune vu Säiten derbäisetze kënnen.",
	'validationstatistics-table' => "Statistike fir jiddwer Nummraum sinn hei drënner, Viruleedungssäite sinn ''net berücksichtegt''.",
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
	'validationstatistics' => 'Статистики за оценки',
	'validationstatistics-users' => "'''{{SITENAME}}''' моментално има '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|корисник|корисници}} со права на „[[{{MediaWiki:Validationpage}}|Уредник]]“.

Уредниците се докажани корисници кои можат да прават моментални проверки на ревизии на страници.",
	'validationstatistics-table' => "Подолу се прикажани статистики за секој именски простор, ''без'' страници за пренасочување.",
	'validationstatistics-ns' => 'Именски простор',
	'validationstatistics-total' => 'Страници',
	'validationstatistics-stable' => 'Оценето',
	'validationstatistics-latest' => 'Синхронизирано',
	'validationstatistics-synced' => 'Синхронизирани/Оценети',
	'validationstatistics-old' => 'Застарени',
	'validationstatistics-utable' => 'Еве список на 5 најактивни прегледувачи во последниов час.',
	'validationstatistics-user' => 'Корисник',
	'validationstatistics-reviews' => 'Оцени',
);

/** Malayalam (മലയാളം)
 * @author Praveenp
 * @author Sadik Khalid
 */
$messages['ml'] = array(
	'validationstatistics' => 'താൾ സംശോധനത്തിന്റെ സ്ഥിതിവിവരം',
	'validationstatistics-users' => "{{SITENAME}}''' പദ്ധതിയിൽ '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|ഉപയോക്താവിന്|ഉപയോക്താക്കൾക്ക്}} [[{{MediaWiki:Validationpage}}|എഡിറ്റർ]] പദവിയുണ്ട്.

താളുകളുടെ നാൾവഴികൾ പരിശോധിച്ച് തെറ്റുതിരുത്താൻ കഴിയുന്ന സ്ഥാപിത ഉപയോക്താക്കളാണ് എഡിറ്റർമാർ.",
	'validationstatistics-table' => "ഓരോ നാമമേഖലയിലേയും സ്ഥിതിവിവരക്കണക്കുകൾ താഴെ കൊടുക്കുന്നു, തിരിച്ചുവിടൽ താളുകൾ ''ഒഴിവാക്കുന്നു''.",
	'validationstatistics-ns' => 'നാമമേഖല',
	'validationstatistics-total' => 'താളുകൾ',
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

/** Maltese (Malti)
 * @author Chrisportelli
 */
$messages['mt'] = array(
	'validationstatistics-ns' => 'Spazju tal-isem',
	'validationstatistics-total' => 'Paġni',
	'validationstatistics-user' => 'Utent',
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
	'validationstatistics' => 'Paginacontrolestatistieken',
	'validationstatistics-users' => "'''{{SITENAME}}''' heeft op het moment '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|gebruiker|gebruikers}} in de rol van [[{{MediaWiki:Validationpage}}|Redacteur]].

Redacteuren zijn gebruikers die zich bewezen hebben en versies van pagina's als gecontroleerd mogen markeren.",
	'validationstatistics-table' => "Hieronder staan statistieken voor iedere naamruimte, ''exclusief'' doorverwijzingen.",
	'validationstatistics-ns' => 'Naamruimte',
	'validationstatistics-total' => "Pagina's",
	'validationstatistics-stable' => 'Gecontroleerd',
	'validationstatistics-latest' => 'Gesynchroniseerd',
	'validationstatistics-synced' => 'Gesynchroniseerd/gecontroleerd',
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
	'validationstatistics' => 'Siderevideringsstatistikk',
	'validationstatistics-users' => "'''{{SITENAME}}''' har på nåværende tidspunkt '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|bruker|brukere}} med [[{{MediaWiki:Validationpage}}|skribentrettigheter]].

Skribenter er etablerte brukere som kan punktsjekke siderevisjoner.",
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
	'validationstatistics' => 'Estatisticas de relectura de las paginas',
	'validationstatistics-users' => "'''{{SITENAME}}''' dispausa actualament de '''[[Special:ListUsers/editor|$1]]''' utilizaire{{PLURAL:$1||s}} amb los dreches de [[{{MediaWiki:Validationpage}}|contributor]].

Los contributors e relectors son d'utilizaires establits que pòdon verificar las revisions de las paginas.",
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
	'validationstatistics' => 'Statystyka oznaczania stron',
	'validationstatistics-users' => "W '''{{GRAMMAR:MS.lp|{{SITENAME}}}}''' zarejestrowanych jest obecnie  '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|użytkownik|użytkowników}} z uprawnieniami [[{{MediaWiki:Validationpage}}|redaktora]].

Redaktorzy są doświadczonymi użytkownikami, którzy mogą oznaczać dowolne wersje stron.",
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
 * @author Borichèt
 * @author Dragonòt
 */
$messages['pms'] = array(
	'validationstatistics' => 'Statìstiche ëd validassion ëd la pàgina',
	'validationstatistics-users' => "'''{{SITENAME}}''' al moment a l'ha '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|utent|utent}} con drit d'[[{{MediaWiki:Validationpage}}|Editor]] 

J'Editor a son utent sicur che a peulo controlé le revision a le pàgine.",
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
 * @author Alchimista
 * @author Giro720
 * @author Hamilton Abreu
 * @author Lijealso
 * @author Malafaya
 * @author Waldir
 */
$messages['pt'] = array(
	'validationstatistics' => 'Estatísticas da revisão de páginas',
	'validationstatistics-users' => "A '''{{SITENAME}}''' tem, neste momento, '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|utilizador|utilizadores}} com permissões de [[{{MediaWiki:Validationpage}}|Editor]].

Editores são utilizadores que podem rever as edições de páginas.",
	'validationstatistics-table' => "São apresentadas abaixo estatísticas para cada espaço nominal, '''excluindo''' páginas de redireccionamento.",
	'validationstatistics-ns' => 'Espaço nominal',
	'validationstatistics-total' => 'Páginas',
	'validationstatistics-stable' => 'Revistas',
	'validationstatistics-latest' => 'Sincronizadas',
	'validationstatistics-synced' => 'Sincronizadas/Revistas',
	'validationstatistics-old' => 'Desactualizadas',
	'validationstatistics-utable' => 'Abaixo está a lista dos 5 revisores mais activos na última hora.',
	'validationstatistics-user' => 'Utilizador',
	'validationstatistics-reviews' => 'Revisões',
);

/** Brazilian Portuguese (Português do Brasil)
 * @author Eduardo.mps
 * @author Giro720
 */
$messages['pt-br'] = array(
	'validationstatistics' => 'Estatísticas da revisão de páginas',
	'validationstatistics-users' => "'''{{SITENAME}}''' possui, no momento, '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|utilizador|utilizadores}} com privilégios de [[{{MediaWiki:Validationpage}}|Editor]] .

Editores são utilizadores estabelecidos que podem verificar detalhadamente revisões de páginas.",
	'validationstatistics-table' => "As estatísticas de cada domínio são exibidas a seguir, '''excetuando-se''' as páginas de redirecionamento.",
	'validationstatistics-ns' => 'Espaço nominal',
	'validationstatistics-total' => 'Páginas',
	'validationstatistics-stable' => 'Analisadas',
	'validationstatistics-latest' => 'Sincronizada',
	'validationstatistics-synced' => 'Sincronizadas/Analisadas',
	'validationstatistics-old' => 'Desatualizadas',
	'validationstatistics-utable' => 'Abaixo está uma lista dos 5 maiores analisadores na última hora.',
	'validationstatistics-user' => 'Usuário',
	'validationstatistics-reviews' => 'Análises',
);

/** Romanian (Română)
 * @author Cin
 * @author Firilacroco
 * @author KlaudiuMihaila
 * @author Mihai
 */
$messages['ro'] = array(
	'validationstatistics-users' => "'''{{SITENAME}}''' are în prezent '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|utilizator|utilizatori}} cu drepturi de [[{{MediaWiki:Validationpage}}|editare]]
și '''[[Special:ListUsers/reviewer|$2]]''' {{PLURAL:$2|utilizator|utilizatori}} cu drepturi de [[{{MediaWiki:Validationpage}}|recenzie]].

Editorii și recenzorii sunt utilizatori stabiliți care pot verifica modificările din pagini.",
	'validationstatistics-ns' => 'Spațiu de nume',
	'validationstatistics-total' => 'Pagini',
	'validationstatistics-stable' => 'Revizualizată',
	'validationstatistics-latest' => 'Sincronizată',
	'validationstatistics-synced' => 'Sincronizată/Revizualizată',
	'validationstatistics-old' => 'Învechită',
	'validationstatistics-user' => 'Utilizator',
	'validationstatistics-reviews' => 'Recenzii',
);

/** Tarandíne (Tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'validationstatistics' => "Statisteche d'a pàgene reviste",
	'validationstatistics-users' => "'''{{SITENAME}}''' jndr'à quiste mumende tène '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|utende|utinde}} cu le deritte de [[{{MediaWiki:Validationpage}}|cangiatore]].

Le cangiature sonde utinde stabbelite ca ponne fà verifiche a cambione de le revisiune a le pàggene.",
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
	'validationstatistics' => 'Статистика проверок страниц',
	'validationstatistics-users' => "В проекте {{SITENAME}} на данный момент '''[[Special:ListUsers/editor|$1]]''' {{plural:$1|участник имееет|участника имеют|участников имеют}} полномочия [[{{MediaWiki:Validationpage}}|«редактора»]].

«Редакторы» — это определённые участники, имеющие возможность делать выборочную проверку конкретных версий страниц.",
	'validationstatistics-table' => "Ниже представлена статистика по каждому пространству имён, ''исключая'' страницы перенаправлений.",
	'validationstatistics-ns' => 'Пространство',
	'validationstatistics-total' => 'Страниц',
	'validationstatistics-stable' => 'Проверенные',
	'validationstatistics-latest' => 'Перепроверенные',
	'validationstatistics-synced' => 'Доля перепроверенных в проверенных',
	'validationstatistics-old' => 'Устаревшие',
	'validationstatistics-utable' => 'Ниже приведен список из 5 наиболее активных выверяющих за последний час.',
	'validationstatistics-user' => 'Участник',
	'validationstatistics-reviews' => 'Проверки',
);

/** Rusyn (русиньскый язык)
 * @author Gazeb
 */
$messages['rue'] = array(
	'validationstatistics-total' => 'Сторінкы',
	'validationstatistics-stable' => 'Перевірены',
	'validationstatistics-latest' => 'Сінхронізовано',
	'validationstatistics-synced' => 'Сінхронізовано/перевірено',
	'validationstatistics-old' => 'Застарілы',
	'validationstatistics-utable' => 'Ниже є список 5 найвеце актівных редакторів за послїдню годину.',
	'validationstatistics-user' => 'Хоснователь',
	'validationstatistics-reviews' => 'Посуджіня',
);

/** Yakut (Саха тыла)
 * @author HalanTul
 */
$messages['sah'] = array(
	'validationstatistics' => 'Сирэй тургутуутун статиистиката',
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

/** Sardinian (Sardu)
 * @author Andria
 */
$messages['sc'] = array(
	'validationstatistics-ns' => 'Nùmene-logu',
	'validationstatistics-total' => 'Pàginas',
	'validationstatistics-user' => 'Usuàriu',
);

/** Sinhala (සිංහල)
 * @author බිඟුවා
 */
$messages['si'] = array(
	'validationstatistics-ns' => 'නාම අවකාශය',
	'validationstatistics-total' => 'පිටු',
);

/** Slovak (Slovenčina)
 * @author Helix84
 */
$messages['sk'] = array(
	'validationstatistics' => 'Štatistiky overenia',
	'validationstatistics-users' => "'''{{SITENAME}}''' má momentálne '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|používateľa|používateľov}} s právami [[{{MediaWiki:Validationpage}}|redaktor]] a '''[[Special:ListUsers/reviewer|$2]]'' {{PLURAL:$2|používateľa|používateľov}} s právami [[{{MediaWiki:Validationpage}}|kontrolór]].",
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

/** Slovenian (Slovenščina)
 * @author Dbc334
 */
$messages['sl'] = array(
	'validationstatistics' => 'Statistika pregledov strani',
	'validationstatistics-ns' => 'Imenski prostor',
	'validationstatistics-total' => 'Strani',
	'validationstatistics-stable' => 'Pregledano',
	'validationstatistics-latest' => 'Usklajeno',
	'validationstatistics-synced' => 'Usklajeno/Pregledano',
	'validationstatistics-old' => 'Zastarelo',
	'validationstatistics-utable' => 'Spodaj se nahaja seznam 5 najbolj aktivnih pregledovalcev v zadnji uri.',
	'validationstatistics-user' => 'Uporabnik',
	'validationstatistics-reviews' => 'Pregledi',
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
 * @author Dafer45
 * @author M.M.S.
 * @author Rotsee
 * @author Skalman
 */
$messages['sv'] = array(
	'validationstatistics' => 'Statistik över sidgranskning',
	'validationstatistics-users' => "'''{{SITENAME}}''' har just nu '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|användare|användare}} med [[{{MediaWiki:Validationpage}}|skribenträttigheter]].

Skribenter är etablerade användare som kan granska sidversioner.",
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
 * @author Kanags
 * @author Ulmo
 */
$messages['ta'] = array(
	'validationstatistics-ns' => 'பெயர்வெளி',
	'validationstatistics-total' => 'பக்கங்கள்',
	'validationstatistics-stable' => 'மீள்பார்வையிடப்பட்டது',
	'validationstatistics-old' => 'காலாவதியானது',
	'validationstatistics-user' => 'பயனர்',
);

/** Telugu (తెలుగు)
 * @author Kiranmayee
 * @author Veeven
 */
$messages['te'] = array(
	'validationstatistics' => 'పేజీ సమీక్షల గణాంకాలు',
	'validationstatistics-users' => "'''{{SITENAME}}'''లో ప్రస్తుతం '''[[Special:ListUsers/editor|$1]]'''{{PLURAL:$1| వాడుకరి|గురు వాడుకరులు}} [[{{MediaWiki:Validationpage}}|సంపాదకుల]] హక్కులతోనూ మరియు '''[[Special:ListUsers/reviewer|$2]]'''{{PLURAL:$2| వాడుకరి|గురు వాడుకరులు}}  [[{{MediaWiki:Validationpage}}|సమీక్షకుల]] హక్కులతోనూ ఉన్నారు.

సంపాదకులు మరియు సమీక్షకులు అంటే పేజీలకు కూర్పులను ఎప్పటికప్పుడు సరిచూడగలిగిన నిర్ధారిత వాడుకరులు.",
	'validationstatistics-table' => "ప్రతీ పేరుబరి యొక్క గణాంకాలు క్రింద చూపించాం, దారిమార్పు పేజీలని ''మినహాయించి''.",
	'validationstatistics-ns' => 'నేంస్పేసు',
	'validationstatistics-total' => 'పేజీలు',
	'validationstatistics-stable' => 'రివ్యూడ్',
	'validationstatistics-latest' => 'సింకుడు',
	'validationstatistics-synced' => 'సింకుడు/రివ్యూడ్',
	'validationstatistics-old' => 'పాతవి',
	'validationstatistics-utable' => 'ఈ క్రిందిది గడచిన గంటలో 5గురు క్రియాశీల సమీక్షకుల యొక్క జాబితా.',
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

/** Turkmen (Türkmençe)
 * @author Hanberke
 */
$messages['tk'] = array(
	'validationstatistics' => 'Barlama statistikalary',
	'validationstatistics-users' => "'''{{SITENAME}}''' saýtynda häzirki wagtda [[{{MediaWiki:Validationpage}}|Redaktor]] hukugyna eýe '''[[Special:ListUsers/editor|$1]]''' sany {{PLURAL:$1|ulanyjy|ulanyjy}} hem-de [[{{MediaWiki:Validationpage}}|Gözden geçiriji]] hukugyna eýe '''[[Special:ListUsers/reviewer|$2]]''' sany {{PLURAL:$2|ulanyjy|ulanyjy}} bardyr.

Redaktorlar we Gözden Geçirijiler sahypalara barlag wersiýasyny belläp bilýän kesgitli ulanyjylardyr.",
	'validationstatistics-table' => "Her bir at giňişligi üçin statistikalar aşakda görkezilýär, gönükdirme sahypalary ''degişli däl''.",
	'validationstatistics-ns' => 'At giňişligi',
	'validationstatistics-total' => 'Sahypalar',
	'validationstatistics-stable' => 'Gözden geçirilen',
	'validationstatistics-latest' => 'Sinhronizirlenen',
	'validationstatistics-synced' => 'Sinhronizirlenen/Gözden geçirilen',
	'validationstatistics-old' => 'Möwriti geçen',
	'validationstatistics-utable' => 'Aşakdaky sanaw soňky bir sagadyň dowamyndaky iň işjeň 5 gözden geçirijiniň sanawydyr.',
	'validationstatistics-user' => 'Ulanyjy',
	'validationstatistics-reviews' => 'Barlaglar',
);

/** Tagalog (Tagalog)
 * @author AnakngAraw
 */
$messages['tl'] = array(
	'validationstatistics' => 'Estadistika ng pagsusuri ng pahina',
	'validationstatistics-users' => "Ang '''{{SITENAME}}''' ay  pangkasalukuyang may '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|tagagamit|mga tagagamit}} na may karapatan bilang [[{{MediaWiki:Validationpage}}|Patnugot]] .

Ang mga patnugot ay mga matatagal nang mga tagagamit na makakasipat ng mga pagbabago sa mga pahina.",
	'validationstatistics-table' => "Ipinapakita sa ibaba ang mga estadistika para sa bawat espasyo ng pangalan, ''hindi kasama'' ang mga pahinang tumuturo papunta sa ibang pahina.",
	'validationstatistics-ns' => 'Espasyo ng pangalan',
	'validationstatistics-total' => 'Mga pahina',
	'validationstatistics-stable' => 'Nasuri na',
	'validationstatistics-latest' => 'Napagsabay na',
	'validationstatistics-synced' => 'Pinagsabay-sabay/Nasuri nang muli',
	'validationstatistics-old' => 'Wala na sa panahon (luma)',
	'validationstatistics-utable' => 'Nasa ibaba ang talaan ng limang pinakamataas na manunuri sa loob ng huling oras.',
	'validationstatistics-user' => 'Tagagamit',
	'validationstatistics-reviews' => 'Mga pagsusuri',
);

/** Turkish (Türkçe)
 * @author Joseph
 * @author Manco Capac
 */
$messages['tr'] = array(
	'validationstatistics' => 'Sayfa gözden geçirme istatistikleri',
	'validationstatistics-users' => "'''{{SITENAME}}''' sitesinde şuanda [[{{MediaWiki:Validationpage}}|Editör]] yetkisine sahip '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|kullanıcı|kullanıcı}} bulunmaktadır.

Editörler, sayfalara kontrol revizyonu atayabilen belirli kullanıcılardır.",
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
 * @author Prima klasy4na
 */
$messages['uk'] = array(
	'validationstatistics' => 'Статистика рецензувань сторінок',
	'validationstatistics-users' => "У проекті '''{{SITENAME}}''' зараз '''[[Special:ListUsers/editor|$1]]''' {{plural:$1|користувач має|користувачі мають|користувачів мають}} права [[{{MediaWiki:Validationpage}}|«редактор»]].

«Редактори» — визначені користувачі, що мають можливість робити вибіркову перевірку конкретних версій сторінок.",
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
	'validationstatistics-users' => "'''{{SITENAME}}''' el gà atualmente '''[[Special:ListUsers/editor|$1]]'''  {{PLURAL:$1|utente|utenti}} con diriti de [[{{MediaWiki:Validationpage}}|revisor]].

I revisori i xe utenti che pode verificar le revision de le pagine.",
	'validationstatistics-table' => "Qua soto se cata le statìsteghe par ogni namespace, ''escluse'' le pagine de redirect.",
	'validationstatistics-ns' => 'Namespace',
	'validationstatistics-total' => 'Pagine',
	'validationstatistics-stable' => 'Ricontrolà',
	'validationstatistics-latest' => 'Sincronizà',
	'validationstatistics-synced' => 'Sincronizà/Ricontrolà',
	'validationstatistics-old' => 'Mia ajornà',
	'validationstatistics-user' => 'Utente',
	'validationstatistics-reviews' => 'Revisioni',
);

/** Veps (Vepsan kel')
 * @author Игорь Бродский
 */
$messages['vep'] = array(
	'validationstatistics' => 'Kodvindoiden statistik',
	'validationstatistics-users' => "{{SITENAME}}-projektas nügüd' '''[[Special:ListUsers/editor|$1]]''' {{plural:$1|kävutajal|kävutajil}} 
oma [[{{MediaWiki:Validationpage}}|«redaktoran»]] oiktused.

Redaktorad oma mugomad kävutajad, kudambil om oiktuz kodvda valitud lehtseiden konkretižed versijad.",
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
	'validationstatistics' => 'Thống kê duyệt trang',
	'validationstatistics-users' => "Hiện nay, có '''[[Special:ListUsers/editor|$1]]''' {{PLURAL:$1|thành viên|thành viên}} tại '''{{SITENAME}}''' có quyền [[{{MediaWiki:Validationpage}}|Biên tập viên]].

Biên tập viên là người dùng có kinh nghiệm có khả năng kiểm tra nhanh các thay đổi tại trang.",
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
 * @author Gaoxuewei
 */
$messages['zh-hans'] = array(
	'validationstatistics' => '审核统计',
	'validationstatistics-users' => "'''{{SITENAME}}'''现时有'''[[Special:ListUsers/editor|$1]]'''{{PLURAL:$1|个|个}}用户具有[[{{MediaWiki:Validationpage}}|编辑]]的权限。

编辑及审定皆为已确认的用户，并可以检查各页面的修定。",
	'validationstatistics-table' => "各名称空间的统计信息显示如下，''不包含''转向页。",
	'validationstatistics-ns' => '名字空间',
	'validationstatistics-total' => '页',
	'validationstatistics-stable' => '已复审',
	'validationstatistics-latest' => '已同步',
	'validationstatistics-synced' => '已同步/已复审',
	'validationstatistics-old' => '已过期',
	'validationstatistics-utable' => '如下列表是过去一小时内前5名审核者。',
	'validationstatistics-user' => '用户',
	'validationstatistics-reviews' => '审核者',
);

/** Traditional Chinese (‪中文(繁體)‬)
 * @author Gaoxuewei
 * @author Horacewai2
 * @author Mark85296341
 * @author Tomchiukc
 */
$messages['zh-hant'] = array(
	'validationstatistics' => '審核統計',
	'validationstatistics-users' => "'''{{SITENAME}}'''現時有'''[[Special:ListUsers/editor|$1]]'''{{PLURAL:$1|個|個}}用戶具有[[{{MediaWiki:Validationpage}}|編輯]]的權限。

編輯及審定皆為已確認的用戶，並可以檢查各頁面的修定。",
	'validationstatistics-table' => "各名稱空間的統計資訊顯示如下，''不包含''轉向頁。",
	'validationstatistics-ns' => '名稱空間',
	'validationstatistics-total' => '頁面',
	'validationstatistics-stable' => '已復審',
	'validationstatistics-latest' => '已同步',
	'validationstatistics-synced' => '已同步/已復審',
	'validationstatistics-old' => '已過期',
	'validationstatistics-utable' => '如下列表是過去一小時內前5名審核者。',
	'validationstatistics-user' => '用戶',
	'validationstatistics-reviews' => '審核者',
);

