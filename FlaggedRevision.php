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
	
	/**
	 * @param object $row (from database)
	 * @access private
	 */
	function __construct( $row ) {
		$this->mRevId = intval( $row->fr_rev_id );
		$this->mPageId = intval( $row->fr_page_id );
		$this->mTimestamp = $row->fr_timestamp;
		$this->mComment = $row->fr_comment;
		$this->mQuality = intval( $row->fr_quality );
		$this->mTags = FlaggedRevs::expandRevisionTags( strval( $row->fr_quality ) );
		# Optional fields
		$this->mUser = isset($row->fr_user) ? $row->fr_user : 0;
		$this->mFlags = isset($row->fr_flags) ? explode(',',$row->fr_flags) : array();
		# If text and flags given, set text field
		if( isset($row->fr_text) && isset($row->fr_flags) ) {
			if( in_array( 'external', $this->mFlags ) ) {
				$url = $row->fr_text;
				@list(/* $proto */,$path) = explode('://',$url,2);
				if( $path=="" ) {
					$this->mText = null;
				} else {
					$this->mText = ExternalStore::fetchFromURL( $url );
				}
			}
			# Uncompress if needed
			FlaggedRevs::uncompressText( $this->mText, $this->mFlags );
		} else {
			$this->mText = null;
		}
	}
	
	/**
	 * @returns Integer revision ID
	 */
	public function getRevId() {
		return $this->mRevId;
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
		return $this->mText;
	}
	
	/**
	 * @returns Integer the user ID of the reviewer
	 */
	public function getUser() {
		return $this->mUser;
	}
}
