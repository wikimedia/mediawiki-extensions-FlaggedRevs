<?php

use MediaWiki\Context\RequestContext;
use MediaWiki\Request\FauxRequest;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\SpecialPage\SpecialPageFactory;
use MediaWiki\Title\Title;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \FlaggedRevsUIHooks
 * @group Database
 */
class FlaggedRevsUIHooksTest extends MediaWikiIntegrationTestCase {

	/**
	 * @dataProvider rcFilterProvider
	 */
	public function testOnChangesListSpecialPageStructuredFiltersViaRecentChanges( $useFilters, $urlParams ) {
		$user = $this->getTestUser()->getUser();
		$userOptionsManager = $this->getServiceContainer()->getUserOptionsManager();
		$userOptionsManager->setOption( $user, 'rcenhancedfilters-disable', $useFilters ? 0 : 1 );

		$context = new RequestContext();
		$context->setLanguage( 'qqx' );
		$context->setUser( $user );
		$context->setTitle( SpecialPage::getTitleFor( 'Recentchanges' ) );
		$context->setRequest( new FauxRequest( $urlParams ) );

		$changesListSpecialPage = TestingAccessWrapper::newFromObject(
			$this->getServiceContainer()->getSpecialPageFactory()->getPage( 'Recentchanges' )
		);
		$changesListSpecialPage->setContext( $context );
		$changesListSpecialPage->registerFilters();
		$this->assertNotNull( $changesListSpecialPage->getFilterGroup( 'flaggedrevs' ) );
		$this->assertNotNull( $changesListSpecialPage->getFilterGroup( 'flaggedRevsUnstructured' ) );

		// Build the query and expect no failure
		$changesListSpecialPage->getRows();
	}

	public static function rcFilterProvider() {
		return [
			'unstructured filters' => [
				false,
				[ 'hideReviewed' => 1 ],
			],
			'rcfilters' => [
				true,
				[ 'flaggedrevs' => 'needreview' ],
			],
		];
	}

	/**
	 * @dataProvider injectStyleForSpecial_loadIPContributionsProvider
	 */
	public function testInjectStyleForSpecial_loadIPContributions( bool $enabled ) {
		$mockSpecialPageFactory = $this->createMock( SpecialPageFactory::class );
		$mockSpecialPageFactory->method( 'exists' )->willReturnMap( [
				[ 'IPContributions', $enabled ]
			] );
		$objectUnderTest = new FlaggedRevsUIHooks(
			$this->getServiceContainer()->getActorStore(),
			$this->getServiceContainer()->getConnectionProvider(),
			$this->getServiceContainer()->getLinkRenderer(),
			$this->getServiceContainer()->getLinksMigration(),
			$this->getServiceContainer()->getMainWANObjectCache(),
			$this->getServiceContainer()->getPermissionManager(),
			$this->getServiceContainer()->getReadOnlyMode(),
			$mockSpecialPageFactory
		);
		$objectUnderTest = TestingAccessWrapper::newFromObject( $objectUnderTest );
		$mockOutput = $this->createMock( OutputPage::class );
		$mockTitle = $this->createMock( Title::class );
		$mockTitle->method( 'isSpecial' )->willReturnMap( [
				[ 'IPContributions', true ]
			] );
		$mockOutput->method( 'getTitle' )->willReturn( $mockTitle );
		if ( $enabled ) {
			$mockOutput->expects( $this->exactly( 2 ) )->method( 'addModuleStyles' );
		} else {
			$mockOutput->expects( $this->never() )->method( 'addModuleStyles' );
		}
		$objectUnderTest->injectStyleForSpecial( $mockOutput );
	}

	public static function injectStyleForSpecial_loadIPContributionsProvider() {
		return [
			'Special:IPContributions exists' => [
				'enabled' => true
			],
			'Special:IPContributions doesn\'t exist' => [
				'enabled' => false
			],
		];
	}
}
