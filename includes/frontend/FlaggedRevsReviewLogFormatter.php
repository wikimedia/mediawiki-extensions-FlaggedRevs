<?php

use MediaWiki\Revision\RevisionLookup;

class FlaggedRevsReviewLogFormatter extends LogFormatter {
	private bool $isDeapproval = false;
	private RevisionLookup $revisionLookup;

	public function __construct(
		LogEntry $entry,
		RevisionLookup $revisionLookup
	) {
		parent::__construct( $entry );
		$this->revisionLookup = $revisionLookup;
	}

	/**
	 * @inheritDoc
	 */
	protected function getMessageKey(): string {
		$rawAction = $this->entry->getSubtype();
		if ( !str_starts_with( $rawAction, 'a' ) ) {
			// unapprove, unapprove2
			$this->isDeapproval = true;
			return 'logentry-review-unapprove';
		} elseif ( str_ends_with( $rawAction, 'a' ) ) {
			// approve-a, approve2-a, approve-ia, approve2-ia
			return 'logentry-review-approve-auto';
		} else {
			// approve, approve2, approve-i, approve2-i
			return 'logentry-review-approve';
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getActionLinks(): string {
		$title = $this->entry->getTarget();
		$params = $this->entry->getParameters();
		$links = '';
		# Show link to page with oldid=x as well as the diff to the former stable rev.
		# Param format is <rev id, last stable id, rev timestamp>.
		if ( isset( $params[0] ) ) {
			$revId = (int)$params[0]; // the revision reviewed
			$oldStable = (int)( $params[1] ?? 0 );
			# Show diff to changes since the prior stable version
			if ( $oldStable && $revId > $oldStable ) {
				$msg = $this->isDeapproval
					? 'review-logentry-diff2' // unreviewed
					: 'review-logentry-diff'; // reviewed
				$links .= '(';
				$links .= $this->getLinkRenderer()->makeKnownLink(
					$title,
					$this->msg( $msg )->text(),
					[],
					[ 'oldid' => $oldStable, 'diff' => $revId ]
				);
				$links .= ')';
			}
			# Show a diff link to this revision
			$ts = empty( $params[2] )
				? $this->revisionLookup->getTimestampFromId( $revId )
				: $params[2];
			$time = $this->context->getLanguage()->timeanddate( $ts, true );
			$links .= ' (';
			$links .= $this->getLinkRenderer()->makeKnownLink(
				$title,
				$this->msg( 'review-logentry-id', $revId, $time )->text(),
				[],
				[ 'oldid' => $revId, 'diff' => 'prev' ]
			);
			$links .= ')';
		}
		return $links;
	}
}
