<?php
/**
 * Internationalisation file for extension FlaggedRevs (group MakeReviewer).
 *
 * @addtogroup Extensions
 */

$messages = array();

/* English (Aaron Schulz) */
$messages['en'] = array(
	'makereviewer'                  => 'Promote/demote editors',
	'makereviewer-header'           => '<strong>This form is used by sysops and bureaucrats to promote users to article validators.</strong>

Type the name of the user in the box and press the button to set the user\'s rights.
Granting users reviewer status will automatically grant them editor status.
Revoking editor status will automatically revoke reviewer status.',
	'makereviewer-username'         => 'Name of the user:',
	'makereviewer-search'           => 'Go',
	'makereviewer-iseditor'         => '[[User:$1|$1]] has editor status.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] does not have editor status.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] has reviewer status.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] does not have reviewer status.',
	'makereviewer-legend'           => 'Change user rights',
	'makereviewer-change-e'         => 'Editor status:',
	'makereviewer-change-r'         => 'Reviewer status:',
	'makereviewer-grant1'           => 'Grant',
	'makereviewer-revoke1'          => 'Revoke',
	'makereviewer-grant2'           => 'Grant',
	'makereviewer-revoke2'          => 'Revoke',
	'makereviewer-comment'          => 'Comment:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] now has editor status.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] no longer has editor status.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] now has reviewer status.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] no longer has reviewer status.',
	'makereviewer-logpage'          => 'Editor status log',
	'makereviewer-logentrygrant-e'  => 'granted editor status to [[$1]]',
	'makereviewer-logentryrevoke-e' => 'removed editor status from [[$1]]',
	'makereviewer-logentrygrant-r'  => 'granted reviewer status to [[$1]]',
	'makereviewer-logentryrevoke-r' => 'removed reviewer status from [[$1]]',
	'makereviewer-autosum'          => 'autopromoted',
	'rights-editor-revoke'          => 'removed editor status from [[$1]]',
);

/** Arabic (العربية)
 * @author Meno25
 */
$messages['ar'] = array(
	'makereviewer'                  => 'ترقية/عزل المحررين',
	'makereviewer-header'           => '<strong>هذه الاستمارة تستخدم بواسطة مدراء النظام و البيروقراطيين لترقية المستخدمين لمصححي مقالات.</strong>

اكتب اسم المستخدم في الصندوق واضغط الزر لضبط صلاحيات المستخدم.
منح المستخدمين صلاحية مراجع سيؤدي لمنحهم صلاحية محرر تلقائيا. سحب صلاحية محرر
سيؤدي إلى سحب صلاحية مراجع تلقائيا.',
	'makereviewer-username'         => 'اسم المستخدم:',
	'makereviewer-search'           => 'اذهب',
	'makereviewer-iseditor'         => '[[User:$1|$1]] لديه صلاحية محرر.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] ليس لديه صلاحية محرر.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] لديه صلاحية مراجع.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] ليس لديه صلاحية مراجع.',
	'makereviewer-legend'           => 'تغيير صلاحيات مستخدم:',
	'makereviewer-change-e'         => 'حالة المحرر:',
	'makereviewer-change-r'         => 'حالة المراجع:',
	'makereviewer-grant1'           => 'منح',
	'makereviewer-revoke1'          => 'سحب',
	'makereviewer-grant2'           => 'منح',
	'makereviewer-revoke2'          => 'سحب',
	'makereviewer-comment'          => 'تعليق:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] لدية الآن صلاحية محرر.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] لم يعد لديه صلاحية محرر.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] لديه الآن صلاحية مراجع.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] لم يعد لديه صلاحية مراجع.',
	'makereviewer-logpage'          => 'سجل صلاحية المحرر',
	'makereviewer-logentrygrant-e'  => 'منح صلاحية محرر إلى [[$1]]',
	'makereviewer-logentryrevoke-e' => 'سحب صلاحية محرر من [[$1]]',
	'makereviewer-logentrygrant-r'  => 'منح صلاحية مراجع إلى [[$1]]',
	'makereviewer-logentryrevoke-r' => 'سحب صلاحية مراجع من [[$1]]',
	'makereviewer-autosum'          => 'ترقية تلقائية',
	'rights-editor-revoke'          => 'أزال حالة محرر من [[$1]]',
);

/** Asturian (Asturianu)
 * @author Esbardu
 */
$messages['ast'] = array(
	'makereviewer'                  => 'Ascender/rebaxar editores',
	'makereviewer-header'           => "<strong>Esti formulariu ye usáu polos alministradores y los burócrates p'ascender usuarios a correutores d'artículos.</strong>

Escribi'l nome del usuariu nel caxellu y calca nel botón pa configurar los derechos del usuariu. Conceder a un usuariu estatus de revisor concéde-y automáticamente l'estatus d'editor. Revocar l'estatus d'editor revóca-y automáticamente l'estatus de revisor.",
	'makereviewer-username'         => 'Nome del usuariu:',
	'makereviewer-search'           => 'Dir',
	'makereviewer-iseditor'         => "[[User:$1|$1]] tien estatus d'editor.",
	'makereviewer-noteditor'        => "[[User:$1|$1]] nun tien estatus d'editor.",
	'makereviewer-isvalidator'      => '[[User:$1|$1]] tien estatus de revisor.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] nun tien estatus de revisor.',
	'makereviewer-legend'           => "Camudar los derechos d'usuariu",
	'makereviewer-change-e'         => "Estatus d'editor:",
	'makereviewer-change-r'         => 'Estatus de revisor:',
	'makereviewer-grant1'           => 'Conceder',
	'makereviewer-revoke1'          => 'Revocar',
	'makereviewer-grant2'           => 'Conceder',
	'makereviewer-revoke2'          => 'Revocar',
	'makereviewer-comment'          => 'Comentariu:',
	'makereviewer-granted-e'        => "[[User:$1|$1]] tien agora estatus d'editor.",
	'makereviewer-revoked-e'        => "[[User:$1|$1]] yá nun tien estatus d'editor.",
	'makereviewer-granted-r'        => '[[User:$1|$1]] tien agora estatus de revisor.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] yá nun tien estatus de revisor.',
	'makereviewer-logpage'          => "Rexistru d'estatus d'editor",
	'makereviewer-logentrygrant-e'  => "concedió estatus d'editor a [[$1]]",
	'makereviewer-logentryrevoke-e' => "revocó l'estatus d'editor a [[$1]]",
	'makereviewer-logentrygrant-r'  => 'concedió estatus de revisor a [[$1]]',
	'makereviewer-logentryrevoke-r' => "revocó l'estatus de revisor a [[$1]]",
	'makereviewer-autosum'          => 'autopromocionáu',
	'rights-editor-revoke'          => "revocó l'estatus d'editor a [[$1]]",
);

$messages['bcl'] = array(
	'makereviewer-username'         => 'Pangaran kan parágamit:',
	'makereviewer-search'           => 'Dumanán',
	'makereviewer-legend'           => 'Ribayan an derechos kan parágamit:',
	'makereviewer-grant1'           => 'Otobón',
	'makereviewer-grant2'           => 'Otobón',
	'makereviewer-comment'          => 'Komento:',
);

/** Bulgarian (Български)
 * @author Borislav
 * @author DCLXVI
 */
$messages['bg'] = array(
	'makereviewer-username'         => 'Име на потребителя:',
	'makereviewer-search'           => 'Отиване',
	'makereviewer-iseditor'         => '[[User:$1|$1]] има правата на редактор.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] няма правата на редактор.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] има правата на рецензент.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] няма правата на рецензент.',
	'makereviewer-legend'           => 'Промяна на потребителските права',
	'makereviewer-change-e'         => 'Права на редактор:',
	'makereviewer-change-r'         => 'Права на рецензент:',
	'makereviewer-grant1'           => 'Предоставяне',
	'makereviewer-revoke1'          => 'Отнемане',
	'makereviewer-grant2'           => 'Предоставяне',
	'makereviewer-revoke2'          => 'Отнемане',
	'makereviewer-comment'          => 'Коментар:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] вече има права на редактор.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] повече няма права на редактор.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] вече има права на рецензент.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] повече няма права на рецензент.',
	'makereviewer-logpage'          => 'Дневник на редакторските права',
	'makereviewer-logentrygrant-e'  => 'даде права на редактор на [[$1]]',
	'makereviewer-logentryrevoke-e' => 'отне правата на редактор на [[$1]]',
	'makereviewer-logentrygrant-r'  => 'даде права на рецензент на [[$1]]',
	'makereviewer-logentryrevoke-r' => 'отне правата на рецензент на [[$1]]',
	'rights-editor-revoke'          => 'отне правата на редактор на [[$1]]',
);

/** Bengali (বাংলা)
 * @author Bellayet
 */
$messages['bn'] = array(
	'makereviewer-username'         => 'ব্যবহারকারীর নাম:',
	'makereviewer-search'           => 'যাও',
	'makereviewer-iseditor'         => '[[User:$1|$1]] এর সস্পাদক পদমর্যাদা রয়েছে।',
	'makereviewer-noteditor'        => '[[User:$1|$1]] এর সম্পাদক পদমর্যাদা নাই।',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] এর পর্যালোচক পদমর্যাদা রয়েছে।',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] এর পর্যালোচক পদমর্যাদা নাই।',
	'makereviewer-legend'           => 'ব্যবহারকারীর অধিকারসমূহ পরিবর্তন করো',
	'makereviewer-change-e'         => 'সম্পাদকের অবস্থা:',
	'makereviewer-change-r'         => 'সংশোধনের অবস্থা:',
	'makereviewer-grant1'           => 'অনুমোদন',
	'makereviewer-revoke1'          => 'বাতিল',
	'makereviewer-grant2'           => 'অনুমোদন',
	'makereviewer-revoke2'          => 'বাতিল',
	'makereviewer-comment'          => 'মন্তব্য:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] এর এখন সম্পাদক পদমর্যাদা রয়েছে।',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] এর এখন আর সম্পাদক পদমর্যাদা নাই।',
	'makereviewer-granted-r'        => '[[User:$1|$1]] এর এখন পর্যালোচক পদমর্যাদা রয়েছে।',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] এর এখন আর পর্যালোচক পদমর্যাদা নাই।',
	'makereviewer-logpage'          => 'সম্পাদকের অবস্থার লগ',
	'makereviewer-logentrygrant-e'  => '[[$1]] এর সম্পাদক পদমর্যাদা অনুমোদন করুন',
	'makereviewer-logentryrevoke-e' => '[[$1]] এর সম্পাদক পদমর্যাদা প্রত্যাহার করুন',
	'makereviewer-logentrygrant-r'  => '[[$1]] এর পর্যালোচক পদমর্যাদা অনুমোদন করুন',
	'makereviewer-logentryrevoke-r' => '[[$1]] এর পর্যালোচক পদমর্যাদা প্রত্যাহার করুন',
	'rights-editor-revoke'          => '[[$1]] এর সম্পাদক পদমর্যাদা প্রত্যাহার করুন',
);

/** Breton (Brezhoneg)
 * @author Fulup
 */
$messages['br'] = array(
	'makereviewer-search' => 'Mont',
);

