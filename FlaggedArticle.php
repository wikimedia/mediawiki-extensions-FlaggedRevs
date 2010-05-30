<?php
/**
 * Class representing a MediaWiki article and history
 *
 * FlaggedArticle::getTitleInstance() is preferred over constructor calls
 */
class FlaggedArticle extends Article {
	/* Process cache variables */
	protected $stableRev = null;
	protected $pageConfig = null;

	/**
	 * Get a FlaggedArticle for a given title
	 * @param Title
	 * @returns FlaggedArticle
	 */
	public static function getTitleInstance( Title $title ) {
		// Check if there is already an instance on this title
		if ( !isset( $title->flaggedRevsArticle ) ) {
			$title->flaggedRevsArticle = new self( $title );
		}
		return $title->flaggedRevsArticle;
	}

	/**
	 * Get a FlaggedArticle for a given article
	 * @param Article
	 * @returns FlaggedArticle
	 */
	public static function getArticleInstance( Article $article ) {
		return self::getTitleInstance( $article->mTitle );
	}

	 /**
	 * Is the stable version shown by default for this page?
     * @param int $flags, FR_MASTER
	 * @returns bool
	 */
	public function isStableShownByDefault( $flags = 0 ) {
		# Get page configuration
		$config = $this->getVisibilitySettings( $flags );
		return (bool)$config['override'];
	}

	/**
	 * Do edits have to be reviewed before being shown by default?
     * @param int $flags, FR_MASTER
	 * @returns bool
	 */
	public function editsRequireReview( $flags = 0 ) {
		return (
			$this->isReviewable( $flags ) && // reviewable page
			$this->isStableShownByDefault( $flags ) && // and stable versions override
			$this->getStableRev( $flags ) // and there is a stable version
		);
	}

	/**
	 * Are edits to this page currently pending?
     * @param int $flags, FR_MASTER
	 * @returns bool
	 */
	public function editsArePending( $flags = 0 ) {
		if ( $this->isReviewable() ) {
			$srev = $this->getStableRev( $flags );
			if ( $srev ) {
				if ( $flags & FR_MASTER ) {
					$latest = $this->getTitle()->getLatestRevID( GAID_FOR_UPDATE );
				} else {
					$latest = $this->getLatest();
				}
				return ( $srev->getRevId() != $latest ); // edits need review
			}
		}
		return false; // all edits go live
	}

	/**
	 * Is this page less open than the site defaults?
	 * @returns bool
	 */
	public function isPageLocked() {
		return ( !FlaggedRevs::isStableShownByDefault() && $this->isStableShownByDefault() );
	}

	/**
	 * Is this page more open than the site defaults?
	 * @returns bool
	 */
	public function isPageUnlocked() {
		return ( FlaggedRevs::isStableShownByDefault() && !$this->isStableShownByDefault() );
	}

	/**
	 * Should tags only be shown for unreviewed content for this user?
	 * @returns bool
	 */
	public function lowProfileUI() {
		return FlaggedRevs::lowProfileUI() &&
			FlaggedRevs::isStableShownByDefault() == $this->isStableShownByDefault();
	}

	 /**
	 * Is this article reviewable?
     * @param int $flags, FR_MASTER
     * @returns bool
	 */
	public function isReviewable( $flags = 0 ) {
		if ( !FlaggedRevs::inReviewNamespace( $this->getTitle() ) ) {
			return false;
		}
        return !( FlaggedRevs::forDefaultVersionOnly()
            && !$this->isStableShownByDefault( $flags ) );
	}
	
	/**
	* Is this page in patrollable?
    * @param int $flags, FR_MASTER
	* @return bool
	*/
	public function isPatrollable( $flags = 0 ) {
        if ( !FlaggedRevs::inPatrolNamespace( $this->getTitle() ) ) {
			return false;
        }
        return !$this->isReviewable( $flags ); // pages that are reviewable are not patrollable
	}

	/**
	 * Get latest quality rev, if not, the latest reviewed one
	 * @param int $flags
	 * @return mixed (FlaggedRevision/false)
	 */
	public function getStableRev( $flags = 0 ) {
		# Cached results available?
		if ( !( $flags & FR_MASTER ) && $this->stableRev !== null ) {
			return $this->stableRev;
		}
		# Do we have one?
		$srev = FlaggedRevision::newFromStable( $this->getTitle(), $flags );
		if ( $srev ) {
			$this->stableRev = $srev;
		} else {
			$this->stableRev = false;
		}
        return $this->stableRev;
	}

	/**
	 * Get visiblity restrictions on page
	 * @param int $flags, FR_MASTER
	 * @returns array (select,override)
	 */
	public function getVisibilitySettings( $flags = 0 ) {
		# Cached results available?
		if ( !( $flags & FR_MASTER ) && $this->pageConfig !== null ) {
			return $this->pageConfig;
		}
		$config = FlaggedRevs::getPageVisibilitySettings( $this->getTitle(), $flags );
		$this->pageConfig = $config;
		return $config;
	}
}
