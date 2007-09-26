<?php
$RevisionreviewMessages = array();

// English (Aaron Schulz)
$RevisionreviewMessages['en'] = array( 
	'makevalidate-autosum'=> 'autopromoted',
	'editor'              => 'Editor',
	'group-editor'        => 'Editors',
	'group-editor-member' => 'Editor',
	'grouppage-editor'    => '{{ns:project}}:Editor',

	'reviewer'              => 'Reviewer',
	'group-reviewer'        => 'Reviewers',
	'group-reviewer-member' => 'Reviewer',
	'grouppage-reviewer'    => '{{ns:project}}:Reviewer',

	'revreview-current'   => 'Draft',
	'revreview-edit'      => 'Edit draft',
	'revreview-source'    => 'draft source',
	'revreview-stable'    => 'Stable',
	'revreview-oldrating' => 'It was rated:',
	'revreview-noflagged' => 'There are no reviewed revisions of this page, so it may \'\'\'not\'\'\' have been 
	[[Help:Article validation|checked]] for quality.',
	
	'revreview-quick-none' => '\'\'\'Current\'\'\'. No reviewed revisions.',
	'revreview-quick-see-quality' => '\'\'\'Current\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} see stable revision]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|change|changes}}])',
	'revreview-quick-see-basic' => '\'\'\'Current\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} see stable revision]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|change|changes}}])',
	'revreview-quick-basic'  => '\'\'\'[[Help:Article validation|Sighted]]\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} see current revision]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|change|changes}}])',
	'revreview-quick-quality' => '\'\'\'[[Help:Article validation|Quality]]\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} see current revision]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|change|changes}}])',
	'revreview-newest-basic'    => 'The [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} latest sighted revision] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} list all]) was [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approved]
	 on <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|change|changes}}] {{plural:$3|needs|need}} review.',
	'revreview-newest-quality'    => 'The [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} latest quality revision] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} list all]) was [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approved]
	 on <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|change|changes}}] {{plural:$3|needs|need}} review.',
	'revreview-basic'  => 'This is the latest [[Help:Article validation|sighted]] revision, 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approved] on <i>$2</i>. The [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} current revision] 
	can be [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} modified]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|change|changes}}] 
	{{plural:$3|awaits|await}} review.',
	'revreview-quality'  => 'This is the latest [[Help:Article validation|quality]] revision, 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approved] on <i>$2</i>. The [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} current revision] 
	can be [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} modified]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|change|changes}}] 
	{{plural:$3|awaits|await}} review.',
	'revreview-static'  => 'This is a [[Help:Article validation|reviewed]] revision of \'\'\'[[:$3|$3]]\'\'\', 
	[{{fullurl:Special:Log/review|page=$1}} approved] on <i>$2</i>. The [{{fullurl:$3|stable=0}} current revision] 
	can be [{{fullurl:$3|action=edit}} modified].',
	'revreview-toggle' => '(+/-)',
	'revreview-note' => '[[User:$1]] made the following notes [[Help:Article validation|reviewing]] this revision:',
	'revreview-update' => 'Please review any changes (shown below) made since stable revision to this page. Templates and images
	may have also changed.',
	'revreview-auto' => '(automatic)',
	'revreview-auto-w' => "You are editing the stable revision, any changes will '''automatically be reviewed'''. 
	You may want to preview the page before saving.",
	'revreview-auto-w-old' => "You are editing an old revision, any changes will '''automatically be reviewed'''. 
	You may want to preview the page before saving.",

	'hist-stable'  => '[sighted]',
	'hist-quality' => '[quality]',

    'flaggedrevs'          => 'Flagged Revisions',
    'review-logpage'       => 'Article review log',
	'review-logpagetext'   => 'This is a log of changes to revisions\' [[Help:Article validation|approval]] status
	for content pages.',
	'review-logentry-app'  => 'reviewed $1',
	'review-logentry-dis'  => 'depreciated a version of $1',
	'review-logentry-conf' => 'set stable version settings for $1',
	'review-logaction'     => 'revision ID $1',

    'revisionreview'       => 'Review revisions',		
    'revreview-main'       => 'You must select a particular revision from a content page in order to review. 

	See the [[Special:Unreviewedpages]] for a list of unreviewed pages.',	
	'revreview-selected'   => "Selected revision of '''$1:'''",
	'revreview-text'       => "Stable versions are set as the default content on page view rather than the newest revision.",
	'revreview-toolow'     => 'You must at least rate each of the below attributes higher than "unapproved" in order 
	for a revision to be considered reviewed. To depreciate a revision, set all fields to "unapproved".',
	'revreview-flag'       => 'Review this revision (#$1)',
	'revreview-legend'     => 'Rate revision content',
	'revreview-notes'      => 'Observations or notes to display:',
	'revreview-accuracy'   => 'Accuracy',
	'revreview-accuracy-0' => 'Unapproved',
	'revreview-accuracy-1' => 'Sighted',
	'revreview-accuracy-2' => 'Accurate',
	'revreview-accuracy-3' => 'Well sourced',
	'revreview-accuracy-4' => 'Featured',
	'revreview-depth'      => 'Depth',
	'revreview-depth-0'    => 'Unapproved',
	'revreview-depth-1'    => 'Basic',		
	'revreview-depth-2'    => 'Moderate',
	'revreview-depth-3'    => 'High',
	'revreview-depth-4'    => 'Featured',
	'revreview-style'      => 'Readability',
	'revreview-style-0'    => 'Unapproved',
	'revreview-style-1'    => 'Acceptable',
	'revreview-style-2'    => 'Good',
	'revreview-style-3'    => 'Concise',
	'revreview-style-4'    => 'Featured',
	'revreview-log'        => 'Log comment:',
	'revreview-submit'     => 'Submit review',
	'revreview-changed'    => '\'\'\'The requestion action could not be performed on this revision.\'\'\'
	
	A template or image may have been requested when no specific version was specified. This can happen if a 
	dynamic template transcludes another image or template depending on a variable that changed since you started 
	reviewed this page. Refreshing the page and rereviewing can solve this problem.',

	'stableversions'        => 'Stable versions',
	'stableversions-leg1'   => 'List reviewed revisions for a page',
	'stableversions-page'   => 'Page name:',
	'stableversions-none'   => '"[[:$1]]" has no reviewed revisions.',
	'stableversions-list'   => 'The following is a list of revisions of "[[:$1]]" that have been reviewed:',
	'stableversions-review' => 'Reviewed on <i>$1</i> by $2',

    'review-diff2stable'    => 'Diff to the last stable revision',
    'review-diff2oldest'    => "Diff to the oldest revision",

    'unreviewedpages'       => 'Unreviewed pages',
    'viewunreviewed'        => 'List unreviewed content pages',
    'unreviewed-outdated'   => 'Show pages that have unreviewed revisions made to the stable version instead.',
    'unreviewed-category'   => 'Category:',
    'unreviewed-diff'       => 'Changes',
    'unreviewed-list'       => 'This page lists articles that have not been reviewed or have new, unreviewed, revisions.',
    
    'revreview-visibility'  => 'This page has a [[Help:Article validation|stable version]], which can be
	[{{fullurl:Special:Stabilization|page={{FULLPAGENAMEE}}}} configured].',
	
    'stabilization'         => 'Page stabilization',
    'stabilization-text'    => 'Change the settings below to adjust how the stable version of [[:$1|$1]] is selected and displayed.',
    'stabilization-perm'    => 'Your account does not have permission to change the stable version configuration.
	Here are the current settings for [[:$1|$1]]:',
	'stabilization-page'    => 'Page name:',
	'stabilization-leg'     => 'Configure the stable version for a page',
	'stabilization-select'  => 'How the stable version is selected',
	'stabilization-select1' => 'The latest quality revision; if not present, then the latest reviewed one',
	'stabilization-select2' => 'The latest reviewed revision',
	'stabilization-def'     => 'Revision displayed on default page view',
	'stabilization-def1'    => 'The stable revision; if not present, then the current one',
	'stabilization-def2'    => 'The current revision',
	'stabilization-submit'  => 'Confirm',
	'stabilization-dne'     => 'There is no page called "[[:$1|$1]]". No configuration possible.',
	'stabilization-success' => 'Stable version configuration for [[:$1|$1]] successfuly set.',
	
	'stabilization-sel-short'   => 'Precedence',
	'stabilization-sel-short-0' => 'Quality',
	'stabilization-sel-short-1' => 'None',
	'stabilization-def-short'   => 'Default',
	'stabilization-def-short-0' => 'Current',
	'stabilization-def-short-1' => 'Stable',
);

/* Arabic (Meno25) */
$RevisionreviewMessages['ar'] = array(
	'makevalidate-autosum'  => 'ترقية تلقائية',
	'editor'                => 'محرر',
	'group-editor'          => 'محررون',
	'group-editor-member'   => 'محرر',
	'grouppage-editor'      => '{{ns:project}}:محرر',
	'reviewer'              => 'مراجع',
	'group-reviewer'        => 'مراجعون',
	'group-reviewer-member' => 'مراجع',
	'grouppage-reviewer'    => '{{ns:project}}:مراجع',
	'revreview-current'     => 'النسخة الحالية',
	'revreview-edit'        => 'عدل المسودة',
	'revreview-source'      => 'مسودة مصدر',
	'revreview-stable'      => 'نسخة مستقرة',
	'revreview-oldrating'   => 'تم تقييمها ك:',
	'revreview-noflagged'   => 'لا توجد نسخ مراجعة لهذه الصفحة، لذا ربما \'\'\'لا\'\'\' تكون قد تم 
	[[Help:Article validation|التحقق من]] جودتها.',
	'revreview-quick-see-quality' => '\'\'\'حالي\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} انظر آخر نسخة جودة]',
	'revreview-quick-see-basic' => '\'\'\'حالي\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} عرض آخر نسخة منظورة]',
	'revreview-quick-basic' => '\'\'\'منظورة\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} عرض النسخة الحالية] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|تغيير|تغييرات}}])',
	'revreview-quick-quality' => '\'\'\'جودة\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} عرض النسخة الحالية] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|تغيير|تغييرات}}])',
	'revreview-quick-none'  => '\'\'\'الحالي\'\'\'. لا نسخ مراجعة.',
	'revreview-newest-basic' => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} النسخة الأخيرة المنظورة] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} عرض الكل]) تم [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} الموافقة عليها]
	 في <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|تغيير|تغييرات}}] {{plural:$3|تحتاج|تحتاج}} مراجعة.',
	'revreview-newest-quality' => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} نسخة الجودة الأخيرة] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} عرض الكل]) تم [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} الموافقة عليها]
	 في <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|تغيير|تغييرات}}] {{plural:$3|تحتاج|تحتاج}} مراجعة.',
	'revreview-basic'       => 'هذه آخر نسخة [[Help:Article validation|منظورة]] , 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} تمت الموافقة عليها] في <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} النسخة الحالية] 
	يمكن [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} تعديلها]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|تغيير|تغييرات}}] 
	{{plural:$3|تنتظر|تنتظر}} مراجعة.',
	'revreview-quality'     => 'هذه آخر نسخة [[Help:Article validation|جودة]], 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} تمت الموافقة عليها] في <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} النسخة الحالية] 
	يمكن [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} تعديلها]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|تغيير|تغييرات}}] 
	{{plural:$3|تنتظر|تنتظر}} مراجعة.',
	'revreview-static'      => 'هذه هي النسخة [[Help:Article validation|المراجعة]] من \'\'\'[[:$3|هذه الصفحة]]\'\'\', 
	[{{fullurl:Special:Log/review|page=$1}} تمت الموافقة عليها] في <i>$2</i>. The [{{fullurl:$3|stable=0}} النسخة الحالية] 
	يمكن [{{fullurl:$3|action=edit}} تعديلها].',
	'revreview-toggle'      => '(تفاصيل)',
	'revreview-note'        => '[[User:$1]] كتب الملاحظات التالية [[Help:Article validation|عند مراجعة]] هذه النسخة:',
	'revreview-update'      => 'من فضلك راجع أية تغييرات (معروضة بالأسفل) تمت منذ النسخة المستقرة لهذه الصفحة. القوالب و الصور
ربما تكون قد تغيرت أيضا.',
	'revreview-auto'        => '(تلقائيا)',
	'revreview-auto-w'      => '\'\'\'ملاحظة:\'\'\' أنت تقوم بتغييرات للنسخة المستقرة، تعديلاتك سيتم مراجعتها تلقائيا. 
ربما تريد أن تعرض الصفحة عرضا مسبقا قبل الحفظ.',
	'hist-stable'           => '[منظورة]',
	'hist-quality'          => '[الجودة]',
	'flaggedrevs'           => 'نسخ معلمة',
	'review-logpage'        => 'سجل مراجعة المقالة',
	'review-logpagetext'    => 'هذا سجل بالتغييرات لحالة\' [[Help:Article validation|الموافقة]] لصفحات المحتوى.',
	'review-logentrygrant'  => 'راجع نسخة ل $1',
	'review-logentryrevoke' => 'سحب نسخة من $1',
	'review-logaction'      => 'رقم النسخة $1',
	'revisionreview'        => 'مراجعة النسخ',
	'revreview-main'        => 'يجب أن تختار نسخة معينة من صفحة محتوى لمراجعتها. 

	انظر [[Special:Unreviewedpages]] لقائمة الصفحات غير المراجعة.',
	'revreview-selected'    => 'النسخة المختارة لصفحة \'\'\'$1:\'\'\'',
	'revreview-text'        => 'النسخ المستقرة محددة كالمحتوى القياسي عند عرض الصفحة و ليس أحدث نسخة.',
	'revreview-toolow'      => 'يجب عليك على الأقل تقييم كل من المحددات بالأسفل أعلى من "غير مقبولة" لكي 
