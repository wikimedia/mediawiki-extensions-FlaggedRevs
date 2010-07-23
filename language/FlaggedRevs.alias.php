<?php
/**
 * Aliases for special pages for extension FlaggedRevs
 *
 * @file
 * @ingroup Extensions
 */

$aliases = array();

/** English
 * @author Aaron Schulz
 */
$aliases['en'] = array(
	'PendingChanges' => array( 'PendingChanges', 'OldReviewedPages' /* deprecated */ ),
	'ProblemChanges' => array( 'ProblemChanges' ),
	'QualityOversight' => array( 'AdvancedReviewLog', 'QualityOversight' /* deprecated */ ),
	'ReviewedPages' => array( 'ReviewedPages' ),
	'RevisionReview' => array( 'RevisionReview' ),
	'Stabilization' => array( 'Stabilization', 'Stabilisation' ),
	'StablePages' => array( 'StablePages' ),
    'ConfiguredPages' => array( 'ConfiguredPages' ),
	'ReviewedVersions' => array( 'ReviewedVersions', 'StableVersions' /*old name*/ ),
	'UnreviewedPages' => array( 'UnreviewedPages' ),
	'ValidationStatistics' => array( 'ValidationStatistics' ),
);

/** Aragonese (Aragonés) */
$aliases['an'] = array(
	'PendingChanges' => array( 'PachinasSupervisatasAntigas' ),
	'QualityOversight' => array( 'SupervisataDeCalidat' ),
	'ReviewedPages' => array( 'PachinasSubervisatas' ),
	'StablePages' => array( 'PachinasEstables' ),
	'UnreviewedPages' => array( 'PachinasNoRevisatas' ),
);

/** Arabic (العربية) */
$aliases['ar'] = array(
	'PendingChanges'       => array( 'صفحات_مراجعة_قديمة' ),
	'ProblemChanges'         => array( 'تغييرات_المشاكل' ),
	'QualityOversight'       => array( 'سجل_المراجعة_المتقدم', 'نظر_الجودة' ),
	'ReviewedPages'          => array( 'صفحات_مراجعة' ),
	'RevisionReview'         => array( 'مراجعة_نسخة' ),
	'Stabilization'          => array( 'استقرار' ),
	'StablePages'            => array( 'صفحات_مستقرة' ),
	'ConfiguredPages'        => array( 'صفحات_مضبوطة' ),
	'ReviewedVersions'       => array( 'نسخ_مراجعة', 'نسخ_مستقرة' ),
	'UnreviewedPages'        => array( 'صفحات_غير_مراجعة' ),
	'ValidationStatistics'   => array( 'إحصاءات_التحقق' ),
);

/** Egyptian Spoken Arabic (مصرى) */
$aliases['arz'] = array(
	'PendingChanges' => array( 'صفح_مراجعه_قديمه' ),
	'ProblemChanges' => array( 'تغييرات_المشاكل' ),
	'QualityOversight' => array( 'مراقبة_الجوده' ),
	'ReviewedPages' => array( 'صفح_مراجعه' ),
	'RevisionReview' => array( 'مراجعة_نسخه' ),
	'Stabilization' => array( 'استقرار' ),
	'StablePages' => array( 'صفح_مستقر' ),
	'ReviewedVersions' => array( 'نسخ_مراجعه', 'نسخ_مستقره' ),
	'UnreviewedPages' => array( 'صفح_مش_متراجعه' ),
	'ValidationStatistics' => array( 'احصائيات_الصلاحيه' ),
);

/** Southern Balochi (بلوچی مکرانی) */
$aliases['bcc'] = array(
	'PendingChanges' => array( 'صفحات-بازبینی-قدیمی' ),
	'QualityOversight' => array( 'رویت-کیفیت' ),
	'ReviewedPages' => array( 'صفحات-بازبینی' ),
	'StablePages' => array( 'صفحات-ثابت' ),
	'UnreviewedPages' => array( 'صفحات-بی-بازبینی' ),
);

