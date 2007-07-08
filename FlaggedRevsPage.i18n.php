<?php
$RevisionreviewMessages = array();

// English (Aaron Schulz)
$RevisionreviewMessages['en'] = array( 
	'makevalidate-autosum'=> 'autopromoted',
	'editor'              => 'Editor',
	'group-editor'        => 'Editors',
	'group-editor-member' => 'Editor',
	'grouppage-editor'    => '{{ns:project}}:Editor',

	'reviewer'              => 'Reviewer',
	'group-reviewer'        => 'Reviewers',
	'group-reviewer-member' => 'Reviewer',
	'grouppage-reviewer'    => '{{ns:project}}:Reviewer',

	'revreview-current'   => 'Current revision',
	'revreview-stable'    => 'Stable version',
	'revreview-oldrating' => 'It was rated as:',
	'revreview-noflagged' => 'There are no reviewed revisions of this page, so it may \'\'\'not\'\'\' have been 
	[[Help:Article validation|checked]] for quality.',
	
	'revreview-quick-see-quality' => '\'\'\'Current\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} See latest quality revision]',
	'revreview-quick-see-basic' => '\'\'\'Current\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} See latest sighted revision]',
	'revreview-quick-basic'  => '\'\'\'Sighted\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} See current revision] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|change|changes}}])',
	'revreview-quick-quality' => '\'\'\'Quality\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} See current revision] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|change|changes}}])',
	'revreview-quick-none' => '\'\'\'Current\'\'\'. No reviewed revisions.',
	'revreview-newest-basic'    => 'The [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} latest sighted revision] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} see all]) of this page was [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approved]
	 on <i>$2</i>. <br/> There {{plural:$3|is $3 revision|are $3 revisions}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} changes]) awaiting review.',
	'revreview-newest-quality'    => 'The [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} latest quality revision] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} see all]) of this page was [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approved]
	 on <i>$2</i>. <br/> There {{plural:$3|is $3 revision|are $3 revisions}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} changes]) awaiting review.',
	'revreview-basic'  => 'This is the latest [[Help:Article validation|sighted]] revision of this page, 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approved] on <i>$2</i>. The [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} current revision] 
	is usually [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} editable] and more up to date. There {{plural:$3|is $3 revision|are $3 revisions}} 
	([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} changes]) awaiting review.',
	'revreview-quality'  => 'This is the latest [[Help:Article validation|quality]] revision of this page, 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approved] on <i>$2</i>. The [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} current revision] 
	is usually [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} editable] and more up to date. There {{plural:$3|is $3 revision|are $3 revisions}} 
	([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} changes]) awaiting review.',
	'revreview-static'  => 'This is a [[Help:Article validation|reviewed]] revision of \'\'\'[[:$3|this page]]\'\'\', 
	[{{fullurl:Special:Log/review|page=$1}} approved] on <i>$2</i>. The [{{fullurl:$3|stable=0}} current revision] 
	is usually editable and more up to date.',
	'revreview-toggle' => '(toggle details)',
	'revreview-note' => '[[User:$1]] made the following notes [[Help:Article validation|reviewing]] this revision:',

	'hist-stable'  => '[sighted]',
	'hist-quality' => '[quality]',

    'flaggedrevs'        => 'Flagged Revisions',
    'review-logpage'     => 'Article review log',
	'review-logpagetext' => 'This is a log of changes to revisions\' [[Help:Article validation|approval]] status
	for content pages.',
	'review-logentrygrant'   => 'reviewed a version of $1',
	'review-logentryrevoke'  => 'depreciated a version of $1',
	'review-logaction'  => 'revision ID $1',

    'revisionreview'       => 'Review revisions',		
    'revreview-main'       => 'You must select a particular revision from a content page in order to review. 

	See the [[Special:Unreviewedpages]] for a list of unreviewed pages.',	
	'revreview-selected'   => "Selected revision of '''$1:'''",
	'revreview-text'       => "Stable versions are set as the default content on page view rather than the newest revision.",
	'revreview-toolow'     => 'You must at least rate each of the below attributes higher than "unapproved" in order 
	for a revision to be considered reviewed. To depreciate a revision, set all fields to "unapproved".',
	'revreview-flag'       => 'Review this revision (#$1):',
	'revreview-legend'     => 'Rate revision content:',
	'revreview-notes'      => 'Observations or notes to display:',
	'revreview-accuracy'   => 'Accuracy',
	'revreview-accuracy-0' => 'Unapproved',
	'revreview-accuracy-1' => 'Sighted',
	'revreview-accuracy-2' => 'Accurate',
	'revreview-accuracy-3' => 'Well sourced',
	'revreview-accuracy-4' => 'Featured',
	'revreview-depth'      => 'Depth',
	'revreview-depth-0'    => 'Unapproved',
	'revreview-depth-1'    => 'Basic',		
	'revreview-depth-2'    => 'Moderate',
	'revreview-depth-3'    => 'High',
	'revreview-depth-4'    => 'Featured',
	'revreview-style'      => 'Readability',
	'revreview-style-0'    => 'Unapproved',
	'revreview-style-1'    => 'Acceptable',
	'revreview-style-2'    => 'Good',
	'revreview-style-3'    => 'Concise',
	'revreview-style-4'    => 'Featured',
	'revreview-log'        => 'Log comment:',
	'revreview-submit'     => 'Submit review',
	'revreview-changed'    => '\'\'\'The requestion action could not be performed on this revision.\'\'\'
	
	A template or image may have been requested when no specific version was specified. This can happen if a 
	dynamic template transcludes another image or template depending on a variable that changed since you started 
	reviewed this page. Refreshing the page and rereviewing can solve this problem.',

	'stableversions'        => 'Stable versions',
	'stableversions-leg1'   => 'List reviewed revisions for a page',
	'stableversions-leg2'   => 'View a reviewed revision',
	'stableversions-page'   => 'Page name',
	'stableversions-rev'    => 'Revision ID',
	'stableversions-none'   => '[[:$1]] has no reviewed revisions.',
	'stableversions-list'   => 'The following is a list of revisions of [[:$1]] that have been reviewed:',
	'stableversions-review' => 'Reviewed on <i>$1</i>',

    'review-diff2stable'    => 'Diff to the last stable revision',
    'review-diff2oldest'    => "Diff to the oldest revision",

    'unreviewedpages'       => 'Unreviewed pages',
    'viewunreviewed'        => 'List unreviewed content pages',
    'included-nonquality'   => 'Show only reviewed pages not marked as quality.',
    'unreviewed-list'       => 'This page lists articles that have not yet been reviewed.',
);

