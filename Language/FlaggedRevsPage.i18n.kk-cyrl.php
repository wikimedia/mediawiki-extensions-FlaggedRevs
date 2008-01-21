<?php
/** Kazakh (Cyrillic) (Қазақша (кирил))
 * @author AlefZet
 */
$messages = array(
	'editor'                      => 'Түзетуші',
	'group-editor'                => 'Түзетушілер',
	'group-editor-member'         => 'түзетуші',
	'grouppage-editor'            => '{{ns:project}}:Түзетуші',
	'reviewer'                    => 'Сын беруші',
	'group-reviewer'              => 'Сын берушілер',
	'group-reviewer-member'       => 'сын беруші',
	'grouppage-reviewer'          => '{{ns:project}}:Сын беруші',
	'revreview-current'           => 'Жоба жазба',
	'tooltip-ca-current'          => 'Бұл беттің ағымдағы жоба жазбасын қарау',
	'revreview-edit'              => 'Жоба жазбаны өңдеу',
	'revreview-source'            => 'жоба жазбалы қайнар',
	'revreview-stable'            => 'Тиянақты',
	'tooltip-ca-stable'           => 'Бұл беттің тиянақты нұсқасын қарау',
	'revreview-oldrating'         => 'Бұл мына баға алды:',
	'revreview-noflagged'         => "Бұл беттің сын берілген нұсқалары мында жоқ, сондықтан бұның сапасы 
'''[[{{MediaWiki:Validationpage}}|тексерілмеген]]''' болуы мүмкін.",
	'stabilization-tab'           => '(сқ)',
	'tooltip-ca-default'          => 'Сапа қамсыздандыруды баптау',
	'validationpage'              => '{{ns:help}}:Мақала ақталуы',
	'revreview-quick-none'        => "'''Ағымдық''' (cын берілген нұсқалар жоқ)",
	'revreview-quick-see-quality' => "'''Жоба жазба''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} тиянақты мақаланы қарау]] 
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|өзгеріс|өзгеріс}}])",
	'revreview-quick-see-basic'   => "'''Жоба жазба''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} тиянақты мақаланы қарау]] 
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|өзгеріс|өзгеріс}}])",
	'revreview-quick-basic'       => "'''[[{{MediaWiki:Validationpage}}|Шолынған]]''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} жоба жазбасын қарау]] 
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|өзгеріс|өзгеріс}}])",
	'revreview-quick-quality'     => "'''[[{{MediaWiki:Validationpage}}|Сапалы]]''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} жоба жазбасын қарау]] 
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|өзгеріс|өзгеріс}}])",
	'revreview-newest-basic'      => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Ең соңғы шолынған нұсқасы] 
([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} барлық тізімі]) <i>$2</i> кезінде [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} бекітілді]. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|өзгерісіне|өзгерісіне}}] сын беруі {{plural:$3|керек|керек}}.',
	'revreview-newest-quality'    => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Ең соңғы сапалы нұсқасы] 
([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} барлығының тізімі]) <i>$2</i> кезінде [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} бекітілді].
[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|өзгерісіне|өзгерісіне}}] сын беруі {{plural:$3|керек|керек}}.',
	'revreview-basic'             => 'Бұл ең соңғы [[{{MediaWiki:Validationpage}}|шолынған]] нұсқа, 
<i>$2</i> кезінде [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} бекітілген]. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Жоба жазбасы] 
[{{fullurl:{{FULLPAGENAMEE}}|action=edit}} өзгертілуі] мүмкін; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|өзгерісі|өзгерісі}}] 
сын беруді {{plural:$3|күтуде|күтуде}}.',
	'revreview-quality'           => 'Бұл ең соңғы [[{{MediaWiki:Validationpage}}|сапалы]] нұсқа, 
<i>$2</i> кезінде [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} бекітілген]. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Жоба жазбасы] 
[{{fullurl:{{FULLPAGENAMEE}}|action=edit}} өзгертілуі] мүмкін; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|өзгерісі|өзгерісі}}] 
сын беруді {{plural:$3|күтуде|күтуде}}.',
	'revreview-static'            => "Бұл '''[[:$3|$3]]''' дегеннің [[{{MediaWiki:Validationpage}}|қарап алынған]] нұсқасы, 
<i>$2</i> кезінде [{{fullurl:Special:Log/review|page=$1}} бекітілген].",
	'revreview-toggle'            => '(+/-)',
	'revreview-note'              => '[[{{ns:user}}:$1]] бұл нұсқаға  [[{{MediaWiki:Validationpage}}|сын бергенде]] келесі аңғартулар жасады:',
	'revreview-update'            => 'Тиянақты нұсқа бекітілгеннен бері жасалған өзгерістерге (төменде көрсетілген) сын беріп шығыңыз.
