<?php
/**
 * Internationalisation file for FlaggedRevs extension, section ValidationStatistics
 *
 * @addtogroup Extensions
 */

$messages = array();

$messages['en'] = array(
	'validationstatistics'        => 'Validation statistics',
	'validationstatistics-users'  => '\'\'\'{{SITENAME}}\'\'\' currently has \'\'\'$1\'\'\' {{PLURAL:$1|user|users}} with [[{{MediaWiki:Validationpage}}|Editor]] rights
and \'\'\'$2\'\'\' {{PLURAL:$2|user|users}} with [[{{MediaWiki:Validationpage}}|Reviewer]] rights.',
	'validationstatistics-table'  => "Statistics for each namespace are shown below, excluding redirects pages.

'''Note:''' the following data is cached for several hours and may not be up to date.",
	'validationstatistics-ns'     => 'Namespace',
	'validationstatistics-total'  => 'Pages',
	'validationstatistics-stable' => 'Reviewed',
	'validationstatistics-latest' => 'Latest reviewed',
	'validationstatistics-synced' => 'Synced/Reviewed',
);

/** Arabic (العربية)
 * @author Meno25
 */
$messages['ar'] = array(
	'validationstatistics'        => 'إحصاءات التحقق',
	'validationstatistics-users'  => "'''{{SITENAME}}''' لديه حاليا '''$1''' {{PLURAL:$1|مستخدم|مستخدم}} بصلاحيات [[{{MediaWiki:Validationpage}}|محرر]]
و '''$2''' {{PLURAL:$2|مستخدم|مستخدم}} بصلاحيات [[{{MediaWiki:Validationpage}}|مراجع]].",
	'validationstatistics-table'  => "الإحصاءات لكل نطاق معروضة بالأسفل، لا يتضمن ذلك صفحات التحويل.

'''ملاحظة:''' البيانات التالية مخزنة لعدة ساعات وربما لا تكون محدثة.",
	'validationstatistics-ns'     => 'النطاق',
	'validationstatistics-total'  => 'الصفحات',
	'validationstatistics-stable' => 'مراجع',
	'validationstatistics-latest' => 'مراجع أخيرا',
	'validationstatistics-synced' => 'تم تحديثه/تمت مراجعته',
);

/** German (Deutsch)

 */
$messages['de'] = array(
	'validationstatistics'        => 'Markierungsstatistik',
	'validationstatistics-users'  => "'''{{SITENAME}}''' hat '''$1''' {{PLURAL:$1|Benutzer|Benutzer}} mit [[{{MediaWiki:Validationpage}}|Prüferrecht]] und '''$2''' {{PLURAL:$2|Benutzer|Benutzer}} mit [[{{MediaWiki:Validationpage}}|Sichterrecht]].",
	'validationstatistics-table'  => "Statistiken für jeden Namensraum, ausgenommen sind Weiterleitungen.

'''Bitte beachten:''' Die folgenden Daten werden jeweils für mehrere Stunden zwischengespeichert und sind daher nicht immer aktuell.",
	'validationstatistics-ns'     => 'Namensraum',
	'validationstatistics-total'  => 'Seiten',
	'validationstatistics-stable' => 'Gesichtet',
	'validationstatistics-latest' => 'Zuletzt gesichtet',
	'validationstatistics-synced' => 'Synced/Gesichtet',
);

/** Italian (Italiano)
 * @author Darth Kule
 */
$messages['it'] = array(
	'validationstatistics'        => 'Statistiche di convalidazione',
	'validationstatistics-users'  => "Al momento, su '''{{SITENAME}}''' {{PLURAL:$1|c'è '''$1''' utente|ci sono '''$1''' utenti}} con i diritti di [[{{MediaWiki:Validationpage}}|Editore]] e '''$2''' {{PLURAL:$2|utente|utenti}} con i diritti di [[{{MediaWiki:Validationpage}}|Revisore]].",
	'validationstatistics-table'  => "Le statistiche per ciascun namaspace sono mostrate di seguito, a esclusione dei redirect.

'''Nota:''' i dati che seguono sono estratti da una copia ''cache'' del database, non aggiornati in tempo reale.",
	'validationstatistics-ns'     => 'Namespace',
	'validationstatistics-total'  => 'Pagine',
	'validationstatistics-stable' => 'Revisionate',
	'validationstatistics-latest' => 'Ultime revisionate',
);

/** Dutch (Nederlands)
 * @author Siebrand
 */
$messages['nl'] = array(
	'validationstatistics'        => 'Eindredactiestatistieken',
	'validationstatistics-users'  => "'''{{SITENAME}}''' heeft op het moment '''$1''' {{PLURAL:$1|gebruiker|gebruikers}} in de rol van [[{{MediaWiki:Validationpage}}|Redacteur]] en '''$2''' {{PLURAL:$2|gebruiker|gebruikers}} met de rol [[{{MediaWiki:Validationpage}}|Eindredacteur]].",
	'validationstatistics-table'  => "Hieronder staan statistieken voor iedere naamruimte, exclusief doorverwijzingen.

'''Let op:''' de onderstaande gegevens komen uit een cache, en kunnen tot enkele uren oud zijn.",
	'validationstatistics-ns'     => 'Naamruimte',
	'validationstatistics-total'  => "Pagina's",
	'validationstatistics-stable' => 'Eindredactie afgerond',
	'validationstatistics-latest' => 'Meest recente eindredacties',
	'validationstatistics-synced' => 'Gesynchroniseerd/Eindredactie',
);

/** Slovak (Slovenčina)
 * @author Helix84
 */
$messages['sk'] = array(
	'validationstatistics'       => 'Štatistiky overenia',
	'validationstatistics-users' => "'''{{SITENAME}}''' má momentálne '''$1''' {{PLURAL:$1|používateľa|používateľov}} s právami [[{{MediaWiki:Validationpage}}|redaktor]] a '''$2''' {{PLURAL:$2|používateľa|používateľov}} s právami [[{{MediaWiki:Validationpage}}|kontrolór]].",
	'validationstatistics-table' => "Dolu sú zobrazené štatistiky pre každý menný priestor okrem presmerovacích stránok.

'''Pozn.:''' nasledujúce údaje pochádzajú z vyrovnávacej pamäte a môžu byť niekoľko hodín staré.",
	'validationstatistics-ns'    => 'Menný priestor',
);

/** Swedish (Svenska)
 * @author M.M.S.
 */
$messages['sv'] = array(
	'validationstatistics'        => 'Valideringsstatistik',
	'validationstatistics-users'  => "'''{{SITENAME}}''' har just nu '''$1''' {{PLURAL:$1|användare|användare}} med [[{{MediaWiki:Validationpage}}|redaktörsrättigheter]] och '''$2''' {{PLURAL:$2|användare|användare}} med [[{{MediaWiki:Validationpage}}|granskningsrättigheter]].",
	'validationstatistics-table'  => "Statistik för varje namnrymd visas nedan, förutom omdirigeringssidor.

'''Notera:''' följande data är cachad för flera timmar och kan vara föråldrad.",
	'validationstatistics-ns'     => 'Namnrymd',
	'validationstatistics-total'  => 'Sidor',
	'validationstatistics-stable' => 'Granskad',
	'validationstatistics-latest' => 'Senast granskad',
	'validationstatistics-synced' => 'Synkad/Granskad',
);

