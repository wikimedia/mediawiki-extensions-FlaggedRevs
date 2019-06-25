Complete online documenation:
https://www.mediawiki.org/wiki/Extension:FlaggedRevs

==Prerequisites==
* Downloaded the extension from GIT
* MediaWiki 1.34+
* Shell access

== Setup ==
* Run 'maintenance/populateSha1.php' if not already done.
* Add the following line to 'LocalSettings.php':
	wfLoadExtension( 'FlaggedRevs' );
* Run 'maintenance/update.php' to add the SQL tables
* Run FlaggedRevs/maintenance/updateAutoPromote.php.
  You can ignore this if you aren't using $wgFlaggedRevsAutopromote.
* To enable article validation statistics, $wgPhpCli must be set correctly. This is not necessary
  if you set a cron job to run FlaggedRevs/maintenance/updateStats.php every so often, which is preferable.

== Configuration ==
* Change settings by adding them to LocalSettings.php.
* The online documentation expains some of these further.

== Uninstallation ==
* Remove the FlaggedRevs wfLoadExtension line from LocalSettings.php.
* Run maintenance/refreshLinks.php from the command line to flush out the stable version links.
* Drop the tables in FlaggedRevs.sql to free up disk space. You can use the following queries:

	-- Replace /*_*/ with the proper DB prefix
	DROP TABLE IF EXISTS /*_*/flaggedpages;
	DROP TABLE IF EXISTS /*_*/flaggedpage_pending;
	DROP TABLE IF EXISTS /*_*/flaggedrevs;
	DROP TABLE IF EXISTS /*_*/flaggedtemplates;
	DROP TABLE IF EXISTS /*_*/flaggedimages;
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
