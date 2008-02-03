<?php
/** Hungarian (Magyar)
 * @author Bdanee
 * @author KossuthRad
 * @author Siebrand
 */
$messages = array(
	'editor'                      => 'Szerkesztő',
	'group-editor'                => 'Szerkesztők',
	'group-editor-member'         => 'Szerkesztő',
	'grouppage-editor'            => '{{ns:project}}:Szerkesztő',
	'reviewer'                    => 'Ellenőr',
	'group-reviewer'              => 'Ellenőr',
	'group-reviewer-member'       => 'Ellenőr',
	'grouppage-reviewer'          => '{{ns:project}}:Ellenőr',
	'revreview-current'           => 'Vázlat',
	'tooltip-ca-current'          => 'Az oldal jelenlegi vázlatának megtekintése',
	'revreview-edit'              => 'Vázlat szerkesztése',
	'revreview-source'            => 'Vázlat forrása',
	'revreview-stable'            => 'Elfogadott változat',
	'tooltip-ca-stable'           => 'Az oldal elfogadott változatának megtekintése',
	'revreview-oldrating'         => 'Osztályozása:',
	'revreview-noflagged'         => "Az oldal még nem rendelkezik ellenőrzött változatokkal, így '''nem''' lehetett
[[{{MediaWiki:Validationpage}}|ellenőrizni]] minőség alapján.",
	'stabilization-tab'           => '(qa)',
	'tooltip-ca-default'          => 'Minőségbiztosítási beállítások',
	'validationpage'              => '{{ns:help}}:Szócikk ellenőrzése',
	'revreview-quick-none'        => "'''Jelenlegi''' (nem megtekintett változatok)",
	'revreview-quick-see-quality' => "'''Vázlat''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} elfogadott változat megtekintése]]  
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} változás])",
	'revreview-quick-see-basic'   => "'''Vázlat''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} elfogadott változat megtekintése]]  
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} változás])",
	'revreview-quick-basic'       => "'''[[{{MediaWiki:Validationpage}}|Áttekintett]]''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} vázlat megtekintése]]  
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} változás])",
	'revreview-quick-quality'     => "'''[[{{MediaWiki:Validationpage}}|Minőségi]]''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} vázlat megtekintése]]  
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} változás])",
	'revreview-newest-basic'      => 'A [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} legutóbbi megtekintett változat]  
([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} összes megjelenítése]), [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} elfogadva]
ekkor: <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 változást] kell ellenőrizni.',
	'revreview-newest-quality'    => 'A [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} legutóbbi minőségi változat]  
([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} összes megjelenítése]), [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} elfogadva]
ekkor: <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 változást] kell ellenőrizni.',
	'revreview-basic'             => 'Ez a legutóbbi [[{{MediaWiki:Validationpage}}|áttekintett]] változat,  
[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} elfogadva] ekkor: <i>$2</i>. A [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} vázlat]  
[{{fullurl:{{FULLPAGENAMEE}}|action=edit}} módosítható]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 változás]  
vár áttekintésre.',
	'revreview-quality'           => 'Ez a legutóbbi [[{{MediaWiki:Validationpage}}|minőségi]] változat,  
[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} elfogadás] ideje: <i>$2</i>. A [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} vázlatot]  
lehet [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} módosítani]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 változás]  
vár áttekintésre.',
	'revreview-static'            => "Ez '''[[:$3|$3]]''' egy [[{{MediaWiki:Validationpage}}|ellenőrzött]] változata, melyet
ekkor [{{fullurl:Special:Log/review|page=$1}} fogadtak el]: <i>$2</i>.",
	'revreview-note'              => '[[User:$1]] az alábbi megjegyzéseket tette ezen változat [[{{MediaWiki:Validationpage}}|ellenőrzésekor]]:',
	'revreview-update'            => 'Ellenőrizz minden változást (lenn láthatóak), amelyek az elfogadott változat óta készültek.
Néhány sablon vagy kép frissítve lett:',
	'revreview-update-none'       => 'Ellenőrizz minden változást (lenn láthatóak), amelyek az elfogadott változat óta készültek.',
	'revreview-auto'              => '(automatikus)',
	'revreview-auto-w'            => "Jelenleg az elfogadott változatot szerkeszted, bármilyen változás '''automatikusan ellenőrizve lesz'''.
