<?php

use MediaWiki\CommentStore\CommentStore;
use MediaWiki\Context\IContextSource;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Request\WebRequest;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\User\User;

/**
 * Main review form UI
 *
 * NOTE: use ONLY for diff-to-stable views and page version views
 */
class RevisionReviewFormUI {
	private User $user;
	private FlaggableWikiPage $article;
	/** A notice inside the review box at the top (HTML) */
	private string $topNotice = '';
	/** A notice inside the review box at the bottom (HTML) */
	private string $bottomNotice = '';
	/** @var array<int,array<string,int>>|null */
	private ?array $templateIds = null;
	private WebRequest $request;
	private RevisionRecord $revRecord;
	private ?RevisionRecord $refRevRecord = null;

	/**
	 * Generates a brief review form for a page
	 */
	public function __construct(
		IContextSource $context,
		FlaggableWikiPage $article,
		RevisionRecord $revRecord
	) {
		$this->user = $context->getUser();
		$this->request = $context->getRequest();
		$this->article = $article;
		$this->revRecord = $revRecord;
	}

	/**
	 * Call this only when the form is shown on a diff:
	 * (a) Shows the "reject" button
	 * (b) Default the rating tags to those of $this->revRecord (if flagged)
	 * @param RevisionRecord $refRevRecord Old revision for diffs ($this->revRecord is the new rev)
	 */
	public function setDiffPriorRevRecord( RevisionRecord $refRevRecord ): void {
		$this->refRevRecord = $refRevRecord;
	}

	/**
	 * Add on a notice inside the review box at the top
	 * @param string $notice HTML to show
	 */
	public function setTopNotice( string $notice ): void {
		$this->topNotice = $notice;
	}

	/**
	 * Add on a notice inside the review box at the bottom
	 * @param string $notice HTML to show
	 */
	public function setBottomNotice( string $notice ): void {
		$this->bottomNotice = $notice;
	}

	/**
	 * Set the template version parameters of what the user is viewing
	 * @param array<int,array<string,int>> $templateIds
	 */
	public function setIncludeVersions( array $templateIds ): void {
		$this->templateIds = $templateIds;
	}

