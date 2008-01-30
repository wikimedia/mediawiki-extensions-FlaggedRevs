<?php
/** Croatian (Hrvatski)
 * @author SpeedyGonsales
 * @author Dnik
 */
$messages = array(
	'editor'                      => 'Suradnik',
	'group-editor'                => 'Suradnici',
	'group-editor-member'         => 'Suradnik',
	'grouppage-editor'            => '{{ns:project}}:Suradnik',
	'reviewer'                    => 'Ocjenjivač',
	'group-reviewer'              => 'Ocjenjivači',
	'group-reviewer-member'       => 'Ocjenjivač',
	'grouppage-reviewer'          => '{{ns:project}}:Ocjenjivač',
	'revreview-current'           => 'Članak u radu',
	'tooltip-ca-current'          => 'Vidi trenutnu inačicu ove stranice',
	'revreview-edit'              => 'Uredi članak u radu',
	'revreview-source'            => 'izvor članka u radu',
	'revreview-stable'            => 'Važeća inačica',
	'tooltip-ca-stable'           => 'Vidi važeću inačicu stranice',
	'revreview-oldrating'         => 'Prethodna ocjena:',
	'revreview-noflagged'         => "Nema ocijenjenih inačica stranice, stoga stranica najvjerojatnije '''nije''' [[{{MediaWiki:Validationpage}}|provjerena]].",
	'stabilization-tab'           => '(vi)',
	'tooltip-ca-default'          => 'Postavke važeće inačice',
	'validationpage'              => '{{ns:help}}:Ocjenjivanje članaka',
	'revreview-quick-none'        => "'''Važeća inačica''' (nema ocijenjenih inačica)",
	'revreview-quick-see-quality' => "'''Članak u izradi''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} vidi važeću inačicu]]  
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|promjena|promjene|promjena}}])",
	'revreview-quick-see-basic'   => "'''Članak u izradi''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} vidi važeću inačicu]]  
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|promjena|promjene|promjena}}])",
	'revreview-quick-basic'       => "'''[[{{MediaWiki:Validationpage}}|Pregled]]''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} vidi članak u izradi]]  
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|promjena|promjene|promjena}}])",
	'revreview-quick-quality'     => "'''[[{{MediaWiki:Validationpage}}|Ocjena]]''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} vidi članak u izradi]]  
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|promjena|promjene|promjena}}])",
	'revreview-newest-basic'      => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} zadnji pregled promjena na članku]  
([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} prikaži sve]) je [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} izvršen]
dana <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|promjena|promjene|promjena}}] {{plural:$3|treba|trebaju|treba}} ocjenu.',
	'revreview-newest-quality'    => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} zadnje ocjenjivanje članka]  
([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} prikaži sve]) je [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} izvršeno]
dana <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|promjena|promjene|promjena}}] {{plural:$3|treba|trebaju|treba}} ocjenu.',
	'revreview-basic'             => 'Ovo je zadnja [[{{MediaWiki:Validationpage}}|pregledana]] promjena,  
[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} odobrena] dana <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Članak u radu]  
možete [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} uređivati]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|promjena|promjene|promjena}}]  
{{plural:$3|čeka|čekaju|čeka}} ocjenjivanje.',
	'revreview-quality'           => 'Ovo je zadnja [[{{MediaWiki:Validationpage}}|ocijenjena]] promjena,  
[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} odobrena] dana <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Članak u radu]  
možete [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} uređivati]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|promjena|promjene|promjena}}]  
{{plural:$3|čeka|čekaju|čeka}} ocjenjivanje.',
	'revreview-static'            => "Ovo je [[{{MediaWiki:Validationpage}}|ocijenjena]] promjena članka '''[[:$3|$3]]''',  
[{{fullurl:Special:Log/review|page=$1}} odobrena] dana <i>$2</i>.",
	'revreview-note'              => '[User:$1]] je zabilježio slijedeće pri [[{{MediaWiki:Validationpage}}|ocjenjivanju]] ove inačice:',
	'revreview-update'            => 'Molim pregledajte sve promjene (prikazane dolje) učinjene od stabilne inačice. Neki
