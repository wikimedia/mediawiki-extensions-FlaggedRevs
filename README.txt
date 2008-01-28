Complete online documenation: 
http://www.mediawiki.org/wiki/Extension:FlaggedRevs

== Setup ==
* Download the extension from SVN
* (MySQL) Run the 'FlaggedRevs.sql' query, substituting in your wiki's table prefix. 
* (PostgreSQL) Use 'FlaggedRevs.pg.sql' instead.
* Upgrade to MediaWiki 1.12
* Run 'maintenance/update.php'
* Run 'maintenance/archives/populateSha1.php'.
* Add the following line to 'LocalSettings.php':
	include_once('extensions/FlaggedRevs/FlaggedRevs.php');

It is important that the sha1 column is populated. This allows for image injection via key 
rather than the (name,timestamp) pair. In the future, image moves may be supported by MediaWiki, 
breaking the later method.

== Configuration ==
There is a commented list of configurable variables in FlaggedRevs.php. The online documentation
expains these further.

== Uninstallation ==
* Remove the include line from LocalSettings.php
* Drop the tables in FlaggedRevs.sql. Drop the columns 'page_ext_reviewed' and 'page_ext_stable', and the index 'ext_namespace_reviewed' from the page table.
* Run maintenance/refreshLinks.php from the command line to flush out the stable version links

== Licensing ==
© GPL, Aaron Schulz, Joerg Baach, 2007


