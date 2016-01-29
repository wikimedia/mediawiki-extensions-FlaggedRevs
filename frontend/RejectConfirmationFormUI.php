<?php
/**
 * Reject confirmation review form UI
 */
class RejectConfirmationFormUI {
	protected $form, $oldRev, $newRev;

	public function __construct( RevisionReviewForm $form ) {
		$this->form = $form;
		$this->newRev = Revision::newFromTitle( $form->getPage(), $form->getOldId() );
		$this->oldRev = Revision::newFromTitle( $form->getPage(), $form->getRefId() );
	}

	/**
	 * Get the "are you sure you want to reject these changes?" form
	 * @return array (html string, error string or true)
	 */
	public function getHtml() {
		global $wgLang, $wgContLang;
		$status = $this->form->checkTarget();
		if ( $status !== true ) {
			return [ '', $status ]; // not a reviewable existing page
		}
		$oldRev = $this->oldRev; // convenience
		$newRev = $this->newRev; // convenience
		# Do not mess with archived/deleted revisions
		if ( !$oldRev || $newRev->isDeleted( Revision::DELETED_TEXT ) ) {
			return [ '', 'review_bad_oldid' ];
		} elseif ( !$newRev || $newRev->isDeleted( Revision::DELETED_TEXT ) ) {
			return [ '', 'review_bad_oldid' ];
		}

		$form = '<div class="plainlinks">';

		$dbr = wfGetDB( DB_REPLICA );
		$revQuery = Revision::getQueryInfo();
		$res = $dbr->select(
			$revQuery['tables'],
			$revQuery['fields'],
			[
				'rev_page' => $oldRev->getPage(),
				'rev_timestamp > ' . $dbr->addQuotes(
					$dbr->timestamp( $oldRev->getTimestamp() ) ),
				'rev_timestamp <= ' . $dbr->addQuotes(
					$dbr->timestamp( $newRev->getTimestamp() ) )
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

		$lastTextId = 0;
		$rejectIds = $rejectAuthors = [];
		foreach ( $res as $row ) {
			$rev = new Revision( $row );
			if ( $rev->getTextId() != $lastTextId ) { // skip null edits
				$rejectIds[] = $rev->getId();
				$rejectAuthors[] = $rev->isDeleted( Revision::DELETED_USER )
					? wfMessage( 'rev-deleted-user' )->text()
					: "[[{$contribs}/{$rev->getUserText()}|{$rev->getUserText()}]]";
				// Used for GENDER support for revreview-reject-summary-*
				$lastRejectAuthor = $rev->getUserText();
			}
			$lastTextId = $rev->getTextId();
		}
		$rejectAuthors = array_values( array_unique( $rejectAuthors ) );

		if ( !$rejectIds ) { // all null edits? (this shouldn't happen)
			return [ '', 'review_reject_nulledits' ];
		}

		// List of revisions being undone...
		$form .= wfMessage( 'revreview-reject-text-list' )
			->numParams( count( $rejectIds ) )
			->params( $oldRev->getTitle()->getPrefixedText() )->parse();
		$form .= '<ul>';

		$list = new RevisionList( RequestContext::getMain(), $oldRev->getTitle() );
		$list->filterByIds( $rejectIds );

		for ( $list->reset(); $list->current(); $list->next() ) {
			$item = $list->current();
			if ( $item->canView() ) {
				$form .= $item->getHTML();
			}
		}
		$form .= '</ul>';
		if ( $newRev->isCurrent() ) {
			// Revision this will revert to (when reverting the top X revs)...
			$form .= wfMessage( 'revreview-reject-text-revto',
				$oldRev->getTitle()->getPrefixedDBKey(), $oldRev->getId(),
				$wgLang->timeanddate( $oldRev->getTimestamp(), true )
			)->parse();
		}

		$comment = $this->form->getComment(); // convenience
		// Determine the default edit summary...
		if ( $oldRev->isDeleted( Revision::DELETED_USER ) ) {
			$oldRevAuthor = wfMessage( 'rev-deleted-user' )->text();
			$oldRevAuthorUsername = '.';
		} else {
			$oldRevAuthor = $oldRev->getUserText();
			$oldRevAuthorUsername = $oldRevAuthor;
		}
		// NOTE: *-cur msg wording not safe for (unlikely) edit auto-merge
		$msg = $newRev->isCurrent()
			? 'revreview-reject-summary-cur'
			: 'revreview-reject-summary-old';
		$defaultSummary = wfMessage( $msg,
			$wgContLang->formatNum( count( $rejectIds ) ),
			$wgContLang->listToText( $rejectAuthors ),
			$oldRev->getId(),
			$oldRevAuthor,
			count( $rejectAuthors ) === 1 ? $lastRejectAuthor : '.',
			$oldRevAuthorUsername
		)->numParams( count( $rejectAuthors ) )->inContentLanguage()->text();
		// If the message is too big, then fallback to the shorter one
		$colonSeparator = wfMessage( 'colon-separator' )->text();
		$maxLen = 255 - strlen( $colonSeparator ) - strlen( $comment );
		if ( strlen( $defaultSummary ) > $maxLen ) {
			$msg = $newRev->isCurrent()
				? 'revreview-reject-summary-cur-short'
				: 'revreview-reject-summary-old-short';
			$defaultSummary = wfMessage( $msg,
				$wgContLang->formatNum( count( $rejectIds ) ),
				$oldRev->getId(),
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
		$form .= Html::hidden( 'target', $oldRev->getTitle()->getPrefixedDBKey() );
		$form .= Html::hidden( 'wpEditToken', $this->form->getUser()->getEditToken() );
		$form .= Html::hidden( 'changetime', $newRev->getTimestamp() );
		$form .= Xml::inputLabel( wfMessage( 'revreview-reject-summary' )->text(), 'wpReason',
			'wpReason', 120, $defaultSummary, [ 'maxlength' => 200 ] ) . "<br />";
		$form .= Html::input( 'wpSubmit', wfMessage( 'revreview-reject-confirm' )->text(), 'submit' );
		$form .= ' ';
		$form .= Linker::link( $this->form->getPage(), wfMessage( 'revreview-reject-cancel' )->escaped(),
			[ 'onClick' => 'history.back(); return history.length <= 1;' ],
			[ 'oldid' => $this->form->getRefId(), 'diff' => $this->form->getOldId() ] );
		$form .= Xml::closeElement( 'form' );

		return [ $form, true ];
	}
}