predlošci/slike su promijenjeni:',
	'revreview-update-none'       => 'Molim, pregledajte sve promjene (prikazane dolje) učinjene od stabilne inačice.',
	'revreview-auto'              => '(automatski)',
	'revreview-auto-w'            => "Uređujete važeću inačicu stranice, svaka vaša promjena biti će '''automatski ocijenjena'''.
Možda želite pregledati vaše izmjene prije snimanja.",
	'revreview-auto-w-old'        => "Uređujete staru inačicu članka, svaka promjena bit će '''automatski ocijenjena'''.
Možda želite pregledati vaše izmjene prije snimanja.",
	'revreview-patrolled'         => 'Odabrana izmjena stranice [[:$1|$1]] je označena pregledanom (patroliranom).',
	'hist-stable'                 => '[pregledana]',
	'hist-quality'                => '[kvalitetna]',
	'flaggedrevs'                 => 'Označene promjene',
	'review-logpage'              => 'Evidencija ocjena članka',
	'review-logpagetext'          => 'Ovo je evidencija promjena [[{{MediaWiki:Validationpage}}|ocjena]] članaka.',
	'review-logentry-app'         => 'ocijenio [[$1]]',
	'review-logentry-dis'         => 'zastarjela inačica stranice [[$1]]',
	'review-logaction'            => 'ocjena broj $1',
	'stable-logpage'              => 'Evidencija stabilnih verzija',
	'stable-logpagetext'          => 'Ovo je evidencija promjena [[{{MediaWiki:Validationpage}}|važećih inačica]] 
članaka u glavnom imenskom prostoru.',
	'stable-logentry'             => 'postavljena važeća inačica stranice [[$1]]',
	'stable-logentry2'            => 'poništi važeću inačicu članka [[$1]]',
	'revisionreview'              => 'Ocijeni inačice',
	'revreview-main'              => 'Morate odabrati neku izmjenu stranice u glavnom imenskom prostoru za ocjenjivanje.

Pogledajte popis [[Special:Unreviewedpages|neocijenjenih stranica]] za to.',
	'revreview-selected'          => "Odabrane promjene '''$1:'''",
	'revreview-text'              => "Važeća (''stabilna'') inačica stranice prikazuje se svima umjesto najnovije inačice.",
	'revreview-toolow'            => 'Morate ocijeniti po svakom od donjih kriterija ocjenom višom od "Ne zadovoljava"
da bi promjena bila pregledana/ocijenjena. U suprotnom, ostavite sve na "Ne zadovoljava".',
	'revreview-flag'              => 'Ocijeni izmjenu (#$1)',
	'revreview-legend'            => 'Ocijeni sadržaj inačice',
	'revreview-notes'             => 'Primjedbe ili napomene koje treba prikazati:',
	'revreview-accuracy'          => 'Točnost',
	'revreview-accuracy-0'        => 'Članak ne zadovoljava',
	'revreview-accuracy-1'        => 'Članak zadovoljava',
	'revreview-accuracy-2'        => 'Dobar',
	'revreview-accuracy-3'        => 'Vrlo dobar (potkrijepljen izvorima)',
	'revreview-accuracy-4'        => 'Izvrstan',
	'revreview-depth'             => 'Dubina',
	'revreview-depth-0'           => 'Članak ne zadovoljava',
	'revreview-depth-1'           => 'Članak zadovoljava',
	'revreview-depth-2'           => 'Dobar',
	'revreview-depth-3'           => 'Vrlo dobar',
	'revreview-depth-4'           => 'Izvrstan',
	'revreview-style'             => 'Čitljivost',
	'revreview-style-0'           => 'Neodobren',
	'revreview-style-1'           => 'Prihvatljiv',
	'revreview-style-2'           => 'Dobar',
	'revreview-style-3'           => 'Vrlo dobar',
	'revreview-style-4'           => 'Izvrstan',
	'revreview-log'               => 'Napomena:',
	'revreview-submit'            => 'Podnesi ocijenu',
	'revreview-changed'           => "'''Traženu akciju nije moguće izvršiti na ovoj inačici.'''

