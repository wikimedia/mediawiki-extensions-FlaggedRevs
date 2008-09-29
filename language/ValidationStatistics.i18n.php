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

/** Arabic (العربية)
 * @author Meno25
 */
$messages['ar'] = array(
	'validationstatistics' => 'إحصاءات التحقق',
	'validationstatistics-users' => "'''{{SITENAME}}''' لديه حاليا '''$1''' {{PLURAL:$1|مستخدم|مستخدم}} بصلاحيات [[{{MediaWiki:Validationpage}}|محرر]]
و '''$2''' {{PLURAL:$2|مستخدم|مستخدم}} بصلاحيات [[{{MediaWiki:Validationpage}}|مراجع]].",
	'validationstatistics-table' => "الإحصاءات لكل نطاق معروضة بالأسفل، لا يتضمن ذلك صفحات التحويل.

'''ملاحظة:''' البيانات التالية مخزنة لعدة ساعات وربما لا تكون محدثة.",
	'validationstatistics-ns' => 'النطاق',
	'validationstatistics-total' => 'الصفحات',
	'validationstatistics-stable' => 'مراجع',
	'validationstatistics-latest' => 'مراجع أخيرا',
	'validationstatistics-synced' => 'تم تحديثه/تمت مراجعته',
	'validationstatistics-old' => 'قديمة',
);

/** Egyptian Spoken Arabic (مصرى)
 * @author Meno25
 */
