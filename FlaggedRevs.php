<?php
/**
 * (c) Aaron Schulz, Joerg Baach, 2007-2008 GPL
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'FlaggedRevs' );

	$wgMessagesDirs['FlaggedRevs'] = __DIR__ . '/i18n/flaggedrevs';
	$wgMessagesDirs['RevisionReview'] = __DIR__ . '/i18n/revisionreview';
	$wgMessagesDirs['Stabilization'] = __DIR__ . '/i18n/stabilization';
	$wgMessagesDirs['ReviewedVersions'] = __DIR__ . '/i18n/reviewedversions';
	$wgMessagesDirs['UnreviewedPages'] = __DIR__ . '/i18n/unreviewedpages';
	$wgMessagesDirs['PendingChanges'] = __DIR__ . '/i18n/pendingchanges';
	$wgMessagesDirs['ProblemChanges'] = __DIR__ . '/i18n/problemchanges';
	$wgMessagesDirs['ReviewedPages'] = __DIR__ . '/i18n/reviewedpages';
	$wgMessagesDirs['StablePages'] = __DIR__ . '/i18n/stablepages';
	$wgMessagesDirs['ConfiguredPages'] = __DIR__ . '/i18n/configuredpages';
	$wgMessagesDirs['QualityOversight'] = __DIR__ . '/i18n/qualityoversight';
	$wgMessagesDirs['ValidationStatistics'] = __DIR__ . '/i18n/validationstatistics';
	$wgMessagesDirs['FlaggedRevsApi'] = __DIR__ . '/i18n/api';
	$wgExtensionMessagesFiles['FlaggedRevsMagic'] = __DIR__ .
		'/frontend/language/FlaggedRevs.i18n.magic.php';
	$wgExtensionMessagesFiles['FlaggedRevsAliases'] = __DIR__ .
		'/frontend/language/FlaggedRevs.alias.php';

	/*wfWarn(
		'Deprecated PHP entry point used for Flagged Revisions extension. ' .
		'Please use wfLoadExtension instead, see' .
		'https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);*/
	return true;
}

die( 'This version of the Flagged Revisions extension requires MediaWiki 1.34+' );
