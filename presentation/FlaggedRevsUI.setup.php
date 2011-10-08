<?php
/**
 * Class containing hooked functions for a FlaggedRevs environment
 */
class FlaggedRevsUISetup {
	/*
	 * Register FlaggedRevs special pages as needed.
	 * @param $pages Array $wgSpecialPages (list of special pages)
	 * @param $groups Array $wgSpecialPageGroups (assoc array of special page groups)
	 * @return void
	 */
	public static function defineSpecialPages( array &$pages, array &$groups ) {
		global $wgUseTagFilter;
		// Show special pages only if FlaggedRevs is enabled on some namespaces
		if ( FlaggedRevs::getReviewNamespaces() ) {
			$pages['RevisionReview'] = 'RevisionReview'; // unlisted
			$pages['ReviewedVersions'] = 'ReviewedVersions'; // unlisted
			$pages['PendingChanges'] = 'PendingChanges';
			$groups['PendingChanges'] = 'quality';
			// Show tag filtered pending edit page if there are tags
			if ( $wgUseTagFilter ) {
				$pages['ProblemChanges'] = 'ProblemChanges';
				$groups['ProblemChanges'] = 'quality';
			}
			if ( !FlaggedRevs::useOnlyIfProtected() ) {
				$pages['ReviewedPages'] = 'ReviewedPages';
				$groups['ReviewedPages'] = 'quality';
				$pages['UnreviewedPages'] = 'UnreviewedPages';
				$groups['UnreviewedPages'] = 'quality';
			}
			$pages['QualityOversight'] = 'QualityOversight';
			$groups['QualityOversight'] = 'quality';
			$pages['ValidationStatistics'] = 'ValidationStatistics';
			$groups['ValidationStatistics'] = 'quality';
			// Protect levels define allowed stability settings
			if ( FlaggedRevs::useProtectionLevels() ) {
				$pages['StablePages'] = 'StablePages';
				$groups['StablePages'] = 'quality';
			} else {
				$pages['ConfiguredPages'] = 'ConfiguredPages';
				$groups['ConfiguredPages'] = 'quality';
				$pages['Stabilization'] = 'Stabilization'; // unlisted
			}
		}
	}

	/**
	 * Append FlaggedRevs resource module definitions
	 * @param $modules Array $wgResourceModules (list of modules)
	 * @return void
	 */
	public static function defineResourceModules( &$modules ) {
		$localModulePath = dirname( __FILE__ ) . '/modules/';
		$remoteModulePath = 'FlaggedRevs/presentation/modules';
		$modules['ext.flaggedRevs.basic'] = array(
			'styles'        => array( 'ext.flaggedRevs.basic.css' ),
			'localBasePath' => $localModulePath,
			'remoteExtPath' => $remoteModulePath,
		);
		$modules['ext.flaggedRevs.advanced'] = array(
			'scripts'       => array( 'ext.flaggedRevs.advanced.js' ),
			'messages'      => array(
				'revreview-toggle-show', 'revreview-toggle-hide',
				'revreview-diff-toggle-show', 'revreview-diff-toggle-hide',
				'revreview-log-toggle-show', 'revreview-log-toggle-hide',
				'revreview-log-details-show', 'revreview-log-details-hide'
			),
			'dependencies'  => array( 'mediawiki.util' ),
			'localBasePath' => $localModulePath,
			'remoteExtPath' => $remoteModulePath,
		);
		$modules['ext.flaggedRevs.review'] = array(
			'scripts'       => array( 'ext.flaggedRevs.review.js' ),
			'styles'        => array( 'ext.flaggedRevs.review.css' ),
			'messages'      => array(
				'savearticle', 'tooltip-save', 'accesskey-save',
				'revreview-submitedit', 'revreview-submitedit-title',
				'revreview-submit-review', 'revreview-submit-unreview',
				'revreview-submit-reviewed', 'revreview-submit-unreviewed',
				'revreview-submitting', 'actioncomplete', 'actionfailed',
				'revreview-adv-reviewing-p', 'revreview-adv-reviewing-c',
				'revreview-sadv-reviewing-p', 'revreview-sadv-reviewing-c',
				'revreview-adv-start-link', 'revreview-adv-stop-link'
			),
			'dependencies'  => array( 'mediawiki.util' ),
			'localBasePath' => $localModulePath,
			'remoteExtPath' => $remoteModulePath,
		);
	}

	/**
	 * Append FlaggedRevs log names and set filterable logs
	 * @param $logNames Array $wgLogNames (assoc array of log name message keys)
	 * @param $logHeaders Array $wgLogHeaders (assoc array of log header message keys)
	 * @param $filterLogTypes Array $wgFilterLogTypes
	 */
	public static function defineLogBasicDescription( &$logNames, &$logHeaders, &$filterLogTypes ) {
		$logNames['review'] = 'review-logpage';
		$logHeaders['review'] = 'review-logpagetext';

		$logNames['stable'] = 'stable-logpage';
		$logHeaders['stable'] = 'stable-logpagetext';

		$filterLogTypes['review'] = true;
	}

	/**
	 * Append FlaggedRevs log action handlers
	 * @param $logActions Array $wgLogActions (assoc array of log action message keys)
	 * @param $logActionsHandlers Array $wgLogActionsHandlers (assoc array of log handlers)
	 * @return void
	 */
	public static function defineLogActionHanders( &$logActions, &$logActionsHandlers ) {
		# Various actions are used for log filtering ...
		$logActions['review/approve']  = 'review-logentry-app'; // checked (again)
		$logActions['review/approve2']  = 'review-logentry-app'; // quality (again)
		$logActions['review/approve-i']  = 'review-logentry-app'; // checked (first time)
		$logActions['review/approve2-i']  = 'review-logentry-app'; // quality (first time)
		$logActions['review/approve-a']  = 'review-logentry-app'; // checked (auto)
		$logActions['review/approve2-a']  = 'review-logentry-app'; // quality (auto)
		$logActions['review/approve-ia']  = 'review-logentry-app'; // checked (initial & auto)
		$logActions['review/approve2-ia']  = 'review-logentry-app'; // quality (initial & auto)
		$logActions['review/unapprove'] = 'review-logentry-dis'; // was checked
		$logActions['review/unapprove2'] = 'review-logentry-dis'; // was quality

		$logActionsHandlers['stable/config'] = 'FlaggedRevsLogView::stabilityLogText'; // customize
		$logActionsHandlers['stable/modify'] = 'FlaggedRevsLogView::stabilityLogText'; // re-customize
		$logActionsHandlers['stable/reset'] = 'FlaggedRevsLogView::stabilityLogText'; // reset
	}
}
