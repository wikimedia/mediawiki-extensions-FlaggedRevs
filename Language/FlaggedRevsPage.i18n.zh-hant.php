<?php
// Chinese (Traditional) (Shinjiman)
$messages = array(
	'validationpage' => '{{ns:help}}:文章確認',
	'makevalidate-autosum'  => '自動升格',
	'editor'                => '編輯',
	'group-editor'          => '編輯',
	'group-editor-member'   => '編輯',
	'grouppage-editor'      => '{{ns:project}}:編者',
	'reviewer'              => '評論家',
	'group-reviewer'        => '評論家',
	'group-reviewer-member' => '評論家',
	'grouppage-reviewer'    => '{{ns:project}}:評論家',
	'revreview-current'     => '草稿',
	'revreview-edit'        => '編輯草稿',
	'revreview-source'      => '草稿原始碼',
	'revreview-stable'      => '穩定',
	'revreview-oldrating'   => '曾經評定為:',
	'revreview-noflagged'   => '這一頁沒有複審過的修訂，它可能\'\'\'未\'\'\'[[{{MediaWiki:Validationpage}}|檢查]]質量。',
	'revreview-quick-none'  => '\'\'\'現時的\'\'\'。沒有已複審的修訂。',
	'revreview-quick-see-quality' => '\'\'\'現時的\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 看睇最後的質素修訂]]',
	'revreview-quick-see-basic' => '\'\'\'現時的\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 看最後檢查過的修訂]]',
	'revreview-quick-basic' => '\'\'\'[[{{MediaWiki:Validationpage}}|視察過的]]\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 看現時修訂]] 
	($2[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} 次更改])',
	'revreview-quick-quality' => '\'\'\'[[{{MediaWiki:Validationpage}}|有質素的]]\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 看現時修訂]] 
	($2[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} 次更改])',
	'revreview-newest-basic' => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 最後視察過的修訂] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} 列示全部]) 於<i>$2</i>曾經[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准過的]。
	 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改]需要複審。',
	'revreview-newest-quality' => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 最後有質素的修訂] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} 列示全部]) 於<i>$2</i>曾經[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准過的]。
	 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改]需要複審。',
	'revreview-basic'       => '這個是最後[[{{MediaWiki:Validationpage}}|視察過的]]修訂，
	於<i>$2</i>[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准]。[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 現時修訂]
	可以[{{fullurl:{{FULLPAGENAMEE}}|action=edit}} 更改]；[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改] 
	正等候複審。',
	'revreview-quality'     => '這個是最後[[{{MediaWiki:Validationpage}}|有質素的]]修訂，
	於<i>$2</i>[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准]。[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 現時修訂] 
	可以[{{fullurl:{{FULLPAGENAMEE}}|action=edit}} 更改]]；[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改] 
	正等候複審。',
	'revreview-static'      => '這個是一個在\'\'\'[[:$3|這頁]]\'\'\'[[{{MediaWiki:Validationpage}}|複審過的]]修訂，
	於<i>$2</i>[{{fullurl:Special:Log/review|page=$1}} 批准]。[{{fullurl:$3|stable=0}} 現時修訂]
	可以[{{fullurl:$3|action=edit}} 更改]。',
	'revreview-note'        => '[[User:$1]]在這次修訂中加入了以下的[[{{MediaWiki:Validationpage}}|複審]]註解:',
	'revreview-update'      => '請複審自從於這頁的穩定版以來的任何更改 (在下面顯示) 。模版和圖像亦可能同時更改。',
	'revreview-auto'        => '(自動)',
	'revreview-auto-w'      => '\'\'\'注意:\'\'\' 您現在是在穩定修訂中作出更改，您的編輯將會自動被複審。
	您可以在保存前先預覽一下。',
	'hist-stable'           => '[已察]',
	'hist-quality'          => '[質素]',
	'flaggedrevs'           => '標註修訂',
	'review-logpage'        => '文章複審記錄',
	'review-logpagetext'    => '這個是內容頁[[{{MediaWiki:Validationpage}}|批准]]狀態的更改記錄。',
	'review-logaction'      => '修訂 ID $1',
	'revisionreview'        => '複審修訂',
	'revreview-main'        => '您一定要在一頁的內容頁中選擇一個個別的修訂去複審。

	參看[[Special:Unreviewedpages]]去擷取未複審的頁面。',
	'revreview-selected'    => '已經選擇 \'\'\'$1\'\'\' 的修訂:',
	'revreview-text'        => '穩定版會設定成一頁檢視時的預設內容，而非最新的修訂。',
	'revreview-toolow'      => '您一定要最少將下面每一項的屬性評定高於"未批准"，去將一個修訂複審。
	要捨棄一個修訂，設定全部欄位作"未批准"。',
	'revreview-flag'        => '複審這次修訂 (#$1)',
	'revreview-legend'      => '評定修訂內容',
	'revreview-notes'       => '要顯示的意見或註解:',
	'revreview-accuracy'    => '準確度',
	'revreview-accuracy-0'  => '未批准',
	'revreview-accuracy-1'  => '視察過',
	'revreview-accuracy-2'  => '準確',
	'revreview-accuracy-3'  => '有良好來源',
	'revreview-accuracy-4'  => '特色',
	'revreview-depth'       => '深度',
	'revreview-depth-0'     => '未批准',
	'revreview-depth-1'     => '基本',
	'revreview-depth-2'     => '中等',
	'revreview-depth-3'     => '高',
	'revreview-depth-4'     => '特色',
	'revreview-style'       => '可讀性',
	'revreview-style-0'     => '未批准',
	'revreview-style-1'     => '可接受',
	'revreview-style-2'     => '好',
	'revreview-style-3'     => '簡潔',
	'revreview-style-4'     => '特色',
	'revreview-log'         => '記錄註解:',
	'revreview-submit'      => '遞交複審',
	'revreview-changed'     => '\'\'\'該複審的動作不可以在這次修訂中進行。\'\'\'
	
	當無一個指定的版本時，一個模版或圖像已經被請求。
	當一個動態模版包含著圖像或跟變數的模版在您開始複審後改過。 
	重新整理這頁後再重新複審便可以解決這個問題。',
	'stableversions'        => '穩定版',
	'stableversions-leg1'   => '列示一版已複審的修訂',
	'stableversions-page'   => '頁面名',
	'stableversions-none'   => '[[:$1]]沒有已複審過的修訂。',
	'stableversions-list'   => '以下是[[:$1]]已複審的修訂一覽:',
	'stableversions-review' => '於<i>$1</i>複審',
	'review-diff2stable'    => '跟上次穩定修訂的差異',
	'review-diff2oldest'    => '跟最舊修訂的差異',
	'unreviewedpages'       => '未複審頁面',
	'viewunreviewed'        => '列示未複審的內容頁',
	'unreviewed-outdated'   => '只顯示對穩定版修訂過的未複審修訂。',
	'unreviewed-category'   => '分類:',
	'unreviewed-diff'       => '更改',
	'unreviewed-list'       => '這一頁列示出還未複審或視察的文章修訂。',
);
