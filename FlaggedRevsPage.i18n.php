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
	'revreview-oldrating' => 'It was rated as:',
	'revreview-noflagged' => 'There are no reviewed revisions of this page, so it may \'\'\'not\'\'\' have been 
	[[Help:Article validation|checked]] for quality.',
	
	'revreview-quick-none' => '\'\'\'Current\'\'\'. No reviewed revisions.',
	'revreview-quick-see-quality' => '\'\'\'Current\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} See latest quality revision]]',
	'revreview-quick-see-basic' => '\'\'\'Current\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=1}} See latest sighted revision]]',
	'revreview-quick-basic'  => '\'\'\'[[Help:Article validation|Sighted]]\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} See current revision]] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|change|changes}}])',
	'revreview-quick-quality' => '\'\'\'[[Help:Article validation|Quality]]\'\'\'. [[{{fullurl:{{FULLPAGENAMEE}}|stable=0}} See current revision]] 
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
	'revreview-static'  => 'This is a [[Help:Article validation|reviewed]] revision of \'\'\'[[:$3|this page]]\'\'\', 
	[{{fullurl:Special:Log/review|page=$1}} approved] on <i>$2</i>. The [{{fullurl:$3|stable=0}} current revision] 
	can be [{{fullurl:$3|action=edit}} modified].',
	'revreview-toggle' => '(+/-)',
	'revreview-note' => '[[User:$1]] made the following notes [[Help:Article validation|reviewing]] this revision:',
	'revreview-update' => 'Please review any changes (shown below) made since stable revision to this page. Templates and images
	may have also changed.',
	'revreview-auto' => '(automatic)',
	'revreview-auto-w' => "'''Note:''' you are making changes to the stable revision, your edits will automatically be reviewed. 
	You may want to preview the page before saving.",

	'hist-stable'  => '[sighted]',
	'hist-quality' => '[quality]',

    'flaggedrevs'        => 'Flagged Revisions',
    'review-logpage'     => 'Article review log',
	'review-logpagetext' => 'This is a log of changes to revisions\' [[Help:Article validation|approval]] status
	for content pages.',
	'review-logentrygrant'   => 'reviewed $1',
	'review-logentryrevoke'  => 'depreciated a version of $1',
	'review-logaction'  => 'revision ID $1',

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
	'stableversions-leg2'   => 'View a reviewed revision',
	'stableversions-page'   => 'Page name',
	'stableversions-rev'    => 'Revision ID',
	'stableversions-none'   => '[[:$1]] has no reviewed revisions.',
	'stableversions-list'   => 'The following is a list of revisions of [[:$1]] that have been reviewed:',
	'stableversions-review' => 'Reviewed on <i>$1</i>',

    'review-diff2stable'    => 'Diff to the last stable revision',
    'review-diff2oldest'    => "Diff to the oldest revision",

    'unreviewedpages'       => 'Unreviewed pages',
    'viewunreviewed'        => 'List unreviewed content pages',
    'unreviewed-outdated'   => 'Show only pages that have unreviewed revisions made to the stable version.',
    'unreviewed-category'   => 'Category:',
    'unreviewed-diff'       => 'Changes',
    'unreviewed-list'       => 'This page lists articles that have not yet been reviewed or have unreviewed revisions.',
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
	'stableversions-leg2'   => 'عرض نسخة مراجعة',
	'stableversions-page'   => 'اسم الصفحة',
	'stableversions-rev'    => 'رقم النسخة',
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
	'revreview-noflagged' => 'Von dieser Seite gibt es keine geprüften Versionen, so dass noch keine Aussage über die [[Help:Article validation|Artikelqualität]]
	gemacht werden kann.',

	'revreview-quick-see-quality' => "'''Aktuell.''' [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Siehe die letzte geprüfte Version]",
	'revreview-quick-see-basic'   => "'''Aktuell.''' [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Siehe die letzte gesichtete Version]",
	'revreview-quick-basic'       => "'''[[Help:Article validation|Gesichtet.]]''' [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Siehe die aktuelle Version] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|Änderung|Änderungen}}])",
	'revreview-quick-quality'     => "'''[[Help:Article validation|Geprüft.]]''' [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Siehe die aktuelle Version] ($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|Änderung|Änderungen}}])",
	'revreview-quick-none'        => "'''Aktuell.'''. Es wurde noch keine Version gesichtet.",

	'revreview-newest-basic' => 'Die [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} letzte gesichtete Version]
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} siehe alle]) wurde am <i>$2</i> [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben].
	[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|Version|Versionen}}] {{plural:$3|steht|stehen}} noch zur Prüfung an.',

	'revreview-newest-quality' => 'Die [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} letzte geprüfte Version]
	([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} siehe alle]) wurde am <i>$2</i> [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben]
	[{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|Version|Versionen}}] {{plural:$3|steht|stehen}} noch zur Prüfung an.',

	'revreview-basic'  => 'Dies ist die letzte [[Help:Gesichtete Versionen|gesichtete]] Version,
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben] am <i>$2</i>. Die [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} derzeitige Version]
	kann [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} bearbeitet] werden; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|Version|Versionen}}]
	{{plural:$3|steht|stehen}} noch zur Prüfung an.',

	'revreview-quality'  => 'This is the latest [[Help:Article validation|quality]] revision, 
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} approved] on <i>$2</i>. The [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} current revision] 
	can be [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} modified]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|change|changes}}] 
	{{plural:$3|awaits|await}} review.',

	'revreview-quality'  => 'Das ist die letzte Version mit [[Help:Versionsbewertung|Qualitätsbewertung]],
	[{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} freigegeben] am <i>$2</i>. Die [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} derzeitige Version]
	kann [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} bearbeitet] werden; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|Version|Versionen}}]
	{{plural:$3|steht|stehen}} noch zur Prüfung an.',

	'revreview-static' => "Dies ist eine [[Help:Geprüfte Versionen|geprüfte]] Version '''[[:$3|dieser Seite]]''', [{{fullurl:Special:Log/review|page=$1}} freigegeben]
	am <i>$2</i>. Die [{{fullurl:$3|stable=0}} derzeitige Version] kann [{{fullurl:$3|action=edit}} bearbeitet] werden.",

	'revreview-toggle' => '(+/-)',
	'revreview-note'   => '[[{{ns:user}}:$1]] machte die folgende [[Help:Article validation|Prüfnotiz]] zu dieser Version:',
	'revreview-update' => 'Bitte prüfe jede Änderung seit der letzten stabilen Version (siehe unten).
	Vorlagen und Bilder können sich ebenfalls geändert haben.',
	'revreview-auto'   => '(automatisch)',
	'revreview-auto-w' => "'''Hinweis:''' Du nimmst eine Bearbeitung an einer stabilen Version vor, daher wird deine eigene Bearbeitung automatisch als geprüft markiert. 
	Du solltest die Seite daher vor dem Speichern in der Vorschau betrachten.",

	'hist-stable'  => '[gesichtet]',
	'hist-quality' => '[geprüft]',

	'flaggedrevs'           => 'Markierte Versionen',
	'review-logpage'        => 'Artikel-Prüf-Logbuch',
	'review-logpagetext'    => 'Dies ist das Änderungs-Logbuch der [[Help:Article validation|Seiten-Freigaben]].',
	'review-logentrygrant'  => 'prüfte $1',
	'review-logentryrevoke' => 'verwarf eine Version von $1',
	'review-logaction'      => 'Version-ID $1',

	'revisionreview'       => 'Versionsprüfung',
	'revreview-main'       => 'Sie müssen eine Artikelversion zur Prüfung auswählen.

	Siehe [[{{ns:special}}:Unreviewedpages]] für eine Liste ungeprüfter Versionen.',	
	'revreview-selected'   => "Gewählte Version von '''$1:'''",
	'revreview-text'       => "Einer stabilen Version wird bei der Seitendarstellung der Vorzug vor einer neueren Version gegeben.",
	'revreview-toolow'     => 'Sie müssen für jedes der untenstehenden Attribute einen Wert höher als „{{int:revreview-accuracy-0}}“ einstellen,
	damit eine Version als geprüft gilt. Um eine Version zu verwerfen, müssen alle Attribute auf „{{int:revreview-accuracy-0}}“ stehen.',
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
	'stableversions-leg1'   => 'Liste der geprüften Versionen für einen Artikel',
	'stableversions-leg2'   => 'Zeige eine geprüfte Version',
	'stableversions-page'   => 'Artikelname',
	'stableversions-rev'    => 'Versionsnummer:',
	'stableversions-none'   => '[[:$1]] hat keine geprüften Versionen.',
	'stableversions-list'   => 'Dies ist die Liste der geprüften Versionen von [[:$1]]:',
	'stableversions-review' => 'geprüft am <i>$1</i>',

	'review-diff2stable'    => 'Unterschied zur letzten stabilen Version',
	'review-diff2oldest'    => "Unterschied zur ältesten Version",

	'unreviewedpages'       => 'Ungeprüfte Artikel',
	'viewunreviewed'        => 'Liste ungeprüfter Artikel',
	'unreviewed-outdated'   => 'Zeige nur Seiten, die ungeprüfte Version nach einer stabilen Version haben.',
	'unreviewed-category'   => 'Kategorie:',
	'unreviewed-diff'       => 'Änderungen',
	'unreviewed-list'       => 'Diese Seite zeigt Artikel, die noch gar nicht geprüft wurden oder ungeprüfte Versionen haben.',
);

