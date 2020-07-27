<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;

/**
 * Reject confirmation review form UI
 */
class RejectConfirmationFormUI {
	/** @var RevisionReviewForm */
	protected $form;

	/** @var RevisionRecord */
	private $oldRevRecord;

	/** @var RevisionRecord */
	private $newRevRecord;

	public function __construct( RevisionReviewForm $form ) {
		$this->form = $form;

		$revLookup = MediaWikiServices::getInstance()->getRevisionLookup();
		$page = $form->getPage();
		$this->newRevRecord = $revLookup->getRevisionByTitle( $page, $form->getOldId() );
		$this->oldRevRecord = $revLookup->getRevisionByTitle( $page, $form->getRefId() );
	}

	/**
	 * Get the "are you sure you want to reject these changes?" form
	 * @return array (html string, error string or true)
	 */
	public function getHtml() {
		global $wgLang;

		$status = $this->form->checkTarget();
		if ( $status !== true ) {
			return [ '', $status ]; // not a reviewable existing page
		}
		$oldRevRecord = $this->oldRevRecord; // convenience
		$newRevRecord = $this->newRevRecord; // convenience
		# Do not mess with archived/deleted revisions
		if ( !$oldRevRecord ||
			$oldRevRecord->isDeleted( RevisionRecord::DELETED_TEXT ) ||
			!$newRevRecord ||
			$newRevRecord->isDeleted( RevisionRecord::DELETED_TEXT )
		) {
			return [ '', 'review_bad_oldid' ];
		}

		$form = '<div class="plainlinks">';

		$dbr = wfGetDB( DB_REPLICA );
		$revQuery = MediaWikiServices::getInstance()->getRevisionStore()->getQueryInfo();
		$res = $dbr->select(
			$revQuery['tables'],
			$revQuery['fields'],
			[
				'rev_page' => $oldRevRecord->getPageId(),
				'rev_timestamp > ' . $dbr->addQuotes(
					$dbr->timestamp( $oldRevRecord->getTimestamp() ) ),
				'rev_timestamp <= ' . $dbr->addQuotes(
					$dbr->timestamp( $newRevRecord->getTimestamp() ) )
			],
			__METHOD__,
			[ 'ORDER BY' => 'rev_timestamp ASC', 'LIMIT' => 251 ], // sanity check
			$revQuery['joins']
		);
		if ( !$dbr->numRows( $res ) ) {
			return [ '', 'review_bad_oldid' ];
		} elseif ( $dbr->numRows( $res ) > 250 ) {
			return [ '', 'review_reject_excessive' ];
		}

		$contribs = SpecialPage::getTitleFor( 'Contributions' )->getPrefixedText();

		$lastRevRecord = null;
		$rejectIds = $rejectAuthors = [];
		$lastRejectAuthor = null;
		$revFactory = MediaWikiServices::getInstance()->getRevisionFactory();
		foreach ( $res as $row ) {
			$revRecord = $revFactory->newRevisionFromRow( $row );

			// skip null edits; if $lastRevRecord is null then this is the first
			// edit being checked, otherwise compare the content to the previous
			// revision record
			if ( $lastRevRecord === null || !$revRecord->hasSameContent( $lastRevRecord ) ) {
				$rejectIds[] = $revRecord->getId();
				$user = $revRecord->getUser();
				$userText = $user ? $user->getName() : '';

				$rejectAuthors[] = $revRecord->isDeleted( RevisionRecord::DELETED_USER )
					? wfMessage( 'rev-deleted-user' )->text()
					: "[[{$contribs}/{$userText}|{$userText}]]";
				// Used for GENDER support for revreview-reject-summary-*
				$lastRejectAuthor = $userText;
			}
			$lastRevRecord = $revRecord;
		}
		$rejectAuthors = array_values( array_unique( $rejectAuthors ) );

		if ( !$rejectIds ) { // all null edits? (this shouldn't happen)
			return [ '', 'review_reject_nulledits' ];
		}

		// List of revisions being undone...
		$oldTitle = Title::newFromLinkTarget( $oldRevRecord->getPageAsLinkTarget() );
		$form .= wfMessage( 'revreview-reject-text-list' )
			->numParams( count( $rejectIds ) )
			->params( $oldTitle->getPrefixedText() )->parse();
		$form .= '<ul>';

		$list = new RevisionList( RequestContext::getMain(), $oldTitle );
		$list->filterByIds( $rejectIds );

		for ( $list->reset(); $list->current(); $list->next() ) {
			$item = $list->current();
			if ( $item->canView() ) {
				$form .= $item->getHTML();
			}
		}
		$form .= '</ul>';

		if ( $newRevRecord->isCurrent() ) {
			// Revision this will revert to (when reverting the top X revs)...
			$form .= wfMessage( 'revreview-reject-text-revto',
				$oldTitle->getPrefixedDBKey(),
				$oldRevRecord->getId(),
				$wgLang->timeanddate( $oldRevRecord->getTimestamp(), true )
			)->parse();
		}

		$comment = $this->form->getComment(); // convenience
		// Determine the default edit summary...
		if ( $oldRevRecord->isDeleted( RevisionRecord::DELETED_USER ) ) {
			$oldRevAuthor = wfMessage( 'rev-deleted-user' )->text();
			$oldRevAuthorUsername = '.';
		} else {
			$oldRevAuthor = $oldRevRecord->getUser() ?
				$oldRevRecord->getUser()->getName() :
				'';
			$oldRevAuthorUsername = $oldRevAuthor;
		}
		// NOTE: *-cur msg wording not safe for (unlikely) edit auto-merge
		$msg = $newRevRecord->isCurrent()
			? 'revreview-reject-summary-cur'
			: 'revreview-reject-summary-old';
		$contLang = MediaWikiServices::getInstance()->getContentLanguage();
		$defaultSummary = wfMessage( $msg,
			$contLang->formatNum( count( $rejectIds ) ),
			$contLang->listToText( $rejectAuthors ),
			$oldRevRecord->getId(),
			$oldRevAuthor,
			count( $rejectAuthors ) === 1 ? $lastRejectAuthor : '.',
			$oldRevAuthorUsername
		)->numParams( count( $rejectAuthors ) )->inContentLanguage()->text();
		// If the message is too big, then fallback to the shorter one
		$colonSeparator = wfMessage( 'colon-separator' )->text();
		$maxLen = CommentStore::COMMENT_CHARACTER_LIMIT - strlen( $colonSeparator ) - strlen( $comment );
		if ( strlen( $defaultSummary ) > $maxLen ) {
			$msg = $newRevRecord->isCurrent()
				? 'revreview-reject-summary-cur-short'
				: 'revreview-reject-summary-old-short';
			$defaultSummary = wfMessage( $msg,
				$contLang->formatNum( count( $rejectIds ) ),
				$oldRevRecord->getId(),
				$oldRevAuthor,
				$oldRevAuthorUsername
			)->inContentLanguage()->text();
		}
		// Append any review comment...
		if ( $comment != '' ) {
			if ( $defaultSummary != '' ) {
				$defaultSummary .= $colonSeparator;
			}
			$defaultSummary .= $comment;
		}

		$form .= '</div>';

		$reviewTitle = SpecialPage::getTitleFor( 'RevisionReview' );
		$form .= Xml::openElement( 'form',
			[ 'method' => 'POST', 'action' => $reviewTitle->getFullUrl() ] );
		$form .= Html::hidden( 'action', 'reject' );
		$form .= Html::hidden( 'wpReject', 1 );
		$form .= Html::hidden( 'wpRejectConfirm', 1 );
		$form .= Html::hidden( 'oldid', $this->form->getOldId() );
		$form .= Html::hidden( 'refid', $this->form->getRefId() );
		$form .= Html::hidden( 'target', $oldTitle->getPrefixedDBKey() );
		$form .= Html::hidden( 'wpEditToken', $this->form->getUser()->getEditToken() );
		$form .= Html::hidden( 'changetime', $newRevRecord->getTimestamp() );
		$form .= Xml::inputLabel( wfMessage( 'revreview-reject-summary' )->text(), 'wpReason',
			'wpReason', 120, $defaultSummary,
			[ 'maxlength' => CommentStore::COMMENT_CHARACTER_LIMIT ] ) . "<br />";
		$form .= Html::input( 'wpSubmit', wfMessage( 'revreview-reject-confirm' )->text(), 'submit' );
		$form .= ' ';
		$form .= Linker::link( $this->form->getPage(), wfMessage( 'revreview-reject-cancel' )->escaped(),
			[ 'onClick' => 'history.back(); return history.length <= 1;' ],
			[ 'oldid' => $this->form->getRefId(), 'diff' => $this->form->getOldId() ] );
		$form .= Xml::closeElement( 'form' );

		return [ $form, true ];
	}
}
