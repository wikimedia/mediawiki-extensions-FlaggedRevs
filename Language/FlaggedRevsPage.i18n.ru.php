<?php
/** Russian (Русский)
 * @author .:Ajvol:.
 */
$messages = array(
	'editor'                      => 'редактор',
	'group-editor'                => 'редакторы',
	'group-editor-member'         => 'редактор',
	'grouppage-editor'            => '{{ns:project}}:Редактор',
	'reviewer'                    => 'рецензент',
	'group-reviewer'              => 'рецензенты',
	'group-reviewer-member'       => 'рецензент',
	'grouppage-reviewer'          => '{{ns:project}}:Рецензент',
	'revreview-current'           => 'черновик',
	'tooltip-ca-current'          => 'Просмотреть текущий черновик этой страницы',
	'revreview-edit'              => 'Править черновик',
	'revreview-source'            => 'исходный текст черновика',
	'revreview-stable'            => 'Чистовик',
	'tooltip-ca-stable'           => 'Просмотреть чистовик этой страницы',
	'revreview-oldrating'         => 'Была оценена:',
	'revreview-noflagged'         => "У этой страницы нет отрецензированных версий, вероятно, её качество '''не''' [[{{MediaWiki:Validationpage}}|оценивалось]].",
	'stabilization-tab'           => '(кк)',
	'tooltip-ca-default'          => 'Настройки контроля качества',
	'validationpage'              => '{{ns:help}}:Проверка статьи',
	'revreview-quick-none'        => "'''Текущая''' (нет отрецензированных версий)",
	'revreview-quick-see-quality' => "'''Черновик''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} см. чистовик]]  
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|правка|правки|правок}}])",
	'revreview-quick-see-basic'   => "'''Черновик''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} см. чистовик]]  
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|правка|правки|правок}}])",
	'revreview-quick-basic'       => "'''[[{{MediaWiki:Validationpage}}|Просмотренная]]''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} см. черновик]]  
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|правка|правки|правок}}])",
	'revreview-quick-quality'     => "'''[[{{MediaWiki:Validationpage}}|Качественная]]''' [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} см. черновик]]  
($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|правка|правки|правок}}])",
	'revreview-newest-basic'      => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Последняя просмотренная версия]  
([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} список всех]) была [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} отмечена]
<i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|правка|правки|правок}}] {{plural:$3|требует|требуют|требуют}} просмотра.',
	'revreview-newest-quality'    => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Последняя качественная версия]  
([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} список всех]) была [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} отмечена]
<i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|правка|правки|правок}}] {{plural:$3|требует|требуют|требуют}} просмотра.',
	'revreview-basic'             => 'Это последняя [[{{MediaWiki:Validationpage}}|просмотренная]] версия,  
[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} отмеченная] <i>$2</i>. Возможно, [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} черновик]  
уже [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} изменён]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|правка|правки|правок}}] {{plural:$3|ожидает|ожидают|ожидают}} просмотра.',
	'revreview-quality'           => 'Это последняя [[{{MediaWiki:Validationpage}}|качественная]] версия,  
[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} отмеченная] <i>$2</i>. Возможно, [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} черновик]  
уже [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} изменён]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|правка|правки|правок}}] {{plural:$3|ожидает|ожидают|ожидают}} просмотра.',
	'revreview-static'            => "Это [[{{MediaWiki:Validationpage}}|отрецензированная]] версия '''[[:$3|$3]]''',  
[{{fullurl:Special:Log/review|page=$1}} отмеченная] <i>$2</i>.",
	'revreview-toggle'            => '(+/-)',
	'revreview-note'              => '[[Участник:$1]] сделал следующее замечание, [[{{MediaWiki:Validationpage}}|рецензирую]] эту версию:',
	'stableversions'              => 'Стабильные версии',
	'stableversions-leg1'         => 'Список выверенных версий страницы',
	'stableversions-page'         => 'Название страницы:',
	'stableversions-none'         => '«[[:$1]]» не имеет выверенных версий.',
	'stableversions-list'         => 'Следующие версии страницы «[[:$1]]» были выверены:',
	'stableversions-review'       => 'Выверена <i>$1</i> участником $2',
);
