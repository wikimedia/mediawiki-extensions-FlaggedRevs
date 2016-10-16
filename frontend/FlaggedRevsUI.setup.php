<?php
/**
 * Class containing UI setup functions for a FlaggedRevs environment.
 * This depends on config variables in LocalSettings.php.
 * Note: avoid  FlaggedRevs class calls here for performance (like load.php).
 */
class FlaggedRevsUISetup {
	/**
	 * Register FlaggedRevs hooks.
	 * @param $hooks Array $wgHooks (assoc array of hooks and handlers)
	 * @return void
	 */
	public static function defineHookHandlers( array &$hooks ) {
		global $wgFlaggedRevsProtection;

		# XXX: Don't mess with dumpHTML article view output...
		if ( !defined( 'MW_HTML_FOR_DUMP' ) ) {
			# Override current revision, set cache...
			$hooks['ArticleViewHeader'][] = 'FlaggedRevsUIHooks::onArticleViewHeader';
			$hooks['ImagePageFindFile'][] = 'FlaggedRevsUIHooks::onImagePageFindFile';
			# Override redirect behavior...
			$hooks['InitializeArticleMaybeRedirect'][] = 'FlaggedRevsUIHooks::overrideRedirect';
			# Set page view tabs (non-Vector)
			$hooks['SkinTemplateTabs'][] = 'FlaggedRevsUIHooks::onSkinTemplateTabs';
			# Set page view tabs (Vector)
			$hooks['SkinTemplateNavigation'][] = 'FlaggedRevsUIHooks::onSkinTemplateNavigation';
			# Add review form
			$hooks['SkinAfterContent'][] = 'FlaggedRevsUIHooks::onSkinAfterContent';
			# Show unreviewed pages links
			$hooks['CategoryPageView'][] = 'FlaggedRevsUIHooks::onCategoryPageView';
			# Mark items in file history (shown on page view)
			$hooks['LocalFile::getHistory'][] = 'FlaggedRevsUIHooks::addToFileHistQuery';
			$hooks['ImagePageFileHistoryLine'][] = 'FlaggedRevsUIHooks::addToFileHistLine';
			# Add review notice, backlog notices, protect form link, and CSS/JS and set robots
			$hooks['BeforePageDisplay'][] = 'FlaggedRevsUIHooks::onBeforePageDisplay';
		}

		if ( !$wgFlaggedRevsProtection ) {
			# Mark items in user contribs
			$hooks['ContribsPager::getQueryInfo'][] = 'FlaggedRevsUIHooks::addToContribsQuery';
			$hooks['ContributionsLineEnding'][] = 'FlaggedRevsUIHooks::addToContribsLine';
		} else {
			# Add protection form field
			$hooks['ProtectionForm::buildForm'][] = 'FlaggedRevsUIHooks::onProtectionForm';
			$hooks['ProtectionForm::showLogExtract'][] = 'FlaggedRevsUIHooks::insertStabilityLog';
			# Save stability settings
			$hooks['ProtectionForm::save'][] = 'FlaggedRevsUIHooks::onProtectionSave';
		}
	}

	/**
	 * Register FlaggedRevs special pages as needed.
	 * @param $pages Array $wgSpecialPages (list of special pages)
	 * @param $updates Array $wgSpecialPageCacheUpdates (assoc array of special page updaters)
	 * @return void
	 */
	public static function defineSpecialPages( array &$pages, array &$updates ) {
		global $wgFlaggedRevsProtection, $wgFlaggedRevsNamespaces, $wgUseTagFilter;

		// Show special pages only if FlaggedRevs is enabled on some namespaces
		if ( count( $wgFlaggedRevsNamespaces ) ) {
			$pages['RevisionReview'] = 'RevisionReview'; // unlisted
			$pages['ReviewedVersions'] = 'ReviewedVersions'; // unlisted
			$pages['PendingChanges'] = 'PendingChanges';
			// Show tag filtered pending edit page if there are tags
			if ( $wgUseTagFilter ) {
				$pages['ProblemChanges'] = 'ProblemChanges';
			}
			if ( !$wgFlaggedRevsProtection ) {
				$pages['ReviewedPages'] = 'ReviewedPages';
				$pages['UnreviewedPages'] = 'UnreviewedPages';
				$updates['UnreviewedPages'] = 'UnreviewedPages::updateQueryCache';
			}
			$pages['QualityOversight'] = 'QualityOversight';
			$pages['ValidationStatistics'] = 'ValidationStatistics';
			$updates['ValidationStatistics'] = 'FlaggedRevsStats::updateCache';
			// Protect levels define allowed stability settings
			if ( $wgFlaggedRevsProtection ) {
				$pages['StablePages'] = 'StablePages';
			} else {
				$pages['ConfiguredPages'] = 'ConfiguredPages';
				$pages['Stabilization'] = 'Stabilization'; // unlisted
			}
		}
	}

	/**
	 * Define AJAX dispatcher functions
	 * @param $ajaxExportList Array $wgAjaxExportList
	 * @return void
	 */
	public static function defineAjaxFunctions( &$ajaxExportList ) {
		$ajaxExportList[] = 'RevisionReview::AjaxReview';
		$ajaxExportList[] = 'FlaggablePageView::AjaxBuildDiffHeaderItems';
	}
}
