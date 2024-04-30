Complete online documentation:
https://www.mediawiki.org/wiki/Extension:FlaggedRevs

== Prerequisites ==
* Downloaded the extension from Git
* MediaWiki 1.38+
* Shell access

== Setup ==
* Add the following line to 'LocalSettings.php':
	wfLoadExtension( 'FlaggedRevs' );
* Run 'maintenance/update.php' to add the SQL tables
* If you are using $wgFlaggedRevsAutopromote, run FlaggedRevs/maintenance/updateAutoPromote.php.
* To enable article validation statistics, $wgPhpCli must be set correctly. This is not necessary
  if you set a cron job to run FlaggedRevs/maintenance/updateStats.php every so often, which is preferable.

== Configuration ==
* Change settings by adding them to LocalSettings.php.
* The online documentation expains some of these further.

=== Autopromote ===
In 1.34 the autopromote config was removed from the default. If you want to keep the same config
add the following to your LocalSettings.php

<source lang="php">
$wgFlaggedRevsAutopromote = [
	'days'                  => 60, # days since registration
	'edits'                 => 250, # total edit count
	'excludeLastDays'       => 1, # exclude the last X days of edits from below edit counts
	'benchmarks'            => 15, # number of "spread out" edits
	'spacing'               => 3, # number of days between these edits (the "spread")
	'totalContentEdits'     => 300, # edits to pages in $wgContentNamespaces
	'totalCheckedEdits'     => 200, # edits before the stable version of pages
	'uniqueContentPages'    => 14, # unique pages in $wgContentNamespaces edited
	'editComments'          => 50, # number of manual edit summaries used
	'userpageBytes'         => 0, # size of userpage (use 0 to not require a userpage)
	'neverBlocked'          => true, # username was never blocked before?
	'maxRevertedEditRatio'  => 0.03, # max fraction of edits reverted via "rollback"/"undo"
];
</source>

== Uninstallation ==
* Remove the FlaggedRevs wfLoadExtension line from LocalSettings.php.
* Run maintenance/refreshLinks.php from the command line to flush out the stable version links.
* Drop the tables in FlaggedRevs.sql to free up disk space. You can use the following queries:

	-- Replace /*_*/ with the proper DB prefix
	DROP TABLE IF EXISTS /*_*/flaggedpages;
	DROP TABLE IF EXISTS /*_*/flaggedrevs;
	DROP TABLE IF EXISTS /*_*/flaggedpage_config;
	DROP TABLE IF EXISTS /*_*/flaggedrevs_tracking;
	DROP TABLE IF EXISTS /*_*/flaggedrevs_promote;
	DROP TABLE IF EXISTS /*_*/flaggedrevs_statistics;

* If they exist, drop the columns 'page_ext_reviewed', 'page_ext_quality', 'page_ext_stable',
and the index 'ext_namespace_reviewed' from the page table. You can use the following query:

	-- Replace /*_*/ with the proper DB prefix
	ALTER TABLE /*_*/page DROP INDEX ext_namespace_reviewed;
	ALTER TABLE /*_*/page DROP COLUMN page_ext_reviewed, DROP COLUMN page_ext_quality, DROP COLUMN page_ext_stable;

== Licensing ==
© GPL, Aaron Schulz, Joerg Baach, 2007