/** Breton (Brezhoneg) */
$aliases['br'] = array(
	'StablePages' => array( 'PajennoùStabil' ),
);

/** Bosnian (Bosanski) */
$aliases['bs'] = array(
	'PendingChanges' => array( 'StarePregledaneStranice' ),
	'QualityOversight' => array( 'KvalitetNadzora' ),
	'ReviewedPages' => array( 'PregledaneStranice' ),
	'RevisionReview' => array( 'PregledRevizija' ),
	'Stabilization' => array( 'Stabilizacija' ),
	'StablePages' => array( 'StabilneStranice' ),
	'UnreviewedPages' => array( 'NeprovjereneStranice' ),
	'ValidationStatistics' => array( 'StatistikeValidacije' ),
);

/** German (Deutsch) */
$aliases['de'] = array(
	'PendingChanges' => array( 'Seiten mit ungesichteten Versionen' ),
	'QualityOversight' => array( 'Markierungsübersicht' ),
	'ReviewedPages' => array( 'Gesichtete Seiten' ),
	'RevisionReview' => array( 'Versionsprüfung' ),
	'Stabilization' => array( 'Seitenkonfiguration', 'Stabilisierung' ),
	'StablePages' => array( 'Konfigurierte Seiten' ),
	'UnreviewedPages' => array( 'Ungesichtete Seiten' ),
	'ValidationStatistics' => array( 'Markierungsstatistik' ),
);

/** Lower Sorbian (Dolnoserbski) */
$aliases['dsb'] = array(
	'PendingChanges' => array( 'Zasej njepśeglědane boki' ),
	'QualityOversight' => array( 'Kwalitna kontrola' ),
	'ReviewedPages' => array( 'Pśeglědane boki' ),
	'RevisionReview' => array( 'Wersijowe pśeglědanje' ),
	'Stabilization' => array( 'Stabilizacija' ),
	'StablePages' => array( 'Stabilne boki' ),
	'UnreviewedPages' => array( 'Njepśeglědane boki' ),
	'ValidationStatistics' => array( 'Statistika pśeglědanjow' ),
);

/** Esperanto (Esperanto) */
$aliases['eo'] = array(
	'PendingChanges' => array( 'Malfreŝe kontrolitaj paĝoj' ),
	'QualityOversight' => array( 'Kvalita kontrolo' ),
	'ReviewedPages' => array( 'Kontrolitaj paĝoj' ),
	'StablePages' => array( 'Stabilaj paĝoj' ),
	'UnreviewedPages' => array( 'Nekontrolitaj paĝoj' ),
);

/** Spanish (Español) */
$aliases['es'] = array(
	'PendingChanges' => array( 'Páginas revisadas antiguas' ),
	'Stabilization' => array( 'Estabilización' ),
	'StablePages' => array( 'Páginas publicadas' ),
	'ReviewedVersions' => array( 'Versiones revisadas' ),
	'UnreviewedPages' => array( 'Páginas_sin_revisar' ),
	'ValidationStatistics' => array( 'Estadísticas de validación' ),
);

/** Persian (فارسی) */
$aliases['fa'] = array(
	'PendingChanges' => array( 'صفحه‌های_بازبینی_شده_قدیمی' ),
	'ProblemChanges' => array( 'تغییر_مشکلات' ),
	'QualityOversight' => array( 'نظارت_کیفی' ),
	'ReviewedPages' => array( 'صفحه‌های_بازبینی_شده' ),
	'RevisionReview' => array( 'بازبینی_نسخه' ),
	'Stabilization' => array( 'پایدارسازی' ),
	'StablePages' => array( 'صفحه‌های_پایدار' ),
	'ReviewedVersions' => array( 'نسخه‌های_پایدار' ),
	'UnreviewedPages' => array( 'صفحه‌های‌بازبینی‌نشده' ),
	'ValidationStatistics' => array( 'آمار_تاییدها' ),
);

/** Finnish (Suomi) */
$aliases['fi'] = array(
	'ProblemChanges' => array( 'Ongelmalliset_muutokset' ),
	'Stabilization' => array( 'Vakaaksi versioksi' ),
	'StablePages' => array( 'Vakaat sivut' ),
	'UnreviewedPages' => array( 'Arvioimattomat sivut' ),
);

