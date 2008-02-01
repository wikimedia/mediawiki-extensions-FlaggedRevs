<?php
/** Yakut (Саха тыла)
 * @author HalanTul
 */
$messages = array(
	'editor'                      => 'Көннөрөөччү',
	'group-editor'                => 'Көннөрөөччүлэр',
	'group-editor-member'         => 'көннөрөөччү',
	'grouppage-editor'            => '{{ns:project}}:Көннөрөөччү',
	'reviewer'                    => 'Рецензент',
	'group-reviewer'              => 'Рецензеннар',
	'group-reviewer-member'       => 'рецензент',
	'grouppage-reviewer'          => '{{ns:project}}:Рецензент',
	'revreview-current'           => 'Харата (черновик)',
	'tooltip-ca-current'          => 'Сирэй саҥа (бүтэһик) черновигын көрдөр',
	'revreview-edit'              => 'Черновигы уларытыы',
	'revreview-source'            => 'черновик бастакы торума',
	'revreview-stable'            => 'Чистовик',
	'tooltip-ca-stable'           => 'Бу сирэй чистовигын көрүү',
	'revreview-oldrating'         => 'Сыаналаммыт:',
	'revreview-noflagged'         => "Бу сирэй ырытыллыбыт торума суох, арааһа кини хаачыстыбата [[{{MediaWiki:Validationpage}}|'''сыаналамматах''']] быһыылаах.",
	'tooltip-ca-default'          => 'Хаачыстыба хонтуруолун туруоруулара',
	'validationpage'              => '{{ns:help}}:Ыстатыйа бэрэбиэркэтэ',
	'revreview-quick-none'        => "'''Бүтэһик торум''' (ырытыллыбыт торума суох)",
	'revreview-quick-see-quality' => "'''Черновик''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} чистовигын көр]]  
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|көннөрүүлээх|көннөрүүлэрдээх}}])",
	'revreview-quick-see-basic'   => "'''Черновик''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} чистовигын көр]]  
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|көннөрүүлээх|көннөрүүлэрдээх}}])",
	'revreview-quick-basic'       => "'''[[{{MediaWiki:Validationpage}}|Көрүллүбүт]]''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} черновигын көр]]   
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|көннөрүүлээх|көннөрүүлэрдээх}}])",
	'revreview-quick-quality'     => "'''[[{{MediaWiki:Validationpage}}|Кичэйэн көрүллүбүт]]''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} черновигын көр]]   
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|көннөрүүлээх|көннөрүүлэрдээх}}])",
	'revreview-newest-basic'      => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Бүтэһик бэрэбиэркэлэммит торума]   
([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} торумнар испииһэктэрэ]) [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} бэлиэтэммит]
<i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 көннөрүү {{plural:$3|көрүллүөхтээх|көрүллүөхтээхтэр}}].',
	'revreview-newest-quality'    => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Бүтэһик кичэйэн көрүллүбүт торума]   
