<?php
/** Dutch (Nederlands)
 * @author Siebrand
 * @author SPQRobin
 * @author Annabel
 */
$messages = array(
	'editor'                      => 'Redacteur',
	'group-editor'                => 'Redacteuren',
	'group-editor-member'         => 'Redacteur',
	'grouppage-editor'            => '{{ns:project}}:Redacteur',
	'reviewer'                    => 'Beoordelaar',
	'group-reviewer'              => 'Beoordelaars',
	'group-reviewer-member'       => 'Beoordelaar',
	'grouppage-reviewer'          => '{{ns:project}}:Beoordelaar',
	'revreview-current'           => 'Huidige versie',
	'tooltip-ca-current'          => 'huidige werkversie van deze pagina tonen',
	'revreview-edit'              => 'concept bewerken',
	'revreview-source'            => 'Brontekst concept',
	'revreview-stable'            => 'Stabiele versie',
	'tooltip-ca-stable'           => 'stabiele versie van deze pagina tonen',
	'revreview-oldrating'         => 'Was gewaardeerd als:',
	'revreview-noflagged'         => "Er zijn geen beoordeelde versies van deze pagina, dus die is wellicht '''niet''' [[{{MediaWiki:Validationpage}}|gecontroleerd]] op kwaliteit.",
	'stabilization-tab'           => '(kb)',
	'tooltip-ca-default'          => 'Instellingen kwaliteitsbewaking',
	'validationpage'              => '{{ns:help}}:Paginacontrole',
	'revreview-quick-none'        => "'''Huidige versie'''. Geen beoordeelde versies.",
	'revreview-quick-see-quality' => "'''Huidige versie'''. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} stabiele versie bekijken]
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|wijziging|wijzigingen}}])",
	'revreview-quick-see-basic'   => "'''Huidige versie'''. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} stabiele versie bekijken]
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|wijziging|wijzigingen}}])",
	'revreview-quick-basic'       => "'''[[{{MediaWiki:Validationpage}}|Beoordeeld]]'''. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} huidige versie bekijken] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|bewerking|bewerkingen}}])",
	'revreview-quick-quality'     => "'''[[{{MediaWiki:Validationpage}}|Kwaliteitsversie]]'''. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} huidige versie bekijken] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|bewerking|bewerkingen}}])",
	'revreview-newest-basic'      => 'De [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} laatst beoordeelde versie] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} alles tonen]) is [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} gekeurd]
	 op <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} $3 {{plural:$3|versie|versies}}] {{plural:$3|heeft|hebben}} een beoordeling nodig.',
	'revreview-newest-quality'    => 'De [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} laatste kwaliteitsversie] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} alles tonen]) is [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} gekeurd]
	 op <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} $3 {{plural:$3|versie|versies}}] {{plural:$3|heeft|hebben}} een beoordeling nodig.',
	'revreview-basic'             => 'Dit is de laatst [[{{MediaWiki:Validationpage}}|beoordeelde]] versie, [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} gekeurd] op <i>$2</i>. De [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} huidige] kan [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} bewerkt] worden; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} $3 {{plural:$3|versie|versies}}] {{plural:$3|wacht|wachten}} op een beoordeling.',
	'revreview-quality'           => 'Dit is de laatste [[{{MediaWiki:Validationpage}}|kwaliteitsversie]], [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} gekeurd] op <i>$2</i>. De [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} huidige] kan [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} bewerkt] worden; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} $3 {{plural:$3|versie|versies}}] {{plural:$3|wacht|wachten}} op een beoordeling.',
	'revreview-static'            => "Dit is een [[{{MediaWiki:Validationpage}}|beoordeelde]] versie van '''[[:$3|deze pagina]]''', [{{fullurl:Special:Log/review|page=$1}} gekeurd] op <i>$2</i>. De [{{fullurl:$3|stable=0}} huidige versie] kan [{{fullurl:$3|action=edit}} bewerkt] worden.",
	'revreview-note'              => '[[User:$1|$1]] heeft de volgende opmerkingen gemaakt bij de [[{{MediaWiki:Validationpage}}|beoordeling]] van deze versie:',
	'revreview-update'            => 'Controleer alstublieft alle onderstaande wijzigingen die gemaakt zijn sinds de stabiele versie voor deze pagina. Sjablonen en afbeeldingen kunnen ook gewijzigd zijn.',
	'revreview-update-none'       => 'Beoordeel alstublieft de wijzigingen (hieronder getoond) die sinds de stabiele versie aan deze pagina zijn gemaakt.',
	'revreview-auto'              => '(automatisch}',
	'revreview-auto-w'            => "'''Opmerking:''' u wijzigt de stabiele versie. Uw bewerkingen worden automatisch gecontroleerd. Controleer de voorvertoning voordat u de pagina opslaat.",
	'revreview-auto-w-old'        => "U bent een oude versie aan het bewerken, elke wijziging wordt '''automatisch beoordeeld'''.
