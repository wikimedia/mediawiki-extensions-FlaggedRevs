<?php
/** Slovak (Slovenčina)
 * @author Helix84
 * @author Siebrand
 * @author SPQRobin
 */
$messages = array(
	'editor'                      => 'Redaktor',
	'group-editor'                => 'Redaktori',
	'group-editor-member'         => 'Redaktor',
	'grouppage-editor'            => '{{ns:project}}:Redaktor',
	'reviewer'                    => 'Revízor',
	'group-reviewer'              => 'Revízori',
	'group-reviewer-member'       => 'Revízor',
	'grouppage-reviewer'          => '{{ns:project}}:Revízor',
	'revreview-current'           => 'Koncept',
	'tooltip-ca-current'          => 'Zobraziť aktuálny koncept tejto stránky',
	'revreview-edit'              => 'Upraviť koncept',
	'revreview-source'            => 'Zdroj konceptu',
	'revreview-stable'            => 'Stabilná verzia',
	'tooltip-ca-stable'           => 'Zobraziť stabilnú verziu tejto stránky',
	'revreview-oldrating'         => 'Bolo ohodnotené ako:',
	'revreview-noflagged'         => "Neexistujú revidované verzie tejto stránky, takže jej
	kvalita '''nebola''' [[{{MediaWiki:Validationpage}}|skontrolovaná]].",
	'stabilization-tab'           => '(kk)',
	'tooltip-ca-default'          => 'Nastavenia kontroly kvality',
	'revreview-quick-none'        => "'''Aktuálna'''. Žiadne revízie neboli skontrolvoané..",
	'revreview-quick-see-quality' => "'''Aktuálna'''. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Pozri poslednú kvalitnú revíziu]",
	'revreview-quick-see-basic'   => "'''Aktuálna'''. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Pozri poslednú skontrolovanú revíziu]",
	'revreview-quick-basic'       => "'''[[{{MediaWiki:Validationpage}}|Skontrolovaná]]'''. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Pozri aktuálnu revíziu] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|zmena|zmeny|zmien}}])",
	'revreview-quick-quality'     => "'''[[{{MediaWiki:Validationpage}}|Kvalitná]]'''. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Pozri aktuálnu revíziu] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|zmena|zmeny|zmien}}])",
	'revreview-newest-basic'      => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Posledná overená revízia] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} zobraziť všetky]) tejto stránky bola [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválená]
	 <i>$2</i>. <br/> {{plural:$3|$3 revízia|$3 revízie||$3 revízií}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} zmeny]) čaká na schválenie.',
	'revreview-newest-quality'    => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Posledná kvalitná revízia] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} zobraziť všetky]) tejto stránky bola [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválená]
	 <i>$2</i>. <br/> {{plural:$3|$3 revízia|$3 revízie||$3 revízií}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} zmeny]) čaká na schválenie.',
	'revreview-basic'             => 'Toto je najnovšia [[{{MediaWiki:Validationpage}}|stabilná]] verzia tejto stránky, 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválená] <i>$4</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Aktuálna verzia] 
	je zvyčajne [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} prístupná úpravám] a aktuálnejšia. 
Na revíziu čaká [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=$2}} {{plural:$3|jedna zmena|$3 zmeny|$3 zmien}}].',
	'revreview-quality'           => 'Toto je najnovšia [[{{MediaWiki:Validationpage}}|kvalitná]] verzia tejto stránky, 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválená] <i>$4</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Aktuálna verzia] 
	je zvyčajne [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} prístupná úpravám] a aktuálnejšia. 
