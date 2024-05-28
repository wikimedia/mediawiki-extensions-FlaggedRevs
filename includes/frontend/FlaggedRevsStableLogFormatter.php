<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;

class FlaggedRevsStableLogFormatter extends LogFormatter {

	/**
	 * @inheritDoc
	 */
	protected function getMessageKey(): string {
		return 'logentry-stable-' . $this->entry->getSubtype();
	}

	/**
	 * @inheritDoc
	 */
	protected function getMessageParameters(): array {
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

	/**
	 * @inheritDoc
	 */
	public function getActionLinks(): string {
		# Add history link showing edits right before the config change
		$hist = $this->getLinkRenderer()->makeLink(
			$this->entry->getTarget(),
			$this->msg( 'hist' )->text(),
			[],
			[ 'action' => 'history', 'offset' => $this->entry->getTimestamp() ]
		);
		return $this->msg( 'parentheses' )->rawParams( $hist )->escaped();
	}

	/**
	 * Make a list of stability settings for display
	 * Also used for null edit summary
	 *
	 * @param array $pars assoc array
	 * @param bool $forContent
	 */
	public static function stabilitySettings( array $pars, bool $forContent ): string {
		global $wgLang;
		$set = [];
		$settings = '';
		$langObj = $forContent ? MediaWikiServices::getInstance()->getContentLanguage() : $wgLang;
		// Protection-based or deferral-based configs (precedence never changed)...
		if ( !isset( $pars['precedence'] ) ) {
			if ( isset( $pars['autoreview'] ) && strlen( $pars['autoreview'] ) ) {
				$set[] = wfMessage( 'stable-log-restriction', $pars['autoreview'] )->inLanguage(
					$langObj )->escaped();
			}
		// General case...
		} else {
			// Default version shown on page view
			if ( isset( $pars['override'] ) ) {
				// Give grep a chance to find the usages:
				// stabilization-def-short-0, stabilization-def-short-1
				$set[] = wfMessage( 'stabilization-def-short' )->inLanguage( $langObj )->escaped() .
					wfMessage( 'colon-separator' )->inLanguage( $langObj )->escaped() .
					wfMessage( 'stabilization-def-short-' . $pars['override'] )
						->inLanguage( $langObj )->escaped();
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
			)->inLanguage( $langObj )->escaped();
			if ( $settings != '' ) {
				$settings .= ' ';
			}
			$settings .= wfMessage( 'parentheses' )->rawParams( $expiry_description )
				->inLanguage( $langObj )->escaped();
		}
		return $settings;
	}
}
