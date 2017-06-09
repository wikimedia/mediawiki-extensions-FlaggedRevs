<?php

class FlaggedRevsStableLogFormatter extends LogFormatter {

	protected function getMessageKey() {
		$rawAction = $this->entry->getSubtype();
		return "logentry-stable-{$rawAction}";
	}

	protected function getMessageParameters() {
		$params = parent::getMessageParameters();
		$action = $this->entry->getSubtype();
		if ( $action !== 'move_stable' ) {
			# Add setting change description as a param
			$settings = $this->entry->getParameters();
			$settings = $this->entry->isLegacy() ? FlaggedRevsLog::expandParams( $settings ) : $settings;
			$params[3] = self::stabilitySettings( $settings, false );
		} else {
			$oldName = $this->makePageLink( Title::newFromText( $params[3] ), [ 'redirect' => 'no' ] );
			$params[3] = Message::rawParam( $oldName );
		}
		return $params;
	}

	public function getActionLinks() {
		# Add history link showing edits right before the config change
		$hist = Linker::link(
			$this->entry->getTarget(),
			wfMessage( 'hist' )->escaped(),
			[],
			[ 'action' => 'history', 'offset' => $this->entry->getTimestamp() ]
		);
		$hist = wfMessage( 'parentheses' )->rawParams( $hist )->escaped();
		return $hist;
	}

	/**
	 * Make a list of stability settings for display
	 * Also used for null edit summary
	 *
	 * @param array $pars assoc array
	 * @param bool $forContent
	 * @return string
	 */
	public static function stabilitySettings( array $pars, $forContent ) {
		global $wgLang, $wgContLang;
		$set = [];
		$settings = '';
		$langObj = $forContent ? $wgContLang : $wgLang;
		// Protection-based or deferral-based configs (precedence never changed)...
		if ( !isset( $pars['precedence'] ) ) {
			if ( isset( $pars['autoreview'] ) && strlen( $pars['autoreview'] ) ) {
				$set[] = wfMessage( 'stable-log-restriction', $pars['autoreview'] )->inLanguage(
					$langObj )->text();
			}
		// General case...
		} else {
			// Default version shown on page view
			if ( isset( $pars['override'] ) ) {
				// Give grep a chance to find the usages:
				// stabilization-def-short-0, stabilization-def-short-1
				$set[] = wfMessage( 'stabilization-def-short' )->inLanguage( $langObj )->text() .
					wfMessage( 'colon-separator' )->inLanguage( $langObj )->text() .
					wfMessage( 'stabilization-def-short-' . $pars['override'] )
						->inLanguage( $langObj )->text();
			}
			// Autoreview restriction
			if ( isset( $pars['autoreview'] ) && strlen( $pars['autoreview'] ) ) {
				$set[] = 'autoreview=' . $pars['autoreview'];
			}
		}
		if ( $set ) {
			$settings = '[' . $langObj->commaList( $set ) . ']';
		}
		# Expiry is a MW timestamp or 'infinity'
		if ( isset( $pars['expiry'] ) && $pars['expiry'] != 'infinity' ) {
			$expiry_description = wfMessage( 'stabilize-expiring',
				$langObj->timeanddate( $pars['expiry'], false, false ),
				$langObj->date( $pars['expiry'], false, false ),
				$langObj->time( $pars['expiry'], false, false )
			)->inLanguage( $langObj )->text();
			if ( $settings != '' ) {
				$settings .= ' ';
			}
			$settings .= wfMessage( 'parentheses', $expiry_description )->inLanguage( $langObj )->text();
		}
		return htmlspecialchars( $settings );
	}
}