تعتبر النسخة مراجعة. لسحب نسخة, اضبط كل الحقول ك "غير مقبولة".',
	'revreview-flag'        => 'راجع هذه النسخة (#$1):',
	'revreview-legend'      => 'قيم محتوى النسخة:',
	'revreview-notes'       => 'الملاحظات للعرض:',
	'revreview-accuracy'    => 'الدقة',
	'revreview-accuracy-0'  => 'غير موافق',
	'revreview-accuracy-1'  => 'معقولة',
	'revreview-accuracy-2'  => 'دقيقة',
	'revreview-accuracy-3'  => 'مصادرها جيدة',
	'revreview-accuracy-4'  => 'مميزة',
	'revreview-depth'       => 'العمق',
	'revreview-depth-0'     => 'غير موافق',
	'revreview-depth-1'     => 'أساسي',
	'revreview-depth-2'     => 'متوسط',
	'revreview-depth-3'     => 'مرتفع',
	'revreview-depth-4'     => 'مميز',
	'revreview-style'       => 'القابلية للقراءة',
	'revreview-style-0'     => 'غير مقبول',
	'revreview-style-1'     => 'مقبول',
	'revreview-style-2'     => 'جيدة',
	'revreview-style-3'     => 'متوسطة',
	'revreview-style-4'     => 'مميزة',
	'revreview-log'         => 'تعليق السجل:',
	'revreview-submit'      => 'تنفيذ',
	'revreview-changed'     => '\'\'\'الأمر المطلوب لم يمكن إجراؤه على هذه النسخة.\'\'\'
	
	قالب أو صورة ربما يكون قد تم طلبه عندما لم يتم تحديد نسخة معينة. هذا يمكن أن يحدث لو 
	قالب ديناميكي يضمن صورة أخرى أو قالب معتمدا على متغير تغير منذ أن بدأت 
مراجعة هذه الصفحة. تحديث الصفحة و إعادة المراجعة يمكن أن يحل هذه المشكلة.',
	'stableversions'        => 'نسخ مستقرة',
	'stableversions-leg1'   => 'عرض النسخ المراجعة لصفحة',
	'stableversions-page'   => 'اسم الصفحة',
	'stableversions-none'   => '[[:$1]] لا يوجد بها نسخ مراجعة.',
	'stableversions-list'   => 'هذه قائمة بنسخ صفحة [[:$1]] التي تم مراجعتها:',
	'stableversions-review' => 'تمت مراجعتها في <i>$1</i>',
	'review-diff2stable'    => 'فرق لآخر نسخة مستقرة',
	'review-diff2oldest'    => 'الفرق مع أقدم نسخة',
	'unreviewedpages'       => 'صفحات غير مراجعة',
	'viewunreviewed'        => 'عرض صفحات المحتوى غير المراجعة',
	'unreviewed-outdated'   => 'اعرض فقط الصفحات التي بها نسخ غير مراجعة بعد النسخة المستقرة.',
	'unreviewed-category'   => 'التصنيف:',
	'unreviewed-diff'       => 'تغييرات',
	'unreviewed-list'       => 'هذه الصفحة تعرض المقالات التي لم يتم مراجعتها.',
);

$RevisionreviewMessages['bcl'] = array(
	'hist-quality'          => '[kalidad]',
	'revreview-depth'       => 'Rarom',
	'stableversions-page'   => 'Pangaran kan pahina',
	'unreviewed-category'   => 'Kategorya:',
	'unreviewed-diff'       => 'Mga pagbabâgo',
);

$RevisionreviewMessages['ca'] = array(
	'makevalidate-autosum'  => 'autoconcedit',
	'reviewer'              => 'Supervisor',
	'group-reviewer'        => 'Supervisors',
	'group-reviewer-member' => 'Supervisor',
	'grouppage-reviewer'    => '{{ns:project}}:Supervisor',
	'revreview-current'     => 'actual',
	'revreview-edit'        => 'edita l\'actual',
	'revreview-source'      => 'Codi de l\'actual',
	'revreview-stable'      => 'Estable',
	'revreview-oldrating'   => 'Estava valorada:',
	'revreview-noflagged'   => 'No hi ha versions revisades d\'aquesta pàgina i, per tant, pot \'\'\'no\'\'\' haver estat [[Help:Article validation|comprovada]] la seva qualitat.',
	'revreview-quick-none'  => '\'\'\'Actual\'\'\'. No hi ha versions revisades.',
	'revreview-quick-see-quality' => '\'\'\'Actual\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} vegeu la versió de qualitat]] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|canvi|canvis}}])',
	'revreview-quick-see-basic' => '\'\'\'Actual\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} vegeu la versió estable]] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|canvi|canvis}}])',
	'revreview-quick-basic' => '\'\'\'[[Help:Article validation|Revisada]]\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} vegeu la versió actual]] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|canvi|canvis}}])',
	'revreview-quick-quality' => '\'\'\'[[Help:Article validation|Qualitat]]\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} vegeu la versió actual]] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|canvi|canvis}}])',
	'revreview-newest-basic' => 'L\'[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} última versió revisada] ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} vegeu-les totes]) va ser [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovada] a <i>$2</i>. Hi ha [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|canvi|canvis}}] que {{plural:$3|necessita|necessiten}} revisió.',
	'revreview-newest-quality' => 'L\'[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} última versió de qualitat] ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} vegeu-les totes]) va ser [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovada] a <i>$2</i>. Hi ha [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|canvi|canvis}}] que {{plural:$3|necessita|necessiten}} revisió.',
	'revreview-basic'       => 'Aquesta és l\'última versió [[Help:Article validation|revisada]], [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovada] a <i>$2</i>. La [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} versió actual] és la que pot ser [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} modificada]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|canvi|canvis}}] {{plural:$3|espera|esperen}} revisió.',
	'revreview-quality'     => 'Aquesta és l\'última versió [[Help:Article validation|de qualitat]], [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovada] a <i>$2</i>. La [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} versió actual] és la que pot ser [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} modificada]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|canvi|canvis}}] {{plural:$3|espera|esperen}} revisió.',
	'revreview-auto'        => '(automàtic)',
);

$RevisionreviewMessages['cs'] = array(
	'makevalidate-autosum'  => 'automaticky povýšen',
	'group-editor'          => 'Editoři',
	'reviewer'              => 'Posuzovatel',
	'group-reviewer'        => 'Posuzovatelé',
	'group-reviewer-member' => 'Posuzovatel',
	'revreview-current'     => 'Návrh',
	'revreview-edit'        => 'Editovat návrh',
	'revreview-source'      => 'zdroj návrhu',
	'revreview-stable'      => 'Stabilní',
	'revreview-oldrating'   => 'Bylo ohodnoceno:',
	'revreview-noflagged'   => 'Tato stránka nemá žádné posouzené verze, takže dosud nebyla [[Help:Article validation|zkontrolována]] kvalita.',
	'revreview-quick-none'  => '\'\'\'Nejnovější verze\'\'\'. Žádné posouzené verze.',
	'revreview-quick-see-quality' => '\'\'\'Nejnovější verze\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Vizte poslední kvalitní verzi]]',
	'revreview-quick-see-basic' => '\'\'\'Nejnovější verze\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Vizte poslední prohlédnutou verzi]]',
	'revreview-quick-basic' => '\'\'\'[[Help:Article validation|Prohlédnuto]]\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Vizte nejnovější verzi]] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|změna|změny|změn}}])',
	'revreview-quick-quality' => '\'\'\'[[Help:Article validation|Kvalitní]]\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Vizte nejnovější verzi]] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|změna|změny|změn}}])',
	'revreview-newest-basic' => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Poslední prohlédnutá verze] ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} seznam všech]) byla [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválena] <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|změna|změny|změn}}] {{plural:$3|potřebuje|potřebují|potřebuje}} posoudit.',
	'revreview-newest-quality' => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Poslední kvalitní verze] ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} seznam všech]) byla [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválena] <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|změna|změny|změn}}] {{plural:$3|potřebuje|potřebují|potřebuje}} posoudit.',
	'revreview-basic'       => 'Toto je poslední [[Help:Article validation|prohlédnutá]] verze. Byla [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválena] <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Nejnovější verzi] lze [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} upravit]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|změna|změny|změn}}] čeká na posouzení.',
	'revreview-quality'     => 'Toto je poslední [[Help:Article validation|kvalitní]] verze. Byla [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválena] <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Nejnovější verzi] lze [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} upravit]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|změna|změny|změn}}] čeká na posouzení.',
	'revreview-static'      => 'Toto je [[Help:Article validation|posouzená]] verze \'\'\'[[:$3|této stránky]]\'\'\' [{{fullurl:Special:Log/review|page=$1}} schválená] <i>$2</i>. [{{fullurl:$3|stable=0}} Nejnovější verzi] můžete [{{fullurl:$3|action=edit}} změnit].',
	'revreview-note'        => 'Uživatel [[User:$1|$1]] doplnil své [[Help:Article validation|posouzení]] této verze následující poznámkou:',
	'revreview-update'      => 'Posuďte všechny změny na této stránce vůči stabilní verzi. Šablony a obrázky se také mohly změnit.',
	'revreview-auto'        => '(automaticky)',
	'revreview-auto-w'      => 'Editujete stabilní verzi, změny budou \'\'\'automaticky označeny jako posouzené\'\'\'. Měli byste zkontrolovat náhled stránky.',
	'revreview-auto-w-old'  => 'Editujete starou verzi, změny budou \'\'\'automaticky označeny jako posouzené\'\'\'. Měli byste zkontrolovat náhled stránky.',
	'hist-stable'           => '[prohlédnutá]',
	'hist-quality'          => '[kvalitní]',
	'flaggedrevs'           => 'Označování verzí',
	'review-logpage'        => 'Kniha posuzování článků',
	'review-logpagetext'    => 'Tato kniha zobrazuje změny [[Help:Article validation|schválení]] verzí stránek.',
	'review-logentrygrant'  => 'posouzeno $1',
	'review-logentryrevoke' => 'znehodnocená verze stránky $1',
	'review-logaction'      => 'identifikace verze $1',
	'revisionreview'        => 'Posouzení verzí',
	'revreview-main'        => 'Musíte vybrat určitou verzi stránky, aby jste ji mohli posoudit. Vizte [[Special:Unreviewedpages|seznam neposouzených stránek]].',
	'revreview-selected'    => 'Vybrané verze stránky \'\'\'$1:\'\'\'',
	'revreview-text'        => 'Stabilní verze je nastavena jako výchozí zobrazený obsah před nejnovější verzí.',
	'revreview-toolow'      => 'Aby byla verze označena jako posouzená, musíte označit každou vlastnost lepším hodnocením než "neschváleno". Pokud chcete verzi odmítnout nechte ve všech polích hodnocení "neschváleno".',
	'revreview-flag'        => 'Posoudit tuto verzi (#$1)',
	'revreview-legend'      => 'Ohodnoťte obsah verze',
	'revreview-notes'       => 'Poznámky k zobrazení:',
	'revreview-accuracy'    => 'Přesnost',
	'revreview-accuracy-0'  => 'Neschváleno',
	'revreview-accuracy-1'  => 'Prohlédnuto',
	'revreview-accuracy-2'  => 'Přesná',
	'revreview-accuracy-3'  => 'Dobře ozdrojovaná',
	'revreview-accuracy-4'  => 'Význačná',
	'revreview-depth'       => 'Hloubka',
	'revreview-depth-0'     => 'Neschváleno',
	'revreview-depth-1'     => 'Základní',
	'revreview-depth-2'     => 'Mírná',
	'revreview-depth-3'     => 'Vysoká',
	'revreview-depth-4'     => 'Význačná',
	'revreview-style'       => 'Čitelnost',
	'revreview-style-0'     => 'Neschváleno',
	'revreview-style-1'     => 'Přijatelná',
	'revreview-style-2'     => 'Dobrá',
	'revreview-style-3'     => 'Výstižná',
	'revreview-style-4'     => 'Význačná',
	'revreview-log'         => 'Komentář:',
	'revreview-submit'      => 'Odeslat posouzení',
	'revreview-changed'     => '\'\'\'Požadovanou akci nelze na této verzi provést.\'\'\' Šablona nebo obrázek byly vyžádány na neurčitou verzi. To se může stát pokud dynamická šablona vkládá jinou šablonu nebo obrázek v závislosti na proměnné, která se změnila zatímco jste posuzovali stránku. Obnovte stránku a znovu ji posuďte.',
	'stableversions'        => 'Stabilní verze',
	'stableversions-leg1'   => 'Přehled posouzených verzí stránky',
	'stableversions-page'   => 'Jméno stránky',
	'stableversions-none'   => '[[:$1]] nemá žádné posouzené verze.',
	'stableversions-list'   => 'Toto je seznam verzí stránky [[:$1]], které byly posouzeny:',
	'stableversions-review' => 'Posouzeno <i>$1</i>',
	'review-diff2stable'    => 'Rozdíl oproti poslední stabilní verzi',
	'review-diff2oldest'    => 'Rozdíl oproti nejstarší verzi',
	'unreviewedpages'       => 'Neposouzené stránky',
	'viewunreviewed'        => 'Seznam neposouzených stránek',
	'unreviewed-outdated'   => 'Zobrazit stránky, které mají neposouzené verze do stabilní verze.',
	'unreviewed-category'   => 'Kategorie:',
	'unreviewed-diff'       => 'Změny',
	'unreviewed-list'       => 'Tato stránka obsahuje články, které nebyly posouzeny nebo mají nové, neposouzené, verze.',
);

