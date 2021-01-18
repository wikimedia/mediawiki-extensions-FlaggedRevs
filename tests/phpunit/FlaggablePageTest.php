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
	protected function setUp() : void {
		parent::setUp();
		$this->user = new User();
	}

	public function testPageDataFromTitle() {
		$title = Title::makeTitle( NS_MAIN, "SomePage" );
		$article = new FlaggableWikiPage( $title );

		$user = $this->user;
		$article->doEditContent(
			ContentHandler::makeContent( "Some text to insert", $title ),
			"creating a page",
			EDIT_NEW,
			false,
			$user
		);

		$data = (array)$article->pageDataFromTitle( wfGetDB( DB_REPLICA ), $title );

		$this->assertTrue( array_key_exists( 'fpc_override', $data ),
			"data->fpc_override field exists" );
		$this->assertTrue( array_key_exists( 'fp_stable', $data ),
			"data->fp_stable field exists" );
		$this->assertTrue( array_key_exists( 'fp_pending_since', $data ),
			"data->fp_pending_since field exists" );
		$this->assertTrue( array_key_exists( 'fp_reviewed', $data ),
			"data->fp_reviewed field exists" );
	}
}