$messages['ca'] = array(
	'makereviewer'                  => 'Promociona o degrada un usuari',
	'makereviewer-header'           => '<strong>Aquest formulari serveix perquè els administradors i buròcrates puguin promocionar els usuaris per a validar articles.</strong>

Escriviu el nom de l\'usuari en la casella i premeu el botó per adjudicar-li els nivells que cregueu necessaris. El fet de donar el nivell de supervisor a un usuari farà que automàticament rebi també el d\'editor, i treure el nivell d\'editor a un usuari provocarà que també perdi el de supervisor (si el té).',
	'makereviewer-username'         => 'Nom d\'usuari:',
	'makereviewer-search'           => 'Accepta',
	'makereviewer-iseditor'         => 'L\'usuari [[User:$1|$1]] té el nivell d\'editor.',
	'makereviewer-noteditor'        => 'L\'usuari [[User:$1|$1]] no té el nivell d\'editor.',
	'makereviewer-isvalidator'      => 'L\'usuari [[User:$1|$1]] té el nivell de supervisor.',
	'makereviewer-notvalidator'     => 'L\'usuari [[User:$1|$1]] no té el nivell de supervisor.',
	'makereviewer-legend'           => 'Canvia els drets d\'usuari',
	'makereviewer-change-e'         => 'Estatus d\'editor:',
	'makereviewer-change-r'         => 'Estatus de supervisor:',
	'makereviewer-grant1'           => 'Concedeix',
	'makereviewer-revoke1'          => 'Revoca',
	'makereviewer-grant2'           => 'Concedeix',
	'makereviewer-revoke2'          => 'Revoca',
	'makereviewer-comment'          => 'Comentari:',
	'makereviewer-granted-e'        => 'L\'usuari [[User:$1|$1]] ha obtingut el nivell d\'editor.',
	'makereviewer-revoked-e'        => 'L\'usuari [[User:$1|$1]] ja no té més el nivell d\'editor.',
	'makereviewer-granted-r'        => 'L\'usuari [[User:$1|$1]] ha obtingut el nivell de supervisor.',
	'makereviewer-revoked-r'        => 'L\'usuari [[User:$1|$1]] ja no té més el nivell de supervisor.',
	'makereviewer-logpage'          => 'Registre de nivells d\'edició',
	'makereviewer-logpagetext'      => 'Aquest registre informa dels canvis de nivell dels usuaris respecte la [[{{MediaWiki:Makevalidate-page}}|validació d\'articles]].',
	'makereviewer-logentrygrant-e'  => 'concedit el nivell d\'editor a [[$1]]',
	'makereviewer-logentryrevoke-e' => 'tret el nivell d\'editor a [[$1]]',
	'makereviewer-logentrygrant-r'  => 'concedit el nivell de supervisor a [[$1]]',
	'makereviewer-logentryrevoke-r' => 'tret el nivell de supervisor a [[$1]]',
	'makereviewer-autosum'          => 'autoconcedit',
	'rights-editor-grant'           => 'concedit el nivell d\'editor a [[$1]]',
	'rights-editor-revoke'          => 'tret el nivell d\'editor a [[$1]]',
);

$messages['cs'] = array(
	'makereviewer'                  => 'Přidat nebo odebrat editory',
	'makereviewer-header'           => '<strong>Tento formulář slouží správcům a byrokratům k povyšování uživatelů na editory s právem schvalovat články.</strong>

Přidělením statusu posuzovatele se automaticky přidělí i status editora. Odebráním statusu editora se automaticky odebere i status posuzovatele.',
	'makereviewer-username'         => 'Jméno uživatele',
	'makereviewer-search'           => 'Hledat',
	'makereviewer-iseditor'         => '[[User:$1|$1]] má status editora.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] nemá status editora.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] má status posuzovatele.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] nemá status posuzovatele.',
	'makereviewer-legend'           => 'Změnit uživatelská práva:',
	'makereviewer-change-e'         => 'status editora:',
	'makereviewer-change-r'         => 'status posuzovatele:',
	'makereviewer-grant1'           => 'Přidělit',
	'makereviewer-revoke1'          => 'Odebrat',
	'makereviewer-grant2'           => 'Přidělit',
	'makereviewer-revoke2'          => 'Odebrat',
	'makereviewer-comment'          => 'Komentář:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] teď má status editora.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] již nemá status editora.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] teď má status posuzovatele.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] již nemá status posuzovatele.',
	'makereviewer-logpage'          => 'Kniha práv editorů',
	'makereviewer-logpagetext'      => 'Tato kniha zobrazuje změny statusu uživatelů pro [[{{MediaWiki:Makevalidate-page}}|schvalování článků]].',
	'makereviewer-logentrygrant-e'  => 'přiděluje status editora uživateli [[$1]]',
	'makereviewer-logentryrevoke-e' => 'odebírá status editora uživateli [[$1]]',
	'makereviewer-logentrygrant-r'  => 'přiděluje status posuzovatele uživateli [[$1]]',
	'makereviewer-logentryrevoke-r' => 'odebírá status posuzovatele uživateli [[$1]]',
	'makereviewer-autosum'          => 'automaticky povýšen',
	'rights-editor-grant'           => 'přiděluje status editora uživateli [[$1]]',
	'rights-editor-revoke'          => 'odebírá status editora uživateli [[$1]]',
);

/** German (Deutsch)
 * @author Raimond Spekking
 */
$messages['de'] = array(
	'makereviewer'                  => 'Sichter/Prüf-Recht erteilen/entziehen',
	'makereviewer-header'           => '<strong>Mit diesem Formular können Administratoren und Bürokraten Benutzern das Recht zur Artikelprüfung erteilen.</strong>

Gebe den Benutzernamen in das Feld ein und klicke auf die Schaltfläche, um das Recht zu setzen.
Durch Erteilung des Prüfrechts wird automatisch auch das Sichter-Recht erteilt. Der Entzug des Sichter-Rechts hat automatisch den Entzug des Prüfrechts zur Folge.',
	'makereviewer-username'         => 'Benutzername:',
	'makereviewer-search'           => 'Status abfragen',
	'makereviewer-iseditor'         => '[[User:$1|$1]] hat das Sichter-Recht.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] hat kein Sichter-Recht.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] hat das Prüfrecht.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] hat kein Prüfrecht.',
	'makereviewer-legend'           => 'Benutzerrechte ändern',
	'makereviewer-change-e'         => 'Sichter-Recht:',
	'makereviewer-change-r'         => 'Prüfrecht:',
	'makereviewer-grant1'           => 'Erteilen',
	'makereviewer-revoke1'          => 'Entziehen',
	'makereviewer-grant2'           => 'Erteilen',
	'makereviewer-revoke2'          => 'Entziehen',
	'makereviewer-comment'          => 'Kommentar:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] hat nun das Sichter-Recht.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] wurde das Sichter-Recht entzogen.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] hat nun das Prüfrecht.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] wurde das Prüfrecht entzogen.',
	'makereviewer-logpage'          => 'Sichter-/Prüf-Rechte-Logbuch',
	'makereviewer-logentrygrant-e'  => 'erteilte das Sichter-Recht an [[$1]]',
	'makereviewer-logentryrevoke-e' => 'entzog das Sichter-Recht von [[$1]]',
	'makereviewer-logentrygrant-r'  => 'erteilte das Prüfrecht an [[$1]]',
	'makereviewer-logentryrevoke-r' => 'entzog das Prüfrecht von [[$1]]',
	'makereviewer-autosum'          => 'automatische Rechtevergabe',
	'rights-editor-revoke'          => 'entzog das Sichter-Recht von [[$1]]',
	'rights-editor-grant'           => 'erteilte das Sichter-Recht an [[$1]]',
);

/** Ewe (Eʋegbe)
 * @author M.M.S.
 */
$messages['ee'] = array(
	'makereviewer-search' => 'Yi',
);

/** Greek (Ελληνικά)
 * @author Consta
 */
$messages['el'] = array(
	'makereviewer-username' => 'Όνομα του χρήστη:',
	'makereviewer-comment'  => 'Σχόλιο:',
);

/** Esperanto (Esperanto)
 * @author Yekrats
 */
$messages['eo'] = array(
	'makereviewer-username'  => 'Nomo de la uzanto:',
	'makereviewer-search'    => 'Ek!',
	'makereviewer-iseditor'  => '[[User:$1|$1]] havas statuson de redaktanto.',
	'makereviewer-noteditor' => '[[User:$1|$1]] ne havas statuson de redaktanto.',
	'makereviewer-comment'   => 'Komento:',
	'makereviewer-granted-e' => '[[User:$1|$1]] nun havas statuson redaktanto.',
	'makereviewer-revoked-e' => '[[User:$1|$1]] ne plu havas statuson redaktanto.',
	'makereviewer-granted-r' => '[[User:$1|$1]] nun havas statuson reekzamenanto.',
	'makereviewer-revoked-r' => '[[User:$1|$1]] ne plu havas statuson reekzamenanto.',
);

$messages['ext'] = array(
	'makereviewer-username'         => 'Nombri el usuáriu:',
	'makereviewer-search'           => 'Dil',
);

/** فارسی (فارسی)
 * @author Huji
 */
$messages['fa'] = array(
	'makereviewer'                  => 'ترفیع دادن یا تنزل دادن ویرایشگران',
	'makereviewer-header'           => '<strong>این فرم توسط مدیران و دیوان‌سالارها برای ترفیع دادن کاربران به درجه بازبینی‌کننده مقاله‌ها استفاده می‌شود.</strong>

نام کاربر مورد نظر را در جعبه وارد کنید و دکمه را فشار دهید تا اختیارات کاربر را ببینید. دادن اختیارات بازبینی به یک کاربر خود به خود به آن‌ها اختیارات ویراستاری را هم می‌دهد. پس گرفتن اختیارات ویراستاری از یک کاربر هم اختیارات بازبینی را از او به طور خودکار می‌گیرد.',
	'makereviewer-username'         => 'نام کاربر:',
	'makereviewer-search'           => 'برو',
	'makereviewer-iseditor'         => '[[User:$1|$1]] دارای اختیارات ویراستاری است.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] دارای اختیارات ویراستاری نیست.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] دارای اختیارات بازبینی است.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] دارای اختیارات بازبینی نیست.',
	'makereviewer-legend'           => 'تغییر اختیارات کاربر',
	'makereviewer-change-e'         => 'اختیارات ویراستاری:',
	'makereviewer-change-r'         => 'اختیارات بازبینی:',
	'makereviewer-grant1'           => 'اعطا',
	'makereviewer-revoke1'          => 'بازپس‌گیری',
	'makereviewer-grant2'           => 'اعطا',
	'makereviewer-revoke2'          => 'بازپس‌گیری',
	'makereviewer-comment'          => 'توضیح:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] اکنون دارای اختیارات ویراستاری است.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] دیگر دارای اختیارات ویراستاری نیست.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] اکنون دارای اختیارات بازبینی است.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] دیگر دارای اختیارات بازبینی نیست.',
	'makereviewer-logpage'          => 'سیاههٔ اختیارات ویراستاری',
	'makereviewer-logentrygrant-e'  => 'به [[$1]] اختیارات ویراستاری داد.',
	'makereviewer-logentryrevoke-e' => 'اختیارات ویراستاری را از [[$1]] گرفت',
	'makereviewer-logentrygrant-r'  => 'به [[$1]] اختیارات بازبینی داد',
	'makereviewer-logentryrevoke-r' => 'اختیارات بازبینی را از [[$1]] گرفت',
	'makereviewer-autosum'          => 'اعطای خودکار',
	'rights-editor-revoke'          => 'اختیارات ویراستاری را از [[$1]] گرفت',
);

/** French (Français)
 * @author Urhixidur
 * @author Sherbrooke
 * @author ChrisPtDe
 */
