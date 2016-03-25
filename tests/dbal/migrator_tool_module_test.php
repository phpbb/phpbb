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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_dbal_migrator_tool_module_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/migrator_module.xml');
	}

	public function setup()
	{
		// Need global $db, $user for delete_module function in acp_modules
		global $phpbb_root_path, $phpEx, $skip_add_log, $db, $user, $phpbb_log;

		parent::setup();

		// Disable the logs
		$skip_add_log = true;

		$db = $this->db = $this->new_dbal();
		$this->cache = new \phpbb\cache\service(new \phpbb\cache\driver\dummy(), new \phpbb\config\config(array()), $this->db, $phpbb_root_path, $phpEx);
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = $this->user = new \phpbb\user($lang, '\phpbb\datetime');

		$cache = new phpbb_mock_cache;
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$auth = $this->getMock('\phpbb\auth\auth');
		$phpbb_log = new \phpbb\log\log($db, $user, $auth, $phpbb_dispatcher, $phpbb_root_path, 'adm/', $phpEx, LOG_TABLE);

		$phpbb_extension_manager = new phpbb_mock_extension_manager($phpbb_root_path);
		$module_manager = new \phpbb\module\module_manager($cache, $this->db, $phpbb_extension_manager, MODULES_TABLE, $phpbb_root_path, $phpEx);

		$this->tool = new \phpbb\db\migration\tool\module($this->db, $this->cache, $this->user, $module_manager, $phpbb_root_path, $phpEx, 'phpbb_modules');
	}

	public function exists_data()
	{
		return array(
			// Test the category
			array(
				'',
				'ACP_CAT',
				true,
			),
			array(
				0,
				'ACP_CAT',
				true,
			),

			// Test the module
			array(
				'',
				'ACP_MODULE',
				false,
			),
			array(
				false,
				'ACP_MODULE',
				true,
			),
			array(
				'ACP_CAT',
				'ACP_MODULE',
				true,
			),
		);
	}

	/**
	* @dataProvider exists_data
	*/
	public function test_exists($parent, $module, $expected)
	{
		$this->assertEquals($expected, $this->tool->exists('acp', $parent, $module));
	}

	public function test_add()
	{
		try
		{
			$this->tool->add('acp', 0, 'ACP_NEW_CAT');
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertEquals(true, $this->tool->exists('acp', 0, 'ACP_NEW_CAT'));

		// Should throw an exception when trying to add a module that already exists
		try
		{
			$this->tool->add('acp', 0, 'ACP_NEW_CAT');
			$this->fail('Exception not thrown');
		}
		catch (Exception $e) {}

		try
		{
			$this->tool->add('acp', 'ACP_NEW_CAT', array(
				'module_basename'	=> 'acp_new_module',
				'module_langname'	=> 'ACP_NEW_MODULE',
				'module_mode'		=> 'test',
				'module_auth'		=> '',
			));
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertEquals(true, $this->tool->exists('acp', 'ACP_NEW_CAT', 'ACP_NEW_MODULE'));
	}

	public function test_remove()
	{
		try
		{
			$this->tool->remove('acp', 'ACP_CAT', 'ACP_MODULE');
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertEquals(false, $this->tool->exists('acp', 'ACP_CAT', 'ACP_MODULE'));
	}

	public function test_reverse()
	{
		try
		{
			$this->tool->add('acp', 0, 'ACP_NEW_CAT');
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}

		try
		{
			$this->tool->reverse('add', 'acp', 0, 'ACP_NEW_CAT');
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertFalse($this->tool->exists('acp', 0, 'ACP_NEW_CAT'));
	}
}