([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} торумнар испииһэктэрэ]) [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} бэлиэтэммит]
<i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 көннөрүү {{plural:$3|көрүллүөхтээх|көрүллүөхтээхтэр}}].',
	'revreview-update'            => 'Бука диэн манна аллара бэриллибит чистовик статуһун биэрии кэннэ оҥоһуллубут уларытыылары бэрэбиэркэлээ (ханныгы баҕараргын). Сорох халыыптар уонна ойуулар саҥардыллыбыттар:',
	'revreview-update-none'       => 'Бука диэн чистовой торум кэнниттэн оноһуллубут уларытыылары (аллара бааллар) көр.',
	'revreview-auto'              => '(аптамаатынан)',
	'revreview-auto-w'            => "Чистовой торуму уларытан эрэҕин, туох баар уларытыылар '''аптамаатынан бэрэбиэркэлэммит курдук бэлиэтэниэхтэрэ'''. Уларытыыны бигэргэтиэх иннинэ хайдах буолуохтааҕын көрөр ордук буолуо.",
	'revreview-auto-w-old'        => "Эргэрбит торуму уларытан эрэҕин, туох баар уларытыылар '''аптамаатынан бэрэбиэркэлэммит курдук бэлиэтэниэхтэрэ'''. Уларытыыны бигэргэтиэх иннинэ хайдах буолуохтааҕын көрөр ордук буолуо.",
	'revreview-patrolled'         => 'Талбыт торумуҥ [[:$1|$1]] бэрэбиэркэлэммит курдук бэлиэтэннэ.',
	'hist-stable'                 => '[торум көрүлүннэ/көрүллүбүт]',
	'hist-quality'                => '[үрдүк хаачыстыбалаах торум]',
	'flaggedrevs'                 => 'Бэлиэтэммит торумнар',
	'review-logpage'              => 'Рецензиялар сурунааллара',
	'review-logpagetext'          => 'Бу сирэйдэр торумнарын [[{{MediaWiki:Validationpage}}|бигэргэтиллибит]] уларытыыларын сурунаала.',
	'review-logentry-app'         => 'ырытыллынна/ырытыллыбыт [[$1]]',
	'review-logentry-dis'         => '[[$1]] эргэрбит торума',
	'review-logaction'            => '$1 торумун идентификатора',
	'stable-logpage'              => 'Бүтэһик (чистовой) торумнар сурунааллара',
	'stable-logpagetext'          => 'Бу бүтэһик [[{{MediaWiki:Validationpage}}|бигэргэтиллибит]] торумнар туруорууларын уларытыы сурунаала.',
	'revisionreview'              => 'Торумнары ырытыы',
	'revreview-main'              => 'Ырытарга сирэй биир эмит торумун талыахтааххын. 

