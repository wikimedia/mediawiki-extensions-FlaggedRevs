<?php

/**
 * @covers \FlaggableWikiPage
 */
class FlaggablePageTest extends PHPUnit\Framework\TestCase {

	/**
	 * @var User|null
	 */
	private $user;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->user = new User();
	}

	public function testPageDataFromTitle() {
		$title = Title::makeTitle( NS_MAIN, "SomePage" );
		$article = FlaggableWikiPage::newInstance( $title );

		$user = $this->user;
		$article->doUserEditContent(
			ContentHandler::makeContent( "Some text to insert", $title ),
			$user,
			"creating a page",
			EDIT_NEW
		);

		$data = (array)$article->pageDataFromTitle( wfGetDB( DB_REPLICA ), $title );

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
