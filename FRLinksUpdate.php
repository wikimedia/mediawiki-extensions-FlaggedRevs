<?php
/**
 * Class containing link update methods and job construction
 * for the special case of refreshing page links due to templates
 * contained only in the stable version of pages
 *
 * @TODO: have flaggedrevs_templatelinks table for stable versions
 *		  to be more specific in what pages to effect
 */
class FRLinksUpdate {
	protected $title;
	protected $sLinks, $cLinks;
	protected $sTemplates, $cTemplates;
	protected $sImages, $cImages;
	protected $sCategories, $cCategories;

	// @TODO: replace raw $linksUpdate field accesses
    public function __construct( LinksUpdate $linksUpdate, ParserOutput $stableOutput ) {
        $this->title = $linksUpdate->mTitle;
		# Stable version links
		$this->sLinks = $stableOutput->getLinks();
		$this->sTemplates = $stableOutput->getTemplates();
		$this->sImages = $stableOutput->getImages();
		$this->sCategories = $stableOutput->getCategories();
		# Current version links
		$this->cLinks = $linksUpdate->mLinks;
		$this->cTemplates = $linksUpdate->mTemplates;
		$this->cImages = $linksUpdate->mImages;
		$this->cCategories = $linksUpdate->mCategories;
    }

	public function doUpdate() {
		$links = array();
		# Get any links that are only in the stable version...
		foreach ( $this->sLinks as $ns => $titles ) {
			foreach ( $titles as $title => $id ) {
				if ( !isset( $this->cLinks[$ns] )
					|| !isset( $this->cLinks[$ns][$title] ) )
				{
					self::addLink( $links, $ns, $title );
				}
			}
		}
		# Get any images that are only in the stable version...
		foreach ( $this->sImages as $image => $n ) {
			if ( !isset( $this->cImages[$image] ) ) {
				self::addLink( $links, NS_FILE, $image );
			}
		}
		# Get any templates that are only in the stable version...
		foreach ( $this->sTemplates as $ns => $titles ) {
			foreach ( $titles as $title => $id ) {
				if ( !isset( $this->cTemplates[$ns] )
					|| !isset( $this->cTemplates[$ns][$title] ) )
				{
					self::addLink( $links, $ns, $title );
				}
			}
		}
		# Get any categories that are only in the stable version...
		foreach ( $this->sCategories as $category => $sort ) {
            if ( !isset( $this->cCategories[$category] ) ) {
				self::addLink( $links, NS_CATEGORY, $category );
			}
        }
		$pageId = $this->title->getArticleId();
		# Get any link tracking changes
		$existing = self::getExistingLinks( $pageId );
		$insertions = self::getLinkInsertions( $existing, $links, $pageId );
		$deletions = self::getLinkDeletions( $existing, $links );
		# Delete removed links
		$dbw = wfGetDB( DB_MASTER );
		if ( $clause = self::makeWhereFrom2d( $deletions ) ) {
			$where = array( 'ftr_from' => $pageId );
			$where[] = $clause;
			$dbw->delete( 'flaggedrevs_tracking', $where, __METHOD__ );
		}
		# Add any new links
		if ( count( $insertions ) ) {
			$dbw->insert( 'flaggedrevs_tracking', $insertions, __METHOD__, 'IGNORE' );
		}
	}

	protected static function addLink( array &$links, $ns, $dbKey ) {
		if ( !isset( $links[$ns] ) ) {
			$links[$ns] = array();
		}
		$links[$ns][$dbKey] = 1;
	}

	protected static function getExistingLinks( $pageId ) {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select( 'flaggedrevs_tracking',
			array( 'ftr_namespace', 'ftr_title' ),
			array( 'ftr_from' => $pageId ),
			__METHOD__ );
		$arr = array();
		while ( $row = $dbr->fetchObject( $res ) ) {
			if ( !isset( $arr[$row->ftr_namespace] ) ) {
				$arr[$row->ftr_namespace] = array();
			}
			$arr[$row->ftr_namespace][$row->ftr_title] = 1;
		}
		return $arr;
	}

	protected static function makeWhereFrom2d( &$arr ) {
		$lb = new LinkBatch();
		$lb->setArray( $arr );
		return $lb->constructSet( 'ftr', wfGetDB( DB_SLAVE ) );
	}

	protected static function getLinkInsertions( $existing, $new, $pageId ) {
		$arr = array();
		foreach ( $new as $ns => $dbkeys ) {
			$diffs = isset( $existing[$ns] ) ?
				array_diff_key( $dbkeys, $existing[$ns] ) : $dbkeys;
			foreach ( $diffs as $dbk => $id ) {
				$arr[] = array(
					'ftr_from'      => $pageId,
					'ftr_namespace' => $ns,
					'ftr_title'     => $dbk
				);
			}
		}
		return $arr;
	}

	protected static function getLinkDeletions( $existing, $new ) {
		$del = array();
		foreach ( $existing as $ns => $dbkeys ) {
			if ( isset( $new[$ns] ) ) {
				$del[$ns] = array_diff_key( $existing[$ns], $new[$ns] );
			} else {
				$del[$ns] = $existing[$ns];
			}
		}
		return $del;
	}

	/*
	* Refresh links of all pages with only the stable version
	* including this page. This will be in a separate transaction.
	* @param Title
	*/
	public static function queueRefreshLinksJobs( Title $title ) {
		global $wgUpdateRowsPerJob;
		wfProfileIn( __METHOD__ );
		# Fetch the IDs
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select( 'flaggedrevs_tracking',
			'ftr_from',
			array( 'ftr_namespace' => $title->getNamespace(),
				'ftr_title' => $title->getDBkey() ),
			__METHOD__
		);
		$numRows = $res->numRows();
		if ( !$numRows ) {
			wfProfileOut( __METHOD__ );
			return; // sanity check
		}
		$numBatches = ceil( $numRows / $wgUpdateRowsPerJob );
		$realBatchSize = ceil( $numRows / $numBatches );
		$start = false;
		$jobs = array();
		do {
			$first = $last = false; // first/last page_id of this batch
			# Get $realBatchSize items (or less if not enough)...
			for ( $i = 0; $i < $realBatchSize; $i++ ) {
				$row = $res->fetchRow();
				# Is there another row?
				if ( $row ) {
					$id = $row[0];
					$last = $id; // $id is the last page_id of this batch
					if ( $first === false ) {
						$first = $id; // set first page_id of this batch
					}
				# Out of rows?
				} else {
					$id = false;
					break;
				}
            }
			# Insert batch into the queue if there is anything there
			if ( $first ) {
				$params = array( 'start' => $first, 'end' => $last, );
				$jobs[] = new RefreshLinksJob2( $title, $params );
			}
            $start = $id; // Where the last ID left off
		} while ( $start );
		Job::batchInsert( $jobs );
		wfProfileOut( __METHOD__ );
	}
}
