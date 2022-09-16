<?php

/**
 * Class containing basic setup functions.
 * This class depends on config variables in LocalSettings.php.
 * Note: avoid  FlaggedRevs class calls here for performance (like load.php).
 */
class FlaggedRevsSetup {

	/**
	 * Entry point for hook handler
	 *
	 * TODO make this the hook handler directly, and combine the methods here
	 */
	public function doSetup() {
		# Conditional autopromote groups
		$this->setAutopromoteConfig();

		# Register special pages (some are conditional)
		$this->setSpecialPageCacheUpdates();
		# Conditional API modules
		$this->setAPIModules();
		# Remove conditionally applicable rights
		$this->setConditionalRights();
		# Defaults for user preferences
		$this->setConditionalPreferences();
	}

	/**
	 * Set $wgAutopromoteOnce
	 */
	private function setAutopromoteConfig() {
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

	private function setSpecialPageCacheUpdates() {
		global $wgSpecialPageCacheUpdates, $wgFlaggedRevsProtection, $wgFlaggedRevsNamespaces;

		// Show special pages only if FlaggedRevs is enabled on some namespaces
		if ( $wgFlaggedRevsNamespaces ) {
			if ( !$wgFlaggedRevsProtection ) {
				$wgSpecialPageCacheUpdates['UnreviewedPages'] = [ UnreviewedPages::class, 'updateQueryCache' ];
			}
			$wgSpecialPageCacheUpdates['ValidationStatistics'] = [ FlaggedRevsStats::class, 'updateCache' ];
		}
	}

	private function setAPIModules() {
		global $wgAPIModules, $wgAPIListModules;
		global $wgFlaggedRevsProtection;

		if ( $wgFlaggedRevsProtection ) {
			$wgAPIModules['stabilize'] = ApiStabilizeProtect::class;
		} else {
			$wgAPIModules['stabilize'] = ApiStabilizeGeneral::class;
			$wgAPIListModules['unreviewedpages'] = ApiQueryUnreviewedpages::class;
			$wgAPIListModules['configuredpages'] = ApiQueryConfiguredpages::class;
		}
	}

	/**
	 * Remove irrelevant user rights
	 */
	private function setConditionalRights() {
		global $wgGroupPermissions, $wgFlaggedRevsProtection;

		if ( $wgFlaggedRevsProtection ) {
			// XXX: Removes sp:ListGroupRights cruft
			unset( $wgGroupPermissions['editor']['unreviewedpages'] );
			unset( $wgGroupPermissions['reviewer']['unreviewedpages'] );
			unset( $wgGroupPermissions['reviewer']['validate'] );
		}
	}

	/**
	 * Set $wgDefaultUserOptions
	 */
	private function setConditionalPreferences() {
		global $wgDefaultUserOptions, $wgSimpleFlaggedRevsUI;

		$wgDefaultUserOptions['flaggedrevssimpleui'] = (int)$wgSimpleFlaggedRevsUI;
	}
}
