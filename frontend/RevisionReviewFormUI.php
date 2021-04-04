<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;

/**
 * Main review form UI
 *
 * NOTE: use ONLY for diff-to-stable views and page version views
 */
class RevisionReviewFormUI {
	/** @var User */
	private $user;
	/** @var FlaggableWikiPage */
	private $article;
	/** @var string */
	private $topNotice = '';
	/** @var string */
	private $bottomNotice = '';
	/** @var string[]|false|null */
	private $fileVersion = null;
	/** @var int[][]|null */
	private $templateIds = null;
	/** @var array[]|null */
	private $fileSha1Keys = null;
	/** @var WebRequest */
	private $request;
	/** @var OutputPage */
	private $out;
	/** @var RevisionRecord */
	private $revRecord;
	/** @var RevisionRecord|null */
	private $refRevRecord = null;

	/**
	 * Generates a brief review form for a page
	 * @param IContextSource $context
	 * @param FlaggableWikiPage $article
	 * @param RevisionRecord $revRecord
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
	public function setDiffPriorRevRecord( RevisionRecord $refRevRecord ) {
		$this->refRevRecord = $refRevRecord;
	}

	/**
	 * Add on a notice inside the review box at the top
	 * @param string $notice HTML to show
	 */
	public function setTopNotice( $notice ) {
		$this->topNotice = (string)$notice;
	}

	/**
	 * Add on a notice inside the review box at the top
	 * @param string $notice HTML to show
	 */
	public function setBottomNotice( $notice ) {
		$this->bottomNotice = (string)$notice;
	}

	/**
	 * Set the file version parameters of what the user is viewing
	 * @param string[]|null $version ('time' => MW timestamp, 'sha1' => sha1)
	 */
	public function setFileVersion( $version ) {
		$this->fileVersion = is_array( $version ) ? $version : false;
	}

	/**
	 * Set the template/file version parameters of what the user is viewing
	 * @param int[][] $templateIds
	 * @param array[] $fileSha1Keys
	 */
	public function setIncludeVersions( array $templateIds, array $fileSha1Keys ) {
		$this->templateIds = $templateIds;
		$this->fileSha1Keys = $fileSha1Keys;
	}

