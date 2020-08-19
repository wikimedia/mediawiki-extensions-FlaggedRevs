<?php

/**
 * Class containing template/file version usage requirements for
 * Parser based on the source text (being parsed) revision ID.
 *
 * Parser hooks check this to determine what template/file version to use.
 * If no requirements are set, the page is parsed as normal.
 */
class FRInclusionManager {
	/** @var array[]|null Files/templates at review time */
	private $reviewedVersions = null;
	/** @var array[] Stable versions of files/templates */
	private $stableVersions = [];

	/** @var self|null */
	private static $instance = null;

	/**
	 * @return self
	 */
	public static function singleton() {
		if ( self::$instance == null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __clone() {
	}

	private function __construct() {
		$this->stableVersions['templates'] = [];
		$this->stableVersions['files'] = [];
	}

	/**
	 * Reset all template/image version data
	 * @return void
	 */
	public function clear() {
		$this->reviewedVersions = null;
		$this->stableVersions['templates'] = [];
		$this->stableVersions['files'] = [];
	}

	/**
	 * (a) Stabilize inclusions in Parser output
	 * (b) Set the template/image versions used in the flagged version of a revision
	 * @param int[][] $tmpParams (ns => dbKey => revId )
	 * @param array[] $imgParams (dbKey => ['time' => MW timestamp,'sha1' => sha1] )
	 */
	private function setReviewedVersions( array $tmpParams, array $imgParams ) {
		$this->reviewedVersions = [];
		$this->reviewedVersions['templates'] = self::formatTemplateArray( $tmpParams );
		$this->reviewedVersions['files'] = self::formatFileArray( $imgParams );
	}

	/**
	 * Set the stable versions of some template/images
	 * @param int[][] $tmpParams (ns => dbKey => revId )
	 * @param array[] $imgParams (dbKey => ['time' => MW timestamp,'sha1' => sha1] )
	 */
	private function setStableVersionCache( array $tmpParams, array $imgParams ) {
		$this->stableVersions['templates'] = self::formatTemplateArray( $tmpParams );
		$this->stableVersions['files'] = self::formatFileArray( $imgParams );
	}

	/**
	 * Clean up a template version array
	 * @param int[][] $params (ns => dbKey => revId )
	 * @return int[][]
	 */
	private function formatTemplateArray( array $params ) {
		$res = [];
		foreach ( $params as $ns => $templates ) {
			$res[$ns] = [];
			foreach ( $templates as $dbKey => $revId ) {
				$res[$ns][$dbKey] = (int)$revId;
			}
		}
		return $res;
	}

	/**
	 * Clean up a file version array
	 * @param array[] $params (dbKey => ['time' => MW timestamp,'sha1' => sha1] )
	 * @phan-param array<string,array{time:string,sha1:string}> $params
	 * @return array[]
	 */
	private function formatFileArray( array $params ) {
		$res = [];
		foreach ( $params as $dbKey => $timeKey ) {
			$time = '0'; // missing
			$sha1 = false;
			if ( $timeKey['time'] ) {
				$time = $timeKey['time'];
				$sha1 = strval( $timeKey['sha1'] );
			}
			$res[$dbKey] = [ 'time' => $time, 'sha1' => $sha1 ];
		}
		return $res;
	}

	/**
	 * (a) Stabilize inclusions in Parser output
	 * (b) Load all of the "review time" versions of template/files from $frev
	 * (c) Load their stable version counterparts (avoids DB hits)
	 * Note: Used when calling FlaggedRevs::parseStableRevision().
	 * @param FlaggedRevision $frev
	 * @return void
	 */
	public function stabilizeParserOutput( FlaggedRevision $frev ) {
		// Stable versions
		$tStbVersions = [];
		$fStbVersions = [];
		$tRevVersions = $frev->getTemplateVersions();
		$fRevVersions = $frev->getFileVersions();
		# We can preload *most* of the stable version IDs the parser will need...
		if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_STABLE ) {
			$tStbVersions = $frev->getStableTemplateVersions();
			$fStbVersions = $frev->getStableFileVersions();
		}
		$this->setReviewedVersions( $tRevVersions, $fRevVersions );
		$this->setStableVersionCache( $tStbVersions, $fStbVersions );
	}

	/**
	 * Should Parser stabilize includes?
	 * @return bool
	 */
	public function parserOutputIsStabilized() {
		return is_array( $this->reviewedVersions );
	}

	/**
	 * Get the "review time" template version for parser
	 * @param Title $title
	 * @throws Exception
	 * @return int|null
	 */
	public function getReviewedTemplateVersion( Title $title ) {
		if ( !is_array( $this->reviewedVersions ) ) {
			throw new Exception( "prepareForParse() nor setReviewedVersions() called yet" );
		}
		$dbKey = $title->getDBkey();
		$namespace = $title->getNamespace();
		return $this->reviewedVersions['templates'][$namespace][$dbKey] ?? null;
	}

	/**
	 * Get the "review time" file version for parser
	 * @param Title $title
	 * @throws Exception
	 * @return array (MW timestamp/'0'/null, sha1/''/null )
	 */
	public function getReviewedFileVersion( Title $title ) {
		if ( !is_array( $this->reviewedVersions ) ) {
			throw new Exception( "prepareForParse() nor setReviewedVersions() called yet" );
		}
		$dbKey = $title->getDBkey();
		# All NS_FILE, no need to check namespace
		if ( isset( $this->reviewedVersions['files'][$dbKey] ) ) {
			$time = $this->reviewedVersions['files'][$dbKey]['time'];
			$sha1 = $this->reviewedVersions['files'][$dbKey]['sha1'];
			return [ $time, $sha1 ];
		}
		return [ null, null ]; // missing version
	}

	/**
	 * Get the stable version of a template
	 * @param Title $title
	 * @return int
	 */
	public function getStableTemplateVersion( Title $title ) {
		$dbKey = $title->getDBkey();
		$namespace = $title->getNamespace();
		$id = $this->stableVersions['templates'][$namespace][$dbKey] ??
			FlaggedRevision::getStableRevId( $title );
		$this->stableVersions['templates'][$namespace][$dbKey] = $id; // cache
		return $id;
	}

	/**
	 * Get the stable version of a file
	 * @param Title $title
	 * @return array (MW timestamp/'0', sha1/'')
	 */
	public function getStableFileVersion( Title $title ) {
		$dbKey = $title->getDBkey();
		$time = '0'; // missing
		$sha1 = false;
		# All NS_FILE, no need to check namespace
		if ( isset( $this->stableVersions['files'][$dbKey] ) ) {
			$time = $this->stableVersions['files'][$dbKey]['time'];
			$sha1 = $this->stableVersions['files'][$dbKey]['sha1'];
			return [ $time, $sha1 ];
		}
		$srev = FlaggedRevision::newFromStable( $title );
		if ( $srev && $srev->getFileTimestamp() ) {
			$time = $srev->getFileTimestamp();
			$sha1 = $srev->getFileSha1();
		}
		$this->stableVersions['files'][$dbKey] = [];
		$this->stableVersions['files'][$dbKey]['time'] = $time;
		$this->stableVersions['files'][$dbKey]['sha1'] = $sha1;
		return [ $time, $sha1 ];
	}
}
