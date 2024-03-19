<?php

use MediaWiki\Title\Title;

/**
 * Class containing template version usage requirements for
 * Parser based on the source text (being parsed) revision ID.
 *
 * Parser hooks check this to determine what template version to use.
 * If no requirements are set, the page is parsed as normal.
 */
class FRInclusionManager {
	/** @var array[]|null templates at review time */
	private $reviewedVersions = null;
	/** @var array[] Stable versions of templates */
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
	}

	/**
	 * Reset all template version data
	 * @return void
	 */
	public function clear() {
		$this->reviewedVersions = null;
		$this->stableVersions['templates'] = [];
	}

	/**
	 * (a) Stabilize inclusions in Parser output
	 * (b) Set the template versions used in the flagged version of a revision
	 * @param int[][] $tmpParams (ns => dbKey => revId )
	 */
	private function setReviewedVersions( array $tmpParams ) {
		$this->reviewedVersions = [];
		$this->reviewedVersions['templates'] = self::formatTemplateArray( $tmpParams );
	}

	/**
	 * Set the stable versions of some template
	 * @param int[][] $tmpParams (ns => dbKey => revId )
	 */
	private function setStableVersionCache( array $tmpParams ) {
		$this->stableVersions['templates'] = self::formatTemplateArray( $tmpParams );
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
	 * (a) Stabilize inclusions in Parser output
	 * (b) Load all of the "review time" versions of template from $frev
	 * (c) Load their stable version counterparts (avoids DB hits)
	 * Note: Used when calling FlaggedRevs::parseStableRevision().
	 * @param FlaggedRevision $frev
	 * @return void
	 */
	public function stabilizeParserOutput( FlaggedRevision $frev ) {
		// Stable versions
		$tStbVersions = [];
		# We can preload *most* of the stable version IDs the parser will need...
		if ( FlaggedRevs::inclusionSetting() == FR_INCLUDES_STABLE ) {
			$tStbVersions = $frev->getStableTemplateVersions();
		}
		$this->reviewedVersions = [];
		$this->reviewedVersions['templates'] = [];
		$this->setStableVersionCache( $tStbVersions );
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
	 * @return int|null
	 */
	public function getReviewedTemplateVersion( Title $title ) {
		if ( !is_array( $this->reviewedVersions ) ) {
			throw new LogicException( "prepareForParse() nor setReviewedVersions() called yet" );
		}
		$dbKey = $title->getDBkey();
		$namespace = $title->getNamespace();
		return $this->reviewedVersions['templates'][$namespace][$dbKey] ?? null;
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
}
