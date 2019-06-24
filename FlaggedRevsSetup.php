<?php
/**
 * Class containing basic setup functions.
 * This class depends on config variables in LocalSettings.php.
 * Note: avoid  FlaggedRevs class calls here for performance (like load.php).
 */
class FlaggedRevsSetup {
	/* Status of whether FlaggedRevs::load() can be called */
	protected static $canLoad = false;

	/**
	 * Signal that LocalSettings.php is loaded.
	 *
	 * @return void
	 */
	public static function setReady() {
		self::$canLoad = true;
	}

	/**
	 * The FlaggedRevs class uses this as a sanity check.
	 *
	 * @return bool
	 */
	public static function isReady() {
		return self::$canLoad;
	}

	/**
	 * Register FlaggedRevs source code paths.
	 *
	 * @return void
	 */
	public static function setConditionalHooks() {
		global $wgHooks, $wgFlaggedRevsProtection;

		if ( $wgFlaggedRevsProtection ) {
			# Add pending changes related magic words
			$wgHooks['ParserFirstCallInit'][] = 'FlaggedRevsHooks::onParserFirstCallInit';
			$wgHooks['ParserGetVariableValueSwitch'][] = 'FlaggedRevsHooks::onParserGetVariableValueSwitch';
			$wgHooks['MagicWordwgVariableIDs'][] = 'FlaggedRevsHooks::onMagicWordwgVariableIDs';
		}

		# ######## User interface #########
		FlaggedRevsUISetup::defineHookHandlers( $wgHooks );
		# ########
	}

	/**
	 * Set $wgAutopromoteOnce
	 *
	 * @return void
	 */
	public static function setAutopromoteConfig() {
		global $wgFlaggedRevsAutoconfirm, $wgFlaggedRevsAutopromote;
		global $wgAutopromoteOnce, $wgGroupPermissions;

		# $wgFlaggedRevsAutoconfirm is now a wrapper around $wgAutopromoteOnce
		$req = $wgFlaggedRevsAutoconfirm; // convenience
		if ( is_array( $req ) ) {
			$criteria = [ '&', // AND
				[ APCOND_AGE, $req['days'] * 86400 ],
				[ APCOND_EDITCOUNT, $req['edits'] ],
				[ APCOND_FR_EDITSUMMARYCOUNT, $req['editComments'] ],
				[ APCOND_FR_UNIQUEPAGECOUNT, $req['uniqueContentPages'] ],
				[ APCOND_FR_EDITSPACING, $req['spacing'], $req['benchmarks'] ],
				[ '|', // OR
					[ APCOND_FR_CONTENTEDITCOUNT,
						$req['totalContentEdits'], $req['excludeLastDays'] * 86400 ],
					[ APCOND_FR_CHECKEDEDITCOUNT,
						$req['totalCheckedEdits'], $req['excludeLastDays'] * 86400 ]
				],
			];
			if ( $req['email'] ) {
				$criteria[] = [ APCOND_EMAILCONFIRMED ];
			}
			if ( $req['neverBlocked'] ) {
				$criteria[] = [ APCOND_FR_NEVERBLOCKED ];
			}
			$wgAutopromoteOnce['onEdit']['autoreview'] = $criteria;
			$wgGroupPermissions['autoreview']['autoreview'] = true;
		}

		# $wgFlaggedRevsAutoconfirm is now a wrapper around $wgAutopromoteOnce
		$req = $wgFlaggedRevsAutopromote; // convenience
		if ( is_array( $req ) ) {
			$criteria = [ '&', // AND
				[ APCOND_AGE, $req['days'] * 86400 ],
				[ APCOND_FR_EDITCOUNT, $req['edits'], $req['excludeLastDays'] * 86400 ],
				[ APCOND_FR_EDITSUMMARYCOUNT, $req['editComments'] ],
				[ APCOND_FR_UNIQUEPAGECOUNT, $req['uniqueContentPages'] ],
				[ APCOND_FR_USERPAGEBYTES, $req['userpageBytes'] ],
				[ APCOND_FR_NEVERDEMOTED ], // for b/c
				[ APCOND_FR_EDITSPACING, $req['spacing'], $req['benchmarks'] ],
				[ '|', // OR
					[ APCOND_FR_CONTENTEDITCOUNT,
						$req['totalContentEdits'], $req['excludeLastDays'] * 86400 ],
					[ APCOND_FR_CHECKEDEDITCOUNT,
						$req['totalCheckedEdits'], $req['excludeLastDays'] * 86400 ]
				],
				[ APCOND_FR_MAXREVERTEDEDITRATIO, $req['maxRevertedEditRatio'] ],
				[ '!', APCOND_ISBOT ]
			];
			if ( $req['neverBlocked'] ) {
				$criteria[] = [ APCOND_FR_NEVERBLOCKED ];
			}
			$wgAutopromoteOnce['onEdit']['editor'] = $criteria;
		}
	}

	/**
	 * Set special page cache updates
	 *
	 * @return void
	 */
	public static function setSpecialPageCacheUpdates() {
		global $wgSpecialPageCacheUpdates;

		FlaggedRevsUISetup::defineSpecialPageCacheUpdates(
			$wgSpecialPageCacheUpdates );
	}

	/**
	 * Set API modules
	 *
	 * @return void
	 */
	public static function setAPIModules() {
		global $wgAPIModules, $wgAPIListModules;
		global $wgFlaggedRevsProtection;

		if ( $wgFlaggedRevsProtection ) {
			$wgAPIModules['stabilize'] = 'ApiStabilizeProtect';
		} else {
			$wgAPIModules['stabilize'] = 'ApiStabilizeGeneral';
			$wgAPIListModules['reviewedpages'] = 'ApiQueryReviewedpages';
			$wgAPIListModules['unreviewedpages'] = 'ApiQueryUnreviewedpages';
			$wgAPIListModules['configuredpages'] = 'ApiQueryConfiguredpages';
		}
	}

	/**
	 * Remove irrelevant user rights
	 *
	 * @return void
	 */
	public static function setConditionalRights() {
		global $wgGroupPermissions, $wgFlaggedRevsProtection;

		if ( $wgFlaggedRevsProtection ) {
			// XXX: Removes sp:ListGroupRights cruft
			if ( isset( $wgGroupPermissions['editor'] ) ) {
				unset( $wgGroupPermissions['editor']['unreviewedpages'] );
			}
			if ( isset( $wgGroupPermissions['reviewer'] ) ) {
				unset( $wgGroupPermissions['reviewer']['unreviewedpages'] );
				unset( $wgGroupPermissions['reviewer']['validate'] );
			}
		}
	}

	/**
	 * Set $wgDefaultUserOptions
	 *
	 * @return void
	 */
	public static function setConditionalPreferences() {
		global $wgDefaultUserOptions, $wgSimpleFlaggedRevsUI;

		$wgDefaultUserOptions['flaggedrevssimpleui'] = (int)$wgSimpleFlaggedRevsUI;
	}
}
