<?php
/**
 * (c) Aaron Schulz, Joerg Baach, 2007-2008 GPL
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

# Stable constant to let extensions be aware that this is enabled
define( 'FLAGGED_REVISIONS', true );

# Load global constants
require __DIR__ . '/FlaggedRevs.defines.php';

// Load stuff already converted to extension registration.
wfLoadExtension( 'Flagged Revisions', __DIR__ . '/extension-wip.json' );

/**
 * This function is for setup that has to happen in Setup.php
 * when the functions in $wgExtensionFunctions get executed.
 * Note: avoid calls to FlaggedRevs class here for performance.
 * @return void
 */
$wgExtensionFunctions[] = function () {
	# LocalSettings.php loaded, safe to load config
	FlaggedRevsSetup::setReady();

	# Conditional autopromote groups
	FlaggedRevsSetup::setAutopromoteConfig();

	# Register special pages (some are conditional)
	FlaggedRevsSetup::setSpecialPageCacheUpdates();
	# Conditional API modules
	FlaggedRevsSetup::setAPIModules();
	# Load hooks that aren't always set
	FlaggedRevsSetup::setConditionalHooks();
	# Remove conditionally applicable rights
	FlaggedRevsSetup::setConditionalRights();
	# Defaults for user preferences
	FlaggedRevsSetup::setConditionalPreferences();
};
