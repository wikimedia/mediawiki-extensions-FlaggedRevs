<?php

use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\IDatabase;

/**
 * Class containing update methods for tracking links that
 * are only in the stable version of pages. Used only for caching.
 */
class FRDependencyUpdate {
	/** @var Title */
	private $title;
	/** @var int[][] */
	private $sLinks;
	/** @var int[][] */
	private $sTemplates;
	/** @var string[] */
	private $sCategories;

	// run updates now
	public const IMMEDIATE = 0;
	// use the job queue for updates
	public const DEFERRED = 1;

	/**
	 * @param Title $title
	 * @param ParserOutput $stableOutput
	 */
	public function __construct( Title $title, ParserOutput $stableOutput ) {
		$this->title = $title;
		# Stable version links
		$this->sLinks = $stableOutput->getLinks();
		$this->sTemplates = $stableOutput->getTemplates();
		$this->sCategories = $stableOutput->getCategories();
	}

	/**
	 * @param int $mode FRDependencyUpdate::IMMEDIATE/FRDependencyUpdate::DEFERRED
	 */
	public function doUpdate( $mode = self::IMMEDIATE ) {
		$deps = [];
		# Get any links that are only in the stable version...
		$cLinks = $this->getCurrentVersionLinks();
		foreach ( $this->sLinks as $ns => $titles ) {
			foreach ( $titles as $title => $pageId ) {
				if ( !isset( $cLinks[$ns][$title] ) ) {
					$this->addDependency( $deps, $ns, $title );
				}
			}
		}
		# Get any templates that are only in the stable version...
		$cTemplates = $this->getCurrentVersionTemplates();
		foreach ( $this->sTemplates as $ns => $titles ) {
			foreach ( $titles as $title => $id ) {
				if ( !isset( $cTemplates[$ns][$title] ) ) {
					$this->addDependency( $deps, $ns, $title );
				}
			}
		}
		# Get any categories that are only in the stable version...
		$cCategories = $this->getCurrentVersionCategories();
		foreach ( $this->sCategories as $category => $sort ) {
			if ( !isset( $cCategories[$category] ) ) {
				$this->addDependency( $deps, NS_CATEGORY, $category );
			}
		}
		# Quickly check for any dependency tracking changes (use a replica DB)
		if ( $this->getExistingDeps() != $deps ) {
			if ( $mode === self::DEFERRED ) {
				# Let the job queue parse and update
				MediaWikiServices::getInstance()->getJobQueueGroup()->push(
					new FRExtraCacheUpdateJob(
						$this->title,
						[ 'type' => 'updatelinks' ]
					)
				);

				return;
			}
			# Determine any dependency tracking changes
			$existing = $this->getExistingDeps( FR_MASTER );
			$insertions = $this->getDepInsertions( $existing, $deps );
			$deletions = $this->getDepDeletions( $existing, $deps );
			$dbw = wfGetDB( DB_PRIMARY );
			# Delete removed links
			if ( $deletions ) {
				$dbw->delete( 'flaggedrevs_tracking', $deletions, __METHOD__ );
			}
			# Add any new links
			if ( $insertions ) {
				$dbw->insert( 'flaggedrevs_tracking', $insertions, __METHOD__, [ 'IGNORE' ] );
			}
		}
	}

	/**
	 * Get existing cache dependencies
	 * @param int $flags FR_MASTER
	 * @return int[][] (ns => dbKey => 1)
	 */
	private function getExistingDeps( $flags = 0 ) {
		$db = wfGetDB( ( $flags & FR_MASTER ) ? DB_PRIMARY : DB_REPLICA );
		$res = $db->select( 'flaggedrevs_tracking',
			[ 'ftr_namespace', 'ftr_title' ],
			[ 'ftr_from' => $this->title->getArticleID() ],
			__METHOD__
		);
		$arr = [];
		foreach ( $res as $row ) {
			$arr[$row->ftr_namespace][$row->ftr_title] = 1;
		}
		return $arr;
	}