	/**
	 * Generates a brief review form for a page
	 * @return array (html string, error string or true)
	 */
	public function getHtml() {
		global $wgLang;
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
		$oldFlags = $frev
			? $frev->getTags() // existing tags
			: FlaggedRevs::quickTags( FR_CHECKED ); // basic tags
		$reviewTime = $frev ? $frev->getTimestamp() : ''; // last review of rev

		$priorRevId = $this->refRevRecord ? $this->refRevRecord->getId() : 0;
		# If we are reviewing updates to a page, start off with the stable revision's
		# flags. Otherwise, we just fill them in with the selected revision's flags.
		# @TODO: do we want to carry over info for other diffs?
		if ( $srev && $srev->getRevId() == $priorRevId ) { // diff-to-stable
			$flags = $srev->getTags();
			# Check if user is allowed to renew the stable version.
			# If not, then get the flags for the new revision itself.
			if ( !FlaggedRevs::userCanSetFlags( $this->user, $oldFlags ) ) {
				$flags = $oldFlags;
			}
			# Re-review button is need for template/file only review case
			$reviewIncludes = ( $srev->getRevId() == $revId && !$article->stableVersionIsSynced() );
		} else { // views
			$flags = $oldFlags;
			$reviewIncludes = false; // re-review button not needed
		}

		# Disable form for unprivileged users
		$disabled = [];
		if ( !MediaWikiServices::getInstance()->getPermissionManager()
				->quickUserCan( 'review', $this->user, $article->getTitle() ) ||
			!FlaggedRevs::userCanSetFlags( $this->user, $flags )
		) {
			$disabled = [ 'disabled' => 'disabled' ];
		}

		# Begin form...
		$reviewTitle = SpecialPage::getTitleFor( 'RevisionReview' );
		$action = $reviewTitle->getLocalURL( 'action=submit' );
		$params = [ 'method' => 'post', 'action' => $action, 'id' => 'mw-fr-reviewform' ];
		$form = Xml::openElement( 'form', $params ) . "\n";
		$form .= Xml::openElement( 'fieldset',
			[ 'class' => 'flaggedrevs_reviewform noprint' ] ) . "\n";
		# Add appropriate legend text
		$legendMsg = $frev ? 'revreview-reflag' : 'revreview-flag';
		$form .= Xml::openElement( 'legend', [ 'id' => 'mw-fr-reviewformlegend' ] );
		$form .= "<strong>" . wfMessage( $legendMsg )->escaped() . "</strong>";
		$form .= Xml::closeElement( 'legend' ) . "\n";
		# Show explanatory text
		$form .= $this->topNotice;

		# Check if anyone is reviewing this already and
		# show a conflict warning message as needed...
		if ( $priorRevId ) {
			list( $u, $ts ) = FRUserActivity::getUserReviewingDiff(
				$priorRevId,
				$this->revRecord->getId()
			);
		} else {
			list( $u, $ts ) = FRUserActivity::getUserReviewingPage(
				$this->revRecord->getPageId()
			);
		}
		$form .= Xml::openElement( 'p' );
		// Page under review (and not by this user)...
		if ( $u !== null && $u != $this->user->getName() ) {
			$form .= '<span class="fr-under-review">';
			$msg = $priorRevId
				? 'revreview-poss-conflict-c'
				: 'revreview-poss-conflict-p';
			$form .= wfMessage( $msg, $u, $wgLang->date( $ts, true ), $wgLang->time( $ts, true ) )
				->parse();
			$form .= "</span>";
		// Page not under review or under review by this user...
		} elseif ( !$frev ) { // rev not already reviewed
			$form .= '<span id="mw-fr-reviewing-status" style="display:none;"></span>'; // JS widget
		}
		$form .= Xml::closeElement( 'p' ) . "\n";

		# Start rating controls
		$css = $disabled ? 'fr-rating-controls-disabled' : 'fr-rating-controls';
		$form .= Xml::openElement( 'p', [ 'class' => $css, 'id' => 'fr-rating-controls' ] ) . "\n";

		# Add main checkboxes/selects
		$form .= Xml::openElement( 'span',
			[ 'id' => 'mw-fr-ratingselects', 'class' => 'fr-rating-options' ] ) . "\n";
		$form .= $this->ratingInputs( $this->user, $flags, (bool)$disabled, (bool)$frev ) . "\n";
		$form .= Xml::closeElement( 'span' ) . "\n";

		# Don't put buttons & comment field on the same line as tag inputs.
		if ( !$disabled && !FlaggedRevs::binaryFlagging() ) { // $disabled => no comment/buttons
			$form .= "<br />";
		}

		# Start comment & buttons
		$form .= Xml::openElement( 'span', [ 'id' => 'mw-fr-confirmreview' ] ) . "\n";

		# Hide comment input if needed
		if ( !$disabled ) {
			$form .= Xml::inputLabel(
				wfMessage( 'revreview-log' )->text(), 'wpReason', 'mw-fr-commentbox', 40, '',
				[ 'maxlength' => CommentStore::COMMENT_CHARACTER_LIMIT, 'class' => 'fr-comment-box' ]
			);
		}

		# Add the submit buttons...
		$rejectId = $this->rejectRefRevId(); // determine if there will be reject button
		$form .= $this->submitButtons( $rejectId, $frev, (bool)$disabled, $reviewIncludes, $this->out );

		# Show stability log if there is anything interesting...
		if ( $article->isPageLocked() ) {
			$form .= ' ' . FlaggedRevsXML::logToggle();
		}

		# End comment & buttons
		$form .= Xml::closeElement( 'span' ) . "\n";

		# ..add the actual stability log body here
		if ( $article->isPageLocked() ) {
			$form .= FlaggedRevsXML::stabilityLogExcerpt( $article );
		}

		# End rating controls
		$form .= Xml::closeElement( 'p' ) . "\n";

		# Show explanatory text
		$form .= $this->bottomNotice;

		# Get the file version used for File: pages as needed
		$fileKey = $this->getFileVersion();
		# Get template/file version info as needed
		[ $templateIds, $fileSha1Keys ] = $this->getIncludeVersions();
		# Convert these into flat string params
		[ $templateParams, $imageParams, $fileVersion ] =
			RevisionReviewForm::getIncludeParams( $templateIds, $fileSha1Keys, $fileKey );

		# Hidden params
		$form .= Html::hidden( 'title', $reviewTitle->getPrefixedText() ) . "\n";
		$form .= Html::hidden( 'target', $article->getTitle()->getPrefixedDBkey() ) . "\n";
		$form .= Html::hidden( 'refid', $priorRevId, [ 'id' => 'mw-fr-input-refid' ] ) . "\n";
		$form .= Html::hidden( 'oldid', $revId, [ 'id' => 'mw-fr-input-oldid' ] ) . "\n";
		$form .= Html::hidden( 'wpEditToken', $this->user->getEditToken() ) . "\n";
		$form .= Html::hidden( 'changetime', $reviewTime,
			[ 'id' => 'mw-fr-input-changetime' ] ) . "\n"; // id for JS
		$form .= Html::hidden( 'userreviewing', (int)( $u === $this->user->getName() ),
			[ 'id' => 'mw-fr-user-reviewing' ] ) . "\n"; // id for JS
		# Add review parameters
		$form .= Html::hidden( 'templateParams', $templateParams ) . "\n";
		$form .= Html::hidden( 'imageParams', $imageParams ) . "\n";
		$form .= Html::hidden( 'fileVersion', $fileVersion ) . "\n";
		# Special token to discourage fiddling...
		$key = $this->request->getSessionData( 'wsFlaggedRevsKey' );
		$checkCode = RevisionReviewForm::validationKey(
			$templateParams, $imageParams, $fileVersion, $revId, $key
		);
		$form .= Html::hidden( 'validatedParams', $checkCode ) . "\n";

		$form .= Xml::closeElement( 'fieldset' ) . "\n";
		$form .= Xml::closeElement( 'form' ) . "\n";

		return [ $form, true /* ok */ ];
	}

