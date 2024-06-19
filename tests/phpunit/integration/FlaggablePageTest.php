<?php

use MediaWiki\Title\Title;

/**
 * @group Database
 * @covers \FlaggableWikiPage
 */
class FlaggablePageTest extends MediaWikiIntegrationTestCase {

	public function testPageDataFromTitle() {
		$title = Title::makeTitle( NS_MAIN, "SomePage" );
		$article = FlaggableWikiPage::newInstance( $title );

		$this->editPage(
			$article,
			'Some text to insert'
		);

		$data = (array)$article->pageDataFromTitle( $this->getDb(), $title );

		$this->assertArrayHasKey( 'fpc_override', $data,
			"data->fpc_override field exists" );
		$this->assertArrayHasKey( 'fp_stable', $data,
			"data->fp_stable field exists" );
		$this->assertArrayHasKey( 'fp_pending_since', $data,
			"data->fp_pending_since field exists" );
		$this->assertArrayHasKey( 'fp_reviewed', $data,
			"data->fp_reviewed field exists" );
	}
}
