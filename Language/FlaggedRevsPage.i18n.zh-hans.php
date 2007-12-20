<?php
// Chinese (Simplified) (Shinjiman)
$messages = array(
	'validationpage' => '{{ns:help}}:文章确认',
	'makevalidate-autosum'  => '自动升格',
	'editor'                => '编辑',
	'group-editor'          => '编辑',
	'group-editor-member'   => '编辑',
	'grouppage-editor'      => '{{ns:project}}:编者',
	'reviewer'              => '评论家',
	'group-reviewer'        => '评论家',
	'group-reviewer-member' => '评论家',
	'grouppage-reviewer'    => '{{ns:project}}:评论家',
	'revreview-current'     => '草稿',
	'revreview-edit'        => '编辑草稿',
	'revreview-source'      => '草稿原始码',
	'revreview-stable'      => '稳定',
	'revreview-oldrating'   => '曾经评定为:',
	'revreview-noflagged'   => '这一页没有复审过的修订，它可能\'\'\'未\'\'\'[[{{MediaWiki:Validationpage}}|检查]]质量。',
	'revreview-quick-none'  => '\'\'\'现时的\'\'\'。没有已复审的修订。',
	'revreview-quick-see-quality' => '\'\'\'现时的\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 看睇最后的质素修订]]',
	'revreview-quick-see-basic' => '\'\'\'现时的\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 看最后检查过的修订]]',
	'revreview-quick-basic' => '\'\'\'[[{{MediaWiki:Validationpage}}|视察过的]]\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 看现时修订]] 
	($2[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} 次更改])',
	'revreview-quick-quality' => '\'\'\'[[{{MediaWiki:Validationpage}}|有质素的]]\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 看现时修订]] 
	($2[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} 次更改])',
	'revreview-newest-basic' => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 最后视察过的修订] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} 列示全部]) 于<i>$2</i>曾经[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准过的]。
	 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改]需要复审。',
	'revreview-newest-quality' => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 最后有质素的修订] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} 列示全部]) 于<i>$2</i>曾经[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准过的]。
	 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改]需要复审。',
	'revreview-basic'       => '这个是最后[[{{MediaWiki:Validationpage}}|视察过的]]修订，
	于<i>$2</i>[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准]。[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 现时修订]
	可以[{{fullurl:{{FULLPAGENAMEE}}|action=edit}} 更改]；[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改] 
	正等候复审。',
	'revreview-quality'     => '这个是最后[[{{MediaWiki:Validationpage}}|有质素的]]修订，
	于<i>$2</i>[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准]。[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 现时修订] 
	可以[{{fullurl:{{FULLPAGENAMEE}}|action=edit}} 更改]]；[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改] 
	正等候复审。',
	'revreview-static'      => '这个是一个在\'\'\'[[:$3|这页]]\'\'\'[[{{MediaWiki:Validationpage}}|复审过的]]修订，
	于<i>$2</i>[{{fullurl:Special:Log/review|page=$1}} 批准]。[{{fullurl:$3|stable=0}} 现时修订]
	可以[{{fullurl:$3|action=edit}} 更改]。',
	'revreview-note'        => '[[User:$1]]在这次修订中加入了以下的[[{{MediaWiki:Validationpage}}|复审]]注解:',
	'revreview-update'      => '请复审自从于这页的稳定版以来的任何更改 (在下面显示) 。模版和图像亦可能同时更改。',
	'revreview-auto'        => '(自动)',
	'revreview-auto-w'      => '\'\'\'注意:\'\'\' 您现在是在稳定修订中作出更改，您的编辑将会自动被复审。
	您可以在保存前先预览一下。',
	'hist-stable'           => '[已察]',
	'hist-quality'          => '[质素]',
	'flaggedrevs'           => '标注修订',
	'review-logpage'        => '文章复审记录',
	'review-logpagetext'    => '这个是内容页[[{{MediaWiki:Validationpage}}|批准]]状态的更改记录。',
	'review-logaction'      => '修订 ID $1',
	'revisionreview'        => '复审修订',
	'revreview-main'        => '您一定要在一页的内容页中选择一个个别的修订去复审。

	参看[[Special:Unreviewedpages]]去撷取未复审的页面。',
	'revreview-selected'    => '已经选择 \'\'\'$1\'\'\' 的修订:',
	'revreview-text'        => '稳定版会设置成一页查看时的预设内容，而非最新的修订。',
	'revreview-toolow'      => '您一定要最少将下面每一项的属性评定高于"未批准"，去将一个修订复审。
	要舍弃一个修订，设置全部栏位作"未批准"。',
	'revreview-flag'        => '复审这次修订 (#$1)',
	'revreview-legend'      => '评定修订内容',
	'revreview-notes'       => '要显示的意见或注解:',
	'revreview-accuracy'    => '准确度',
	'revreview-accuracy-0'  => '未批准',
	'revreview-accuracy-1'  => '视察过',
	'revreview-accuracy-2'  => '准确',
	'revreview-accuracy-3'  => '有良好来源',
	'revreview-accuracy-4'  => '特色',
	'revreview-depth'       => '深度',
	'revreview-depth-0'     => '未批准',
	'revreview-depth-1'     => '基本',
	'revreview-depth-2'     => '中等',
	'revreview-depth-3'     => '高',
	'revreview-depth-4'     => '特色',
	'revreview-style'       => '可读性',
	'revreview-style-0'     => '未批准',
	'revreview-style-1'     => '可接受',
	'revreview-style-2'     => '好',
	'revreview-style-3'     => '简洁',
	'revreview-style-4'     => '特色',
	'revreview-log'         => '记录注解:',
	'revreview-submit'      => '递交复审',
	'revreview-changed'     => '\'\'\'该复审的动作不可以在这次修订中进行。\'\'\'
	
	当无一个指定的版本时，一个模版或图像已经被请求。
	当一个动态模版包含着图像或跟变数的模版在您开始复审后改过。 
	重新整理这页后再重新复审便可以解决这个问题。',
	'stableversions'        => '稳定版',
	'stableversions-leg1'   => '列示一版已复审的修订',
	'stableversions-page'   => '页面名',
	'stableversions-none'   => '[[:$1]]没有已复审过的修订。',
	'stableversions-list'   => '以下是[[:$1]]已复审的修订一览:',
	'stableversions-review' => '于<i>$1</i>复审',
	'review-diff2stable'    => '跟上次稳定修订的差异',
	'review-diff2oldest'    => '跟最旧修订的差异',
	'unreviewedpages'       => '未复审页面',
	'viewunreviewed'        => '列示未复审的内容页',
	'unreviewed-outdated'   => '只显示对稳定版修订过的未复审修订。',
	'unreviewed-category'   => '分类:',
	'unreviewed-diff'       => '更改',
	'unreviewed-list'       => '这一页列示出还未复审或视察的文章修订。',
);
