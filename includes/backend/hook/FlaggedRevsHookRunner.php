<?php

use MediaWiki\Extension\FlaggedRevs\Backend\Hook\FlaggedRevsStabilitySettingsChangedHook;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Page\Hook\RevisionFromEditCompleteHook;
use MediaWiki\Page\WikiPage;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\User\UserIdentity;

/**
 * Handle running FlaggedRevs's hooks
 * @author DannyS712
 */
class FlaggedRevsHookRunner implements
	RevisionFromEditCompleteHook,
	FlaggedRevsRevisionReviewFormAfterDoSubmitHook,
	FlaggedRevsStabilitySettingsChangedHook
{

	public function __construct( private readonly HookContainer $hookContainer ) {
	}

	/**
	 * @note Core hook that is run
	 *
	 * @param WikiPage $wikiPage WikiPage edited
	 * @param RevisionRecord $rev New revision
	 * @param int|bool $originalRevId If the edit restores or repeats an earlier revision (such as a
	 *   rollback or a null revision), the ID of that earlier revision. False otherwise.
	 *   (Used to be called $baseID.)
	 * @param UserIdentity $user Editing user
	 * @param string[] &$tags Tags to apply to the edit and recent change. This is empty, and
	 *   replacement is ignored, in the case of import or page move
	 */
	public function onRevisionFromEditComplete( $wikiPage, $rev, $originalRevId,
		$user, &$tags
	) {
		$this->hookContainer->run(
			'RevisionFromEditComplete',
			[ $wikiPage, $rev, $originalRevId, $user, &$tags ]
		);
	}

	/**
	 * @param RevisionReviewForm $form
	 * @param string|bool $status
	 */
	public function onFlaggedRevsRevisionReviewFormAfterDoSubmit( $form, $status ) {
		$this->hookContainer->run(
			'FlaggedRevsRevisionReviewFormAfterDoSubmit',
			[ $form, $status ]
		);
	}

	/** @inheritDoc */
	public function onFlaggedRevsStabilitySettingsChanged( $title, $newStabilitySettings, $userIdentity, $reason ) {
		$this->hookContainer->run(
			'FlaggedRevsStabilitySettingsChanged',
			[ $title, $newStabilitySettings, $userIdentity, $reason ]
		);
	}
}
