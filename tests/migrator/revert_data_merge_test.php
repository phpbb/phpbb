<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\db\migration\helper;
use phpbb\db\migration\migration;
use phpbb\db\migrator;
use phpbb\db\tools\tools_interface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerInterface;

class phpbb_migrator_revert_data_merge_test extends phpbb_test_case
{
	/**
	 * Data provider for test_revert_steps_merge.
	 *
	 * Each row:
	 *	0 - steps returned by update_data()
	 *	1 - steps returned by revert_data()
	 *	2 - expected merged revert step list
	 */
	public static function revert_steps_provider(): array
	{
		return [
			'update_and_revert' => [
				[
					['module.add', [[
						'module_basename'	=> 'foo_module',
						'module_class'		=> 'acp',
					]]],
					['config.add', ['foo_config', 0]],
					['permission.add', ['a_foo']],
				],
				[
					['permission.remove', ['a_foo']],
				],
				[
					['permission.reverse', ['add', 'a_foo']],
					['config.reverse', ['add', 'foo_config', 0]],
					['module.reverse', ['add', [
						'module_basename'	=> 'foo_module',
						'module_class'		=> 'acp',
					]]],
					['permission.remove', ['a_foo']],
				],
			],
			'empty_revert_data' => [
				[
					['config.add', ['bar_config', 1]],
				],
				[],
				[
					['config.reverse', ['add', 'bar_config', 1]],
				],
			],
			'empty_update_data' => [
				[],
				[
					['permission.remove', ['a_bar']],
				],
				[
					['permission.remove', ['a_bar']],
				],
			],
		];
	}

	/**
	 * @dataProvider revert_steps_provider
	 */
	public function test_revert_steps_merge(array $update_data, array $revert_data, array $expected): void
	{
		$db = $this->createMock(driver_interface::class);
		$db_tools = $this->createMock(tools_interface::class);
		$container = $this->createMock(ContainerInterface::class);

		/** @var migrator|MockObject $migrator */
		$migrator = $this->getMockBuilder(migrator::class)
			->setConstructorArgs([
				$container,
				new config([]),
				$db,
				$db_tools,
				'phpbb_migrations',
				'',
				'php',
				'phpbb_',
				[],
				new helper(),
			])
			->setMethods(['load_migration_state', 'set_migration_state', 'process_data_step', 'get_migration'])
			->getMock();

		$migrator->method('load_migration_state')->willReturn(null);
		$migrator->method('set_migration_state')->willReturn(null);

		// Mock migration
		$migration = $this->getMockBuilder(migration::class)
			->disableOriginalConstructor()
			->getMock();
		$migration->method('update_data')->willReturn($update_data);
		$migration->method('revert_data')->willReturn($revert_data);
		$migrator->method('get_migration')->willReturn($migration);

		// Intercept process_data_step() to capture the assembled step list
		$captured_steps = null;
		$migrator->method('process_data_step')
			->willReturnCallback(function ($steps, $state, $revert = false) use (&$captured_steps) {
				$captured_steps = $steps;
				return true;
			});

		// Seed migration_state via reflection so try_revert() sees the migration as
		// fully applied (data phase done). stdClass is used as the migration name
		// because any existing class name satisfies the class_exists() guard inside
		// try_revert(), and get_migration() is already stubbed above.
		$migration_name = stdClass::class;

		$ref = new ReflectionClass($migrator);
		$state_prop = $ref->getProperty('migration_state');
		$state_prop->setAccessible(true);
		$state_prop->setValue($migrator, [
			$migration_name => [
				'migration_depends_on'	=> [],
				'migration_schema_done'	=> true,
				'migration_data_done'	=> true,
				'migration_data_state'	=> '',
				'migration_start_time'	=> 0,
				'migration_end_time'	=> 0,
			],
		]);

		// Call the protected try_revert() directly via reflection.
		$try_revert = $ref->getMethod('try_revert');
		$try_revert->setAccessible(true);
		$try_revert->invoke($migrator, $migration_name);

		$this->assertEquals(
			$expected,
			$captured_steps,
			'try_revert() must pass both reversed update_data() and explicit revert_data() steps ' .
			'to process_data_step() – neither half may be suppressed or duplicated.'
		);
	}
}
