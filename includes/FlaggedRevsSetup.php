<?php

use MediaWiki\Config\Config;

/**
 * Class containing basic setup functions.
 * This class depends on config variables in LocalSettings.php.
 * Note: avoid FlaggedRevs class calls here for performance (like load.php).
 */
class FlaggedRevsSetup {

	private Config $config;

	public function __construct( Config $config ) {
		$this->config = $config;
	}

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
		global $wgAutopromoteOnce, $wgGroupPermissions;

		# $wgFlaggedRevsAutoconfirm is now a wrapper around $wgAutopromoteOnce
		$autoconfirm = $this->config->get( 'FlaggedRevsAutoconfirm' );
		if ( is_array( $autoconfirm ) ) {
			$criteria = [ '&', // AND
				[ APCOND_AGE, $autoconfirm['days'] * 86400 ],
				[ APCOND_EDITCOUNT, $autoconfirm['edits'] ],
				[ APCOND_FR_EDITSUMMARYCOUNT, $autoconfirm['editComments'] ],
				[ APCOND_FR_UNIQUEPAGECOUNT, $autoconfirm['uniqueContentPages'] ],
				[
					APCOND_FR_EDITSPACING,
					$autoconfirm['spacing'],
					$autoconfirm['benchmarks']
				],
				[ '|', // OR
					[
						APCOND_FR_CONTENTEDITCOUNT,
						$autoconfirm['totalContentEdits'],
						$autoconfirm['excludeLastDays'] * 86400
					],
					[
						APCOND_FR_CHECKEDEDITCOUNT,
						$autoconfirm['totalCheckedEdits'],
						$autoconfirm['excludeLastDays'] * 86400
					]
				],
			];
			if ( $autoconfirm['email'] ) {
				$criteria[] = [ APCOND_EMAILCONFIRMED ];
			}
			if ( $autoconfirm['neverBlocked'] ) {
				$criteria[] = [ APCOND_FR_NEVERBLOCKED ];
			}
			$wgAutopromoteOnce['onEdit']['autoreview'] = $criteria;
			$wgGroupPermissions['autoreview']['autoreview'] = true;
		}

		# $wgFlaggedRevsAutopromote is now a wrapper around $wgAutopromoteOnce
		$autopromote = $this->config->get( 'FlaggedRevsAutopromote' );
		if ( is_array( $autopromote ) ) {
			$criteria = [ '&', // AND
				[ APCOND_AGE, $autopromote['days'] * 86400 ],
				[
					APCOND_FR_EDITCOUNT,
					$autopromote['edits'],
					$autopromote['excludeLastDays'] * 86400
				],
				[ APCOND_FR_EDITSUMMARYCOUNT, $autopromote['editComments'] ],
				[ APCOND_FR_UNIQUEPAGECOUNT, $autopromote['uniqueContentPages'] ],
				[ APCOND_FR_USERPAGEBYTES, $autopromote['userpageBytes'] ],
				[ APCOND_FR_NEVERDEMOTED ], // for b/c
				[
					APCOND_FR_EDITSPACING,
					$autopromote['spacing'],
					$autopromote['benchmarks']
				],
				[ '|', // OR
					[
						APCOND_FR_CONTENTEDITCOUNT,
						$autopromote['totalContentEdits'],
						$autopromote['excludeLastDays'] * 86400
					],
					[
						APCOND_FR_CHECKEDEDITCOUNT,
						$autopromote['totalCheckedEdits'],
						$autopromote['excludeLastDays'] * 86400
					]
				],
				[ APCOND_FR_MAXREVERTEDEDITRATIO, $autopromote['maxRevertedEditRatio'] ],
				[ '!', APCOND_ISBOT ]
			];
			if ( $autopromote['neverBlocked'] ) {
				$criteria[] = [ APCOND_FR_NEVERBLOCKED ];
			}
			$wgAutopromoteOnce['onEdit']['editor'] = $criteria;
		}
	}

	private function setSpecialPageCacheUpdates() {
		global $wgSpecialPageCacheUpdates;

		// Show special pages only if FlaggedRevs is enabled on some namespaces
		if ( $this->config->get( 'FlaggedRevsNamespaces' ) ) {
			if ( !$this->config->get( 'FlaggedRevsProtection' ) ) {
				$wgSpecialPageCacheUpdates['UnreviewedPages'] = [ UnreviewedPages::class, 'updateQueryCache' ];
			}
			$wgSpecialPageCacheUpdates['ValidationStatistics'] = [ FlaggedRevsStats::class, 'updateCache' ];
		}
	}

	private function setAPIModules() {
		global $wgAPIModules, $wgAPIListModules;

		if ( $this->config->get( 'FlaggedRevsProtection' ) ) {
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
		global $wgGroupPermissions;

		if ( $this->config->get( 'FlaggedRevsProtection' ) ) {
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
		global $wgDefaultUserOptions;

		$wgDefaultUserOptions['flaggedrevssimpleui'] = (int)$this->config->get( 'SimpleFlaggedRevsUI' );
	}
}