	/**
	 * Generates a brief review form for a page
	 * @return array (html string, error string or true)
	 */
	public function getHtml(): array {
		$revId = $this->revRecord->getId();
		if ( $this->revRecord->isDeleted( RevisionRecord::DELETED_TEXT ) ) {
			return [ '', 'review_bad_oldid' ]; # The revision must be valid and public
		}
		$article = $this->article; // convenience

		$srev = $article->getStableRev();
		# See if the version being displayed is flagged...
		if ( $revId == $article->getStable() ) {
			$frev = $srev; // avoid query
		} else {
			$frev = FlaggedRevision::newFromTitle( $article->getTitle(), $revId );
		}
		$oldTag = $frev ? $frev->getTag() : FlaggedRevs::quickTag();
		$reviewTime = $frev ? $frev->getTimestamp() : ''; // last review of rev

		$priorRevId = $this->refRevRecord ? $this->refRevRecord->getId() : 0;
		# If we are reviewing updates to a page, start off with the stable revision's
		# tag. Otherwise, we just fill them in with the selected revision's tag.
		# @TODO: do we want to carry over info for other diffs?
		if ( $srev && $srev->getRevId() == $priorRevId ) { // diff-to-stable
			$tag = $srev->getTag();
			# Check if user is allowed to renew the stable version.
			# If not, then get the tag for the new revision itself.
			if ( !FlaggedRevs::userCanSetTag( $this->user, $oldTag ) ) {
				$tag = $oldTag;
			}
			# Re-review button is need for template only review case
			$reviewIncludes = ( $srev->getRevId() == $revId && !$article->stableVersionIsSynced() );
		} else { // views
			$tag = $oldTag;
			$reviewIncludes = false; // re-review button not needed
		}

		# Disable form for unprivileged users
		$disabled = !MediaWikiServices::getInstance()->getPermissionManager()
				->quickUserCan( 'review', $this->user, $article->getTitle() ) ||
			!FlaggedRevs::userCanSetTag( $this->user, $tag );

		# Begin form...
		$reviewTitle = SpecialPage::getTitleFor( 'RevisionReview' );
		$action = $reviewTitle->getLocalURL( 'action=submit' );
		$params = [ 'method' => 'post', 'action' => $action, 'id' => 'mw-fr-reviewform' ];
		$form = Html::openElement( 'form', $params ) . "\n";
		$form .= Html::openElement( 'fieldset',
			[ 'class' => 'flaggedrevs_reviewform noprint cdx-card', 'style' => 'font-size: 90%;' ] ) . "\n";
		# Add appropriate legend text
		$legendMsg = $frev ? 'revreview-reflag' : 'revreview-flag';
		$form .= Html::openElement( 'div', [ 'id' => 'mw-fr-reviewformlegend' ] );
		$form .= '<div class="cdx-card__text__title">' . wfMessage( $legendMsg )->escaped() . '</div>';
		# Show explanatory text
		$form .= $this->topNotice;

		# Start rating controls
		$css = $disabled ? 'fr-rating-controls-disabled' : 'fr-rating-controls';
		$form .= Html::openElement( 'p', [ 'class' => $css, 'id' => 'fr-rating-controls' ] ) . "\n";

		# Add main checkboxes/selects
		[ $radios, $radiosShown ] = $this->ratingInputs( $this->user, $tag, $disabled );
		$form .= Html::rawElement(
			'span',
			[ 'id' => 'mw-fr-ratingselects', 'class' => 'fr-rating-options' ],
			$radios
		);

		# Hide comment input if needed
		if ( !$disabled ) {
			$form .= '<div class="cdx-text-input" style="padding-bottom: 5px;">';
			$form .= Html::label( wfMessage( 'revreview-log' )->text(), 'mw-fr-commentbox' );
			$form .= Html::input( 'wpReason', '', 'text',
				[
					'size' => 40,
					'maxlength' => CommentStore::COMMENT_CHARACTER_LIMIT,
					'class' => 'fr-comment-box cdx-text-input__input',
					'id' => 'mw-fr-commentbox'
				]
			);
			$form .= '</div>';
		}

		# Add the submit buttons...
		$rejectId = $this->rejectRefRevId(); // determine if there will be reject button
		$form .= $this->submitButtons( $rejectId, $frev, $disabled, $reviewIncludes, $radiosShown );

		# Show stability log if there is anything interesting...
		if ( $article->isPageLocked() ) {
			$form .= FlaggedRevsHTML::stabilityLogExcerpt( $article->getTitle() );
		}

		# End rating controls
		$form .= Html::closeElement( 'p' ) . "\n";

		# Show explanatory text
		$form .= $this->bottomNotice;

		# Hidden params
		$form .= Html::hidden( 'title', $reviewTitle->getPrefixedText() ) . "\n";
		$form .= Html::hidden( 'target', $article->getTitle()->getPrefixedDBkey() ) . "\n";
		$form .= Html::hidden( 'refid', $priorRevId, [ 'id' => 'mw-fr-input-refid' ] ) . "\n";
		$form .= Html::hidden( 'oldid', $revId, [ 'id' => 'mw-fr-input-oldid' ] ) . "\n";
		$form .= Html::hidden( 'wpEditToken', $this->user->getEditToken() ) . "\n";
		$form .= Html::hidden( 'changetime', $reviewTime,
			[ 'id' => 'mw-fr-input-changetime' ] ) . "\n"; // id for JS
		# Special token to discourage fiddling...
		$key = $this->request->getSessionData( 'wsFlaggedRevsKey' );
		$checkCode = RevisionReviewForm::validationKey( $revId, $key );
		$form .= Html::hidden( 'validatedParams', $checkCode ) . "\n";
		$form .= Html::closeElement( 'fieldset' ) . "\n";
		$form .= Html::closeElement( 'form' ) . "\n";
		return [ $form, true /* ok */ ];
	}

	/**
	 * If the REJECT button should show then get the ID of the last good rev
	 */
	private function rejectRefRevId(): int {
		if ( $this->refRevRecord ) {
			$priorId = $this->refRevRecord->getId();
			if ( $priorId == $this->article->getStable() &&
				$priorId != $this->revRecord->getId() &&
				!$this->revRecord->hasSameContent( $this->refRevRecord )
			) {
				return $priorId; // left rev must be stable and right one newer
			}
		}
		return 0;
	}

	/**
	 * Generate main tag radio buttons for review form if necessary
	 * @param User $user
	 * @param int|null $selected selected tag
	 * @param bool $disabled form disabled
	 * @return array{0:string,1:bool} The tags HTML and whether it contains radio buttons
	 */
	private function ratingInputs( User $user, ?int $selected, bool $disabled ): array {
		if ( FlaggedRevs::binaryFlagging() ) {
			return [ '', false ];
		}

		$quality = FlaggedRevs::getTagName();
		$levels = $this->getRatingFormLevels( $user );
		if ( $disabled || count( $levels ) === 0 ) {
			// Display the value for the tag as text
			return [
				$this->getTagMsg( $quality )->escaped() . ': ' . $this->getTagValueMsg( $selected ?? 0 ),
				false
			];
		}

		$minLevel = array_keys( $levels )[ 0 ];
		$inputName = "wp$quality";
		if ( count( $levels ) === 1 ) {
			// No need to display the single settable level
			return [
				Html::hidden( $inputName, $minLevel ),
				false
			];
		}

		# Determine the level selected by default
		if ( !$selected || !isset( $levels[$selected] ) ) {
			$selected = $minLevel;
		}

		$inputs = $this->getTagMsg( $quality )->escaped() . ":\n";
		foreach ( $levels as $i => $name ) {
			$id = $inputName . $i;
			$item = Html::radio(
				$inputName,
				$i == $selected,
				[ 'value' => $i, 'id' => $id, 'class' => "fr-rating-option-$i cdx-radio__input" ]
			);
			$item .= "\u{00A0}";
			$item .= '<span class="cdx-radio__icon"></span>';
			$item .= Html::label(
				$this->getTagMsg( $name )->text(),
				$id,
				[ 'class' => "fr-rating-option-$i cdx-radio__label" ]
			);
			$inputs .= Html::rawElement( 'span', [ 'class' => 'cdx-radio cdx-radio--inline' ], $item );
		}
		return [ $inputs, true ];
	}

