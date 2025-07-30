<?php

declare( strict_types=1 );

namespace MediaWiki\Extensions\FlaggedRevs\Tests\Structure;

use MediaWiki\Tests\Structure\AbstractSchemaTestBase;

/**
 * @coversNothing
 */
class FlaggedRevsSchemaTest extends AbstractSchemaTestBase {

	protected static function getSchemasDirectory(): string {
		return __DIR__ . '/../../../includes/backend/schema';
	}

	protected static function getSchemaChangesDirectory(): string {
		return __DIR__ . '/../../../includes/backend/schema/abstractSchemaChanges/';
	}

	protected static function getSchemaSQLDirs(): array {
		return [
			'mysql' => __DIR__ . '/../../../includes/backend/schema/mysql',
			'sqlite' => __DIR__ . '/../../../includes/backend/schema/sqlite',
			'postgres' => __DIR__ . '/../../../includes/backend/schema/postgres',
		];
	}
}