// German (Raimond Spekking)
$RevisionreviewMessages['de'] = array( 
	'editor'              => 'Editor',
	'group-editor'        => 'Editoren',
	'group-editor-member' => 'Editor',
	'grouppage-editor'    => '{{ns:project}}:Editor',

	'reviewer'              => 'Prüfer',
	'group-reviewer'        => 'Prüfer',
	'group-reviewer-member' => 'Prüfer',
	'grouppage-reviewer'    => '{{ns:project}}:Prüfer',

	'revreview-current'   => 'Aktuelle Version',
	'revreview-stable'    => 'Stabile Version',
	'revreview-oldrating' => 'war eingestuft als:',
	'revreview-noflagged' => 'Von dieser Seite gibt es keine geprüften Versionen, so dass noch keine Aussage über die [[Help:Article validation|Artikelqualität]]
	gemacht werden kann.',

	'revreview-quick-see-quality' => "'''Aktuell.''' [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Siehe die letzte geprüfte Version]",
	'revreview-quick-see-basic'   => "'''Aktuell.''' [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Siehe die letzte gesichtete Version]",
	'revreview-quick-basic'       => "'''Gesichtet.''' [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Siehe die aktuelle Version] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|Änderung|Änderungen}}])",
	'revreview-quick-quality'     => "'''Geprüft.''' [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Siehe die aktuelle Version] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|Änderung|Änderungen}}])",
	'revreview-quick-none'        => "'''Aktuell.'''. Es wurde noch keine Version gesichtet.",

	'revreview-newest-basic' => 'Die [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} letzte gesichtete Version]
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} siehe alle]) dieser Seite wurde am <i>$2</i> [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben].<br />
	{{plural:$3|1 Version steht|$3 Versionen stehen}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} Änderungen]) noch zur Prüfung an.',

	'revreview-newest-quality' => 'Die [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} letzte geprüfte Version]
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} siehe alle]) diese Seite  wurde am <i>$2</i> [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben]<br/>
	There {{plural:$3|is $3 revision|are $3 revisions}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} changes]) awaiting review.',

	'revreview-basic'  => 'Dies ist die letzte [[Help:Gesichtete Versionen|gesichtete]] Version dieser Seite,
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben] am <i>$2</i>. Die [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} derzeitige Version]
	kann in der Regel [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} bearbeitet] werden und ist aktueller. {{plural:$3|1 Version steht|$3 Versionen stehen}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} Änderungen])
	noch zur Prüfung an.',

	'revreview-quality'  => 'Das ist für diesen Artikel die letzte Version mit [[Help:Versionsbewertung|Qualitätsbewertung]]
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben] am <i>$2</i>.
	Die [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} derzeitige Version] kann in der Regel [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} bearbeitet] werden und ist aktueller.
	{{plural:$3|1 Version steht|$3 Versionen stehen}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} Änderungen]) noch zur Prüfung an.',

	'revreview-static' => "Dies ist eine [[Help:Geprüfte Versionen|geprüfte]] Version '''[[:$3|dieser Seite]]''', [{{fullurl:Special:Log/review|page=$1}} freigegeben]
	am <i>$2</i>. Die [{{fullurl:$3|stable=0}} derzeitige Version] kann in der Regel bearbeitet werden und ist aktueller.",

	'revreview-toggle' => '(Details umschalten)',
	'revreview-note'   => '[[{{ns:user}}:$1]] machte die folgende [[Help:Article validation|Prüfnotiz]] zu dieser Version:',

	'hist-stable'  => '[gesichtet]',
	'hist-quality' => '[geprüft]',

	'flaggedrevs'           => 'Markierte Versionen',
	'review-logpage'        => 'Artikel-Prüf-Logbuch',
	'review-logpagetext'    => 'Dies ist das Änderungs-Logbuch der [[Help:Article validation|Seiten-Freigaben]].',
	'review-logentrygrant'  => 'prüfte eine Version von $1',
	'review-logentryrevoke' => 'verwarf eine Version von $1',
	'review-logaction'      => 'Version-ID $1',

	'revisionreview'       => 'Versionsprüfung',
	'revreview-main'       => 'Sie müssen eine Artikelversion zur Prüfung auswählen.

	Siehe [[{{ns:special}}:Unreviewedpages]] für eine Liste ungeprüfter Versionen.',	
	'revreview-selected'   => "Gewählte Version von '''$1:'''",
	'revreview-text'       => "Einer stabilen Version wird bei der Seitendarstellung der Vorzug vor einer neueren Version gegeben.",
	'revreview-toolow'     => 'Sie müssen für jedes der untenstehenden Attribute einen Wert höher als „{{int:revreview-accuracy-0}}“ einstellen,
	damit eine Version als geprüft gilt. Um eine Version zu verwerfen, müssen alle Attribute auf „{{int:revreview-accuracy-0}}“ stehen.',
	'revreview-flag'       => 'Prüfe Version #$1:',
	'revreview-legend'     => 'Inhalt der Version bewerten:',
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
	'stableversions-leg1'   => 'Liste der geprüften Versionen für einen Artikel',
	'stableversions-leg2'   => 'Zeige eine geprüfte Version',
	'stableversions-page'   => 'Artikelname',
	'stableversions-rev'    => 'Versionsnummer:',
	'stableversions-none'   => '[[:$1]] hat keine geprüften Versionen.',
	'stableversions-list'   => 'Dies ist die Liste der geprüften Versionen von [[:$1]]:',
	'stableversions-review' => 'geprüft am <i>$1</i>',

	'review-diff2stable'    => 'Unterschied zur letzten stabilen Version',
	'review-diff2oldest'    => "Unterschied zur ältesten Version",

	'unreviewedpages'       => 'Ungeprüfte Artikel',
	'viewunreviewed'        => 'Liste ungeprüfter Artikel',
	'included-nonquality'   => 'Zeige nur geprüfte Artikel, die noch keine Qualitätsbewertung haben.',
	'unreviewed-list'       => 'Diese Liste enthält Artikel, die noch nicht geprüft wurden.',
);

