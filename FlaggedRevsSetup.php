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
	 * Set $wgAutopromoteOnce
	 */
	public static function setAutopromoteConfig() {
		global $wgFlaggedRevsAutoconfirm, $wgFlaggedRevsAutopromote;
		global $wgAutopromoteOnce, $wgGroupPermissions;

		# $wgFlaggedRevsAutoconfirm is now a wrapper around $wgAutopromoteOnce
		if ( is_array( $wgFlaggedRevsAutoconfirm ) ) {
			$criteria = [ '&', // AND
				[ APCOND_AGE, $wgFlaggedRevsAutoconfirm['days'] * 86400 ],
				[ APCOND_EDITCOUNT, $wgFlaggedRevsAutoconfirm['edits'] ],
				[ APCOND_FR_EDITSUMMARYCOUNT, $wgFlaggedRevsAutoconfirm['editComments'] ],
				[ APCOND_FR_UNIQUEPAGECOUNT, $wgFlaggedRevsAutoconfirm['uniqueContentPages'] ],
				[
					APCOND_FR_EDITSPACING,
					$wgFlaggedRevsAutoconfirm['spacing'],
					$wgFlaggedRevsAutoconfirm['benchmarks']
				],
				[ '|', // OR
					[
						APCOND_FR_CONTENTEDITCOUNT,
						$wgFlaggedRevsAutoconfirm['totalContentEdits'],
						$wgFlaggedRevsAutoconfirm['excludeLastDays'] * 86400
					],
					[
						APCOND_FR_CHECKEDEDITCOUNT,
						$wgFlaggedRevsAutoconfirm['totalCheckedEdits'],
						$wgFlaggedRevsAutoconfirm['excludeLastDays'] * 86400
					]
				],
			];
			if ( $wgFlaggedRevsAutoconfirm['email'] ) {
				$criteria[] = [ APCOND_EMAILCONFIRMED ];
			}
			if ( $wgFlaggedRevsAutoconfirm['neverBlocked'] ) {
				$criteria[] = [ APCOND_FR_NEVERBLOCKED ];
			}
			$wgAutopromoteOnce['onEdit']['autoreview'] = $criteria;
			$wgGroupPermissions['autoreview']['autoreview'] = true;
		}

		# $wgFlaggedRevsAutopromote is now a wrapper around $wgAutopromoteOnce
		if ( is_array( $wgFlaggedRevsAutopromote ) ) {
			$criteria = [ '&', // AND
				[ APCOND_AGE, $wgFlaggedRevsAutopromote['days'] * 86400 ],
				[
					APCOND_FR_EDITCOUNT,
					$wgFlaggedRevsAutopromote['edits'],
					$wgFlaggedRevsAutopromote['excludeLastDays'] * 86400
				],
				[ APCOND_FR_EDITSUMMARYCOUNT, $wgFlaggedRevsAutopromote['editComments'] ],
				[ APCOND_FR_UNIQUEPAGECOUNT, $wgFlaggedRevsAutopromote['uniqueContentPages'] ],
				[ APCOND_FR_USERPAGEBYTES, $wgFlaggedRevsAutopromote['userpageBytes'] ],
				[ APCOND_FR_NEVERDEMOTED ], // for b/c
				[
					APCOND_FR_EDITSPACING,
					$wgFlaggedRevsAutopromote['spacing'],
					$wgFlaggedRevsAutopromote['benchmarks']
				],
				[ '|', // OR
					[
						APCOND_FR_CONTENTEDITCOUNT,
						$wgFlaggedRevsAutopromote['totalContentEdits'],
						$wgFlaggedRevsAutopromote['excludeLastDays'] * 86400
					],
					[
						APCOND_FR_CHECKEDEDITCOUNT,
						$wgFlaggedRevsAutopromote['totalCheckedEdits'],
						$wgFlaggedRevsAutopromote['excludeLastDays'] * 86400
					]
				],
				[ APCOND_FR_MAXREVERTEDEDITRATIO, $wgFlaggedRevsAutopromote['maxRevertedEditRatio'] ],
				[ '!', APCOND_ISBOT ]
			];
			if ( $wgFlaggedRevsAutopromote['neverBlocked'] ) {
				$criteria[] = [ APCOND_FR_NEVERBLOCKED ];
			}
			$wgAutopromoteOnce['onEdit']['editor'] = $criteria;
		}
	}

	/**
	 * Set special page cache updates
	 */
	public static function setSpecialPageCacheUpdates() {
		global $wgSpecialPageCacheUpdates;

		FlaggedRevsUISetup::defineSpecialPageCacheUpdates(
			$wgSpecialPageCacheUpdates );
	}

	/**
	 * Set API modules
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
	 */
	public static function setConditionalPreferences() {
		global $wgDefaultUserOptions, $wgSimpleFlaggedRevsUI;

		$wgDefaultUserOptions['flaggedrevssimpleui'] = (int)$wgSimpleFlaggedRevsUI;
	}
}