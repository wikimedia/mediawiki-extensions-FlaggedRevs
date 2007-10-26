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
	# Basic rights for Sysops
	$wgGroupPermissions['sysop']['makereview'] = true;
	$wgGroupPermissions['sysop']['removereview'] = true;
	# Extra ones for Bureaucrats
	# Add UI page rights just in case we have non-sysop bcrats
	$wgGroupPermissions['bureaucrat']['makereview'] = true;
	$wgGroupPermissions['bureaucrat']['removereview'] = true;
	$wgGroupPermissions['bureaucrat']['makevalidate'] = true;
	# We may want a sort of invitational community system...
	# $wgGroupPermissions['editor']['makereview'] = true;
	# $wgGroupPermissions['reviewer']['makereview'] = true;
	
	/**
	 * Register the special page
	 */
	$wgSpecialPages['Makevalidate'] = 'MakeValidate';
	$wgAutoloadClasses['MakeValidate'] = dirname(__FILE__) . '/Makevalidate_body.php';
	
	/**
	 * Populate the message cache
	 */
	function efMakeValidate() {
		global $wgMessageCache, $wgLang, $MakevalidateMessages;
		# Internationalization
		$MakevalidateMessages = array();
		// Default to English langauge
		$f = dirname( __FILE__ ) . '/Language/Makevalidate.i18n.en.php';
		include( $f );
		
		$f = dirname( __FILE__ ) . '/Language/Makevalidate.i18n.' . $wgLang->getCode() . '.php';
		if( file_exists( $f ) ) {
			include( $f );
		}
		foreach( $MakevalidateMessages as $lang => $messages ) {
			$wgMessageCache->addMessages( $messages, $lang );
		}
	}

} else {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	exit( 1 );
}


