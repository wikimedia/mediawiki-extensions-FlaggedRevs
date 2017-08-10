<?php

class FlaggedRevsLog {
	/**
	 * Record a log entry on the review action
	 * @param Title $title
	 * @param array $dims
	 * @param array $oldDims
	 * @param string $comment
	 * @param int $revId revision ID
	 * @param int $stableId prior stable revision ID
	 * @param bool $approve approved? (otherwise unapproved)
	 * @param bool $auto
	 * @param User $user performing the action
	 */
	public static function updateReviewLog(
		Title $title, array $dims, array $oldDims,
		$comment, $revId, $stableId, $approve, $auto = false, $user
	) {
		# Tag rating list (e.g. accuracy=x, depth=y, style=z)
		$ratings = [];
		# Skip rating list if flagging is just an 0/1 feature...
		if ( !FlaggedRevs::binaryFlagging() ) {
			// Give grep a chance to find the usages:
			// revreview-accuracy, revreview-depth, revreview-style,
			// revreview-accuracy-0, revreview-accuracy-1, revreview-accuracy-2,
			// revreview-accuracy-3, revreview-accuracy-4, revreview-depth-0, revreview-depth-1,
			// revreview-depth-2, revreview-depth-3, revreview-depth-4, revreview-style-0,
			// revreview-style-1, revreview-style-2, revreview-style-3, revreview-style-4
			foreach ( $dims as $quality => $level ) {
				$ratings[] = wfMessage( "revreview-$quality" )->inContentLanguage()->text() .
					wfMessage( 'colon-separator' )->inContentLanguage()->text() .
					wfMessage( "revreview-$quality-$level" )->inContentLanguage()->text();
			}
		}
		$isAuto = ( $auto && !FlaggedRevs::isQuality( $dims ) ); // Paranoid check
		// Approved revisions
		if ( $approve ) {
			# Make comma-separated list of ratings
			$rating = !empty( $ratings )
				? '[' . implode( ', ', $ratings ) . ']'
				: '';
			# Append comment with ratings
			if ( $rating != '' ) {
				$comment .= $comment ? " $rating" : $rating;
			}
			# Sort into the proper action (useful for filtering)
			$action = ( FlaggedRevs::isQuality( $dims ) || FlaggedRevs::isQuality( $oldDims ) ) ?
				'approve2' : 'approve';
			if ( !$stableId ) { // first time
				$action .= $isAuto ? "-ia" : "-i";
			} elseif ( $isAuto ) { // automatic
				$action .= "-a";
			}
		// De-approved revisions
		} else {
			$action = FlaggedRevs::isQuality( $oldDims ) ?
				'unapprove2' : 'unapprove';
		}
		$ts = Revision::getTimestampFromId( $title, $revId, Revision::READ_LATEST );

		$logEntry = new ManualLogEntry( 'review', $action );
		$logEntry->setPerformer( $user );
		$logEntry->setTarget( $title );
		$logEntry->setComment( $comment );

		# Param format is <rev id, old stable id, rev timestamp>
		$logEntry->setParameters( [ $revId, $stableId, $ts ] );
		# Make log easily searchable by rev_id
		$logEntry->setRelations( [ 'rev_id' => $revId ] );

		$logid = $logEntry->insert();
		if ( !$auto ) {
			$logEntry->publish( $logid, 'udp' );
		}
	}

	/**
	 * Record a log entry on the stability config change action
	 * @param Title $title
	 * @param array $config
	 * @param array $oldConfig
	 * @param string $reason
	 * @param User $user performing the action
	 */
	public static function updateStabilityLog(
		Title $title, array $config, array $oldConfig, $reason, $user
	) {
		if ( FRPageConfig::configIsReset( $config ) ) {
			# We are going back to default settings
			$action = 'reset';
		} else {
			# We are changing to non-default settings
			$action = ( $oldConfig === FRPageConfig::getDefaultVisibilitySettings() )
				? 'config' // set a custom configuration
				: 'modify'; // modified an existing custom configuration
		}

		$logEntry = new ManualLogEntry( 'stable', $action );
		$logEntry->setPerformer( $user );
		$logEntry->setTarget( $title );
		$logEntry->setComment( $reason );
		$params = self::stabilityLogParams( $config );
		$logEntry->setParameters( $params );

		$logId = $logEntry->insert();
		$logEntry->publish( $logId );
	}

	/**
	 * Record move of settings in stability log
	 * @param Title $newTitle
	 * @param Title $oldTitle
	 * @param string $reason
	 * @param User $user performing the action
	 */
	public static function updateStabilityLogOnMove(
		Title $newTitle, Title $oldTitle, $reason, $user
	) {
		$logEntry = new ManualLogEntry( 'stable', 'move_stable' );
		$logEntry->setPerformer( $user );
		$logEntry->setTarget( $newTitle );

		// Build comment for log
		$comment = wfMessage(
			'prot_1movedto2',
			$oldTitle->getPrefixedText(),
			$newTitle->getPrefixedText()
		)->inContentLanguage()->text();
		if ( $reason ) {
			$comment .= wfMessage( 'colon-separator' )->inContentLanguage()->text() . $reason;
		}
		$logEntry->setComment( $comment );

		$logEntry->setParameters( [
			'4::oldtitle' => $oldTitle->getPrefixedText(),
		] );

		$logId = $logEntry->insert();
		$logEntry->publish( $logId );
	}

	/**
	 * Get log params (associate array) from a stability config
	 * @param array $config
	 * @return array (associative)
	 */
	public static function stabilityLogParams( array $config ) {
		$params = $config;
		if ( !FlaggedRevs::useOnlyIfProtected() ) {
			$params['precedence'] = 1; // b/c hack for presenting log params...
		}
		return $params;
	}

	/**
	 * Expand a list of log params into an associative array
	 * For legacy log entries
	 * @param array $pars
	 * @return array (associative)
	 */
	public static function expandParams( array $pars ) {
		$res = [];
		$pars = array_filter( $pars, 'strlen' );
		foreach ( $pars as $paramAndValue ) {
			list( $param, $value ) = explode( '=', $paramAndValue, 2 );
			$res[$param] = $value;
		}
		return $res;
	}
}