Controleer uw bewerking voordat u deze opslaat.",
	'revreview-patrolled'         => 'De geselecteerde versie van [[:$1|$1]] is gemarkeerd als gecontroleerd.',
	'hist-stable'                 => '[beoordeelde versie]',
	'hist-quality'                => '[kwaliteitsversie]',
	'flaggedrevs'                 => 'Aangevinkte versies',
	'review-logpage'              => 'Beoordelingslogboek',
	'review-logpagetext'          => "Dit is een logboek met wijzigingen in de [[{{MediaWiki:Makevalidate-page}}|waarderingsstatus]] van versies van pagina's.",
	'review-logentry-app'         => 'beoordeelde [[$1]]',
	'review-logentry-dis'         => 'heeft een versie van [[$1]] lager beoordeeld',
	'review-logaction'            => 'versienummer $1',
	'stable-logpage'              => 'Logboek stabiele versies',
	'stable-logpagetext'          => 'Dit is een logboek met wijzigingen aan de instellingen voor [[{{MediaWiki:Validationpage}}|stabiele versies]] voor de hoofdnaamruimte.',
	'stable-logentry'             => 'stabiele versies zijn ingesteld voor [[$1]]',
	'stable-logentry2'            => 'stabiele versies voor [[$1]] opnieuw instellen',
	'revisionreview'              => 'Versies beoordelen',
	'revreview-main'              => "U moet een specifieke versie van een pagina kiezen om te kunnen beoordelen. 

	Zie  [[Special:Unreviewedpages]] voor een lijst met pagina's waarvoor nog geen beoordeling is gegeven.",
	'revreview-selected'          => "Geselecteerde versie van '''$1:'''",
	'revreview-text'              => 'Stabiele versies worden standaard getoond in plaats van de nieuwste versie.',
	'revreview-toolow'            => 'U moet tenminste alle onderstaande eigenschappen hoger instellen dan "niet gekeurd" om een versie als 
	beoordeeld aan te laten merken. Om de waardering van een versie te verwijderen, stelt u alle velden in op "niet gekeurd".',
	'revreview-flag'              => 'Deze versie beoordelen (#$1):',
	'revreview-legend'            => 'Versieinhoud waarderen',
	'revreview-notes'             => 'Weer te geven observaties of notities:',
	'revreview-accuracy'          => 'Nauwkeurigheid',
	'revreview-accuracy-0'        => 'Niet gekeurd',
	'revreview-accuracy-1'        => 'Beoordeeld',
	'revreview-accuracy-2'        => 'Nauwkeurig',
	'revreview-accuracy-3'        => 'Goed van bronnen voorzien',
	'revreview-accuracy-4'        => 'Uitgelicht',
	'revreview-depth'             => 'Diepgang',
	'revreview-depth-0'           => 'Niet beoordeeld',
	'revreview-depth-1'           => 'Basis',
	'revreview-depth-2'           => 'Middelmatig',
	'revreview-depth-3'           => 'Hoog',
	'revreview-depth-4'           => 'Uitgelicht',
	'revreview-style'             => 'Leesbaarheid',
	'revreview-style-0'           => 'Niet beoordeeld',
	'revreview-style-1'           => 'Aanvaardbaar',
	'revreview-style-2'           => 'Goed',
	'revreview-style-3'           => 'Bondig',
	'revreview-style-4'           => 'Uitgelicht',
	'revreview-log'               => 'Opmerking:',
	'revreview-submit'            => 'Beoordeling opslaan',
	'revreview-changed'           => "'''De gevraagde actie kon niet uitgevoerd worden voor deze versie.'''
	
	Er is een sjabloon of afbeelding opgevraagd zonder dat een specifieke versie is aangegeven. Dit kan voorkomen als een 
	dynamisch sjabloon een andere afbeelding of een ander sjabloon bevat, afhankelijk van een variabele die is gewijzigd sinds
	u bent begonnen met de beoordeling van deze pagina. Ververs de pagina en start de beoordeling opnieuw om dit probleem op te lossen.",
	'stableversions'              => 'Stabiele versies',
	'stableversions-title'        => 'Stabiele versies van "$1"',
	'stableversions-leg1'         => 'Lijst van beoordeelde versies voor een pagina',
	'stableversions-page'         => 'Paginanaam:',
	'stableversions-none'         => '[[:$1]] heeft geen beoordeelde versies.',
	'stableversions-list'         => 'Hieronder staat een lijst met versies van [[:$1]] waarvoor een beoordeling is uitgevoerd:',
	'stableversions-review'       => 'Beoordeling uitgevoerd op <i>$1</i>',
	'review-diff2stable'          => 'Wijzigingen tussen de stabiele en huidige versies',
	'unreviewedpages'             => "Pagina's zonder beoordeling",
	'viewunreviewed'              => "Lijst van pagina's zonder beoordeling",
	'unreviewed-outdated'         => "Toon alleen pagina's die nog niet-beoordeelde versies hebben na de stabiele versie.",
	'unreviewed-category'         => 'Categorie:',
	'unreviewed-diff'             => 'Wijzigingen',
	'unreviewed-list'             => "Deze pagina toont pagina's die nog geen beoordeling hebben gehad.",
	'revreview-visibility'        => 'Deze pagina heeft een [[{{MediaWiki:Validationpage}}|stabiele versie]], die [{{fullurl:Special:Stabilization|page={{FULLPAGENAMEE}}}} aangepast] kan worden.',
	'stabilization'               => 'Paginastabilisatie',
	'stabilization-text'          => 'Wijzig de onderstaande instellingen om aan te passen hoe de stabiele versie van [[:$1|$1]] geselecteerd is en getoond wordt.',
	'stabilization-perm'          => 'Uw account heeft niet de toelating om de stabiele versie te wijzigen.
	Dit zijn de huidige instellingen voor [[:$1|$1]]:',
	'stabilization-page'          => 'Paginanaam:',
	'stabilization-leg'           => 'Stabiele versie van een pagina aanpassen',
	'stabilization-select'        => 'Hoe de stabiele versie wordt geselecteerd',
	'stabilization-select1'       => 'De laatste kwaliteitsversie; als die er niet is, dan de laatste beoordeelde versie',
	'stabilization-select2'       => 'De laatste beoordeelde versie',
	'stabilization-def'           => 'Versie die standaard getoond wordt',
	'stabilization-def1'          => 'De stabiele versie',
	'stabilization-def2'          => 'De huidige versie',
	'stabilization-submit'        => 'Bevestigen',
	'stabilization-notexists'     => 'Er is geen pagina "[[:$1|$1]]". Instellen is niet mogelijk.',
	'stabilization-notcontent'    => 'De pagina "[[:$1|$1]]" kan niet beoordeeld worden. Instellen is niet mogelijk.',
	'stabilization-comment'       => 'Opmerking:',
	'stabilization-expiry'        => 'Verloopt:',
	'stabilization-sel-short'     => 'Voorrang',
	'stabilization-sel-short-0'   => 'Kwaliteit',
	'stabilization-sel-short-1'   => 'Geen',
	'stabilization-def-short'     => 'Standaard',
	'stabilization-def-short-0'   => 'Huidig',
	'stabilization-def-short-1'   => 'Stabiel',
	'stabilize_expiry_invalid'    => 'Ongeldige verloopdatum.',
	'stabilize_expiry_old'        => 'Deze verloopdatum is al verstreken.',
	'stabilize-expiring'          => 'verloopt $1 (UTC)',
	'reviewedpages'               => "Beoordeelde pagina's",
	'reviewedpages-leg'           => "Een lijst met pagina's die tot op het aangegeven beoordeeld zijn",
	'reviewedpages-list'          => "De volgende pagina's zijn tot op het geselecteerde niveau beoordeeld",
	'reviewedpages-none'          => "Er staan geen pagina's in deze lijst",
	'reviewedpages-lev-0'         => 'Beoordeeld',
	'reviewedpages-lev-1'         => 'Kwaliteit',
	'reviewedpages-lev-2'         => 'Uitgelicht',
	'reviewedpages-all'           => 'beoordeelde versies',
	'reviewedpages-best'          => 'laatste hoogst gewaardeerde versie',
);

