<?php

use MediaWiki\Extension\Scribunto\Engines\LuaCommon\LibraryBase;
use MediaWiki\Title\Title;

class FlaggedRevsScribuntoLuaLibrary extends LibraryBase {
	public function register() {
		$lib = [
			'getStabilitySettings' => [ $this, 'getStabilitySettings' ],
		];

		return $this->getEngine()->registerInterface(
			__DIR__ . '/mw.ext.FlaggedRevs.lua', $lib, []
		);
	}

	/**
	 * @param string|null $pagename
	 *
	 * @return array
	 */
	public function getStabilitySettings( $pagename = null ) {
		$this->checkTypeOptional(
			'mw.ext.FlaggedRevs.getStabilitySettings', 1, $pagename, 'string', null
		);
		$title = $pagename !== null ? Title::newFromText( $pagename ) : $this->getTitle();
		if ( !$title || !FlaggedRevs::inReviewNamespace( $title ) ) {
			return [ null ];
		}
		$page = FlaggableWikiPage::getTitleInstance( $title );
		if ( !$page->isDataLoaded() ) {
			$this->incrementExpensiveFunctionCount();
		}
		return [ $page->getStabilitySettings() ];
	}

}
