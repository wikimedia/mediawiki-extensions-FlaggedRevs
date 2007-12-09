<?php
/* Arabic (Meno25) */
$messages = array(
	'editor'                    => 'محرر',
	'group-editor'              => 'محررون',
	'group-editor-member'       => 'محرر',
	'grouppage-editor'          => '{{ns:project}}:محرر',
	'reviewer'                  => 'مراجع',
	'group-reviewer'            => 'مراجعون',
	'group-reviewer-member'     => 'مراجع',
	'grouppage-reviewer'        => '{{ns:project}}:مراجع',
	'revreview-current'         => 'مسودة',
	'tooltip-ca-current'        => 'عرض المسودة الحالية لهذه الصفحة',
	'revreview-edit'            => 'عدل المسودة',
	'revreview-source'          => 'مصدر المسودة',
	'revreview-stable'          => 'مستقرة',
	'tooltip-ca-stable'         => 'عرض النسخة المستقرة لهذه الصفحة',
	'revreview-oldrating'       => 'تم تقييمها ك:',
	'revreview-noflagged'       => 'لا توجد نسخ مراجعة لهذه الصفحة، لذا ربما \'\'\'لا\'\'\' تكون قد تم 
	[[{{MediaWiki:Makevalidate-page}}|التحقق من]] جودتها.',
	'stabilization-tab'         => '(تج)',
	'tooltip-ca-default'        => 'إعدادات تأكيد الجودة',
	'revreview-quick-none'      => '\'\'\'الحالي\'\'\'. لا نسخ مراجعة.',
	'revreview-quick-see-quality' => '\'\'\'حالي\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} انظر آخر نسخة جودة]',
	'revreview-quick-see-basic' => '\'\'\'حالي\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} عرض آخر نسخة منظورة]',
	'revreview-quick-basic'     => '\'\'\'منظورة\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} عرض النسخة الحالية] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|تغيير|تغييرات}}])',
	'revreview-quick-quality'   => '\'\'\'جودة\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} عرض النسخة الحالية] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|تغيير|تغييرات}}])',
	'revreview-newest-basic'    => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} النسخة الأخيرة المنظورة] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} عرض الكل]) تم [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} الموافقة عليها]
	 في <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|تغيير|تغييرات}}] {{plural:$3|تحتاج|تحتاج}} مراجعة.',
	'revreview-newest-quality'  => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} نسخة الجودة الأخيرة] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} عرض الكل]) تم [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} الموافقة عليها]
	 في <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|تغيير|تغييرات}}] {{plural:$3|تحتاج|تحتاج}} مراجعة.',
	'revreview-basic'           => 'هذه آخر نسخة [[{{MediaWiki:Makevalidate-page}}|منظورة]] ، 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} تمت الموافقة عليها] في <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} النسخة الحالية] 
	يمكن [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} تعديلها]؛ [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|تغيير|تغييرات}}] 
	{{plural:$3|تنتظر|تنتظر}} مراجعة.',
	'revreview-quality'         => 'هذه آخر نسخة [[{{MediaWiki:Makevalidate-page}}|جودة]], 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} تمت الموافقة عليها] في <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} النسخة الحالية] 
	يمكن [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} تعديلها]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|تغيير|تغييرات}}] 
	{{plural:$3|تنتظر|تنتظر}} مراجعة.',
	'revreview-static'          => 'هذه هي النسخة [[{{MediaWiki:Makevalidate-page}}|المراجعة]] من \'\'\'[[:$3|هذه الصفحة]]\'\'\', 
	[{{fullurl:Special:Log/review|page=$1}} تمت الموافقة عليها] في <i>$2</i>. The [{{fullurl:$3|stable=0}} النسخة الحالية] 
	يمكن [{{fullurl:$3|action=edit}} تعديلها].',
	'revreview-toggle'          => '(+/-)',#identical but defined
	'revreview-note'            => '[[User:$1]] كتب الملاحظات التالية [[{{MediaWiki:Makevalidate-page}}|عند مراجعة]] هذه النسخة:',
	'revreview-update'          => 'من فضلك راجع أية تغييرات (معروضة بالأسفل) تمت منذ النسخة المستقرة لهذه الصفحة. القوالب و الصور
