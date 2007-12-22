<?php
/** Cantonese (粵語)
 * @author Shinjiman
 */
$messages = array(
	'editor'                      => '編輯',
	'group-editor'                => '編輯',
	'group-editor-member'         => '編輯',
	'grouppage-editor'            => '{{ns:project}}:編者',
	'reviewer'                    => '評論家',
	'group-reviewer'              => '評論家',
	'group-reviewer-member'       => '評論家',
	'grouppage-reviewer'          => '{{ns:project}}:評論家',
	'revreview-current'           => '草稿',
	'revreview-edit'              => '編輯草稿',
	'revreview-source'            => '草稿原始碼',
	'revreview-stable'            => '穩定',
	'revreview-oldrating'         => '曾經評定為:',
	'revreview-noflagged'         => "呢一版無複審過嘅修訂，佢可能'''未'''[[{{MediaWiki:Validationpage}}|檢查]]質量。",
	'validationpage'              => '{{ns:help}}:文章確認',
	'revreview-quick-none'        => "'''現時嘅'''。無已複審嘅修訂。",
	'revreview-quick-see-quality' => "'''現時嘅'''。[[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 睇最後嘅質素修訂]]",
	'revreview-quick-see-basic'   => "'''現時嘅'''。[[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 睇最後檢查過嘅修訂]]",
	'revreview-quick-basic'       => "'''[[{{MediaWiki:Validationpage}}|視察過嘅]]'''。[[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 睇現時修訂]] 
	($2[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} 次更改])",
	'revreview-quick-quality'     => "'''[[{{MediaWiki:Validationpage}}|有質素嘅]]'''。[[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 睇現時修訂]] 
	($2[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} 次更改])",
	'revreview-newest-basic'      => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 最後視察過嘅修訂] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} 列示全部]) 響<i>$2</i>曾經[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准過嘅]。
	 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改]需要複審。',
	'revreview-newest-quality'    => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 最後有質素嘅修訂] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} 列示全部]) 響<i>$2</i>曾經[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准過嘅]。
	 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改]需要複審。',
	'revreview-basic'             => '呢個係最後[[{{MediaWiki:Validationpage}}|視察過嘅]]修訂，
	響<i>$2</i>[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准]。[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 現時修訂]
	可以[{{fullurl:{{FULLPAGENAMEE}}|action=edit}} 改]；[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改] 
	等緊去複審。',
	'revreview-quality'           => '呢個係最後[[{{MediaWiki:Validationpage}}|有質素嘅]]修訂，
	響<i>$2</i>[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准]。[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 現時修訂] 
	可以[{{fullurl:{{FULLPAGENAMEE}}|action=edit}} 改]]；[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改] 
	等緊去複審。',
	'revreview-static'            => "呢個係一個響'''[[:$3|呢版]]'''[[{{MediaWiki:Validationpage}}|複審過嘅]]修訂，
	響<i>$2</i>[{{fullurl:Special:Log/review|page=$1}} 批准]。[{{fullurl:$3|stable=0}} 現時修訂]
	可以[{{fullurl:$3|action=edit}} 改]。",
	'revreview-note'              => '[[User:$1]]響呢次修訂度加咗下面嘅[[{{MediaWiki:Validationpage}}|複審]]註解:',
	'revreview-update'            => '請複審自從響呢版嘅穩定版以來嘅任何更改 (響下面度顯示) 。模同圖亦可能同時更改。',
	'revreview-auto'              => '(自動)',
	'revreview-auto-w'            => "'''注意:''' 你而家係響穩定修訂度做緊更改，你嘅編輯將會自動被複審。
	你可以響保存之前先預覽一吓。",
	'hist-stable'                 => '[睇過]',
	'hist-quality'                => '[質素]',
	'flaggedrevs'                 => '加咗旗嘅修訂',
	'review-logpage'              => '文章複審記錄',
	'review-logpagetext'          => '呢個係內容版[[{{MediaWiki:Validationpage}}|批准]]狀態嘅更改記錄。',
	'review-logaction'            => '修訂 ID $1',
	'revisionreview'              => '複審修訂',
	'revreview-main'              => '你一定要響一版內容頁度揀一個個別嘅修訂去複審。

	睇[[Special:Unreviewedpages]]去拎未複審嘅版。',
	'revreview-selected'          => "已經揀咗 '''$1''' 嘅修訂:",
	'revreview-text'              => '穩定版會設定做一版睇嗰陣嘅預設內容，而唔係最新嘅修訂。',
	'revreview-toolow'            => '你一定要最少將下面每一項嘅屬性評定高過"未批准"，去將一個修訂複審。
	要捨棄一個修訂，設定全部格做"未批准"。',
	'revreview-flag'              => '複審呢次修訂 (#$1)',
	'revreview-legend'            => '評定修訂內容',
	'revreview-notes'             => '要顯示嘅意見或註解:',
	'revreview-accuracy'          => '準確度',
	'revreview-accuracy-0'        => '未批准',
	'revreview-accuracy-1'        => '視察過',
	'revreview-accuracy-2'        => '準確',
	'revreview-accuracy-3'        => '有好來源',
	'revreview-accuracy-4'        => '正',
	'revreview-depth'             => '深度',
	'revreview-depth-0'           => '未批准',
	'revreview-depth-1'           => '基本',
	'revreview-depth-2'           => '中等',
	'revreview-depth-3'           => '高',
	'revreview-depth-4'           => '正',
	'revreview-style'             => '可讀性',
	'revreview-style-0'           => '未批准',
	'revreview-style-1'           => '可接受',
	'revreview-style-2'           => '好',
	'revreview-style-3'           => '簡潔',
	'revreview-style-4'           => '正',
	'revreview-log'               => '記錄註解:',
	'revreview-submit'            => '遞交複審',
	'revreview-changed'           => "'''個複審嘅動作唔可以響呢次修訂度進行。'''
	
	當無一個指定嘅版本嗰陣，一個模或圖已經被請求。
	當一個動態模包含住圖像或跟變數嘅模響你開始複審之後改過。 
	重新整理過呢版之後再重新複審就可以解決呢個問題。",
	'stableversions'              => '穩定版',
	'stableversions-leg1'         => '列示一版複審過嘅修訂',
	'stableversions-page'         => '版名',
	'stableversions-none'         => '[[:$1]]無複審過嘅修訂。',
	'stableversions-list'         => '下面係[[:$1]]已經複審過嘅修訂一覽:',
	'stableversions-review'       => '響<i>$1</i>複審過',
	'review-diff2stable'          => '同上次穩定修訂嘅差異',
	'unreviewedpages'             => '未複審嘅版',
	'viewunreviewed'              => '列示未複審嘅內容版',
	'unreviewed-outdated'         => '只係顯示對穩定版修訂過嘅未複審修訂。',
	'unreviewed-category'         => '分類:',
	'unreviewed-diff'             => '更改',
	'unreviewed-list'             => '呢一版列示出重未複審或視察過嘅文章修訂。',
);