/* Norwegian (Jon Harald Søby) */
$RevisionreviewMessage['no'] = array(
	'editor'                => 'Redaktør',
	'group-editor'          => 'Redaktøter',
	'group-editor-member'   => 'Redaktør',
	'grouppage-editor'      => '{{ns:project}}:Redaktør',
	'reviewer'              => 'godkjenner',
	'group-reviewer'        => 'godkjennere',
	'group-reviewer-member' => 'godkjenner',
	'grouppage-reviewer'    => '{{ns:project}}:godkjenner',
	'revreview-current'     => 'Nåværende revisjon',
	'revreview-stable'      => 'Stabil versjon',
	'revreview-noflagged'   => 'Det er ingen godkjente revisjoner av denne siden, så den har \'\'\'ikke\'\'\' blitt [[Help:Article validation|kvalitetssjekket]].',
	'revreview-basic'       => 'Dette er den siste [[Help:Article validation|stabile]] revisjonen ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} se alle]) av denne siden, [{{fullurl:Special:Log/review|page={{FULLPAGENAMEE}}}} godkjent] <i>$4</i>. Den [{{fullurl:{{FULLPAGENAMEE}}|stable=false}} nåværende revisjonen] kan vanligvis redigeres, og er mer oppdatert. Det er $3 {{PLURAL:$3|revisjon|revisjoner}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=$2}} endringer]) som venter på godkjenning.',
	'revreview-quality'     => 'Dette er den siste [[Help:Article validation|kvalitetsrevisjonen]] ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} se alle]) av denne siden, [{{fullurl:Special:Log/review|page={{FULLPAGENAMEE}}}} godkjent] <i>$4</i>. Den [{{fullurl:{{FULLPAGENAMEE}}|stable=false}} nåværende revisjonen] kan vanligvis redigeres, og er mer oppdatert. Det er $3 {{PLURAL:$3|revisjon|revisjoner}} ([{{fullurl:{{FULLPAGENAME}}|oldid=$1&diff=$2}} endringer]) som venter på godkjenning.',
	'revreview-static'      => 'Dette er en [[Help:Article validation|godkjent]] revisjon av siden \'\'\'[[$3]]\'\'\', [{{fullurl:Special:Log/review|page=$1}} godkjent] <i>$2</i>. Den [{{fullurl:$3|stable=false}} nåværende revisjonen] er kan vanligvis redigeres, og er mer oppdatert.',
	'revreview-note'        => '[[User:$1]] hadde følgende merknader under [[Help:Article validation|godkjenning]] av denne revisjonen:',
	'hist-stable'           => '[stabil]',
	'hist-quality'          => '[kvalitet]',
	'flaggedrevs'           => 'Flaggede revisjoner',
	'review-logpage'        => 'Artikkelgodkjenningslogg',
	'review-logpagetext'    => 'Dette er en logg over endringer i revisjoner [[Help:Article validation|godkjenningsstatus]] for innholdssider.',
	'review-logentrygrant'  => 'godkjente en versjon av [[$1]]',
	'review-logaction'      => 'revisjon $1',
	'revisionreview'        => 'Godkjenningsstatus',
	'revreview-main'        => 'Du må velge en revisjon fra en innholdsside for å kunne godkjenne den.

Se [[Special:Unreviewedpages]] for en liste over sider uten godkjenning.',
	'revreview-selected'    => 'Valgt revisjon av \'\'\'$1:\'\'\'',
	'revreview-text'        => 'Godkjente revisjoner er satt til standard i stedet for nyeste revisjoner.',
	'revreview-flag'        => 'Godkjenn denne revisjonen (#$1):',
	'revreview-notes'       => 'Merknader:',
	'revreview-accuracy'    => 'Nøyaktighet',
	'revreview-accuracy-0'  => 'Ikke godkjent',
	'revreview-accuracy-1'  => 'Sett',
	'revreview-accuracy-2'  => 'Nøyaktig',
	'revreview-accuracy-3'  => 'Gode kilder',
	'revreview-accuracy-4'  => 'Utmerket',
	'revreview-depth'       => 'Dybde',
	'revreview-depth-0'     => 'Ikke godkjent',
	'revreview-depth-1'     => 'Grunnleggende',
	'revreview-depth-2'     => 'Moderat',
	'revreview-depth-3'     => 'Høy',
	'revreview-depth-4'     => 'Utmerket',
	'revreview-style'       => 'Lesbarhet',
	'revreview-style-0'     => 'Ikke godkjent',
	'revreview-style-1'     => 'Akseptabel',
	'revreview-style-2'     => 'God',
	'revreview-style-3'     => 'Konsis',
	'revreview-style-4'     => 'Utmerket',
	'revreview-log'         => 'Loggkommentar:',
	'stableversions'        => 'Stabile versjoner',
	'stableversions-leg2'   => 'Vis en godkjent revisjon',
	'stableversions-page'   => 'Sidenavn',
	'stableversions-rev'    => 'Revisjons-ID',
	'stableversions-none'   => '[[$1]] har ingen godkjente revisjoner.',
	'stableversions-list'   => 'Følgende er en liste over revisjoner av [[$1]] som har blitt godkjent:',
	'stableversions-review' => 'Godkjent <i>$1</i>',
	'review-diff2stable'    => 'Forskjell fra siste stabile versjon',
	'review-diff2oldest'    => 'Forskjell fra eldste revisjon',
	'unreviewedpages'       => 'Ikke godkjente sider',
	'included-nonquality'   => 'Inkluder godkjente sider som ikke er merket som kvalitet.',
	'unreviewed-list'       => 'Denne sider lister opp artikler som ikke har blitt godkjent enda.',
);