Ырытыллыбатах сирэйдэри [[Special:Unreviewedpages|манна]] көр.',
	'revreview-selected'          => "'''$1''' талыллыбыт торума:",
	'revreview-text'              => 'Анаан туруоруллубатаҕына чистовой торумнар көстөллөр (саҥа, хойукку торумнар буолбатах).',
	'revreview-flag'              => '(#$1) торуму ырытыы',
	'revreview-legend'            => 'Торум ис хоһоонун сыаналааһын',
	'revreview-notes'             => 'Көрдөрүллэр кэтээһиннэр уонна самычаанньалар:',
	'revreview-accuracy'          => 'Чопчута',
	'revreview-accuracy-0'        => 'Бигэргэтиллибэтэх',
	'revreview-accuracy-1'        => 'Көрүллүбүт',
	'revreview-accuracy-2'        => 'Чопчу',
	'revreview-accuracy-3'        => 'Источниктардаах',
	'revreview-accuracy-4'        => 'Талыы-талба',
	'revreview-depth'             => 'Толорута',
	'revreview-depth-0'           => 'Бигэргэтиллибэтэх',
	'revreview-depth-1'           => 'олоҕо баар',
	'revreview-depth-2'           => 'Орто',
	'revreview-depth-3'           => 'Толору',
	'revreview-depth-4'           => 'Талыы-талба',
	'revreview-style'             => 'Ааҕарга табыгастааҕа',
	'revreview-style-0'           => 'Бигэргэтиллибэтэх',
	'revreview-style-1'           => 'Син аҕай',
	'revreview-style-2'           => 'Үчүгэй',
	'revreview-style-3'           => 'Кылгас',
	'revreview-style-4'           => 'Уһулуччу үчүгэй',
	'revreview-log'               => 'Ырытыы:',
	'revreview-submit'            => 'Ырытыыны ыыт',
	'stableversions'              => 'Чистовые версии',
	'stableversions-title'        => '"$1" чистовой торумнара',
	'stableversions-leg1'         => 'Сирэй ырытыллыбыт торумнарын испииһэгэ',
	'stableversions-page'         => 'Сирэй аата:',
	'stableversions-none'         => '"[[:$1]]" көрүллүбүт/бэрэбиэркэлэммит торумнара суох.',
	'stableversions-list'         => 'Сирэй бу "[[:$1]]" торумнара ырытыллыбыттар:',
	'stableversions-review'       => '$2 кыттааччы ырыппыт <i>$1</i>',
	'review-diff2stable'          => 'Чистовой уонна саҥа торумнар уратылара',
	'unreviewedpages'             => 'Ырытыллыбатах сирэйдэр',
	'viewunreviewed'              => 'Ис хоһоонноро көрүллүбэтэх сирэйдэр испииһэктэрэ',
	'unreviewed-outdated'         => 'Чистовой торумнары көрдөрбөккө эрэ ырытыллыбатах торумнары көрдөр.',
	'unreviewed-category'         => 'Категория:',
	'unreviewed-diff'             => 'Уларыйыылар',
	'unreviewed-list'             => 'Манна көрүллүбэтэх, эбэтэр ырытыллыбатах уларытыылардаах сирэйдэр испииһэктэрэ көрдөрүлүннэ.',
	'stabilization'               => 'Сирэй стабилизацията',
	'stabilization-page'          => 'Сирэй аата:',
	'stabilization-leg'           => 'Сирэй чистовой торумун туруорууларын уларыт',
	'stabilization-select'        => 'Чистовой торуму хайдах талар туһунан',
	'stabilization-select1'       => 'Бүтэһик бэрэбиэркэлэммит торум; суох буоллаҕына - бүтэһик көрүллүбүт.',
	'stabilization-select2'       => 'Бүтэһик ырытыллыбыт сирэй',
	'stabilization-def'           => 'Анаан этиллибэтэҕинэ көрдөрүллэр торум',
	'stabilization-def1'          => 'Чистовой торум, суох буоллаҕына - бүтэһик торум',
	'stabilization-def2'          => 'Бүтэһик торум',
	'stabilization-submit'        => 'Бигэргэтии',
	'stabilization-notexists'     => 'Маннык ааттаах сирэй «[[:$1|$1]]» суох. Онон уларытар кыах суох.',
	'stabilization-notcontent'    => '«[[:$1|$1]]» сирэй ырытыллыбат. Онон туруорууларын уларытар сатаммат.',
	'stabilization-comment'       => 'Хос быһаарыы:',
	'stabilization-expiry'        => 'Болдьоҕо бүтэр:',
	'stabilization-sel-short'     => 'Бэрээдэгэ',
	'stabilization-sel-short-0'   => 'Үрдүк таһымнаах',
	'stabilization-sel-short-1'   => 'Суох',
	'stabilization-def-short'     => 'Анал туруоруута суох',
	'stabilization-def-short-0'   => 'Бүтэһик',
	'stabilization-def-short-1'   => 'Чистовой',
	'stabilize_expiry_invalid'    => 'Болдьох сыыһа туруорулунна.',
	'stabilize_expiry_old'        => 'Болдьох этиллибит кэмэ номнуо ааспыт.',
	'stabilize-expiring'          => 'Болдьоҕо бүтэр: $1 (UTC)',
	'reviewedpages'               => 'Ырытыллыбыт сирэйдэр',
	'reviewedpages-leg'           => 'Ханнык эмит сыананы ылбыт сирэйдэр испииһэктэрэ',
	'reviewedpages-list'          => 'Бу сирэйдэр сөптөөх сыананы ылбыттар',
	'reviewedpages-none'          => 'Испииһэк кураанах',
	'reviewedpages-lev-0'         => 'Көрүллүбүт',
	'reviewedpages-lev-1'         => 'Бэрэбиэркэлэммит',
	'reviewedpages-lev-2'         => 'Талыы-талба',
	'reviewedpages-all'           => 'ырытыллыбыт торумнар',
	'reviewedpages-best'          => 'саамай үрдүктүк сыаналаммыт бүтэһик торума',
);