	/**
	 * If the REJECT button should show then get the ID of the last good rev
	 * @return int
	 */
	private function rejectRefRevId() {
		if ( $this->refRevRecord ) {
			$priorId = $this->refRevRecord->getId();
			if ( $priorId == $this->article->getStable() &&
				$priorId != $this->revRecord->getId()
			) {
				if ( !$this->revRecord->hasSameContent( $this->refRevRecord ) ) {
					return $priorId; // left rev must be stable and right one newer
				}
			}
		}
		return 0;
	}

	/**
	 * @param User $user
	 * @param int[] $flags selected flags
	 * @param bool $disabled form disabled
	 * @param bool $reviewed rev already reviewed
	 * @return string
	 * Generates a main tag inputs (checkboxes/radios/selects) for review form
	 */
	private function ratingInputs( $user, $flags, $disabled, $reviewed ) {
		# Get all available tags for this page/user
		list( $labels, $minLevels ) = $this->ratingFormTags( $user, $flags );
		if ( $labels === false ) {
			$disabled = true; // a tag is unsettable
		}
		# If there are no tags, make one checkbox to approve/unapprove
		if ( FlaggedRevs::binaryFlagging() ) {
			return '';
		}
		$items = [];
		# Build rating form...
		if ( $disabled ) {
			if ( !FlaggedRevs::useOnlyIfProtected() ) {
				// Display the value for each tag as text
				$quality = FlaggedRevs::getTagName();
				$selected = $flags[$quality] ?? 0;
				$items[] = FlaggedRevs::getTagMsg( $quality )->escaped() . ": " .
					FlaggedRevs::getTagValueMsg( $quality, $selected );
			}
		} else {
			$size = count( $labels, 1 ) - count( $labels );
			foreach ( $labels as $quality => $levels ) {
				$item = '';
				$numLevels = count( $levels );
				$minLevel = $minLevels[$quality];
				# Determine the level selected by default
				if ( !empty( $flags[$quality] ) && isset( $levels[$flags[$quality]] ) ) {
					$selected = $flags[$quality]; // valid non-zero value
				} else {
					$selected = $minLevel;
				}
				# Show label as needed
				if ( !FlaggedRevs::binaryFlagging() ) {
					$item .= Xml::tags( 'label', [ 'for' => "wp$quality" ],
						FlaggedRevs::getTagMsg( $quality )->escaped() ) . ":\n";
				}
				# If the sum of qualities of all flags is above 6, use drop down boxes.
				# 6 is an arbitrary value choosen according to screen space and usability.
				if ( $size > 6 ) {
					$attribs = [ 'name' => "wp$quality", 'id' => "wp$quality" ];
					$item .= Xml::openElement( 'select', $attribs ) . "\n";
					foreach ( $levels as $i => $name ) {
						$optionClass = [ 'class' => "fr-rating-option-$i" ];
						$item .= Xml::option( FlaggedRevs::getTagMsg( $name )->text(), $i,
							( $i == $selected ), $optionClass ) . "\n";
					}
					$item .= Xml::closeElement( 'select' ) . "\n";
				# If there are more than two levels, current user gets radio buttons
				} elseif ( $numLevels > 2 ) {
					foreach ( $levels as $i => $name ) {
						$attribs = [ 'class' => "fr-rating-option-$i" ];
						$item .= Xml::radioLabel( FlaggedRevs::getTagMsg( $name )->text(), "wp$quality",
							$i, "wp$quality" . $i, ( $i == $selected ), $attribs ) . "\n";
					}
				# Otherwise make checkboxes (two levels available for current user)
				} elseif ( $numLevels == 2 ) {
					$i = $minLevel;
					$attribs = [ 'class' => "fr-rating-option-$i" ];
					$attribs += [ 'value' => $i ];
					$item .= Xml::checkLabel( wfMessage( 'revreview-' . $levels[$i] )->text(),
						"wp$quality", "wp$quality", ( $selected == $i ), $attribs ) . "\n";
				}
				$items[] = $item;
			}
		}
		return implode( '&#160;&#160;&#160;', $items );
	}

