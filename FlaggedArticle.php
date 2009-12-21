<?php
/**
 * Class representing a MediaWiki article and history
 *
 * FlaggedArticle::getTitleInstance() prefered over constructor calls
 */
class FlaggedArticle extends Article {
	# Process cache variables
	protected $stableRev = null;
	protected $pageConfig = null;
	protected $flags = array();

	# Max number of revisions in tag cache
	const CACHE_MAX = 1000;

	/**
	 * Get a FlaggedArticle for a given title
	 */
	public static function getTitleInstance( Title $title ) {
		// Check if there is already an instance on this title
		if( !isset( $title->flaggedRevsArticle ) ) {
			$title->flaggedRevsArticle = new self( $title );
		}
		return $title->flaggedRevsArticle;
	}

	/**
	 * Get a FlaggedArticle for a given article
	 */
	public static function getArticleInstance( Article $article ) {
		return self::getTitleInstance( $article->mTitle );
	}

	 /**
	 * Is the stable version shown by default for this page?
	 * @returns bool
	 */
	public function showStableByDefault() {
		# Get page configuration
		$config = $this->getVisibilitySettings();
		return (bool)$config['override'];
	}
	
	 /**
	 * Is most of the UI on this page to be hidden?
	 * @returns bool
	 */
	public function limitedUI() {
		return ( FlaggedRevs::forDefaultVersionOnly() && !$this->showStableByDefault() );
	}

	/**
	 * Is this page less open than the site defaults?
	 * @returns bool
	 */
	public function isPageLocked() {
		return ( !FlaggedRevs::showStableByDefault() && $this->showStableByDefault() );
	}

	/**
	 * Is this page more open than the site defaults?
	 * @returns bool
	 */
	public function isPageUnlocked() {
		return ( FlaggedRevs::showStableByDefault() && !$this->showStableByDefault() );
	}

	/**
	 * Should tags only be shown for unreviewed content for this user?
	 * @returns bool
	 */
	public function lowProfileUI() {
		return FlaggedRevs::lowProfileUI() &&
			FlaggedRevs::showStableByDefault() == $this->showStableByDefault();
	}

	 /**
	 * Is this article reviewable?
	 * @param bool $titleOnly, only check if title is in reviewable namespace
	 */
	public function isReviewable( $titleOnly = false ) {
		if( !FlaggedRevs::isPageReviewable( $this->getTitle() ) ) {
			return false;
		} elseif( !$titleOnly && FlaggedRevs::forDefaultVersionOnly()
			&& !$this->showStableByDefault() )
		{
			return false;
		}
		return true;
	}
	
	/**
	* Is this page in patrolable?
	* @param bool $titleOnly, only check if title is in reviewable namespace
	* @return bool
	*/
	public function isPatrollable( $titleOnly = false ) {
		if( FlaggedRevs::isPagePatrollable( $this->getTitle() ) ) {
			return true;
		} elseif( !$titleOnly && FlaggedRevs::forDefaultVersionOnly()
			&& !$this->showStableByDefault() )
		{
			return true;
		}
		return false;
	}

	/**
	 * Get latest quality rev, if not, the latest reviewed one
	 * @param int $flags
	 * @return Row
	 */
	public function getStableRev( $flags = 0 ) {
		if( $this->stableRev === false ) {
			return null; // We already looked and found nothing...
		}
		# Cached results available?
		if( !is_null($this->stableRev) ) {
			return $this->stableRev;
		}
		# Do we have one?
		$srev = FlaggedRevision::newFromStable( $this->getTitle(), $flags );
		if( $srev ) {
			$this->stableRev = $srev;
			$this->flags[$srev->getRevId()] = $srev->getTags();
			return $srev;
		} else {
			$this->stableRev = false;
			return null;
		}
	}

	/**
	 * Get visiblity restrictions on page
	 * @param Bool $forUpdate, use DB master?
	 * @returns Array (select,override)
	*/
	public function getVisibilitySettings( $forUpdate = false ) {
		# Cached results available?
		if( !is_null($this->pageConfig) ) {
			return $this->pageConfig;
		}
		# Get the content page, skip talk
		$title = $this->getTitle()->getSubjectPage();
		$config = FlaggedRevs::getPageVisibilitySettings( $title, $forUpdate );
		$this->pageConfig = $config;
		return $config;
	}

	/**
	 * @param int $revId
	 * @returns Array, output of the flags for a given revision
	 */
	public function getFlagsForRevision( $revId ) {
		# Cached results?
		if( isset($this->flags[$revId]) ) {
			return $this->flags[$revId];
		}
		# Get the flags
		$flags = FlaggedRevs::getRevisionTags( $this->getTitle(), $revId );
		# Don't let cache get too big
		if( count($this->flags) >= self::CACHE_MAX ) {
			$this->flags = array();
		}
		# Try to cache results
		$this->flags[$revId] = $flags;
		return $flags;
	}
}
