<?php
/**
 * Main review form UI
 *
 * NOTE: use ONLY for diff-to-stable views and page version views
 */
class RevisionReviewFormGUI {
	protected $user, $article, $rev;
	protected $refRev = null;
	protected $topNotice = '';
	protected $templateIDs = null, $imageSHA1Keys = null;

	 /**
	 * Generates a brief review form for a page
	 * @param User $user
	 * @param FlaggedArticle $article
	 * @param Revision $rev
	 */
	public function __construct( User $user, FlaggedArticle $article, Revision $rev ) {
		$this->user = $user;
		$this->article = $article;
		$this->rev = $rev;
	}

	/*
	 * Call this only when the form is shown on a diff:
	 * (a) Shows the "reject" button
	 * (b) Default the rating tags to those of $this->rev (if flagged)
	 * @param Revision $refRev Old revision for diffs ($this->rev is the new rev)
	 */
	public function setDiffPriorRev( Revision $refRev ) {
		$this->refRev = $refRev;
	}

	/*
	 * Add on a notice inside the review box at the top
	 * @param string $topNotice Text to
	 */
	public function setTopNotice( $topNotice ) {
		$this->topNotice = (string)$topNotice;
	}

	/*
	 * Set the template/file version parameters corresponding to what the user is viewing
	 * @param string $topNotice Text to
	 */
	public function setIncludeVersions( array $templateIDs, array $imageSHA1Keys ) {
		$this->templateIDs = $templateIDs;
		$this->imageSHA1Keys = $imageSHA1Keys;
	}

