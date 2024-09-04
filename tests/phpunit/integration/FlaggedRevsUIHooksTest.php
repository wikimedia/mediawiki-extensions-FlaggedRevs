<?php

use MediaWiki\Context\RequestContext;
use MediaWiki\Request\FauxRequest;
use MediaWiki\SpecialPage\SpecialPage;
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
}