/** French (Français) */
$aliases['fr'] = array(
	'PendingChanges' => array( 'AnciennesPagesRelues' ),
	'QualityOversight' => array( 'SuperviseurQualité' ),
	'ReviewedPages' => array( 'Pages révisées' ),
	'RevisionReview' => array( 'Relecture des révisions' ),
	'StablePages' => array( 'Pages stables' ),
	'UnreviewedPages' => array( 'Pages non relues' ),
	'ValidationStatistics' => array( 'Statistiques de validation' ),
);

/** Franco-Provençal (Arpetan) */
$aliases['frp'] = array(
	'PendingChanges' => array( 'Pâges que les vèrsions sont dèpassâs', 'PâgesQueLesVèrsionsSontDèpassâs' ),
	'QualityOversight' => array( 'Supèrvision de qualitât', 'SupèrvisionDeQualitât' ),
	'ReviewedPages' => array( 'Pâges revues', 'PâgesRevues' ),
	'RevisionReview' => array( 'Rèvision de les vèrsions', 'RèvisionDeLesVèrsions' ),
	'Stabilization' => array( 'Stabilisacion' ),
	'StablePages' => array( 'Pâges stâbles', 'PâgesStâbles' ),
	'UnreviewedPages' => array( 'Pâges pas revues', 'PâgesPasRevues' ),
	'ValidationStatistics' => array( 'Statistiques de validacion', 'StatistiquesDeValidacion' ),
);

/** Galician (Galego) */
$aliases['gl'] = array(
	'PendingChanges' => array( 'Páxinas revisadas hai tempo' ),
	'QualityOversight' => array( 'Revisión de calidade' ),
	'ReviewedPages' => array( 'Páxinas revisadas' ),
	'RevisionReview' => array( 'Revisión da revisión' ),
	'Stabilization' => array( 'Estabilización' ),
	'StablePages' => array( 'Páxinas estábeis' ),
	'UnreviewedPages' => array( 'Páxinas non revisadas' ),
	'ValidationStatistics' => array( 'Estatísticas de validación' ),
);

/** Swiss German (Alemannisch) */
$aliases['gsw'] = array(
	'PendingChanges' => array( 'Syte mit Versione wu nit gsichtet sin' ),
	'QualityOversight' => array( 'Markierigsibersicht' ),
	'ReviewedPages' => array( 'Gsichteti Syte' ),
	'RevisionReview' => array( 'Versionspriefig' ),
	'Stabilization' => array( 'Sytekonfiguration' ),
	'StablePages' => array( 'Konfigurierti Syte' ),
	'UnreviewedPages' => array( 'Syte wu nit gsichtet sin' ),
	'ValidationStatistics' => array( 'Markierigsstatischtik' ),
);

/** Gujarati (ગુજરાતી) */
$aliases['gu'] = array(
	'PendingChanges' => array( 'જુનાં તપાસાયેલા પાનાં' ),
	'QualityOversight' => array( 'ગુણવતા દુર્લક્ષ' ),
	'ReviewedPages' => array( 'રીવ્યુપાનાં' ),
	'RevisionReview' => array( 'આવૃત્તિરીવ્યુ' ),
	'Stabilization' => array( 'સ્થિરતા' ),
	'StablePages' => array( 'સ્થિરપાનાઓ' ),
);

/** Hindi (हिन्दी) */
$aliases['hi'] = array(
	'PendingChanges' => array( 'पुरानेदेखेंहुएपन्ने' ),
	'QualityOversight' => array( 'गुणवत्ताओव्हरसाईट' ),
	'ReviewedPages' => array( 'जाँचेहुएपन्ने' ),
	'StablePages' => array( 'स्थिरपन्ने' ),
	'UnreviewedPages' => array( 'नदेखेंहुएपन्ने' ),
);

/** Croatian (Hrvatski) */
$aliases['hr'] = array(
	'StablePages' => array( 'Stabilne_stranice' ),
);

