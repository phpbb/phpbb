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

class get_callable_from_step_test extends phpbb_database_test_case
{
	protected function setUp(): void
	{
		global $phpbb_root_path, $php_ext, $table_prefix, $phpbb_log, $user;

		parent::setUp();

		$phpbb_log = $this->getMockBuilder('\phpbb\log\log')->disableOriginalConstructor()->getMock();
		$db = $this->new_dbal();
		$db_doctrine = $this->new_doctrine_dbal();
		$factory = new \phpbb\db\tools\factory();
		$user = $this->getMockBuilder('\phpbb\user')->disableOriginalConstructor()->getMock();
		$user->ip = '127.0.0.1';
		$module_manager = new \phpbb\module\module_manager(
			$this->getMockBuilder('\phpbb\cache\driver\dummy')->disableOriginalConstructor()->getMock(),
			$db,
			new phpbb_mock_extension_manager($phpbb_root_path),
			'phpbb_modules',
			$phpbb_root_path,
			$php_ext
		);
		$module_tools = new \phpbb\db\migration\tool\module($db, $user, $module_manager, 'phpbb_modules');
		$this->migrator = new \phpbb\db\migrator(
			new phpbb_mock_container_builder(),
			new \phpbb\config\config(array()),
			$db,
			$factory->get($db_doctrine),
			'phpbb_migrations',
			$phpbb_root_path,
			$php_ext,
			$table_prefix,
			self::get_core_tables(),
			array($module_tools),
			new \phpbb\db\migration\helper()
		);

		if (!$module_tools->exists('acp', 0, 'new_module_langname'))
		{
			$module_tools->add('acp', 0, array(
				'module_basename'	=> 'new_module_basename',
				'module_langname'	=> 'new_module_langname',
				'module_mode'		=> 'settings',
				'module_auth'		=> '',
				'module_display'	=> true,
				'before'			=> false,
				'after'				=> false,
			));
			$this->module_added = true;
		}
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/../dbal/fixtures/migrator.xml');
	}

	public function get_callable_from_step_provider()
	{
		return array(
			array(
				array('if', array(
					false,
					array('permission.add', array('some_data')),
				)),
				true, // expects false
			),
			array(
				array('if', array(
					array('module.exists', array(
						'mcp',
						'RANDOM_PARENT',
						'RANDOM_MODULE'
					)),
					array('permission.add', array('some_data')),
				)),
				true, // expects false
			),
			array(
				array('if', array(
					array('module.exists', array(
						'acp',
						0,
						'new_module_langname'
					)),
					array('module.add', array(
						'acp',
						0,
						'module_basename'	=> 'new_module_basename2',
						'module_langname'	=> 'new_module_langname2',
						'module_mode'		=> 'settings',
						'module_auth'		=> '',
						'module_display'	=> true,
						'before'			=> false,
						'after'				=> false,
					)),
				)),
				false, // expects false
			),
		);
	}

	/**
	 * @dataProvider get_callable_from_step_provider
	 */
	public function test_get_callable_from_step($step, $expects_false)
	{
		if ($expects_false)
		{
			$this->assertFalse($this->call_get_callable_from_step($step));
		}
		else
		{
			$this->assertNotFalse($this->call_get_callable_from_step($step));
		}
	}

	protected function call_get_callable_from_step($step)
	{
		$class = new ReflectionClass($this->migrator);
		$method = $class->getMethod('get_callable_from_step');
		$method->setAccessible(true);
		return $method->invokeArgs($this->migrator, array($step));
	}
}