Кейбір жаңартылған үлгілер/суреттер:',
	'revreview-update-none'       => 'Тиянақты нұсқа бекітілгеннен бері жасалған өзгерістерге (төменде көрсетілген) сын беріп шығыңыз.',
	'revreview-auto'              => '(өздіктік)',
	'revreview-auto-w'            => "Тиянақты нұсқаны өңдеудесіз, өзгерістердің әрқайсысына '''өздіктік сын беріледі'''.
Сақтаудың алдында бетті қарап шығуыңызға болады.",
	'revreview-auto-w-old'        => "Ескі нұсқаны өңдеудесіз, өзгерістердің әрқайсысына '''өздіктік сын беріледі'''.
Сақтаудың алдында бетті қарап шығуыңызға болады.",
	'revreview-patrolled'         => '[[:$1|$1]] дегеннің бөлектелінген нұсқасы күзетте деп белгіленді.',
	'hist-stable'                 => '[шолынған]',
	'hist-quality'                => '[сапалынған]',
	'flaggedrevs'                 => 'Белгіленген нұсқалар',
	'review-logpage'              => 'Мақалаға сын беру журналы',
	'review-logpagetext'          => 'Бұл мағлұмат беттердегі нұсқаларды [[{{MediaWiki:Validationpage}}|бекіту]] күйі
өзгерістерінің журналы.',
	'review-logentry-app'         => '[[$1]] дегенге сын берді',
	'review-logentry-dis'         => '[[$1]] дегеннің нұсқасын кемітті',
	'review-logaction'            => 'нұсқа нөмірі $1',
	'stable-logpage'              => 'Тиянақты нұсқа журналы',
	'stable-logpagetext'          => 'Бұл мағлұмат беттердегі [[{{MediaWiki:Validationpage}}|тиянақты нұсқа]] бапталымы
өзгерістерінің журналы.',
	'stable-logentry'             => '[[$1]] үшін тиянақты нұсқа бапталымы реттелді',
	'stable-logentry2'            => '[[$1]] үшін тиянақты нұсқа бапталымы қайта қойылды',
	'revisionreview'              => 'Нұсқаларға сын беру',
	'revreview-main'              => 'Сын беру үшін мағлұмат бетінің ерекше нұсқасын бөлектеуіңіз керек.

Сын берілмеген бет тізімі үшін [[{{ns:special}}:Unreviewedpages]] бетін қараңыз.',
	'revreview-selected'          => "'''$1:''' дегеннің бөлектелінген нұсқасы",
	'revreview-text'              => 'Тиянақты нұсқалар ең жаңа нұсқасынан гөрі бет көрінісіндегі әдепкі мағлұмат деп тапсырылады.',
	'revreview-toolow'            => 'Нұсқаға сын берілген деп саналуы үшін төмендегі қасиеттердің қай-қайсысын «бекітілмеген»
дегеннен жоғары деңгей беруіңіз керек. Нұсқаны кеміту үшін, барлық өрістерді «бекітілмеген» деп тапсырылсын.',
	'revreview-flag'              => 'Бұл нұсқаға (#$1) сын беру',
	'revreview-legend'            => 'Нұсқа мағлұматына деңгей беру',
	'revreview-notes'             => 'Көрсетілетін пікірлер мен аңғартпалар:',
	'revreview-accuracy'          => 'Дәлдігі',
	'revreview-accuracy-0'        => 'бекітілмеген',
	'revreview-accuracy-1'        => 'шолынған',
	'revreview-accuracy-2'        => 'дәлді',
	'revreview-accuracy-3'        => 'қайнар келтірілген',
	'revreview-accuracy-4'        => 'таңдамалы',
	'revreview-depth'             => 'Кәмілдігі',
	'revreview-depth-0'           => 'бекітілмеген',
	'revreview-depth-1'           => 'іргелі',
	'revreview-depth-2'           => 'орташа',
	'revreview-depth-3'           => 'жоғары',
	'revreview-depth-4'           => 'таңдамалы',
	'revreview-style'             => 'Оқымдылығы',
	'revreview-style-0'           => 'бекітілмеген',
	'revreview-style-1'           => 'тиімді',
	'revreview-style-2'           => 'жақсы',
	'revreview-style-3'           => 'тартымды',
	'revreview-style-4'           => 'таңдамалы',
	'revreview-log'               => 'Мәндемесі:',
	'revreview-submit'            => 'Сын жіберу',
	'revreview-changed'           => "'''Бұл нұсқада сұраным әрекеті орындалмайды.'''