$messages['fr'] = array(
	'makereviewer'                  => 'Promouvoir/Démettre les éditeurs',
	'makereviewer-header'           => "'''Ce formulaire est utilisé par les administrateurs et les bureaucrates pour promouvoir les contributeurs au poste de réviseur d’articles.'''

Saisir le nom du contributeur dans la boîte de dialogue pour lui donner ces droits. Donner les droits de réviseur donne automatiquement les droits d’éditeur. Révoquer les droits de réviseur révoque automatiquement les droits d’éditeur.",
	'makereviewer-username'         => 'Nom du contributeur :',
	'makereviewer-search'           => 'Aller',
	'makereviewer-iseditor'         => "[[User:$1|$1]] a les droits d'éditeur.",
	'makereviewer-noteditor'        => "[[User:$1|$1]] n'a pas les droits d'éditeur.",
	'makereviewer-isvalidator'      => '[[User:$1|$1]] a les droits de réviseur.',
	'makereviewer-notvalidator'     => "[[User:$1|$1]] n'a pas les droits de réviseur.",
	'makereviewer-legend'           => 'Modifier les droits du contributeur',
	'makereviewer-change-e'         => "Droits de l'éditeur :",
	'makereviewer-change-r'         => 'Droits du réviseur :',
	'makereviewer-grant1'           => 'Accorder',
	'makereviewer-revoke1'          => 'Révoquer',
	'makereviewer-grant2'           => 'Accorder',
	'makereviewer-revoke2'          => 'Révoquer',
	'makereviewer-comment'          => 'Commentaire :',
	'makereviewer-granted-e'        => "[[User:$1|$1]] a les droits d'éditeur.",
	'makereviewer-revoked-e'        => "[[User:$1|$1]] n'a plus les droits d'éditeur.",
	'makereviewer-granted-r'        => '[[User:$1|$1]] a les droits de réviseur.',
	'makereviewer-revoked-r'        => "[[User:$1|$1]] n'a plus les droits de réviseur.",
	'makereviewer-logpage'          => "Journal des droits de l'éditeur",
	'makereviewer-logentrygrant-e'  => "a accordé les droits d'éditeur à [[$1]]",
	'makereviewer-logentryrevoke-e' => "a révoqué les droits d'éditeur de [[$1]]",
	'makereviewer-logentrygrant-r'  => 'a accordé les droits de réviseur à [[$1]]',
	'makereviewer-logentryrevoke-r' => 'a révoqué les droits de réviseur de [[$1]]',
	'makereviewer-autosum'          => 'Autopromu',
	'rights-editor-revoke'          => "a révoqué les droits d'éditeur de [[$1]]",
);

/** Franco-Provençal (Arpetan)
 * @author ChrisPtDe
 */
$messages['frp'] = array(
	'makereviewer'                  => 'Nomar/cassar los èditors',
	'makereviewer-header'           => "'''Ceti formulèro est utilisâ per los administrators et los burôcrates por nomar los contributors u pôsto de rèvisor d’articllos.'''

Buchiér lo nom du contributor dens la bouèta por lui balyér celos drêts. Balyér los drêts de rèvisor balye ôtomaticament los drêts d’èditor. Rèvocar los drêts de rèvisor rèvoque ôtomaticament los drêts d’èditor.",
	'makereviewer-username'         => 'Nom du contributor :',
	'makereviewer-search'           => 'Alar',
	'makereviewer-iseditor'         => '[[User:$1|$1]] at los drêts d’èditor.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] at pas los drêts d’èditor.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] at los drêts de rèvisor.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] at pas los drêts de rèvisor.',
	'makereviewer-legend'           => 'Modifiar los drêts du contributor',
	'makereviewer-change-e'         => 'Drêts d’èditor :',
	'makereviewer-change-r'         => 'Drêts de rèvisor :',
	'makereviewer-grant1'           => 'Balyér',
	'makereviewer-revoke1'          => 'Rèvocar',
	'makereviewer-grant2'           => 'Balyér',
	'makereviewer-revoke2'          => 'Rèvocar',
	'makereviewer-comment'          => 'Comentèro :',
	'makereviewer-granted-e'        => 'Dês ora, [[User:$1|$1]] at los drêts d’èditor.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] at pas més los drêts d’èditor.',
	'makereviewer-granted-r'        => 'Dês ora, [[User:$1|$1]] at los drêts de rèvisor.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] at pas més los drêts de rèvisor.',
	'makereviewer-logpage'          => 'Jornal des drêts d’èditor',
	'makereviewer-logentrygrant-e'  => 'at balyê los drêts d’èditor a [[$1]]',
	'makereviewer-logentryrevoke-e' => 'at rèvocâ los drêts d’èditor de [[$1]]',
	'makereviewer-logentrygrant-r'  => 'at balyê los drêts de rèvisor a [[$1]]',
	'makereviewer-logentryrevoke-r' => 'at rèvocâ los drêts de rèvisor de [[$1]]',
	'makereviewer-autosum'          => 'Ôtonomâ',
	'rights-editor-revoke'          => 'at rèvocâ los drêts d’èditor de [[$1]]',
);

/** Galician (Galego)
 * @author Alma
 */
$messages['gl'] = array(
	'makereviewer'                  => 'Promover/degradar editores',
	'makereviewer-header'           => '<strong>Este formulario é usado por administradores e burócratas para promover aos usuarios de artigos máis válidos.</strong>

Introduza o nome do usuario na caixa e prema o botón para establecer os dereitos dos usuarios. A concesión do status de revisor de usuarios automaticamente lles concede o status de editor. Revocar o status de editor automaticamente revocará o status de revisor.',
	'makereviewer-username'         => 'Nome do usuario:',
	'makereviewer-search'           => 'Ir',
	'makereviewer-iseditor'         => '[[User:$1|$1]] ten o status de editor.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] non ten o status de editor.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] ten o status de revisor.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] non ten o status de revisor.',
	'makereviewer-legend'           => 'Mudar dereitos de usuario',
	'makereviewer-change-e'         => 'Status de editor:',
	'makereviewer-change-r'         => 'Status de revisor:',
	'makereviewer-grant1'           => 'Conceder',
	'makereviewer-revoke1'          => 'Retirar',
	'makereviewer-grant2'           => 'Conceder',
	'makereviewer-revoke2'          => 'Retirar',
	'makereviewer-comment'          => 'Comentario:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] agora ten o status de editor.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] xa non ten o status de editor.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] agora ten o status de revisor.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] xa non ten o status de revisor.',
	'makereviewer-logpage'          => 'Rexistro do status de editor',
	'makereviewer-logentrygrant-e'  => 'outorgado o status de editor a [[$1]]',
	'makereviewer-logentryrevoke-e' => 'eliminado o status de editor a [[$1]]',
	'makereviewer-logentrygrant-r'  => 'outorgado o status de revisor a [[$1]]',
	'makereviewer-logentryrevoke-r' => 'eliminado o status de revisor de [[$1]]',
	'makereviewer-autosum'          => 'autopromocionado',
	'rights-editor-grant'           => 'outorgado o status de editor a [[$1]]',
	'rights-editor-revoke'          => 'eliminado o status de editor de [[$1]]',
);

/** Croatian (Hrvatski)
 * @author Dnik
 * @author SpeedyGonsales
 */
$messages['hr'] = array(
	'makereviewer'                  => 'Promoviraj/ukini prava suradniku',
	'makereviewer-header'           => '<strong>Ovaj obrazac koriste administratori i birokrati da promoviraju suradnike u ocjenjivače članaka.</strong>

Unesite ime suradnika u rubriku i pritisnie tipku da postavite prava korisnika. Dodjeljivanje prava ocjenjivača će automatski dodijeliti status urednika. Oduzimanje prava urednika će automatski oduzeti status ocjenjivača.',
	'makereviewer-username'         => 'Ime suradnika:',
	'makereviewer-search'           => 'Kreni',
	'makereviewer-iseditor'         => '[[Suradnik:$1|$1]] ima status urednika.',
	'makereviewer-noteditor'        => '[[Suradnik:$1|$1]] nema status urednika.',
	'makereviewer-isvalidator'      => '[[Suradnik:$1|$1]] ima status ocjenivača.',
	'makereviewer-notvalidator'     => '[[Suradnik:$1|$1]] nema status ocjenjivača.',
	'makereviewer-legend'           => 'Promijeni prava korisnika',
	'makereviewer-change-e'         => 'Status urednika:',
	'makereviewer-change-r'         => 'Status ocjenjivača:',
	'makereviewer-grant1'           => 'Dodjeli',
	'makereviewer-revoke1'          => 'Oduzmi',
	'makereviewer-grant2'           => 'Dodjeli',
	'makereviewer-revoke2'          => 'Oduzmi',
	'makereviewer-comment'          => 'Napomena:',
	'makereviewer-granted-e'        => '[[Suradnik:$1|$1]] sada ima status urednika.',
	'makereviewer-revoked-e'        => '[[Suradnik:$1|$1]] više nema status urednika.',
	'makereviewer-granted-r'        => '[[Suradnik:$1|$1]] sada ima status ocjenivača.',
	'makereviewer-revoked-r'        => '[[Suradnik:$1|$1]] više nema status ocjenivača.',
	'makereviewer-logpage'          => 'Evidencija statusa urednika',
	'makereviewer-logentrygrant-e'  => 'dodjeljen status urednika suradniku [[$1]]',
	'makereviewer-logentryrevoke-e' => 'oduzet status urednika suradniku [[$1]]',
	'makereviewer-logentrygrant-r'  => 'dodjeljen status ocjenjivača suradniku [[$1]]',
	'makereviewer-logentryrevoke-r' => 'oduzet status ocjenjivača suradniku [[$1]]',
	'makereviewer-autosum'          => 'samopromoviran',
	'rights-editor-revoke'          => 'oduzet status urednika suradniku [[$1]]',
);

$messages['hsb'] = array(
	'makereviewer'                  => 'Wobdźěłowarjow zasadźić/wotsadźić',
	'makereviewer-header'           => '<strong>Z tutym formularom móža administratorojo a běrokraća wužiwarjam prawo pruwowanja dać.</strong>

Zapisaj wužiwarske mjeno do pola a klikń na tłóčatko, zo by wužiwarske prawo spožčił. Spožčenje statusa pruwowarja budźe so awtomatisce status wobdźěłowarja spožčeć.',
	'makereviewer-username'         => 'Wužiwarske mjeno:',
	'makereviewer-search'           => 'Pytać',
	'makereviewer-iseditor'         => '[[User:$1|$1]] ma prawo wobdźěłowarja.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] nima prawo wobdźěłowarja.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] ma prawo pruwowarja.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] nima prawo pruwowarja.',
	'makereviewer-legend'           => 'Wužiwarske prawa změnić',
	'makereviewer-change-e'         => 'Prawo wobdźěłowarja:',
	'makereviewer-change-r'         => 'Status pruwowarja:',
	'makereviewer-grant1'           => 'Dać',
	'makereviewer-revoke1'          => 'Zebrać',
	'makereviewer-grant2'           => 'Dać',
	'makereviewer-revoke2'          => 'Zebrać',
	'makereviewer-comment'          => 'Komentar:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] ma nětko status wobdźěłowarja.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] hižo status wobdźěłowarja nima.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] ma nětko status pruwowarja.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] hižo status pruwowarja nima.',
	'makereviewer-logpage'          => 'Protokol statusa wobdźěłowarja',
	'makereviewer-logpagetext'      => 'To je protokol změnow [[{{MediaWiki:Makevalidate-page}}|pruwowanskich prawow]] wužiwarja.',
	'makereviewer-page'             => '{{ns:help}}:Pruwowanje nastawkow',
	'makereviewer-logentrygrant-e'  => 'Status wobdźěłowarja bu [[$1]] daty.',
	'makereviewer-logentryrevoke-e' => 'Status wobdźěłowarja bu [[$1]] zebrany.',
	'makereviewer-logentrygrant-r'  => 'status pruwowarja bu [[$1]] daty.',
	'makereviewer-logentryrevoke-r' => 'status pruwowarja bu [[$1]] zebrany.',
	'makereviewer-autosum'          => 'Prawo awtomatisce spožčene',
	'rights-editor-grant'           => 'status wobdźěłowarja bu [[$1]] daty.',
	'rights-editor-revoke'          => 'status wobdźěłowarja bu [[$1]] zebrany.',
);

/** Hungarian (Magyar)
 * @author Bdanee
 * @author KossuthRad
 */