Tražen je prdeložak ili slika bez navođenja verzije. To se može dogoditi ukoliko
predložak uključuje sliku ili drugi predložak koji ovisi o varijabli koja se promijenila
nakon što ste počeli ocjenjivati članak. Ctrl + R može riješiti taj problem.",
	'stableversions'              => 'Stabilna inačica',
	'stableversions-title'        => 'Stabilne inačice "$1"',
	'stableversions-leg1'         => 'Prikaži pregledane inačice stranice',
	'stableversions-page'         => 'Ime stranice:',
	'stableversions-none'         => 'Članak "[[:$1]]" nema pregledanih inačica.',
	'stableversions-list'         => 'Slijedi popis inačica članka "[[:$1]]" koje su ocijenjene:',
	'stableversions-review'       => 'Ocijenjeno <i>$1</i> od suradnika $2',
	'review-diff2stable'          => "Promjene između važeće (''stabilne'') i trenutne inačice",
	'unreviewedpages'             => 'Neocijenjene stranice',
	'viewunreviewed'              => 'Ispiši stranice s neocjenjenim sadržajem',
	'unreviewed-outdated'         => "Prikaži članke koji imaju neocijenjene promjene važeće (''stabilne'') inačice.",
	'unreviewed-category'         => 'Kategorija:',
	'unreviewed-diff'             => 'Promjene',
	'unreviewed-list'             => 'Slijedi popis neocijenjenih članaka (odnosno onih koji su mijenjani od zadnje ocjene).',
	'revreview-visibility'        => 'Ovaj članak ima [[{{MediaWiki:Validationpage}}|važeću inačicu]], koja može biti
[{{fullurl:Special:Stabilization|page={{FULLPAGENAMEE}}}} konfigurirana].',
	'stabilization'               => 'Stabilizacija stranice',
	'stabilization-text'          => 'Promijeni postavke kako će se važeća inačica članka [[:$1|$1]] prikazivati.',
	'stabilization-perm'          => 'Vaš suradnički račun nema prava mijenjanja stabilne inačice članka.
Slijede važeće postavke za [[:$1|$1]]:',
	'stabilization-page'          => 'Ime stranice:',
	'stabilization-leg'           => "Odredi važeću (''stabilnu'') inačicu članka",
	'stabilization-select'        => 'Kako je odabrana stabilna verzija',
	'stabilization-select1'       => 'Posljednja ocjena kvalitete; ukoliko je nije bilo, posljednje pregledavanje',
	'stabilization-select2'       => 'Posljednja ocijenjena inačica',
	'stabilization-def'           => "Odabir inačice koja se prikazuje po ''defaultu''",
	'stabilization-def1'          => 'Stabilna inačica; ako je nema, trenutna',
	'stabilization-def2'          => 'Tekuća inačica',
	'stabilization-submit'        => 'Potvrdite',
	'stabilization-notexists'     => 'Ne postoji stranica "[[:$1|$1]]", te stoga nije moguće namještanje postavki za tu stranicu.',
	'stabilization-notcontent'    => 'Stranica "[[:$1|$1]]" ne može biti ocijenjena. Namještanje postavki nije moguće.',
	'stabilization-comment'       => 'Komentar:',
	'stabilization-sel-short'     => 'Prvenstvo',
	'stabilization-sel-short-0'   => 'Kvaliteta',
	'stabilization-sel-short-1'   => 'Nema',
	'stabilization-def-short'     => 'Uobičajeno',
	'stabilization-def-short-0'   => 'Tekući',
	'stabilization-def-short-1'   => 'Važeća inačica',
	'reviewedpages'               => 'Ocijenjene stranice',
	'reviewedpages-leg'           => 'Prikaži popis stranica ocijenjenih ocjenom',
	'reviewedpages-list'          => 'Slijedeće stranice su ocijenjene ocjenom',
	'reviewedpages-none'          => 'Nema stranica u ovom popisu',
	'reviewedpages-lev-0'         => 'Pregledani članci',
	'reviewedpages-lev-1'         => 'Kvalitetni članci',
	'reviewedpages-lev-2'         => 'Izvrsni članci',
	'reviewedpages-all'           => 'ocjenjene verzije',
	'reviewedpages-best'          => 'posljednja najviše ocijenjena inačica',
);

