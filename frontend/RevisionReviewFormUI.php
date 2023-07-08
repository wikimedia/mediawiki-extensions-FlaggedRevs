<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;

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
	private OutputPage $out;
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
		$this->out = $context->getOutput();
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
		$form = Xml::openElement( 'form', $params ) . "\n";
		$form .= Xml::openElement( 'fieldset',
			[ 'class' => 'flaggedrevs_reviewform noprint cdx-card', 'style' => 'font-size: 90%;' ] ) . "\n";
		# Add appropriate legend text
		$legendMsg = $frev ? 'revreview-reflag' : 'revreview-flag';
		$form .= Xml::openElement( 'span', [ 'id' => 'mw-fr-reviewformlegend' ] );
		$form .= '<span class="cdx-card__text__title">' . wfMessage( $legendMsg )->escaped() . '</span>';
		# Show explanatory text
		$form .= $this->topNotice;

		# Start rating controls
		$css = $disabled ? 'fr-rating-controls-disabled' : 'fr-rating-controls';
		$form .= Xml::openElement( 'p', [ 'class' => $css, 'id' => 'fr-rating-controls' ] ) . "\n";

		# Add main checkboxes/selects
		$form .= Xml::openElement( 'span',
			[ 'id' => 'mw-fr-ratingselects', 'class' => 'fr-rating-options' ] ) . "\n";
		$form .= $this->ratingInputs( $this->user, $tag, $disabled ) . "\n";
		$form .= Xml::closeElement( 'span' ) . "\n";

		# Hide comment input if needed
		if ( !$disabled ) {
			$form .= '<div class="cdx-text-input" style="padding-bottom: 5px;">';
			$form .= Xml::label( wfMessage( 'revreview-log' )->text(), 'mw-fr-commentbox' );
			$form .= Xml::input(
				'wpReason', 40, '',
				[
					'maxlength' => CommentStore::COMMENT_CHARACTER_LIMIT,
					'class' => 'fr-comment-box cdx-text-input__input',
					'id' => 'mw-fr-commentbox'
				]
			);
			$form .= '</div>';
		}

		# Add the submit buttons...
		$rejectId = $this->rejectRefRevId(); // determine if there will be reject button
		$form .= $this->submitButtons( $rejectId, $frev, $disabled, $reviewIncludes, $this->out );

		# Show stability log if there is anything interesting...
		if ( $article->isPageLocked() ) {
			$form .= ' ' . FlaggedRevsXML::logToggle();
		}

		# ..add the actual stability log body here
		if ( $article->isPageLocked() ) {
			$form .= FlaggedRevsXML::stabilityLogExcerpt( $article->getTitle() );
		}

		# End rating controls
		$form .= Xml::closeElement( 'p' ) . "\n";

		# Show explanatory text
		$form .= $this->bottomNotice;

		# Get template version info as needed
		$templateIds = $this->getIncludeVersions();
		# Convert these into flat string params
		$templateParams = RevisionReviewForm::getIncludeParams( $templateIds );

		# Hidden params
		$form .= Html::hidden( 'title', $reviewTitle->getPrefixedText() ) . "\n";
		$form .= Html::hidden( 'target', $article->getTitle()->getPrefixedDBkey() ) . "\n";
		$form .= Html::hidden( 'refid', $priorRevId, [ 'id' => 'mw-fr-input-refid' ] ) . "\n";
		$form .= Html::hidden( 'oldid', $revId, [ 'id' => 'mw-fr-input-oldid' ] ) . "\n";
		$form .= Html::hidden( 'wpEditToken', $this->user->getEditToken() ) . "\n";
		$form .= Html::hidden( 'changetime', $reviewTime,
			[ 'id' => 'mw-fr-input-changetime' ] ) . "\n"; // id for JS
		# Add review parameters
		$form .= Html::hidden( 'templateParams', $templateParams ) . "\n";
		# Special token to discourage fiddling...
		$key = $this->request->getSessionData( 'wsFlaggedRevsKey' );
		$checkCode = RevisionReviewForm::validationKey( $templateParams, $revId, $key );
		$form .= Html::hidden( 'validatedParams', $checkCode ) . "\n";

		$form .= Xml::closeElement( 'fieldset' ) . "\n";
		$form .= Xml::closeElement( 'form' ) . "\n";
		$form .= Xml::closeElement( 'span' ) . "\n";
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
	 * Generates a main tag inputs (checkboxes/radios/selects) for review form
	 * @param User $user
	 * @param int|null $selected selected tag
	 * @param bool $disabled form disabled
	 */
	private function ratingInputs( User $user, ?int $selected, bool $disabled ): string {
		if ( FlaggedRevs::binaryFlagging() ) {
			return '';
		}

		$quality = FlaggedRevs::getTagName();
		# Get all available tags for this page/user
		list( $levels, $minLevel ) = $this->getRatingFormLevels( $user, $selected );
		if ( $disabled || $minLevel === null ) {
			// Display the value for the tag as text
			return $this->getTagMsg( $quality )->escaped() . ": " .
				$this->getTagValueMsg( $selected ?? 0 );
		}

		# Determine the level selected by default
		if ( !$selected || !isset( $levels[$selected] ) ) {
			$selected = $minLevel;
		}
		# Show label as needed
		$item = Xml::tags( 'label', [ 'for' => "wp$quality" ],
			$this->getTagMsg( $quality )->escaped() ) . ":\n";
		# If there are more than two levels, current user gets radio buttons
		if ( count( $levels ) > 2 ) {
			foreach ( $levels as $i => $name ) {
				$item .= Xml::openElement( 'span', [ 'class' => 'cdx-radio cdx-radio--inline' ] );
				$item .= Xml::radio(
					"wp$quality",
					$i,
					( $i == $selected ),
					[ 'id' => "wp$quality" . $i, 'class' => "fr-rating-option-$i cdx-radio__input" ] ) .
					"\u{00A0}";
				$item .= '<span class="cdx-radio__icon"></span>';
				$item .= Xml::label(
					$this->getTagMsg( $name )->text(),
					"wp$quality" . $i,
					[ 'class' => "fr-rating-option-$i cdx-radio__label" ]
				);
				$item .= Xml::closeElement( 'span' );
			}
		# Otherwise make checkboxes (two levels available for current user)
		} elseif ( count( $levels ) == 2 ) {
			$i = $minLevel;
			$item .= Xml::openElement( 'span', [ 'class' => 'cdx-checkbox' ] );
			$item .= Xml::check(
					"wp$quality",
					( $i == $selected ),
					[ 'id' => "wp$quality", 'class' => "fr-rating-option-$i cdx-checkbox__input", 'value' => $i ] ) .
				"\u{00A0}";
			$item .= '<span class="cdx-checkbox__icon"></span>';
			$item .= Xml::label(
				wfMessage( 'revreview-' . $levels[$i] )->text(),
				"wp$quality" . $i,
				[ 'class' => "fr-rating-option-$i cdx-radio__label" ]
			);
			$item .= Xml::closeElement( 'span' );
		}
		return $item;
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
		$levels = FlaggedRevs::getLevels();
		if ( isset( $levels[$value] ) ) {
			return wfMessage( 'revreview-' . $levels[$value] )->escaped();
		}
		return '';
	}

	/**
	 * @return array [ array<int,string>|null $labels, int|null $minLevel ]
	 *  If `$minLevel` is null, the user cannot set the rating
	 */
	private function getRatingFormLevels( User $user, ?int $selected ): array {
		if ( $selected !== null && !FlaggedRevs::userCanSetValue( $user, $selected ) ) {
			return [ null, null ]; // form will have to be disabled
		}
		$labels = []; // applicable tag levels
		$minLevel = null; // first non-zero level number, if any
		foreach ( FlaggedRevs::getLevels() as $i => $msg ) {
			# Some levels may be restricted or not applicable...
			if ( !FlaggedRevs::userCanSetValue( $user, $i ) ) {
				continue; // skip this level
			} elseif ( $i > 0 && !$minLevel ) {
				$minLevel = $i; // first non-zero level number
			}
			$labels[$i] = $msg; // set label
		}
		return [ $labels, $minLevel ];
	}

	/**
	 * Generates review form submit buttons
	 * @param int $rejectId left rev ID for "reject" on diffs
	 * @param FlaggedRevision|null $frev the flagged revision, if any
	 * @param bool $disabled is the form disabled?
	 * @param bool $reviewIncludes force the review button to be usable?
	 * @param OutputPage $out
	 */
	private function submitButtons(
		int $rejectId, ?FlaggedRevision $frev, bool $disabled, bool $reviewIncludes, OutputPage $out
	): string {
		$disAttrib = [ 'disabled' => 'disabled' ];
		# ACCEPT BUTTON: accept a revision
		# We may want to re-review to change:
		# (a) notes (b) tags (c) pending template changes
		if ( FlaggedRevs::binaryFlagging() ) { // just the buttons
			$applicable = ( !$frev || $reviewIncludes ); // no tags/notes
			$needsChange = false; // no state change possible
		} else { // buttons + ratings
			$applicable = true; // tags might change
			$needsChange = ( $frev && !$reviewIncludes );
		}
		$s = Xml::submitButton( wfMessage( 'revreview-submit-review' )->text(),
			[
				'name'      => 'wpApprove',
				'id'        => 'mw-fr-submit-accept',
				'class' => 'cdx-button cdx-button--action-progressive',
				'accesskey' => wfMessage( 'revreview-ak-review' )->text(),
				'title'     => wfMessage( 'revreview-tt-flag' )->text() . ' [' .
					wfMessage( 'revreview-ak-review' )->text() . ']'
			] + ( ( $disabled || !$applicable ) ? $disAttrib : [] )
		);
		# REJECT BUTTON: revert from a pending revision to the stable
		if ( $rejectId ) {
			$s .= ' ';
			$s .= Xml::submitButton( wfMessage( 'revreview-submit-reject' )->text(),
				[
					'name'  => 'wpReject',
					'id'    => 'mw-fr-submit-reject',
					'class' => 'cdx-button cdx-button--action-destructive',
					'title' => wfMessage( 'revreview-tt-reject' )->text(),
				] + ( $disabled ? $disAttrib : [] )
			);
		}
		# UNACCEPT BUTTON: revoke a revisions acceptance
		# Hide if revision is not flagged
		$s .= ' ';
		$s .= Xml::submitButton( wfMessage( 'revreview-submit-unreview' )->text(),
			[
				'name'  => 'wpUnapprove',
				'id'    => 'mw-fr-submit-unaccept',
				'class' => 'cdx-button cdx-button--action-destructive',
				'title' => wfMessage( 'revreview-tt-unflag' )->text(),
				'style' => $frev ? '' : 'display:none'
			] + ( $disabled ? $disAttrib : [] )
		) . "\n";
		// Disable buttons unless state changes in some cases (non-JS compatible)
		$s .= Html::inlineScript(
			"var jsReviewNeedsChange = " . (int)$needsChange . ";",
			$out->getCSP()->getNonce()
		);
		return $s;
	}

	/**
	 * @return array<int,array<string,int>>
	 */
	private function getIncludeVersions(): array {
		if ( $this->templateIds === null ) {
			throw new LogicException(
				"Template versions not provided to review form; call setIncludeVersions()."
			);
		}
		return $this->templateIds;
	}
}
