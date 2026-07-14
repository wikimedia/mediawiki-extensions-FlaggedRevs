<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\FlaggedRevs\Test\Integration\Frontend\SpecialPages\Reports;

use MediaWiki\Context\RequestContext;
use MediaWiki\Tests\ChangeTags\RestrictedTagTestTrait;
use MediaWiki\Tests\Unit\Permissions\MockAuthorityTrait;
use MediaWikiIntegrationTestCase;
use PendingChanges;
use PendingChangesPager;

/**
 * @covers PendingChangesPager
 * @group Database
 */
class PendingChangesPagerTest extends MediaWikiIntegrationTestCase {
	use RestrictedTagTestTrait;
	use MockAuthorityTrait;

	private function getPager( array $authorityRights ): PendingChangesPager {
		RequestContext::getMain()->setAuthority( $this->mockRegisteredAuthorityWithPermissions( $authorityRights ) );

		return new PendingChangesPager(
			$this->createMock( PendingChanges::class ),
			NS_MAIN,
			'Foo Bar',
			null,
			false,
			false,
			'mw-private-test'
		);
	}

	/** @dataProvider provideGetQueryInfoForTagFilter */
	public function testGetQueryInfoForTagFilter( array $authorityRights, bool $userCanSeeTag ): void {
		$this->setRestrictedTags( [ 'mw-private-test' => 'patrol' ] );

		$queryInfo = $this->getPager( $authorityRights )->getQueryInfo();

		if ( $userCanSeeTag ) {
			$this->assertNotContains( '1=0', $queryInfo['conds'] );
			$this->assertContains( 'change_tag', $queryInfo['tables'] );
			$this->assertContains( 'change_tag_def', $queryInfo['tables'] );
			$this->assertSame( 'mw-private-test', $queryInfo['conds']['ctd_name'] );
			$this->assertArrayHasKey( 'change_tag', $queryInfo['join_conds'] );
			$this->assertArrayHasKey( 'change_tag_def', $queryInfo['join_conds'] );
		} else {
			$this->assertContains( '1=0', $queryInfo['conds'] );
			$this->assertNotContains( 'change_tag', $queryInfo['tables'] );
			$this->assertNotContains( 'change_tag_def', $queryInfo['tables'] );
			$this->assertArrayNotHasKey( 'ctd_name', $queryInfo['conds'] );
		}
	}

	public static function provideGetQueryInfoForTagFilter(): array {
		return [
			'User cannot see restricted tag' => [
				'authorityRights' => [],
				'userCanSeeTag' => false,
			],
			'User can see restricted tag' => [
				'authorityRights' => [ 'patrol' ],
				'userCanSeeTag' => true,
			],
		];
	}
}
