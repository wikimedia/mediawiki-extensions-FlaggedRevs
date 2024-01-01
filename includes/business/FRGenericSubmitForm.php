<?php

use MediaWiki\User\User;

/**
 * Class containing generic form business logic
 * Note: edit tokens are the responsibility of the caller
 * Usage: (a) set ALL form params before doing anything else
 *        (b) call ready() when all params are set
 *        (c) call preload() OR submit() as needed
 */
abstract class FRGenericSubmitForm {
	# Notify functions when we are submitting
	protected const FOR_SUBMISSION = 1;

	/* Internal form state */

	# Params not given yet
	protected const FORM_UNREADY = 0;
	# Params given and ready to submit
	protected const FORM_READY = 1;
	# Params pre-loaded (likely from replica DB)
	protected const FORM_PRELOADED = 2;
	# Form submitted
	protected const FORM_SUBMITTED = 3;

	/** @var int Form state (disallows bad operations) */
	private $state = self::FORM_UNREADY;

	/** @var User User performing the action */
	protected $user;

	/**
	 * @param User $user
	 */
	final public function __construct( User $user ) {
		$this->user = $user;
		$this->initialize();
	}

	/**
	 * Initialize any parameters on construction
	 * @return void
	 */
	protected function initialize() {
	}

	/**
	 * Get the submitting user
	 * @return User
	 */
	final public function getUser() {
		return $this->user;
	}

	/**
	 * Get the internal form state
	 * @return int
	 */
	final protected function getState() {
		return $this->state;
	}

	/**
	 * Signal that inputs are all given (via accessors)
	 * @return true|string true on success, error string on target failure
	 */
	final public function ready() {
		if ( $this->state != self::FORM_UNREADY ) {
			throw new LogicException( __CLASS__ . " ready() already called.\n" );
		}

		$this->state = self::FORM_READY;
		$status = $this->doCheckTargetGiven();
		if ( $status !== true ) {
			return $status; // bad target
		}

		$this->doBuildOnReady();
		return true;
	}

	/**
	 * Load any objects after ready() called
	 * NOTE: do not do any DB hits here, just build objects
	 */
	protected function doBuildOnReady() {
	}

	/**
	 * Set a member field to a value if the fields are unlocked
	 * @param mixed &$field Field of this form
	 * @param mixed $value Value to set the field to
	 * @return void
	 */
	final protected function trySet( &$field, $value ) {
		if ( $this->state != self::FORM_UNREADY ) {
			throw new LogicException( __CLASS__ . " fields cannot be set anymore.\n" );
		} else {
			$field = $value; // still allowing input
		}
	}

	/**
	 * Check that a target is given (e.g. from GET/POST request)
	 * NOTE: do not do any DB hits here, just check if there is a target
	 * @return true|string true on success, error string on failure
	 */
	protected function doCheckTargetGiven() {
		return true;
	}

	/**
	 * Check that the target is valid (e.g. from GET/POST request)
	 * @param int $flags FOR_SUBMISSION (set on submit)
	 * @return true|string true on success, error string on failure
	 */
	protected function doCheckTarget( $flags = 0 ) {
		return true;
	}

	/**
	 * Check that a target is and it is valid (e.g. from GET/POST request)
	 * NOTE: do not do any DB hits here, just check if there is a target
	 * @return true|string true on success, error string on failure
	 */
	final public function checkTarget() {
		if ( $this->state != self::FORM_READY ) {
			throw new LogicException( __CLASS__ . " input fields not set yet.\n" );
		}
		$status = $this->doCheckTargetGiven();
		if ( $status !== true ) {
			return $status; // bad target
		}
		return $this->doCheckTarget();
	}

	/**
	 * Validate and clean up target/parameters (e.g. from POST request)
	 * @return true|string true on success, error string on failure
	 */
	private function checkParameters() {
		$status = $this->doCheckTarget( self::FOR_SUBMISSION );
		if ( $status !== true ) {
			return $status; // bad target
		}
		return $this->doCheckParameters();
	}

	/**
	 * Verify and clean up parameters (e.g. from POST request)
	 * @return true|string true on success, error string on failure
	 */
	protected function doCheckParameters() {
		return true;
	}

	/**
	 * Preload existing params for the target from the DB (e.g. for GET request)
	 * NOTE: do not call this and then submit()
	 * @return true|string true on success, error string on failure
	 */
	final public function preload() {
		if ( $this->state != self::FORM_READY ) {
			throw new LogicException( __CLASS__ . " input fields not set yet.\n" );
		}
		$status = $this->checkTarget();
		if ( $status !== true ) {
			return $status; // bad target
		}
		$this->doPreloadParameters();
		$this->state = self::FORM_PRELOADED;
		return true;
	}

	/**
	 * Preload existing params for the target from the DB (e.g. for GET request)
	 */
	protected function doPreloadParameters() {
	}

	/**
	 * Submit the form parameters for the page config to the DB
	 * @return true|string true on success, error string on failure
	 */
	final public function submit() {
		if ( $this->state != self::FORM_READY ) {
			throw new LogicException( __CLASS__ . " input fields preloaded or not set yet.\n" );
		}
		$status = $this->checkParameters();
		if ( $status !== true ) {
			return $status; // cannot submit - broken target or params
		}
		$status = $this->doSubmit();
		if ( $status !== true ) {
			return $status; // cannot submit
		}
		$this->state = self::FORM_SUBMITTED;
		return true;
	}

	/**
	 * Submit the form parameters for the page config to the DB
	 * @return true|string true on success, error string on failure
	 */
	protected function doSubmit() {
		return true;
	}
}