$messages['arz'] = array(
	'validationstatistics' => 'إحصاءات التحقق',
	'validationstatistics-users' => "'''{{SITENAME}}''' لديه حاليا '''$1''' {{PLURAL:$1|مستخدم|مستخدم}} بصلاحيات [[{{MediaWiki:Validationpage}}|محرر]]
و '''$2''' {{PLURAL:$2|مستخدم|مستخدم}} بصلاحيات [[{{MediaWiki:Validationpage}}|مراجع]].",
	'validationstatistics-table' => "الإحصاءات لكل نطاق معروضة بالأسفل، لا يتضمن ذلك صفحات التحويل.

'''ملاحظة:''' البيانات التالية مخزنة لعدة ساعات وربما لا تكون محدثة.",
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

/** Bulgarian (Български)
 * @author DCLXVI
 */
$messages['bg'] = array(
	'validationstatistics-ns' => 'Именно пространство',
	'validationstatistics-total' => 'Страници',
);

/** German (Deutsch) */
$messages['de'] = array(
	'validationstatistics' => 'Markierungsstatistik',
	'validationstatistics-users' => "{{SITENAME}} hat '''$1''' {{PLURAL:$1|Benutzer|Benutzer}} mit [[{{MediaWiki:Validationpage}}|Sichterrecht]] und '''$2''' {{PLURAL:$2|Benutzer|Benutzer}} mit [[{{MediaWiki:Validationpage}}|Prüferrecht]].",
	'validationstatistics-table' => "Statistiken für jeden Namensraum, ausgenommen sind Weiterleitungen.

'''Bitte beachten:''' Die folgenden Daten werden jeweils für mehrere Stunden zwischengespeichert und sind daher nicht immer aktuell.",
	'validationstatistics-ns' => 'Namensraum',
	'validationstatistics-total' => 'Seiten gesamt',
	'validationstatistics-stable' => 'Mindestens eine Version gesichtet',
	'validationstatistics-latest' => 'Anzahl Seiten, die in der aktuellen Version gesichtet sind',
	'validationstatistics-synced' => 'Prozentsatz Seiten, die in der aktuellen Version gesichtet sind',
	'validationstatistics-old' => 'Seiten mit ungesichteten Versionen',
	'validationstatistics-nbr' => '$1 %',
);

/** Esperanto (Esperanto)
 * @author Yekrats
 */
$messages['eo'] = array(
	'validationstatistics' => 'Validigadaj statistikoj',
	'validationstatistics-users' => "'''{{SITENAME}}''' nun havas '''$1''' {{PLURAL:$1|uzanton|uzantojn}} kun
[[{{MediaWiki:Validationpage}}|Redaktanto]]-rajtoj
kaj '''$2''' {{PLURAL:$2|uzanton|uzantojn}} kun [[{{MediaWiki:Validationpage}}|Kontrolanto]]-rajtoj.",
	'validationstatistics-table' => "Statistikoj por ĉiu nomspaco estas jene montritaj, krom alidirektiloj.

'''Notu:''' la jenaj datenoj estas en kaŝmemoro dum multaj horoj kaj eble ne estas ĝisdataj.",
	'validationstatistics-ns' => 'Nomspaco',
	'validationstatistics-total' => 'Paĝoj',
	'validationstatistics-stable' => 'Kontrolitaj',
	'validationstatistics-latest' => 'Laste kontrolita',
	'validationstatistics-synced' => 'Ĝisdatigita/Kontrolita',
	'validationstatistics-old' => 'Malfreŝa',
);

/** Persian (فارسی)
 * @author Huji
 */
$messages['fa'] = array(
	'validationstatistics' => 'آمار معتبرسازی',
	'validationstatistics-users' => "'''{{SITENAME}}''' در حال حاضر '''$1''' {{PLURAL:$1|کاربر|کاربر}} با اختیارات [[{{MediaWiki:Validationpage}}|ویرایشگر]] و '''$2''' {{PLURAL:$2|کاربر|کاربر}} با اختیارات[[{{MediaWiki:Validationpage}}|مرورگر]] دارد.",
	'validationstatistics-table' => "'''نکته:''' داده‌هایی که در ادامه می‌آید برای چندین ساعت در میان‌گیر ذخیره شده‌اند و ممکن است به روز نباشند.",
	'validationstatistics-ns' => 'فضای نام',
	'validationstatistics-total' => 'صفحه‌ها',
	'validationstatistics-stable' => 'بازبینی شده',
	'validationstatistics-latest' => 'آخرین بازبینی',
	'validationstatistics-synced' => 'به روز شده/بازبینی شده',
	'validationstatistics-old' => 'تاریخ گذشته',
);

/** French (Français)
 * @author Grondin
 * @author Zetud
 */
$messages['fr'] = array(
	'validationstatistics' => 'Statistiques de validation',
	'validationstatistics-users' => "'''{{SITENAME}}''' dispose actuellement de '''$1''' {{PLURAL:$1|utilisateur|utilisateurs}} avec les droits d’[[{{MediaWiki:Validationpage}}|éditeur]] et de '''$2''' {{PLURAL:$2|utilisateur|utilisateurs}} avec les droits de [[{{MediaWiki:Validationpage}}|relecteur]].",
	'validationstatistics-table' => "Les statistiques pour chaque espace de nom sont affichées ci-dessous, à l’exclusion des pages de redirection.

'''Note :''' les données suivantes sont cachées pendant plusieurs heures et ne peuvent pas être mises à jour.",
	'validationstatistics-ns' => 'Nom de l’espace',
	'validationstatistics-total' => 'Pages',
	'validationstatistics-stable' => 'Relu',
	'validationstatistics-latest' => 'Relu en tout dernier lieu',
	'validationstatistics-synced' => 'Synchronisé/Relu',
	'validationstatistics-old' => 'Désuet',
);

/** Galician (Galego)
 * @author Toliño
 */
$messages['gl'] = array(
	'validationstatistics' => 'Estatísticas de validación',
	'validationstatistics-users' => "Actualmente, '''{{SITENAME}}''' ten '''$1''' {{PLURAL:$1|usuario|usuarios}} con dereitos de [[{{MediaWiki:Validationpage}}|editor]] e '''$2''' {{PLURAL:$2|usuario|usuarios}} con dereitos de [[{{MediaWiki:Validationpage}}|revisor]].",
	'validationstatistics-table' => "As estatísticas para cada espazo de nomes son amosadas embaixo, excluíndo as páxinas de redirección.

'''Nota:''' os seguintes datos están na memoria caché durante varias horas e poden non estar actualizados.",
	'validationstatistics-ns' => 'Espazo de nomes',
	'validationstatistics-total' => 'Páxinas',
	'validationstatistics-stable' => 'Revisado',
	'validationstatistics-latest' => 'Última revisión',
	'validationstatistics-synced' => 'Sincronizado/Revisado',
	'validationstatistics-old' => 'Anticuado',
);

/** Ancient Greek (Ἀρχαία ἑλληνικὴ)
 * @author Crazymadlover
 */
$messages['grc'] = array(
	'validationstatistics-total' => 'Δέλτοι',
);

/** Hebrew (עברית)
 * @author Agbad
 */
$messages['he'] = array(
	'validationstatistics-ns' => 'מרחב שם',
	'validationstatistics-total' => 'דפים',
);

/** Croatian (Hrvatski)
 * @author Dalibor Bosits
 */
$messages['hr'] = array(
	'validationstatistics-ns' => 'Imenski prostor',
);

/** Hungarian (Magyar)
 * @author Dani
 * @author Samat
 */
$messages['hu'] = array(
	'validationstatistics' => 'Ellenőrzési statisztikák',
	'validationstatistics-users' => "A(z) '''{{SITENAME}}''' wikinek jelenleg '''{{PLURAL:$1|egy|$1}}''' [[{{MediaWiki:Validationpage}}|járőrjoggal]], valamint '''{{PLURAL:$2|egy|$2}}''' [[{{MediaWiki:Validationpage}}|lektorjoggal]] rendelkező szerkesztője van.",
	'validationstatistics-table' => "Statisztika valamennyi névtérre, az átirányítások kivételével

'''Megjegyzés:''' ezek az adatok csak néhány óránként frissülnek.",
	'validationstatistics-ns' => 'Névtér',
	'validationstatistics-total' => 'Oldalak',
	'validationstatistics-stable' => 'Ellenőrzött',
	'validationstatistics-latest' => 'Legutóbb ellenőrzött',
	'validationstatistics-synced' => 'Szinkronizálva/ellenőrizve',
	'validationstatistics-old' => 'Elavult',
);

/** Italian (Italiano)
 * @author Darth Kule
 */
$messages['it'] = array(
	'validationstatistics' => 'Statistiche di convalidazione',
	'validationstatistics-users' => "Al momento, su '''{{SITENAME}}''' {{PLURAL:$1|c'è '''$1''' utente|ci sono '''$1''' utenti}} con i diritti di [[{{MediaWiki:Validationpage}}|Editore]] e '''$2''' {{PLURAL:$2|utente|utenti}} con i diritti di [[{{MediaWiki:Validationpage}}|Revisore]].",
	'validationstatistics-table' => "Le statistiche per ciascun namaspace sono mostrate di seguito, a esclusione dei redirect.

'''Nota:''' i dati che seguono sono estratti da una copia ''cache'' del database, non aggiornati in tempo reale.",
	'validationstatistics-ns' => 'Namespace',
	'validationstatistics-total' => 'Pagine',
	'validationstatistics-stable' => 'Revisionate',
	'validationstatistics-latest' => 'Ultime revisionate',
	'validationstatistics-old' => 'Non aggiornate',
);

/** Khmer (ភាសាខ្មែរ)
 * @author Lovekhmer
 */
$messages['km'] = array(
	'validationstatistics-ns' => 'លំហឈ្មោះ',
	'validationstatistics-total' => 'ទំព័រ',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 */
$messages['lb'] = array(
	'validationstatistics' => 'Statistike vun de Validaiounen',
	'validationstatistics-users' => "''{{SITENAME}}''' huet elo '''$1''' {{PLURAL:$1|Benotzer|Benotzer}} mat [[{{MediaWiki:Validationpage}}|Editeursrechter]]
an '''$2''' {{PLURAL:$2|Benotzer|Benotzer}} mat [[{{MediaWiki:Validationpage}}|Validatiounsrechter]].",
	'validationstatistics-ns' => 'Nummraum',
	'validationstatistics-total' => 'Säiten',
	'validationstatistics-stable' => 'Validéiert',
);

/** Malay (Bahasa Melayu)
 * @author Aviator
 */
$messages['ms'] = array(
	'validationstatistics' => 'Statistik pengesahan',
	'validationstatistics-users' => "'''{{SITENAME}}''' kini mempunyai {{PLURAL:$1|seorang|'''$1''' orang}} pengguna dengan hak [[{{MediaWiki:Validationpage}}|Penyunting]] dan {{PLURAL:$2|seorang|'''$2''' orang}} pengguna dengan hak [[{{MediaWiki:Validationpage}}|Pemeriksa]].",
	'validationstatistics-table' => "Berikut ialah statistik bagi setiap ruang nama, tidak termasuk laman lencongan.

'''Catatan:''' data berikut diambil daripada simpanan sementara ('''cache''') dan kemungkinan besar bukan yang terkini.",
	'validationstatistics-ns' => 'Ruang nama',
	'validationstatistics-total' => 'Laman',
	'validationstatistics-stable' => 'Diperiksa',
	'validationstatistics-latest' => 'Pemeriksaan terakhir',
);

/** Dutch (Nederlands)
 * @author Siebrand
 */
$messages['nl'] = array(
	'validationstatistics' => 'Eindredactiestatistieken',
	'validationstatistics-users' => "'''{{SITENAME}}''' heeft op het moment '''$1''' {{PLURAL:$1|gebruiker|gebruikers}} in de rol van [[{{MediaWiki:Validationpage}}|Redacteur]] en '''$2''' {{PLURAL:$2|gebruiker|gebruikers}} met de rol [[{{MediaWiki:Validationpage}}|Eindredacteur]].",
	'validationstatistics-table' => "Hieronder staan statistieken voor iedere naamruimte, exclusief doorverwijzingen.

'''Let op:''' de onderstaande gegevens komen uit een cache, en kunnen tot enkele uren oud zijn.",
	'validationstatistics-ns' => 'Naamruimte',
	'validationstatistics-total' => "Pagina's",
	'validationstatistics-stable' => 'Eindredactie afgerond',
	'validationstatistics-latest' => 'Meest recente eindredacties',
	'validationstatistics-synced' => 'Gesynchroniseerd/Eindredactie',
	'validationstatistics-old' => 'Verouderd',
);

/** Norwegian (bokmål)‬ (‪Norsk (bokmål)‬)
 * @author Jon Harald Søby
 */
$messages['no'] = array(
	'validationstatistics' => 'Valideringsstatistikk',
	'validationstatistics-users' => "'''{{SITENAME}}''' har '''$1''' {{PLURAL:$1|bruker|brukere}} med [[{{MediaWiki:Validationpage}}|skribentrettigheter]] og '''$2''' {{PLURAL:$2|bruker|brukere}} med [[{{MediaWiki:Validationpage}}|anmelderrettigheter]].",
	'validationstatistics-table' => "Statistikk for hvert navnerom vises nedenfor, utenom omdirigeringssider.

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
	'validationstatistics-users' => "'''{{SITENAME}}''' dispausa actualament de '''$1''' {{PLURAL:$1|utilizaire|utilizaires}} amb los dreches d’[[{{MediaWiki:Validationpage}}|editor]] e de '''$2''' {{PLURAL:$2|utilizaire|utilizaires}} amb los dreches de [[{{MediaWiki:Validationpage}}|relector]].",
	'validationstatistics-table' => "Las estatisticas per cada espaci de nom son afichadas çaijós, a l’exclusion de las paginas de redireccion.

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
 * @author Wpedzich
 */
$messages['pl'] = array(
	'validationstatistics' => 'Statystyki oznaczania',
	'validationstatistics-users' => "W serwisie '''{{SITENAME}}''' aktualnie zarejestrowanych jest '''$1''' {{PLURAL:$1|użytkownik|użytkowników}} z uprawnieniami [[{{MediaWiki:Validationpage}}|redaktora]] oraz  '''$2''' {{PLURAL:$2|użytkownik|użytkowników}} z uprawnieniami [[{{MediaWiki:Validationpage}}|recenzenta]].",
	'validationstatistics-table' => "Poniżej znajdują się statystyki dla każdej przestrzeni nazw, z wyłączeniem przekierowań.

'''Uwaga:''' poniższe dane są kopią z pamięci podręcznej sprzed nawet kilku godzin, mogą więc być nieaktualne.",
	'validationstatistics-ns' => 'Przestrzeń nazw',
	'validationstatistics-total' => 'Stron',
	'validationstatistics-stable' => 'Przejrzanych',
	'validationstatistics-latest' => 'Z ostatnią edycją oznaczoną jako przejrzana',
	'validationstatistics-synced' => 'Zsynchronizowana/przejrzana',
	'validationstatistics-old' => 'Wymagające ponownego oznaczenia jako przejrzane',
);

/** Portuguese (Português)
 * @author Malafaya
 */
$messages['pt'] = array(
	'validationstatistics-ns' => 'Espaço nominal',
	'validationstatistics-total' => 'Páginas',
	'validationstatistics-old' => 'Desactualizado',
);

/** Romanian (Română)
 * @author KlaudiuMihaila
 */
$messages['ro'] = array(
	'validationstatistics-ns' => 'Spaţiu de nume',
);

/** Russian (Русский)
 * @author AlexSm
 */
$messages['ru'] = array(
	'validationstatistics-ns' => 'Пространство',
	'validationstatistics-total' => 'Страниц',
);

/** Slovak (Slovenčina)
 * @author Helix84
 */
$messages['sk'] = array(
	'validationstatistics' => 'Štatistiky overenia',
	'validationstatistics-users' => "'''{{SITENAME}}''' má momentálne '''$1''' {{PLURAL:$1|používateľa|používateľov}} s právami [[{{MediaWiki:Validationpage}}|redaktor]] a '''$2''' {{PLURAL:$2|používateľa|používateľov}} s právami [[{{MediaWiki:Validationpage}}|kontrolór]].",
	'validationstatistics-table' => "Dolu sú zobrazené štatistiky pre každý menný priestor okrem presmerovacích stránok.

'''Pozn.:''' nasledujúce údaje pochádzajú z vyrovnávacej pamäte a môžu byť niekoľko hodín staré.",
	'validationstatistics-ns' => 'Menný priestor',
	'validationstatistics-total' => 'Stránky',
	'validationstatistics-stable' => 'Skontrolované',
	'validationstatistics-latest' => 'Posledné skontrolované',
	'validationstatistics-synced' => 'Synchronizované/skontrolované',
	'validationstatistics-old' => 'Zastaralé',
);

/** Swedish (Svenska)
 * @author M.M.S.
 */
$messages['sv'] = array(
	'validationstatistics' => 'Valideringsstatistik',
	'validationstatistics-users' => "'''{{SITENAME}}''' har just nu '''$1''' {{PLURAL:$1|användare|användare}} med [[{{MediaWiki:Validationpage}}|redaktörsrättigheter]] och '''$2''' {{PLURAL:$2|användare|användare}} med [[{{MediaWiki:Validationpage}}|granskningsrättigheter]].",
	'validationstatistics-table' => "Statistik för varje namnrymd visas nedan, förutom omdirigeringssidor.

'''Notera:''' följande data är cachad för flera timmar och kan vara föråldrad.",
	'validationstatistics-ns' => 'Namnrymd',
	'validationstatistics-total' => 'Sidor',
	'validationstatistics-stable' => 'Granskad',
	'validationstatistics-latest' => 'Senast granskad',
	'validationstatistics-synced' => 'Synkad/Granskad',
	'validationstatistics-old' => 'Föråldrad',
);

/** Thai (ไทย)
 * @author Octahedron80
 */
$messages['th'] = array(
	'validationstatistics-ns' => 'เนมสเปซ',
);

/** Vietnamese (Tiếng Việt)
 * @author Minh Nguyen
 */
$messages['vi'] = array(
	'validationstatistics' => 'Thống kê phê chuẩn',
	'validationstatistics-users' => "Hiện nay, '''$1''' thành viên tại '''{{SITENAME}}''' có quyền [[{{MediaWiki:Validationpage}}|Chủ bút]] và '''$2''' thành viên có quyền [[{{MediaWiki:Validationpage}}|Người duyệt]].",
	'validationstatistics-table' => "Đây có thống kê về các không gian tên, trừ các trang đổi hướng.

'''Chú ý:''' Dữ liệu sau được nhớ đệm vài tiếng đồng hồ và có thể lỗi thời.",
	'validationstatistics-ns' => 'Không gian tên',
	'validationstatistics-total' => 'Số trang',
	'validationstatistics-stable' => 'Được duyệt',
	'validationstatistics-latest' => 'Được duyệt gần đây',
	'validationstatistics-synced' => 'Cập nhật/Duyệt',
	'validationstatistics-old' => 'Lỗi thời',
);

