<?php

/*
 * Created on Sep 19, 2009
 *
 * API module for MediaWiki's FlaggedRevs extension
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
 * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

/**
 * API module to stabilize pages
 *
 * @ingroup FlaggedRevs
 */
class ApiStabilize extends ApiBase {
	public function execute() {
		global $wgUser, $wgContLang;
		$params = $this->extractRequestParams();

		// Check permissions
		if( !$wgUser->isAllowed( 'stablesettings' ) )
			$this->dieUsageMsg( array( 'badaccess-group0' ) );
		if( $wgUser->isBlocked() )
			$this->dieUsageMsg( array( 'blockedtext' ) );
		//if( !$wgUser->matchEditToken( $params['token'] ) )
		//	$this->dieUsageMsg( array( 'sessionfailure' ) );

		$target = Title::newFromText( $params['title'] );
		if( !$target )
			$this->dieUsageMsg( array( 'invalidtitle', $params['title'] ) );
		if( !$target->getArticleID() )
			$this->dieUsageMsg( array( 'notanarticle', $params['title'] ) );

		$stabilize = $params['default'] == 'stable';
		$autoreview = $params['restriction'] == 'none' ?
			'' : $params['restriction'];
		switch( $params['precedence'] ) {
			case 'latest':
				$precedence = FLAGGED_VIS_LATEST;
				break;
			case 'quality':
				$precedence = FLAGGED_VIS_QUALITY;
				break;
			case 'pristine':
				$precedence = FLAGGED_VIS_PRISTINE;
				break;
		}
		$reset = $precedence == FlaggedRevs::getPrecedence() &&
			$stabilize == FlaggedRevs::showStableByDefault();

		if( !$reset || $params['expiry'] == 'infinite' || $params['expiry'] == 'indefinite' ) {
			$expiry = Block::infinity();
		} else {
			# Convert GNU-style date, on error returns -1 for PHP <5.1 and false for PHP >=5.1
			$expiry = strtotime( $params['expiry'] );
			if( $expiry < 0 || $expiry === false ) {
				$this->dieUsageMsg( array( 'ipb_expiry_invalid' ) );
			}
			$expiry = wfTimestamp( TS_MW, $expiry );
			if( $expiry < wfTimestampNow() )
				$this->dieUsageMsg( array( 'ipb_expiry_invalid' ) );
		}

		FlaggedRevs::purgeExpiredConfigurations();

		$dbw = wfGetDB( DB_MASTER );
		$row = $dbw->selectRow( 'flaggedpage_config',
			array( 'fpc_select', 'fpc_override', 'fpc_level', 'fpc_expiry' ),
			array( 'fpc_page_id' => $target->getArticleID() ),
			__METHOD__
		);
		if( !$reset ) {
			$changed = false;
			if( !$row
				|| $row->fpc_select != $precedence
				|| $row->fpc_override != $stabilize
				|| $row->fpc_level != $autoreview
				|| $row->fpc_expiry != $expiry ) {
					$changed = true;
					$dbw->replace( 'flaggedpage_config',
						array( 'PRIMARY' ),
						array( 'fpc_page_id' => $target->getArticleID(),
							'fpc_select'   => $precedence,
							'fpc_override' => $stabilize,
							'fpc_level'    => $autoreview,
							'fpc_expiry'   => $expiry ),
						__METHOD__
					);
			}
		} else {
			$dbw->delete(
				'flaggedpage_config',
				array( 'fpc_page_id' => $target->getArticleID() ),
				__METHOD__
			);
			$changed = (bool)$dbw->affectedRows();
		}

		# Mostly copied from Stabilization_body.php
		if( $changed ) {
			$latest = $target->getLatestRevID( GAID_FOR_UPDATE );
			# ID, accuracy, depth, style
			$set = array();
			# @FIXME: do this better
			$set[] = wfMsgForContent( "stabilization-sel-short" ) . wfMsgForContent( 'colon-separator' ) .
				wfMsgForContent("stabilization-sel-short-{$precedence}");
			$set[] = wfMsgForContent( "stabilization-def-short" ) . wfMsgForContent( 'colon-separator' ) .
				wfMsgForContent("stabilization-def-short-" . ((int)$stabilize) );
			if( strlen( $autoreview ) ) {
				$set[] = "autoreview={$autoreview}";
			}
			$settings = '[' . implode( ', ' , $set ). ']';
			# Append comment with settings (other than for resets)
			$reason = '';
			if( !$reset ) {
				$reason = $params['reason'] ? "{$params['reason']} $settings" : "$settings";
				$encodedExpiry = Block::encodeExpiry( $expiry, $dbw );
				if( $encodedExpiry != 'infinity' ) {
					$expiryDescription = ' (' . wfMsgForContent( 'stabilize-expiring',
						$wgContLang->timeanddate($expiry, false, false) ,
						$wgContLang->date($expiry, false, false) ,
						$wgContLang->time($expiry, false, false) ) . ')';
					$reason .= $expiryDescription;
				}
			}
			# Add log entry...
			$log = new LogPage( 'stable' );
			if( !$reset ) {
				$log->addEntry( 'config', $target, $reason );
				$type = "stable-logentry";
			} else {
				$log->addEntry( 'reset', $target, $reason );
				$type = "stable-logentry2";
			}
			# Build null-edit comment
			$comment = $wgContLang->ucfirst( wfMsgForContent( $type, $target->getPrefixedText() ) );
			if( $reason ) {
				$comment .= ": $reason";
			}
			# Insert a null revision
			$nullRevision = Revision::newNullRevision( $dbw, $target->getArticleID(), $comment, true );
			$nullRevId = $nullRevision->insertOn( $dbw );
			# Update page record and touch page
			$article = new Article( $target );
			$article->updateRevisionOn( $dbw, $nullRevision, $latest );
			wfRunHooks( 'NewRevisionFromEditComplete', array( $article, $nullRevision, $latest ) );

			$res['title'] = $target->getPrefixedText();
			$res['default'] = (int)$stabilize;
			$this->getResult()->addValue(null, $this->getModuleName(), $res);
		}
	}

