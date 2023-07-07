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

	/** @inheritdoc */
	protected $tablesUsed = [ 'flaggedrevs' ];

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
		$page = $this->getExistingTestPage( __METHOD__ );

		$oldid = $page->getLatest();
		$this->editPage( $page, __METHOD__ );
		$refid = $page->getLatest();
		$target = $page->getTitle()->getPrefixedDBkey();
		$templateParams = 'templateParamsValue';
		$wpApprove = 1;
		$wpUnapprove = 0;
		$wpReject = 0;
		$wpReason = 'wpReasonValue';
		$changetime = null;
		$csrf = new CsrfTokenSet( $context->getRequest() );
		$wpEditToken = $csrf->getToken( 'edit' )->toString();
		$wpDimName = 'wp' . FlaggedRevs::getTagName();
		$wpDimValue = count( FlaggedRevs::getLevels() ) - 1; // max level
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
				'wpUnapprove' => $wpUnapprove,
				'wpReject' => $wpReject,
				'wpReason' => $wpReason,
				'changetime' => $changetime,
				$wpDimName => $wpDimValue,
			] ),
			'headers' => [
				'Content-Type' => 'application/json',
			],
		] );
		$handler = $this->newHandler();
		$response = $this->executeHandler( $handler, $request );

		$this->assertStringStartsWith( '{"change-time":"', $response->getBody()->getContents() );
		$this->assertTrue(
			$response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
			'Status should be in 2xx range.'
		);

		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );
	}

	public function testWithMinParams() {
		$context = $this->createContext();
		$page = $this->getExistingTestPage( __METHOD__ );

		$oldid = $page->getLatest();
		$target = $page->getTitle()->getPrefixedDBkey();
		$templateParams = 'templateParamsValue';
		$wpApprove = 1;
		$csrf = new CsrfTokenSet( $context->getRequest() );
		$wpEditToken = $csrf->getToken( 'edit' )->toString();
		$wpDimName = 'wp' . FlaggedRevs::getTagName();
		$wpDimValue = count( FlaggedRevs::getLevels() ) - 1; // max level
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
				$wpDimName => $wpDimValue,
			] ),
			'headers' => [
				'Content-Type' => 'application/json',
			],
		] );
		$handler = $this->newHandler();
		$response = $this->executeHandler( $handler, $request );

		$this->assertStringStartsWith( '{"change-time":"', $response->getBody()->getContents() );
		$this->assertTrue(
			$response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
			'Status should be in 2xx range.'
		);

		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );
	}

	public function testWithNonexistingPage() {
		$page = $this->getNonexistingTestPage();
		$context = $this->createContext();
		$target = $page->getTitle()->getPrefixedDBkey();
		$csrf = new CsrfTokenSet( $context->getRequest() );
		$wpEditToken = $csrf->getToken( 'edit' )->toString();
		$wpDimName = 'wp' . FlaggedRevs::getTagName();
		$wpDimValue = count( FlaggedRevs::getLevels() ) - 1; // max level

		$request = new RequestData( [
			'method' => 'POST',
			'pathParams' => [ 'target' => $target ],
			'bodyContents' => json_encode( [
				'wpEditToken' => $wpEditToken,
				$wpDimName => $wpDimValue,
			] ),
			'headers' => [
				'Content-Type' => 'application/json',
			],
		] );
		$handler = $this->newHandler();
		$response = $this->executeHandler( $handler, $request );

		$this->assertStringContainsString( 'The target page does not exist.', $response->getBody()->getContents() );
		$this->assertGreaterThanOrEqual( 400, $response->getStatusCode() );
	}

	public function testWithConfiguredAccuracyParams() {
		$context = $this->createContext();
		$page = $this->getExistingTestPage( __METHOD__ );

		$this->setMwGlobals( [
			'wgFlaggedRevsTags' => [ 'accuracy' => [ 'levels' => 3, 'quality' => 20, 'pristine' => 21 ] ],
		] );

		$oldid = $page->getLatest();
		$this->editPage( $page, __METHOD__ );
		$refid = $page->getLatest();
		$target = $page->getTitle()->getPrefixedDBkey();
		$templateParams = 'templateParamsValue';
		$wpApprove = 1;
		$wpUnapprove = 0;
		$wpReject = 0;
		$wpReason = 'wpReasonValue';
		$changetime = null;
		$csrf = new CsrfTokenSet( $context->getRequest() );
		$wpEditToken = $csrf->getToken( 'edit' )->toString();
		$wpDimName = 'wp' . FlaggedRevs::getTagName();
		$wpDimValue = count( FlaggedRevs::getLevels() ) - 1; // max level
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
				'wpUnapprove' => $wpUnapprove,
				'wpReject' => $wpReject,
				'wpReason' => $wpReason,
				'changetime' => $changetime,
				$wpDimName => $wpDimValue,
			] ),
			'headers' => [
				'Content-Type' => 'application/json',
			],
		] );
		$handler = $this->newHandler();
		$response = $this->executeHandler( $handler, $request );
		$this->assertStringStartsWith( '{"change-time":"', $response->getBody()->getContents() );
		$this->assertTrue(
			$response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
			'Status should be in 2xx range.'
		);
		$this->assertSame( 'application/json', $response->getHeaderLine( 'Content-Type' ) );
	}
}