/* Piedmontese (Bèrto 'd Sèra) */
$RevisionreviewMessage['pms'] = array(
	'editor'                => 'Redator',
	'group-editor'          => 'Redator',
	'group-editor-member'   => 'Redator',
	'grouppage-editor'      => '{{ns:project}}:Redator',
	'reviewer'              => 'Revisor',
	'group-reviewer'        => 'Revisor',
	'group-reviewer-member' => 'Revisor',
	'grouppage-reviewer'    => '{{ns:project}}:Revisor',
	'revreview-current'     => 'Version corenta',
	'revreview-stable'      => 'Version stàbila',
	'revreview-oldrating'   => 'A l\'é stait giudicà për:',
	'revreview-noflagged'   => 'A-i é pa gnun-a version revisionà dë sta pàgina-sì, donca a l\'é belfé ch\'a la sia \'\'\'nen\'\'\' staita
	[[Help:Article validation|controlà]] coma qualità.',
	'revreview-quick-see-quality' => '\'\'\'Corenta\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} ùltima version votà për qualità]',
	'revreview-quick-see-basic' => '\'\'\'Corenta\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} ùltima version vardà]',
	'revreview-quick-basic' => '\'\'\'Vardà\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} version corenta] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|modìfica|modìfiche}}])',
	'revreview-quick-quality' => '\'\'\'Qualità\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} version corenta] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|modìfica|modìfiche}}])',
	'revreview-quick-none'  => '\'\'\'Corenta\'\'\'. Pa gnun-a version revisionà.',
	'revreview-newest-basic' => 'L\'[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} ùltima version vardà] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} vardeje tute]) dë sta pàgina-sì a l\'é staita [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovà]
	 dël <i>$2</i>. <br/> A-i {{plural:$3|é|son}} $3 version ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} modìfiche]) ch\'a speto na revision.',
	'revreview-newest-quality' => 'L\'[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} ùltim vot ëd qualità] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} vardeje tuti]) dë sta pàgina-sì a l\'é stait [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovà]
	 dël <i>$2</i>. <br/> A-i {{plural:$3|é|son}} $3 version ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} modìfiche]) ch\'a speto d\'esse revisionà.',
	'revreview-basic'       => 'Costa-sì a l\'é l\'ùltima version [[Help:Article validation|vardà]] dla pàgina, 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovà] dël <i>$2</i>. La [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} version corenta] 
	për sòlit as peul [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} modifichesse] e a l\'é pì agiornà. A-i {{plural:$3|é $3 revision|son $3 version}} 
	([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} modìfiche]) ch\'a speto d\'esse vardà.',
	'revreview-quality'     => 'Costa-sì a l\'é l\'ùltima revision ëd [[Help:Article validation|qualità]] dë sta pàgina, e a l\'é staita
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovà] dël <i>$2</i>. La [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} version corenta] 
	për sòlit as peul [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} modifichesse] e a l\'é pì agiornà. A-i {{plural:$3|é|son}} $3 version 
	([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} modìfiche]) da revisioné.',
	'revreview-static'      => 'Costa a l\'é na version [[Help:Article validation|revisionà]] dë \'\'\'[[:$3|sta pàgina]]\'\'\', 
	[{{fullurl:Special:Log/review|page=$1}} aprovà] dij <i>$2</i>. La [{{fullurl:$3|stable=0}} version corenta] 
	për sòlit as peul modifichesse e a l\'é pì agiornà.',
	'revreview-toggle'      => '(visca/dësmòrta ij detaj)',
	'revreview-note'        => '[[User:$1]] a l\'ha buta-ie ste nòte-sì a la revision, antramentr ch\'a la [[Help:Article validation|controlava]]:',
	'hist-stable'           => '[vardà]',
	'hist-quality'          => '[qualità]',
	'flaggedrevs'           => 'Revision marcà',
	'review-logpage'        => 'Registr dij contròj dj\'artìcoj',
	'review-logpagetext'    => 'Sossì a l\'é un registr dle modìfiche dlë stat d\'[[Help:Article validation|aprovassion]] 
	dle pàgine ëd contnù.',
	'review-logentrygrant'  => 'Na version ëd $1 a l\'é staita vardà',
	'review-logentryrevoke' => 'depressà na version ëd $1',
	'review-logaction'      => 'Nùmer ëd revision $1',
	'revisionreview'        => 'Revisioné le version',
	'revreview-main'        => 'Për podej revisioné a venta ch\'as selession-a na version ëd na pàgina ëd contnù. 

	Ch\'a varda [[Special:Unreviewedpages|da revisioné]] për na lista ëd pàgine ch\'a speto na revision.',
	'revreview-selected'    => 'Version selessionà ëd \'\'\'$1:\'\'\'',
	'revreview-text'        => 'Për sòlit pitòst che nen j\'ùltime, as ësmon-o për contnù le version stàbij.',
	'revreview-toolow'      => 'A venta ch\'a buta tuti j\'atribut ambelessì sota almanch pì àot che "pa aprovà" përché
	na version ës conta da revisionà. Për dëspresié na version ch\'a-i buta tuti ij camp a "pa aprovà".',
	'revreview-flag'        => 'Revisioné sta version (#$1):',
	'revreview-legend'      => 'Deje \'l vot al contnù dla version:',
	'revreview-notes'       => 'Osservation ò nòte da smon-e:',
	'revreview-accuracy'    => 'Cura',
	'revreview-accuracy-0'  => 'Pa aprovà',
	'revreview-accuracy-1'  => 'Vardà',
	'revreview-accuracy-2'  => 'Curà',
	'revreview-accuracy-3'  => 'Bon-e sorgiss',
	'revreview-accuracy-4'  => 'Premià',
	'revreview-depth'       => 'Ancreus',
	'revreview-depth-0'     => 'Pa aprovà',
	'revreview-depth-1'     => 'Mìnim',
	'revreview-depth-2'     => 'Mes',
	'revreview-depth-3'     => 'Bon',
	'revreview-depth-4'     => 'Premià',
	'revreview-style'       => 'Belfé da lese',
	'revreview-style-0'     => 'Pa aprovà',
	'revreview-style-1'     => 'A peul andé',
	'revreview-style-2'     => 'Bon-a',
	'revreview-style-3'     => 'Concisa',
	'revreview-style-4'     => 'Premià',
	'revreview-log'         => 'Coment për ël registr:',
	'revreview-submit'      => 'Buta la revision',
	'stableversions'        => 'Version stàbij',
	'stableversions-leg1'   => 'Fé na lista dle version aprovà ëd na pàgina',
	'stableversions-leg2'   => 'Vardé na version revisionà',
	'stableversions-page'   => 'Nòm dla pàgina',
	'stableversions-rev'    => 'Nùmer ëd version',
	'stableversions-none'   => '[[:$1]] a l\'ha pa gnun-a version revisionà.',
	'stableversions-list'   => 'Costa-sì a l\'é na lista ëd version ëd [[:$1]] ch\'a son ëstaite revisionà:',
	'stableversions-review' => 'Revisionà dël <i>$1</i>',
	'review-diff2stable'    => 'Diferensa da \'nt l\'ùltima version stàbila',
	'review-diff2oldest'    => 'Diferensa da \'nt la revision pì veja',
	'unreviewedpages'       => 'Pàgine dësrevisionà',
	'viewunreviewed'        => 'Lista dle pàgine ëd contnù ch\'a son ëstaite dësrevisionà',
	'included-nonquality'   => 'Smon mach le pàgine già vardà ch\'a son sensa marca ëd qualità.',
	'unreviewed-list'       => 'Costa-sì a l\'é na lista d\'artìcoj ch\'a son anco\' pa stait revisionà.',
);

