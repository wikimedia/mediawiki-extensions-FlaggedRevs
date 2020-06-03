<?php

interface FlaggedRevsFRGenericSubmitFormReadyHook {

	/**
	 * When a FRGenericSubmitForm (subclass) is about to be submitted. Return boolean 'false'
	 * to skip internal processing and use $result to return an error message key
	 *
	 * @param FRGenericSubmitForm $form
	 * @param string &$result
	 * @return bool|void
	 */
	public function onFlaggedRevsFRGenericSubmitFormReady( $form, &$result );
}
