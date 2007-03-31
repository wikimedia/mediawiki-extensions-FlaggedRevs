<?php

global $IP;
require_once( "$IP/includes/LogPage.php" );
require_once( "$IP/includes/SpecialLog.php" );

class MakeValidate extends SpecialPage {

	var $target = '';

	/**
	 * Constructor
	 */
	function MakeValidate() {
		SpecialPage::SpecialPage( 'Makevalidate', 'makereview' );
	}
	
	/**
	 * Main execution function
	 * @param $par Parameters passed to the page
	 */
	function execute( $par ) {
		global $wgRequest, $wgOut, $wgmakevalidatePrivileged, $wgUser;
		
		if( !$wgUser->isAllowed( 'makereview' ) ) {
			$wgOut->permissionRequired( 'makereview' );
			return;
		}
		
		$this->setHeaders();

		$this->target = $par
						? $par
						: $wgRequest->getText( 'username', '' );

		$wgOut->addWikiText( wfMsgNoTrans( 'makevalidate-header' ) );
		$wgOut->addHtml( $this->makeSearchForm() );
		
		if( $this->target != '' ) {
			$wgOut->addHtml( wfElement( 'p', NULL, NULL ) );
			$user = User::newFromName( $this->target );
			if( is_object( $user ) && !is_null( $user ) ) {
				global $wgVersion;
				if( version_compare( $wgVersion, '1.9alpha' ) < 0 ) {
					$user->loadFromDatabase();
				} else {
					$user->load();
				}
				# Valid username, check existence
				if( $user->getID() ) {
						if( $wgRequest->getCheck( 'dosearch' ) || !$wgRequest->wasPosted() || !$wgUser->matchEditToken( $wgRequest->getVal( 'token' ), 'makevalidate' ) ) {
							# Exists, check editor & reviewer status
							# We never just assigned reviewer status alone
							if( in_array( 'editor', $user->mGroups ) && in_array( 'reviewer', $user->mGroups ) ) {
								# Has a reviewer flag
								$wgOut->addWikiText( wfMsg( 'makevalidate-iseditor', $user->getName() ) );
								$wgOut->addWikiText( wfMsg( 'makevalidate-isvalidator', $user->getName() ) );
								$wgOut->addHtml( $this->makeGrantForm( MW_MAKEVALIDATE_REVOKE_REVOKE ) );
							} else if( in_array( 'editor', $user->mGroups ) ) {
								# Has a editor flag
								$wgOut->addWikiText( wfMsg( 'makevalidate-iseditor', $user->getName() ) );
								$wgOut->addHtml( $this->makeGrantForm( MW_MAKEVALIDATE_REVOKE_GRANT ) );
							} else if( in_array( 'reviewer', $user->mGroups ) ) {
								# This shouldn't happen...
								$wgOut->addHtml( $this->makeGrantForm( MW_MAKEVALIDATE_GRANT_REVOKE ) );
							} else {
								# Not a reviewer; show the grant form
								$wgOut->addHtml( $this->makeGrantForm( MW_MAKEVALIDATE_GRANT_GRANT ) );
							}
						} elseif( $wgRequest->getCheck( 'grantR' ) ) {
							# Permission check
							if( !$wgUser->isAllowed( 'makevalidate' ) ) {
								$wgOut->permissionRequired( 'makevalidate' ); return;
							}
							# Grant the flag
							if( !in_array( 'editor', $user->mGroups ) )
								$user->addGroup( 'editor' );
							# All reviewers are editors too
							if( !in_array( 'reviewer', $user->mGroups ) )
								$user->addGroup( 'reviewer' );
							$this->addLogItem( 'grantR', $user, trim( $wgRequest->getText( 'comment' ) ) );
							$wgOut->addWikiText( wfMsg( 'makevalidate-granted-r', $user->getName() ) );
						} elseif( $wgRequest->getCheck( 'revokeR' ) ) {
							# Permission check
							if( !$wgUser->isAllowed( 'makevalidate' ) ) {
								$wgOut->permissionRequired( 'makevalidate' ); return;
							}
							# Revoke the flag
							if ( in_array( 'reviewer', $user->mGroups ) )
								$user->removeGroup( 'reviewer' );
							$this->addLogItem( 'revokeR', $user, trim( $wgRequest->getText( 'comment' ) ) );
							$wgOut->addWikiText( wfMsg( 'makevalidate-revoked-r', $user->getName() ) );
						} elseif( $wgRequest->getCheck( 'grantE' ) ) {
							# Grant the flag
							if( !in_array( 'editor', $user->mGroups ) )
								$user->addGroup( 'editor' );
							$this->addLogItem( 'grantE', $user, trim( $wgRequest->getText( 'comment' ) ) );
							$wgOut->addWikiText( wfMsg( 'makevalidate-granted-e', $user->getName() ) );
						} elseif( $wgRequest->getCheck( 'revokeE' ) ) {
							if( in_array( 'reviewer', $user->mGroups ) ) {
								# Permission check
								if( !$wgUser->isAllowed( 'makevalidate' ) ) {
									$wgOut->permissionRequired( 'makevalidate' ); return;
								}
								# Reviewer flag falls of too
								$user->removeGroup( 'reviewer' );
							} else if( in_array( 'editor', $user->mGroups ) ) {
								# Revoke the flag
								$user->removeGroup( 'editor' );
							}
							$this->addLogItem( 'revokeE', $user, trim( $wgRequest->getText( 'comment' ) ) );
							$wgOut->addWikiText( wfMsg( 'makevalidate-revoked-e', $user->getName() ) );
						}
						# Show log entries
						$this->showLogEntries( $user );
				} else {
					# Doesn't exist
					$wgOut->addWikiText( wfMsg( 'nosuchusershort', htmlspecialchars( $this->target ) ) );
				}
			} else {
				# Invalid username
				$wgOut->addWikiText( wfMsg( 'noname' ) );
			}
		}
		
	}
	