$messages['hu'] = array(
	'makereviewer'                  => 'Szerkesztők kinevezése/lefokozása',
	'makereviewer-header'           => '<strong>Ezen űrlap segítségével az adminisztrátorok és bürokraták szerkesztőket nevezhetnek ki szócikkek ellenőrzőjévé.</strong>

Írd be a nevet a dobozba, és kattints a gombra, hogy beállíthasd a jogait. Az ellenőri jogok megadása egyben a szerkesztői jogok megadását is jelenti, míg a szerkesztői jogok megvonása az ellenőri jogok megvonását is jelenti.',
	'makereviewer-username'         => 'A felhasználó neve:',
	'makereviewer-search'           => 'Menj',
	'makereviewer-iseditor'         => '[[User:$1|$1]] rendelkezik szerkesztői joggal.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] nem rendelkezik szerkesztői jogokkal.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] rendelkezik ellenőri jogokkal.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] nem rendelkezik ellenőri jogokkal.',
	'makereviewer-legend'           => 'Szerkesztő jogainak megváltoztatása',
	'makereviewer-change-e'         => 'Szerkesztő állapota:',
	'makereviewer-change-r'         => 'Ellenőri állapot:',
	'makereviewer-grant1'           => 'Megadás',
	'makereviewer-revoke1'          => 'Visszavonás',
	'makereviewer-grant2'           => 'Megadás',
	'makereviewer-revoke2'          => 'Visszavonás',
	'makereviewer-comment'          => 'Megjegyzés:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] ezentúl rendelkezik szerkesztői jogokkal.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] ezentúl nem rendelkezik szerkesztői jogokkal.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] ezentúl rendelkezik ellenőri jogokkal.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] ezentúl nem rendelkezik ellenőri jogokkal.',
	'makereviewer-logpage'          => 'Szerkesztői állapot napló',
	'makereviewer-logentrygrant-e'  => 'szerkesztői jogokat adott [[$1]] számára',
	'makereviewer-logentryrevoke-e' => 'elvette [[$1]] szerkesztői jogait',
	'makereviewer-logentrygrant-r'  => 'ellenőri jogokat adott [[$1]] számára',
	'makereviewer-logentryrevoke-r' => 'elvette [[$1]] ellenőri jogait',
	'makereviewer-autosum'          => 'automatikusan megadva',
	'rights-editor-revoke'          => '[[$1]] szerkesztői jogai meg lettek vonva',
);

/** Indonesian (Bahasa Indonesia)
 * @author IvanLanin
 */
$messages['id'] = array(
	'makereviewer'                  => 'Pengangkatan/penurunan editor',
	'makereviewer-header'           => '<strong>Isian ini digunakan oleh pengurus dan birokrat untuk mengangkat pengguna menjadi pemvalidasi artikel.</strong>

Masukkan nama pengguna pada kotak isian dan tekan tombol untuk mengatur hak pengguna. Memberikan status peninjau akan secara otomatis memberikan juga status editor. Mencabut status editor akan secara otomatis mencabut status peninjau.',
	'makereviewer-username'         => 'Nama pengguna:',
	'makereviewer-search'           => 'Cari',
	'makereviewer-iseditor'         => '[[User:$1|$1]] memiliki status editor.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] tak memiliki status editor.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] memiliki status peninjau.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] tak memiliki status peninjau.',
	'makereviewer-legend'           => 'Ganti hak pengguna',
	'makereviewer-change-e'         => 'Status editor:',
	'makereviewer-change-r'         => 'Status peninjau:',
	'makereviewer-grant1'           => 'Berikan',
	'makereviewer-revoke1'          => 'Cabut',
	'makereviewer-grant2'           => 'Berikan',
	'makereviewer-revoke2'          => 'Cabut',
	'makereviewer-comment'          => 'Komentar:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] sekarang memiliki status editor.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] tak lagi memiliki status editor.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] sekarang memiliki status peninjau.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] tak lagi memiliki status peninjau.',
	'makereviewer-logpage'          => 'Log status editor',
	'makereviewer-logentrygrant-e'  => 'memberikan status editor untuk [[$1]]',
	'makereviewer-logentryrevoke-e' => 'mencabut status editor dari [[$1]]',
	'makereviewer-logentrygrant-r'  => 'memberikan status peninjau untuk [[$1]]',
	'makereviewer-logentryrevoke-r' => 'mencabut status peninjau dari [[$1]]',
	'makereviewer-autosum'          => 'promosi otomatis',
	'rights-editor-revoke'          => 'mencabut status editor dari [[$1]]',
);

/** Icelandic (Íslenska)
 * @author S.Örvarr.S
 */
$messages['is'] = array(
	'makereviewer-username' => 'Nafn notanda:',
	'makereviewer-search'   => 'Áfram',
	'makereviewer-revoke2'  => 'Afturkalla',
	'makereviewer-comment'  => 'Athugasemd:',
);

/** Japanese (日本語)
 * @author JtFuruhata
 */
$messages['ja'] = array(
	'makereviewer'                  => '記事査読権限の付与/剥奪',
	'makereviewer-header'           => '<strong>{{int:group-sysop}}または{{int:group-bureaucrat}}が利用者の記事査読権限を変更するためのフォームです。</strong>

権限を変更する利用者名を入力してボタンを押してください。査読者権限を付与すると、自動的に編集者権限も付与されます。編集者権限を剥奪すると、自動的に査読者権限も剥奪されます。',
	'makereviewer-username'         => '利用者名:',
	'makereviewer-search'           => '査読権限表示',
	'makereviewer-iseditor'         => '[[{{ns:user}}:$1|$1]] には、編集者権限が付与されています。',
	'makereviewer-noteditor'        => '[[{{ns:user}}:$1|$1]] に、編集者権限は付与されていません。',
	'makereviewer-isvalidator'      => '[[{{ns:user}}:$1|$1]] には、査読者権限が付与されています。',
	'makereviewer-notvalidator'     => '[[{{ns:user}}:$1|$1]] に、査読者権限は付与されていません。',
	'makereviewer-legend'           => '利用者権限の変更',
	'makereviewer-change-e'         => '編集者権限:',
	'makereviewer-change-r'         => '査読者権限:',
	'makereviewer-grant1'           => '付与する',
	'makereviewer-revoke1'          => '付与しない',
	'makereviewer-grant2'           => '付与する',
	'makereviewer-revoke2'          => '付与しない',
	'makereviewer-comment'          => '変更内容の要約:',
	'makereviewer-granted-e'        => '[[{{ns:user}}:$1|$1]] に、編集者権限が付与されました。',
	'makereviewer-revoked-e'        => '[[{{ns:user}}:$1|$1]] に、編集者権限はありません。',
	'makereviewer-granted-r'        => '[[{{ns:user}}:$1|$1]] に、査読者権限が付与されました。',
	'makereviewer-revoked-r'        => '[[{{ns:user}}:$1|$1]] に、査読者権限はありません。',
	'makereviewer-logpage'          => '記事査読権限変更ログ',
	'makereviewer-logentrygrant-e'  => '[[$1]] へ編集者権限付与',
	'makereviewer-logentryrevoke-e' => '[[$1]] の編集者権限取り消し',
	'makereviewer-logentrygrant-r'  => '[[$1]] へ査読者権限付与',
	'makereviewer-logentryrevoke-r' => '[[$1]] の査読者権限取り消し',
	'makereviewer-autosum'          => '自動権限付与',
	'rights-editor-revoke'          => '[[$1]] の編集者権限取り消し',
);

/** Kazakh (Arabic) (قازاقشا (توتە))
 * @author AlefZet
 */
$messages['kk-arab'] = array(
	'makereviewer'                  => 'تۇزەتۋشى كۇيىن بەرۋ نە قايتارۋ',
	'makereviewer-header'           => '<strong>بۇل ٴپىشىندى قاتىسۋشىلارعا ماقالا راستاۋشى اتاعىن بەرۋ ٴۇشىن اكىمشىلەر مەن بىتىكشىلەر
قولدانادى.</strong>

قاتىسۋشى قۇقىقتارىن تاپسىرۋ ٴۇشىن قاتىسۋشى اتىن جولاقتا تەرىڭىز دە باتىرمانى باسىڭىز.
قاتىسۋشىلارعا سىن بەرۋشى كۇيىن بەرگەندە ولارعا تۇزەتۋشى كۇيى دە وزدىكتىك بەرىلەدى. تۇزەتۋشى كۇيىن قايتا شاقىرعاندا
سىن بەرۋشى كۇيى دە وزدىكتىك قايتا شاقىرىلادى.',
	'makereviewer-username'         => 'قاتىسۋشى اتى:',
	'makereviewer-search'           => 'ٴوتۋ',
	'makereviewer-iseditor'         => '[[{{ns:user}}:$1|$1]] دەگەندە تۇزەتۋشى كۇيى بار.',
	'makereviewer-noteditor'        => '[[{{ns:user}}:$1|$1]] دەگەننەن تۇزەتۋشى كۇيى جوق.',
	'makereviewer-isvalidator'      => '[[{{ns:user}}:$1|$1]] دەگەندە سىن بەرۋشى كۇيى بار.',
	'makereviewer-notvalidator'     => '[[{{ns:user}}:$1|$1]] دەگەندە سىن بەرۋشى كۇيى جوق.',
	'makereviewer-legend'           => 'قاتىسۋشى قۇقىقتارىن وزگەرتۋ',
	'makereviewer-change-e'         => 'تۇزەتۋشى كۇيى:',
	'makereviewer-change-r'         => 'سىن بەرۋشى كۇيى:',
	'makereviewer-grant1'           => 'بەرۋ',
	'makereviewer-revoke1'          => 'قايتا شاقىرۋ',
	'makereviewer-grant2'           => 'بەرۋ',
	'makereviewer-revoke2'          => 'قايتا شاقىرۋ',
	'makereviewer-comment'          => 'ماندەمەسى:',
	'makereviewer-granted-e'        => '[[{{ns:user}}:$1|$1]] دەگەندە ەندى تۇزەتۋشى كۇيى بار.',
	'makereviewer-revoked-e'        => '[[{{ns:user}}:$1|$1]] دەگەندە ەندى تۇزەتۋشى كۇيى جوق.',
	'makereviewer-granted-r'        => '[[{{ns:user}}:$1|$1]] دەگەندە ەندى سىن بەرۋشى كۇيى بار.',
	'makereviewer-revoked-r'        => '[[{{ns:user}}:$1|$1]] دەگەندە ەندى سىن بەرۋشى كۇيى جوق.',
	'makereviewer-logpage'          => 'تۇزەتۋشى كۇيى جۋرنالى',
	'makereviewer-logentrygrant-e'  => 'تۇزەتۋشى كۇيىن [[$1]] دەگەنگە بەردى',
	'makereviewer-logentryrevoke-e' => 'تۇزەتۋشى كۇيىن [[$1]] دەگەننەن الاستادى',
	'makereviewer-logentrygrant-r'  => 'سىن بەرۋشى كۇيىن [[$1]] دەگەنگە بەردى',
	'makereviewer-logentryrevoke-r' => 'سىن بەرۋشى كۇيىن [[$1]] دەگەننەن الاستادى',
	'makereviewer-autosum'          => 'اتاق وزدىكتىك بەرىلدى',
	'rights-editor-revoke'          => 'تۇزەتۋشى كۇيىن [[$1]] دەگەننەن الاستادى',
);

/** Kazakh (Cyrillic) (Қазақша (кирил))
 * @author AlefZet
 */
$messages['kk-cyrl'] = array(
	'makereviewer'                  => 'Түзетуші күйін беру не қайтару',
	'makereviewer-header'           => '<strong>Бұл пішінді қатысушыларға мақала растаушы атағын беру үшін әкімшілер мен бітікшілер қолданады.</strong>

Қатысушы құқықтарын тапсыру үшін қатысушы атын жолақта теріңіз де батырманы басыңыз. Қатысушыларға сын беруші күйін бергенде оларға түзетуші күйі де өздіктік беріледі. Түзетуші күйін қайта шақырғанда сын беруші күйі де өздіктік қайта шақырылады.',
	'makereviewer-username'         => 'Қатысушы аты:',
	'makereviewer-search'           => 'Өту',
	'makereviewer-iseditor'         => '[[{{ns:user}}:$1|$1]] дегенде түзетуші күйі бар.',
	'makereviewer-noteditor'        => '[[{{ns:user}}:$1|$1]] дегеннен түзетуші күйі жоқ.',
	'makereviewer-isvalidator'      => '[[{{ns:user}}:$1|$1]] дегенде сын беруші күйі бар.',
	'makereviewer-notvalidator'     => '[[{{ns:user}}:$1|$1]] дегенде сын беруші күйі жоқ.',
	'makereviewer-legend'           => 'Қатысушы құқықтарын өзгерту',
	'makereviewer-change-e'         => 'Түзетуші күйі:',
	'makereviewer-change-r'         => 'Сын беруші күйі:',
	'makereviewer-grant1'           => 'Беру',
	'makereviewer-revoke1'          => 'Қайта шақыру',
	'makereviewer-grant2'           => 'Беру',
	'makereviewer-revoke2'          => 'Қайта шақыру',
	'makereviewer-comment'          => 'Мәндемесі:',
	'makereviewer-granted-e'        => '[[{{ns:user}}:$1|$1]] дегенде енді түзетуші күйі бар.',
	'makereviewer-revoked-e'        => '[[{{ns:user}}:$1|$1]] дегенде енді түзетуші күйі жоқ.',
	'makereviewer-granted-r'        => '[[{{ns:user}}:$1|$1]] дегенде енді сын беруші күйі бар.',
	'makereviewer-revoked-r'        => '[[{{ns:user}}:$1|$1]] дегенде енді сын беруші күйі жоқ.',
	'makereviewer-logpage'          => 'Түзетуші күйі журналы',
	'makereviewer-logentrygrant-e'  => 'түзетуші күйін [[$1]] дегенге берді',
	'makereviewer-logentryrevoke-e' => 'түзетуші күйін [[$1]] дегеннен аластады',
	'makereviewer-logentrygrant-r'  => 'сын беруші күйін [[$1]] дегенге берді',
	'makereviewer-logentryrevoke-r' => 'сын беруші күйін [[$1]] дегеннен аластады',
	'makereviewer-autosum'          => 'атақ өздіктік берілді',
	'rights-editor-revoke'          => 'түзетуші күйін [[$1]] дегеннен аластады',
);

