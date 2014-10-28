<?php
/**
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
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

/**
 * API module to stabilize pages
 *
 * @ingroup FlaggedRevs
 */
abstract class ApiStabilize extends ApiBase {
	// Title param
	protected $title;

	public function execute() {
		global $wgUser;
		$params = $this->extractRequestParams();

		if ( !isset( $params['title'] ) ) {
			$this->dieUsageMsg( array( 'missingparam', 'title' ) );
		} elseif ( !isset( $params['token'] ) ) {
			$this->dieUsageMsg( array( 'missingparam', 'token' ) );
		}

		$this->title = Title::newFromText( $params['title'] );
		if ( $this->title == null ) {
			$this->dieUsage( "Invalid title given.", "invalidtitle" );
		}

		$errors = $this->title->getUserPermissionsErrors( 'stablesettings', $wgUser );
		if ( $errors ) {
			// We don't care about multiple errors, just report one of them
			$this->dieUsageMsg( reset( $errors ) );
		}

		$this->doExecute(); // child class
	}

	public abstract function doExecute();

	public function mustBePosted() {
		return true;
	}

	public function isWriteMode() {
 		return true;
 	}

	public function needsToken() {
		return 'csrf';
	}

	public function getTokenSalt() {
		return '';
	}
}

// Assumes $wgFlaggedRevsProtection is off
class ApiStabilizeGeneral extends ApiStabilize {
	public function doExecute() {
		global $wgUser, $wgContLang;

		$params = $this->extractRequestParams();

		$form = new PageStabilityGeneralForm( $wgUser );
		$form->setPage( $this->title ); # Our target page
		$form->setWatchThis( $params['watch'] ); # Watch this page
		$form->setReasonExtra( $params['reason'] ); # Reason
		$form->setReasonSelection( 'other' ); # Reason dropdown
		$form->setExpiryCustom( $params['expiry'] ); # Expiry
		$form->setExpirySelection( 'other' ); # Expiry dropdown
		$restriction = $params['autoreview'];

		// Fill in config fields from URL params
		if ( $params['default'] === null ) {
			// Default version setting not optional
			$this->dieUsageMsg( array( 'missingparam', 'default' ) );
		} else {
			$form->setOverride( $this->defaultFromKey( $params['default'] ) );
		}

		$form->setReviewThis( $params['review'] ); # Auto-review option

		if ( $restriction == 'none' ) {
			$restriction = ''; // 'none' => ''
		}

		$form->setAutoreview( $restriction ); # Autoreview restriction
		$form->ready();

		$status = $form->submit(); // true/error message key
		if ( $status !== true ) {
			$this->dieUsageMsg( $this->msg( $status )->text() );
		}

		# Output success line with the title and config parameters
		$res = array();
		$res['title'] = $this->title->getPrefixedText();
		$res['default'] = $params['default'];
		$res['autoreview'] = $params['autoreview'];
		$res['expiry'] = $wgContLang->formatExpiry( $form->getExpiry(), TS_ISO_8601 );
		$this->getResult()->addValue( null, $this->getModuleName(), $res );
	}

	protected function defaultFromKey( $key ) {
		if ( $key == 'stable' ) {
			return 1;
		} elseif ( $key == 'latest' ) {
			return 0;
		}

		return null; // bad key?
	}

	public function getAllowedParams() {
		// Replace '' with more readable 'none' in autoreview restiction levels
		$autoreviewLevels = FlaggedRevs::getRestrictionLevels();
		$autoreviewLevels[] = 'none';
		$pars = array(
			'default'     => array(
				ApiBase :: PARAM_TYPE => array( 'latest', 'stable' ),
				ApiBase :: PARAM_DFLT => null,
			),
			'autoreview'  => array(
				ApiBase :: PARAM_TYPE => $autoreviewLevels,
				ApiBase :: PARAM_DFLT => 'none',
			),
			'expiry'      => array(
				ApiBase::PARAM_DFLT => 'infinite',
				/** @todo Once support for MediaWiki < 1.25 is dropped, just use ApiBase::PARAM_HELP_MSG directly */
				constant( 'ApiBase::PARAM_HELP_MSG' ) ?: '' => 'apihelp-stabilize-param-expiry-general',
			),
			'reason'      => '',
			'review'      => false,
			'watch'       => null,
			'token'       => null,
			'title'       => array(
				/** @todo Once support for MediaWiki < 1.25 is dropped, just use ApiBase::PARAM_HELP_MSG directly */
				constant( 'ApiBase::PARAM_HELP_MSG' ) ?: '' => 'apihelp-stabilize-param-title-general',
			),
		);
		return $pars;
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'default'       => 'Default revision to show',
			'autoreview'    => 'Auto-review restriction',
			'expiry'        => 'Expiry for these settings',
			'title'         => 'Title of page to be stabilized',
			'reason'        => 'Reason',
			'review'        => 'Review this page',
			'watch'         => 'Watch this page',
			'token'         => 'An edit token retrieved through prop=info'
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Change page stability settings';
	}

