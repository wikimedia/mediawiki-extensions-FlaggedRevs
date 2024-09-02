/**
 * FlaggedRevs Advanced JavaScript
 *
 * @author Aaron Schulz
 * @author Krinkle <krinklemail@gmail.com> 2011
 */
( function () {
	'use strict';

	/* Expands flag info box details */
	function showBoxDetails() {
		var $revisionDetailDialog = $( '#mw-fr-revision-details' );
		return mw.loader.using( [
			'codex-styles'
		] ).then( function () {
			$revisionDetailDialog.css( 'display', 'block' );
		} );
	}

	/* Collapses flag info box details */
	function hideBoxDetails() {
		$( '#mw-fr-revision-details' ).css( 'display', 'none' );
	}

	/**
	 * Toggles diffs
	 *
	 * @this {jQuery}
	 * @return {boolean}
	 */
	function toggleDiff() {
		var $diff = $( '#mw-fr-stable-diff' ),
			$toggle = $( '#mw-fr-diff-toggle' );

		if ( !$diff.length ) {
			var alignStart, rtlDir;
			rtlDir = $( '#mw-content-text' ).attr( 'dir' ) === 'rtl';
			alignStart = rtlDir ? 'right' : 'left';
			$diff = $( '<div>' )
				.hide()
				.attr( 'id', 'mw-fr-stable-diff' )
				// The following classes are used here:
				// * diff-editfont-monospace
				// * diff-editfont-sans-serif
				// * diff-editfont-serif
				.addClass( 'diff-editfont-' + mw.user.options.get( 'editfont' ) )
				// The following classes are used here:
				// * diff-contentalign-left
				// * diff-contentalign-right
				.addClass( 'diff-contentalign-' + alignStart )
				.append(
					$( '<table>' ).addClass( 'diff' ).append(
						$( '<col>' ).addClass( 'diff-marker' ),
						$( '<col>' ).addClass( 'diff-content' ),
						$( '<col>' ).addClass( 'diff-marker' ),
						$( '<col>' ).addClass( 'diff-content' ),
						$( '<thead>' ).append(
							$( '<tr>' ).addClass( 'diff-title' ).append(
								$( '<td>' )
									.attr( 'colspan', 2 )
									.addClass( 'diff-otitle diff-side-deleted' )
									.text( mw.msg( 'brackets', mw.msg( 'revreview-hist-basic' ) ) )
									.wrapInner( '<span class="flaggedrevs-color-1">' )
									.wrapInner( '<b>' ),
								$( '<td>' )
									.attr( 'colspan', 2 )
									.addClass( 'diff-ntitle diff-side-added' )
									.text( mw.msg( 'brackets', mw.msg( 'revreview-hist-pending' ) ) )
									.wrapInner( '<span class="flaggedrevs-color-0">' )
									.wrapInner( '<b>' )
							)
						),
						$( '<tbody>' ).append(
							$( '<tr>' ).append(
								$( '<td>' )
									.attr( 'colspan', 4 )
									.addClass( 'diff-notice' )
									.append( $.createSpinner( { size: 'large', type: 'block' } ) )
							)
						)
					)
				);

			var multiNotice = $toggle.find( 'a' ).data( 'mw-multinotice' );
			if ( multiNotice ) {
				$diff.find( 'thead' ).append(
					$( '<tr>' ).append(
						$( '<td>' )
							.attr( 'colspan', 4 )
							.addClass( 'diff-multi' )
							.html( multiNotice )
					)
				);
			}

			$toggle.after( $diff );

			var diffPar = {
				action: 'compare',
				fromrev: $toggle.find( 'a' ).data( 'mw-fromrev' ),
				torev: $toggle.find( 'a' ).data( 'mw-torev' ),
				slots: 'main',
				uselang: mw.config.get( 'wgUserLanguage' )
			};
			if ( mw.config.get( 'wgUserVariant' ) ) {
				diffPar.variant = mw.config.get( 'wgUserVariant' );
			}

			new mw.Api().post( diffPar ).then( function handleDiffResponse( response ) {
				var $table = $diff.find( 'table.diff' );

				if ( response.compare.bodies.main ) {
					var diff = response.compare.bodies;

					$table.find( 'tbody' ).html( diff.main );
					mw.hook( 'wikipage.diff' ).fire( $table );
				} else {
					// The diff is empty.
					var $tableCell = $( '<td>' )
						.attr( 'colspan', 4 )
						.addClass( 'diff-notice' )
						.append(
							$( '<div>' )
								.addClass( 'mw-diff-empty' )
								.text( mw.msg( 'diff-empty' ) )
						);
					$table.find( 'tbody' )
						.empty()
						.append(
							$( '<tr>' ).append( $tableCell )
						);
				}
				$diff.show();
			} );
		}

		if ( $diff.length && $toggle.length ) {
			if ( $diff.css( 'display' ) === 'none' ) {
				// FIXME: Use CSS transition
				// eslint-disable-next-line no-jquery/no-animate-toggle
				$diff.show( 'slow' );
				$toggle.children( 'a' ).text( mw.msg( 'revreview-diff-toggle-hide' ) );
			} else {
				// FIXME: Use CSS transition
				// eslint-disable-next-line no-jquery/no-animate-toggle
				$diff.hide( 'slow' );
				$toggle.children( 'a' ).text( mw.msg( 'revreview-diff-toggle-show' ) );
			}
		}

		return false;
	}

	/**
	 * Startup function
	 */
	function init() {
		// Enables rating detail box
		var $toggle = $( '#mw-fr-revision-toggle' );

		if ( $toggle.length ) {
			hideBoxDetails(); // hide the initially displayed ratings
		}

		// Simple UI: Show the box on mouseOver
		$toggle.on( 'mouseover', showBoxDetails );

		// Enables diff detail box and toggle
		$toggle = $( '#mw-fr-diff-toggle' );
		$toggle.children( 'a' ).on( 'click', toggleDiff );

		// Close the mw-fr-revision-details dialog on ESC key press
		document.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Escape' ) {
				var dialog = document.getElementById( 'mw-fr-revision-details' );
				if ( dialog && dialog.style.display !== 'none' ) {
					dialog.style.display = 'none';
				}
			}
		} );
	}

	// Perform some onload events:
	$( init );

}() );