/** Kazakh (Latin) (Qazaqşa (latın))
 * @author AlefZet
 */
$messages['kk-latn'] = array(
	'makereviewer'                  => 'Tüzetwşi küýin berw ne qaýtarw',
	'makereviewer-header'           => '<strong>Bul pişindi qatıswşılarğa maqala rastawşı atağın berw üşin äkimşiler men bitikşiler qoldanadı.</strong>

Qatıswşı quqıqtarın tapsırw üşin qatıswşı atın jolaqta teriñiz de batırmanı basıñız. Qatıswşılarğa sın berwşi küýin bergende olarğa tüzetwşi küýi de özdiktik beriledi. Tüzetwşi küýin qaýta şaqırğanda sın berwşi küýi de özdiktik qaýta şaqırıladı.',
	'makereviewer-username'         => 'Qatıswşı atı:',
	'makereviewer-search'           => 'Ötw',
	'makereviewer-iseditor'         => '[[{{ns:user}}:$1|$1]] degende tüzetwşi küýi bar.',
	'makereviewer-noteditor'        => '[[{{ns:user}}:$1|$1]] degennen tüzetwşi küýi joq.',
	'makereviewer-isvalidator'      => '[[{{ns:user}}:$1|$1]] degende sın berwşi küýi bar.',
	'makereviewer-notvalidator'     => '[[{{ns:user}}:$1|$1]] degende sın berwşi küýi joq.',
	'makereviewer-legend'           => 'Qatıswşı quqıqtarın özgertw',
	'makereviewer-change-e'         => 'Tüzetwşi küýi:',
	'makereviewer-change-r'         => 'Sın berwşi küýi:',
	'makereviewer-grant1'           => 'Berw',
	'makereviewer-revoke1'          => 'Qaýta şaqırw',
	'makereviewer-grant2'           => 'Berw',
	'makereviewer-revoke2'          => 'Qaýta şaqırw',
	'makereviewer-comment'          => 'Mändemesi:',
	'makereviewer-granted-e'        => '[[{{ns:user}}:$1|$1]] degende endi tüzetwşi küýi bar.',
	'makereviewer-revoked-e'        => '[[{{ns:user}}:$1|$1]] degende endi tüzetwşi küýi joq.',
	'makereviewer-granted-r'        => '[[{{ns:user}}:$1|$1]] degende endi sın berwşi küýi bar.',
	'makereviewer-revoked-r'        => '[[{{ns:user}}:$1|$1]] degende endi sın berwşi küýi joq.',
	'makereviewer-logpage'          => 'Tüzetwşi küýi jwrnalı',
	'makereviewer-logentrygrant-e'  => 'tüzetwşi küýin [[$1]] degenge berdi',
	'makereviewer-logentryrevoke-e' => 'tüzetwşi küýin [[$1]] degennen alastadı',
	'makereviewer-logentrygrant-r'  => 'sın berwşi küýin [[$1]] degenge berdi',
	'makereviewer-logentryrevoke-r' => 'sın berwşi küýin [[$1]] degennen alastadı',
	'makereviewer-autosum'          => 'ataq özdiktik berildi',
	'rights-editor-revoke'          => 'tüzetwşi küýin [[$1]] degennen alastadı',

);

/** Khmer (ភាសាខ្មែរ)
 * @author Lovekhmer
 * @author Chhorran
 */
$messages['km'] = array(
	'makereviewer-username' => 'ឈ្មោះអ្នកប្រើ៖',
	'makereviewer-search'   => 'ទៅ',
	'makereviewer-legend'   => 'ផ្លាស់ប្តូរសិទ្ធិនៃអ្នកប្រើប្រាស់',
	'makereviewer-grant1'   => 'ផ្តល់ឱ្យ',
	'makereviewer-revoke1'  => 'ដកហូត',
	'makereviewer-grant2'   => 'ផ្តល់ឱ្យ',
	'makereviewer-revoke2'  => 'ដកហូត',
	'makereviewer-comment'  => 'យោបល់៖',
);