ربما تكون قد تغيرت أيضا.',
	'revreview-update-none'     => 'من فضلك راجع أية تغييرات (معروضة بالأسفل) منذ النسخة المستقرة لهذه الصفحة.',
	'revreview-auto'            => '(تلقائيا)',
	'revreview-auto-w'          => '\'\'\'ملاحظة:\'\'\' أنت تقوم بتغييرات للنسخة المستقرة، تعديلاتك سيتم مراجعتها تلقائيا. 
ربما تريد أن تعرض الصفحة عرضا مسبقا قبل الحفظ.',
	'revreview-auto-w-old'      => 'أنت تحرر نسخة قديمة، أية تغييرات ستتم \'\'\'مراجعتها تلقائيا\'\'\'. 
	ربما تريد عرض الصفحة عرضا مسبقا قبل الحفظ.',
	'hist-stable'               => '[منظورة]',
	'hist-quality'              => '[الجودة]',
	'flaggedrevs'               => 'نسخ معلمة',
	'review-logpage'            => 'سجل مراجعة المقالة',
	'review-logpagetext'        => 'هذا سجل بالتغييرات لحالة\' [[{{MediaWiki:Makevalidate-page}}|الموافقة]] لصفحات المحتوى.',
	'review-logentry-app'       => 'راجع $1',
	'review-logentry-dis'       => 'أزال نسخة من $1',
	'review-logaction'          => 'رقم النسخة $1',
	'stable-logpage'            => 'سجل النسخة المستقرة',
	'stable-logpagetext'        => 'هذا سجل بالتغييرات لضبط [[{{MediaWiki:Makevalidate-page}}|النسخة المستقرة]] 
	لصفحات المحتوى.',
	'stable-logentry'           => 'ضبط النسخة المستقرة ل[[$1]]',
	'stable-logentry2'          => 'أعاد ضبط النسخة المستقرة ل[[$1]]',
	'revisionreview'            => 'مراجعة النسخ',
	'revreview-main'            => 'يجب أن تختار نسخة معينة من صفحة محتوى لمراجعتها. 

	انظر [[Special:Unreviewedpages]] لقائمة الصفحات غير المراجعة.',
	'revreview-selected'        => 'النسخة المختارة لصفحة \'\'\'$1:\'\'\'',
	'revreview-text'            => 'النسخ المستقرة محددة كالمحتوى القياسي عند عرض الصفحة و ليس أحدث نسخة.',
	'revreview-toolow'          => 'يجب عليك على الأقل تقييم كل من المحددات بالأسفل أعلى من "غير مقبولة" لكي 
تعتبر النسخة مراجعة. لسحب نسخة, اضبط كل الحقول ك "غير مقبولة".',
	'revreview-flag'            => 'راجع هذه النسخة (#$1):',
	'revreview-legend'          => 'قيم محتوى النسخة',
	'revreview-notes'           => 'الملاحظات للعرض:',
	'revreview-accuracy'        => 'الدقة',
	'revreview-accuracy-0'      => 'غير موافق',
	'revreview-accuracy-1'      => 'منظورة',
	'revreview-accuracy-2'      => 'دقيقة',
	'revreview-accuracy-3'      => 'مصادرها جيدة',
	'revreview-accuracy-4'      => 'مميزة',
	'revreview-depth'           => 'العمق',
	'revreview-depth-0'         => 'غير موافق',
	'revreview-depth-1'         => 'أساسي',
	'revreview-depth-2'         => 'متوسط',
	'revreview-depth-3'         => 'مرتفع',
	'revreview-depth-4'         => 'مميز',
	'revreview-style'           => 'القابلية للقراءة',
	'revreview-style-0'         => 'غير مقبول',
	'revreview-style-1'         => 'مقبول',
	'revreview-style-2'         => 'جيدة',
	'revreview-style-3'         => 'متوسطة',
	'revreview-style-4'         => 'مميزة',
	'revreview-log'             => 'تعليق السجل:',
	'revreview-submit'          => 'تنفيذ المراجعة',
	'revreview-changed'         => '\'\'\'الأمر المطلوب لم يمكن إجراؤه على هذه النسخة.\'\'\'
	
	قالب أو صورة ربما يكون قد تم طلبه عندما لم يتم تحديد نسخة معينة. هذا يمكن أن يحدث لو 
	قالب ديناميكي يضمن صورة أخرى أو قالب معتمدا على متغير تغير منذ أن بدأت 
