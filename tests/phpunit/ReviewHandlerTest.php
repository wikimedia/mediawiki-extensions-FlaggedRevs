<?php

use MediaWiki\Extension\FlaggedRevs\Rest\ReviewHandler;
use MediaWiki\Rest\RequestData;
use MediaWiki\Session\CsrfTokenSet;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;

/**
 * @covers \MediaWiki\Extension\FlaggedRevs\Rest\ReviewHandler
 *
 * @group Database
 */
class ReviewHandlerTest extends MediaWikiIntegrationTestCase {
	use HandlerTestTrait;

	private function newHandler() {
		return new ReviewHandler();
	}

	protected function setUp(): void {
		parent::setUp();
		$this->setUserLang( 'qqx' );
	}

	private function createContext() {
		$user = $this->getTestUser( [ 'sysop', 'reviewer' ] )->getUser();

		$context = RequestContext::getMain();
		$context->setUser( $user );

		return $context;
	}

	public function testWithAllParams() {
		$context = $this->createContext();
		$page = $this->getExistingTestPage();

		$oldid = $page->getLatest();
		$this->editPage( $page, 'SecondEdit' );
		$refid = $page->getLatest();
		$target = $page->getTitle()->getPrefixedDBkey();
		$templateParams = 'templateParamsValue';
		$wpApprove = 1;
		$wpReason = 'wpReasonValue';
		$changetime = null;
		$csrf = new CsrfTokenSet( $context->getRequest() );
		$wpEditToken = $csrf->getToken( 'edit' )->toString();
		$wpDimName = 'wp' . FlaggedRevs::getTagName();
		$validatedParams = RevisionReviewForm::validationKey(
			$templateParams, $oldid, $context->getRequest()->getSessionData( 'wsFlaggedRevsKey' )
		);

		$request = new RequestData( [
			'method' => 'POST',
			'pathParams' => [ 'target' => $target ],
			'bodyContents' => json_encode( [
				'oldid' => $oldid,
				'wpEditToken' => $wpEditToken,
				'refid' => $refid,
				'validatedParams' => $validatedParams,
				'templateParams' => $templateParams,
				'wpApprove' => $wpApprove,
				'wpReason' => $wpReason,
				'changetime' => $changetime,
				$wpDimName => FlaggedRevs::getMaxLevel(),
			] ),
			'headers' => [
				'Content-Type' => 'application/json',
			],
		] );
		$handler = $this->newHandler();
		$response = $this->executeHandler( $handler, $request );

		$this->assertTrue(
			$response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
			'Status should be in 2xx range.'
		);

		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$assocFromResp = json_decode( $response->getBody()->getContents(), true );
		$this->assertTrue( $assocFromResp['change-time'] || $assocFromResp['change-time'] === '' );
	}

	public function testWithMinParams() {
		$context = $this->createContext();
		$page = $this->getExistingTestPage();

		$oldid = $page->getLatest();
		$target = $page->getTitle()->getPrefixedDBkey();
		$templateParams = 'templateParamsValue';
		$wpApprove = 1;
		$csrf = new CsrfTokenSet( $context->getRequest() );
		$wpEditToken = $csrf->getToken( 'edit' )->toString();
		$wpDimName = 'wp' . FlaggedRevs::getTagName();
		$validatedParams = RevisionReviewForm::validationKey(
			$templateParams, $oldid, $context->getRequest()->getSessionData( 'wsFlaggedRevsKey' )
		);

		$request = new RequestData( [
			'method' => 'POST',
			'pathParams' => [ 'target' => $target ],
			'bodyContents' => json_encode( [
				'oldid' => $oldid,
				'wpEditToken' => $wpEditToken,
				'validatedParams' => $validatedParams,
				'templateParams' => $templateParams,
				'wpApprove' => $wpApprove,
				$wpDimName => FlaggedRevs::getMaxLevel(),
			] ),
			'headers' => [
				'Content-Type' => 'application/json',
			],
		] );
		$handler = $this->newHandler();
		$response = $this->executeHandler( $handler, $request );

		$this->assertTrue(
			$response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
			'Status should be in 2xx range.'
		);

		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$assocFromResp = json_decode( $response->getBody()->getContents(), true );
		$this->assertTrue( $assocFromResp[ 'change-time' ] || $assocFromResp[ 'change-time' ] === '' );
	}

	public function testWithNonexistingPage() {
		$page = $this->getNonexistingTestPage();
		$context = $this->createContext();
		$target = $page->getTitle()->getPrefixedDBkey();
		$csrf = new CsrfTokenSet( $context->getRequest() );
		$wpEditToken = $csrf->getToken( 'edit' )->toString();
		$wpDimName = 'wp' . FlaggedRevs::getTagName();

		$request = new RequestData( [
			'method' => 'POST',
			'pathParams' => [ 'target' => $target ],
			'bodyContents' => json_encode( [
				'wpEditToken' => $wpEditToken,
				$wpDimName => FlaggedRevs::getMaxLevel(),
			] ),
			'headers' => [
				'Content-Type' => 'application/json',
			],
		] );
		$handler = $this->newHandler();
		$response = $this->executeHandler( $handler, $request );

		$this->assertTrue(
			$response->getStatusCode() >= 400 && $response->getStatusCode() < 500,
			'Status should be in 4xx range.'
		);

		$assocFromResp = json_decode( $response->getBody()->getContents(), true );
		$this->assertStringContainsString( 'The target page does not exist.', $assocFromResp[ 'error-html' ] );
	}

	public function testWithConfiguredAccuracyParams() {
		$context = $this->createContext();
		$page = $this->getExistingTestPage();

		$this->setMwGlobals( [
			'wgFlaggedRevsTags' => [ 'accuracy' => [ 'levels' => 3 ] ],
		] );

		$oldid = $page->getLatest();
		$this->editPage( $page, 'SecondEdit' );
		$refid = $page->getLatest();
		$target = $page->getTitle()->getPrefixedDBkey();
		$templateParams = 'templateParamsValue';
		$wpApprove = 1;
		$wpReason = 'wpReasonValue';
		$changetime = null;
		$csrf = new CsrfTokenSet( $context->getRequest() );
		$wpEditToken = $csrf->getToken( 'edit' )->toString();
		$wpDimName = 'wp' . FlaggedRevs::getTagName();
		$validatedParams = RevisionReviewForm::validationKey(
			$templateParams, $oldid, $context->getRequest()->getSessionData( 'wsFlaggedRevsKey' )
		);

		$request = new RequestData( [
			'method' => 'POST',
			'pathParams' => [ 'target' => $target ],
			'bodyContents' => json_encode( [
				'oldid' => $oldid,
				'wpEditToken' => $wpEditToken,
				'refid' => $refid,
				'validatedParams' => $validatedParams,
				'templateParams' => $templateParams,
				'wpApprove' => $wpApprove,
				'wpReason' => $wpReason,
				'changetime' => $changetime,
				$wpDimName => FlaggedRevs::getMaxLevel(),
			] ),
			'headers' => [
				'Content-Type' => 'application/json',
			],
		] );
		$handler = $this->newHandler();
		$response = $this->executeHandler( $handler, $request );
		$this->assertTrue(
			$response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
			'Status should be in 2xx range.'
		);

		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );

		$assocFromResp = json_decode( $response->getBody()->getContents(), true );
		$this->assertNull( $assocFromResp['change-time'] );
	}
}
