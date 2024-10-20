<?php

use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Revision\RevisionRecord;
use Wikimedia\TestingAccessWrapper;

/**
 * @group Database
 */
class FlaggedRevsBackendTest extends MediaWikiIntegrationTestCase {

	/**
	 * @covers \FlaggedRevs::parseStableRevisionPooled
	 */
	public function testParseStableRevisionPooled() {
		$page = $this->getExistingTestPage( __METHOD__ );
		$rev = $page->getRevisionRecord();
		$title = $page->getTitle();
		$user = $rev->getUser( RevisionRecord::RAW );
		$comment = $rev->getComment( RevisionRecord::RAW );

		$row = [
			'rev_page' => $rev->getPageId(),
			'rev_id' => $rev->getId(),
			'rev_user' => $user->getId(),
			'rev_actor' => $user->getActorId(),
			'rev_minor_edit' => $rev->isMinor(),
			'rev_deleted' => $rev->getVisibility(),
			'rev_timestamp' => $rev->getTimestamp(),
			'rev_parent_id' => $rev->getParentId(),
			'rev_len' => $rev->getSize(),
			'rev_sha1' => $rev->getSha1(),
			'rev_comment_id' => $comment->id,
			'rev_comment_text' => $comment->text,
			'rev_comment_data' => $comment->data,
			'page_namespace' => $title->getNamespace(),
			'page_title' => $title->getDBkey(),
			'page_latest' => $title->getLatestRevID(),
			'fr_timestamp' => '',
			'fr_quality' => 0,
			'fr_tags' => '',
			'fr_flags' => '',
			'fr_user' => $user->getId()
		];

		$popts = ParserOptions::newFromAnon();
		/** @var FlaggedRevision $frev */
		$frev = TestingAccessWrapper::newFromClass( FlaggedRevision::class );
		$frev = $frev->newFromRow( (object)$row, $title, 0 );

		$out = FlaggedRevs::parseStableRevisionPooled( $frev, $popts );
		$this->assertInstanceOf( ParserOutput::class, $out->getValue() );
	}

}
