<?php
$RevisionreviewMessages = array();

// English (Aaron Schulz)
$RevisionreviewMessages['en'] = array( 
	'editor'              => 'Editor',
	'group-editor'        => 'Editors',
	'group-editor-member' => 'Editor',
	'grouppage-editor'    => '{{ns:project}}:Editor',

	'reviewer'              => 'Reviewer',
	'group-reviewer'        => 'Reviewers',
	'group-reviewer-member' => 'Reviewer',
	'grouppage-reviewer'    => '{{ns:project}}:Reviewer',

	'revreview-current'     => 'Current revision',
	'revreview-noflagged' => 'There are no reviewed revisions of this page, so it may \'\'\'not\'\'\' have been 
	[[Help:Article validation|checked]] for quality.',
	'revreview-newest'    => 'The [{{fullurl:Special:stableversions|oldid=$1}} latest reviewed revision] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} see all]) was [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approved]
	 on <i>$2</i>. <br/> There {{plural:$3|is $3 revision|are $3 revisions}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} changes]) awaiting review. It is rated as:',
	'revreview-basic'  => 'This is the latest [[Help:Article validation|stable]] revision of this page, 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approved] on <i>$4</i>. The [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} current revision] 
	is usually [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} editable] and more up to date. There {{plural:$3|is $3 revision|are $3 revisions}} 
	([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=$2}} changes]) awaiting review.',
	'revreview-quality'  => 'This is the latest [[Help:Article validation|quality]] revision of this page, 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approved] on <i>$4</i>. The [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} current revision] 
	is usually [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} editable] and more up to date. There {{plural:$3|is $3 revision|are $3 revisions}} 
	([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=$2}} changes]) awaiting review.',
	'revreview-static'  => 'This is a [[Help:Article validation|reviewed]] revision of the page \'\'\'[[:$3]]\'\'\', 
	[{{fullurl:Special:Log/review|page=$1}} approved] on <i>$2</i>. The [{{fullurl:$3|stable=0}} current revision] 
	is usually editable and more up to date.',
	'revreview-toggle' => '(toggle details)',
	'revreview-note' => '[[User:$1]] made the following notes [[Help:Article validation|reviewing]] this revision:',

	'hist-stable'  => '[stable]',
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

	See the [[Special:Unreviewedpages]] for a list of 
	unreviewed pages.',	
	'revreview-selected'   => "Selected revision of '''$1:'''",
	'revreview-text'       => "Approved revisions are set as the default content on page view rather than the newest
	 revision.",
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
	'revreview-submit'     => 'Apply review',

	'stableversions'        => 'Stable versions',
	'stableversions-leg1'   => 'List reviewed revisions for a page',
	'stableversions-leg2'   => 'View a reviewed revision',
	'stableversions-page'   => 'Page name',
	'stableversions-rev'    => 'Revision ID',
	'stableversions-none'   => '[[:$1]] has no reviewed revisions.',
	'stableversions-list'   => 'The following is a list of revisions of [[:$1]] that have been reviewed:',
	'stableversions-review' => 'Reviewed on <i>$1</i>',
	'stableversions-quality' => '[Latest quality revision]',

    'review-diff2stable'    => 'Diff to the last stable revision',
    'review-diff2oldest'    => "Diff to the oldest revision",

    'unreviewedpages'       => 'Unreviewed pages',
    'viewunreviewed'        => 'List unreviewed content pages',
    'included-nonquality'   => 'Include reviewed pages not marked as quality.',
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

	'revreview-noflagged' => 'Von dieser Seite gibt es keine geprüften Versionen, so dass noch keine Aussage über die [[Help:Article validation|Artikelqualität]]
	gemacht werden kann.',

	'revreview-newest'    => 'Die [{{fullurl:Special:stableversions|oldid=$1}} letzte geprüfte Version]
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} siehe alle]) wurde [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben] am <i>$2</i>.
	{{plural:$3|1 Version steht|$3 Versionen stehen}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} Änderungen]) noch zur Prüfung an. Bewertung:',

	'revreview-basic'  => 'Dies ist die letzte [[Help:Article validation|stabile]] Version dieser Seite,
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben] am <i>$4</i>. Die [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} derzeitige Version]
	kann in der Regel [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} bearbeitet] werden und ist aktueller. {{plural:$3|1 Version steht|$3 Versionen stehen}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=$2}} Änderungen])
	noch zur Prüfung an.',

	'revreview-quality'  => 'Das ist für diesen Artikel die letzte Version mit [[Help:Article validation|Qualitätsbewertung]]
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben] am <i>$4</i>.
	Die [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} derzeitige Version] kann in der Regel [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} bearbeitet] werden und ist aktueller.
	{{plural:$3|1 Version steh|$3 Versionen stehen}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=$2}} Änderungen]) noch zur Prüfung an.',

	'revreview-static'  => "Dies ist eine [[Help:Article validation|geprüfte]] Version der Seite '''„[[:$3]]“''', [{{fullurl:Special:Log/review|page=$1}} freigegeben]
	am <i>$2</i>. Die [{{fullurl:$3|stable=0}} derzeitige Version] kann in der Regel bearbeitet werden und ist aktueller.",
	'revreview-toggle' => '(Details umschalten)',
	'revreview-note' => '[[{{ns:user}}:$1]] machte die folgende [[Help:Article validation|Prüfnotiz]] zu dieser Version:',

	'hist-stable'  => '[gesichtet]',
	'hist-quality' => '[geprüft]',

	'flaggedrevs'        => 'Markierte Versionen',
	'review-logpage'     => 'Artikel-Prüf-Logbuch',
	'review-logpagetext' => 'Dies ist das Änderungs-Logbuch der [[Help:Article validation|Seiten-Freigaben]].',
	'review-logentrygrant'   => 'prüfte eine Version von $1',
	'review-logentryrevoke'  => 'verwarf eine Version von $1',
	'review-logaction'  => 'Version-ID $1',

	'revisionreview'       => 'Versionsprüfung',		
	'revreview-main'       => 'Sie müssen eine Artikelversion zur Prüfung auswählen.

	Siehe [[{{ns:special}}:Unreviewedpages]] für eine Liste ungeprüfter Versionen.',	
	'revreview-selected'   => "Gewählte Version von '''$1:'''",
	'revreview-text'       => "Einer freigegebenen Version wird bei der Seitendarstellung der Vorzug vor einer neueren Version gegeben.",
	'revreview-toolow'     => 'Sie müssen für jedes der untenstehenden Attribute einen Wert höher als „{{int:revreview-accuracy-0}}“ einstellen,
	damit eine Version als geprüft gilt. Um eine Version zu verwerfen, müssen alle Attribute auf „{{int:revreview-accuracy-0}}“ stehen.',
	'revreview-flag'       => 'Prüfe Version #$1:',
	'revreview-legend'     => 'Inhalt der Version bewerten:',
	'revreview-notes'      => 'Anzuzeigende Bemerkungen oder Notizen:',
	'revreview-accuracy'   => 'Genauigkeit',
	'revreview-accuracy-0' => 'nicht freigegeben',
	'revreview-accuracy-1' => 'gesichtet',
	'revreview-accuracy-2' => 'sorgfältig',
	'revreview-accuracy-3' => 'Quellen geprüft',
	'revreview-accuracy-4' => 'exzellent',
	'revreview-depth'      => 'Tiefe',
	'revreview-depth-0'    => 'nicht freigegeben',
	'revreview-depth-1'    => 'einfach',		
	'revreview-depth-2'    => 'mittel',
	'revreview-depth-3'    => 'hoch',
	'revreview-depth-4'    => 'exzellent',
	'revreview-style'      => 'Lesbarkeit',
	'revreview-style-0'    => 'nicht freigegeben',
	'revreview-style-1'    => 'akzeptabel',
	'revreview-style-2'    => 'gut',
	'revreview-style-3'    => 'präzise',
	'revreview-style-4'    => 'exzellent',
	'revreview-log'        => 'Logbuch-Eintrag:',
	'revreview-submit'     => 'Prüfung speichern',

	'stableversions'        => 'Stabile Versionen',
	'stableversions-leg1'   => 'Liste der geprüften Versionen für einen Artikel',
	'stableversions-leg2'   => 'Zeige eine geprüfte Version',
	'stableversions-page'   => 'Artikelname',
	'stableversions-rev'    => 'Versionsnummer:',
	'stableversions-none'   => '[[:$1]] hat keine geprüften Versionen.',
	'stableversions-list'   => 'Dies ist die Liste der geprüften Versionen von [[:$1]]:',
	'stableversions-review' => 'geprüft am <i>$1</i>',
	'stableversions-quality' => '[Letzte Version mit Qualitätsbewertung]',

	'review-diff2stable'    => 'Unterschied zur letzten stabilen Version',
	'review-diff2oldest'    => "Unterschied zur ältesten Version",

	'unreviewedpages'       => 'Ungeprüfte Artikel',
	'viewunreviewed'        => 'Liste ungeprüfter Artikel',
	'included-nonquality'   => 'Schließe geprüfte Artikel ein, die keine Qualitätsbewertung haben.',
	'unreviewed-list'       => 'Diese Liste enthält Artikel, die noch nicht geprüft wurden.',
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

	'revreview-noflagged' => 'Não há edições críticas para esta página; talvez ainda \'\'\'não\'\'\' tenha sido [[{{ns:help}}:Validação de páginas|verificada]] a sua qualidade.',
	'revreview-newest'    => 'A [{{fullurl:Special:stableversions|oldid=$1}} mais recente edição crítica] ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$2&diff=$3}} dif]) ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} ver todas]) foi [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovada] em <i>$4</i>, avaliada como:',
	'revreview-basic'  => 'Esta é a mais recente edição [[{{ns:help}}:Validação de páginas|crítica]] ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} veja todas]) desta página, [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovada] em <i>$4</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=false}} Edições atuais] talvez sejam editáveis e mais atualizadas. Existem {{plural:$3|$3 edição|$3 edições}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=$2}} dif]) aguardando análise.',
	'revreview-quality'  => 'Esta é a mais recente edição [[{{ns:help}}:Validação de páginas|crítica]] ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} veja todas]) desta página, [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovada] em <i>$4</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Edições recentes] talvez sejam editáveis e mais atualizadas. Existem {{plural:$3|$3 edição|$3 edições}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=$2}} dif]) aguardando análise.',
	'revreview-static'  => 'Esta é uma edição [[{{ns:help}}:Validação de páginas|crítica]] da página \'\'\'[[:$3]]\'\'\', [{{fullurl:Special:Log/review|page=$1}} aprovada] em <i>$2</i>. [{{fullurl:$3|stable=0}} Edições atuais] talvez sejam editáveis e mais atualizadas.',
	'revreview-note' => '[[{{ns:user}}:$1|$1]] deixou as seguintes observações ao [[{{ns:help}}:Validação de páginas|criticar]] esta edição:',

	'hist-stable'  => '[ed. crítica]',
	'hist-quality' => '[ed. qualificada]',

    'flaggedrevs'        => 'Edições Críticas',
    'review-logpage'     => 'Registo de edições críticas',
	'review-logpagetext' => 'Este é um registo de alterações de status de páginas de conteúdo com [[{{ns:help}}:Validação de páginas|edições críticas]].',
	'review-logentrygrant'   => 'foi criticada uma edição de $1',
	'review-logentryrevoke'  => 'foi rebaixada uma edição de $1',
	'review-logaction'  => 'edição $1',

    'revisionreview'       => 'Criticar edições',		
    'revreview-main'       => 'Você precisa selecionar uma edição em específico de uma página de conteúdo para poder fazer uma edição crítica.

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
	'revreview-submit'     => 'Aplicar à edição selecionada',

	'stableversions'        => 'Edições Críticas',
	'stableversions-leg1'   => 'Listar edições críticas de uma página',
	'stableversions-leg2'   => 'Ver uma edição crítica',
	'stableversions-page'   => 'Título da página',
	'stableversions-rev'    => 'ID da edição',
	'stableversions-none'   => '[[:$1]] não possui edições críticas.',
	'stableversions-list'   => 'A seguir, uma lista das edições de [[:$1]] que são edições críticas:',
	'stableversions-review' => 'Criticada em <i>$1</i>',
	'stableversions-quality' => '[Edição mais recente marcada como de qualidade]',

    'review-diff2stable'    => 'Comparar com a edição crítica mais recente',
    'review-diff2oldest'    => "Comparar com a edição mais antiga",

    'unreviewedpages'       => 'Páginas sem edições críticas',
    'viewunreviewed'        => 'Listar páginas de conteúdo que ainda não possuam uma edição crítica',
    'included-nonquality'   => 'Incluir páginas analisadas que não tenham sido marcadas como de qualidade.',
    'unreviewed-list'       => 'Esta página lista as páginas de conteúdo que ainda não receberam uma edição crítica.',
);
?>