/** Upper Sorbian (Hornjoserbsce) */
$aliases['hsb'] = array(
	'PendingChanges' => array( 'Zaso njepřehladane strony' ),
	'QualityOversight' => array( 'Kwalitna kontrola' ),
	'ReviewedPages' => array( 'Přehladane strony' ),
	'RevisionReview' => array( 'Wersijowe přehladanje' ),
	'Stabilization' => array( 'Stabilizacija' ),
	'StablePages' => array( 'Stabilne strony' ),
	'UnreviewedPages' => array( 'Njepřehladane strony' ),
	'ValidationStatistics' => array( 'Statistika přehladanjow' ),
);

/** Hungarian (Magyar) */
$aliases['hu'] = array(
	'PendingChanges' => array( 'Elavult ellenőrzött lapok', 'Régen ellenőrzött lapok' ),
	'QualityOversight' => array( 'Minőségellenőrzés' ),
	'ReviewedPages' => array( 'Ellenőrzött lapok' ),
	'RevisionReview' => array( 'Változat ellenőrzése' ),
	'Stabilization' => array( 'Lap rögzítése' ),
	'StablePages' => array( 'Rögzített lapok' ),
	'UnreviewedPages' => array( 'Ellenőrizetlen lapok' ),
	'ValidationStatistics' => array( 'Ellenőrzési statisztika' ),
);

/** Interlingua (Interlingua) */
$aliases['ia'] = array(
	'PendingChanges' => array( 'Paginas revidite ancian' ),
	'ProblemChanges' => array( 'Modificationes problematic' ),
	'QualityOversight' => array( 'Supervision de qualitate' ),
	'ReviewedPages' => array( 'Paginas revidite' ),
	'RevisionReview' => array( 'Recension de versiones' ),
	'StablePages' => array( 'Paginas publicate', 'Paginas stabile' ),
	'ReviewedVersions' => array( 'Versiones revidite', 'Versiones stabile' ),
	'UnreviewedPages' => array( 'Paginas non revidite' ),
	'ValidationStatistics' => array( 'Statisticas de validation' ),
);

/** Indonesian (Bahasa Indonesia) */
$aliases['id'] = array(
	'PendingChanges' => array( 'Halaman tertinjau usang', 'HalamanTertinjauUsang' ),
	'ProblemChanges' => array( 'Perubahan masalah', 'PerubahanMasalah' ),
	'QualityOversight' => array( 'Pemeriksaan kualitas', 'PemeriksaanKualitas' ),
	'ReviewedPages' => array( 'Halaman tertinjau', 'HalamanTertinjau' ),
	'RevisionReview' => array( 'Tinjauan revisi', 'TinjauanRevisi' ),
	'Stabilization' => array( 'Stabilisasi' ),
	'StablePages' => array( 'Halaman stabil', 'HalamanStabil' ),
	'UnreviewedPages' => array( 'Halaman yang belum ditinjau', 'HalamanBelumDitinjau' ),
	'ValidationStatistics' => array( 'Statistik validasi', 'StatistikValidasi' ),
);

/** Japanese (日本語) */
$aliases['ja'] = array(
	'PendingChanges' => array( '古くなった査読済みページ' ),
	'ProblemChanges' => array( '問題の修正' ),
	'QualityOversight' => array( '品質監督' ),
	'ReviewedPages' => array( '査読済みページ' ),
	'RevisionReview' => array( '特定版の査読' ),
	'Stabilization' => array( '固定', '採択', 'ページの採択' ),
	'StablePages' => array( '固定ページ', '安定ページ', '採用ページ' ),
	'ConfiguredPages' => array( '査読設定のあるページ' ),
	'ReviewedVersions' => array( '固定版', '安定版', '採用版' ),
	'UnreviewedPages' => array( '未査読ページ', '査読待ちページ' ),
	'ValidationStatistics' => array( '判定統計' ),
);