	/**
	 * Get INSERT rows for cache dependencies in $new but not in $existing
	 * @param int[][] $existing
	 * @param int[][] $new
	 * @return array[]
	 */
	private function getDepInsertions( array $existing, array $new ) {
		$arr = [];
		foreach ( $new as $ns => $dbkeys ) {
			if ( isset( $existing[$ns] ) ) {
				$diffs = array_diff_key( $dbkeys, $existing[$ns] );
			} else {
				$diffs = $dbkeys;
			}
			foreach ( $diffs as $dbk => $id ) {
				$arr[] = [
					'ftr_from'      => $this->title->getArticleID(),
					'ftr_namespace' => $ns,
					'ftr_title'     => $dbk
				];
			}
		}
		return $arr;
	}

	/**
	 * Get WHERE clause to delete items in $existing but not in $new
	 * @param int[][] $existing
	 * @param int[][] $new
	 * @return array|false
	 */
	private function getDepDeletions( array $existing, array $new ) {
		$del = [];
		foreach ( $existing as $ns => $dbkeys ) {
			if ( isset( $new[$ns] ) ) {
				$del[$ns] = array_diff_key( $dbkeys, $new[$ns] );
			} else {
				$del[$ns] = $dbkeys;
			}
		}
		if ( $del ) {
			$clause = $this->makeWhereFrom2d( $del, wfGetDB( DB_PRIMARY ) );
			if ( $clause ) {
				return [ $clause, 'ftr_from' => $this->title->getArticleID() ];
			}
		}
		return false;
	}

	/**
	 * Make WHERE clause to match $arr titles
	 * @param array[] $arr
	 * @param IDatabase $db
	 * @return string|bool
	 */
	private function makeWhereFrom2d( $arr, $db ) {
		$lb = MediaWikiServices::getInstance()->getLinkBatchFactory()->newLinkBatch();
		$lb->setArray( $arr );
		return $lb->constructSet( 'ftr', $db );
	}

	/**
	 * @param int[][] &$deps
	 * @param int $ns
	 * @param string $dbKey
	 */
	private function addDependency( array &$deps, $ns, $dbKey ) {
		$deps[$ns][$dbKey] = 1;
	}

	/**
	 * Get an array of existing links, as a 2-D array
	 * @return int[][] (ns => dbKey => 1)
	 */
	private function getCurrentVersionLinks() {
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select( 'pagelinks',
			[ 'pl_namespace', 'pl_title' ],
			[ 'pl_from' => $this->title->getArticleID() ],
			__METHOD__
		);
		$arr = [];
		foreach ( $res as $row ) {
			$arr[$row->pl_namespace][$row->pl_title] = 1;
		}
		return $arr;
	}

	/**
	 * Get an array of existing templates, as a 2-D array
	 * @return int[][] (ns => dbKey => 1)
	 */
	private function getCurrentVersionTemplates() {
		$dbr = wfGetDB( DB_REPLICA );
		$linksMigration = MediaWikiServices::getInstance()->getLinksMigration();
		$queryInfo = $linksMigration->getQueryInfo( 'templatelinks' );
		list( $nsField, $titleField ) = $linksMigration->getTitleFields( 'templatelinks' );
		$res = $dbr->select( $queryInfo['tables'],
			$queryInfo['fields'],
			[ 'tl_from' => $this->title->getArticleID() ],
			__METHOD__,
			[],
			$queryInfo['joins']
		);
		$arr = [];
		foreach ( $res as $row ) {
			$arr[$row->$nsField][$row->$titleField] = 1;
		}
		return $arr;
	}

	/**
	 * Get an array of existing categories, with the name in the key and sort key in the value.
	 * @return string[] (category => sortkey)
	 */
	private function getCurrentVersionCategories() {
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select( 'categorylinks',
			[ 'cl_to', 'cl_sortkey' ],
			[ 'cl_from' => $this->title->getArticleID() ],
			__METHOD__
		);
		$arr = [];
		foreach ( $res as $row ) {
			$arr[$row->cl_to] = $row->cl_sortkey;
		}
		return $arr;
	}
}