$RevisionreviewMessages['nl'] = array(
	'makevalidate-autosum'  => 'automatisch gepromoveerd',
	'editor'                => 'Redacteur',
	'group-editor'          => 'Redacteuren',
	'group-editor-member'   => 'Redacteur',
	'grouppage-editor'      => '{{ns:project}}:Redacteur',
	'revreview-current'     => 'Huidige versie',
	'revreview-edit'        => 'Concept bewerken',
	'revreview-source'      => 'Brontekst concept',
	'revreview-stable'      => 'Stabiele versie',
	'revreview-oldrating'   => 'Was gewaardeerd als:',
	'revreview-noflagged'   => 'Er zijn geen gereviewde versies van deze pagina, dus die is wellicht \'\'\'niet\'\'\' 
	[[Help:Article validation|gecontroleerd]] op kwaliteit.',
	'revreview-quick-see-quality' => '\'\'\'Huidige versie\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Bekijk laatste kwaliteitsversie]',
	'revreview-quick-see-basic' => '\'\'\'Huidige versie\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Bekijk laatste bekeken versie]',
	'revreview-quick-basic' => '\'\'\'Bekeken versie\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Bekijk huidige versie] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|bewerking|bewerkingen}}])',
	'revreview-quick-quality' => '\'\'\'Kwaliteitsversie\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Bekijk huidige versie] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|bewerking|bewerkingen}}])',
	'revreview-quick-none'  => '\'\'\'Huidige versie\'\'\'. Geen gereviewde versies.',
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
	'stableversions-leg2'   => 'Bekijk een gereviewde versie',
	'stableversions-page'   => 'Paginanaam',
	'stableversions-rev'    => 'Versienummer',
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
	'revreview-current'     => 'Naćisk',
	'revreview-edit'        => 'Naćisk wobdźěłać',
	'revreview-source'      => 'Žórło naćiska',
	'revreview-stable'      => 'Stabilna wersija',
	'revreview-auto'        => '(awtomatisce)',
	'hist-quality'          => '[pruwowany]',
	'review-logaction'      => 'Wersijowy ID $1',
	'revisionreview'        => 'Wersije přepruwować',
	'revreview-selected'    => 'Wubrana wersija z \'\'\'$1:\'\'\'',
	'revreview-accuracy-3'  => 'Žórła přepruwowane',
	'revreview-depth'       => 'Hłubokosć',
	'revreview-depth-1'     => 'jednory',
	'revreview-depth-2'     => 'srěni',
	'revreview-depth-3'     => 'wysoki',
	'revreview-style-2'     => 'dobry',
	'revreview-style-3'     => 'skrótka',
	'revreview-log'         => 'Protokolowy zapisk:',
	'revreview-submit'      => 'Přepruwowanje składować',
	'stableversions'        => 'Stabilne wersije',
	'stableversions-leg1'   => 'Přepruwowane wersije za nastawk nalistować',
	'stableversions-leg2'   => 'Přepruwowanu wersiju pokazać',
	'stableversions-page'   => 'Mjeno nastawka',
	'stableversions-rev'    => 'Wersijowy ID:',
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
	'stableversions-leg2'   => 'Vis en godkjent revisjon',
	'stableversions-page'   => 'Sidenavn',
	'stableversions-rev'    => 'Revisjons-ID',
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
	'stableversions-leg2'   => 'Vardé na version revisionà',
	'stableversions-page'   => 'Nòm dla pàgina',
	'stableversions-rev'    => 'Nùmer ëd version',
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
	'revreview-current'     => 'Edição atual',
	'revreview-stable'      => 'Edição estável',
	'revreview-oldrating'   => 'Esteve avaliada como:',
	'revreview-noflagged'   => 'Não há edições críticas para esta página; talvez ainda \'\'\'não\'\'\' tenha sido [[{{ns:help}}:Validação de páginas|verificada]] a sua qualidade.',
	'revreview-quick-see-quality' => '\'\'\'Atual\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Veja a edição estável mais recente]',
	'revreview-quick-see-basic' => '\'\'\'Atual\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Veja a edição crítica mais recente]',
	'revreview-quick-basic' => '\'\'\'Crítica\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Veja a edição atual] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|alteração|alterações}}])',
	'revreview-quick-quality' => '\'\'\'Estável\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Veja a edição atual] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} {{plural:$2|alteração|alterações}}])',
	'revreview-quick-none'  => '\'\'\'Crítica\'\'\'. Não há edições críticas.',
	'revreview-newest-basic' => 'A [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} mais recente edição crítica] ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} listar todas]) foi [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovada]
	 em <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|alteração|alterações}}] {{plural:$3|necessita|necessitam}} análise.',
	'revreview-newest-quality' => 'A [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} mais recente edição crítica] ([{{fullurl:Special:Stableversions|page={{FULLPAGENAMEE}}}} listar todas]) foi [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovada] em <i>$2</i>. [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|alteração|alterações}}] {{plural:$3|necessita|necessitam}} análise.',
	'revreview-basic'       => 'Esta é a mais recente edição [[{{ns:help}}:Validação de páginas|crítica]] desta página, [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovada] em <i>$2</i>. É possível [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} editar] a [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} versão atual]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|alteração|alterações}}] {{plural:$3|aguarda|aguardam}} revisão.',
	'revreview-quality'     => 'Esta é a mais recente edição [[{{ns:help}}:Validação de páginas|estável]], [{{fullurl:Special:Log|type=review&page={{FULLPAGENAMEE}}}} aprovada] em <i>$2</i>. É possível [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} editar] a [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} versão atual]; [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur&editreview=1}} $3 {{plural:$3|alteração|alterações}}] {{plural:$3|aguarda|aguardam}} revisão.',
	'revreview-static'      => 'Esta é uma edição [[{{ns:help}}:Validação de páginas|crítica]] \'\'\'[[:$3|desta página]]\'\'\', [{{fullurl:Special:Log/review|page=$1}} aprovada] em <i>$2</i>. É possível [{{fullurl:{{FULLPAGENAMEE}}|action=edit}} editar] a [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} versão atual].',
	'revreview-note'        => '[[{{ns:user}}:$1|$1]] deixou as seguintes observações ao [[{{ns:help}}:Validação de páginas|criticar]] esta edição:',
	'revreview-update'      => 'Por gentileza, analise todas as alterações exibidas a seguir, feitas desde a última edição estável desta página. Talvez as predefinições e imagens utilizadas possam ter sido também alteradas.',
	'revreview-auto'        => '(automático)',
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
	'revreview-flag'        => 'Critique esta edição (#$1):',
	'revreview-legend'      => 'Avaliar conteúdo da edição:',
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
	'stableversions-leg2'   => 'Ver uma edição crítica',
	'stableversions-page'   => 'Título da página',
	'stableversions-rev'    => 'ID da edição',
	'stableversions-none'   => '[[:$1]] não possui edições críticas.',
	'stableversions-list'   => 'A seguir, uma lista das edições de [[:$1]] que são edições críticas:',
	'stableversions-review' => 'Criticada em <i>$1</i>',
	'review-diff2stable'    => 'Comparar com a edição crítica mais recente',
	'review-diff2oldest'    => 'Comparar com a edição mais antiga',
	'unreviewedpages'       => 'Páginas sem edições críticas',
	'viewunreviewed'        => 'Listar páginas de conteúdo que ainda não possuam uma edição crítica',
	'included-nonquality'   => 'Exibir apenas as páginas analisadas que não tenham sido marcadas como estáveis.',
	'unreviewed-list'       => 'Esta página lista as páginas de conteúdo que ainda não receberam uma edição crítica.',
	'rights-editor-revoke'  => 'foi removido o estado de editor de [[$1]]',
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
	'revreview-stable'      => 'Stabilná verzia',
	'revreview-oldrating'   => 'Bolo ohodnotené ako:',
	'revreview-noflagged'   => 'Neexistujú revidované verzie tejto stránky, takže jej
	kvalita \'\'\'nebola\'\'\' [[Help:Revízia článkov|skontrolovaná]].',
	'revreview-quick-see-quality' => '\'\'\'Aktuálna\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Pozri poslednú kvalitnú revíziu]',
	'revreview-quick-see-basic' => '\'\'\'Aktuálna\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=1}} Pozri poslednú skontrolovanú revíziu]',
	'revreview-quick-basic' => '\'\'\'Skontrolovaná\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Pozri aktuálnu revíziu] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|zmena|zmeny|zmien}}])',
	'revreview-quick-quality' => '\'\'\'Kvalitná\'\'\'. [{{fullurl:{{FULLPAGENAMEE}}|stable=0}} Pozri aktuálnu revíziu] 
	($2 [{{fullurl:{{FULLPAGENAMEE}}|oldid=$1&diff=cur}} {{plural:$2|zmena|zmeny|zmien}}])',
	'revreview-quick-none'  => '\'\'\'Aktuálna\'\'\'. Žiadne revízie neboli skontrolvoané..',
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
	'stableversions-leg2'   => 'Zobraziť skontrolovanú verziu',
	'stableversions-page'   => 'Názov stránky',
	'stableversions-rev'    => 'ID verzie',
	'stableversions-none'   => '[[:$1]] nemá skontrolované verzie.',
	'stableversions-list'   => 'Nasleduje zoznam verzií stránky [[:$1]], ktoré boli skontrolované:',
	'stableversions-review' => 'Skontrolované <i>$1</i>',
	'review-diff2stable'    => 'Rozdiely oproti poslednej stabilnej verzii',
	'review-diff2oldest'    => 'Rozdiely oproti najstaršej verzii',
	'unreviewedpages'       => 'Neskontrolované stránky',
	'viewunreviewed'        => 'Zoznam neskontrolovaných stránok s obsahom',
	'unreviewed-list'       => 'Táto stránka obsahuje zoznam článkov, ktoré zatiaľ neboli skontrolované.',
);