Үлгі не сурет ерекше нұсқа келтірілмегенде сұраналады. Бұл егер осы бетке сын беруді бастағнда 
өзгеретін айналмалыға тәуелді өзгермелі үлгі арқылы басқа суретті не үлгіні ендірген болса болады. 
Бетті жаңарту және қайта сын беру бұл мәселені шешу мүмкін.",
	'stableversions'              => 'Тиянақты нұсқалар',
	'stableversions-leg1'         => 'Сын берілген беттің нұсқа тізімі',
	'stableversions-page'         => 'Бет атауы:',
	'stableversions-none'         => '«[[:$1]]» бетінде сын берілген еш нұсқа жоқ.',
	'stableversions-list'         => 'Келесі тізімде «[[:$1]]» бетінің сын берілген нұсқалары келтіріледі:',
	'stableversions-review'       => '$2 <i>$1</i> кезінде сын берді',
	'review-diff2stable'          => 'Тиянақты мен ағымдық нұсқалар арадағы өзгерістер',
	'unreviewedpages'             => 'Сын берілмеген беттер',
	'viewunreviewed'              => 'Сын берілмеген мағлұмат беттерді тізімдеу',
	'unreviewed-outdated'         => 'Бұның орнына тиянақты нұсқаға жасалған сын берілмеген нұсқалары бар беттерді көрсет.',
	'unreviewed-category'         => 'Санат:',
	'unreviewed-diff'             => 'Өзгерістер',
	'unreviewed-list'             => 'Бұл бетте сын берілмеген мақалалар не жаңадан жасалған, сын берілмеген, нұсқалары бар мақалар тізімделінеді.',
	'revreview-visibility'        => 'Осы беттің [[{{MediaWiki:Validationpage}}|тиянақты нұсқасы]] бар, бұл 
[{{fullurl:Special:Stabilization|page={{FULLPAGENAMEE}}}} бапталауы] мүмкін.',
	'stabilization'               => 'Бетті тиянақтау',
	'stabilization-text'          => 'Төмендегі бапталымдарды өзгерткенде [[:$1|$1]] дегеннің тиянақты нұсқасы қалай бөлектенуі мен көрсетілуі түзетіледі.',
	'stabilization-perm'          => 'Тіркелгіңізге тиянақты нұсқаның бапталымын өзгертуге рұқсат берілмеген.
[[:$1|$1]] үшін ағымдағы баптаулар мында келтіріледі:',
	'stabilization-page'          => 'Бет атауы:',
	'stabilization-leg'           => 'Бет үшін тиянақты нұсқаны баптау',
	'stabilization-select'        => 'Тиянақты нұсқа қалай бөлектенеді',
	'stabilization-select1'       => 'Ең соңғы сапалы нұсқасы; егер жоқ болса, ең соңғы шолынғандардың біреуі болады',
	'stabilization-select2'       => 'Ең соңғы сын берілген нұсқа',
	'stabilization-def'           => 'Беттің әдепкі көрінісінде келтірілетін нұсқа',
	'stabilization-def1'          => 'Тиянақты нұсқасы; егер жоқ болса, ағымдағылардың біреуі болады',
	'stabilization-def2'          => 'Ағымдық нұсқасы',
	'stabilization-submit'        => 'Құптау',
	'stabilization-notexists'     => '«[[:$1|$1]]» деп аталған еш бет жоқ. Еш бапталым реттелмейді.',
	'stabilization-notcontent'    => '«[[:$1|$1]]» деген бетке сын берілмейді. Еш бапталым реттелмейді.',
	'stabilization-comment'       => 'Мәндеме:',
	'stabilization-sel-short'     => 'Артықшылық',
	'stabilization-sel-short-0'   => 'Сапалы',
	'stabilization-sel-short-1'   => 'Ешқандай',
	'stabilization-def-short'     => 'Әдепкі',
	'stabilization-def-short-0'   => 'Ағымдық',
	'stabilization-def-short-1'   => 'Тиянақты',
	'reviewedpages'               => 'Сын берілген беттер',
	'reviewedpages-leg'           => 'Анық деңгейде сын берілген беттерді тізімдеу',
	'reviewedpages-list'          => 'Келесі беттерге келтірілген деңгейде сын берілген',
	'reviewedpages-none'          => 'Бұл тізімде еш бет жоқ',
	'reviewedpages-lev-0'         => 'шолынған',
	'reviewedpages-lev-1'         => 'сапалы',
	'reviewedpages-lev-2'         => 'таңдамалы',
	'reviewedpages-all'           => 'сын берілген нұсқалары',
	'reviewedpages-best'          => 'ең соңғы ең жоғары деңгей берілген нұсқасы',

);
