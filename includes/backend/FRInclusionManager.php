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
	private bool $parserOutputIsStabilized = false;
	/** Stable versions of templates */
	private array $stableTemplates = [];

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
		$this->stableTemplates = [];
	}

	/**
	 * Reset all template version data
	 * @return void
	 */
	public function clear() {
		$this->parserOutputIsStabilized = false;
		$this->stableTemplates = [];
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
		$this->parserOutputIsStabilized = true;
		$this->stableTemplates = self::formatTemplateArray( $tStbVersions );
	}

	/**
	 * Should Parser stabilize includes?
	 * @return bool
	 */
	public function parserOutputIsStabilized() {
		return $this->parserOutputIsStabilized;
	}

	/**
	 * Get the stable version of a template
	 * @param Title $title
	 * @return int
	 */
	public function getStableTemplateVersion( Title $title ) {
		$dbKey = $title->getDBkey();
		$namespace = $title->getNamespace();
		$id = $this->stableTemplates[$namespace][$dbKey] ??
			FlaggedRevision::getStableRevId( $title );
		$this->stableTemplates[$namespace][$dbKey] = $id; // cache
		return $id;
	}
}
