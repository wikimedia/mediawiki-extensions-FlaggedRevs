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

		if ( !isset( $params['title'] ) ) {
			$this->dieUsageMsg( array( 'missingparam', 'title' ) );
		} elseif ( !isset( $params['token'] ) ) {
			$this->dieUsageMsg( array( 'missingparam', 'token' ) );
		}

		$title = Title::newFromText( $params['title'] );
		if ( $title == null ) {
			$this->dieUsage( "Invalid title given.", "invalidtitle" );
		}
		$errors = $title->getUserPermissionsErrors( 'stablesettings', $wgUser );
		if ( $errors ) {
			// We don't care about multiple errors, just report one of them
			$this->dieUsageMsg( reset( $errors ) );
		}

		$form = FlaggedRevs::getPageStabilityForm();

		$form->setPage( $title ); # Our target page
		$form->setWatchThis( $params['watch'] ); # Watch this page
		$form->setReason( $params['reason'] ); # Reason
		$form->setReasonSelection( 'other' ); # Reason dropdown
		$form->setExpiry( $params['expiry'] ); # Expiry
		$form->setExpirySelection( 'other' ); # Expiry dropdown
		if ( FlaggedRevs::useProtectionLevels() ) {
			$restriction = $params['protectlevel'];
		} else {
			$restriction = $params['autoreview'];
			// Fill in config fields from URL params
			$form->setPrecedence( $this->precendenceFromKey( $params['precedence'] ) );
			if ( $params['default'] === null ) {
				// Default version setting not optional
				$this->dieUsageMsg( array( 'missingparam', 'default' ) );
			} else {
				$form->setOverride( $this->defaultFromKey( $params['default'] ) );
			}
			$form->setReviewThis( $params['review'] ); # Auto-review option
		}
		if ( $restriction == 'none' ) {
			$restriction = ''; // 'none' => ''
		}
		$form->setAutoreview( $restriction ); # Autoreview restriction
		$form->ready();
		
		$status = $form->submit(); // true/error message key
		if ( $status !== true ) {
			$this->dieUsage( wfMsg( $status ) );
		}
		
		# Output success line with the title and config parameters
		$res = array();
		$res['title'] = $title->getPrefixedText();
		if ( FlaggedRevs::useProtectionLevels() ) {
			$res['protectlevel'] = $params['protectlevel'];
		} else {
			$res['default'] = $params['default'];
			$res['precedence'] = $params['precedence'];
			$res['autoreview'] = $params['autoreview'];
		}
		$res['expiry'] = $form->getExpiry();
		$this->getResult()->addValue( null, $this->getModuleName(), $res );
	}
	
	protected function defaultFromKey( $key ) {
		if ( $key == 'stable' ) {
			return 1;
		} else if ( $key == 'latest' ) {
			return 0;
		}
		// bad key?
		return null;
	}
	
	protected function precendenceFromKey( $key ) {
		if ( $key == 'pristine' ) {
			return FLAGGED_VIS_PRISTINE;
		} else if ( $key == 'quality' ) {
			return FLAGGED_VIS_QUALITY;
		} else if ( $key == 'latest' ) {
			return FLAGGED_VIS_LATEST;
		}
		// bad key?
		return null;
	}
	
	protected function keyFromPrecendence( $precedence ) {
		if ( $precedence == FLAGGED_VIS_PRISTINE ) {
			return 'pristine';
		} else if ( $precedence == FLAGGED_VIS_QUALITY ) {
			return 'quality';
		} else if ( $precedence == FLAGGED_VIS_LATEST ) {
			return 'lastest';
		}
		// bad key?
		return null;
	}

	public function mustBePosted() {
		return true;
	}
	
	public function isWriteMode() {
 		return true;
 	}

	public function getAllowedParams() {
		// Replace '' with more readable 'none' in autoreview restiction levels
		$autoreviewLevels = FlaggedRevs::getRestrictionLevels();
		$autoreviewLevels[] = 'none';
		if ( FlaggedRevs::useProtectionLevels() ) {
			$pars = array(
				'protectlevel' => array(
					ApiBase :: PARAM_TYPE => $autoreviewLevels,
					ApiBase :: PARAM_DFLT => 'none',
				)
			);
		} else {
			$pars = array(
				'default'     => array(
					ApiBase :: PARAM_TYPE => array( 'latest', 'stable' ),
					ApiBase :: PARAM_DFLT => null,
				),
				'precedence'  => array(
					ApiBase :: PARAM_TYPE => array( 'pristine', 'quality', 'latest' ),
					ApiBase :: PARAM_DFLT => $this->keyFromPrecendence( FlaggedRevs::getPrecedence() )
				),
				'autoreview' => array(
					ApiBase :: PARAM_TYPE => $autoreviewLevels,
					ApiBase :: PARAM_DFLT => 'none',
				),
			);
		}
		$pars += array(
			'expiry' => 'infinite',
			'reason' => '',
			'review' => false,
			'watch'  => null,
			'token'  => null,
			'title'  => null,
		);
		return $pars;
	}

	public function getParamDescription() {
		if ( FlaggedRevs::useProtectionLevels() ) {
			$desc = array(
				'protectlevel' => 'The review protection level',
			);
		} else {
			$desc = array(
				'default' 		=> 'Default revision to show',
				'precedence'	=> 'What stable revision should be shown',
				'autoreview' 	=> 'Auto-review restriction',
			);
		}
		$desc += array(
			'expiry' 		=> 'Stabilization expiry',
			'title' 		=> 'Title of page to be stabilized',
			'reason' 		=> 'Reason',
			'review'        => 'Review this page',
			'watch'         => 'Watch this page',
			'token' 		=> 'An edit token retrieved through prop=info',
		);
		return $desc;
	}

	public function getDescription() {
		return 'Change page stabilization settings.';
	}
	
	public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
			array( 'missingparam', 'title' ),
			array( 'missingparam', 'token' ),
			array( 'missingparam', 'default' ),
			array( 'code' => 'invalidtitle', 'info' => 'Invalid title given.' ),
			array( 'code' => 'invalidtitle', 'info' => 'Target page does not exist.' ),
			array( 'code' => 'invalidtitle', 'info' => 'Title given does not correspond to a reviewable page.' ),
			array( 'code' => 'badprotectlevel', 'info' => 'Invalid protection level given.' ),
			array( 'code' => 'invalidconfig', 'info' => 'Invalid config parameters given. The precendence level may beyond your rights.' ),
		) );
	}
	
	public function getTokenSalt() {
		return '';
	}

	protected function getExamples() {
		if ( FlaggedRevs::useProtectionLevels() )
			return 'api.php?action=stabilize&title=Test&protectlevel=none&reason=Test&token=123ABC';
		else
			return 'api.php?action=stabilize&title=Test&default=stable&reason=Test&token=123ABC';
	}

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}
}
