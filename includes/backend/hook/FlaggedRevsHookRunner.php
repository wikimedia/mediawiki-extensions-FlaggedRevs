<?php

use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Page\Hook\RevisionFromEditCompleteHook;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\User\UserIdentity;

/**
 * Handle running FlaggedRevs's hooks
 * @author DannyS712
 */
class FlaggedRevsHookRunner implements
	RevisionFromEditCompleteHook,
	FlaggedRevsRevisionReviewFormAfterDoSubmitHook
{

	/** @var HookContainer */
	private $hookContainer;

	/**
	 * @param HookContainer $hookContainer
	 */
	public function __construct( HookContainer $hookContainer ) {
		$this->hookContainer = $hookContainer;
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

}