	protected function getDescriptionMessage() {
		return parent::getDescriptionMessage() . '-general';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return 'api.php?action=stabilize&title=Test&default=stable&reason=Test&token=123ABC';
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=stabilize&title=Test&default=stable&reason=Test&token=123ABC'
				=> 'apihelp-stabilize-example-general',
		);
	}
}

// Assumes $wgFlaggedRevsProtection is on
class ApiStabilizeProtect extends ApiStabilize {
	public function doExecute() {
		global $wgUser, $wgContLang;
		$params = $this->extractRequestParams();

		$form = new PageStabilityProtectForm( $wgUser );
		$form->setPage( $this->title ); # Our target page
		$form->setWatchThis( $params['watch'] ); # Watch this page
		$form->setReasonExtra( $params['reason'] ); # Reason
		$form->setReasonSelection( 'other' ); # Reason dropdown
		$form->setExpiryCustom( $params['expiry'] ); # Expiry
		$form->setExpirySelection( 'other' ); # Expiry dropdown

		$restriction = $params['protectlevel'];
		if ( $restriction == 'none' ) {
			$restriction = ''; // 'none' => ''
		}

		$form->setAutoreview( $restriction ); # Autoreview restriction
		$form->ready();

		$status = $form->submit(); // true/error message key
		if ( $status !== true ) {
			$this->dieUsageMsg( $this->msg( $status )->text() );
		}

		# Output success line with the title and config parameters
		$res = array();
		$res['title'] = $this->title->getPrefixedText();
		$res['protectlevel'] = $params['protectlevel'];
		$res['expiry'] = $wgContLang->formatExpiry( $form->getExpiry(), TS_ISO_8601 );
		$this->getResult()->addValue( null, $this->getModuleName(), $res );
	}

	public function getAllowedParams() {
		// Replace '' with more readable 'none' in autoreview restiction levels
		$autoreviewLevels = FlaggedRevs::getRestrictionLevels();
		$autoreviewLevels[] = 'none';
		return array(
			'protectlevel' => array(
				ApiBase :: PARAM_TYPE => $autoreviewLevels,
				ApiBase :: PARAM_DFLT => 'none',
			),
			'expiry'      => array(
				ApiBase::PARAM_DFLT => 'infinite',
				/** @todo Once support for MediaWiki < 1.25 is dropped, just use ApiBase::PARAM_HELP_MSG directly */
				constant( 'ApiBase::PARAM_HELP_MSG' ) ?: '' => 'apihelp-stabilize-param-expiry-protect',
			),
			'reason'    => '',
			'watch'     => null,
			'token'     => null,
			'title'       => array(
				/** @todo Once support for MediaWiki < 1.25 is dropped, just use ApiBase::PARAM_HELP_MSG directly */
				constant( 'ApiBase::PARAM_HELP_MSG' ) ?: '' => 'apihelp-stabilize-param-title-protect',
			),
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'protectlevel'  => 'The review-protection level',
			'expiry'        => 'Review-protection expiry',
			'title'         => 'Title of page to be review-protected',
			'reason'        => 'Reason',
			'watch'         => 'Watch this page',
			'token'         => 'An edit token retrieved through prop=info',
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Configure review-protection settings for a page';
	}

	protected function getDescriptionMessage() {
		return parent::getDescriptionMessage() . '-protect';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return 'api.php?action=stabilize&title=Test&protectlevel=none&reason=Test&token=123ABC';
	}

	protected function getExamplesMessages() {
		return array(
			'action=stabilize&title=Test&protectlevel=none&reason=Test&token=123ABC'
				=> 'apihelp-stabilize-example-protect',
		);
	}
}