	/**
	 * @param User $user
	 * @param int[] $selected
	 * @return array[]|false[]
	 */
	private function ratingFormTags( $user, $selected ) {
		$labels = [];
		$minLevels = [];
		if ( FlaggedRevs::useOnlyIfProtected() ) {
			return [ $labels, $minLevels ];
		}
		$tag = FlaggedRevs::getTagName();
		$levels = FlaggedRevs::getLevels();
		if ( isset( $selected[$tag] ) &&
			!FlaggedRevs::userCanSetTag( $user, $tag, $selected[$tag] )
		) {
			return [ false, false ]; // form will have to be disabled
		}
		$labels[$tag] = []; // applicable tag levels
		$minLevels[$tag] = false; // first non-zero level number
		foreach ( $levels as $i => $msg ) {
			# Some levels may be restricted or not applicable...
			if ( !FlaggedRevs::userCanSetTag( $user, $tag, $i ) ) {
				continue; // skip this level
			} elseif ( $i > 0 && !$minLevels[$tag] ) {
				$minLevels[$tag] = $i; // first non-zero level number
			}
			$labels[$tag][$i] = $msg; // set label
		}
		if ( !$minLevels[$tag] ) {
			return [ false, false ]; // form will have to be disabled
		}
		return [ $labels, $minLevels ];
	}

	/**
	 * Generates review form submit buttons
	 * @param int $rejectId left rev ID for "reject" on diffs
	 * @param FlaggedRevision $frev the flagged revision, if any
	 * @param bool $disabled is the form disabled?
	 * @param bool $reviewIncludes force the review button to be usable?
	 * @param OutputPage $out
	 * @return string
	 */
	private function submitButtons(
		$rejectId, $frev, $disabled, $reviewIncludes, OutputPage $out
	) {
		$disAttrib = [ 'disabled' => 'disabled' ];
		# ACCEPT BUTTON: accept a revision
		# We may want to re-review to change:
		# (a) notes (b) tags (c) pending template/file changes
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
	 * @return string[]|false
	 */
	private function getFileVersion() {
		if ( $this->fileVersion === null ) {
			throw new Exception(
				"File page file version not provided to review form; call setFileVersion()."
			);
		}
		return $this->fileVersion;
	}

	/**
	 * @return array[]
	 */
	private function getIncludeVersions() {
		if ( $this->templateIds === null || $this->fileSha1Keys === null ) {
			throw new Exception(
				"Template or file versions not provided to review form; call setIncludeVersions()."
			);
		}
		return [ $this->templateIds, $this->fileSha1Keys ];
	}
}
