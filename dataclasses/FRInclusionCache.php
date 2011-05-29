<?php
/**
 * Class containing draft template/file version usage for
 * Parser based on the source text of a revision ID & title.
 */
class FRInclusionCache {
	/**
	 * Get template and image versions from parsing a revision
	 * @param Article $article
	 * @param Revision $rev
	 * @param User $user
	 * @param string $regen use 'regen' to force regeneration
	 * @return array( templateIds, fileSHA1Keys )
	 * templateIds like ParserOutput->mTemplateIds
	 * fileSHA1Keys like ParserOutput->mImageTimeKeys
	 */
	public static function getRevIncludes(
		Article $article, Revision $rev, User $user, $regen = ''
	) {
		global $wgParser, $wgMemc;
		wfProfileIn( __METHOD__ );
		$versions = false;
		$hash = md5( $article->getTitle()->getPrefixedDBkey() );
		$key = wfMemcKey( 'flaggedrevs', 'revIncludes', $rev->getId(), $hash );
		if ( $regen !== 'regen' ) { // check cache
			$versions = FlaggedRevs::getMemcValue( $wgMemc->get( $key ), $article, 'allowStale' );
		}
		if ( !is_array( $versions ) ) { // cache miss
			$pOut = false;
			if ( $rev->isCurrent() ) {
				$parserCache = ParserCache::singleton();
				# Try current version parser cache (as anon)...
				$pOut = $parserCache->get( $article, $article->makeParserOptions( $user ) );
				if ( $pOut == false && $rev->getUser() ) { // try the user who saved the change
					$author = User::newFromId( $rev->getUser() );
					$pOut = $parserCache->get( $article, $article->makeParserOptions( $author ) );
				}
			}
			if ( $pOut == false ) {
				$title = $article->getTitle();
				$pOpts = ParserOptions::newFromUser( $user ); // Note: tidy off
				# Disable slow crap that doesn't matter for getting templates/files...
				$parser = clone $wgParser;
				$parser->clearTagHook( 'ref' );
				$parser->clearTagHook( 'references' );
				$pOut = $parser->parse(
					$rev->getText(), $title, $pOpts, true, true, $rev->getId() );
			}
			# Get the template/file versions used...
			$versions = array( $pOut->getTemplateIds(), $pOut->getImageTimeKeys() );
			# Save to cache...
			$data = FlaggedRevs::makeMemcObj( $versions );
			$wgMemc->set( $key, $data, 24*3600 ); // inclusions may be dynamic
		} else {
			# Do a link batch query for page_latest...
			$lb = new LinkBatch();
			foreach ( $versions as $ns => $tmps ) {
				foreach ( $tmps as $dbKey => $revIdDraft ) {
					$lb->add( $ns, $dbKey );
				}
			}
			$lb->execute();
			# Update array with the current page_latest values.
			# This kludge is there since $newTemplates (thus $revIdDraft) is cached.
			foreach ( $versions as $ns => $tmps ) {
				foreach ( $tmps as $dbKey => &$revIdDraft ) {
					$title = new Title( $ns, $dbKey );
					$revIdDraft = (int)$title->getLatestRevID();
				}
			}
		}	
		wfProfileOut( __METHOD__ );
		return $versions;
	}

	/**
	 * Set template and image versions from parsing a revision
	 * @param Title $title
	 * @param int $revId
	 * @param ParserOutput $rev
	 * @return void
	 */
	public static function setRevIncludes( Title $title, $revId, ParserOutput $pOut ) {
		global $wgMemc;
		$hash = md5( $title->getPrefixedDBkey() );
		$key = wfMemcKey( 'flaggedrevs', 'revIncludes', $revId, $hash );
		# Get the template/file versions used...
		$versions = array( $pOut->getTemplateIds(), $pOut->getImageTimeKeys() );
		# Save to cache...
		$data = FlaggedRevs::makeMemcObj( $versions );
		$wgMemc->set( $key, $data, 24*3600 ); // inclusions may be dynamic
	}
}