مراجعة هذه الصفحة. تحديث الصفحة وإعادة المراجعة يمكن أن يحل هذه المشكلة.',
	'stableversions'            => 'نسخ مستقرة',
	'stableversions-leg1'       => 'عرض النسخ المراجعة لصفحة',
	'stableversions-page'       => 'اسم الصفحة',
	'stableversions-none'       => '[[:$1]] لا يوجد بها نسخ مراجعة.',
	'stableversions-list'       => 'هذه قائمة بنسخ صفحة [[:$1]] التي تم مراجعتها:',
	'stableversions-review'     => 'تمت مراجعتها في <i>$1</i>',
	'review-diff2stable'        => 'فرق لآخر نسخة مستقرة',
	'review-diff2oldest'        => 'الفرق مع أقدم نسخة',
	'unreviewedpages'           => 'صفحات غير مراجعة',
	'viewunreviewed'            => 'عرض صفحات المحتوى غير المراجعة',
	'unreviewed-outdated'       => 'اعرض فقط الصفحات التي بها نسخ غير مراجعة بعد النسخة المستقرة.',
	'unreviewed-category'       => 'التصنيف:',
	'unreviewed-diff'           => 'تغييرات',
	'unreviewed-list'           => 'هذه الصفحة تعرض المقالات التي لم يتم مراجعتها.',
	'revreview-visibility'      => 'هذه الصفحة لديها [[{{MediaWiki:Makevalidate-page}}|نسخة مستقرة]]، يمكن
	[{{fullurl:Special:Stabilization|page={{FULLPAGENAMEE}}}} ضبطها].',
	'stabilization'             => 'تثبيت الصفحة',
	'stabilization-text'        => 'غير الإعدادات بالأسفل لضبط الكيفية التي بها النسخة المستقرة من [[:$1|$1]] يتم اختيارها وعرضها.',
	'stabilization-perm'        => 'حسابك لا يمتلك الصلاحية لتغيير إعدادات النسخة المستقرة.
	هنا الإعدادات الحالية ل[[:$1|$1]]:',
	'stabilization-page'        => 'اسم الصفحة:',
	'stabilization-leg'         => 'ضبط النسخة المستقرة لصفحة',
	'stabilization-select'      => 'كيفية اختيار النسخة المستقرة',
	'stabilization-select1'     => 'آخر نسخة جودة؛ لو غير موجودة، إذا آخر واحدة منظورة',
	'stabilization-select2'     => 'آخر نسخة مراجعة',
	'stabilization-def'         => 'النسخة المعروضة عند رؤية الصفحة افتراضيا',
	'stabilization-def1'        => 'النسخة المستقرة؛ لو غير موجودة، إذا النسخة الحالية',
	'stabilization-def2'        => 'النسخة الحالية',
	'stabilization-submit'      => 'تأكيد',
	'stabilization-notexists'   => 'لا توجد صفحة بالاسم "[[:$1|$1]]". لا ضبط ممكن.',
	'stabilization-notcontent'  => 'الصفحة "[[:$1|$1]]" لا يمكن مراجعتها. لا ضبط ممكن.',
	'stabilization-success'     => 'إعدادات النسخة المستقرة ل[[:$1|$1]] تم ضبطها بنجاح.',
	'stabilization-sel-short'   => 'تنفيذ',
	'stabilization-sel-short-0' => 'جودة',
	'stabilization-sel-short-1' => 'لا شيء',
	'stabilization-def-short'   => 'افتراضي',
	'stabilization-def-short-0' => 'حالي',
	'stabilization-def-short-1' => 'مستقر',
	'reviewedpages'             => 'صفحات مراجعة',
	'reviewedpages-leg'         => 'عرض الصفحات المراجعة حتى مستوى معين',
	'reviewedpages-list'        => 'الصفحات التالية تمت مراجعتها حتى المستوى المحدد',
	'reviewedpages-none'        => 'لا توجد صفحات في هذه القائمة',
	'reviewedpages-lev-0'       => 'منظورة',
	'reviewedpages-lev-1'       => 'جودة',
	'reviewedpages-lev-2'       => 'مختارة',
	'reviewedpages-all'         => 'نسخ مراجعة',
	'reviewedpages-best'        => 'آخر نسخة بأعلى تقييم',
);