// Portuguese (Lugusto)
$RevisionreviewMessages['pt'] = array( 
	'editor'              => 'Editor',
	'group-editor'        => 'Editores',
	'group-editor-member' => 'Editor',
	'grouppage-editor'    => '{{ns:project}}:{{int:group-editor}}',

	'reviewer'              => 'Crítico',
	'group-reviewer'        => 'Críticos',
	'group-reviewer-member' => 'Crítico',
	'grouppage-reviewer'    => '{{ns:project}}:{{int:group-reviewer}}',

	'revreview-current'     => 'Edição atual',
	'revreview-stable'      => 'Edição analisada',
	'revreview-noflagged'   => 'Não há edições críticas para esta página; talvez ainda \'\'\'não\'\'\' tenha sido [[{{ns:help}}:Validação de páginas|verificada]] a sua qualidade.',
	'revreview-newest'      => 'A [{{fullurl:Special:stableversions|oldid=$1}} mais recente edição crítica] ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} ver todas]) foi [{{fullurl:Special:LogLog|type=review&page={{FULLPAGENAMEE}}}} aprovada] em <i>$4</i>. {{plural:$3|Existe $3 edição|Existem $3 edições}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} diff]) aguardando por análise. A atual foi avaliada como:',
	'revreview-basic'       => 'Esta é a mais recente edição [[{{ns:help}}:Validação de páginas|crítica]] desta página, [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovada] em <i>$4</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=false}} Edições atuais] talvez sejam [[{{fullurl:{{FULLPAGENAMEE}}|action=edit}} editáveis] e mais atualizadas. {{plural:$3|Existe $3 edição|Existem $3 edições}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=$2}} diff]) aguardando análise.',
	'revreview-quality'     => 'Esta é a mais recente edição [[{{ns:help}}:Validação de páginas|crítica]] desta página, [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovada] em <i>$4</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Edições recentes] talvez sejam [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} editáveis] e mais atualizadas. {{plural:$3|Existe $3 edição|Existem $3 edições}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=$2}} dif]) aguardando análise.',
	'revreview-static'      => 'Esta é uma edição [[{{ns:help}}:Validação de páginas|crítica]] da página \'\'\'[[:$3]]\'\'\', [{{fullurl:Special:Log/review|page=$1}} aprovada] em <i>$2</i>. [{{fullurl:$3|stable=0}} Edições atuais] talvez sejam editáveis e mais atualizadas.',
	'revreview-toggle'      => '(alternar detalhes)',
	'revreview-note'        => '[[{{ns:user}}:$1|$1]] deixou as seguintes observações ao [[{{ns:help}}:Validação de páginas|criticar]] esta edição:',

	'hist-stable'  => '[ed. crítica]',
	'hist-quality' => '[ed. qualificada]',

	'flaggedrevs'           => 'Edições Críticas',
	'review-logpage'        => 'Registo de edições críticas',
	'review-logpagetext'    => 'Este é um registo de alterações de status de páginas de conteúdo com [[{{ns:help}}:Validação de páginas|edições críticas]].',
	'review-logentrygrant'  => 'foi criticada uma edição de $1',
	'review-logentryrevoke' => 'foi rebaixada uma edição de $1',
	'review-logaction'      => 'ID de edição: $1',

	'revisionreview'        => 'Criticar edições',		
	'revreview-main'        => 'Você precisa selecionar uma edição em específico de uma página de conteúdo para poder fazer uma edição crítica.

Veja [[{{ns:special}}:Unreviewedpages]] para uma listagem de páginas ainda não criticadas.',	
	'revreview-selected'   => "Edição selecionada de '''$1:'''",
	'revreview-text'       => "As edições aprovadas são exibidas no lugar de edições mais recentes.",
	'revreview-toolow'     => 'Você precisará criticar em cada um dos atributos com valores mais altos do que "rejeitada" para que uma edição seja considerada aprovada. Para rebaixar uma edição, defina todos os atributos como "rejeitada".',
	'revreview-flag'       => 'Critique esta edição (#$1):',
	'revreview-legend'     => 'Avaliar conteúdo da edição:',
	'revreview-notes'      => 'Observações ou notas a serem exibidas:',
	'revreview-accuracy'   => 'Precisão',
	'revreview-accuracy-0' => 'Rejeitada',
	'revreview-accuracy-1' => 'Objetiva',
	'revreview-accuracy-2' => 'Precisa',
	'revreview-accuracy-3' => 'Bem referenciada',
	'revreview-accuracy-4' => 'Exemplar',
	'revreview-depth'      => 'Profundidade',
	'revreview-depth-0'    => 'Rejeitada',
	'revreview-depth-1'    => 'Básica',		
	'revreview-depth-2'    => 'Moderada',
	'revreview-depth-3'    => 'Alta',
	'revreview-depth-4'    => 'Exemplar',
	'revreview-style'      => 'Inteligibilidade',
	'revreview-style-0'    => 'Rejeitada',
	'revreview-style-1'    => 'Aceitável',
	'revreview-style-2'    => 'Boa',
	'revreview-style-3'    => 'Concisa',
	'revreview-style-4'    => 'Exemplar',
	'revreview-log'        => 'Comentário exibido no registo:',
	'revreview-submit'     => 'Aplicar crítica',

	'stableversions'        => 'Edições Críticas',
	'stableversions-leg1'   => 'Listar edições críticas de uma página',
	'stableversions-leg2'   => 'Ver uma edição crítica',
	'stableversions-page'   => 'Título da página',
	'stableversions-rev'    => 'ID da edição',
	'stableversions-none'   => '[[:$1]] não possui edições críticas.',
	'stableversions-list'   => 'A seguir, uma lista das edições de [[:$1]] que são edições críticas:',
	'stableversions-review' => 'Criticada em <i>$1</i>',

	'review-diff2stable'    => 'Comparar com a edição crítica mais recente',
	'review-diff2oldest'    => "Comparar com a edição mais antiga",

	'unreviewedpages'       => 'Páginas sem edições críticas',
	'viewunreviewed'        => 'Listar páginas de conteúdo que ainda não possuam uma edição crítica',
	'included-nonquality'   => 'Incluir páginas analisadas que não tenham sido marcadas como de qualidade.',
	'unreviewed-list'       => 'Esta página lista as páginas de conteúdo que ainda não receberam uma edição crítica.',
);

