<?php
// German (Raimond Spekking)
$messages = array( 
	'editor'               => 'Sichter',
	'group-editor'         => 'Sichter',
	'group-editor-member'  => 'Sichter',
	'grouppage-editor'     => '{{ns:project}}:Sichter',

	'reviewer'              => 'Prüfer',
	'group-reviewer'        => 'Prüfer',
	'group-reviewer-member' => 'Prüfer',
	'grouppage-reviewer'    => '{{ns:project}}:Prüfer',

	'revreview-current'   => 'Entwurf (bearbeitbar)',
	'tooltip-ca-current'  => 'Ansehen des aktuellen Entwurfes dieser Seite',
	'revreview-edit'      => 'Bearbeite Entwurf',
	'revreview-source'    => 'Entwurfs-Quelltext',
	'revreview-stable'    => 'Stabil',
	'tooltip-ca-stable'   => 'Ansehen der stabilen Version dieser Seite',
	'revreview-oldrating' => 'Bisherige Einstufung:',
	'revreview-noflagged' => 'Von dieser Seite gibt es keine markierten Versionen, so dass noch keine Aussage über die [[{{MediaWiki:Makevalidate-page}}|Qualität]] gemacht werden kann.',

	'stabilization-tab'   => '(qa)',
	'tooltip-ca-default'  => 'Einstellungen der Artikel-Qualität',

	'revreview-quick-none'        => "'''Aktuell.''' Es wurde noch keine Version überprüft.",

	'revreview-quick-see-quality' => "'''Aktuell.''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Siehe die letzte überprüfte Version]]
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|Änderung|Änderungen}}])",

	'revreview-quick-see-basic'   => "'''Aktuell.''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Siehe die letzte überprüfte Version]]
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|Änderung|Änderungen}}])",

	'revreview-quick-basic'       => "'''[[{{MediaWiki:Makevalidate-page}}|Gesichtet.]]''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Siehe die aktuelle Version]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|Änderung|Änderungen}}])",

	'revreview-quick-quality'     => "'''[[{{MediaWiki:Makevalidate-page}}|Geprüft.]]''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Siehe die aktuelle Version]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|Änderung|Änderungen}}])",

	'revreview-newest-basic' => 'Die [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} letzte überprüfte Version]
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} siehe alle]) wurde am <i>$2</i> [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben].
	[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|Version|Versionen}}] {{plural:$3|steht|stehen}} noch zur Prüfung an.',

	'revreview-newest-quality' => 'Die [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} letzte überprüfte Version]
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} siehe alle]) wurde am <i>$2</i> [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben].
	[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|Version|Versionen}}] {{plural:$3|steht|stehen}} noch zur Prüfung an.',

	'revreview-basic'  => 'Dies ist die letzte [[Help:Gesichtete Versionen|gesichtete]] Version,
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben] am <i>$2</i>. Die [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} derzeitige Version]
	kann [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} bearbeitet] werden; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|Version|Versionen}}]
	{{plural:$3|steht|stehen}} noch zur Prüfung an.',

	'revreview-quality'  => 'Das ist die letzte [[Help:Versionsbewertung|geprüfte]] Version,
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben] am <i>$2</i>. Die [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} derzeitige Version]
	kann [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} bearbeitet] werden; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|Version|Versionen}}]
	{{plural:$3|steht|stehen}} noch zur Prüfung an.',

	'revreview-static' => "Dies ist eine [[Help:Geprüfte Versionen|geprüfte]] Version von '''[[:$3|$3]]''', 
	[{{fullurl:Special:Log/review|page=$1}} freigegeben] am <i>$2</i>.",

	'revreview-toggle'      => '(+/-)',
	'revreview-note'        => '[[{{ns:user}}:$1]] machte die folgende [[{{MediaWiki:Makevalidate-page}}|Prüfnotiz]] zu dieser Version:',
	'revreview-update'      => 'Bitte prüfe jede Änderung seit der letzten stabilen Version (siehe unten).
	Die folgenden Vorlagen und Bilder wurden ebenfalls verändert:',
	'revreview-update-none' => 'Bitte prüfe jede Änderung seit der letzten stabilen Version (siehe unten).',
	'revreview-auto'        => '(automatisch)',
	'revreview-auto-w'      => "Du bearbeitest eine stabile Version, deine Bearbeitung wird '''automatisch als überprüft markiert.''' 
	Du solltest die Seite daher vor dem Speichern in der Vorschau betrachten.",
        'revreview-auto-w-old'  => "Du bearbeitest eine alte Version, deine Bearbeitung wird '''automatisch als überprüft markiert.''' 
        Du solltest die Seite daher vor dem Speichern in der Vorschau betrachten.",

	'hist-stable'  => '[gesichtet]',
	'hist-quality' => '[geprüft]',

	'flaggedrevs'           => 'Markierte Versionen',
	'review-logpage'        => 'Artikel-Prüf-Logbuch',
	'review-logpagetext'    => 'Dies ist das Änderungs-Logbuch der [[{{MediaWiki:Makevalidate-page}}|Seiten-Freigaben]].',
	'review-logentry-app'   => 'überprüfte [[$1]]',
	'review-logentry-dis'   => 'verwarf eine Version von [[$1]]',
	'review-logaction'      => 'Version-ID $1',

	'stable-logpage'     => 'Stabile-Versionen-Logbuch',
	'stable-logpagetext' => 'Dies ist das Änderungs-Logbuch der Konfigurationseinstellungen der [[{{MediaWiki:Makevalidate-page}}|Stabilen Versionen]]',
	'stable-logentry'    => 'konfigurierte die Seiten-Einstellung von [[$1]]',
	'stable-logentry2'   => 'setzte die Seiten-Einstellung für [[$1]] zurück',

	'revisionreview'       => 'Versionsprüfung',
	'revreview-main'       => 'Du musst eine Artikelversion zur Prüfung auswählen.

	Siehe [[{{ns:special}}:Unreviewedpages]] für eine Liste nicht überprüfter Versionen.',	
	'revreview-selected'   => "Gewählte Version von '''$1:'''",
	'revreview-text'       => "Einer stabilen Version wird bei der Seitendarstellung der Vorzug vor einer neueren Version gegeben.",
	'revreview-toolow'     => 'Du musst für jedes der untenstehenden Attribute einen Wert höher als „{{int:revreview-accuracy-0}}“ einstellen,
	damit eine Version als überprüft gilt. Um eine Version zu verwerfen, müssen alle Attribute auf „{{int:revreview-accuracy-0}}“ stehen.',
	'revreview-flag'       => 'Prüfe Version #$1',
	'revreview-legend'     => 'Inhalt der Version bewerten',
	'revreview-notes'      => 'Anzuzeigende Bemerkungen oder Notizen:',
	'revreview-accuracy'   => 'Genauigkeit',
	'revreview-accuracy-0' => 'nicht freigegeben',
	'revreview-accuracy-1' => 'gesichtet',
	'revreview-accuracy-2' => 'geprüft',
	'revreview-accuracy-3' => 'Quellen geprüft', # not used in de.wiki
	'revreview-accuracy-4' => 'exzellent', # not used in de.wiki
	'revreview-depth'      => 'Tiefe', # not used in de.wiki
	'revreview-depth-0'    => 'nicht freigegeben', # not used in de.wiki
	'revreview-depth-1'    => 'einfach', # not used in de.wiki
	'revreview-depth-2'    => 'mittel', # not used in de.wiki
	'revreview-depth-3'    => 'hoch', # not used in de.wiki
	'revreview-depth-4'    => 'exzellent', # not used in de.wiki
	'revreview-style'      => 'Lesbarkeit', # not used in de.wiki
	'revreview-style-0'    => 'nicht freigegeben', # not used in de.wiki
	'revreview-style-1'    => 'akzeptabel', # not used in de.wiki
	'revreview-style-2'    => 'gut', # not used in de.wiki
	'revreview-style-3'    => 'präzise', # not used in de.wiki
	'revreview-style-4'    => 'exzellent', # not used in de.wiki
	'revreview-log'        => 'Logbuch-Eintrag:',
	'revreview-submit'     => 'Prüfung speichern',
	'revreview-changed'    => '\'\'\'Die Aktion konnte nicht auf diese Version angewendet werden.\'\'\'

	Eine Vorlage oder ein Bild wurden ohne spezifische Versionsnummer angefordert. Dies kann passieren,
	wenn eine dynamische Vorlage eine weitere Vorlage oder ein Bild einbindet, das von einer Variable abhängig ist, die
	sich seit Beginn der Prüfung verändert hat. Ein Neuladen der Seite und Neustart der Prüfung kann das Problem beheben.',

	'stableversions'        => 'Stabile Versionen',
	'stableversions-leg1'   => 'Liste der überprüften Versionen für einen Artikel',
	'stableversions-page'   => 'Artikelname:',
	'stableversions-none'   => '„[[:$1]]“ hat keine überprüften Versionen.',
	'stableversions-list'   => 'Dies ist die Liste der überprüften Versionen von „[[:$1]]“:',
	'stableversions-review' => 'überprüft am <i>$1</i> durch $2',

	'review-diff2stable'    => 'Unterschied zur stabilen Version',

	'unreviewedpages'       => 'Nicht überprüfte Artikel',
	'viewunreviewed'        => 'Liste nicht überprüfter Artikel',
	'unreviewed-outdated'   => 'Zeige nur Seiten, die nicht überprüfte Versionen nach einer stabilen Version haben.',
	'unreviewed-category'   => 'Kategorie:',
	'unreviewed-diff'       => 'Änderungen',
	'unreviewed-list'       => 'Diese Seite zeigt Artikel, die noch nicht nie überprüft wurden oder nicht überprüfte Versionen haben.',

	'revreview-visibility'  => 'Diese Seite hat eine [[{{MediaWiki:Makevalidate-page}}|stabile Version]], welche
	[{{fullurl:Special:Stabilization|page={{FULLPAGENAMEE}}}} konfiguriert] werden kann.',

	'stabilization'           => 'Seiten-Stabilität',
	'stabilization-text'      => 'Ändere die Einstellungen um festzulegen, wie die stabile Version von „[[:$1|$1]]“ ausgewählt und angezeigt werden soll.',
	'stabilization-perm'      => 'Du hast nicht die erforderliche Berechtigung, um die Einstellungen der stabilen Version zu ändern.
	Die aktuellen Einstellungen für „[[:$1|$1]]“ sind:',
	'stabilization-page'      => 'Seitenname:',
	'stabilization-leg'       => 'Einstellungen der stabilen Version für eine Seite',
	'stabilization-select'    => 'Auswahl der stabilen Version',
	'stabilization-select1'   => 'Die letzte geprüfte Version; wenn keine vorhanden ist, dann die letzte gesichtete Version',
	'stabilization-select2'   => 'Die letzte überprüfte Version',
	'stabilization-def'       => 'Angezeigte Version in der normalen Seitenansicht',
	'stabilization-def1'      => 'Die stabile Version; wenn keine vorhanden ist, dann die aktuelle Version',
	'stabilization-def2'      => 'Die aktuellste Version',
	'stabilization-submit'    => 'Bestätigen',
	'stabilization-notexists' => 'Es gibt keine Seite „[[:$1|$1]]“. Keine Einstellungen möglich.',
	'stabilization-comment'     => 'Kommentar:',
	'stabilization-sel-short'   => 'Priorität',
	'stabilization-sel-short-0' => 'Qualität',
	'stabilization-sel-short-1' => 'keine',
	'stabilization-def-short'   => 'Standard',
	'stabilization-def-short-0' => 'Aktuell',
	'stabilization-def-short-1' => 'Stabil',

	'reviewedpages'             => 'Überprüfte Seiten',
	'reviewedpages-leg'         => 'Liste der überprüften Seiten',
	'reviewedpages-list'        => 'Die folgenden Seiten wurden überprüft und haben den angegebenen Status erhalten',
	'reviewedpages-none'        => 'Die Liste ist leer.',
	'reviewedpages-lev-0'       => 'Gesichtet',
	'reviewedpages-lev-1'       => 'Quality',
	'reviewedpages-lev-2'       => 'Exzellent',
	'reviewedpages-all'         => 'überprüfte Versionen',
	'reviewedpages-best'        => 'letzte am höchsten bewertete Version',
);