// German (Raimond Spekking)
$RevisionreviewMessages['de'] = array( 
	'makevalidate-autosum'=> 'autopromoted', # needs still a nice and short translation :-(
	'editor'              => 'Editor',
	'group-editor'        => 'Editoren',
	'group-editor-member' => 'Editor',
	'grouppage-editor'    => '{{ns:project}}:Editor',

	'reviewer'              => 'Prüfer',
	'group-reviewer'        => 'Prüfer',
	'group-reviewer-member' => 'Prüfer',
	'grouppage-reviewer'    => '{{ns:project}}:Prüfer',

	'revreview-edit'      => 'Bearbeite Entwurf',
	'revreview-source'    => 'Entwurfs-Quelltext',
	'revreview-current'   => 'Entwurf (bearbeitbar)',
	'revreview-stable'    => 'Stabil',
	'revreview-oldrating' => 'war eingestuft als:',
	'revreview-noflagged' => 'Von dieser Seite gibt es keine überprüften Versionen, so dass noch keine Aussage über die 
	[[Help:Article validation|Artikelqualität]] gemacht werden kann.',

	'revreview-quick-none'        => "'''Aktuell.'''. Es wurde noch keine Version überprüft.",

	'revreview-quick-see-quality' => "'''Aktuell.''' [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Siehe die letzte überprüfte Version]
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|Änderung|Änderungen}}])",

	'revreview-quick-see-basic'   => "'''Aktuell.''' [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Siehe die letzte überprüfte Version]
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|Änderung|Änderungen}}])",

	'revreview-quick-basic'       => "'''[[Help:Article validation|Gesichtet.]]''' [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Siehe die aktuelle Version] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|Änderung|Änderungen}}])",

	'revreview-quick-quality'     => "'''[[Help:Article validation|Geprüft.]]''' [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Siehe die aktuelle Version] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|Änderung|Änderungen}}])",

	'revreview-newest-basic' => 'Die [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} letzte überprüfte Version]
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} siehe alle]) wurde am <i>$2</i> [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben].
	[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|Version|Versionen}}] {{plural:$3|steht|stehen}} noch zur Prüfung an.',

	'revreview-newest-quality' => 'Die [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} letzte überprüfte Version]
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} siehe alle]) wurde am <i>$2</i> [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben]
	[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|Version|Versionen}}] {{plural:$3|steht|stehen}} noch zur Prüfung an.',

	'revreview-basic'  => 'Dies ist die letzte [[Help:Gesichtete Versionen|gesichtete]] Version,
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben] am <i>$2</i>. Die [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} derzeitige Version]
	kann [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} bearbeitet] werden; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|Version|Versionen}}]
	{{plural:$3|steht|stehen}} noch zur Prüfung an.',

	'revreview-quality'  => 'Das ist die letzte Version mit [[Help:Versionsbewertung|geprüfte]] Version,
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben] am <i>$2</i>. Die [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} derzeitige Version]
	kann [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} bearbeitet] werden; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|Version|Versionen}}]
	{{plural:$3|steht|stehen}} noch zur Prüfung an.',

	'revreview-static' => "Dies ist eine [[Help:Geprüfte Versionen|geprüfte]] Version von '''[[:$3|$3]]''', 
	[{{fullurl:Special:Log/review|page=$1}} freigegeben] am <i>$2</i>. Die [{{fullurl:$3|stable=0}} derzeitige Version] 
	kann [{{fullurl:$3|action=edit}} bearbeitet] werden.",

	'revreview-toggle' => '(+/-)',
	'revreview-note'   => '[[{{ns:user}}:$1]] machte die folgende [[Help:Article validation|Prüfnotiz]] zu dieser Version:',
	'revreview-update' => 'Bitte prüfe jede Änderung seit der letzten stabilen Version (siehe unten).
	Vorlagen und Bilder können sich ebenfalls geändert haben.',
	'revreview-auto'   => '(automatisch)',
	'revreview-auto-w' => "Du bearbeitest eine stabile Version, deine Bearbeitung wird '''automatisch als überprüft markiert.''' 
	Du solltest die Seite daher vor dem Speichern in der Vorschau betrachten.",
        'revreview-auto-w-old' => "Du bearbeitest eine alte Version, deine Bearbeitung wird '''automatisch als überprüft markiert.''' 
        Du solltest die Seite daher vor dem Speichern in der Vorschau betrachten.",

	'hist-stable'  => '[gesichtet]',
	'hist-quality' => '[geprüft]',

	'flaggedrevs'           => 'Markierte Versionen',
	'review-logpage'        => 'Artikel-Prüf-Logbuch',
	'review-logpagetext'    => 'Dies ist das Änderungs-Logbuch der [[Help:Article validation|Seiten-Freigaben]].',
	'review-logentry-app'   => 'überprüfte $1',
	'review-logentry-dis'   => 'verwarf eine Version von $1',
	'review-logentry-conf'  => 'setzte Einstellungen für stabile Version für $1',
	'review-logaction'      => 'Version-ID $1',

	'revisionreview'       => 'Versionsprüfung',
	'revreview-main'       => 'Du musst eine Artikelversion zur Prüfung auswählen.

	Siehe [[{{ns:special}}:Unreviewedpages]] für eine Liste nicht überprüfter Versionen.',	
	'revreview-selected'   => "Gewählte Version von '''$1:'''",
	'revreview-text'       => "Einer stabilen Version wird bei der Seitendarstellung der Vorzug vor einer neueren Version gegeben.",
	'revreview-toolow'     => 'Du musst für jedes der untenstehenden Attribute einen Wert höher als „{{int:revreview-accuracy-0}}“ einstellen,
	damit eine Version als überprüft gilt. Um eine Version zu verwerfen, müssen alle Attribute auf „{{int:revreview-accuracy-0}}“ stehen.',
	'revreview-flag'       => 'Prüfe Version #$1',
	'revreview-legend'     => 'Inhalt der Version bewerten',
	'revreview-notes'      => 'Anzuzeigende Bemerkungen oder Notizen:',
	'revreview-accuracy'   => 'Genauigkeit',
	'revreview-accuracy-0' => 'nicht freigegeben',
	'revreview-accuracy-1' => 'gesichtet',
	'revreview-accuracy-2' => 'geprüft',
	'revreview-accuracy-3' => 'Quellen geprüft', # not used in de.wiki
	'revreview-accuracy-4' => 'exzellent', # not used in de.wiki
	'revreview-depth'      => 'Tiefe', # not used in de.wiki
	'revreview-depth-0'    => 'nicht freigegeben', # not used in de.wiki
	'revreview-depth-1'    => 'einfach', # not used in de.wiki
	'revreview-depth-2'    => 'mittel', # not used in de.wiki
	'revreview-depth-3'    => 'hoch', # not used in de.wiki
	'revreview-depth-4'    => 'exzellent', # not used in de.wiki
	'revreview-style'      => 'Lesbarkeit', # not used in de.wiki
	'revreview-style-0'    => 'nicht freigegeben', # not used in de.wiki
	'revreview-style-1'    => 'akzeptabel', # not used in de.wiki
	'revreview-style-2'    => 'gut', # not used in de.wiki
	'revreview-style-3'    => 'präzise', # not used in de.wiki
	'revreview-style-4'    => 'exzellent', # not used in de.wiki
	'revreview-log'        => 'Logbuch-Eintrag:',
	'revreview-submit'     => 'Prüfung speichern',
	'revreview-changed'    => '\'\'\'Die Aktion konnte nicht auf diese Version angewendet werden.\'\'\'

	Eine Vorlage oder ein Bild wurden ohne spezifische Versionsnummer angefordert. Dies kann passieren,
	wenn eine dynamische Vorlage eine weitere Vorlage oder ein Bild einbindet, das von einer Variable abhängig ist, die
	sich seit Beginn der Prüfung verändert hat. Ein Neuladen der Seite und Neustart der Prüfung kann das Problem beheben.',

	'stableversions'        => 'Stabile Versionen',
	'stableversions-leg1'   => 'Liste der überprüften Versionen für einen Artikel',
	'stableversions-page'   => 'Artikelname:',
	'stableversions-none'   => '„[[:$1]]“ hat keine überprüften Versionen.',
	'stableversions-list'   => 'Dies ist die Liste der überprüften Versionen von „[[:$1]]“:',
	'stableversions-review' => 'überprüft am <i>$1</i> durch $2',

	'review-diff2stable'    => 'Unterschied zur letzten stabilen Version',
	'review-diff2oldest'    => "Unterschied zur ältesten Version",

	'unreviewedpages'       => 'Nicht überprüfte Artikel',
	'viewunreviewed'        => 'Liste nicht überprüfter Artikel',
	'unreviewed-outdated'   => 'Zeige nur Seiten, die nicht überprüfte Versionen nach einer stabilen Version haben.',
	'unreviewed-category'   => 'Kategorie:',
	'unreviewed-diff'       => 'Änderungen',
	'unreviewed-list'       => 'Diese Seite zeigt Artikel, die noch nicht nie überprüft wurden oder nicht überprüfte Versionen haben.',

	'revreview-visibility'  => 'Diese Seite hat eine [[Help:Article validation|stabile Version]], welche
	[{{fullurl:Special:Stabilization|page={{FULLPAGENAMEE}}}} konfiguriert] werden kann.',

	'stabilization'         => 'Seiten-Stabilität',
	'stabilization-text'    => 'Ändere die Einstellungen um festzulegen, wie die stabile Version von „[[:$1|$1]]“ ausgewählt und angezeigt werden soll.',
	'stabilization-perm'    => 'Dein Benutzerkonto hat nicht die erforderliche Berechtigung, um die Einstellungen der stabilen Version zu ändern.
	Die aktuellen Einstellungen für „[[:$1|$1]]“ sind:',
	'stabilization-page'    => 'Seitenname:',
	'stabilization-leg'     => 'Einstellungen der stabilen Version für eine Seite',
	'stabilization-select'  => 'Auswahl der stabilen Version',
	'stabilization-select1' => 'Die letzte geprüfte Version; wenn keine vorhnanden ist, dann die letzte gesichtete Version',
	'stabilization-select2' => 'Die letzte gesichtete Version',
	'stabilization-def'     => 'Angezeigte Version in der normalen Seitenansicht',
	'stabilization-def1'    => 'Die stabile Version',
	'stabilization-def2'    => 'Die aktuellste Version',
	'stabilization-submit'  => 'Bestätigen',
	'stabilization-dne'     => 'Es gibt keine Seite „[[:$1|$1]]“. Keine Einstellungen möglich.',
	'stabilization-success' => 'Einstellungen für die stabile Version von „[[:$1|$1]]“ erfolgreich gespeichert.',
	
	'stabilization-sel-short'   => 'Priorität',
	'stabilization-sel-short-0' => 'Qualität',
	'stabilization-sel-short-1' => 'keine',
	'stabilization-def-short'   => 'Standard',
	'stabilization-def-short-0' => 'Aktuell',
	'stabilization-def-short-1' => 'Stabil',
);

$RevisionreviewMessages['la'] = array(
	'editor'                => 'Recensor',
	'group-editor'          => 'Recensores',
	'group-editor-member'   => 'Recensor',
	'grouppage-editor'      => '{{ns:project}}:Recensor',
	'reviewer'              => 'Revisor',
	'group-reviewer'        => 'Revisores',
	'group-reviewer-member' => 'Revisor',
	'grouppage-reviewer'    => '{{ns:project}}:Revisor',
	'revreview-style-2'     => 'Bonus',
	'revreview-log'         => 'Sententia:',
	'stableversions-page'   => 'Nomen paginae',
	'unreviewed-category'   => 'Categoria:',
	'unreviewed-diff'       => 'Cambiationes',
);