/** Colognian (Ripoarisch) */
$aliases['ksh'] = array(
	'PendingChanges' => array( 'SiggeMetUnjesichVersione' ),
	'ReviewedPages' => array( 'JesichSigge' ),
	'UnreviewedPages' => array( 'UNjesichSigge' ),
);

/** Luxembourgish (Lëtzebuergesch) */
$aliases['lb'] = array(
	'PendingChanges' => array( 'Säite mat Versiounen déi net iwwerpréift sinn' ),
	'ProblemChanges' => array( 'Problematesch Ännerungen' ),
	'ReviewedPages' => array( 'Säiten déi iwwerkuckt goufen' ),
	'RevisionReview' => array( 'Versioun iwwerpréifen' ),
	'Stabilization' => array( 'Stabilisatioun' ),
	'StablePages' => array( 'Stabil Säiten' ),
	'ReviewedVersions' => array( 'Stabil Versiounen' ),
	'UnreviewedPages' => array( 'Net iwwerpréifte Säiten' ),
	'ValidationStatistics' => array( 'Statistik vun den iwwerpréifte Säiten' ),
);

/** Macedonian (Македонски) */
$aliases['mk'] = array(
	'PendingChanges' => array( 'СтариОценетиСтраници' ),
	'ProblemChanges' => array( 'ПромениНаПроблеми' ),
	'QualityOversight' => array( 'НадлегувањеНаКвалитетот' ),
	'ReviewedPages' => array( 'ПрегледаниСтраници' ),
	'RevisionReview' => array( 'ПрегледНаРевизии' ),
	'Stabilization' => array( 'Стабилизација' ),
	'StablePages' => array( 'СтабилниСтраници' ),
	'ReviewedVersions' => array( 'ПрегледаниВерзии', 'СтабилниВерзии' ),
	'UnreviewedPages' => array( 'НепрегледаниСтраници' ),
	'ValidationStatistics' => array( 'ВалидацискиСтатистики' ),
);

/** Malayalam (മലയാളം) */
$aliases['ml'] = array(
	'PendingChanges' => array( 'മുമ്പ് സംശോധനം ചെയ്ത താളുകൾ' ),
	'ProblemChanges' => array( 'പ്രശ്നകാരിമാറ്റങ്ങൾ' ),
	'QualityOversight' => array( 'ഗുണമേന്മാമേൽനോട്ടം' ),
	'ReviewedPages' => array( 'സംശോധനംചെയ്തതാളുകൾ' ),
	'RevisionReview' => array( 'നാൾപ്പതിപ്പ്സംശോധനം' ),
	'Stabilization' => array( 'സ്ഥിരപ്പെടുത്തൽ' ),
	'StablePages' => array( 'സ്ഥിരതാളുകൾ' ),
	'ConfiguredPages' => array( 'ക്രമീകരിച്ചതാളുകൾ' ),
	'ReviewedVersions' => array( 'സംശോധിതപതിപ്പുകൾ', 'സ്ഥിരതയുള്ള പതിപ്പുകൾ' ),
	'UnreviewedPages' => array( 'സംശോധനംചെയ്യാത്തതാളുകൾ' ),
	'ValidationStatistics' => array( 'മൂല്യനിർണ്ണയസ്ഥിതിവിവരം' ),
);

/** Marathi (मराठी) */
$aliases['mr'] = array(
	'PendingChanges' => array( 'जुनीतपासलेलीपाने' ),
	'QualityOversight' => array( 'गुणवत्ताओव्हरसाईट' ),
	'ReviewedPages' => array( 'तपासलेलीपाने' ),
	'RevisionReview' => array( 'आवृत्तीसमीक्षा' ),
	'Stabilization' => array( 'स्थिरीकरण' ),
	'StablePages' => array( 'स्थिरपाने' ),
	'UnreviewedPages' => array( 'नतपासलेलीपाने' ),
);

/** Malay (Bahasa Melayu) */
$aliases['ms'] = array(
	'PendingChanges' => array( 'Laman diperiksa lapuk' ),
	'QualityOversight' => array( 'Kawalan mutu' ),
	'ReviewedPages' => array( 'Laman diperiksa' ),
	'StablePages' => array( 'Laman stabil' ),
	'UnreviewedPages' => array( 'Laman_tidak_diperiksa' ),
);

