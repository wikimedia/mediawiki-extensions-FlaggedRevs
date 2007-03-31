<?php

/**
 * Special page to allow local bureaucrats to grant/revoke the reviewer flag
 * for a particular user
 *
 * @addtogroup Extensions
 * Tiny modifications by Aaron Schulz to MakeBot
 * MakeBot extension:
 ** @author Rob Church <robchur@gmail.com>
 ** @copyright © 2006 Rob Church
 ** @licence GNU General Public Licence 2.0 or later
 */

if( defined( 'MEDIAWIKI' ) ) {

	define( 'MW_MAKEVALIDATE_GRANT_GRANT', 1 );
	define( 'MW_MAKEVALIDATE_REVOKE_GRANT', 2 );
	define( 'MW_MAKEVALIDATE_REVOKE_REVOKE', 3 );
	define( 'MW_MAKEVALIDATE_GRANT_REVOKE', 4 );
	
	$wgExtensionFunctions[] = 'efMakevalidate';
	$wgAvailableRights[] = 'makereview';
	$wgAvailableRights[] = 'makevalidate';
	
	/**
	 * Determines who can use the extension; as a default, bureaucrats are permitted
	 */
	# Basic rights for sysop
	$wgGroupPermissions['sysop']['makereview'] = true;
	# Extra ones for bureaucrats
	# Add UI page rights just in case we have non-sysop bcrats
	$wgGroupPermissions['bureaucrat']['makereview'] = true;
	$wgGroupPermissions['bureaucrat']['makevalidate'] = true;
	
	/**
	 * Toggles whether or not a bot flag can be given to a user who is also a sysop or bureaucrat
	 */
	$wgMakeBotPrivileged = false;
	
	/**
	 * Register the special page
	 */
	$wgAutoloadClasses['Makevalidate'] = dirname( __FILE__ ) . '/Makevalidate.class.php';
	$wgSpecialPages['Makevalidate'] = 'Makevalidate';
	
	/**
	 * Populate the message cache and set up the auditing
	 */
	function efMakeValidate() {
		global $wgMessageCache, $wgLogTypes, $wgLogNames, $wgLogHeaders, $wgLogActions;
		require_once( dirname( __FILE__ ) . '/Makevalidate.i18n.php' );
		foreach( efMakeValidateMessages() as $lang => $messages )
			$wgMessageCache->addMessages( $messages, $lang );
		$wgLogTypes[] = 'validate';
		$wgLogNames['validate'] = 'makevalidate-logpage';
		$wgLogHeaders['validate'] = 'makevalidate-logpagetext';
		$wgLogActions['validate/grantE']  = 'makevalidate-logentrygrant-e';
		$wgLogActions['validate/revokeE'] = 'makevalidate-logentryrevoke-e';
		$wgLogActions['validate/grantR']  = 'makevalidate-logentrygrant-r';
		$wgLogActions['validate/revokeR'] = 'makevalidate-logentryrevoke-r';
	}

} else {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	exit( 1 );
}

?>