Megtekintheted a lap előnézetét mentés előtt.",
	'revreview-auto-w-old'        => "Jelenleg egy korábbi változatot szerkesztesz, bármilyen változás '''automatikusan ellenőrizve lesz'''.
Megtekintheted a lap előnézetét mentés előtt.",
	'revreview-patrolled'         => '[[:$1|$1]] kiválasztott változata ellenőrzöttnek lett jelölve.',
	'hist-stable'                 => '[áttekintett változat]',
	'hist-quality'                => '[minőségi változat]',
	'flaggedrevs'                 => 'Ellenőrzött változatok',
	'review-logpage'              => 'Cikk-áttekintési napló',
	'review-logpagetext'          => 'Ez a lap a lapok verzióinak [[{{MediaWiki:Validationpage}}|elfogadottsági]] állapotában történt változások
naplója.',
	'review-logentry-app'         => 'áttekintette [[$1]]-t',
	'review-logentry-dis'         => 'törölte [[$1]] egyik verziójának az értékelését',
	'review-logaction'            => 'változat azonosítója: $1',
	'stable-logpage'              => 'Elfogadott változatok naplója',
	'stable-logpagetext'          => 'Ez a lap a lapok [[{{MediaWiki:Validationpage}}|elfogadott változataiban]] történt változások
naplója.',
	'stable-logentry'             => 'beállította [[$1]] elfogadott változatait',
	'stable-logentry2'            => 'törölte [[$1]] stabil változataival kapcsolatos beállításokat',
	'revisionreview'              => 'Változatok ellenőrzése',
	'revreview-main'              => 'Ki kell választanod egy oldal adott változatát az ellenőrzéshez.

Lásd az [[Special:Unreviewedpages|ellenőrizetlen lapok listáját]].',
	'revreview-selected'          => "'''$1''' kiválasztott változata:",
	'revreview-text'              => 'Az oldalon alapértelmezettként az elfogadott változatok jelennek meg az újabbak helyett.',
	'revreview-toolow'            => 'Ahhoz, hogy egy változat ellenőrzött legyen, minhol az „ellenőrizetlen”-től különböző 
értéket kell megadnod. Egy változat ellenőrzésének törléséhez állíts mindent erre az értékre.',
	'revreview-flag'              => 'Változat ellenőrzése',
	'revreview-legend'            => 'Változat tartalmának értékelése',
	'revreview-notes'             => 'Megjelenítendő megfigyelések vagy megjegyzések:',
	'revreview-accuracy'          => 'Pontosság',
	'revreview-accuracy-0'        => 'Ellenőrizetlen',
	'revreview-accuracy-1'        => 'Megtekintett',
	'revreview-accuracy-2'        => 'Pontos',
	'revreview-accuracy-3'        => 'Forrásokkal megfelelően ellátva',
	'revreview-accuracy-4'        => 'Kiemelt',
	'revreview-depth'             => 'Mélység',
	'revreview-depth-0'           => 'Ellenőrizetlen',
	'revreview-depth-1'           => 'Alapszintű',
	'revreview-depth-2'           => 'Átlagos',
	'revreview-depth-3'           => 'Részletes',
	'revreview-depth-4'           => 'Kiemelt',
	'revreview-style'             => 'Olvashatóság',
	'revreview-style-0'           => 'Ellenőrizetlen',
	'revreview-style-1'           => 'Elfogadható',
	'revreview-style-2'           => 'Jó',
	'revreview-style-3'           => 'Tömör',
	'revreview-style-4'           => 'Kiemelt',
	'revreview-log'               => 'Megjegyzés:',
	'revreview-submit'            => 'Áttekintés elküldése',
	'revreview-changed'           => "'''A kért művelet nem hajtható végre ezen a változaton.'''