/** Nedersaksisch (Nedersaksisch) */
$aliases['nds-nl'] = array(
	'PendingChanges' => array( "Pagina's_verouwerde_eindredactie" ),
	'ProblemChanges' => array( 'Preblematische_wiezigingen' ),
	'QualityOversight' => array( 'Kwaliteitscontrole' ),
	'ReviewedPages' => array( 'Pagina_mit_eindredactie' ),
	'RevisionReview' => array( 'Eindredactie_versies' ),
	'Stabilization' => array( 'Stabilisasie' ),
	'StablePages' => array( "Stebiele_pagina's" ),
	'UnreviewedPages' => array( "Pagina's_zonder_eindredactie" ),
	'ValidationStatistics' => array( 'Eindredactiestaotestieken' ),
);

/** Dutch (Nederlands) */
$aliases['nl'] = array(
	'PendingChanges' => array( 'PaginasVerouderdeEindredactie', "Pagina'sVerouderdeEindredactie" ),
	'ProblemChanges' => array( 'ProblematischeWijzigingen' ),
	'QualityOversight' => array( 'KwaliteitsControle' ),
	'ReviewedPages' => array( 'PaginasMetEindredactie', "Pagina'sMetEindredactie" ),
	'RevisionReview' => array( 'EindredactieVersies' ),
	'Stabilization' => array( 'Stabilisatie' ),
	'StablePages' => array( 'StabielePaginas', "StabielePagina's" ),
	'ReviewedVersions' => array( 'GecontroleerdeVersies', 'StabieleVersies' ),
	'UnreviewedPages' => array( 'PaginasZonderEindredactie', "Pagina'sZonderEindredactie" ),
	'ValidationStatistics' => array( 'Eindredactiestatistieken', 'StatistiekenEindredactie' ),
);

/** Norwegian Nynorsk (‪Norsk (nynorsk)‬) */
$aliases['nn'] = array(
	'PendingChanges' => array( 'Gamle vurderte sider' ),
	'QualityOversight' => array( 'Kvalitetsoversyn' ),
	'ReviewedPages' => array( 'Vurderte sider' ),
	'RevisionReview' => array( 'Versjonsvurdering' ),
	'Stabilization' => array( 'Stabilisering' ),
	'StablePages' => array( 'Stabile sider' ),
	'UnreviewedPages' => array( 'Ikkje-vurderte sider' ),
	'ValidationStatistics' => array( 'Valideringsstatistikk' ),
);

/** Norwegian (bokmål)‬ (‪Norsk (bokmål)‬) */
$aliases['no'] = array(
	'PendingChanges' => array( 'Gamle anmeldte sider' ),
	'ProblemChanges' => array( 'Problemendringer' ),
	'QualityOversight' => array( 'Kvalitetsoversikt' ),
	'ReviewedPages' => array( 'Anmeldte sider' ),
	'RevisionReview' => array( 'Revisjonsgjennomgang' ),
	'Stabilization' => array( 'Stabilisering' ),
	'StablePages' => array( 'Stabile sider' ),
	'ReviewedVersions' => array( 'Gjennomgåtte sider' ),
	'UnreviewedPages' => array( 'Ikke-gjennomgåtte sider' ),
	'ValidationStatistics' => array( 'Valideringsstatistikk' ),
);

/** Occitan (Occitan) */
$aliases['oc'] = array(
	'PendingChanges' => array( 'PaginasAncianasRelegidas' ),
	'QualityOversight' => array( 'SupervisorQualitat' ),
	'ReviewedPages' => array( 'Paginas revisadas', 'PaginasRevisadas' ),
	'RevisionReview' => array( 'Relectura de las revisions' ),
	'StablePages' => array( 'Paginas establas', 'PaginasEstablas' ),
	'UnreviewedPages' => array( 'Paginas pas relegidas', 'PaginasPasRelegidas' ),
);