	 /**
	 * Generates a brief review form for a page
	 * @return Array (html string, error string or true)
	 */
	public function getHtml() {
		global $wgOut, $wgLang, $wgParser, $wgEnableParserCache;
		$revId = $this->rev->getId();
		if ( $this->rev->isDeleted( Revision::DELETED_TEXT ) ) {
			return array( '', 'review_bad_oldid' ); # The revision must be valid and public
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

		$priorRevId = $this->refRev ? $this->refRev->getId() : 0;
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
		$disabled = array();
		if ( !$article->getTitle()->quickUserCan( 'review' ) ||
			!$article->getTitle()->quickUserCan( 'edit' ) ||
			!FlaggedRevs::userCanSetFlags( $this->user, $flags ) )
		{
			$disabled = array( 'disabled' => 'disabled' );
		}

		# Begin form...
		$reviewTitle = SpecialPage::getTitleFor( 'RevisionReview' );
		$action = $reviewTitle->getLocalUrl( 'action=submit' );
		$params = array( 'method' => 'post', 'action' => $action, 'id' => 'mw-fr-reviewform' );
		$form = Xml::openElement( 'form', $params );
		$form .= Xml::openElement( 'fieldset',
			array( 'class' => 'flaggedrevs_reviewform noprint' ) );
		# Add appropriate legend text
		$legendMsg = $frev ? 'revreview-reflag' : 'revreview-flag';
		$form .= Xml::openElement( 'legend', array( 'id' => 'mw-fr-reviewformlegend' ) );
		$form .= "<strong>" . wfMsgHtml( $legendMsg ) . "</strong>";
		$form .= Xml::closeElement( 'legend' ) . "\n";
		# Show explanatory text
		$form .= $this->topNotice;
		# Show possible conflict warning msg...
		if ( $priorRevId ) {
			list( $u, $ts ) =
				FRUserActivity::getUserReviewingDiff( $priorRevId, $this->rev->getId() );
		} else {
			list( $u, $ts ) = FRUserActivity::getUserReviewingPage( $this->rev->getPage() );
		}
		if ( $u !== null && $u != $this->user->getName() ) {
			$msg = $priorRevId ? 'revreview-poss-conflict-c' : 'revreview-poss-conflict-p';
			$form .= '<p><span class="fr-under-review">' .
				wfMsgExt( $msg, 'parseinline',
					$u, $wgLang->date( $ts, true ), $wgLang->time( $ts, true ) ) .
				'</span></p>';
		}

		if ( $disabled ) {
			$form .= Xml::openElement( 'div', array( 'class' => 'fr-rating-controls-disabled',
				'id' => 'fr-rating-controls-disabled' ) );
		} else {
			$form .= Xml::openElement( 'div', array( 'class' => 'fr-rating-controls',
				'id' => 'fr-rating-controls' ) );
		}

		# Add main checkboxes/selects
		$form .= Xml::openElement( 'span',
			array( 'id' => 'mw-fr-ratingselects', 'class' => 'fr-rating-options' ) );
		$form .= self::ratingInputs( $this->user, $flags, (bool)$disabled, (bool)$frev );
		$form .= Xml::closeElement( 'span' );

		# Get template/file version info as needed
		list( $templateIDs, $imageSHA1Keys ) = $this->getIncludeVersions();
		# Convert these into flat string params
		list( $templateParams, $imageParams, $fileVersion ) =
			RevisionReviewForm::getIncludeParams( $article, $templateIDs, $imageSHA1Keys );

		$form .= Xml::openElement( 'span', array( 'style' => 'white-space: nowrap;' ) );
		# Hide comment input if needed
		if ( !$disabled ) {
			if ( count( FlaggedRevs::getTags() ) > 1 ) {
				$form .= "<br />"; // Don't put too much on one line
			}
			$form .= "<span id='mw-fr-commentbox' style='clear:both'>" .
				Xml::inputLabel( wfMsg( 'revreview-log' ), 'wpReason', 'wpReason', 40, '',
					array( 'class' => 'fr-comment-box' ) ) . "&#160;&#160;&#160;</span>";
		}
		# Determine if there will be reject button
		$rejectId = $this->rejectRefRevId();
		# Add the submit buttons
		$form .= self::submitButtons( $rejectId, $frev, (bool)$disabled, $reviewIncludes );
		# Show stability log if there is anything interesting...
		if ( $article->isPageLocked() ) {
			$form .= ' ' . FlaggedRevsXML::logToggle( 'revreview-log-toggle-show' );
		}
		$form .= Xml::closeElement( 'span' );
		# ..add the actual stability log body here
	    if ( $article->isPageLocked() ) {
			$form .= FlaggedRevsXML::stabilityLogExcerpt( $article );
		}
		$form .= Xml::closeElement( 'div' ) . "\n";

		# Hidden params
		$form .= Html::hidden( 'title', $reviewTitle->getPrefixedText() ) . "\n";
		$form .= Html::hidden( 'target', $article->getTitle()->getPrefixedDBKey() ) . "\n";
		$form .= Html::hidden( 'refid', $priorRevId ) . "\n";
		$form .= Html::hidden( 'oldid', $revId ) . "\n";
		$form .= Html::hidden( 'action', 'submit' ) . "\n";
		$form .= Html::hidden( 'wpEditToken', $this->user->editToken() ) . "\n";
		$form .= Html::hidden( 'changetime', $reviewTime,
			array( 'id' => 'mw-fr-input-changetime' ) ); // id for JS
		# Add review parameters
		$form .= Html::hidden( 'templateParams', $templateParams ) . "\n";
		$form .= Html::hidden( 'imageParams', $imageParams ) . "\n";
		$form .= Html::hidden( 'fileVersion', $fileVersion ) . "\n";
		# Special token to discourage fiddling...
		$checkCode = RevisionReviewForm::validationKey(
			$templateParams, $imageParams, $fileVersion, $revId
		);
		$form .= Html::hidden( 'validatedParams', $checkCode ) . "\n";

		$form .= Xml::closeElement( 'fieldset' );
		$form .= Xml::closeElement( 'form' );

		return array( $form, true /* ok */ );
	}

	/*
	* If the REJECT button should show then get the ID of the last good rev
	* @return int
	*/
	protected function rejectRefRevId() {
		if ( $this->refRev ) {
			$priorId = $this->refRev->getId();
			if ( $priorId == $this->article->getStable() && $priorId != $this->rev->getId() ) {
				if ( $this->rev->getRawText() != $this->refRev->getRawText() ) {
					return $priorId; // left rev must be stable and right one newer
				}
			}
		}
		return 0;
	}

	/**
	 * @param User $user
	 * @param array $flags, selected flags
	 * @param bool $disabled, form disabled
	 * @param bool $reviewed, rev already reviewed
	 * @returns string
	 * Generates a main tag inputs (checkboxes/radios/selects) for review form
	 */
	protected static function ratingInputs( $user, $flags, $disabled, $reviewed ) {
		# Get all available tags for this page/user
		list( $labels, $minLevels ) = self::ratingFormTags( $user, $flags );
		if ( $labels === false ) {
			$disabled = true; // a tag is unsettable
		}
		# If there are no tags, make one checkbox to approve/unapprove
		if ( FlaggedRevs::binaryFlagging() ) {
			return '';
		}
		$items = array();
		# Build rating form...
		if ( $disabled ) {
			// Display the value for each tag as text
			foreach ( FlaggedRevs::getTags() as $quality ) {
				$selected = isset( $flags[$quality] ) ? $flags[$quality] : 0;
				$items[] = FlaggedRevs::getTagMsg( $quality ) . ": " .
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
					$item .= Xml::tags( 'label', array( 'for' => "wp$quality" ),
						FlaggedRevs::getTagMsg( $quality ) ) . ":\n";
				}
				# If the sum of qualities of all flags is above 6, use drop down boxes.
				# 6 is an arbitrary value choosen according to screen space and usability.
				if ( $size > 6 ) {
					$attribs = array( 'name' => "wp$quality", 'id' => "wp$quality",
						'onchange' => "FlaggedRevsReview.updateRatingForm()" );
					$item .= Xml::openElement( 'select', $attribs ) . "\n";
					foreach ( $levels as $i => $name ) {
						$optionClass = array( 'class' => "fr-rating-option-$i" );
						$item .= Xml::option( FlaggedRevs::getTagMsg( $name ), $i,
							( $i == $selected ), $optionClass ) . "\n";
					}
					$item .= Xml::closeElement( 'select' ) . "\n";
				# If there are more than two levels, current user gets radio buttons
				} elseif ( $numLevels > 2 ) {
					foreach ( $levels as $i => $name ) {
						$attribs = array( 'class' => "fr-rating-option-$i",
							'onchange' => "FlaggedRevsReview.updateRatingForm()" );
						$item .= Xml::radioLabel( FlaggedRevs::getTagMsg( $name ), "wp$quality",
							$i,	"wp$quality" . $i, ( $i == $selected ), $attribs ) . "\n";
					}
				# Otherwise make checkboxes (two levels available for current user)
				} else if ( $numLevels == 2 ) {
					$i = $minLevel;
					$attribs = array( 'class' => "fr-rating-option-$i",
						'onchange' => "FlaggedRevsReview.updateRatingForm()" );
					$attribs = $attribs + array( 'value' => $i );
					$item .= Xml::checkLabel( wfMsg( 'revreview-' . $levels[$i] ),
						"wp$quality", "wp$quality", ( $selected == $i ), $attribs ) . "\n";
				}
				$items[] = $item;
			}
		}
		return implode( '&#160;&#160;&#160;', $items );
	}

