<?php

/**
 * Special page to allow local bureaucrats to grant/revoke the reviewer flag
 * for a particular user
 *
 * @addtogroup Extensions
 * Modifications by Aaron Schulz to MakeBot
 * MakeBot extension:
 ** @author Rob Church <robchur@gmail.com>
 ** @copyright © 2006 Rob Church
 ** @licence GNU General Public Licence 2.0 or later
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

define( 'MW_MAKEVALIDATE_GRANT_GRANT', 1 );
define( 'MW_MAKEVALIDATE_REVOKE_GRANT', 2 );
define( 'MW_MAKEVALIDATE_REVOKE_REVOKE', 3 );
define( 'MW_MAKEVALIDATE_GRANT_REVOKE', 4 );
	
$wgExtensionFunctions[] = 'efMakeReviewer';
$wgAvailableRights[] = 'makereviewer';
$wgAvailableRights[] = 'makevalidator';

$wgHooks['UserRights'][] = 'efMakeReviewerDemote';

/**	
* Determines who can use the extension; as a default, bureaucrats are permitted
*/
# Basic rights for Sysops
$wgGroupPermissions['sysop']['makereviewer'] = true;
$wgGroupPermissions['sysop']['removereviewer'] = true;
# Extra ones for Bureaucrats
# Add UI page rights just in case we have non-sysop bcrats
$wgGroupPermissions['bureaucrat']['makereviewer'] = true;
$wgGroupPermissions['bureaucrat']['removereviewer'] = true;
$wgGroupPermissions['bureaucrat']['makevalidator'] = true;
	
/**
 * Register the special page
 */
$wgSpecialPages['MakeReviewer'] = 'MakeReviewer';
$wgAutoloadClasses['MakeReviewer'] = dirname(__FILE__) . '/MakeReviewer_body.php';
	
/**
 * Populate the message cache
 */
function efMakeReviewer() {
	global $wgMessageCache, $wgLang;
	# Internationalization
	$messages = array();
	// Default to English langauge
	$f = dirname( __FILE__ ) . '/Language/MakeReviewer.i18n.en.php';
	include( $f );
	$wgMessageCache->addMessages( $messages, 'en' );
	
	$f = dirname( __FILE__ ) . '/Language/MakeReviewer.i18n.' . $wgLang->getCode() . '.php';
	if( file_exists( $f ) ) {
		include( $f );
	}
	$wgMessageCache->addMessages( $messages, $wgLang->getCode() );
}

function efMakeReviewerDemote( $u, $addgroup, $removegroup ) {
	if( $removegroup && in_array( 'editor', $removegroup ) ) {
		$log = new LogPage( 'rights' );
		$targetPage = $u->getUserPage();
		# Add dummy entry to mark that a user's editor rights
		# were removed. This avoid auto-promotion.
		$log->addEntry( 'erevoke', $targetPage, '', array() );
	}
	return true;
}