$RevisionreviewMessages['nl'] = array(
	'makevalidate-autosum'  => 'automatisch gepromoveerd',
	'editor'                => 'Redacteur',
	'group-editor'          => 'Redacteuren',
	'group-editor-member'   => 'Redacteur',
	'grouppage-editor'      => '{{ns:project}}:Redacteur',
	'revreview-current'     => 'Huidige versie',
	'revreview-edit'        => 'concept bewerken',
	'revreview-source'      => 'Brontekst concept',
	'revreview-stable'      => 'Stabiele versie',
	'revreview-oldrating'   => 'Was gewaardeerd als:',
	'revreview-noflagged'   => 'Er zijn geen gereviewde versies van deze pagina, dus die is wellicht \'\'\'niet\'\'\' 
	[[Help:Article validation|gecontroleerd]] op kwaliteit.',
	'revreview-quick-none'  => '\'\'\'Huidige versie\'\'\'. Geen gereviewde versies.',
	'revreview-quick-see-quality' => '\'\'\'Huidige versie\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Bekijk laatste kwaliteitsversie]',
	'revreview-quick-see-basic' => '\'\'\'Huidige versie\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Bekijk laatste bekeken versie]',
	'revreview-quick-basic' => '\'\'\'Bekeken versie\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Bekijk huidige versie] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|bewerking|bewerkingen}}])',
	'revreview-quick-quality' => '\'\'\'Kwaliteitsversie\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Bekijk huidige versie] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|bewerking|bewerkingen}}])',
	'revreview-newest-basic' => 'De [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} laatst bekeken versie] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} toon alle]) is [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} gekeurd]
	 op <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} $3 {{plural:$3|versie|versies}}] {{plural:$3|heeft|hebben}} een review nodig.',
	'revreview-newest-quality' => 'De [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} laatste kwaliteitsversie] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} toon alle]) is [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} gekeurd]
	 op <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} $3 {{plural:$3|versie|versies}}] {{plural:$3|heeft|hebben}} een review nodig.',
	'revreview-basic'       => 'Dit is de laatst [[Help:Article validation|bekeken]] versie, 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} gekeurd] op <i>$2</i>. De [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} huidige] 
	kan [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} bewerkt] worden; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} $3 {{plural:$3|versie|versies}}] 
	{{plural:$3|wacht|wachten}} op review.',
	'revreview-quality'     => 'Dit is de laatste [[Help:Article validation|kwaliteitsversie]], 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} gekeurd] op <i>$2</i>. De [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} huidige] 
	kan [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} bewerkt] worden; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} $3 {{plural:$3|versie|versies}}] 
	{{plural:$3|wacht|wachten}} op review.',
	'revreview-static'      => 'Dit is een [[Help:Article validation|gereviewde]] versie van \'\'\'[[:$3|deze pagina]]\'\'\', 
	[{{fullurl:Special:Log/review|page=$1}} gekeurd] op <i>$2</i>. De [{{fullurl:$3|stable=0}} huidige versie] 
	kan [{{fullurl:$3|action=edit}} bewerkt] worden.',
	'revreview-note'        => '[[User:$1|$1]] heeft de volgende opmerkingen gemaakt bij de [[Help:Article validation|review]] van deze versie:',
	'revreview-update'      => 'Controleer alstublieft alle onderstaande wijzigingen die gemaakt zijn sinds de stabiele versie voor deze pagina. Sjablonen en afbeeldingen kunnen ook gewijzigd zijn.',
	'revreview-auto'        => '(automatisch}',
	'revreview-auto-w'      => '\'\'\'Opmerking:\'\'\' u wijzigt de stabiele versie. Uw bewerkingen worden automatisch gecontroleerd. Controleer de voorvertoning voordat u de pagina opslaat.',
	'revreview-auto-w-old'  => 'U bent een oude versie aan het bewerken, elke wijziging zal \'\'\'automatisch gereviewed worden\'\'\'.
Gelieve de bewerking ter controle te tonen voor het oplaan.',
	'hist-stable'           => '[bekeken pagina]',
	'hist-quality'          => '[kwaliteitspagina]',
	'flaggedrevs'           => 'Aangevinkte versies',
	'review-logpage'        => 'Reviewlogboek',
	'review-logpagetext'    => 'Dit is een logboek met wijzigingen in de [[Help:Article validation|waarderingsstatus]] van versies
	van pagina\'s.',
	'review-logentrygrant'  => 'reviewde een versie van $1',
	'review-logentryrevoke' => 'verlaagde de waardering van een versie van $1',
	'review-logaction'      => 'versienummer $1',
	'revisionreview'        => 'Versies reviewen',
	'revreview-main'        => 'U moet een specifieke versie van een pagina kiezen om te kunnen reviewen. 

	[[Special:Unreviewedpages|Hier]] treft u een lijst aan met pagina\'s waarvoor nog geen review is uitgevoerd.',
	'revreview-selected'    => 'Geselecteerde versie van \'\'\'$1:\'\'\'',
	'revreview-text'        => 'Stabiele versies worden standaard getoond in plaats van de meest recentie versie.',
	'revreview-toolow'      => 'U moet tenminste alle onderstaande attributen hoger instellen dan "niet gewaardeerd" om een versie als 
	gereviewed aan te laten merken. Om de waardering van een versie te verwijderen, stelt u alle velden in op "niet gewaardeerd".',
	'revreview-flag'        => 'Review deze versie (#$1):',
	'revreview-legend'      => 'Waardeer versieinhoud:',
	'revreview-notes'       => 'Weer te geven bbservaties of notities:',
	'revreview-accuracy'    => 'Accuraatheid',
	'revreview-accuracy-0'  => 'Niet gekeurd',
	'revreview-accuracy-1'  => 'Bekeken',
	'revreview-accuracy-2'  => 'Accuraat',
	'revreview-accuracy-3'  => 'Goed van bronnen voorzien',
	'revreview-accuracy-4'  => 'Uitgelicht',
	'revreview-depth'       => 'Diepgang',
	'revreview-depth-0'     => 'Niet gewaardeerd',
	'revreview-depth-1'     => 'Basaal',
	'revreview-depth-2'     => 'Middelmatig',
	'revreview-depth-3'     => 'Hoog',
	'revreview-depth-4'     => 'Uitgelicht',
	'revreview-style'       => 'Leesbaarheid',
	'revreview-style-0'     => 'Niet gewaardeerd',
	'revreview-style-1'     => 'Acceptabel',
	'revreview-style-2'     => 'Goed',
	'revreview-style-3'     => 'Bondig',
	'revreview-style-4'     => 'Uitgelicht',
	'revreview-log'         => 'Logboekopmerking:',
	'revreview-submit'      => 'Review insturen',
	'revreview-changed'     => '\'\'\'De gevraagde actie kon niet uitgevoerd worden voor deze versie.\'\'\'
	
	Er is een sjabloon of afbeelding opgevraagd zonder dat een specifieke versie is aangegeven. Dit kan voorkomen als een 
	dynamisch sjabloon een andere afbeelding of een ander sjabloon bevat, afhankelijk van een variabele die is gewijzigd sinds
	u bent begonnen met de review van deze pagina. Ververs de pagina en start de review opnieuw om dit probleem op te lossen.',
	'stableversions'        => 'Stabiele versies',
	'stableversions-leg1'   => 'Lijst van gereviewde versies voor een pagina',
	'stableversions-page'   => 'Paginanaam',
	'stableversions-none'   => '[[:$1]] heeft geen gereviewde versies.',
	'stableversions-list'   => 'Hieronder staat een lijst met versies van [[:$1]] waarop een review is uitgevoerd:',
	'stableversions-review' => 'Review uitgevoerd op <i>$1</i>',
	'review-diff2stable'    => 'Verschil met de laatste stabiele versie',
	'review-diff2oldest'    => 'Verschil met de oudste versie',
	'unreviewedpages'       => 'Pagina\'s zonder review',
	'viewunreviewed'        => 'Lijst van pagina\'s zonder review',
	'unreviewed-outdated'   => 'Toon alleen pagina\'s waarvan nog niet gereviewde versies zijn op de stabiele versie.',
	'unreviewed-category'   => 'Categorie:',
	'unreviewed-diff'       => 'Wijzigingen',
	'unreviewed-list'       => 'Deze pagina toont pagina\'s die nog geen review hebben gehad.',
);

$RevisionreviewMessages['hsb'] = array(
	'makevalidate-autosum'  => 'Prawo awtomatisce spožčene',
	'editor'                => 'wobdźěłowar',
	'group-editor'          => 'wobdźěłowarjo',
	'group-editor-member'   => 'wobdźěłowar',
	'grouppage-editor'      => '{{ns:project}}:Wobdźěłowarjo',
	'reviewer'              => 'přehladowar',
	'group-reviewer'        => 'přehladowarjo',
	'group-reviewer-member' => 'přehladowar',
	'grouppage-reviewer'    => '{{ns:project}}:Přehladowarjo',
	'revreview-current'     => 'Naćisk',
	'revreview-edit'        => 'Naćisk wobdźěłać',
	'revreview-source'      => 'Žórło naćiska',
	'revreview-stable'      => 'Stabilna wersija',
	'revreview-oldrating'   => 'Zastopnjowanje:',
	'revreview-noflagged'   => 'Njeje přehladowanych wersijow tuteje strony, tak zo njejsu wuprajenja k [[{{ns:help}}:Article validation|spušćomnosći nastawka]] móžne.',
	'revreview-quick-none'  => '\'\'\'Aktualnje\'\'\'. Žane přehladowane wersije.',
	'revreview-quick-see-quality' => '\'\'\'Aktualnje\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Hlej poslednju přehladanu wersiju]',
	'revreview-quick-see-basic' => '\'\'\'Aktualnje\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Hlej poslednju přehladanu wersiju]',
	'revreview-quick-basic' => '\'\'\'[[Help:Article validation|Wuhladowany.]]\'\'\' [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Hlej aktualna wersiju] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|změna|změnow|změny|změnje}}])',
	'revreview-quick-quality' => '\'\'\'[[Help:Article validation|Pruwowany.]]\'\'\' [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Hlej aktualnu wersiju] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|změna|změnow|změny|změnje}}])',
	'revreview-newest-basic' => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Poslednja wuhladana wersija]
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} hlej wšě]) bu dnja <i>$2</i> [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} dopušćena].
	[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|wersija|wersijow|wersije|wersiji}}] {{plural:$3|dyrbi|dyrbi|dyrbja|dyrbjetej}} so pruwować.',
	'revreview-newest-quality' => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Poslednja pruwowana wersija]
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} hlej wšě]) bu <i>$2</i> [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} dopušćena].
[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|wersija|wersijow|wersije|wersiji}}] {{plural:$3|dyrbi|dyrbi|dyrbja|dyrbjetej}} so hišće pruwować.',
	'revreview-basic'       => 'To je poslednja [[Help:Article validation|wuhladana]] wersija,
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} dopušćena] <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} tuchwilna wersija]
	móže so [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} wobdźěłać]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|wersija|wersijow|wersije|wersiji}}]
	{{plural:$3|dyrbi|dyrbi|dyrbjadyrbjetej}} so hišće pruwować.',
	'revreview-quality'     => 'To je poslednja [[Help:Versionsbewertung|kwalitna wersija]],
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} dopušćena]  <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Tuchwilna wersija]
	móže so [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} wobdźěłać; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|wersija|wersijow|wersije|wersiji}}]
	{{plural:$3|dyrbi|dyrbi|dyrbja|dyrbjetej}} so hišće pruwować.',
	'revreview-static'      => 'To je [[Help:Article validation|pruwowana]] wersija \'\'\'[[:$3|tuteje strony]]\'\'\', [{{fullurl:Special:Log/review|page=$1}} dopušćena]
	am <i>$2</i>. [{{fullurl:$3|stable=0}} Tuchwilna wersija] móže so [{{fullurl:$3|action=edit}} wobdźěłać].',
	'revreview-toggle'      => '(+/-)',#identical but defined
	'revreview-note'        => '[[{{ns:user}}:$1]] činješe slědowace [[Help:Article validation|pruwowanske noticy]] k tutej wersiji:',
	'revreview-update'      => 'Prošu pruwuj kóždu změnu wot poslednjeje stabilneje wersije (hlej deleka). Předłohi a wobrazy móža tež změnjene być.',
	'revreview-auto'        => '(awtomatisce)',
	'revreview-auto-w'      => 'Wobdźěłuješ runje stabilnu wersiju, wšě změny so \'\'\'awtomatisce pruwować.\'\'\' Ty měł sej tohodla stronu před składowanjom w přehledźe wobhladać.',
	'revreview-auto-w-old'  => 'Wobdźěłuješ staru wersiju, wšě změny budu so \'\'\'awtomatisce pruwować.\'\'\' Ty měł sej tohodla stronu před składowanjom w přehledźe wobhladać.',
	'hist-stable'           => '[wuhladany]',
	'hist-quality'          => '[pruwowany]',
	'flaggedrevs'           => 'Woznamjenjene wersije',
	'review-logpage'        => 'Protokol přehladanjow',
	'review-logpagetext'    => 'To je protokol změnow [[Help:Application validation| dopušćenjow za nastawki.',
	'review-logentry-app'   => 'je $1 přepruwował',
	'review-logentry-dis'   => 'je wersiju wot $1 zaćisnył',
	'review-logentry-conf'  => 'je nastajenja za stabilnu wersiju za $1 stajił',
	'review-logaction'      => 'Wersijowy ID $1',
	'revisionreview'        => 'Wersije přepruwować',
	'revreview-main'        => 'Dyrbiš wěstu wersiju nastawka za přehladanje wubrać.


	Hlej [[{{ns:special}}:Unreviewedpages]] za lisćinu njepřehladanych wersijow.',
	'revreview-selected'    => 'Wubrana wersija z \'\'\'$1:\'\'\'',
	'revreview-text'        => 'Při zwobraznjenju strony preferuja so stabilne wersije bóle hač nowše.',
	'revreview-toolow'      => 'Dyrbiš za kóždy z deleka naspomnjenych atributow hódnotu wyše hač „{{int:revreview-accuracy-0}}“ zapodać,
	zo by so wersija jako přehladana woznamjeniła. Zo by wersiju zaćisnył, dyrbja wšě atributy „{{int:revreview-accuracy-0}}“ być.',
	'revreview-flag'        => 'Wersiju #$1 přepruwować',
	'revreview-legend'      => 'Wobsah wersije pohódnoćić',
	'revreview-notes'       => 'Wobkedźbowanja abo přispomnjenki, kotrež maja so pokazać:',
	'revreview-accuracy'    => 'Dokładnosć',
	'revreview-accuracy-0'  => 'njespušćomna',
	'revreview-accuracy-1'  => 'přehladana',
	'revreview-accuracy-2'  => 'pruwowana',
	'revreview-accuracy-3'  => 'žórła přepruwowane',
	'revreview-accuracy-4'  => 'wuběrna',
	'revreview-depth'       => 'Hłubokosć',
	'revreview-depth-0'     => 'njespušćomna',
	'revreview-depth-1'     => 'jednora',
	'revreview-depth-2'     => 'srěnja',
	'revreview-depth-3'     => 'wysoka',
	'revreview-depth-4'     => 'wuběrna',
	'revreview-style'       => 'Čitajomnosć',
	'revreview-style-0'     => 'njespušćomna',
	'revreview-style-1'     => 'akceptabelna',
	'revreview-style-2'     => 'dobra',
	'revreview-style-3'     => 'precizna',
	'revreview-style-4'     => 'wuběrna',
	'revreview-log'         => 'Protokolowy zapisk:',
	'revreview-submit'      => 'Přepruwowanje składować',
	'revreview-changed'     => '\'\'\'Naprašowanska akcija njeda so ma tutu wersiju nałožić.\'\'\' Předłoha abo wobraz bu bjez podataje wersije naprašowany. To móže so stać, jeli dynamiska předłoha dalšu předłohu abo dalši wobraz zapřijmje, kotrejž stej wot wariable wotwisnej, kotraž je so wot spočatka pruwowanja změniła. Znowačitanje strony a nowe startowanje pruwowanja móže tón problem rozrisać.',
	'stableversions'        => 'Stabilne wersije',
	'stableversions-leg1'   => 'Přepruwowane wersije za nastawk nalistować',
	'stableversions-page'   => 'Mjeno nastawka',
	'stableversions-none'   => '[[:$1]] přepruwowane wersije nima.',
	'stableversions-list'   => 'To je lisćina přepruwowanych wersijow wot [[:$1]]:',
	'stableversions-review' => 'Přepruwowany dnja <i>$1</i>',
	'review-diff2stable'    => 'Rozdźěl k poslednjej stabilnej wersiji',
	'review-diff2oldest'    => 'Rozdźěl k najstaršej wersiji',
	'unreviewedpages'       => 'Njepruwowane nastawki',
	'viewunreviewed'        => 'Lisćina njepruwowanych nastawkow',
	'unreviewed-outdated'   => 'Jenož strony pokazać, kotrež maja njepruwowane wersije po stabilnej wersiji.',
	'unreviewed-category'   => 'Kategorija:',
	'unreviewed-diff'       => 'Změny',
	'unreviewed-list'       => 'Tuta strona naliči nastawki kotrež hišće pruwowane njejsu abo maja njepruwowane wersije.',
	'revreview-visibility'  => 'Tuta strona ma [[Help:Article validation|stabilnu wersiju]], kotraž da so
	[{{fullurl:Special:Stabilization|page={{FULLPAGENAMEE}}}} konfigurować].',
	'stabilization'         => 'Stabilizacija strony',
	'stabilization-text'    => 'Změń nastajenja, zo by postajił, kak ma so stabilna wersija wot "[[:$1|$1]]" wubrać a zwobraznić.',
	'stabilization-perm'    => 'Twoje wužiwarske konto nima trěbne prawo, zo by nastajenja stabilneje wersije změniło.
	Aktualne nastajenja za „[[:$1|$1]]“ su:',
	'stabilization-page'    => 'Mjeno strony:',
	'stabilization-leg'     => 'Stabilnu wersiju za stronu konfigurować',
	'stabilization-select'  => 'Wuběranje stabilneje wersije',
	'stabilization-select1' => 'Poslednja pruwowana wersija; jeli žana njeje, potom poslednja přehladana wersija',
	'stabilization-select2' => 'Poslednja pruwowana wersija',
	'stabilization-def'     => 'Wersija zwobraznjena w normalnym napohledźe strony',
	'stabilization-def1'    => 'Stabilna wersija',
	'stabilization-def2'    => 'Aktualna wersija',
	'stabilization-submit'  => 'Potwjerdźić',
	'stabilization-dne'     => 'Njeje strona „[[:$1|$1]]“. Žana konfiguracija móžno.',
	'stabilization-success' => 'Nastajenja za stabilnu wersiju wot "[[:$1|$1]]" wuspěšnje stajene.',
	'stabilization-sel-short' => 'Priorita',
	'stabilization-sel-short-0' => 'Kwalita',
	'stabilization-sel-short-1' => 'Žana',
	'stabilization-def-short' => 'Standard',
	'stabilization-def-short-0' => 'Aktualny',
	'stabilization-def-short-1' => 'Stabilny',
);