// Slovak (Helix84)
$RevisionreviewMessage['sk'] = array(
	'editor'                => 'Redaktor',
	'group-editor'          => 'Redaktori',
	'group-editor-member'   => 'Redaktor',
	'grouppage-editor'      => '{{ns:project}}:Redaktor',
	'reviewer'              => 'Revízor',
	'group-reviewer'        => 'Revízori',
	'group-reviewer-member' => 'Revízor',
	'grouppage-reviewer'    => '{{ns:project}}:Revízor',
	'revreview-current'     => 'Aktuálna revízia',
	'revreview-stable'      => 'Stabilná verzia',
	'revreview-rating'      => 'Bolo ohodnotené ako:',
	'revreview-noflagged'   => 'Neexistujú revidované verzie tejto stránky, takže jej
	kvalita \'\'\'nebola\'\'\' [[Help:Revízia článkov|skontrolovaná]].',
	'revreview-newest'      => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Najnovšia stabilná verzia] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} zobraziť všetky]) tejto stránky bola [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválená]
	 <i>$2</i>. <br/> Na revíziu čaká [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$3|jedna zmena|$3 zmeny|$3 zmien}}].',
	'revreview-basic'       => 'Toto je najnovšia [[Help:Revízia článkov|stabilná]] verzia tejto stránky, 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválená] <i>$4</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Aktuálna verzia] 
	je zvyčajne [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} prístupná úpravám] a aktuálnejšia. 
