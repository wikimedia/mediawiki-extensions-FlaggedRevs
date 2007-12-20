<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "FlaggedRevs extension\n";
	exit( 1 );
}

class MakeReviewer extends SpecialPage {

	var $target = '';

	/**
	 * Constructor
	 */
	function __construct() {
		SpecialPage::SpecialPage( 'MakeReviewer', 'makereviewer' );
	}
	
	/**
	 * Main execution function
	 * @param $par Parameters passed to the page
	 */
	function execute( $par ) {
		global $wgRequest, $wgOut, $wgUser;
		
		if( !$wgUser->isAllowed( 'makereviewer' ) ) {
			$wgOut->permissionRequired( 'makereviewer' );
			return;
		}
		
		$this->setHeaders();

		$this->target = $par ? $par : $wgRequest->getText( 'username', '' );

		$wgOut->addWikiText( wfMsgNoTrans( 'makereviewer-header' ) );
		$wgOut->addHtml( $this->makeSearchForm() );
		
		if( $this->target != '' ) {
			$wgOut->addHtml( Xml::element( 'p', NULL, NULL ) );
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
					$oldgroups = $user->getGroups();
					if( $wgRequest->getCheck( 'dosearch' ) || !$wgRequest->wasPosted() || !$wgUser->matchEditToken( $wgRequest->getVal( 'token' ), 'makereviewer' ) ) {
						# Exists, check editor & reviewer status
						# We never assign reviewer status alone
						if( in_array( 'editor', $user->mGroups ) && in_array( 'reviewer', $user->mGroups ) ) {
							# Has a reviewer flag
							$wgOut->addWikiText( wfMsg( 'makereviewer-iseditor', $user->getName() ) );
							$wgOut->addWikiText( wfMsg( 'makereviewer-isvalidator', $user->getName() ) );
							$wgOut->addHtml( $this->makeGrantForm( MW_MAKEVALIDATE_REVOKE_REVOKE ) );
						} else if( in_array( 'editor', $user->mGroups ) ) {
							# Has a editor flag
							$wgOut->addWikiText( wfMsg( 'makereviewer-iseditor', $user->getName() ) );
							$wgOut->addHtml( $this->makeGrantForm( MW_MAKEVALIDATE_REVOKE_GRANT ) );
						} else if( in_array( 'reviewer', $user->mGroups ) ) {
							# This shouldn't happen...
							$wgOut->addHtml( $this->makeGrantForm( MW_MAKEVALIDATE_GRANT_REVOKE ) );
						} else {
							# Not a reviewer; show the grant form
							$wgOut->addHtml( $this->makeGrantForm( MW_MAKEVALIDATE_GRANT_GRANT ) );
						}
					} elseif( $wgRequest->getCheck( 'grant2' ) ) {
						# Permission check
						if( !$wgUser->isAllowed( 'makevalidator' ) ) {
							$wgOut->permissionRequired( 'makevalidator' ); 
							return;
						}
						# Grant the flag
						if( !in_array( 'reviewer', $user->mGroups ) )
							$user->addGroup( 'reviewer' );
						# All reviewers are editors too
						if( !in_array( 'editor', $user->mGroups ) )
							$user->addGroup( 'editor' );
						$this->addLogItem( 'rights', $user, trim( $wgRequest->getText( 'comment' ) ), $oldgroups);
						$wgOut->addWikiText( wfMsg( 'makereviewer-granted-r', $user->getName() ) );
					} elseif( $wgRequest->getCheck( 'revoke2' ) ) {
						# Permission check
						if( !$wgUser->isAllowed( 'makevalidator' ) ) {
							$wgOut->permissionRequired( 'makevalidator' ); 
							return;
						}
						# Revoke the flag
						if ( in_array( 'reviewer', $user->mGroups ) )
							$user->removeGroup( 'reviewer' );
						$this->addLogItem( 'rights', $user, trim( $wgRequest->getText( 'comment' ) ), $oldgroups );
						$wgOut->addWikiText( wfMsg( 'makereviewer-revoked-r', $user->getName() ) );
					} elseif( $wgRequest->getCheck( 'grant1' ) ) {
						# Grant the flag
						if( !in_array( 'editor', $user->mGroups ) )
							$user->addGroup( 'editor' );
						$this->addLogItem( 'egrant', $user, trim( $wgRequest->getText( 'comment' ) ), $oldgroups );
						$wgOut->addWikiText( wfMsg( 'makereviewer-granted-e', $user->getName() ) );
					} elseif( $wgRequest->getCheck( 'revoke1' ) ) {
						# Permission check
						if( !$wgUser->isAllowed( 'removereview' ) ) {
							$wgOut->permissionRequired( 'removereview' ); 
							return;
						}
						if( in_array( 'reviewer', $user->mGroups ) ) {
							# Permission check
							if( !$wgUser->isAllowed( 'makevalidator' ) ) {
								$wgOut->permissionRequired( 'makevalidator' ); 
								return;
							}
							$user->removeGroup( 'editor' );
							# Reviewer flag falls of too
							$user->removeGroup( 'reviewer' );
						} else if( in_array( 'editor', $user->mGroups ) ) {
							# Revoke the flag
							$user->removeGroup( 'editor' );
						}
						$this->addLogItem( 'erevoke', $user, trim( $wgRequest->getText( 'comment' ) ), $oldgroups );
						$wgOut->addWikiText( wfMsg( 'makereviewer-revoked-e', $user->getName() ) );
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
		$form  = Xml::openElement( 'form', array( 'method' => 'post', 'action' => $thisTitle->getLocalUrl() ) );
		$form .= Xml::element( 'label', array( 'for' => 'username' ), wfMsg( 'makereviewer-username' ) ) . ' ';
		$form .= Xml::element( 'input', array( 'type' => 'text', 'name' => 'username', 'id' => 'username', 'value' => $this->target ) ) . ' ';
		$form .= Xml::element( 'input', array( 'type' => 'submit', 'name' => 'dosearch', 'value' => wfMsg( 'makereviewer-search' ) ) );
		$form .= Xml::closeElement( 'form' );
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
			$grant1 = true; $revoke1 = false;
			$grant2 = true; $revoke2 = false;
		} else if ( $type == MW_MAKEVALIDATE_REVOKE_GRANT ) {
			$grant1 = false; $revoke1 = true;
			$grant2 = true; $revoke2 = false;
		} else if ( $type == MW_MAKEVALIDATE_REVOKE_REVOKE ) {
			$grant1 = false; $revoke1 = $wgUser->isAllowed('makevalidator');
			$grant2 = false; $revoke2 = true;
		} else {
		// OK, this one should never happen
			$grant1 = true; $revoke1 = $wgUser->isAllowed('makevalidator');
			$grant2 = false; $revoke2 = true;
		}
	
		# Start the table
		$form  = Xml::openElement( 'form', array( 'method' => 'post', 'action' => $thisTitle->getLocalUrl() ) );
		$form .= '<fieldset><legend>' . wfMsg('makereviewer-legend') . '</legend>';
		$form .= Xml::openElement( 'table' ) . Xml::openElement( 'tr' );
		# Grant/revoke buttons
		$form .= Xml::element( 'td', array( 'align' => 'right' ), wfMsg( 'makereviewer-change-e' ) );
		$form .= Xml::openElement( 'td' );
		foreach( array( 'grant1', 'revoke1' ) as $button ) {
			$attribs = array( 'type' => 'submit', 'name' => $button, 'value' => wfMsg( 'makereviewer-' . $button ) );
			if( !$$button )
				$attribs['disabled'] = 'disabled';
			$form .= Xml::element( 'input', $attribs );
		}
		$form .= Xml::closeElement( 'td' ) . Xml::closeElement( 'tr' );
		// Check permissions
		if ( $wgUser->isAllowed('makevalidator') ) {
			$form .= Xml::element( 'td', array( 'align' => 'right' ), wfMsg( 'makereviewer-change-r' ) );
			$form .= Xml::openElement( 'td' );
			foreach( array( 'grant2', 'revoke2' ) as $button ) {
				$attribs = array( 'type' => 'submit', 'name' => $button, 'value' => wfMsg( 'makereviewer-' . $button ) );
				if( !$$button )
					$attribs['disabled'] = 'disabled';
				$form .= Xml::element( 'input', $attribs );
			}
			$form .= Xml::closeElement( 'td' ) . Xml::closeElement( 'tr' );
		}
		# Comment field
		$form .= Xml::openElement( 'td', array( 'align' => 'right' ) );
		$form .= Xml::element( 'label', array( 'for' => 'comment' ), wfMsg( 'makereviewer-comment' ) );
		$form .= Xml::openElement( 'td' );
		$form .= Xml::element( 'input', array( 'type' => 'text', 'name' => 'comment', 'id' => 'comment', 'size' => 45 ) );
		$form .= Xml::closeElement( 'td' ) . Xml::closeElement( 'tr' );
		# End table
		$form .= Xml::closeElement( 'table' );
		# Username
		$form .= Xml::element( 'input', array( 'type' => 'hidden', 'name' => 'username', 'value' => $this->target ) );
		# Edit token
		$form .= Xml::element( 'input', array( 'type' => 'hidden', 'name' => 'token', 'value' => $wgUser->editToken( 'makereviewer' ) ) );
		$form .= '</fieldset>';
		$form .= Xml::closeElement( 'form' );
		return $form;
	}

	/**
	 * Add logging entries for the specified action
	 * @param $type Either grant or revoke
	 * @param $user User receiving the action
	 * @param $comment Comment for the log item
	 */
	function addLogItem( $type, &$user, $comment = '', $oldgroups ) {
		global $wgUser;
		
		$log = new LogPage( 'rights' );
		$targetPage = $user->getUserPage();
		
		$params = array();
		if( $type=='rights' ) {
			$newgroups = $user->getGroups();
			$params = array( implode( ', ',$oldgroups ), implode( ', ',$newgroups ) );
		}
		
		$log->addEntry( $type, $targetPage, $comment, $params );
	}
	
	/**
	 * Show the bot status log entries for the specified user
	 * @param $user User to show the log for
	 */
	function showLogEntries( &$user ) {
		global $wgOut;
		$title = $user->getUserPage();
		$wgOut->addHtml( Xml::element( 'h2', NULL, htmlspecialchars( LogPage::logName( 'rights' ) ) ) );
		$logViewer = new LogViewer( new LogReader( new FauxRequest( array( 'page' => $title->getPrefixedText(), 'type' => 'rights' ) ) ) );
		$logViewer->showList( $wgOut );
	}

}