/* Norwegian (Jon Harald Søby) */
$RevisionreviewMessages['no'] = array(
	'editor'                => 'Redaktør',
	'group-editor'          => 'Redaktøter',
	'group-editor-member'   => 'Redaktør',
	'grouppage-editor'      => '{{ns:project}}:Redaktør',
	'reviewer'              => 'godkjenner',
	'group-reviewer'        => 'godkjennere',
	'group-reviewer-member' => 'godkjenner',
	'grouppage-reviewer'    => '{{ns:project}}:godkjenner',
	'revreview-current'     => 'Nåværende revisjon',
	'revreview-stable'      => 'Stabil versjon',
	'revreview-noflagged'   => 'Det er ingen godkjente revisjoner av denne siden, så den har \'\'\'ikke\'\'\' blitt [[Help:Article validation|kvalitetssjekket]].',
	'revreview-basic'       => 'Dette er den siste [[Help:Article validation|stabile]] revisjonen ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} se alle]) av denne siden, [{{fullurl:Special:Log/review|page={{FULLPAGENAMEE}}}} godkjent] <i>$4</i>. Den [{{fullurl:{{FULLPAGENAMEE}}|stable=false}} nåværende revisjonen] kan vanligvis redigeres, og er mer oppdatert. Det er $3 {{PLURAL:$3|revisjon|revisjoner}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=$2}} endringer]) som venter på godkjenning.',
	'revreview-quality'     => 'Dette er den siste [[Help:Article validation|kvalitetsrevisjonen]] ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} se alle]) av denne siden, [{{fullurl:Special:Log/review|page={{FULLPAGENAMEE}}}} godkjent] <i>$4</i>. Den [{{fullurl:{{FULLPAGENAMEE}}|stable=false}} nåværende revisjonen] kan vanligvis redigeres, og er mer oppdatert. Det er $3 {{PLURAL:$3|revisjon|revisjoner}} ([{{fullurl:{{FULLPAGENAME}}|oldid=$1&diff=$2}} endringer]) som venter på godkjenning.',
	'revreview-static'      => 'Dette er en [[Help:Article validation|godkjent]] revisjon av siden \'\'\'[[$3]]\'\'\', [{{fullurl:Special:Log/review|page=$1}} godkjent] <i>$2</i>. Den [{{fullurl:$3|stable=false}} nåværende revisjonen] er kan vanligvis redigeres, og er mer oppdatert.',
	'revreview-note'        => '[[User:$1]] hadde følgende merknader under [[Help:Article validation|godkjenning]] av denne revisjonen:',
	'hist-stable'           => '[stabil]',
	'hist-quality'          => '[kvalitet]',
	'flaggedrevs'           => 'Flaggede revisjoner',
	'review-logpage'        => 'Artikkelgodkjenningslogg',
	'review-logpagetext'    => 'Dette er en logg over endringer i revisjoner [[Help:Article validation|godkjenningsstatus]] for innholdssider.',
	'review-logentrygrant'  => 'godkjente en versjon av [[$1]]',
	'review-logaction'      => 'revisjon $1',
	'revisionreview'        => 'Godkjenningsstatus',
	'revreview-main'        => 'Du må velge en revisjon fra en innholdsside for å kunne godkjenne den.

Se [[Special:Unreviewedpages]] for en liste over sider uten godkjenning.',
	'revreview-selected'    => 'Valgt revisjon av \'\'\'$1:\'\'\'',
	'revreview-text'        => 'Godkjente revisjoner er satt til standard i stedet for nyeste revisjoner.',
	'revreview-flag'        => 'Godkjenn denne revisjonen (#$1):',
	'revreview-notes'       => 'Merknader:',
	'revreview-accuracy'    => 'Nøyaktighet',
	'revreview-accuracy-0'  => 'Ikke godkjent',
	'revreview-accuracy-1'  => 'Sett',
	'revreview-accuracy-2'  => 'Nøyaktig',
	'revreview-accuracy-3'  => 'Gode kilder',
	'revreview-accuracy-4'  => 'Utmerket',
	'revreview-depth'       => 'Dybde',
	'revreview-depth-0'     => 'Ikke godkjent',
	'revreview-depth-1'     => 'Grunnleggende',
	'revreview-depth-2'     => 'Moderat',
	'revreview-depth-3'     => 'Høy',
	'revreview-depth-4'     => 'Utmerket',
	'revreview-style'       => 'Lesbarhet',
	'revreview-style-0'     => 'Ikke godkjent',
	'revreview-style-1'     => 'Akseptabel',
	'revreview-style-2'     => 'God',
	'revreview-style-3'     => 'Konsis',
	'revreview-style-4'     => 'Utmerket',
	'revreview-log'         => 'Loggkommentar:',
	'stableversions'        => 'Stabile versjoner',
	'stableversions-page'   => 'Sidenavn',
	'stableversions-none'   => '[[$1]] har ingen godkjente revisjoner.',
	'stableversions-list'   => 'Følgende er en liste over revisjoner av [[$1]] som har blitt godkjent:',
	'stableversions-review' => 'Godkjent <i>$1</i>',
	'review-diff2stable'    => 'Forskjell fra siste stabile versjon',
	'review-diff2oldest'    => 'Forskjell fra eldste revisjon',
	'unreviewedpages'       => 'Ikke godkjente sider',
	'included-nonquality'   => 'Inkluder godkjente sider som ikke er merket som kvalitet.',
	'unreviewed-list'       => 'Denne sider lister opp artikler som ikke har blitt godkjent enda.',
);

