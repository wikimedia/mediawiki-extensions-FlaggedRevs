<?php

class Scribunto_LuaFlaggedRevsLibrary extends Scribunto_LuaLibraryBase {
	public function register() {
		$lib = array(
			'getStabilitySettings' => array( $this, 'getStabilitySettings' ),
		);

		return $this->getEngine()->registerInterface( __DIR__ . '/mw.ext.FlaggedRevs.lua', $lib, array() );
	}

	public function getStabilitySettings( $pagename = null ) {
		$this->checkTypeOptional( 'mw.ext.FlaggedRevs.getStabilitySettings', 1, $pagename, 'string', null );
		if ( $pagename ) {
			$title = Title::newFromText( $pagename );
			if ( !( $title instanceof Title ) ) {
				return array( null );
			}
		} else {
			$title = $this->getTitle();
		}
		if ( !FlaggedRevs::inReviewNamespace( $title ) ) {
			return array( null );
		}
		$page = FlaggableWikiPage::getTitleInstance( $title );
		if ( !$page->isDataLoaded() ) {
			$this->incrementExpensiveFunctionCount();
		}
		return array( $page->getStabilitySettings() );
	}

}