Na revíziu čaká [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=$2}} {{plural:$3|jedna zmena|$3 zmeny|$3 zmien}}].',
	'revreview-static'            => "Toto je [[{{MediaWiki:Validationpage}}|skontrolovaná]] verzia stránky '''[[:$3]]''', 
	[{{fullurl:Special:Log/review|page=$1}} schválená] <i>$2</i>. [{{fullurl:$3|stable=0}} Najnovšia verzia] 
	je zvyčajne prístupná úpravám a aktuálnejšia.",
	'revreview-toggle'            => '(prepnúť zobrazenie podrobností)',
	'revreview-note'              => '[[User:$1]] urobil nasledovné poznámky počas [[{{MediaWiki:Validationpage}}|kontroly]] tejto verzie:',
	'revreview-update'            => 'Prosím, skontrolujte všetky zmeny (zobrazené nižšie), ktoré boli vykonané od poslednej stabilnej revízie. Šablóny a obrázky sa tiež mohli zmeniť.',
	'revreview-update-none'       => 'Prosím, skontrolujte všetky zmeny (zobrazené nižšie), ktoré boli urobené od stabilnej verzie tejto stránky.',
	'revreview-auto'              => '(automatické)',
	'revreview-auto-w'            => "Upravujete stabilnú revíziu, akékoľvek zmeny budú '''automaticky označené ako skontrolované'''. Pred uložením by ste mali použiť náhľad.",
	'revreview-auto-w-old'        => "Upravujete strú revíziu, akékoľvek zmeny budú '''automaticky označené ako skontrolované'''. Pred uložením by ste mali použiť náhľad.",
	'hist-stable'                 => '[stabilná]',
	'hist-quality'                => '[kvalitná]',
	'flaggedrevs'                 => 'Označené verzie',
	'review-logpage'              => 'Záznam kontrol stránky',
	'review-logpagetext'          => 'Toto je záznam zmien stavu [[{{MediaWiki:Makevalidate-page}}|kontroly]] verzií
	stránok s obsahom.',
	'review-logentry-app'         => 'skontrolované [[$1]]',
	'review-logentry-dis'         => 'neodporúča sa verzia [[$1]]',
	'review-logaction'            => 'ID verzie $1',
	'stable-logpage'              => 'Záznam stabilných verzií',
	'stable-logpagetext'          => 'Toto je záznam zmien v konfigurácii [[{{MediaWiki:Validationpage}}|stabilnej verzie]] článkov.',
	'stable-logentry'             => 'nastavil stabilné verzie [[$1]]',
	'stable-logentry2'            => 'zrušil stabilné verzie [[$1]]',
	'revisionreview'              => 'Prezrieť kontroly',
	'revreview-main'              => 'Musíte vybrať konkrétnu verziu stránky s obsahom, aby ste ju mohli skontrolovať. 

	Pozri zoznam neskontrolovaných stránok
	[[Special:Unreviewedpages]].',
	'revreview-selected'          => "Zvolená verzia '''$1:'''",
	'revreview-text'              => 'Stabilné verzie, nie najnovšie verzie, sú nastavené ako štandardný obsah stránky.',
	'revreview-toolow'            => 'Musíte ohodnotiť každý z nasledujúcich atribútov minimálne vyššie ako "neschválené", aby bolo možné
	verziu považovať za skontrolovanú. Ak chcete učiniť verziu zastaralou, nastavte všetky polia na "neschválené".',
	'revreview-flag'              => 'Skontrolovať túto verziu (#$1):',
	'revreview-legend'            => 'Ohodnotiť obsah verzie:',
	'revreview-notes'             => 'Pozorovania alebo poznámky, ktoré sa majú zobraziť:',
	'revreview-accuracy'          => 'Presnosť',
	'revreview-accuracy-0'        => 'neschválené',
	'revreview-accuracy-1'        => 'zbežná',
	'revreview-accuracy-2'        => 'presná',
	'revreview-accuracy-3'        => 'dobre uvedené zdroje',
	'revreview-accuracy-4'        => 'odporúčaný',
	'revreview-depth'             => 'Hĺbka',
	'revreview-depth-0'           => 'neschválené',
	'revreview-depth-1'           => 'základná',
	'revreview-depth-2'           => 'stredná',
	'revreview-depth-3'           => 'vysoká',
	'revreview-depth-4'           => 'odporúčaný',
	'revreview-style'             => 'Čitateľnosť',
	'revreview-style-0'           => 'neschválené',
	'revreview-style-1'           => 'prijateľná',
	'revreview-style-2'           => 'dobrá',
	'revreview-style-3'           => 'zhustená',
	'revreview-style-4'           => 'odporúčaný',
	'revreview-log'               => 'Komentár záznamu:',
	'revreview-submit'            => 'Aplikovať kontrolu',
	'revreview-changed'           => "'''Požadovaná činnosť by sa namala vykonávať na tejto revízii.'''
	
	Šablóna alebo obrázok mohlol byť vyžiadaný bez uvedenia konkrétnej verzie. To sa môže stať, keď
	dynamická šablóna transkluduje iný obrázok alebo šablónu v závislosti od premennej, ktorá sa zmenila, odkedy ste začali
	s kontrolou tejto stránky. Obnovením stránky a opätovnou kontrolou vyriešite tento problém.",
	'stableversions'              => 'Stabilné verzie',
	'stableversions-leg1'         => 'Zoznam skontrolovaných verzií stránky',
	'stableversions-page'         => 'Názov stránky',
	'stableversions-none'         => '[[:$1]] nemá skontrolované verzie.',
	'stableversions-list'         => 'Nasleduje zoznam verzií stránky [[:$1]], ktoré boli skontrolované:',
	'stableversions-review'       => 'Skontrolované <i>$1</i>',
	'review-diff2stable'          => 'Rozdiely oproti poslednej stabilnej verzii',
	'unreviewedpages'             => 'Neskontrolované stránky',
	'viewunreviewed'              => 'Zoznam neskontrolovaných stránok s obsahom',
	'unreviewed-outdated'         => 'Zobraziť stránky, ktoré majú neskontrolované revízie stabilnej verzie.',
	'unreviewed-category'         => 'Kategória:',
	'unreviewed-diff'             => 'Zmeny',
	'unreviewed-list'             => 'Táto stránka obsahuje zoznam článkov, ktoré zatiaľ neboli skontrolované.',
	'revreview-visibility'        => 'Táto stránka má [[{{MediaWiki:Validationpage}}|stabilnú verziu]], ktorú je možné [{{fullurl:Special:Stabilization|page={{FULLPAGENAMEE}}}} nastaviť].',
	'stabilization'               => 'Stabilizácia stránky',
	'stabilization-text'          => 'Voľby nižšie menia spôsob výberu a zobrazenia stabilnej verzie stránky [[:$1|$1]].',
	'stabilization-perm'          => 'Váš účet nemá oprávnenie meniť nastavenia stabilnej verzie. Tu sú súčasné nastavenia [[:$1|$1]]:',
	'stabilization-page'          => 'Názov stránky:',
	'stabilization-leg'           => 'Zmeniť nastavenia stabilnej verzie',
	'stabilization-select'        => 'Spôsob výberu stabilnej verzie',
	'stabilization-select1'       => 'Posledná kvalitná revízia; ak nie je prítomná, je to posledná skontrolovaná',
	'stabilization-select2'       => 'Posledná skontrolovaná revízia',
	'stabilization-def'           => 'Revízia, ktorá sa zobrazí pri štandardnom zobrazení stránky',
	'stabilization-def1'          => 'Stabilná revízia; ak nie je prítomná, je to aktuálna',
	'stabilization-def2'          => 'Aktuálna revízia',
	'stabilization-submit'        => 'Potvrdiť',
	'stabilization-notexists'     => 'Neexistuje stránka s názvom „[[:$1|$1]]“. Konfigurácia nie je možná.',
	'stabilization-notcontent'    => 'Stránku „[[:$1|$1]]“ nie je možné skontrolovať. Konfigurácia nie je možná.',
	'stabilization-sel-short'     => 'Precedencia',
	'stabilization-sel-short-0'   => 'Kvalita',
	'stabilization-sel-short-1'   => 'žiadna',
	'stabilization-def-short'     => 'štandard',
	'stabilization-def-short-0'   => 'aktuálna',
	'stabilization-def-short-1'   => 'stabilná',
);
