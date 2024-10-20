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

use MediaWiki\Api\ApiBase;
use MediaWiki\MediaWikiServices;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * API module to stabilize pages
 * Assumes $wgFlaggedRevsProtection is off
 *
 * @ingroup FlaggedRevs
 */
class ApiStabilizeGeneral extends ApiStabilize {
	public function doExecute() {
		$params = $this->extractRequestParams();
		$user = $this->getUser();

		$form = new PageStabilityGeneralForm( $user );
		$form->setTitle( $this->title ); # Our target page
		$form->setReasonExtra( $params['reason'] ); # Reason
		$form->setReasonSelection( 'other' ); # Reason dropdown
		$form->setExpiryCustom( $params['expiry'] ); # Expiry
		$form->setExpirySelection( 'other' ); # Expiry dropdown
		$restriction = $params['autoreview'];

		// Fill in config fields from URL params
		$form->setOverride( $this->defaultFromKey( $params['default'] ) );

		$form->setReviewThis( $params['review'] ); # Auto-review option

		if ( $restriction == 'none' ) {
			$restriction = ''; // 'none' => ''
		}

		$form->setAutoreview( $restriction ); # Autoreview restriction
		$form->ready();

		$status = $form->submit(); // true/error message key
		if ( $status !== true ) {
			$this->dieWithError( $status );
		}

		# Output success line with the title and config parameters
		$res = [];
		$res['title'] = $this->title->getPrefixedText();
		$res['default'] = $params['default'];
		$res['autoreview'] = $params['autoreview'];
		$res['expiry'] = MediaWikiServices::getInstance()->getContentLanguage()
			->formatExpiry( $form->getExpiry(), TS_ISO_8601 );
		$this->getResult()->addValue( null, $this->getModuleName(), $res );
	}

	/**
	 * @param string $key
	 * @return int|null
	 */
	private function defaultFromKey( $key ) {
		if ( $key == 'stable' ) {
			return 1;
		} elseif ( $key == 'latest' ) {
			return 0;
		}

		return null; // bad key?
	}

	/**
	 * @inheritDoc
	 */
	protected function getAllowedParams() {
		// Replace '' with more readable 'none' in autoreview restiction levels
		$autoreviewLevels = [ ...FlaggedRevs::getRestrictionLevels(), 'none' ];
		$pars = [
			'default' => [
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => [ 'latest', 'stable' ],
			],
			'autoreview' => [
				ParamValidator::PARAM_TYPE => $autoreviewLevels,
				ParamValidator::PARAM_DEFAULT => 'none',
			],
			'expiry' => [
				ParamValidator::PARAM_DEFAULT => 'infinite',
				ApiBase::PARAM_HELP_MSG => 'apihelp-stabilize-param-expiry-general',
			],
			'reason' => '',
			'review' => false,
		];

		$pars += [
			'title' => [
				ParamValidator::PARAM_REQUIRED => true,
				ApiBase::PARAM_HELP_MSG => 'apihelp-stabilize-param-title-general',
			],
		];
		return $pars;
	}

	/**
	 * @return string
	 */
	protected function getSummaryMessage() {
		return "apihelp-{$this->getModulePath()}-summary-general";
	}

	/**
	 * @inheritDoc
	 */
	protected function getExtendedDescription() {
		return [ [
			"apihelp-{$this->getModulePath()}-extended-description-general",
			'api-help-no-extended-description',
		] ];
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			'action=stabilize&title=Test&default=stable&reason=Test&token=123ABC'
				=> 'apihelp-stabilize-example-general',
		];
	}
}