Na revíziu čaká [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=$2}} {{plural:$3|jedna zmena|$3 zmeny|$3 zmien}}].',
	'revreview-quality'     => 'Toto je najnovšia [[Help:Revízia článkov|kvalitná]] verzia tejto stránky, 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválená] <i>$4</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Aktuálna verzia] 
	je zvyčajne [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} prístupná úpravám] a aktuálnejšia. 
Na revíziu čaká [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=$2}} {{plural:$3|jedna zmena|$3 zmeny|$3 zmien}}].',
	'revreview-static'      => 'Toto je [[Help:Revízia článkov|skontrolovaná]] verzia stránky \'\'\'[[:$3]]\'\'\', 
	[{{fullurl:Special:Log/review|page=$1}} schválená] <i>$2</i>. [{{fullurl:$3|stable=0}} Najnovšia verzia] 
	je zvyčajne prístupná úpravám a aktuálnejšia.',
	'revreview-toggle'      => '(prepnúť zobrazenie podrobností)',
	'revreview-note'        => '[[User:$1]] urobil nasledovné poznámky počas [[Help:Revízia článkov|kontroly]] tejto verzie:',
	'hist-stable'           => '[stabilná]',
	'hist-quality'          => '[kvalitná]',
	'flaggedrevs'           => 'Označené verzie',
	'review-logpage'        => 'Záznam kontrol stránky',
	'review-logpagetext'    => 'Toto je záznam zmien stavu [[Help:Revízia článkov|kontroly]] verzií
	stránok s obsahom.',
	'review-logentrygrant'  => 'skontrolovaná verzia $1',
	'review-logentryrevoke' => 'zastaralá verzia $1',
	'review-logaction'      => 'ID verzie $1',
	'revisionreview'        => 'Prezrieť kontroly',
	'revreview-main'        => 'Musíte vybrať konkrétnu verziu stránky s obsahom, aby ste ju mohli skontrolovať. 

	Pozri zoznam neskontrolovaných stránok
	[[Special:Unreviewedpages]].',
	'revreview-selected'    => 'Zvolená verzia \'\'\'$1:\'\'\'',
	'revreview-text'        => 'Stabilné verzie, nie najnovšie verzie, sú nastavené ako štandardný obsah stránky.',
	'revreview-toolow'      => 'Musíte ohodnotiť každý z nasledujúcich atribútov minimálne vyššie ako "neschválené", aby bolo možné
	verziu považovať za skontrolovanú. Ak chcete učiniť verziu zastaralou, nastavte všetky polia na "neschválené".',
	'revreview-flag'        => 'Skontrolovať túto verziu (#$1):',
	'revreview-legend'      => 'Ohodnotiť obsah verzie:',
	'revreview-notes'       => 'Pozorovania alebo poznámky, ktoré sa majú zobraziť:',
	'revreview-accuracy'    => 'Presnosť',
	'revreview-accuracy-0'  => 'neschválené',
	'revreview-accuracy-1'  => 'zbežná',
	'revreview-accuracy-2'  => 'presná',
	'revreview-accuracy-3'  => 'dobre uvedené zdroje',
	'revreview-accuracy-4'  => 'odporúčaný',
	'revreview-depth'       => 'Hĺbka',
	'revreview-depth-0'     => 'neschválené',
	'revreview-depth-1'     => 'základná',
	'revreview-depth-2'     => 'stredná',
	'revreview-depth-3'     => 'vysoká',
	'revreview-depth-4'     => 'odporúčaný',
	'revreview-style'       => 'Čitateľnosť',
	'revreview-style-0'     => 'neschválené',
	'revreview-style-1'     => 'prijateľná',
	'revreview-style-2'     => 'dobrá',
	'revreview-style-3'     => 'zhustená',
	'revreview-style-4'     => 'odporúčaný',
	'revreview-log'         => 'Komentár záznamu:',
	'revreview-submit'      => 'Aplikovať kontrolu',
	'stableversions'        => 'Stabilné verzie',
	'stableversions-leg1'   => 'Zoznam skontrolovaných verzií stránky',
	'stableversions-leg2'   => 'Zobraziť skontrolovanú verziu',
	'stableversions-page'   => 'Názov stránky',
	'stableversions-rev'    => 'ID verzie',
	'stableversions-none'   => '[[:$1]] nemá skontrolované verzie.',
	'stableversions-list'   => 'Nasleduje zoznam verzií stránky [[:$1]], ktoré boli skontrolované:',
	'stableversions-review' => 'Skontrolované <i>$1</i>',
	'review-diff2stable'    => 'Rozdiely oproti poslednej stabilnej verzii',
	'review-diff2oldest'    => 'Rozdiely oproti najstaršej verzii',
	'unreviewedpages'       => 'Neskontrolované stránky',
	'viewunreviewed'        => 'Zoznam neskontrolovaných stránok s obsahom',
	'included-nonquality'   => 'Vrátane skontrolovaných stránok neoznačených ako kvalitné.',
	'unreviewed-list'       => 'Táto stránka obsahuje zoznam článkov, ktoré zatiaľ neboli skontrolované.',
);

