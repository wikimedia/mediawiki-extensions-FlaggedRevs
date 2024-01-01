<?php

interface FlaggedRevsRevisionReviewFormAfterDoSubmitHook {

	/**
	 * This hook is called after a review has been processed and written to the database.
	 *
	 * @param RevisionReviewForm $form
	 * @param string|bool $status
	 */
	public function onFlaggedRevsRevisionReviewFormAfterDoSubmit( $form, $status );
}
