<?php
/**
 * Class containing generic form business logic
 * Note: edit tokens are the responsibility of the caller
 * Usage: (a) set ALL form params before doing anything else
 *		  (b) call ready() when all params are set
 *		  (c) call submit() as needed
 */
abstract class FRGenericSubmitForm {
	const ON_SUBMISSION = 1; # Notify functions when we are submitting

	protected $inputLock = 0; # Disallow bad submissions
	protected $user = null;

	public function __construct( User $user ) {
		$this->user = $user;
		$this->initialize();
	}

	/**
	* Initialize any parameters on construction
	* @return void
	*/
	protected function initialize() {}

	/**
	* Get the submitting user
	* @return User
	*/
	public function getUser() {
		return $this->user;
	}

	/**
	* Signal that inputs will be given (via accessors)
	* @return void
	*/
	public function start() {
		$this->inputLock = 0;
	}

	/**
	* Signal that inputs are all given (via accessors)
	* @return mixed (true on success, error string on target failure)
	*/
	public function ready() {
		$this->inputLock = 1;
		$status = $this->doCheckTarget();
		if ( $status !== true ) {
			return $status; // bad target
		}
		return $this->doLoadOnReady();
	}

	/**
	* Set a member field to a value if the fields are unlocked
	* @param mixed &$field Field of this form
	* @param mixed $value Value to set the field to
	* @return void
	*/
	protected function trySet( &$field, $value ) {
		if ( $this->inputLock ) {
			throw new MWException( __CLASS__ . " fields cannot be set anymore.\n");
		} else {
			$field = $value; // still allowing input
		} 
	}

	/*
	* Check that the target is valid (e.g. from GET/POST request)
	* @param int $flags ON_SUBMISSION (set on submit)
	* @return mixed (true on success, error string on failure)
	*/
	protected function doCheckTarget( $flags = 0 ) {
		return true;
	}

	/**
	* Load any parameters after ready() called
	* @return mixed (true on success, error string on failure)
	*/
	protected function doLoadOnReady() {
		return true;
	}

	/*
	* Verify and clean up parameters (e.g. from POST request)
	* @return mixed (true on success, error string on failure)
	*/
	protected function checkParameters() {
		$status = $this->doCheckTarget( self::ON_SUBMISSION );
		if ( $status !== true ) {
			return $status; // bad target
		}
		return $this->doCheckParameters();
	}

	/*
	* Verify and clean up parameters (e.g. from POST request)
	* @return mixed (true on success, error string on failure)
	*/
	protected function doCheckParameters() {
		return true;
	}

	/**
	* Submit the form parameters for the page config to the DB.
	* 
	* @return mixed (true on success, error string on failure)
	*/
	public function submit() {
		if ( !$this->inputLock ) {
			throw new MWException( __CLASS__ . " input fields not set yet.\n");
		}
		$status = $this->checkParameters();
		if ( $status !== true ) {
			return $status; // cannot submit - broken params
		}
		return $this->doSubmit();
	}

	/**
	* Submit the form parameters for the page config to the DB.
	* 
	* @return mixed (true on success, error string on failure)
	*/
	protected function doSubmit() {
		return true;
	}
}