Egy sablon vagy kép lett kérve, mikor nem lett adott változat megadva. Ez akkor történhet meg,  
mikor egy dinamikus sablon más képet vagy sablont illeszt be egy változótól függően, amely megváltozott,
mióta elkezdted ellenőrizni a lapot. Az oldal frissítése és az ellenőrzés újbóli elvégzése megoldhatja a problémát.",
	'stableversions'              => 'Elfogadott változatok',
	'stableversions-title'        => '„$1” ellenőrzött változatai',
	'stableversions-leg1'         => 'Oldal ellenőrzött változatainak listája',
	'stableversions-page'         => 'A lap neve:',
	'stableversions-none'         => '„[[:$1]]” nem rendelkezik ellenőrzött változatokkal',
	'stableversions-list'         => '„[[:$1]]” következő változatai lettek ellenőrizve:',
	'stableversions-review'       => 'Ellenőrizte $2, <i>$1</i>-kor',
	'review-diff2stable'          => 'Eltérések az elfogadott és a jelenlegi változat között',
	'unreviewedpages'             => 'Ellenőrizetlen lapok',
	'viewunreviewed'              => 'Ellenőrizetlen tartalmú oldalak listája',
	'unreviewed-outdated'         => 'Azon oldalak listája, amelyek ellenőrizetlen változatokkal rendelkeznek ellenőrzöttek helyett.',
	'unreviewed-category'         => 'Kategória:',
	'unreviewed-diff'             => 'Eltérések',
	'unreviewed-list'             => 'Ez az oldal azokat a lapokat tartalmazza, amelyek még nem lettek ellenőrizve, vagy rendelkeznek új, ellenőrizetlen változatokkal.',
	'revreview-visibility'        => 'Ez az oldal rendelkezik [[{{MediaWiki:Validationpage}}|elfogadott változattal]], amelyet
[{{fullurl:Special:Stabilization|page={{FULLPAGENAMEE}}}} be lehet állítani].',
	'stabilization'               => 'Oldal elfogadása',
	'stabilization-text'          => 'Az alábbi beállítások megváltoztatásával állíthatod be [[:$1|$1]] elfogadott változatát, és annak megjelenítését.',
	'stabilization-perm'          => 'Nincs jogosultságod megváltoztatni az elfogadott változat beállításait.
[[:$1|$1]] jelenlegi beállításai itt találhatóak:',
	'stabilization-page'          => 'A lap neve:',
	'stabilization-leg'           => 'Lap elfogadott változatának beállítása',
	'stabilization-select'        => 'Hogyan legyen az elfogadott változat kiválasztva',
	'stabilization-select1'       => 'A legutóbbi minőségi változat; ha nincs, akkor a legutóbbi áttekintett változat',
	'stabilization-select2'       => 'A legutóbbi áttekintett változat',
	'stabilization-def'           => 'Az alapértelmezettként megjelenített változat',
	'stabilization-def1'          => 'Az elfogadott változat; ha nincs, akkor a jelenlegi',
	'stabilization-def2'          => 'A jelenlegi változat',
	'stabilization-submit'        => 'Megerősítés',
	'stabilization-notexists'     => 'Nincs „[[:$1|$1]]” nevű lap, így nem lehet beállítani.',
	'stabilization-notcontent'    => '„[[:$1|$1]]” nem ellenőrizhető, így nem is lehet beállítani.',
	'stabilization-comment'       => 'Megjegyzés:',
	'stabilization-expiry'        => 'Lejárat:',
	'stabilization-sel-short'     => 'Precendencia',
	'stabilization-sel-short-0'   => 'Minőség',
	'stabilization-sel-short-1'   => 'Semmi',
	'stabilization-def-short'     => 'Alapértelmezett',
	'stabilization-def-short-0'   => 'Jelenlegi',
	'stabilization-def-short-1'   => 'Elfogadott',
	'stabilize_expiry_invalid'    => 'Hibás lejárati dátum.',
	'stabilize_expiry_old'        => 'A lejárati idő már elmúlt.',
	'stabilize-expiring'          => 'lejár $1-kor (UTC)',
	'reviewedpages'               => 'Ellenőrzött lapok',
	'reviewedpages-leg'           => 'Valamilyen szinten ellenőrzött lapok',
	'reviewedpages-list'          => 'Az alábbi lapok egy adott szinten ellenőrizve vannak',
	'reviewedpages-none'          => 'A lista nem tartalmaz lapokat',
	'reviewedpages-lev-0'         => 'Áttekintett',
	'reviewedpages-lev-1'         => 'Minőségi',
	'reviewedpages-lev-2'         => 'Kiemelt',
	'reviewedpages-all'           => 'Ellenőrzött változatok',
	'reviewedpages-best'          => 'legutóbbi legjobban értékelt változat',
);

