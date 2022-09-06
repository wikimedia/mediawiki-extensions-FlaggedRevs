<?php

namespace MediaWiki\Extension\FlaggedRevs\Rest;

use FlaggablePageView;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Rest\StringStream;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * Handler class for REST API endpoints that update diff header items
 */
class DiffHeaderHandler extends SimpleHandler {

	/**
	 * @param int $oldId
	 * @param int $newId
	 * @return Response
	 */
	public function run( int $oldId, int $newId ) {
		$html = FlaggablePageView::buildDiffHeaderItems( $oldId, $newId );
		$response = $this->getResponseFactory()->create();
		$response->setBody( new StringStream( $html ) );
		$response->setHeader( 'Content-Type', 'text/html' );
		return $response;
	}

	public function needsWriteAccess() {
		return false;
	}

	/** @inheritDoc */
	public function getParamSettings() {
		return [
			'oldId' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => 'integer',
			],
			'newId' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => 'integer',
			]
		];
	}
}
