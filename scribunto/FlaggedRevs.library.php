<?php

class Scribunto_LuaFlaggedRevsLibrary extends Scribunto_LuaLibraryBase {
	public function register() {
		$lib = [
			'getStabilitySettings' => [ $this, 'getStabilitySettings' ],
		];

		return $this->getEngine()->registerInterface(
			__DIR__ . '/mw.ext.FlaggedRevs.lua', $lib, []
		);
	}

	public function getStabilitySettings( $pagename = null ) {
		$this->checkTypeOptional(
			'mw.ext.FlaggedRevs.getStabilitySettings', 1, $pagename, 'string', null
		);
		if ( $pagename ) {
			$title = Title::newFromText( $pagename );
			if ( !( $title instanceof Title ) ) {
				return [ null ];
			}
		} else {
			$title = $this->getTitle();
		}
		if ( !FlaggedRevs::inReviewNamespace( $title ) ) {
			return [ null ];
		}
		$page = FlaggableWikiPage::getTitleInstance( $title );
		if ( !$page->isDataLoaded() ) {
			$this->incrementExpensiveFunctionCount();
		}
		return [ $page->getStabilitySettings() ];
	}

}