	protected function getRestrictionLevels() {
		global $wgUser, $wgFlaggedRevsRestrictionLevels;
		$levels = array( 'none' );
		foreach( $wgFlaggedRevsRestrictionLevels as $level ) {
			if( $level == 'sysop' )
				if( $wgUser->isAllowed( 'protect' ) || $wgUser->isAllowed( 'editprotected' ) )
					$levels[] = 'sysop';
			else
				if( $wgUser->isAllowed( $level ) )
					$levels[] = $level;
		}
		return $levels;
	}

	public function mustBePosted() {
		return true;
	}
	
	public function isWriteMode() { 
 		return true; 
 	}

	public function getAllowedParams() {
		$pars = array(
			'default' => array(
				ApiBase :: PARAM_TYPE => array( 'latest', 'stable' ),
			),
			'precedence' => array(
				ApiBase :: PARAM_TYPE => array( 'pristine', 'quality', 'latest' ),
				ApiBase :: PARAM_DFLT => 'latest',
			),
			'restriction' => array(
				ApiBase :: PARAM_TYPE => $this->getRestrictionLevels(),
				ApiBase :: PARAM_DFLT => 'none',
			),
			'expiry' => 'infinite',
			'reason' => null,
			'token' => null,
			'title' => null,
		);
		return $pars;
	}

	public function getParamDescription() {
		$desc = array(
			'default' => 'Default revision to show',
			'precedence' => 'What stable revision should be shown',
			'restriction' => 'Auto-review restriction',
			'expiry' => 'Stabilization expiry',
			'title' => 'Title of page to be stabilized',
			'reason' => 'Reason',
			'token' => 'An edit token retrieved through prop=info',
		);
		return $desc;
	}

	public function getDescription() {
		return 'Change page stabilization settings.';
	}

	protected function getExamples() {
		return 'api.php?action=stabilize&title=Test&default=stable&reason=Test&token=123ABC';
	}

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}
}
