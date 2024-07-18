<?php

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\FlaggedRevs\Rest\ReviewHandler;
use MediaWiki\Request\WebRequest;
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

	protected function setUp(): void {
		parent::setUp();
		$this->setUserLang( 'qqx' );
		$this->overrideConfigValues( [
			'FlaggedRevsAutoReview' => 0,
			'FlaggedRevsNamespaces' => [ NS_MAIN ],
			'FlaggedRevsProtection' => false,
			'FlaggedRevsTags' => [ 'accuracy' => [ 'levels' => 3 ] ],
		] );
	}

	private function createWebRequest(): WebRequest {
		$user = $this->getTestUser( [ 'sysop', 'reviewer' ] )->getUser();

		$context = RequestContext::getMain();
		$context->setUser( $user );

		return $context->getRequest();
	}

	public function testWithAllParams() {
		$webRequest = $this->createWebRequest();
		$page = $this->getExistingTestPage( __METHOD__ );

		$oldid = $page->getLatest();
		$this->editPage( $page, __METHOD__ );
		$refid = $page->getLatest();
		$target = $page->getTitle()->getPrefixedDBkey();
		$wpApprove = 1;
		$wpReason = 'wpReasonValue';
		$changetime = null;
		$csrf = new CsrfTokenSet( $webRequest );
		$wpEditToken = $csrf->getToken( 'edit' )->toString();
		$wpDimName = 'wp' . FlaggedRevs::getTagName();
		$validatedParams = RevisionReviewForm::validationKey(
			 $oldid, $webRequest->getSessionData( 'wsFlaggedRevsKey' )
		);

		$request = new RequestData( [
			'method' => 'POST',
			'pathParams' => [ 'target' => $target ],
			'bodyContents' => json_encode( [
				'oldid' => (string)$oldid,
				'wpEditToken' => (string)$wpEditToken,
				'refid' => (string)$refid,
				'validatedParams' => (string)$validatedParams,
				'wpApprove' => (string)$wpApprove,
				'wpReason' => (string)$wpReason,
				'changetime' => (string)$changetime,
				$wpDimName => (string)FlaggedRevs::getMaxLevel(),
			] ),
			'headers' => [
				'Content-Type' => 'application/json',
			],
		] );
		$handler = new ReviewHandler();
		$response = $this->executeHandler( $handler, $request );

		$this->assertStringStartsWith( '{"change-time":"', $response->getBody()->getContents() );
		$this->assertLessThan( 300, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );
	}

	public function testWithMinParams() {
		$webRequest = $this->createWebRequest();
		$page = $this->getExistingTestPage( __METHOD__ );

		$oldid = $page->getLatest();
		$target = $page->getTitle()->getPrefixedDBkey();
		$wpApprove = 1;
		$csrf = new CsrfTokenSet( $webRequest );
		$wpEditToken = $csrf->getToken( 'edit' )->toString();
		$wpDimName = 'wp' . FlaggedRevs::getTagName();
		$validatedParams = RevisionReviewForm::validationKey(
			 $oldid, $webRequest->getSessionData( 'wsFlaggedRevsKey' )
		);

		$request = new RequestData( [
			'method' => 'POST',
			'pathParams' => [ 'target' => $target ],
			'bodyContents' => json_encode( [
				'oldid' => (string)$oldid,
				'wpEditToken' => $wpEditToken,
				'validatedParams' => $validatedParams,
				'wpApprove' => (string)$wpApprove,
				$wpDimName => (string)FlaggedRevs::getMaxLevel(),
			] ),
			'headers' => [
				'Content-Type' => 'application/json',
			],
		] );
		$handler = new ReviewHandler();
		$response = $this->executeHandler( $handler, $request );

		$this->assertStringStartsWith( '{"change-time":"', $response->getBody()->getContents() );
		$this->assertLessThan( 300, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );
	}

	public function testWithNonexistingPage() {
		$page = $this->getNonexistingTestPage();
		$webRequest = $this->createWebRequest();
		$target = $page->getTitle()->getPrefixedDBkey();
		$csrf = new CsrfTokenSet( $webRequest );
		$wpEditToken = $csrf->getToken( 'edit' )->toString();
		$wpDimName = 'wp' . FlaggedRevs::getTagName();

		$request = new RequestData( [
			'method' => 'POST',
			'pathParams' => [ 'target' => $target ],
			'bodyContents' => json_encode( [
				'wpEditToken' => $wpEditToken,
				$wpDimName => (string)FlaggedRevs::getMaxLevel(),
			] ),
			'headers' => [
				'Content-Type' => 'application/json',
			],
		] );
		$handler = new ReviewHandler();
		$response = $this->executeHandler( $handler, $request );

		$this->assertStringContainsString( 'The target page does not exist.', $response->getBody()->getContents() );
		$this->assertGreaterThanOrEqual( 400, $response->getStatusCode() );
	}

	public function testWithConfiguredAccuracyParams() {
		$webRequest = $this->createWebRequest();
		$page = $this->getExistingTestPage( __METHOD__ );

		$oldid = $page->getLatest();
		$this->editPage( $page, __METHOD__ );
		$refid = $page->getLatest();
		$target = $page->getTitle()->getPrefixedDBkey();
		$wpApprove = 1;
		$wpReason = 'wpReasonValue';
		$changetime = null;
		$csrf = new CsrfTokenSet( $webRequest );
		$wpEditToken = $csrf->getToken( 'edit' )->toString();
		$wpDimName = 'wp' . FlaggedRevs::getTagName();
		$validatedParams = RevisionReviewForm::validationKey(
			$oldid, $webRequest->getSessionData( 'wsFlaggedRevsKey' )
		);

		$request = new RequestData( [
			'method' => 'POST',
			'pathParams' => [ 'target' => $target ],
			'bodyContents' => json_encode( [
				'oldid' => (string)$oldid,
				'wpEditToken' => $wpEditToken,
				'refid' => (string)$refid,
				'validatedParams' => $validatedParams,
				'wpApprove' => (string)$wpApprove,
				'wpReason' => $wpReason,
				'changetime' => $changetime,
				$wpDimName => (string)FlaggedRevs::getMaxLevel(),
			] ),
			'headers' => [
				'Content-Type' => 'application/json',
			],
		] );
		$handler = new ReviewHandler();
		$response = $this->executeHandler( $handler, $request );

		$this->assertStringStartsWith( '{"change-time":"', $response->getBody()->getContents() );
		$this->assertLessThan( 300, $response->getStatusCode() );
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );
	}
}