$messages['la'] = array(
	'makereviewer-username'         => 'Nomen usoris:',
	'makereviewer-search'           => 'Ire',
	'makereviewer-iseditor'         => '[[User:$1|$1]] statum recensorem habet.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] non habet statum recensorem.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] statum revisorem habet.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] non habet statum revisorem.',
	'makereviewer-change-e'         => 'Status recensor:',
	'makereviewer-change-r'         => 'Status revisor:',
	'makereviewer-grant1'           => 'Licere',
	'makereviewer-revoke1'          => 'Revocare',
	'makereviewer-grant2'           => 'Licere',
	'makereviewer-revoke2'          => 'Revocare',
	'makereviewer-comment'          => 'Sententia:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] nunc habet statum recensorem.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] non jam habet statum recensorem.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] nunc habet statum revisorem.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] non jam habet statum revisorem.',
	'makereviewer-logentrygrant-e'  => 'licuit statum recensorem pro [[$1]]',
	'makereviewer-logentryrevoke-e' => 'removit statum recensorem usoris [[$1]]',
	'makereviewer-logentrygrant-r'  => 'licuit statum revisorem pro [[$1]]',
	'makereviewer-logentryrevoke-r' => 'removit statum revisorem usoris [[$1]]',
	'rights-editor-grant'           => 'licuit statum recensorem pro [[$1]]',
	'rights-editor-revoke'          => 'removit statum recensorem usoris [[$1]]',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 */
$messages['lb'] = array(
	'makereviewer-username' => 'Benotzernumm:',
	'makereviewer-search'   => 'Status offroen',
	'makereviewer-legend'   => 'Benotzerrechter änneren',
	'makereviewer-comment'  => 'Bemierkung:',
);

/** Limburgish (Limburgs)
 * @author Ooswesthoesbes
 * @author Matthias
 */
$messages['li'] = array(
	'makereviewer'                  => 'Promotie/demotie redacteure',
	'makereviewer-header'           => "<strong>Dit formulier wörd gebroek door beheerders en bureaucrate om gebroekers aan te wieze die pagina's kinne validere.</strong>

Veur de naam van 'ne gebroeker in 't inveurveld in en klik op de knoep om de gebroekersrech in te stelle. 'ne Gebroeker de status reviewer gaeve, maak dae gebroeker automatisch redacteur. 't Intrekke van de status redacteur haajt 't intrekke van de status reviewer in.",
	'makereviewer-username'         => 'Gebroekersnaam:',
	'makereviewer-search'           => 'Gao',
	'makereviewer-iseditor'         => '[[User:$1|$1]] haet redacteursstatus.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] haet gein redacteursstatus.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] haet bekiekersstatus.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] haet gein bekiekersstatus.',
	'makereviewer-legend'           => 'Veranger gebroekersrech:',
	'makereviewer-change-e'         => 'Redacteursstatus:',
	'makereviewer-change-r'         => 'Bekiekersstatus:',
	'makereviewer-grant1'           => 'Gaeve',
	'makereviewer-revoke1'          => 'Innömme',
	'makereviewer-grant2'           => 'Gaeve',
	'makereviewer-revoke2'          => 'Innömme',
	'makereviewer-comment'          => 'Opmèrking:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] haet noe de redacteursstatus.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] haet gein redacteursstaus mieë.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] haet noe de bekiekersstatus.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] heeft neet langer de status reviewer.',
	'makereviewer-logpage'          => 'Logbook status redacteur',
	'makereviewer-logentrygrant-e'  => 'heeft de status redacteur toegekend aan [[$1]]',
	'makereviewer-logentryrevoke-e' => 'heeft de status redacteur ingetrokke veur [[$1]]',
	'makereviewer-logentrygrant-r'  => 'heeft de status reviewer toegekend aan [[$1]]',
	'makereviewer-logentryrevoke-r' => 'heeft de status reviewer ingetrokke veur [[$1]]',
	'makereviewer-autosum'          => 'automatisch gepromoveerd',
	'rights-editor-revoke'          => 'verwijderde redacteurstatus van [[$1]]',
);

/** Lithuanian (Lietuvių)
 * @author Matasg
 */
$messages['lt'] = array(
	'makereviewer-username'  => 'Naudotojo vardas:',
	'makereviewer-search'    => 'Ieškoti',
	'makereviewer-iseditor'  => '[[User:$1|$1]] turi redaktoriaus statusą.',
	'makereviewer-noteditor' => '[[User:$1|$1]] neturi redaktoriaus statuso.',
	'makereviewer-grant1'    => 'Suteikti',
	'makereviewer-revoke1'   => 'Panaikinti',
	'makereviewer-grant2'    => 'Suteikti',
	'makereviewer-revoke2'   => 'Panaikinti',
	'makereviewer-comment'   => 'Komentaras:',
);

/** Marathi (मराठी)
 * @author Kaustubh
 * @author Mahitgar
 */
$messages['mr'] = array(
	'makereviewer'                  => 'संपादकाना पदोन्नती/पदावनती द्या',
	'makereviewer-header'           => '<strong>हा अर्ज प्रबंधक तसेच प्रचालक यांना सदस्यांना पदोन्नती देण्यासाठी वापरला जातो.</strong>

सदस्य अधिकार बदलण्यासाठी सदस्याचे नाव पॄष्ठपेटीमध्ये लिहून त्यापुढील कळीवर टिचकी मारा.
सदस्याला तपासनीसाचा दर्जा दिल्यास आपोआप त्याला संपादकाचे अधिकार मिळतील.
संपादकाचे अधिकार रद्द केल्यास आपोआप तपासनीसाचे अधिकार रद्द होतील.',
	'makereviewer-username'         => 'सदस्याचे नाव:',
	'makereviewer-search'           => 'चला',
	'makereviewer-iseditor'         => '[[User:$1|$1]] ला संपादक अधिकार आहेत.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] ला संपादक अधिकार नाहीत.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] ला तपासनीस अधिकार आह्त.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] ला तपासनीस अधिकार नाहीत.',
	'makereviewer-legend'           => 'सदस्य अधिकार बदला',
	'makereviewer-change-e'         => 'संपादक अधिकार:',
	'makereviewer-change-r'         => 'तपासनीस अधिकार:',
	'makereviewer-grant1'           => 'अधिकार द्या',
	'makereviewer-revoke1'          => 'अधिकार काढून घ्या',
	'makereviewer-grant2'           => 'अधिकार द्या',
	'makereviewer-revoke2'          => 'अधिकार काढून घ्या',
	'makereviewer-comment'          => 'प्रतिक्रीया',
	'makereviewer-granted-e'        => '[[User:$1|$1]] ला आता संपादक अधिकार आहेत.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] ला आता संपादक अधिकार नाहीत.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] ला आता तपासनीस अधिकार आहेत.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] ला आता तपासनीस अधिकार नाहीत.',
	'makereviewer-logpage'          => 'संपादक अधिकार सूची',
	'makereviewer-logentrygrant-e'  => '[[$1]] ला संपादक अधिकार दिले',
	'makereviewer-logentryrevoke-e' => '[[$1]] चे संपादक अधिकार काढून घेतले',
	'makereviewer-logentrygrant-r'  => '[[$1]] ला तपासनीस अधिकार दिले',
	'makereviewer-logentryrevoke-r' => '[[$1]] चे तपासनीस अधिकार काढून घेतले',
	'makereviewer-autosum'          => 'आपोआप पदोन्नती',
	'rights-editor-revoke'          => '[[$1]] चे संपादक अधिकार काढून घेतले',
);

/** Low German (Plattdüütsch)
 * @author Slomox
 */
$messages['nds'] = array(
	'makereviewer-search'  => 'Status affragen',
	'makereviewer-legend'  => 'Brukerrechten ännern',
	'makereviewer-comment' => 'Kommentar:',
);

/** Dutch (Nederlands)
 * @author Siebrand
 */
$messages['nl'] = array(
	'makereviewer'                  => 'Promotie/demotie redacteuren',
	'makereviewer-header'           => "<strong>Dit formulier wordt gebruikt door beheerders en bureaucraten om gebruikers aan te wijzen die pagina's kunnen valideren.</strong>

Voer de naam van een gebruiker in het invoerveld in en klik op de knop om de gebruikersrechten in te stellen. Een gebruiker de status reviewer geven, maakt die gebruiker automatisch redacteur. Het intrekken van de status redacteur houdt het intrekken van de status reviewer in.",
	'makereviewer-username'         => 'Gebruiker:',
	'makereviewer-search'           => 'OK',
	'makereviewer-iseditor'         => '[[User:$1|$1]] heeft de status redacteur.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] heeft niet de status redacteur.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] heeft de status reviewer.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] heeft niet de status reviewer.',
	'makereviewer-legend'           => 'Wijzig gebruikersrechten:',
	'makereviewer-change-e'         => 'Status redacteur:',
	'makereviewer-change-r'         => 'Status reviewer:',
	'makereviewer-grant1'           => 'Toekennen',
	'makereviewer-revoke1'          => 'Intrekken',
	'makereviewer-grant2'           => 'Toekennen',
	'makereviewer-revoke2'          => 'Intrekken',
	'makereviewer-comment'          => 'Opmerking:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] heeft nu de status redacteur.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] heeft niet langer de status redacteur.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] heeft nu de status reviewer.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] heeft niet langer de status reviewer.',
	'makereviewer-logpage'          => 'Logboek status redacteur',
	'makereviewer-logentrygrant-e'  => 'heeft de status redacteur toegekend aan [[$1]]',
	'makereviewer-logentryrevoke-e' => 'heeft de status redacteur ingetrokken voor [[$1]]',
	'makereviewer-logentrygrant-r'  => 'heeft de status reviewer toegekend aan [[$1]]',
	'makereviewer-logentryrevoke-r' => 'heeft de status reviewer ingetrokken voor [[$1]]',
	'makereviewer-autosum'          => 'automatisch gepromoveerd',
	'rights-editor-revoke'          => 'verwijderde redacteurstatus van [[$1]]',
);

/** Norwegian (‪Norsk (bokmål)‬)
 * @author Jon Harald Søby
 */
$messages['no'] = array(
	'makereviewer'                  => 'Forfrem eller degrader bidragsytere',
	'makereviewer-header'           => '<strong>Dette skjemaet brukes av administratorer og byråkrater for å forfremme brukere til artikkelgodkjennere.</strong>

Skriv inn navnet på brukeren i boksen og trykk knappen for å sette brukerrettigheter. Å gi brukere godkjennerstatus vil automatisk gi dem redaktørstatus. Fjerning av redaktørstatus vil automatisk føre til fjerning av godkjennerstatus.',
	'makereviewer-username'         => 'Brukernavn:',
	'makereviewer-search'           => '{{int:Go}}',
	'makereviewer-iseditor'         => '[[User:$1|$1]] har redaktørstatus.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] har ikke redaktørstatus.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] har godkjennerstatus.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] har ikke godkjennerstatus.',
	'makereviewer-legend'           => 'Endre brukerrettigheter:',
	'makereviewer-change-e'         => 'Redaktørstatus:',
	'makereviewer-change-r'         => 'Godkjennerstatus:',
	'makereviewer-grant1'           => 'Gi',
	'makereviewer-revoke1'          => 'Fjern',
	'makereviewer-grant2'           => 'Gi',
	'makereviewer-revoke2'          => 'Fjern',
	'makereviewer-comment'          => 'Kommentar:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] har nå redaktørstatus.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] har ikke lenger redaktørstatus.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] har nå godkjennerstatus.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] har ikke lenger godkjennerstatus.',
	'makereviewer-logpage'          => 'Godkjennerstatuslogg',
	'makereviewer-logentrygrant-e'  => 'ga redaktørstatus til [[User:$1|$1]]',
	'makereviewer-logentryrevoke-e' => 'fjernet redaktørstatus fra [[User:$1|$1]]',
	'makereviewer-logentrygrant-r'  => 'ga godkjennerstatus til [[User:$1|$1]]',
	'makereviewer-logentryrevoke-r' => 'fjernet godkjennerstatus fra [[User:$1|$1]]',
	'makereviewer-autosum'          => 'autoforfremmet',
	'rights-editor-revoke'          => 'fjernet redaktørstatus fra [[$1]]',
);

/** Northern Sotho (Sesotho sa Leboa)
 * @author Mohau
 */
$messages['nso'] = array(
	'makereviewer-username' => 'Leina la mošomiši:',
	'makereviewer-search'   => 'Sepela',
);

/** Occitan (Occitan)
 * @author Cedric31
 * @author ChrisPtDe
 */
$messages['oc'] = array(
	'makereviewer'                  => 'Promòure/Demetre los editors',
	'makereviewer-header'           => "'''Aqueste formulari es utilizat per los administrators e los burocratas per promòure los contributors al pòst de revisor d'articles.''' Picar lo nom del contributor dins la boita de dialòg per li balhar aquestes dreches. Balhar los dreches de revisor balha automaticament los dreches d'editor. Revocar los dreches de revisor revòca automaticament los dreches d'editor.",
	'makereviewer-username'         => "Nom de l'utilizaire:",
	'makereviewer-search'           => 'Anar',
	'makereviewer-iseditor'         => "[[User:$1|$1]] a los dreches d'editor.",
	'makereviewer-noteditor'        => "[[User:$1|$1]] a pas los dreches d'editor.",
	'makereviewer-isvalidator'      => '[[User:$1|$1]] a los dreches de revisor.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] a pas los dreches de revisor.',
	'makereviewer-legend'           => "Cambiar los dreches d'utilizaire:",
	'makereviewer-change-e'         => "Dreches de l'editor :",
	'makereviewer-change-r'         => 'Dreches del revisor :',
	'makereviewer-grant1'           => 'Acordar',
	'makereviewer-revoke1'          => 'Revocar',
	'makereviewer-grant2'           => 'Acordar',
	'makereviewer-revoke2'          => 'Revocar',
	'makereviewer-comment'          => 'Comentari :',
	'makereviewer-granted-e'        => "[[User:$1|$1]] a los dreches d'editor.",
	'makereviewer-revoked-e'        => "[[User:$1|$1]] a pas mai los dreches d'editor.",
	'makereviewer-granted-r'        => '[[User:$1|$1]] a los dreches de revisor.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] a pas mai los dreches de revisor.',
	'makereviewer-logpage'          => "Jornal dels dreches de l'editor",
	'makereviewer-logentrygrant-e'  => "a acordat los dreches d'editor a [[$1]]",
	'makereviewer-logentryrevoke-e' => "a revocat los dreches d'editor de [[$1]]",
	'makereviewer-logentrygrant-r'  => 'a acordat los dreches de revisor a [[$1]]',
	'makereviewer-logentryrevoke-r' => 'a revocat los dreches de revisor de [[$1]]',
	'makereviewer-autosum'          => 'Autopromolgut',
	'rights-editor-revoke'          => "a revocat los dreches d'editor de [[$1]]",
);

/** Polish (Polski)
 * @author Masti
 */
$messages['pl'] = array(
	'makereviewer-username'  => 'Nazwa użytkownika:',
	'makereviewer-search'    => 'Pokaż',
	'makereviewer-iseditor'  => '[[User:$1|$1]] ma status edytora.',
	'makereviewer-noteditor' => '[[User:$1|$1]] nie ma statusu edytora.',
	'makereviewer-legend'    => 'Zmień uprawnienia użytkownika',
	'makereviewer-grant1'    => 'Przyznaj',
	'makereviewer-revoke1'   => 'Odbierz',
	'makereviewer-grant2'    => 'Przyznaj',
	'makereviewer-revoke2'   => 'Odbierz',
	'makereviewer-comment'   => 'Komentarz:',
);

/* Piedmontese (Bèrto 'd Sèra) */
$messages['pms'] = array(
	'makereviewer'                  => 'Promeuv/dësbassa ij redator',
	'makereviewer-header'           => '<strong>Sta pàgina-sì a la dòvro aministrator e mangiapapé për buteje a j\'utent la qualìfica da convalidator dj\'artìcoj.</strong>

Ch\'a scriva lë stranòm dl\'utent ant ël camp e peuj ch\'a-i bata dzora al boton për travajr ant sla qualìfica dl\'utent. Ën butand-je la qualìfica da revisor a n\'utent a-j da n\'aotomàtica \'cò cola da redator. Ën gavand-je cola da redator a-j gava via n\'aotomàtich \'cò cola da revisor.',
	'makereviewer-username'         => 'Stranòm:',
	'makereviewer-search'           => 'Va',
	'makereviewer-iseditor'         => '[[User:$1|$1]] a l\'ha la qualìfica da redator.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] a l\'ha pa la qualìfica da redator.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] a l\'ha la qualìfica da revisor.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] a l\'ha pa la qualìfica da revisor.',
	'makereviewer-legend'           => 'Cambieje sò drit a n\'utent:',
	'makereviewer-change-e'         => 'Qualìfica ëd redator:',
	'makereviewer-change-r'         => 'Qualìfica ëd revisor:',
	'makereviewer-grant1'           => 'Buta',
	'makereviewer-revoke1'          => 'Gava',
	'makereviewer-grant2'           => 'Buta',
	'makereviewer-revoke2'          => 'Gava',
	'makereviewer-comment'          => 'Coment:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] adess a l\'ha la qualìfica da redator.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] a l\'ha pì nen la qualìfica da redator.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] adess a l\'ha la qualìfica da revisor.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] a l\'ha pì nen la qualìfica da revisor.',
	'makereviewer-logpage'          => 'Registr dle qualìfiche da editor',
	'makereviewer-logpagetext'      => 'Sòn a l\'é un registr dle modìfiche a le qualìfiche ch\'a toco la [[{{MediaWiki:Makevalidate-page}}|convàlida dj\'artìcoj]].',
	'makereviewer-logentrygrant-e'  => 'Butaje la qualìfica da redator a [[$1]]',
	'makereviewer-logentryrevoke-e' => 'Gavaje la qualìfica da redator a [[$1]]',
	'makereviewer-logentrygrant-r'  => 'Butaje la qualìfica da revisor a [[$1]]',
	'makereviewer-logentryrevoke-r' => 'Gavaje la qualìfica da revisor a [[$1]]',
	'makereviewer-autosum'          => 'promossion aotomàtica',
	'rights-editor-grant'           => 'Daje la qualìfica da revisor a [[$1]]',
	'rights-editor-revoke'          => 'gava-je la qualìfica ëd redator a [[$1]]',
);

/** Pashto (پښتو)
 * @author Ahmed-Najib-Biabani-Ibrahimkhel
 */
$messages['ps'] = array(
	'makereviewer-username' => 'د کارونکي نوم:',
	'makereviewer-search'   => 'ورځه',
);

/* Portuguese (Lugusto) */
$messages['pt'] = array(
	'makereviewer'                  => 'Promover/rebaixar editores',
	'makereviewer-header'           => '<strong>Este é um formulário utilizado por {{int:group-sysop}} e {{int:group-bureaucrat}} para promover usuários a validadores de páginas.</strong>

Digite o nome de usuário no espaço indicado e clique no botão correspondente a alteração de privilégios desejada de ser feita. Conceder o estado de {{int:group-reviewer-member}} fará com que a pessoa se torne {{int:group-editor-member}} automaticamente. Revogar o status de {{int:group-editor-member}} automaticamente revogará também o estado de {{int:group-reviewer-member}}.',
	'makereviewer-username'         => 'Nome de usuário:',
	'makereviewer-search'           => 'Ir',
	'makereviewer-iseditor'         => '[[{{ns:user}}:$1|$1]] possui privilégios de {{int:group-editor-member}}.',
	'makereviewer-noteditor'        => '[[{{ns:user}}:$1|$1]] não possui privilégios de {{int:group-editor-member}}.',
	'makereviewer-isvalidator'      => '[[{{ns:user}}:$1|$1]] possui privilégios de {{int:group-reviewer-member}}.',
	'makereviewer-notvalidator'     => '[[{{ns:user}}:$1|$1]] não possui privilégios de {{int:group-reviewer-member}}.',
	'makereviewer-legend'           => 'Alterar privilégios de usuário:',
	'makereviewer-change-e'         => 'Privilégios de {{int:group-editor-member}}:',
	'makereviewer-change-r'         => 'Privilégios de {{int:group-reviewer-member}}:',
	'makereviewer-grant1'           => 'Conceder',
	'makereviewer-revoke1'          => 'Revocar',
	'makereviewer-grant2'           => 'Conceder',
	'makereviewer-revoke2'          => 'Revocar',
	'makereviewer-comment'          => 'Comentário:',
	'makereviewer-granted-e'        => '[[{{ns:user}}:$1|$1]] agora possui privilégios de {{int:group-editor-member}}.',
	'makereviewer-revoked-e'        => '[[{{ns:user}}:$1|$1]] não mais possui privilégios de {{int:group-editor-member}}.',
	'makereviewer-granted-r'        => '[[{{ns:user}}:$1|$1]] agora possui privilégios de {{int:group-reviewer-member}}.',
	'makereviewer-revoked-r'        => '[[{{ns:user}}:$1|$1]] não mais possui privilégios de {{int:group-reviewer-member}}.',
	'makereviewer-logpage'          => 'Registo de privilégios de editores',
	'makereviewer-logpagetext'      => 'Este é um registo de alterações de privilégios de [[{{MediaWiki:Makevalidate-page}}|validadores de páginas]].',
	'makereviewer-logentrygrant-e'  => 'concedidos privilégios de {{int:group-editor-member}} para [[$1]]',
	'makereviewer-logentryrevoke-e' => 'removidos privilégios de {{int:group-editor-member}} de [[$1]]',
	'makereviewer-logentrygrant-r'  => 'concedidos privilégios de {{int:group-reviewer-member}} para [[$1]]',
	'makereviewer-logentryrevoke-r' => 'removidos privilégios de {{int:group-reviewer-member}} para [[$1]]',
	'makereviewer-autosum'          => 'promovido automaticamente',
	'rights-editor-grant'           => 'concedidos privilégios de {{int:group-editor-member}} para [[$1]]',
	'rights-editor-revoke'          => 'removidos privilégios de {{int:group-editor-member}} para [[$1]]',
);

/** Russian (Русский)
 * @author .:Ajvol:.
 */
$messages['ru'] = array(
	'makereviewer'                  => 'Повышение/понижение статуса редакторов',
	'makereviewer-header'           => '<strong>Эта форма используется администраторами и бюрократами чтобы повысить статус участника до уровня рецензента.</strong>

Наберите имя участника и и нажмите кнопку, чтобы установить права. Присвоение участнику статуса рецензента автоматически влечёт за собой присвоение ему статуса редактора. Отзыв статуса редактора приведёт к автоматическому отзыву статуса рецензента.',
	'makereviewer-username'         => 'Имя участника:',
	'makereviewer-search'           => 'Найти',
	'makereviewer-iseditor'         => '[[User:$1|$1]] имеет статус редактора.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] не имеет статуса редактора.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] имеет статус рецензента.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] не имеет статуса рецензента.',
	'makereviewer-legend'           => 'Изменение прав участника',
	'makereviewer-change-e'         => 'Статус редактора:',
	'makereviewer-change-r'         => 'Статус рецензента:',
	'makereviewer-grant1'           => 'Присвоить',
	'makereviewer-revoke1'          => 'Отозвать',
	'makereviewer-grant2'           => 'Присвоить',
	'makereviewer-revoke2'          => 'Отозвать',
	'makereviewer-comment'          => 'Примечание:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] теперь имеет статус редактора.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] больше не имеет статуса редактора.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] теперь имеет статус рецензента.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] больше не имеет статуса рецензента.',
	'makereviewer-logpage'          => 'Журнал статусов редакторов',
	'makereviewer-logentrygrant-e'  => 'присвоил статус редактора участнику [[$1]]',
	'makereviewer-logentryrevoke-e' => 'отозвал статус редактора у участника [[$1]]',
	'makereviewer-logentrygrant-r'  => 'присвоил статус рецензента участнику [[$1]]',
	'makereviewer-logentryrevoke-r' => 'отозвал статус рецензента у участника [[$1]]',
	'makereviewer-autosum'          => 'автоназначение',
	'rights-editor-revoke'          => 'снят статус редактора с [[$1]]',
);

/** Yakut (Саха тыла)
 * @author HalanTul
 */
$messages['sah'] = array(
	'makereviewer'                  => 'Эрэдээктэрдэри үрдээтии/намтатыы',
	'makereviewer-header'           => '<strong>Манна бюрокрааттар уонна админнар кыттааччы таһымын ырытааччы таһымыгар дылы үрдэтэллэр.</strong>

Уларытарга кыттааччы аатын киллэр уонна тимэҕи баттаа. Ырытааччы таһыма эрэдээктэр быраабын аптамаатынан биэрэр. Киһи эрэдээктэриттэн ууратылыннаҕына ырытааччы буолан эмиэ бүтэр.',
	'makereviewer-username'         => 'Кыттааччы аата:',
	'makereviewer-search'           => 'Бул',
	'makereviewer-iseditor'         => '[[User:$1|$1]] эрэдээктэр статустаах.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] эрэдээктэр статуһа суох.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] ырытааччы (рецензент) статустаах.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] ырытааччы статуһа суох.',
	'makereviewer-legend'           => 'Кыттааччы быраабын уларытыы',
	'makereviewer-change-e'         => 'Эрэдээктэр статуһа:',
	'makereviewer-change-r'         => 'Ырытааччы статуһа:',
	'makereviewer-grant1'           => 'Аныырга',
	'makereviewer-revoke1'          => 'Устарга',
	'makereviewer-grant2'           => 'Аныырга',
	'makereviewer-revoke2'          => 'Устарга',
	'makereviewer-comment'          => 'Быһаарыы:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] мантан ыла эрэдээктэр статустаах.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] мантан ыла эрэдээктэр буолбатах.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] мантан ыла ырытааччы буолла.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] мантан ыла ырытааччы буолбатах.',
	'makereviewer-logpage'          => 'Эрэдээктэрдэр статустарын сурунаала',
	'makereviewer-logentrygrant-e'  => 'эрдээктэр статуһун [[$1]] кыттааччыга иҥэрдэ',
	'makereviewer-logentryrevoke-e' => 'эрэдээктэр статуһуттан [[$1]] босхолонно',
	'makereviewer-logentrygrant-r'  => 'ырытааччы статуһа [[$1]] кыттааччыга иҥэрилиннэ',
	'makereviewer-logentryrevoke-r' => 'ырытааччы статуһуттан [[$1]] босхолонно',
	'makereviewer-autosum'          => 'аптамаатынан анааһын',
	'rights-editor-revoke'          => 'эрэдээктэр статуһуттан бу кэмтэн босхоломмут: [[$1]]',
);

// Slovak (Helix84)
$messages['sk'] = array(
	'makereviewer'                  => 'Povýšiť/degradovať používateľov',
	'makereviewer-header'           => '<strong>Tento formulár používajú správcovia a byrokrati pre povýšenie používateľov na overovateľov článkov.</strong>

Napíšte meno používateľa do poľa a stlačte tlačidlo. Tým sa nastavia používateľovu práva. Udelenie používateľovi status kontrolóra in automaticky zabezpečí status redaktora. Odmietnutie statusu redaktora automaticky zamietne status kontrolóra.',
	'makereviewer-username'         => 'Meno používateľa:',
	'makereviewer-search'           => 'Choď',
	'makereviewer-iseditor'         => '[[User:$1|$1]] má status redaktora.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] nemá status redaktora.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] má status kontrolóra.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] nemá status kontrolóra.',
	'makereviewer-legend'           => 'Zmeniť práva používateľa:',
	'makereviewer-change-e'         => 'Status redaktora:',
	'makereviewer-change-r'         => 'Status kontrolóra:',
	'makereviewer-grant1'           => 'Udeliť',
	'makereviewer-revoke1'          => 'Odobrať',
	'makereviewer-grant2'           => 'Udeliť',
	'makereviewer-revoke2'          => 'Odobrať',
	'makereviewer-comment'          => 'Komentár:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] má teraz status redaktora.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] odteraz nemá status redaktora.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] má teraz status kontrolóra.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] odteraz nemá status kontrolóra.',
	'makereviewer-logpage'          => 'Záznam stavu redaktorov',
	'makereviewer-logpagetext'      => 'Toto je záznam zmien stausu používateľov pre [[{{MediaWiki:Makevalidate-page}}|kontrolu článkov]].',
	'makereviewer-logentrygrant-e'  => '[[User:$1|$1]] odteraz má status redaktor.',
	'makereviewer-logentryrevoke-e' => '[[User:$1|$1]] odteraz nemá status redaktor.',
	'makereviewer-logentrygrant-r'  => '[[User:$1|$1]] odteraz má status kontrolór.',
	'makereviewer-logentryrevoke-r' => '[[User:$1|$1]] odteraz nemá status kontrolór.',
	'makereviewer-autosum'          => 'samopovýšenie',
	'rights-editor-grant'           => '[[User:$1|$1]] odteraz má status redaktor.',
	'rights-editor-revoke'          => '[[User:$1|$1]] odteraz nemá status redaktor.',
);

$messages['ss'] = array(
	'makereviewer-search'           => 'Kúhámba',
);

/** Seeltersk (Seeltersk)
 * @author Pyt
 */
$messages['stq'] = array(
	'makereviewer'                  => 'Sieuwer-/Wröig-Gjucht reeke/äntluuke',
	'makereviewer-header'           => '<strong>Mäd dit Formular konnen Administratore un Bürokrate Benutsere dät Gjucht tou Artikkelpröiwenge reeke.</strong>

Reek dän Benutsernoome in dät Fäild ien un klik ap ju Schaltfläche, uum dät Gjucht tou sätten. Truch Reeken fon dät Wröiggjucht wäd automatisk uk dät Sieuwer-Gjucht roat. Dät Äntluuken fon dät Sieuwer-Gjucht häd automatisk dät Äntluuken fon dät Wröiggjucht as Foulge.',
	'makereviewer-username'         => 'Benutsernoome:',
	'makereviewer-search'           => 'Stoatus oufräigje',
	'makereviewer-iseditor'         => '[[User:$1|$1]] häd dät Sieuwer-Gjucht.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] häd neen Sieuwer-Gjucht.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] häd dät Wröiggjucht.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] häd neen Wröiggjucht.',
	'makereviewer-legend'           => 'Benutsergjuchte annerje',
	'makereviewer-change-e'         => 'Sieuwer-Gjucht:',
	'makereviewer-change-r'         => 'Wröiggjucht:',
	'makereviewer-grant1'           => 'Reeke',
	'makereviewer-revoke1'          => 'Äntluuke',
	'makereviewer-grant2'           => 'Reeke',
	'makereviewer-revoke2'          => 'Äntluuke',
	'makereviewer-comment'          => 'Kommentoar:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] häd nu dät Sieuwer-Gjucht.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] wuude dät Sieuwer-Gjucht äntleeken.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] häd nu dät Wröiggjucht.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] wuude dät Wröiggjucht äntleeken.',
	'makereviewer-logpage'          => 'Sieuwer-/Wröig-Gjuchte-Logbouk',
	'makereviewer-logentrygrant-e'  => 'roate dät Sieuwer-Gjucht an [[$1]]',
	'makereviewer-logentryrevoke-e' => 'äntlook dät Sieuwer-Gjucht fon [[$1]]',
	'makereviewer-logentrygrant-r'  => 'roate dät Wröiggjucht an [[$1]]',
	'makereviewer-logentryrevoke-r' => 'äntlook dät Wröiggjucht fon [[$1]]',
	'makereviewer-autosum'          => 'automatiske Gjuchte-uutgoawe',
	'rights-editor-grant'           => 'roate dät Sieuwer-Gjucht an [[$1]]',
	'rights-editor-revoke'          => 'äntlook dät Sieuwer-Gjucht fon [[$1]]',
);

/** Swedish (Svenska)
 * @author Lejonel
 * @author M.M.S.
 */
$messages['sv'] = array(
	'makereviewer'                  => 'Befordra/degradera redaktörer',
	'makereviewer-header'           => '<strong>Det här formuläret används av administratörer och byråkrater för att befordra användare till sidgranskare</strong>

Skriv användarens namn i rutan och tryck på knappen för att ändra användarens rättigheter.
När användare ges granskarstatus så får de automatiskt också redaktörstatus. Om redaktörstatus tas ifrån användare, så tas samtidigt automatiskt deras granskarstatus ifrån dem.',
	'makereviewer-username'         => 'Användarnamn:',
	'makereviewer-search'           => 'Gå till',
	'makereviewer-iseditor'         => '[[User:$1|$1]] har redaktörsbehörighet.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] har inte redaktörsbehörighet.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] har granskarbehörighet.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] har inte granskarbehörighet.',
	'makereviewer-legend'           => 'Ändra användarbehörigheter',
	'makereviewer-change-e'         => 'Redaktörsbehörighet:',
	'makereviewer-change-r'         => 'Granskarbehörighet:',
	'makereviewer-grant1'           => 'Ge',
	'makereviewer-revoke1'          => 'Ta bort',
	'makereviewer-grant2'           => 'Ge',
	'makereviewer-revoke2'          => 'Ta bort',
	'makereviewer-comment'          => 'Kommentar:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] har nu redaktörsbehörighet.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] har inte längre redaktörsbehörighet.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] har nu granskarbehörighet.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] har inte längre granskarbehörighet.',
	'makereviewer-logpage'          => 'Redaktörstatuslogg',
	'makereviewer-logentrygrant-e'  => 'gav [[$1]] redaktörstatus',
	'makereviewer-logentryrevoke-e' => 'tog ifrån [[$1]] redaktörstatus',
	'makereviewer-logentrygrant-r'  => 'gav [[$1]] granskarstatus',
	'makereviewer-logentryrevoke-r' => 'tog ifrån [[$1]] granskarstatus',
	'makereviewer-autosum'          => 'autobefodring',
	'rights-editor-revoke'          => 'tog ifrån [[$1]] redaktörstatus',
);

/** Telugu (తెలుగు)
 * @author Veeven
 * @author వైజాసత్య
 * @author Chaduvari
 */
$messages['te'] = array(
	'makereviewer'                  => 'రచయితలను పదోన్నతి/నిమ్నత చెయ్యండి',
	'makereviewer-username'         => 'వాడుకరి పేరు:',
	'makereviewer-search'           => 'వెళ్ళు',
	'makereviewer-iseditor'         => '[[User:$1|$1]]కి ఎడిటర్ హోదా ఉంది.',
	'makereviewer-noteditor'        => '[[User:$1|$1]]కి ఎడిటర్ హోదా లేదు.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]]కి సమీక్షకుల హోదా ఉంది.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]]కి సమీక్షకుల హోదా లేదు.',
	'makereviewer-legend'           => 'వాడుకరి హక్కులను మార్చండి',
	'makereviewer-change-e'         => 'ఎడిటర్ హోదా:',
	'makereviewer-change-r'         => 'సమీక్షకుని హోదా:',
	'makereviewer-grant1'           => 'ఇవ్వు',
	'makereviewer-revoke1'          => 'వెనక్కి తీసుకో',
	'makereviewer-grant2'           => 'ఇవ్వు',
	'makereviewer-revoke2'          => 'వెనక్కి తీసుకో',
	'makereviewer-comment'          => 'వ్యాఖ్య:',
	'makereviewer-granted-e'        => '[[User:$1|$1]]కి ఇప్పుడు ఎడిటర్ హోదా ఉంది.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]]కి ఇక ఎడిటర్ హోదా లేదు.',
	'makereviewer-granted-r'        => '[[User:$1|$1]]కి ఇప్పుడు సమీక్షకుల హోదా ఉంది.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]]కి ఇక సమీక్షకులు హోదా లేదు.',
	'makereviewer-logpage'          => 'సంపాదకుల స్థాయి లాగ్',
	'makereviewer-logentrygrant-e'  => '[[$1]]కి ఎడిటర్ హోదా ఇచ్చారు',
	'makereviewer-logentryrevoke-e' => '[[$1]] నుండి ఎడిటర్ హోదా తొలగించారు',
	'makereviewer-logentrygrant-r'  => '[[$1]]కి సమీక్షకుల హోదా ఇచ్చారు',
	'makereviewer-logentryrevoke-r' => '[[$1]] నుండి సమీక్షకుల హోదా తొలగించారు',
	'makereviewer-autosum'          => 'ఆటోమాటిగ్గా పదోన్నతి చెయ్యబడ్డారు',
	'rights-editor-revoke'          => '[[$1]] నుండి ఎడిటర్ హోదా తొలగించారు',
);

/** Tajik (Тоҷикӣ)
 * @author Ibrahim
 */
$messages['tg'] = array(
	'makereviewer'                  => 'Таъриф/таназул додани вироишгарон',
	'makereviewer-header'           => '<strong>Ин форм аз тарафи мудирон ва девонсолорон таъриф додани корбарон ба дараҷаи бозбиникунандаҳои мақола, истифода бурда мешавад.</strong>

Номи корбари мавриди назарро дар ҷаъба ворид куне ва тугмаро фишор диҳед то ихтиёроти корбарро бубинед. Додани ихтиёроти бозбинӣ ба як корбар худ ба худ ба онҳо ихтиёроти виросториро ҳам медиҳад. Пас гирифтани инхтиёроти виростори аз як корбар ҳам ихтиёроти бозбиниро аз ӯ ба таври худкор мегирад.',
	'makereviewer-username'         => 'Номи корбар:',
	'makereviewer-search'           => 'Бирав',
	'makereviewer-iseditor'         => '[[User:$1|$1]] дорои ихтиёроти виросторӣ аст.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] дорои ихтиёроти виросторӣ нест.',
	'makereviewer-isvalidator'      => '[[User:$1|$1]] дорои ихтиёроти бозбин аст.',
	'makereviewer-notvalidator'     => '[[User:$1|$1]] дорои ихтиёроти бозбин нест.',
	'makereviewer-legend'           => 'Тағйири ихтиёроти корбар',
	'makereviewer-change-e'         => 'Вазъи виростор:',
	'makereviewer-change-r'         => 'Вазъи бозбин:',
	'makereviewer-grant1'           => 'Ато',
	'makereviewer-revoke1'          => 'Бозпасгирӣ',
	'makereviewer-grant2'           => 'Ато',
	'makereviewer-revoke2'          => 'Бозпасгирӣ',
	'makereviewer-comment'          => 'Тавзеҳ:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] акнун дорои ихтиёроти виросторӣ аст.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] дигар дорои ихтиёроти виросторӣ нест.',
	'makereviewer-granted-r'        => '[[User:$1|$1]] акнун дорои ихтиёроти бозбин аст.',
	'makereviewer-revoked-r'        => '[[User:$1|$1]] дигар дорои ихтиёроти бозбин нест.',
	'makereviewer-logpage'          => 'Гузориши вазъи виростор',
	'makereviewer-logentrygrant-e'  => 'ба [[$1]] ихтиёроти виросторӣ дода шуд',
	'makereviewer-logentryrevoke-e' => 'аз [[$1]] ихтиёроти виросторӣ гирифта шуд',
	'makereviewer-logentrygrant-r'  => 'ба [[$1]] ихтиёроти бозбин дода шуд',
	'makereviewer-logentryrevoke-r' => 'аз [[$1]] ихтиёроти бозбин гирифта шуд',
	'makereviewer-autosum'          => 'Ба таври худкор пешбарӣ шудан',
	'rights-editor-revoke'          => 'Ихтиёроти виростор аз [[$1]] гирифта шуд',
);

/** Turkish (Türkçe)
 * @author Erkan Yilmaz
 * @author Karduelis
 */
$messages['tr'] = array(
	'makereviewer-username' => 'Kullanıcının ismi:',
	'makereviewer-search'   => 'Git',
	'makereviewer-comment'  => 'Açıklama:',
);

/** Vietnamese (Tiếng Việt)
 * @author Minh Nguyen
 */
$messages['vi'] = array(
	'makereviewer-username' => 'Tên thành viên:',
	'makereviewer-search'   => 'Hiển thị',
	'makereviewer-grant1'   => 'Phong cờ',
	'makereviewer-revoke1'  => 'Rút cờ',
	'makereviewer-grant2'   => 'Phong cờ',
	'makereviewer-revoke2'  => 'Rút cờ',
	'makereviewer-comment'  => 'Lý do:',
	'makereviewer-autosum'  => 'tự phong cờ',
);

/** Volapük (Volapük)
 * @author Smeira
 * @author Malafaya
 */
$messages['vo'] = array(
	'makereviewer-username'         => 'Nem gebana:',
	'makereviewer-search'           => 'Getolöd',
	'makereviewer-iseditor'         => '[[User:$1|$1]] labon stadi redakana.',
	'makereviewer-noteditor'        => '[[User:$1|$1]] no labon redakanastadi.',
	'makereviewer-isvalidator'      => 'Geban: [[User:$1|$1]] labon krütanastadi.',
	'makereviewer-notvalidator'     => 'Geban: [[User:$1|$1]] no labon krütanastadi.',
	'makereviewer-legend'           => 'Votükön gebanagitätis',
	'makereviewer-change-e'         => 'Redakanastad:',
	'makereviewer-change-r'         => 'Krütanastad:',
	'makereviewer-grant1'           => 'Gevön',
	'makereviewer-revoke1'          => 'Moükön',
	'makereviewer-grant2'           => 'Gevön',
	'makereviewer-revoke2'          => 'Moükön',
	'makereviewer-comment'          => 'Küpet:',
	'makereviewer-granted-e'        => '[[User:$1|$1]] nu labon redakanastadi.',
	'makereviewer-revoked-e'        => '[[User:$1|$1]] no plu labon redakanastadi.',
	'makereviewer-granted-r'        => 'Geban: [[User:$1|$1]] labon anu krütanastadi.',
	'makereviewer-revoked-r'        => 'Geban: [[User:$1|$1]] no plu labon krütanastadi.',
	'makereviewer-logpage'          => 'Jenotalised redakanastada',
	'makereviewer-logentrygrant-e'  => 'egevon redakanastadi gebane: [[$1]]',
	'makereviewer-logentryrevoke-e' => 'moükön redakanastadi de geban: [[$1]]',
	'makereviewer-logentrygrant-r'  => 'egevon krütanastadi gebane: [[$1]]',
	'makereviewer-logentryrevoke-r' => 'emoükon krütanastadi gebana: [[$1]]',
	'rights-editor-revoke'          => 'emoükon redakanastadi gebana: [[$1]]',
);

