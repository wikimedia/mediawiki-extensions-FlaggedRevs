<?php

class FlaggedRevsReviewLogFormatter extends LogFormatter {
	protected $isDeapproval = false;

	protected function getMessageKey() {
		$rawAction = $this->entry->getSubtype();
		if ( $rawAction[0] === 'a' ) {
			if ( $rawAction[strlen( $rawAction ) - 1] === 'a' ) {
				// approve-a, approve2-a, approve-ia, approve2-ia
				return "logentry-review-approve-auto";
			} else {
				// approve, approve2, approve-i, approve2-i
				return "logentry-review-approve";
			}
		} else {
			// unapprove, unapprove2
			$this->isDeapproval = true;
			return "logentry-review-unapprove";
		}
	}

	public function getActionLinks() {
		$title = $this->entry->getTarget();
		$params = $this->entry->getParameters();
		$links = '';
		# Show link to page with oldid=x as well as the diff to the former stable rev.
		# Param format is <rev id, last stable id, rev timestamp>.
		if ( isset( $params[0] ) ) {
			$revId = (int)$params[0]; // the revision reviewed
			$oldStable = isset( $params[1] ) ? (int)$params[1] : 0;
			# Show diff to changes since the prior stable version
			if ( $oldStable && $revId > $oldStable ) {
				$msg = $this->isDeapproval
					? 'review-logentry-diff2' // unreviewed
					: 'review-logentry-diff'; // reviewed
				$links .= '(';
				$links .= Linker::linkKnown(
					$title,
					wfMessage( $msg )->escaped(),
					[],
					[ 'oldid' => $oldStable, 'diff' => $revId ]
				);
				$links .= ')';
			}
			# Show a diff link to this revision
			$ts = empty( $params[2] )
				? Revision::getTimestampFromId( $title, $revId )
				: $params[2];
			$time = $this->context->getLanguage()->timeanddate( $ts, true );
			$links .= ' (';
			$links .= Linker::linkKnown(
				$title,
				wfMessage( 'review-logentry-id', $revId, $time )->escaped(),
				[],
				[ 'oldid' => $revId, 'diff' => 'prev' ] + FlaggedRevs::diffOnlyCGI()
			);
			$links .= ')';
		}
		return $links;
	}
}