/* Piedmontese (Bèrto 'd Sèra) */
$RevisionreviewMessages['pms'] = array(
	'editor'                => 'Redator',
	'group-editor'          => 'Redator',
	'group-editor-member'   => 'Redator',
	'grouppage-editor'      => '{{ns:project}}:Redator',
	'reviewer'              => 'Revisor',
	'group-reviewer'        => 'Revisor',
	'group-reviewer-member' => 'Revisor',
	'grouppage-reviewer'    => '{{ns:project}}:Revisor',
	'revreview-current'     => 'Version corenta',
	'revreview-stable'      => 'Version stàbila',
	'revreview-oldrating'   => 'A l\'é stait giudicà për:',
	'revreview-noflagged'   => 'A-i é pa gnun-a version revisionà dë sta pàgina-sì, donca a l\'é belfé ch\'a la sia \'\'\'nen\'\'\' staita
	[[Help:Article validation|controlà]] coma qualità.',
	'revreview-quick-see-quality' => '\'\'\'Corenta\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} ùltima version votà për qualità]',
	'revreview-quick-see-basic' => '\'\'\'Corenta\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} ùltima version vardà]',
	'revreview-quick-basic' => '\'\'\'Vardà\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} version corenta] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|modìfica|modìfiche}}])',
	'revreview-quick-quality' => '\'\'\'Qualità\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} version corenta] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|modìfica|modìfiche}}])',
	'revreview-quick-none'  => '\'\'\'Corenta\'\'\'. Pa gnun-a version revisionà.',
	'revreview-newest-basic' => 'L\'[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} ùltima version vardà] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} vardeje tute]) dë sta pàgina-sì a l\'é staita [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovà]
	 dël <i>$2</i>. <br/> A-i {{plural:$3|é|son}} $3 version ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} modìfiche]) ch\'a speto na revision.',
	'revreview-newest-quality' => 'L\'[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} ùltim vot ëd qualità] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} vardeje tuti]) dë sta pàgina-sì a l\'é stait [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovà]
	 dël <i>$2</i>. <br/> A-i {{plural:$3|é|son}} $3 version ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} modìfiche]) ch\'a speto d\'esse revisionà.',
	'revreview-basic'       => 'Costa-sì a l\'é l\'ùltima version [[Help:Article validation|vardà]] dla pàgina, 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovà] dël <i>$2</i>. La [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} version corenta] 
	për sòlit as peul [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} modifichesse] e a l\'é pì agiornà. A-i {{plural:$3|é $3 revision|son $3 version}} 
	([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} modìfiche]) ch\'a speto d\'esse vardà.',
	'revreview-quality'     => 'Costa-sì a l\'é l\'ùltima revision ëd [[Help:Article validation|qualità]] dë sta pàgina, e a l\'é staita
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovà] dël <i>$2</i>. La [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} version corenta] 
	për sòlit as peul [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} modifichesse] e a l\'é pì agiornà. A-i {{plural:$3|é|son}} $3 version 
	([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} modìfiche]) da revisioné.',
	'revreview-static'      => 'Costa a l\'é na version [[Help:Article validation|revisionà]] dë \'\'\'[[:$3|sta pàgina]]\'\'\', 
	[{{fullurl:Special:Log/review|page=$1}} aprovà] dij <i>$2</i>. La [{{fullurl:$3|stable=0}} version corenta] 
	për sòlit as peul modifichesse e a l\'é pì agiornà.',
	'revreview-toggle'      => '(+/-)',
	'revreview-note'        => '[[User:$1]] a l\'ha buta-ie ste nòte-sì a la revision, antramentr ch\'a la [[Help:Article validation|controlava]]:',
	'hist-stable'           => '[vardà]',
	'hist-quality'          => '[qualità]',
	'flaggedrevs'           => 'Revision marcà',
	'review-logpage'        => 'Registr dij contròj dj\'artìcoj',
	'review-logpagetext'    => 'Sossì a l\'é un registr dle modìfiche dlë stat d\'[[Help:Article validation|aprovassion]] 
	dle pàgine ëd contnù.',
	'review-logentrygrant'  => 'Na version ëd $1 a l\'é staita vardà',
	'review-logentryrevoke' => 'depressà na version ëd $1',
	'review-logaction'      => 'Nùmer ëd revision $1',
	'revisionreview'        => 'Revisioné le version',
	'revreview-main'        => 'Për podej revisioné a venta ch\'as selession-a na version ëd na pàgina ëd contnù. 

	Ch\'a varda [[Special:Unreviewedpages|da revisioné]] për na lista ëd pàgine ch\'a speto na revision.',
	'revreview-selected'    => 'Version selessionà ëd \'\'\'$1:\'\'\'',
	'revreview-text'        => 'Për sòlit pitòst che nen j\'ùltime, as ësmon-o për contnù le version stàbij.',
	'revreview-toolow'      => 'A venta ch\'a buta tuti j\'atribut ambelessì sota almanch pì àot che "pa aprovà" përché
	na version ës conta da revisionà. Për dëspresié na version ch\'a-i buta tuti ij camp a "pa aprovà".',
	'revreview-flag'        => 'Revisioné sta version (#$1):',
	'revreview-legend'      => 'Deje \'l vot al contnù dla version:',
	'revreview-notes'       => 'Osservation ò nòte da smon-e:',
	'revreview-accuracy'    => 'Cura',
	'revreview-accuracy-0'  => 'Pa aprovà',
	'revreview-accuracy-1'  => 'Vardà',
	'revreview-accuracy-2'  => 'Curà',
	'revreview-accuracy-3'  => 'Bon-e sorgiss',
	'revreview-accuracy-4'  => 'Premià',
	'revreview-depth'       => 'Ancreus',
	'revreview-depth-0'     => 'Pa aprovà',
	'revreview-depth-1'     => 'Mìnim',
	'revreview-depth-2'     => 'Mes',
	'revreview-depth-3'     => 'Bon',
	'revreview-depth-4'     => 'Premià',
	'revreview-style'       => 'Belfé da lese',
	'revreview-style-0'     => 'Pa aprovà',
	'revreview-style-1'     => 'A peul andé',
	'revreview-style-2'     => 'Bon-a',
	'revreview-style-3'     => 'Concisa',
	'revreview-style-4'     => 'Premià',
	'revreview-log'         => 'Coment për ël registr:',
	'revreview-submit'      => 'Buta la revision',
	'stableversions'        => 'Version stàbij',
	'stableversions-leg1'   => 'Fé na lista dle version aprovà ëd na pàgina',
	'stableversions-page'   => 'Nòm dla pàgina',
	'stableversions-none'   => '[[:$1]] a l\'ha pa gnun-a version revisionà.',
	'stableversions-list'   => 'Costa-sì a l\'é na lista ëd version ëd [[:$1]] ch\'a son ëstaite revisionà:',
	'stableversions-review' => 'Revisionà dël <i>$1</i>',
	'review-diff2stable'    => 'Diferensa da \'nt l\'ùltima version stàbila',
	'review-diff2oldest'    => 'Diferensa da \'nt la revision pì veja',
	'unreviewedpages'       => 'Pàgine dësrevisionà',
	'viewunreviewed'        => 'Lista dle pàgine ëd contnù ch\'a son ëstaite dësrevisionà',
	'included-nonquality'   => 'Smon mach le pàgine già vardà ch\'a son sensa marca ëd qualità.',
	'unreviewed-list'       => 'Costa-sì a l\'é na lista d\'artìcoj ch\'a son anco\' pa stait revisionà.',
);

// Portuguese (Lugusto)
$RevisionreviewMessages['pt'] = array(
	'makevalidate-autosum'  => 'promovido automaticamente',
	'group-editor'          => 'Editores',
	'grouppage-editor'      => '{{ns:project}}:{{int:group-editor}}',
	'reviewer'              => 'Crítico',
	'group-reviewer'        => 'Críticos',
	'group-reviewer-member' => 'Crítico',
	'grouppage-reviewer'    => '{{ns:project}}:{{int:group-reviewer}}',
	'revreview-current'     => 'Esboço',
	'revreview-edit'        => 'Editar esboço',
	'revreview-source'      => 'código do esboço',
	'revreview-stable'      => 'Estável',
	'revreview-oldrating'   => 'Esteve avaliada como:',
	'revreview-noflagged'   => 'Não há edições críticas para esta página; talvez ainda \'\'\'não\'\'\' tenha sido [[{{ns:help}}:Validação de páginas|verificada]] a sua qualidade.',
	'revreview-quick-none'  => '\'\'\'Crítica\'\'\'. Não há edições críticas.',
	'revreview-quick-see-quality' => '\'\'\'Atual\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} ver edição estável]] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|alteração|alterações}}])',
	'revreview-quick-see-basic' => '\'\'\'Atual\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} ver edição estável]] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|alteração|alterações}}])',
	'revreview-quick-basic' => '\'\'\'[[{{ns:help}}:Validação de páginas|Crítica]]\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} ver edição atual]] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|alteração|alterações}}])',
	'revreview-quick-quality' => '\'\'\'[[{{ns:help}}:Validação de páginas|Estável]]\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} ver edição atual]] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|alteração|alterações}}])',
	'revreview-newest-basic' => 'A [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} mais recente edição crítica] ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} listar todas]) foi [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovada]
	 em <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|alteração|alterações}}] {{plural:$3|necessita|necessitam}} análise.',
	'revreview-newest-quality' => 'A [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} mais recente edição crítica] ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} listar todas]) foi [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovada] em <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|alteração|alterações}}] {{plural:$3|necessita|necessitam}} análise.',
	'revreview-basic'       => 'Esta é a mais recente edição [[{{ns:help}}:Validação de páginas|crítica]] desta página, [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovada] em <i>$2</i>. É possível [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} editar] a [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} versão atual]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|alteração|alterações}}] {{plural:$3|aguarda|aguardam}} revisão.',
	'revreview-quality'     => 'Esta é a mais recente edição [[{{ns:help}}:Validação de páginas|estável]], [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovada] em <i>$2</i>. É possível [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} editar] a [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} versão atual]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|alteração|alterações}}] {{plural:$3|aguarda|aguardam}} revisão.',
	'revreview-static'      => 'Esta é uma edição [[{{ns:help}}:Validação de páginas|crítica]] da \'\'\'[[:$3|$3]]\'\'\', [{{fullurl:Special:Log/review|page=$1}} aprovada] em <i>$2</i>. É possível [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} editar] a [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} versão atual].',
	'revreview-note'        => '[[{{ns:user}}:$1|$1]] deixou as seguintes observações ao [[{{ns:help}}:Validação de páginas|criticar]] esta edição:',
	'revreview-update'      => 'Por gentileza, analise todas as alterações exibidas a seguir, feitas desde a última edição estável desta página. Talvez as predefinições e imagens utilizadas possam ter sido também alteradas.',
	'revreview-auto'        => '(automático)',
	'revreview-auto-w'      => 'Você está editando a edição estável, todas as alterações serão \'\'\'automaticamente tidas como revistas\'\'\' (ed. crítica). Talvez deseje prever a página antes de a salvar.',
	'revreview-auto-w-old'  => 'Você está editando uma edição antiga, todas as alterações serão \'\'\'automaticamente tidas como revistas\'\'\' (ed. crítica). Talvez deseje prever a página antes de a salvar.',
	'hist-stable'           => '[ed. crítica]',
	'hist-quality'          => '[ed. estável]',
	'flaggedrevs'           => 'Edições Críticas',
	'review-logpage'        => 'Registo de edições críticas',
	'review-logpagetext'    => 'Este é um registo de alterações de status de páginas de conteúdo com [[{{ns:help}}:Validação de páginas|edições críticas]].',
	'review-logentrygrant'  => 'foi criticada uma edição de $1',
	'review-logentryrevoke' => 'foi rebaixada uma edição de $1',
	'review-logaction'      => 'ID de edição: $1',
	'revisionreview'        => 'Criticar edições',
	'revreview-main'        => 'Você precisa selecionar uma edição em específico de uma página de conteúdo para poder fazer uma edição crítica.

Veja [[{{ns:special}}:Unreviewedpages]] para uma listagem de páginas ainda não criticadas.',
	'revreview-selected'    => 'Edição selecionada de \'\'\'$1:\'\'\'',
	'revreview-text'        => 'As edições aprovadas são exibidas por padrão no lugar de edições mais recentes.',
	'revreview-toolow'      => 'Você precisará criticar em cada um dos atributos com valores mais altos do que "rejeitada" para que uma edição seja considerada aprovada. Para rebaixar uma edição, defina todos os atributos como "rejeitada".',
	'revreview-flag'        => 'Critique esta edição (#$1)',
	'revreview-legend'      => 'Avaliar conteúdo da edição',
	'revreview-notes'       => 'Observações ou notas a serem exibidas:',
	'revreview-accuracy'    => 'Precisão',
	'revreview-accuracy-0'  => 'Rejeitada',
	'revreview-accuracy-1'  => 'Objetiva',
	'revreview-accuracy-2'  => 'Precisa',
	'revreview-accuracy-3'  => 'Bem referenciada',
	'revreview-accuracy-4'  => 'Exemplar',
	'revreview-depth'       => 'Profundidade',
	'revreview-depth-0'     => 'Rejeitada',
	'revreview-depth-1'     => 'Básica',
	'revreview-depth-2'     => 'Moderada',
	'revreview-depth-3'     => 'Alta',
	'revreview-depth-4'     => 'Exemplar',
	'revreview-style'       => 'Inteligibilidade',
	'revreview-style-0'     => 'Rejeitada',
	'revreview-style-1'     => 'Aceitável',
	'revreview-style-2'     => 'Boa',
	'revreview-style-3'     => 'Concisa',
	'revreview-style-4'     => 'Exemplar',
	'revreview-log'         => 'Comentário exibido no registo:',
	'revreview-submit'      => 'Enviar crítica',
	'revreview-changed'     => '\'\'\'A acção seleccionada não pode ser executada nesta edição.\'\'\'
	
Uma predefinição ou imagem pode ter sido requisitada sem uma edição específica ter sido informada. Isso pode ocorrer quando uma predefinição dinâmica faz transclusão de outra imagem ou predefinição através de uma variável que pode ter sido alterada enquanto era feita a edição crítica nesta página. Recarregar a página e enviar uma nova edição crítica talvez seja suficiente para contornar este contratempo.',
	'stableversions'        => 'Edições Críticas',
	'stableversions-leg1'   => 'Listar edições críticas de uma página',
	'stableversions-page'   => 'Título da página',
	'stableversions-none'   => '[[:$1]] não possui edições críticas.',
	'stableversions-list'   => 'A seguir, uma lista das edições de "[[:$1]]" que são edições críticas:',
	'stableversions-review' => 'Criticada em <i>$1</i> por $2',
	'review-diff2stable'    => 'Comparar com a edição crítica mais recente',
	'review-diff2oldest'    => 'Comparar com a edição mais antiga',
	'unreviewedpages'       => 'Páginas sem edições críticas',
	'viewunreviewed'        => 'Listar páginas de conteúdo que ainda não possuam uma edição crítica',
	'unreviewed-outdated'   => 'Substituir pelas páginas que possuem edição crítica mas sofreram alterações que ainda não foram revistas.',
	'unreviewed-category'   => 'Categoria',
	'unreviewed-diff'       => 'Alterações',
	'unreviewed-list'       => 'Esta página lista as páginas de conteúdo que ainda não receberam uma edição crítica ou que possuam uma nova edição a ser analisada.',
);

// Slovak (Helix84)
$RevisionreviewMessages['sk'] = array(
	'makevalidate-autosum'  => 'samopovýšenie',
	'editor'                => 'Redaktor',
	'group-editor'          => 'Redaktori',
	'group-editor-member'   => 'Redaktor',
	'grouppage-editor'      => '{{ns:project}}:Redaktor',
	'reviewer'              => 'Revízor',
	'group-reviewer'        => 'Revízori',
	'group-reviewer-member' => 'Revízor',
	'grouppage-reviewer'    => '{{ns:project}}:Revízor',
	'revreview-current'     => 'Aktuálna revízia',
	'revreview-edit'        => 'Upraviť koncept',
	'revreview-source'      => 'Zdroj konceptu',
	'revreview-stable'      => 'Stabilná verzia',
	'revreview-oldrating'   => 'Bolo ohodnotené ako:',
	'revreview-noflagged'   => 'Neexistujú revidované verzie tejto stránky, takže jej
	kvalita \'\'\'nebola\'\'\' [[Help:Revízia článkov|skontrolovaná]].',
	'revreview-quick-none'  => '\'\'\'Aktuálna\'\'\'. Žiadne revízie neboli skontrolvoané..',
	'revreview-quick-see-quality' => '\'\'\'Aktuálna\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Pozri poslednú kvalitnú revíziu]',
	'revreview-quick-see-basic' => '\'\'\'Aktuálna\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Pozri poslednú skontrolovanú revíziu]',
	'revreview-quick-basic' => '\'\'\'Skontrolovaná\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Pozri aktuálnu revíziu] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|zmena|zmeny|zmien}}])',
	'revreview-quick-quality' => '\'\'\'Kvalitná\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Pozri aktuálnu revíziu] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|zmena|zmeny|zmien}}])',
	'revreview-newest-basic' => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Posledná overená revízia] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} zobraziť všetky]) tejto stránky bola [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválená]
	 <i>$2</i>. <br/> {{plural:$3|$3 revízia|$3 revízie||$3 revízií}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} zmeny]) čaká na schválenie.',
	'revreview-newest-quality' => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Posledná kvalitná revízia] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} zobraziť všetky]) tejto stránky bola [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválená]
	 <i>$2</i>. <br/> {{plural:$3|$3 revízia|$3 revízie||$3 revízií}} ([{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} zmeny]) čaká na schválenie.',
	'revreview-basic'       => 'Toto je najnovšia [[Help:Revízia článkov|stabilná]] verzia tejto stránky, 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválená] <i>$4</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Aktuálna verzia] 
	je zvyčajne [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} prístupná úpravám] a aktuálnejšia. 
