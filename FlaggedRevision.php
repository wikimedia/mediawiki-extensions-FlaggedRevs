<?php

class FlaggedRevision {
	private $mRevId;
	private $mPageId;
	private $mTimestamp;
	private $mComment;
	private $mQuality;
	private $mTags;
	private $mText;
	private $mFlags;
	private $mUser;
	private $mTitle;

	/**
	 * @param Title $title
	 * @param Row $row (from database)
	 * @access private
	 */
	function __construct( $title, $row ) {
		$this->mTitle = $title;
		$this->mRevId = intval( $row->fr_rev_id );
		$this->mPageId = intval( $row->fr_page_id );
		$this->mTimestamp = $row->fr_timestamp;
		$this->mComment = $row->fr_comment;
		$this->mQuality = intval( $row->fr_quality );
		$this->mTags = FlaggedRevs::expandRevisionTags( strval($row->fr_tags) );
		# Optional fields
		$this->mUser = isset($row->fr_user) ? $row->fr_user : 0;
		$this->mFlags = isset($row->fr_flags) ? explode(',',$row->fr_flags) : null;
		$this->mRawDBText = isset($row->fr_text) ? $row->fr_text : null;
		# Deal with as it comes
		$this->mText = null;
	}

	/**
	 * @returns Integer revision ID
	 */
	public function getRevId() {
		return $this->mRevId;
	}
	
	/**
	 * @returns Title title
	 */
	public function getTitle() {
		return $this->mTitle;
	}

	/**
	 * @returns Integer page ID
	 */
	public function getPage() {
		return $this->mPageId;
	}

	/**
	 * @returns String revision timestamp in MW format
	 */
	public function getTimestamp() {
		return wfTimestamp(TS_MW, $this->mTimestamp);
	}

	/**
	 * @returns String review comment
	 */
	public function getComment() {
		return $this->mComment;
	}

	/**
	 * @returns Integer revision timestamp in MW format
	 */
	public function getQuality() {
		return $this->mQuality;
	}

	/**
	 * @returns Array tag metadata
	 */
	public function getTags() {
		return $this->mTags;
	}

	/**
	 * @returns String expanded text
	 */
	public function getText() {
		$this->loadText(); // load if not loaded
		return $this->mText;
	}

	/**
	 * @returns Integer the user ID of the reviewer
	 */
	public function getUser() {
		return $this->mUser;
	}
	
	private function loadText() {
		wfProfileIn( __METHOD__ );
		# Loaded already?
		if( !is_null($this->mText) )
			return true;
		# DB stuff loaded already?
		if( is_null($this->mFlags) || is_null($this->mRawDBText) ) {
			$dbw = wfGetDB( DB_MASTER );
			$row = $dbw->selectRow( 'flaggedrevs',
				array( 'fr_text', 'fr_flags' ),
				array( 'fr_rev_id' => $this->mRevId ),
				__METHOD__ );
			// WTF ???
			if( !$row ) {
				$this->mDBRawText = false;
				$this->mFlags = false;
				$this->mText = false;
				wfProfileOut( __METHOD__ );
				return false;
			} else {
				$this->mDBRawText = $row->fr_text;
				$this->mFlags = $row->fr_flags;
			}
		}
		// Check uncompressed cache first...
		global $wgRevisionCacheExpiry, $wgMemc;
		if( $wgRevisionCacheExpiry ) {
			$key = wfMemcKey( 'flaggedrevisiontext', 'revid', $this->getRevId() );
			$text = $wgMemc->get( $key );
			if( is_string($text) ) {
				$this->mText = $text;
				wfProfileOut( __METHOD__ );
				return true;
			}
		}
		// Check if fr_text is just some URL to external DB storage
		if( in_array( 'external', $this->mFlags ) ) {
			$url = $row->fr_text;
			@list(/* $proto */,$path) = explode('://',$url,2);
			if( $path=="" ) {
				$this->mText = null;
			} else {
				$this->mText = ExternalStore::fetchFromURL( $url );
			}
		} else {
			$this->mText = $row->fr_text;
		}
		// Uncompress if needed
		$this->mText = FlaggedRevs::uncompressText( $this->mText, $this->mFlags );
		// Caching may be beneficial for massive use of external storage
		if( $wgRevisionCacheExpiry ) {
			$wgMemc->set( $key, $this->mText, $wgRevisionCacheExpiry );
		}	
		wfProfileOut( __METHOD__ );
	}
}
