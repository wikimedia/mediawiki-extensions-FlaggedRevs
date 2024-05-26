<?php

namespace MediaWiki\Extension\FlaggedRevs\Test;

use MediaWiki\Config\ConfigException;
use MediaWiki\Tests\Api\ApiTestCase;

/**
 * Tests for ApiFlagConfig class
 *
 * @covers \ApiFlagConfig
 * @covers \FlaggedRevs::useOnlyIfProtected
 * @covers \FlaggedRevs::getTagName
 * @covers \FlaggedRevs::getMaxLevel
 */
class ApiFlagConfigTest extends ApiTestCase {
	public function testFlagConfigFlaggedRevsProtection() {
		$this->overrideConfigValue( 'FlaggedRevsProtection', true );
		$params = [ 'action' => 'flagconfig' ];
		[ $result ] = $this->doApiRequest( $params );
		$expected = [ 'flagconfig' => [] ];
		$this->assertEquals( $expected, $result );
	}

	public function testFlagConfigNoFlaggedRevsProtectionDefault() {
		$this->overrideConfigValue( 'FlaggedRevsProtection', false );
		$params = [ 'action' => 'flagconfig' ];
		[ $result ] = $this->doApiRequest( $params );
		$expected = [
			'flagconfig' => [
				[
					'name' => 'accuracy',
					'levels' => 3,
					'tier1' => 1,
				]
			],
		];
		$this->assertEquals( $expected, $result );
	}

	public function testFlagConfigNoFlaggedRevsProtectionCustom() {
		$this->overrideConfigValue( 'FlaggedRevsProtection', false );
		$this->overrideConfigValue( 'FlaggedRevsTags', [
			'accuracy' => [
				'levels' => 2
			]
		] );
		$params = [ 'action' => 'flagconfig' ];
		[ $result ] = $this->doApiRequest( $params );
		$expected = [
			'flagconfig' => [
				[
					'name' => 'accuracy',
					'levels' => 2,
					'tier1' => 1,
				]
			],
		];
		$this->assertEquals( $expected, $result );
	}

	public function testFlagConfigNoFlaggedRevsProtectionError() {
		$this->overrideConfigValue( 'FlaggedRevsProtection', false );
		$this->overrideConfigValue( 'FlaggedRevsTags', [
			'accuracy' => [
				'levels' => 3
			],
			'tone' => [
				'levels' => 3
			]
		] );
		$params = [ 'action' => 'flagconfig' ];
		$this->expectException( ConfigException::class );
		$this->doApiRequest( $params );
	}
}
