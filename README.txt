Complete online documenation: 
http://www.mediawiki.org/wiki/Extension:FlaggedRevs

== Setup ==
* Download the extension from SVN
* Upgrade to MediaWiki 1.14+
* Run 'maintenance/update.php' if you needed to upgrade
* Run 'maintenance/archives/populateSha1.php'.
* Make sure '../extensions/FlaggedRevs' is readable (for CSS/JS)
* Add the following line to 'LocalSettings.php': 
	include_once('extensions/FlaggedRevs/FlaggedRevs.php');
* Run 'maintenance/update.php'

It is important that the sha1 column is populated. This allows for image injection via key 
rather than the (name,timestamp) pair. In the future, image moves may be supported by MediaWiki, 
breaking the later method.

Be sure to set the $wgReviewCodes variable as well. See FlaggedRevs.php for details.

== Configuration ==
There is a commented list of configurable variables in FlaggedRevs.php. The online documentation
expains these further.

== Uninstallation ==
* Remove the FlaggedRevs include line from LocalSettings.php.
* Run maintenance/refreshLinks.php from the command line to flush out the stable version links.
* Drop the tables in FlaggedRevs.sql to free up disk space. You can use the following query:

	-- Replace /*_*/ with the proper DB prefix
	DROP TABLE IF EXISTS /*_*/flaggedpages;
	DROP TABLE IF EXISTS /*_*/flaggedpage_pending;
	DROP TABLE IF EXISTS /*_*/flaggedrevs;
	DROP TABLE IF EXISTS/*_*/flaggedtemplates;
	DROP TABLE IF EXISTS /*_*/flaggedimages;
	DROP TABLE IF EXISTS /*_*/flaggedpage_config;
	DROP TABLE IF EXISTS /*_*/flaggedrevs_tracking;
	DROP TABLE IF EXISTS /*_*/flaggedrevs_promote;

* If they exist, drop the columns 'page_ext_reviewed', 'page_ext_quality', 'page_ext_stable', 
and the index 'ext_namespace_reviewed' from the page table. You can use the following query:

	-- Replace /*_*/ with the proper DB prefix
	ALTER TABLE /*_*/page DROP INDEX ext_namespace_reviewed;
	ALTER TABLE /*_*/page DROP COLUMN page_ext_reviewed, DROP COLUMN page_ext_quality, DROP COLUMN page_ext_stable;

== Licensing ==
© GPL, Aaron Schulz, Joerg Baach, 2007


