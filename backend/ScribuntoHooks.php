<?php

namespace MediaWiki\Extension\FlaggedRevs\Backend;

use FlaggedRevsScribuntoLuaLibrary;
use MediaWiki\Extension\Scribunto\Hooks\ScribuntoExternalLibrariesHook;

/**
 * Class containing hooked functions for a FlaggedRevs environment
 * All hooks from the Scribunto extension which is optional to use with this extension.
 */
class ScribuntoHooks implements
	ScribuntoExternalLibrariesHook
{

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ScribuntoExternalLibraries
	 *
	 * @param string $engine
	 * @param array &$extraLibraries
	 */
	public function onScribuntoExternalLibraries( string $engine, array &$extraLibraries ): void {
		if ( $engine == 'lua' ) {
			$extraLibraries['mw.ext.FlaggedRevs'] = FlaggedRevsScribuntoLuaLibrary::class;
		}
	}
}