/** Polish (Polski) */
$aliases['pl'] = array(
	'PendingChanges' => array( 'Zdezaktualizowane przejrzane strony' ),
	'ProblemChanges' => array( 'Wątpliwe zmiany' ),
	'QualityOversight' => array( 'Rejestr oznaczania wersji' ),
	'ReviewedPages' => array( 'Przejrzane strony' ),
	'RevisionReview' => array( 'Oznaczenie wersji' ),
	'Stabilization' => array( 'Konfiguracja strony' ),
	'StablePages' => array( 'Strony stabilizowane', 'Strony z domyślnie pokazywaną wersją oznaczoną' ),
	'UnreviewedPages' => array( 'Nieprzejrzane strony' ),
	'ValidationStatistics' => array( 'Statystyki oznaczania' ),
);

/** Portuguese (Português) */
$aliases['pt'] = array(
	'PendingChanges' => array( 'Páginas analisadas antigas' ),
	'QualityOversight' => array( 'Controlo de qualidade' ),
	'ReviewedPages' => array( 'Páginas analisadas' ),
	'RevisionReview' => array( 'Revisão de versões' ),
	'Stabilization' => array( 'Estabilização' ),
	'StablePages' => array( 'Páginas estáveis' ),
	'ReviewedVersions' => array( 'Versões revistas' ),
	'UnreviewedPages' => array( 'Páginas a analisar' ),
	'ValidationStatistics' => array( 'Estatísticas de validação' ),
);

/** Brazilian Portuguese (Português do Brasil) */
$aliases['pt-br'] = array(
	'PendingChanges' => array( 'Versões_antigas_de_páginas_analisadas' ),
	'QualityOversight' => array( 'Observatório_da_qualidade' ),
	'ReviewedPages' => array( 'Páginas_analisadas' ),
	'RevisionReview' => array( 'Revisão de edições' ),
	'Stabilization' => array( 'Estabilização' ),
	'StablePages' => array( 'Páginas_estáveis' ),
	'ConfiguredPages' => array( 'Páginas configuradas' ),
	'UnreviewedPages' => array( 'Páginas_a_analisar' ),
	'ValidationStatistics' => array( 'Estatísticas de validação' ),
);

/** Sanskrit (संस्कृत) */
$aliases['sa'] = array(
	'PendingChanges' => array( 'पूर्वतनआवलोकीतपृष्ठ:' ),
	'QualityOversight' => array( 'गुणपूर्णवृजावलोकन' ),
	'ReviewedPages' => array( 'समसमीक्षीतपृष्ठ:' ),
	'RevisionReview' => array( 'आवृत्तीसमसमीक्षा' ),
	'Stabilization' => array( 'स्वास्थ्य' ),
	'StablePages' => array( 'स्वस्थपृष्ठ' ),
	'UnreviewedPages' => array( 'असमसमीक्षीतपृष्ठ:' ),
	'ValidationStatistics' => array( 'उपयोगितासिद्धीसांख्यिकी' ),
);

/** Slovak (Slovenčina) */
$aliases['sk'] = array(
	'PendingChanges' => array( 'StaréSkontrolovanéStránky' ),
	'ProblemChanges' => array( 'ProblematickéZmeny' ),
	'QualityOversight' => array( 'DohľadNadKvalitou' ),
	'ReviewedPages' => array( 'SkontrolovanéStránky' ),
	'RevisionReview' => array( 'KontrolaKontroly' ),
	'Stabilization' => array( 'Stabilizácia' ),
	'StablePages' => array( 'StabilnéStránky' ),
	'UnreviewedPages' => array( 'NeskontrolovanéStránky' ),
	'ValidationStatistics' => array( 'ŠtatistikaOverovania' ),
);

/** Albanian (Shqip) */
$aliases['sq'] = array(
	'Stabilization' => array( 'Stabilizim' ),
	'StablePages' => array( 'FaqetStabile' ),
);

