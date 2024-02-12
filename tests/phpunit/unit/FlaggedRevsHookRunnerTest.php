<?php

use MediaWiki\Tests\HookContainer\HookRunnerTestBase;

/**
 * @covers \FlaggedRevsHookRunner
 */
class FlaggedRevsHookRunnerTest extends HookRunnerTestBase {

	public static function provideHookRunners() {
		yield FlaggedRevsHookRunner::class => [ FlaggedRevsHookRunner::class ];
	}
}
