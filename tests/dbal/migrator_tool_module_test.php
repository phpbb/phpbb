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

require_once __DIR__ . '/ext/foo/bar/acp/acp_test_info.php';
require_once __DIR__ . '/ext/foo/bar/ucp/ucp_test_info.php';

class phpbb_dbal_migrator_tool_module_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__.'/fixtures/migrator_module.xml');
	}

	protected function setUp(): void
	{
		// Need global $db, $user for delete_module function in acp_modules
		global $phpbb_root_path, $phpEx, $skip_add_log, $db, $user, $phpbb_log;

		parent::setUp();

		// Disable the logs
		$skip_add_log = true;

		$db = $this->db = $this->new_dbal();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$this->cache = new \phpbb\cache\service(new \phpbb\cache\driver\dummy(), new \phpbb\config\config(array()), $this->db, $phpbb_dispatcher, $phpbb_root_path, $phpEx);
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = $this->user = new \phpbb\user($lang, '\phpbb\datetime');

		$cache = new phpbb_mock_cache;
		$auth = $this->createMock('\phpbb\auth\auth');
		$phpbb_log = new \phpbb\log\log($db, $user, $auth, $phpbb_dispatcher, $phpbb_root_path, 'adm/', $phpEx, LOG_TABLE);

		// Correctly set the root path for this test to this directory, so the classes can be found
		$phpbb_root_path = __DIR__ . '/';

		$phpbb_extension_manager = new phpbb_mock_extension_manager($phpbb_root_path);
		$module_manager = new \phpbb\module\module_manager($cache, $this->db, $phpbb_extension_manager, MODULES_TABLE, $phpbb_root_path, $phpEx);

		$this->tool = new \phpbb\db\migration\tool\module($this->db, $this->user, $module_manager, 'phpbb_modules');
	}

	public function exists_data_acp()
	{
		return array(
			// Test the existing category
			array(
				'',
				'ACP_CAT',
				false,
				true,
			),
			array(
				0,
				'ACP_CAT',
				false,
				true,
			),
			array(
				false,
				'ACP_CAT',
				false,
				true,
			),

			// Test the existing category lazily
			array(
				'',
				'ACP_CAT',
				true,
				true,
			),
			array(
				0,
				'ACP_CAT',
				true,
				true,
			),
			array(
				false,
				'ACP_CAT',
				true,
				true,
			),

			// Test the existing module
			array(
				'',
				'ACP_MODULE',
				false,
				false,
			),
			array(
				false,
				'ACP_MODULE',
				false,
				true,
			),
			array(
				'ACP_CAT',
				'ACP_MODULE',
				false,
				true,
			),

			// Test the existing module lazily
			array(
				'',
				'ACP_MODULE',
				true,
				false,
			),
			array(
				false,
				'ACP_MODULE',
				true,
				true,
			),
			array(
				'ACP_CAT',
				'ACP_MODULE',
				true,
				true,
			),

			// Test for non-existant modules
			array(
				'',
				'ACP_NON_EXISTANT_CAT',
				false,
				false,
			),
			array(
				false,
				'ACP_NON_EXISTANT_CAT',
				false,
				false,
			),
			array(
				'ACP_CAT',
				'ACP_NON_EXISTANT_MODULE',
				false,
				false,
			),

			// Test for non-existant modules lazily
			array(
				'',
				'ACP_NON_EXISTANT_CAT',
				true,
				false,
			),
			array(
				false,
				'ACP_NON_EXISTANT_CAT',
				true,
				false,
			),
			array(
				'ACP_CAT',
				'ACP_NON_EXISTANT_MODULE',
				true,
				false,
			),
		);
	}

	/**
	* @dataProvider exists_data_acp
	*/
	public function test_exists_acp($parent, $module, $lazy, $expected)
	{
		$this->assertEquals($expected, $this->tool->exists('acp', $parent, $module, $lazy));
	}

	public function exists_data_ucp()
	{
		return array(
			// Test the existing category
			array(
				'',
				'UCP_MAIN_CAT',
				false,
				true,
			),
			array(
				0,
				'UCP_MAIN_CAT',
				false,
				true,
			),
			array(
				false,
				'UCP_MAIN_CAT',
				false,
				true,
			),

			// Test the existing category lazily
			array(
				'',
				'UCP_MAIN_CAT',
				true,
				true,
			),
			array(
				0,
				'UCP_MAIN_CAT',
				true,
				true,
			),
			array(
				false,
				'UCP_MAIN_CAT',
				true,
				true,
			),

			// Test the existing module
			array(
				'',
				'UCP_SUBCATEGORY',
				false,
				false,
			),
			array(
				false,
				'UCP_SUBCATEGORY',
				false,
				true,
			),
			array(
				'UCP_MAIN_CAT',
				'UCP_SUBCATEGORY',
				false,
				true,
			),
			array(
				'UCP_SUBCATEGORY',
				'UCP_MODULE',
				false,
				true,
			),

			// Test the existing module lazily
			array(
				'',
				'UCP_SUBCATEGORY',
				true,
				false,
			),
			array(
				false,
				'UCP_SUBCATEGORY',
				true,
				true,
			),
			array(
				'UCP_MAIN_CAT',
				'UCP_SUBCATEGORY',
				true,
				true,
			),
			array(
				'UCP_SUBCATEGORY',
				'UCP_MODULE',
				true,
				true,
			),

			// Test for non-existant modules
			array(
				'',
				'UCP_NON_EXISTANT_CAT',
				false,
				false,
			),
			array(
				'UCP_MAIN_CAT',
				'UCP_NON_EXISTANT_MODULE',
				false,
				false,
			),

			// Test for non-existant modules lazily
			array(
				'',
				'UCP_NON_EXISTANT_CAT',
				true,
				false,
			),
			array(
				'UCP_MAIN_CAT',
				'UCP_NON_EXISTANT_MODULE',
				true,
				false,
			),
		);
	}

	/**
	* @dataProvider exists_data_ucp
	*/
	public function test_exists_ucp($parent, $module, $lazy, $expected)
	{
		$this->assertEquals($expected, $this->tool->exists('ucp', $parent, $module, $lazy));
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

		// Test adding module when plural parent module_langname exists
		// PHPBB3-14703
		// Adding success
		try
		{
			$this->tool->add('acp', 'ACP_FORUM_BASED_PERMISSIONS', array(
				'module_basename'	=> 'acp_new_permissions_module',
				'module_langname'	=> 'ACP_NEW_PERMISSIONS_MODULE',
				'module_mode'		=> 'test',
				'module_auth'		=> '',
				'after'				=> 'ACP_FORUM_BASED_PERMISSIONS_CHILD_1',
			));
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertEquals(true, $this->tool->exists('acp', 'ACP_FORUM_BASED_PERMISSIONS', 'ACP_NEW_PERMISSIONS_MODULE'));

		// Test adding UCP modules
		// Test adding new UCP category
		try
		{
			$this->tool->add('ucp', 0, 'UCP_NEW_CAT');
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertEquals(true, $this->tool->exists('ucp', 0, 'UCP_NEW_CAT'));

		// Test adding new UCP subcategory
		try
		{
			$this->tool->add('ucp', 'UCP_NEW_CAT', 'UCP_NEW_SUBCAT');
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertEquals(true, $this->tool->exists('ucp', 'UCP_NEW_CAT', 'UCP_NEW_SUBCAT'));

		// Test adding new UCP module
		try
		{
			$this->tool->add('ucp', 'UCP_NEW_SUBCAT', array(
				'module_basename'	=> 'ucp_new_module',
				'module_langname'	=> 'UCP_NEW_MODULE',
				'module_mode'		=> 'ucp_test',
				'module_auth'		=> '',
			));
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertEquals(true, $this->tool->exists('ucp', 'UCP_NEW_SUBCAT', 'UCP_NEW_MODULE'));

		// Test adding new UCP module the automatic way, single mode
		try
		{
			$this->tool->add('ucp', 'UCP_NEW_CAT', array(
				'module_basename'	=> '\foo\bar\ucp\ucp_test_module',
				'modes'				=> array('mode_1'),
			));
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertEquals(true, $this->tool->exists('ucp', 'UCP_NEW_CAT', 'UCP_NEW_MODULE_MODE_1'));
		$this->assertEquals(false, $this->tool->exists('ucp', 'UCP_NEW_CAT', 'UCP_NEW_MODULE_MODE_2'));

		// Test adding new ACP module the automatic way, all modes
		try
		{
			$this->tool->add('acp', 'ACP_NEW_CAT', array(
				'module_basename' => '\foo\bar\acp\acp_test_module',
			));
		}
		catch (Exception $e)
		{
			$this->fail($e);
		}
		$this->assertEquals(true, $this->tool->exists('acp', 'ACP_NEW_CAT', 'ACP_NEW_MODULE_MODE_1'));
		$this->assertEquals(true, $this->tool->exists('acp', 'ACP_NEW_CAT', 'ACP_NEW_MODULE_MODE_2'));
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
