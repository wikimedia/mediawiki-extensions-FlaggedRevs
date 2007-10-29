<?php

$messages = array(
	'makevalidate-autosum'      => 'autopromocionado',
	'editor'                    => 'Editor',#identical but defined
	'group-editor'              => 'Editores',
	'group-editor-member'       => 'Editor',#identical but defined
	'grouppage-editor'          => '{{ns:project}}:Editor',#identical but defined
	'reviewer'                  => 'Revisor',
	'group-reviewer'            => 'Revisores',
	'group-reviewer-member'     => 'Revisor',
	'grouppage-reviewer'        => '{{ns:project}}:Revisor',
	'revreview-current'         => 'Proxecto',
	'tooltip-ca-current'        => 'Ver o proxecto actual desta páxina',
	'revreview-edit'            => 'Editar proxecto',
	'revreview-source'          => 'Fontes do proxecto',
	'revreview-stable'          => 'Estábel',
	'tooltip-ca-stable'         => 'Ver a versión estábel desta páxina',
	'revreview-oldrating'       => 'Foi valorado:',
	'revreview-noflagged'       => 'Non hai revisións examinadas desta páxina,  polo que pode que \'\'\'non\'\'\' foran [[Help:Article validation|revisadas]] na súa calidade.',
	'stabilization-tab'         => '(qa)',#identical but defined
	'tooltip-ca-default'        => 'Configuración de garantía da calidade',
	'revreview-quick-none'      => '\'\'\'Actualización\'\'\'. Non examinou as revisións.',
	'revreview-quick-see-quality' => '\'\'\'Actualización\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} véxase revisión estábel]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|change|cambios}}])',
	'revreview-quick-see-basic' => '\'\'\'Actualización\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} véxase revisión estábel]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|change|cambios}}])',
	'revreview-quick-basic'     => '\'\'\'[[Help:Article validation|Sighted]]\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} véxase revisión actual]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|change|cambios}}])',
	'revreview-quick-quality'   => '\'\'\'[[Help:Article validation|Calidade]]\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} véxase revisión actual]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|change|cambios}}])',
	'revreview-newest-quality'  => 'A [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} última revisión de calidade] 
	([{{fullurl:Special:Stableversions|páxina={{FULLPAGENAMEE}}}} de toda a listaxe]) foi [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprobada]
	 en <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|change|cambios}}] {{plural:$3|needs|precisan}} revisión.',
	'revreview-quality'         => 'Esta é a última revisión de [[Help:Article validation|calidade]], 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprobada] en <i>$2</i>. A [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} revisión actual] 
	pode ser [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} modificada]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|change|cambios}}] 
	{{plural:$3|awaits|agardan}} ser revisados.',
	'revreview-static'          => 'Esta é a revisión [[Help:Article validation|examinada]] de \'\'\'[[:$3|$3]]\'\'\', 
	[{{fullurl:Special:Log/review|page=$1}} aprobada] en <i>$2</i>. A [{{fullurl:$3|stable=0}} revisión actual] 
	pode ser [{{fullurl:$3|action=edit}} modificada].',
	'revreview-toggle'          => '(+/-)',#identical but defined
	'revreview-note'            => '[[User:$1]] fixo as seguintes notas [[Help:Article validation|examinando]] esta revisión:',
	'revreview-update'          => 'Revise os cambios (amósanse embaixo) feitos desde a revisión estábel desta páxina. Os seguintes
modelos
	e imaxes tamén foron actualizados:',
	'revreview-update-none'     => 'Revise os cambios (amósanse embaixo) feitos desde a revisión estábel desta páxina.',
	'revreview-auto'            => '(automático)',
	'revreview-auto-w'          => 'Vostede está editando unha revisión estábel, calquera cambio \'\'\'será automaticamente revisado\'\'\'.
	Se o desexa pode obter unha vista previa da páxina antes de gardala.',
	'revreview-auto-w-old'      => 'Vostede está editando unha revisión vella, calquera cambio \'\'\'será automaticamente revisado\'\'\'.
	Se o desexa pode obter unha vista previa da páxina antes de gardala.',
	'hist-quality'              => '[calidade]',
	'flaggedrevs'               => 'Revisións de pabellón',
	'review-logpage'            => 'Rexistro de revisións do artigo',
	'review-logpagetext'        => 'Este é un rexistro dos cambios para as revisións de [[Help:Article validation|aprobación]] do status
	para o contido das páxinas.',
	'review-logentry-app'       => 'revisados [[$1]]',
	'review-logaction'          => 'revisión ID $1',
	'stable-logpage'            => 'Rexistro de versión estábeis',
	'stable-logpagetext'        => 'Este é un rexistro dos cambios de [[Help:Article validation|versión estábel]]
	de configuración do contido das páxinas.',
	'revisionreview'            => 'Examinar revisións',
	'revreview-selected'        => 'Seleccionada a revisión de \'\'\'$1:\'\'\'',
	'revreview-flag'            => 'Examinada esta revisión (#$1)',
	'revreview-depth-1'         => 'Básico',
	'revreview-depth-2'         => 'Moderado',
	'revreview-depth-3'         => 'Alto',
	'unreviewedpages'           => 'Páxinas sen revisar',
	'unreviewed-category'       => 'Categoría:',
	'unreviewed-diff'           => 'Cambios',
	'unreviewed-list'           => 'Esta páxina lista artigos que non foron revisados ou que teñen novas revisións sen examinar.',
	'stabilization'             => 'Estabilización da páxina',
	'stabilization-page'        => 'Nome da páxina:',
	'stabilization-leg'         => 'Configurar a versión estábel para a páxina',
);
