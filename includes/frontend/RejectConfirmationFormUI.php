<?php

use MediaWiki\CommentStore\CommentStore;
use MediaWiki\Context\RequestContext;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\RevisionStore;
use MediaWiki\RevisionList\RevisionList;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;
use MediaWiki\Xml\Xml;

/**
 * Reject confirmation review form UI
 *
 * TODO inject dependencies
 */
class RejectConfirmationFormUI {
	private RevisionReviewForm $form;
	private ?RevisionRecord $oldRevRecord;
	private ?RevisionRecord $newRevRecord;
	private RevisionStore $revisionStore;

	public function __construct( RevisionReviewForm $form ) {
		$this->form = $form;

		$revisionStore = MediaWikiServices::getInstance()->getRevisionStore();
		$title = $form->getTitle();
		$this->newRevRecord = $revisionStore->getRevisionByTitle( $title, $form->getOldId() );
		$this->oldRevRecord = $revisionStore->getRevisionByTitle( $title, $form->getRefId() );
		$this->revisionStore = $revisionStore;
	}

	/**
	 * Get the "are you sure you want to reject these changes?" form
	 * @return array (html string, error string or true)
	 */
	public function getHtml(): array {
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

		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

		$revQuery = $this->revisionStore->getQueryInfo();
		$res = $dbr->newSelectQueryBuilder()
			->tables( $revQuery['tables'] )
			->fields( $revQuery['fields'] )
			->where( [
				'rev_page' => $oldRevRecord->getPageId(),
				$dbr->expr( 'rev_timestamp', '>', $dbr->timestamp( $oldRevRecord->getTimestamp() ) ),
				$dbr->expr( 'rev_timestamp', '<=', $dbr->timestamp( $newRevRecord->getTimestamp() ) ),
			] )
			->orderBy( 'rev_timestamp' )
			->limit( 251 ) // sanity check
			->joinConds( $revQuery['joins'] )
			->caller( __METHOD__ )
			->fetchResultSet();
		if ( !$res->numRows() ) {
			return [ '', 'review_bad_oldid' ];
		} elseif ( $res->numRows() > 250 ) {
			return [ '', 'review_reject_excessive' ];
		}

		$contribs = SpecialPage::getTitleFor( 'Contributions' )->getPrefixedText();

		$lastRevRecord = null;
		$rejectIds = [];
		$rejectAuthors = [];
		$lastRejectAuthor = null;
		foreach ( $res as $row ) {
			$revRecord = $this->revisionStore->newRevisionFromRow( $row );

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

		$formHTML = '<div class="plainlinks">';
		$formHTML .= wfMessage( 'revreview-reject-text-list' )
			->numParams( count( $rejectIds ) )
			->params( $oldTitle->getPrefixedText() )->parse();
		$formHTML .= '<ul>';

		$list = new RevisionList( RequestContext::getMain(), $oldTitle );
		$list->filterByIds( $rejectIds );

		for ( $list->reset(); $list->current(); $list->next() ) {
			$item = $list->current();
			if ( $item->canView() ) {
				$formHTML .= $item->getHTML();
			}
		}
		$formHTML .= '</ul>';

		if ( $newRevRecord->isCurrent() ) {
			// Revision this will revert to (when reverting the top X revs)...
			$formHTML .= wfMessage( 'revreview-reject-text-revto',
				$oldTitle->getPrefixedDBkey(),
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
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$defaultSummary = wfMessage(
			$msg,
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

		$formHTML .= '</div>';

		$reviewTitle = SpecialPage::getTitleFor( 'RevisionReview' );
		$formHTML .= Html::openElement(
			'form',
			[ 'method' => 'POST', 'action' => $reviewTitle->getLocalURL() ]
		);
		$formHTML .= Html::hidden( 'action', RevisionReviewForm::ACTION_REJECT );
		$formHTML .= Html::hidden( 'wpReject', 1 );
		$formHTML .= Html::hidden( 'wpRejectConfirm', 1 );
		$formHTML .= Html::hidden( 'oldid', $this->form->getOldId() );
		$formHTML .= Html::hidden( 'refid', $this->form->getRefId() );
		$formHTML .= Html::hidden( 'target', $oldTitle->getPrefixedDBkey() );
		$formHTML .= Html::hidden( 'wpEditToken', $this->form->getUser()->getEditToken() );
		$formHTML .= Html::hidden( 'changetime', $newRevRecord->getTimestamp() );
		$formHTML .= Xml::inputLabel(
			wfMessage( 'revreview-reject-summary' )->text(),
			'wpReason',
			'wpReason',
			120,
			$defaultSummary,
			[ 'maxlength' => CommentStore::COMMENT_CHARACTER_LIMIT ]
		);
		$formHTML .= "<br />";
		$formHTML .= Html::input( 'wpSubmit', wfMessage( 'revreview-reject-confirm' )->text(), 'submit' );
		$formHTML .= ' ';
		$formHTML .= $linkRenderer->makeLink(
			$this->form->getTitle(),
			wfMessage( 'revreview-reject-cancel' )->text(),
			[ 'onClick' => 'history.back(); return history.length <= 1;' ],
			[ 'oldid' => $this->form->getRefId(), 'diff' => $this->form->getOldId() ]
		);
		$formHTML .= Html::closeElement( 'form' );

		return [ $formHTML, true ];
	}
}
