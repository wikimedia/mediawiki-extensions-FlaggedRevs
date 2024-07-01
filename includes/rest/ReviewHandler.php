<?php

namespace MediaWiki\Extension\FlaggedRevs\Rest;

use FlaggedRevs;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use RevisionReview;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * Handler class for REST API endpoint that updates revision review items
 */
class ReviewHandler extends SimpleHandler {

	/**
	 * @param string $target
	 * @return Response
	 */
	public function run( $target ) {
		$body = $this->getValidatedBody();
		$body[ 'target' ] = $target;
		$result = RevisionReview::doReview( $body );
		$response = $this->getResponseFactory()->createJson( $result );
		if ( isset( $result[ 'error-html' ] ) ) {
			$response->setStatus( 400 );
		}
		return $response;
	}

	public function needsWriteAccess() {
		return true;
	}

	/** @inheritDoc */
	public function getParamSettings() {
		return [
			'target' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			]
		];
	}

	/** @inheritDoc */
	public function getBodyParamSettings(): array {
		// NOTE: All parameters are received as strings, because review.js
		//       takes the values from HTML form elements without understanding
		//       their types.
		//       This is not a problem since we pass the request body to
		//       RevisionReview::doReview(), which is designed to handle submit
		//       data from an HTML form, which is all strings anyway.

		return [
			'oldid' => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
			'refid' => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
			'validatedParams' => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
			'templateParams' => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
			'wpApprove' => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
			'wpUnapprove' => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
			'wpReject' => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
			'wpReason' => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
			'changetime' => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
			'wpEditToken' => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'wp' . FlaggedRevs::getTagName() => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
		];
	}
}