	/**
	 * Produce a form to allow for entering a username
	 * @return string
	 */
	function makeSearchForm() {
		$thisTitle = Title::makeTitle( NS_SPECIAL, $this->getName() );
		$form  = wfOpenElement( 'form', array( 'method' => 'post', 'action' => $thisTitle->getLocalUrl() ) );
		$form .= wfElement( 'label', array( 'for' => 'username' ), wfMsg( 'makevalidate-username' ) ) . ' ';
		$form .= wfElement( 'input', array( 'type' => 'text', 'name' => 'username', 'id' => 'username', 'value' => $this->target ) ) . ' ';
		$form .= wfElement( 'input', array( 'type' => 'submit', 'name' => 'dosearch', 'value' => wfMsg( 'makevalidate-search' ) ) );
		$form .= wfCloseElement( 'form' );
		return $form;
	}
	
	/**
	 * Produce a form to allow granting or revocation of the flag
	 * @param $type Either MW_makevalidate_GRANT or MW_makevalidate_REVOKE
	 *				where the trailing name refers to what's enabled
	 * @return string
	 */
	function makeGrantForm( $type ) {
		global $wgUser;
		$thisTitle = Title::makeTitle( NS_SPECIAL, $this->getName() );
		if( $type == MW_MAKEVALIDATE_GRANT_GRANT ) {
			$grantE = true; $revokeE = false;
			$grantR = true; $revokeR = false;
		} else if ( $type == MW_MAKEVALIDATE_REVOKE_GRANT ) {
			$grantE = false; $revokeE = true;
			$grantR = true; $revokeR = false;
		} else if ( $type == MW_MAKEVALIDATE_REVOKE_REVOKE ) {
			$grantE = false; $revokeE = true;
			$grantR = false; $revokeR = true;
		} else {
		// OK, this one should never happen
			$grantE = true; $revokeE = false;
			$grantR = false; $revokeR = true;
		}
	
		# Start the table
		$form  = wfOpenElement( 'form', array( 'method' => 'post', 'action' => $thisTitle->getLocalUrl() ) );
		$form .= wfOpenElement( 'table' ) . wfOpenElement( 'tr' );
		# Grant/revoke buttons
		$form .= wfElement( 'td', array( 'align' => 'right' ), wfMsg( 'makevalidate-change-e' ) );
		$form .= wfOpenElement( 'td' );
		foreach( array( 'grantE', 'revokeE' ) as $button ) {
			$attribs = array( 'type' => 'submit', 'name' => $button, 'value' => wfMsg( 'makevalidate-' . $button ) );
			if( !$$button )
				$attribs['disabled'] = 'disabled';
			$form .= wfElement( 'input', $attribs );
		}
		$form .= wfCloseElement( 'td' ) . wfCloseElement( 'tr' );
		// Check permissions
		if ( $wgUser->isAllowed('makevalidate') ) {
			$form .= wfElement( 'td', array( 'align' => 'right' ), wfMsg( 'makevalidate-change-r' ) );
			$form .= wfOpenElement( 'td' );
			foreach( array( 'grantR', 'revokeR' ) as $button ) {
				$attribs = array( 'type' => 'submit', 'name' => $button, 'value' => wfMsg( 'makevalidate-' . $button ) );
				if( !$$button )
					$attribs['disabled'] = 'disabled';
				$form .= wfElement( 'input', $attribs );
			}
			$form .= wfCloseElement( 'td' ) . wfCloseElement( 'tr' );
		}
		# Comment field
		$form .= wfOpenElement( 'td', array( 'align' => 'right' ) );
		$form .= wfElement( 'label', array( 'for' => 'comment' ), wfMsg( 'makevalidate-comment' ) );
		$form .= wfOpenElement( 'td' );
		$form .= wfElement( 'input', array( 'type' => 'text', 'name' => 'comment', 'id' => 'comment', 'size' => 45 ) );
		$form .= wfCloseElement( 'td' ) . wfCloseElement( 'tr' );
		# End table
		$form .= wfCloseElement( 'table' );
		# Username
		$form .= wfElement( 'input', array( 'type' => 'hidden', 'name' => 'username', 'value' => $this->target ) );
		# Edit token
		$form .= wfElement( 'input', array( 'type' => 'hidden', 'name' => 'token', 'value' => $wgUser->editToken( 'makevalidate' ) ) );
		$form .= wfCloseElement( 'form' );
		return $form;
	}

	/**
	 * Add logging entries for the specified action
	 * @param $type Either grant or revoke
	 * @param $target User receiving the action
	 * @param $comment Comment for the log item
	 */
	function addLogItem( $type, &$target, $comment = '' ) {
		$log = new LogPage( 'validate' );
		$targetPage = $target->getUserPage();
		$log->addEntry( $type, $targetPage, $comment );
	}
	
	/**
	 * Show the bot status log entries for the specified user
	 * @param $user User to show the log for
	 */
	function showLogEntries( &$user ) {
		global $wgOut;
		$title = $user->getUserPage();
		$wgOut->addHtml( wfElement( 'h2', NULL, htmlspecialchars( LogPage::logName( 'validate' ) ) ) );
		$logViewer = new LogViewer( new LogReader( new FauxRequest( array( 'page' => $title->getPrefixedText(), 'type' => 'validate' ) ) ) );
		$logViewer->showList( $wgOut );
	}

}

?>
