<?php
$messages = array(
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
	'revreview-noflagged'       => 'Non hai revisións examinadas desta páxina,  polo que pode que \'\'\'non\'\'\' foran [[{{MediaWiki:Makevalidate-page}}|revisadas]] na súa calidade.',
	'stabilization-tab'         => '(qa)',#identical but defined
	'tooltip-ca-default'        => 'Configuración de garantía da calidade',
	'revreview-quick-none'      => '\'\'\'Actualización\'\'\'. Non examinou as revisións.',
	'revreview-quick-see-quality' => '\'\'\'Actualización\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} véxase revisión estábel]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|change|cambios}}])',
	'revreview-quick-see-basic' => '\'\'\'Actualización\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} véxase revisión estábel]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|change|cambios}}])',
	'revreview-quick-basic'     => '\'\'\'[[{{MediaWiki:Makevalidate-page}}|Sighted]]\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} véxase revisión actual]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|change|cambios}}])',
	'revreview-quick-quality'   => '\'\'\'[[{{MediaWiki:Makevalidate-page}}|Calidade]]\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} véxase revisión actual]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|change|cambios}}])',
	'revreview-newest-quality'  => 'A [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} última revisión de calidade] 
	([{{fullurl:Special:Stableversions|páxina={{FULLPAGENAMEE}}}} de toda a listaxe]) foi [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprobada]
	 en <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|change|cambios}}] {{plural:$3|needs|precisan}} revisión.',
	'revreview-quality'         => 'Esta é a última revisión de [[{{MediaWiki:Makevalidate-page}}|calidade]], 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprobada] en <i>$2</i>. A [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} revisión actual] 
	pode ser [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} modificada]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|change|cambios}}] 
	{{plural:$3|awaits|agardan}} ser revisados.',
	'revreview-static'          => 'Esta é a revisión [[{{MediaWiki:Makevalidate-page}}|examinada]] de \'\'\'[[:$3|$3]]\'\'\', 
	[{{fullurl:Special:Log/review|page=$1}} aprobada] en <i>$2</i>. A [{{fullurl:$3|stable=0}} revisión actual] 
	pode ser [{{fullurl:$3|action=edit}} modificada].',
	'revreview-toggle'          => '(+/-)',#identical but defined
	'revreview-note'            => '[[User:$1]] fixo as seguintes notas [[{{MediaWiki:Makevalidate-page}}|examinando]] esta revisión:',
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
	'flaggedrevs'               => 'Revisións marcadas',
	'review-logpage'            => 'Rexistro de revisións do artigo',
	'review-logpagetext'        => 'Este é un rexistro dos cambios para as revisións de [[{{MediaWiki:Makevalidate-page}}|aprobación]] do status
	para o contido das páxinas.',
	'review-logentry-app'       => 'revisados [[$1]]',
	'review-logentry-dis'       => 'devaluada a versión de [[$1]]',
	'review-logaction'          => 'revisión ID $1',
	'stable-logpage'            => 'Rexistro de versión estábeis',
	'stable-logpagetext'        => 'Este é un rexistro dos cambios de [[{{MediaWiki:Makevalidate-page}}|versión estábel]]
	de configuración do contido das páxinas.',
	'stable-logentry'           => 'configurada a versión estábel para [[$1]]',
	'stable-logentry2'          => 'resetear a versión estábel para [[$1]]',
	'revisionreview'            => 'Examinar revisións',
	'revreview-main'            => 'Vostede debe seleccionar unha revisión particular dun contido da páxina de cara á revisión.

	Vexa a [[Special:Unreviewedpages]] para a listaxe de páxinas sen revisar.',
	'revreview-selected'        => 'Seleccionada a revisión de \'\'\'$1:\'\'\'',
	'revreview-text'            => 'As versións estábeis son o contido predefinido ao ver unha páxina en vez da revisión máis recente.',
	'revreview-flag'            => 'Examinada esta revisión (#$1)',
	'revreview-legend'          => 'Valorar o contido da revisión',
	'revreview-notes'           => 'Observacións ou notas para amosar:',
	'revreview-accuracy'        => 'Exactitude',
	'revreview-accuracy-0'      => 'Sen aprobar',
	'revreview-accuracy-2'      => 'Exacto',
	'revreview-accuracy-3'      => 'Ben documentado',
	'revreview-accuracy-4'      => 'Destacado',
	'revreview-depth'           => 'Profundidade',
	'revreview-depth-0'         => 'Sen aprobar',
	'revreview-depth-1'         => 'Básico',
	'revreview-depth-2'         => 'Moderado',
	'revreview-depth-3'         => 'Alto',
	'revreview-depth-4'         => 'Destacado',
	'revreview-style'           => 'Lexibilidade',
	'revreview-style-0'         => 'Sen aprobar',
	'revreview-style-1'         => 'Aceptábel',
	'revreview-style-2'         => 'Bon',
	'revreview-style-3'         => 'Conciso',
	'revreview-style-4'         => 'Destacado',
	'revreview-log'             => 'Comentario para o rexistro:',
	'revreview-submit'          => 'Enviar revisión',
	'stableversions'            => 'Versións estábeis',
	'stableversions-leg1'       => 'Listar as revisións revisadas dunha páxina',
	'stableversions-page'       => 'Nome da páxina:',
	'stableversions-none'       => '"[[:$1]]" non ten revisións examinadas.',
	'stableversions-list'       => 'A seguinte é unha listaxe das revisións de "[[:$1]]" que foron revisadas:',
	'stableversions-review'     => 'Revisado en <i>$1</i> por $2',
	'review-diff2stable'        => 'Diff coa última revisión estábel',
	'review-diff2oldest'        => 'Diff coa revisión máis antiga',
	'unreviewedpages'           => 'Páxinas sen revisar',
	'viewunreviewed'            => 'Listaxe de páxinas de contido sen revisar',
	'unreviewed-outdated'       => 'Amosar páxinas que teñen revisións sen examinar feitas desde a versión estábel.',
	'unreviewed-category'       => 'Categoría:',
	'unreviewed-diff'           => 'Cambios',
	'unreviewed-list'           => 'Esta páxina lista artigos que non foron revisados ou que teñen novas revisións sen examinar.',
	'revreview-visibility'      => 'Esta páxina ten unha [[{{MediaWiki:Makevalidate-page}}|versión estábel]], a cal pode ser
	[{{fullurl:Special:Stabilization|page={{FULLPAGENAMEE}}}} configurada].',
	'stabilization'             => 'Estabilización da páxina',
	'stabilization-text'        => 'Mude a configuración a continuación para axustar a forma na que a versión estábel de [[:$1|$1]] foi seleccionada e se amosa.',
	'stabilization-perm'        => 'A súa conta non ten permisos para mudar a configuración da versión estábel.
	Esta é a configuración actual para [[:$1|$1]]:',
	'stabilization-page'        => 'Nome da páxina:',
	'stabilization-leg'         => 'Configurar a versión estábel para a páxina',
	'stabilization-select'      => 'Como foi seleccionada a versión estábel',
	'stabilization-select1'     => 'A última revisión de calidade; se non se atopa, entón a vista máis recente.',
	'stabilization-select2'     => 'A última revisión vista',
	'stabilization-def'         => 'Revisión que aparece por defecto na vista da páxina',
	'stabilization-def1'        => 'A revisión estábel, se non presente, entón a actual',
	'stabilization-def2'        => 'A revisión actual',
	'stabilization-submit'      => 'Confirmar',
	'stabilization-notexists'   => 'Non hai unha páxina chamada "[[:$1|$1]]". A non configuración é posíbel.',
	'stabilization-notcontent'  => 'A páxina "[[:$1|$1]]" non pode ser revisada. A non configuración é posíbel.',
	'stabilization-success'     => 'A configuración da versión estábel para [[:$1|$1]] estableceuse con éxito.',
	'stabilization-sel-short'   => 'Precedencia',
	'stabilization-sel-short-0' => 'Calidade',
	'stabilization-sel-short-1' => 'Ningún',
	'stabilization-def-short'   => 'Por defecto',
	'stabilization-def-short-0' => 'Actual',
	'stabilization-def-short-1' => 'Estábel',
	'reviewedpages'             => 'Páxinas revisadas',
	'reviewedpages-leg'         => 'Listaxe das páxinas revisadas a un certo nivel',
	'reviewedpages-list'        => 'As seguintes páxinas foron revisadas a un nivel específico',
	'reviewedpages-none'        => 'Non hai páxinas nesta listaxe',
	'reviewedpages-lev-1'       => 'Calidade',
	'reviewedpages-all'         => 'Versións revisadas',
);
