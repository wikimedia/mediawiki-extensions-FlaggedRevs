<?php
/* Piemontèis (Bèrto 'd Sèra - 71) */
$messages = array(
	'makevalidate-autosum'  => 'promossion aotomàtica',
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
	'revreview-quick-none'  => '\'\'\'Corenta\'\'\'. Pa gnun-a version revisionà.',
	'revreview-quick-see-quality' => '\'\'\'Corenta\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} ùltima version votà për qualità]',
	'revreview-quick-see-basic' => '\'\'\'Corenta\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} ùltima version vardà]',
	'revreview-quick-basic' => '\'\'\'Vardà\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} version corenta] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|modìfica|modìfiche}}])',
	'revreview-quick-quality' => '\'\'\'Qualità\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} version corenta] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|modìfica|modìfiche}}])',
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
	'revreview-changed'     => '\'\'\'L\'arcesta a l\'é nen podusse sodisfé për lòn ch\'a toca sta revision-sì.\'\'\'
	
	A puel esse ch\'a sia ciamasse në stamp ò na figura sensa ch\'a fussa butasse la version. Sòn a peul rivé quand në 
	stamp dinàmich a transclud na figura ò n\'àotr ëstamp conforma a na variàbil dont contnù a peul esse cambià da  
	quand a l\'ha anandiasse a vardé sta pàgina-sì. Carié torna la pàgina e anandiesse da zero a peul arsolve la gran-a.',
	'stableversions'        => 'Version stàbij',
	'stableversions-leg1'   => 'Fé na lista dle version aprovà ëd na pàgina',
	'stableversions-page'   => 'Nòm dla pàgina',
	'stableversions-none'   => '[[:$1]] a l\'ha pa gnun-a version revisionà.',
	'stableversions-list'   => 'Costa-sì a l\'é na lista ëd version ëd [[:$1]] ch\'a son ëstaite revisionà:',
	'stableversions-review' => 'Revisionà dël <i>$1</i>',
	'review-diff2stable'    => 'Diferensa da \'nt l\'ùltima version stàbila',
	'review-diff2oldest'    => 'Diferensa da \'nt la revision pì veja',
	'unreviewedpages'       => 'Pàgine dësrevisionà',
	'viewunreviewed'        => 'Lista dle pàgine ëd contnù ch\'a son ëstaite dësrevisionà',
	'unreviewed-list'       => 'Costa-sì a l\'é na lista d\'artìcoj ch\'a son anco\' pa stait revisionà.',
);