	protected static function ratingFormTags( $user, $selected ) {
		$labels = array();
		$minLevels = array();
		# Build up all levels available to user
		foreach ( FlaggedRevs::getDimensions() as $tag => $levels ) {
			if ( isset( $selected[$tag] ) &&
				!FlaggedRevs::userCanSetTag( $user, $tag, $selected[$tag] ) )
			{
				return array( false, false ); // form will have to be disabled
			}
			$labels[$tag] = array(); // applicable tag levels
			$minLevels[$tag] = false; // first non-zero level number
			foreach ( $levels as $i => $msg ) {
				# Some levels may be restricted or not applicable...
				if ( !FlaggedRevs::userCanSetTag( $user, $tag, $i ) ) {
					continue; // skip this level
				} else if ( $i > 0 && !$minLevels[$tag] ) {
					$minLevels[$tag] = $i; // first non-zero level number
				}
				$labels[$tag][$i] = $msg; // set label
			}
			if ( !$minLevels[$tag] ) {
				return array( false, false ); // form will have to be disabled
			}
		}
		return array( $labels, $minLevels );
	}

	/**
	 * Generates review form submit buttons
	 * @param int $rejectId left rev ID for "reject" on diffs
	 * @param FlaggedRevision $frev, the flagged revision, if any
	 * @param bool $disabled, is the form disabled?
	 * @param bool $reviewIncludes, force the review button to be usable?
	 * @returns string
	 */
	protected static function submitButtons(
		$rejectId, $frev, $disabled, $reviewIncludes = false
	) {
		$disAttrib = array( 'disabled' => 'disabled' );
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
		$s = Xml::submitButton( wfMsgHtml( 'revreview-submit-review' ),
			array(
				'name'  	=> 'wpApprove',
				'id' 		=> 'mw-fr-submit-accept',
				'accesskey' => wfMsg( 'revreview-ak-review' ),
				'title' 	=> wfMsg( 'revreview-tt-flag' ) . ' [' .
					wfMsg( 'revreview-ak-review' ) . ']'
			) + ( ( $disabled || !$applicable ) ? $disAttrib : array() )
		);
		# REJECT BUTTON: revert from a pending revision to the stable
		if ( $rejectId ) {
			$s .= ' ';
			$s .= Xml::submitButton( wfMsgHtml( 'revreview-submit-reject' ),
				array(
					'name'  => 'wpReject',
					'id' 	=> 'mw-fr-submit-reject',
					'title' => wfMsg( 'revreview-tt-reject' ),
				) + ( $disabled ? $disAttrib : array() )
			);
		}
		# UNACCEPT BUTTON: revoke a revisions acceptance
		# Hide if revision is not flagged
		$s .= ' ';
		$s .= Xml::submitButton( wfMsgHtml( 'revreview-submit-unreview' ),
			array(
				'name'  => 'wpUnapprove',
				'id' 	=> 'mw-fr-submit-unaccept',
				'title' => wfMsg( 'revreview-tt-unflag' ),
				'style' => $frev ? '' : 'display:none'
			) + ( $disabled ? $disAttrib : array() )
		);
		// Disable buttons unless state changes in some cases (non-JS compatible)
		$s .= "<script type=\"text/javascript\">
			var jsReviewNeedsChange = " . (int)$needsChange . "</script>";
		return $s;
	}

	protected function getIncludeVersions() {
		# Do we need to get inclusion IDs from parser output?
		if ( $this->templateIDs === null || $this->imageSHA1Keys === null ) {
			list( $this->templateIDs, $this->imageSHA1Keys ) =
				RevisionReviewForm::currentIncludeVersions( $this->article, $this->rev );
		}
		return array( $this->templateIDs, $this->imageSHA1Keys );
	}
}