	/**
	 * Get the UI name for a tag
	 */
	private function getTagMsg( string $tag ): Message {
		return wfMessage( "revreview-$tag" );
	}

	/**
	 * Get the UI name for a value of a tag
	 */
	private function getTagValueMsg( int $value ): string {
		$quality = FlaggedRevs::getTagName();
		// Possible message keys that come pre-defined with the extension:
		// * revreview-accuracy-0
		// * revreview-accuracy-1
		// * revreview-accuracy-2
		// * revreview-accuracy-3
		// * revreview-accuracy-4
		$msg = wfMessage( "revreview-$quality-$value" );
		return $msg->isDisabled() ? (string)$value : $msg->escaped();
	}

	/**
	 * Get all available tags for this user
	 * @return array<int,string>
	 */
	private function getRatingFormLevels( User $user ): array {
		$quality = FlaggedRevs::getTagName();
		$labels = []; // applicable tag levels
		for ( $i = 1; $i <= FlaggedRevs::getMaxLevel(); $i++ ) {
			# Some levels may be restricted or not applicable...
			if ( FlaggedRevs::userCanSetValue( $user, $i ) ) {
				$labels[$i] = "$quality-$i";
			}
		}
		return $labels;
	}

	/**
	 * Generates review form submit buttons
	 * @param int $rejectId left rev ID for "reject" on diffs
	 * @param FlaggedRevision|null $frev the flagged revision, if any
	 * @param bool $disabled is the form disabled?
	 * @param bool $reviewIncludes force the review button to be usable?
	 * @param bool $radiosShown Whether any radio buttons appear. If not,
	 *  and there are no pending changes (either on the page itself or in
	 *  transcluded templates), the review button will be disabled.
	 */
	private function submitButtons(
		int $rejectId, ?FlaggedRevision $frev, bool $disabled, bool $reviewIncludes, bool $radiosShown
	): string {
		$disAttrib = [ 'disabled' => 'disabled' ];
		# ACCEPT BUTTON: accept a revision
		# We may want to re-review to change:
		# (a) notes (b) tags (c) pending template changes
		if ( !$radiosShown ) { // just the buttons
			$applicable = ( !$frev || $reviewIncludes ); // no tags/notes
			$needsChange = false; // no state change possible
		} else { // buttons + ratings
			$applicable = true; // tags might change
			$needsChange = ( $frev && !$reviewIncludes );
		}
		// Disable buttons unless state changes in some cases (non-JS compatible)
		$needsChangeAttrib = $needsChange ? [ 'data-mw-fr-review-needs-change' => '' ] : [];
		$s = Html::submitButton( wfMessage( 'revreview-submit-review' )->text(),
			[
				'name'      => 'wpApprove',
				'id'        => 'mw-fr-submit-accept',
				'class'     => 'cdx-button cdx-button--action-progressive',
				'accesskey' => wfMessage( 'revreview-ak-review' )->text(),
				'title'     => wfMessage( 'revreview-tt-flag' )->text() . ' [' .
					wfMessage( 'revreview-ak-review' )->text() . ']'
			] + ( ( $disabled || !$applicable ) ? $disAttrib : [] ) + $needsChangeAttrib
		);
		# REJECT BUTTON: revert from a pending revision to the stable
		if ( $rejectId ) {
			$s .= ' ';
			$s .= Html::submitButton( wfMessage( 'revreview-submit-reject' )->text(),
				[
					'name'  => 'wpReject',
					'id'    => 'mw-fr-submit-reject',
					'class' => 'cdx-button cdx-button--action-destructive',
					'title' => wfMessage( 'revreview-tt-reject' )->text(),
				] + ( $disabled ? $disAttrib : [] )
			);
		}
		# UNACCEPT BUTTON: revoke a revision's acceptance
		# Hide if revision is not flagged
		$s .= ' ';
		$s .= Html::submitButton( wfMessage( 'revreview-submit-unreview' )->text(),
			[
				'name'  => 'wpUnapprove',
				'id'    => 'mw-fr-submit-unaccept',
				'class' => 'cdx-button cdx-button--action-destructive',
				'title' => wfMessage( 'revreview-tt-unflag' )->text(),
				'style' => $frev ? '' : 'display:none'
			] + ( $disabled ? $disAttrib : [] )
		) . "\n";
		return $s;
	}
}