Na revíziu čaká [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=$2}} {{plural:$3|jedna zmena|$3 zmeny|$3 zmien}}].',
	'revreview-quality'     => 'Toto je najnovšia [[Help:Revízia článkov|kvalitná]] verzia tejto stránky, 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} schválená] <i>$4</i>. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Aktuálna verzia] 
	je zvyčajne [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} prístupná úpravám] a aktuálnejšia. 
Na revíziu čaká [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=$2}} {{plural:$3|jedna zmena|$3 zmeny|$3 zmien}}].',
	'revreview-static'      => 'Toto je [[Help:Revízia článkov|skontrolovaná]] verzia stránky \'\'\'[[:$3]]\'\'\', 
	[{{fullurl:Special:Log/review|page=$1}} schválená] <i>$2</i>. [{{fullurl:$3|stable=0}} Najnovšia verzia] 
	je zvyčajne prístupná úpravám a aktuálnejšia.',
	'revreview-toggle'      => '(prepnúť zobrazenie podrobností)',
	'revreview-note'        => '[[User:$1]] urobil nasledovné poznámky počas [[Help:Revízia článkov|kontroly]] tejto verzie:',
	'revreview-update'      => 'Prosím, skontrolujte všetky zmeny (zobrazené nižšie), ktoré boli vykonané od poslednej stabilnej revízie. Šablóny a obrázky sa tiež mohli zmeniť.',
	'revreview-auto'        => '(automatické)',
	'revreview-auto-w'      => 'Upravujete stabilnú revíziu, akékoľvek zmeny budú \'\'\'automaticky označené ako skontrolované\'\'\'. Pred uložením by ste mali použiť náhľad.',
	'revreview-auto-w-old'  => 'Upravujete strú revíziu, akékoľvek zmeny budú \'\'\'automaticky označené ako skontrolované\'\'\'. Pred uložením by ste mali použiť náhľad.',
	'hist-stable'           => '[stabilná]',
	'hist-quality'          => '[kvalitná]',
	'flaggedrevs'           => 'Označené verzie',
	'review-logpage'        => 'Záznam kontrol stránky',
	'review-logpagetext'    => 'Toto je záznam zmien stavu [[Help:Revízia článkov|kontroly]] verzií
	stránok s obsahom.',
	'review-logentrygrant'  => 'skontrolovaná verzia $1',
	'review-logentryrevoke' => 'zastaralá verzia $1',
	'review-logaction'      => 'ID verzie $1',
	'revisionreview'        => 'Prezrieť kontroly',
	'revreview-main'        => 'Musíte vybrať konkrétnu verziu stránky s obsahom, aby ste ju mohli skontrolovať. 

	Pozri zoznam neskontrolovaných stránok
	[[Special:Unreviewedpages]].',
	'revreview-selected'    => 'Zvolená verzia \'\'\'$1:\'\'\'',
	'revreview-text'        => 'Stabilné verzie, nie najnovšie verzie, sú nastavené ako štandardný obsah stránky.',
	'revreview-toolow'      => 'Musíte ohodnotiť každý z nasledujúcich atribútov minimálne vyššie ako "neschválené", aby bolo možné
	verziu považovať za skontrolovanú. Ak chcete učiniť verziu zastaralou, nastavte všetky polia na "neschválené".',
	'revreview-flag'        => 'Skontrolovať túto verziu (#$1):',
	'revreview-legend'      => 'Ohodnotiť obsah verzie:',
	'revreview-notes'       => 'Pozorovania alebo poznámky, ktoré sa majú zobraziť:',
	'revreview-accuracy'    => 'Presnosť',
	'revreview-accuracy-0'  => 'neschválené',
	'revreview-accuracy-1'  => 'zbežná',
	'revreview-accuracy-2'  => 'presná',
	'revreview-accuracy-3'  => 'dobre uvedené zdroje',
	'revreview-accuracy-4'  => 'odporúčaný',
	'revreview-depth'       => 'Hĺbka',
	'revreview-depth-0'     => 'neschválené',
	'revreview-depth-1'     => 'základná',
	'revreview-depth-2'     => 'stredná',
	'revreview-depth-3'     => 'vysoká',
	'revreview-depth-4'     => 'odporúčaný',
	'revreview-style'       => 'Čitateľnosť',
	'revreview-style-0'     => 'neschválené',
	'revreview-style-1'     => 'prijateľná',
	'revreview-style-2'     => 'dobrá',
	'revreview-style-3'     => 'zhustená',
	'revreview-style-4'     => 'odporúčaný',
	'revreview-log'         => 'Komentár záznamu:',
	'revreview-submit'      => 'Aplikovať kontrolu',
	'revreview-changed'     => '\'\'\'Požadovaná činnosť by sa namala vykonávať na tejto revízii.\'\'\'
	
	Šablóna alebo obrázok mohlol byť vyžiadaný bez uvedenia konkrétnej verzie. To sa môže stať, keď
	dynamická šablóna transkluduje iný obrázok alebo šablónu v závislosti od premennej, ktorá sa zmenila, odkedy ste začali
	s kontrolou tejto stránky. Obnovením stránky a opätovnou kontrolou vyriešite tento problém.',
	'stableversions'        => 'Stabilné verzie',
	'stableversions-leg1'   => 'Zoznam skontrolovaných verzií stránky',
	'stableversions-page'   => 'Názov stránky',
	'stableversions-none'   => '[[:$1]] nemá skontrolované verzie.',
	'stableversions-list'   => 'Nasleduje zoznam verzií stránky [[:$1]], ktoré boli skontrolované:',
	'stableversions-review' => 'Skontrolované <i>$1</i>',
	'review-diff2stable'    => 'Rozdiely oproti poslednej stabilnej verzii',
	'review-diff2oldest'    => 'Rozdiely oproti najstaršej verzii',
	'unreviewedpages'       => 'Neskontrolované stránky',
	'viewunreviewed'        => 'Zoznam neskontrolovaných stránok s obsahom',
	'unreviewed-outdated'   => 'Zobraziť stránky, ktoré majú neskontrolované revízie stabilnej verzie.',
	'unreviewed-category'   => 'Kategória:',
	'unreviewed-diff'       => 'Zmeny',
	'unreviewed-list'       => 'Táto stránka obsahuje zoznam článkov, ktoré zatiaľ neboli skontrolované.',
);

// Cantonese (Shinjiman)
$RevisionreviewMessages['yue'] = array( 
	'makevalidate-autosum'=> '自動升格',
	'editor'              => '編輯',
	'group-editor'        => '編輯',
	'group-editor-member' => '編輯',
	'grouppage-editor'    => '{{ns:project}}:編者',

	'reviewer'              => '評論家',
	'group-reviewer'        => '評論家',
	'group-reviewer-member' => '評論家',
	'grouppage-reviewer'    => '{{ns:project}}:評論家',

	'revreview-current'   => '草稿',
	'revreview-edit'      => '編輯草稿',
	'revreview-source'    => '草稿原始碼',
	'revreview-stable'    => '穩定',
	'revreview-oldrating' => '曾經評定為:',
	'revreview-noflagged' => '呢一版無複審過嘅修訂，佢可能\'\'\'未\'\'\'[[Help:文章確認|檢查]]質量。',
	
	'revreview-quick-none' => '\'\'\'現時嘅\'\'\'。無已複審嘅修訂。',
	'revreview-quick-see-quality' => '\'\'\'現時嘅\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 睇最後嘅質素修訂]]',
	'revreview-quick-see-basic' => '\'\'\'現時嘅\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 睇最後檢查過嘅修訂]]',
	'revreview-quick-basic'  => '\'\'\'[[Help:文章確認|視察過嘅]]\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 睇現時修訂]] 
	($2[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} 次更改])',
	'revreview-quick-quality' => '\'\'\'[[Help:文章確認|有質素嘅]]\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 睇現時修訂]] 
	($2[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} 次更改])',
	'revreview-newest-basic'    => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 最後視察過嘅修訂] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} 列示全部]) 響<i>$2</i>曾經[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准過嘅]。
	 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改]需要複審。',
	'revreview-newest-quality'    => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 最後有質素嘅修訂] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} 列示全部]) 響<i>$2</i>曾經[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准過嘅]。
	 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改]需要複審。',
	'revreview-basic'  => '呢個係最後[[Help:文章確認|視察過嘅]]修訂，
	響<i>$2</i>[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准]。[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 現時修訂]
	可以[{{fullurl:{{FULLPAGENAMEE}}|action=edit}} 改]；[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改] 
	等緊去複審。',
	'revreview-quality'  => '呢個係最後[[Help:文章確認|有質素嘅]]修訂，
	響<i>$2</i>[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准]。[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 現時修訂] 
	可以[{{fullurl:{{FULLPAGENAMEE}}|action=edit}} 改]]；[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改] 
	等緊去複審。',
	'revreview-static'  => '呢個係一個響\'\'\'[[:$3|呢版]]\'\'\'[[Help:文章確認|複審過嘅]]修訂，
	響<i>$2</i>[{{fullurl:Special:Log/review|page=$1}} 批准]。[{{fullurl:$3|stable=0}} 現時修訂]
	可以[{{fullurl:$3|action=edit}} 改]。',
	'revreview-toggle' => '(+/-)',
	'revreview-note' => '[[User:$1]]響呢次修訂度加咗下面嘅[[Help:文章確認|複審]]註解:',
	'revreview-update' => '請複審自從響呢版嘅穩定版以來嘅任何更改 (響下面度顯示) 。模同圖亦可能同時更改。',
	'revreview-auto' => '(自動)',
	'revreview-auto-w' => "'''注意:''' 你而家係響穩定修訂度做緊更改，你嘅編輯將會自動被複審。
	你可以響保存之前先預覽一吓。",

	'hist-stable'  => '[睇過]',
	'hist-quality' => '[質素]',

    'flaggedrevs'        => '加咗旗嘅修訂',
    'review-logpage'     => '文章複審記錄',
	'review-logpagetext' => '呢個係內容版[[Help:文章確認|批准]]狀態嘅更改記錄。',
	'review-logentrygrant'   => '已經複審咗 $1',
	'review-logentryrevoke'  => '已經捨棄咗 $1 嘅版本',
	'review-logaction'  => '修訂 ID $1',

    'revisionreview'       => '複審修訂',		
    'revreview-main'       => '你一定要響一版內容頁度揀一個個別嘅修訂去複審。

	睇[[Special:Unreviewedpages]]去拎未複審嘅版。',	
	'revreview-selected'   => "已經揀咗 '''$1''' 嘅修訂:",
	'revreview-text'       => "穩定版會設定做一版睇嗰陣嘅預設內容，而唔係最新嘅修訂。",
	'revreview-toolow'     => '你一定要最少將下面每一項嘅屬性評定高過"未批准"，去將一個修訂複審。
	要捨棄一個修訂，設定全部格做"未批准"。',
	'revreview-flag'       => '複審呢次修訂 (#$1)',
	'revreview-legend'     => '評定修訂內容',
	'revreview-notes'      => '要顯示嘅意見或註解:',
	'revreview-accuracy'   => '準確度',
	'revreview-accuracy-0' => '未批准',
	'revreview-accuracy-1' => '視察過',
	'revreview-accuracy-2' => '準確',
	'revreview-accuracy-3' => '有好來源',
	'revreview-accuracy-4' => '正',
	'revreview-depth'      => '深度',
	'revreview-depth-0'    => '未批准',
	'revreview-depth-1'    => '基本',		
	'revreview-depth-2'    => '中等',
	'revreview-depth-3'    => '高',
	'revreview-depth-4'    => '正',
	'revreview-style'      => '可讀性',
	'revreview-style-0'    => '未批准',
	'revreview-style-1'    => '可接受',
	'revreview-style-2'    => '好',
	'revreview-style-3'    => '簡潔',
	'revreview-style-4'    => '正',
	'revreview-log'        => '記錄註解:',
	'revreview-submit'     => '遞交複審',
	'revreview-changed'    => '\'\'\'個複審嘅動作唔可以響呢次修訂度進行。\'\'\'
	
	當無一個指定嘅版本嗰陣，一個模或圖已經被請求。
	當一個動態模包含住圖像或跟變數嘅模響你開始複審之後改過。 
	重新整理過呢版之後再重新複審就可以解決呢個問題。',

	'stableversions'        => '穩定版',
	'stableversions-leg1'   => '列示一版複審過嘅修訂',
	'stableversions-page'   => '版名',
	'stableversions-none'   => '[[:$1]]無複審過嘅修訂。',
	'stableversions-list'   => '下面係[[:$1]]已經複審過嘅修訂一覽:',
	'stableversions-review' => '響<i>$1</i>複審過',

    'review-diff2stable'    => '同上次穩定修訂嘅差異',
    'review-diff2oldest'    => "同最舊修訂嘅差異",

    'unreviewedpages'       => '未複審嘅版',
    'viewunreviewed'        => '列示未複審嘅內容版',
    'unreviewed-outdated'   => '只係顯示對穩定版修訂過嘅未複審修訂。',
    'unreviewed-category'   => '分類:',
    'unreviewed-diff'       => '更改',
    'unreviewed-list'       => '呢一版列示出重未複審或視察過嘅文章修訂。',
);