/** Serbian Cyrillic ekavian (Српски (ћирилица)) */
$aliases['sr-ec'] = array(
	'PendingChanges' => array( 'СтареПрегледанеСтране' ),
	'QualityOversight' => array( 'НадгледањеКвалитета' ),
	'ReviewedPages' => array( 'ПрегледанеСтране' ),
	'StablePages' => array( 'СтабилнеСтране' ),
	'UnreviewedPages' => array( 'НепрегледанеСтране' ),
);

/** Swedish (Svenska) */
$aliases['sv'] = array(
	'PendingChanges' => array( 'Gamla granskade sidor' ),
	'QualityOversight' => array( 'Kvalitetsöversikt' ),
	'ReviewedPages' => array( 'Granskade sidor' ),
	'RevisionReview' => array( 'Versionsgranskning' ),
	'Stabilization' => array( 'Stabilisering' ),
	'StablePages' => array( 'Stabila sidor' ),
	'UnreviewedPages' => array( 'Ogranskade sidor' ),
	'ValidationStatistics' => array( 'Valideringsstatistik' ),
);

/** Swahili (Kiswahili) */
$aliases['sw'] = array(
	'PendingChanges' => array( 'KurasaZilizoonyeshwaAwali' ),
	'ReviewedPages' => array( 'OnyeshaKurasa' ),
	'Stabilization' => array( 'Uimalishaji' ),
	'StablePages' => array( 'KurasaImara' ),
	'UnreviewedPages' => array( 'KurasaZisizoonyeshwa' ),
	'ValidationStatistics' => array( 'TakwimuIliyosahihi' ),
);

/** Tagalog (Tagalog) */
$aliases['tl'] = array(
	'PendingChanges' => array( 'Nasuring lumang mga pahina' ),
	'QualityOversight' => array( 'Maingat na pamamahala ng kalidad' ),
	'ReviewedPages' => array( 'Sinuring mga pahina' ),
	'RevisionReview' => array( 'Pagsusuri ng pagbabago' ),
	'Stabilization' => array( 'Pagpapatatag', 'Pagpapatibay' ),
	'StablePages' => array( 'Matatag na mga pahina' ),
	'UnreviewedPages' => array( 'Mga pahina hindi pa nasusuri' ),
	'ValidationStatistics' => array( 'Mga estadistika ng pagtitiyak' ),
);

/** Turkish (Türkçe) */
$aliases['tr'] = array(
	'PendingChanges' => array( 'EskiİncelenmişSayfalar' ),
	'ProblemChanges' => array( 'ProblemDeğişiklikleri' ),
	'QualityOversight' => array( 'KaliteGözetimi' ),
	'ReviewedPages' => array( 'İncelenmişSayfalar' ),
	'RevisionReview' => array( 'Sürümİnceleme' ),
	'Stabilization' => array( 'Stabilizasyon', 'İstikrar' ),
	'StablePages' => array( 'StabilSayfalar', 'İstikrarlıSayfalar' ),
	'ReviewedVersions' => array( 'İncelenmişSürümler', 'StabilSürümler' ),
	'UnreviewedPages' => array( 'İncelenmemişSayfalar' ),
	'ValidationStatistics' => array( 'Doğrulamaİstatistikleri' ),
);

/** Vèneto (Vèneto) */
$aliases['vec'] = array(
	'PendingChanges' => array( 'PagineRiesaminàVèce' ),
	'QualityOversight' => array( 'ControloQualità' ),
	'ReviewedPages' => array( 'PagineRiesaminà' ),
	'StablePages' => array( 'PagineStabili' ),
	'UnreviewedPages' => array( 'PagineNonRiesaminà' ),
	'ValidationStatistics' => array( 'StatìstegheDeValidassion' ),
);

/** Vietnamese (Tiếng Việt) */
$aliases['vi'] = array(
	'PendingChanges' => array( 'Trang chưa duyệt cũ' ),
	'QualityOversight' => array( 'Giám sát chất lượng' ),
	'ReviewedPages' => array( 'Trang đã duyệt' ),
	'StablePages' => array( 'Trang ổn định' ),
	'UnreviewedPages' => array( 'Trang chưa duyệt' ),
);