// Chinese (Simplified) (Shinjiman)
$RevisionreviewMessages['zh-hans'] = array( 
	'makevalidate-autosum'=> '自动升格',
	'editor'              => '编辑',
	'group-editor'        => '编辑',
	'group-editor-member' => '编辑',
	'grouppage-editor'    => '{{ns:project}}:编者',

	'reviewer'              => '评论家',
	'group-reviewer'        => '评论家',
	'group-reviewer-member' => '评论家',
	'grouppage-reviewer'    => '{{ns:project}}:评论家',

	'revreview-current'   => '草稿',
	'revreview-edit'      => '编辑草稿',
	'revreview-source'    => '草稿原始码',
	'revreview-stable'    => '稳定',
	'revreview-oldrating' => '曾经评定为:',
	'revreview-noflagged' => '这一页没有复审过的修订，它可能\'\'\'未\'\'\'[[Help:文章确认|检查]]质量。',
	
	'revreview-quick-none' => '\'\'\'现时的\'\'\'。没有已复审的修订。',
	'revreview-quick-see-quality' => '\'\'\'现时的\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 看睇最后的质素修订]]',
	'revreview-quick-see-basic' => '\'\'\'现时的\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 看最后检查过的修订]]',
	'revreview-quick-basic'  => '\'\'\'[[Help:文章确认|视察过的]]\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 看现时修订]] 
	($2[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} 次更改])',
	'revreview-quick-quality' => '\'\'\'[[Help:文章确认|有质素的]]\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 看现时修订]] 
	($2[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} 次更改])',
	'revreview-newest-basic'    => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 最后视察过的修订] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} 列示全部]) 于<i>$2</i>曾经[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准过的]。
	 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改]需要复审。',
	'revreview-newest-quality'    => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 最后有质素的修订] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} 列示全部]) 于<i>$2</i>曾经[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准过的]。
	 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改]需要复审。',
	'revreview-basic'  => '这个是最后[[Help:文章确认|视察过的]]修订，
	于<i>$2</i>[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准]。[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 现时修订]
	可以[{{fullurl:{{FULLPAGENAMEE}}|action=edit}} 更改]；[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改] 
	正等候复审。',
	'revreview-quality'  => '这个是最后[[Help:文章确认|有质素的]]修订，
	于<i>$2</i>[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准]。[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 现时修订] 
	可以[{{fullurl:{{FULLPAGENAMEE}}|action=edit}} 更改]]；[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改] 
	正等候复审。',
	'revreview-static'  => '这个是一个在\'\'\'[[:$3|这页]]\'\'\'[[Help:文章确认|复审过的]]修订，
	于<i>$2</i>[{{fullurl:Special:Log/review|page=$1}} 批准]。[{{fullurl:$3|stable=0}} 现时修订]
	可以[{{fullurl:$3|action=edit}} 更改]。',
	'revreview-toggle' => '(+/-)',
	'revreview-note' => '[[User:$1]]在这次修订中加入了以下的[[Help:文章确认|复审]]注解:',
	'revreview-update' => '请复审自从于这页的稳定版以来的任何更改 (在下面显示) 。模版和图像亦可能同时更改。',
	'revreview-auto' => '(自动)',
	'revreview-auto-w' => "'''注意:''' 您现在是在稳定修订中作出更改，您的编辑将会自动被复审。
	您可以在保存前先预览一下。",

	'hist-stable'  => '[已察]',
	'hist-quality' => '[质素]',

    'flaggedrevs'        => '标注修订',
    'review-logpage'     => '文章复审记录',
	'review-logpagetext' => '这个是内容页[[Help:文章确认|批准]]状态的更改记录。',
	'review-logentrygrant'   => '已复审 $1',
	'review-logentryrevoke'  => '已舍弃 $1 的版本',
	'review-logaction'  => '修订 ID $1',

    'revisionreview'       => '复审修订',		
    'revreview-main'       => '您一定要在一页的内容页中选择一个个别的修订去复审。

	参看[[Special:Unreviewedpages]]去撷取未复审的页面。',	
	'revreview-selected'   => "已经选择 '''$1''' 的修订:",
	'revreview-text'       => "稳定版会设置成一页查看时的预设内容，而非最新的修订。",
	'revreview-toolow'     => '您一定要最少将下面每一项的属性评定高于"未批准"，去将一个修订复审。
	要舍弃一个修订，设置全部栏位作"未批准"。',
	'revreview-flag'       => '复审这次修订 (#$1)',
	'revreview-legend'     => '评定修订内容',
	'revreview-notes'      => '要显示的意见或注解:',
	'revreview-accuracy'   => '准确度',
	'revreview-accuracy-0' => '未批准',
	'revreview-accuracy-1' => '视察过',
	'revreview-accuracy-2' => '准确',
	'revreview-accuracy-3' => '有良好来源',
	'revreview-accuracy-4' => '特色',
	'revreview-depth'      => '深度',
	'revreview-depth-0'    => '未批准',
	'revreview-depth-1'    => '基本',		
	'revreview-depth-2'    => '中等',
	'revreview-depth-3'    => '高',
	'revreview-depth-4'    => '特色',
	'revreview-style'      => '可读性',
	'revreview-style-0'    => '未批准',
	'revreview-style-1'    => '可接受',
	'revreview-style-2'    => '好',
	'revreview-style-3'    => '简洁',
	'revreview-style-4'    => '特色',
	'revreview-log'        => '记录注解:',
	'revreview-submit'     => '递交复审',
	'revreview-changed'    => '\'\'\'该复审的动作不可以在这次修订中进行。\'\'\'
	
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
    'review-diff2oldest'    => "跟最旧修订的差异",

    'unreviewedpages'       => '未复审页面',
    'viewunreviewed'        => '列示未复审的内容页',
    'unreviewed-outdated'   => '只显示对稳定版修订过的未复审修订。',
    'unreviewed-category'   => '分类:',
    'unreviewed-diff'       => '更改',
    'unreviewed-list'       => '这一页列示出还未复审或视察的文章修订。',
);

// Chinese (Traditional) (Shinjiman)
$RevisionreviewMessages['zh-hant'] = array( 
	'makevalidate-autosum'=> '自動升格',
	'editor'              => '編輯',
	'group-editor'        => '編輯',
	'group-editor-member' => '編輯',
	'grouppage-editor'    => '{{ns:project}}:編者',

	'reviewer'              => '評論家',
	'group-reviewer'        => '評論家',
	'group-reviewer-member' => '評論家',
	'grouppage-reviewer'    => '{{ns:project}}:評論家',

	'revreview-current'   => '草稿',
	'revreview-edit'      => '編輯草稿',
	'revreview-source'    => '草稿原始碼',
	'revreview-stable'    => '穩定',
	'revreview-oldrating' => '曾經評定為:',
	'revreview-noflagged' => '這一頁沒有複審過的修訂，它可能\'\'\'未\'\'\'[[Help:文章確認|檢查]]質量。',
	
	'revreview-quick-none' => '\'\'\'現時的\'\'\'。沒有已複審的修訂。',
	'revreview-quick-see-quality' => '\'\'\'現時的\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 看睇最後的質素修訂]]',
	'revreview-quick-see-basic' => '\'\'\'現時的\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 看最後檢查過的修訂]]',
	'revreview-quick-basic'  => '\'\'\'[[Help:文章確認|視察過的]]\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 看現時修訂]] 
	($2[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} 次更改])',
	'revreview-quick-quality' => '\'\'\'[[Help:文章確認|有質素的]]\'\'\'。[[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 看現時修訂]] 
	($2[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} 次更改])',
	'revreview-newest-basic'    => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 最後視察過的修訂] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} 列示全部]) 於<i>$2</i>曾經[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准過的]。
	 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改]需要複審。',
	'revreview-newest-quality'    => '[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} 最後有質素的修訂] 
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} 列示全部]) 於<i>$2</i>曾經[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准過的]。
	 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改]需要複審。',
	'revreview-basic'  => '這個是最後[[Help:文章確認|視察過的]]修訂，
	於<i>$2</i>[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准]。[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 現時修訂]
	可以[{{fullurl:{{FULLPAGENAMEE}}|action=edit}} 更改]；[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改] 
	正等候複審。',
	'revreview-quality'  => '這個是最後[[Help:文章確認|有質素的]]修訂，
	於<i>$2</i>[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} 批准]。[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} 現時修訂] 
	可以[{{fullurl:{{FULLPAGENAMEE}}|action=edit}} 更改]]；[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3次更改] 
	正等候複審。',
	'revreview-static'  => '這個是一個在\'\'\'[[:$3|這頁]]\'\'\'[[Help:文章確認|複審過的]]修訂，
	於<i>$2</i>[{{fullurl:Special:Log/review|page=$1}} 批准]。[{{fullurl:$3|stable=0}} 現時修訂]
	可以[{{fullurl:$3|action=edit}} 更改]。',
	'revreview-toggle' => '(+/-)',
	'revreview-note' => '[[User:$1]]在這次修訂中加入了以下的[[Help:文章確認|複審]]註解:',
	'revreview-update' => '請複審自從於這頁的穩定版以來的任何更改 (在下面顯示) 。模版和圖像亦可能同時更改。',
	'revreview-auto' => '(自動)',
	'revreview-auto-w' => "'''注意:''' 您現在是在穩定修訂中作出更改，您的編輯將會自動被複審。
	您可以在保存前先預覽一下。",

	'hist-stable'  => '[已察]',
	'hist-quality' => '[質素]',

    'flaggedrevs'        => '標註修訂',
    'review-logpage'     => '文章複審記錄',
	'review-logpagetext' => '這個是內容頁[[Help:文章確認|批准]]狀態的更改記錄。',
	'review-logentrygrant'   => '已複審 $1',
	'review-logentryrevoke'  => '已捨棄 $1 的版本',
	'review-logaction'  => '修訂 ID $1',

    'revisionreview'       => '複審修訂',		
    'revreview-main'       => '您一定要在一頁的內容頁中選擇一個個別的修訂去複審。

	參看[[Special:Unreviewedpages]]去擷取未複審的頁面。',	
	'revreview-selected'   => "已經選擇 '''$1''' 的修訂:",
	'revreview-text'       => "穩定版會設定成一頁檢視時的預設內容，而非最新的修訂。",
	'revreview-toolow'     => '您一定要最少將下面每一項的屬性評定高於"未批准"，去將一個修訂複審。
	要捨棄一個修訂，設定全部欄位作"未批准"。',
	'revreview-flag'       => '複審這次修訂 (#$1)',
	'revreview-legend'     => '評定修訂內容',
	'revreview-notes'      => '要顯示的意見或註解:',
	'revreview-accuracy'   => '準確度',
	'revreview-accuracy-0' => '未批准',
	'revreview-accuracy-1' => '視察過',
	'revreview-accuracy-2' => '準確',
	'revreview-accuracy-3' => '有良好來源',
	'revreview-accuracy-4' => '特色',
	'revreview-depth'      => '深度',
	'revreview-depth-0'    => '未批准',
	'revreview-depth-1'    => '基本',		
	'revreview-depth-2'    => '中等',
	'revreview-depth-3'    => '高',
	'revreview-depth-4'    => '特色',
	'revreview-style'      => '可讀性',
	'revreview-style-0'    => '未批准',
	'revreview-style-1'    => '可接受',
	'revreview-style-2'    => '好',
	'revreview-style-3'    => '簡潔',
	'revreview-style-4'    => '特色',
	'revreview-log'        => '記錄註解:',
	'revreview-submit'     => '遞交複審',
	'revreview-changed'    => '\'\'\'該複審的動作不可以在這次修訂中進行。\'\'\'
	
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
    'review-diff2oldest'    => "跟最舊修訂的差異",

    'unreviewedpages'       => '未複審頁面',
    'viewunreviewed'        => '列示未複審的內容頁',
    'unreviewed-outdated'   => '只顯示對穩定版修訂過的未複審修訂。',
    'unreviewed-category'   => '分類:',
    'unreviewed-diff'       => '更改',
    'unreviewed-list'       => '這一頁列示出還未複審或視察的文章修訂。',
);

$RevisionreviewMessages['zh'] = $RevisionreviewMessages['zh-hans'];
$RevisionreviewMessages['zh-cn'] = $RevisionreviewMessages['zh-hans'];
$RevisionreviewMessages['zh-hk'] = $RevisionreviewMessages['zh-hant'];
$RevisionreviewMessages['zh-sg'] = $RevisionreviewMessages['zh-hans'];
$RevisionreviewMessages['zh-tw'] = $RevisionreviewMessages['zh-hant'];
$RevisionreviewMessages['zh-yue'] = $RevisionreviewMessages['yue'];
