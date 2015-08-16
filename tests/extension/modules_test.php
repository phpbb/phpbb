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

require_once dirname(__FILE__) . '/ext/vendor2/foo/acp/a_info.php';
require_once dirname(__FILE__) . '/ext/vendor2/foo/mcp/a_info.php';
require_once dirname(__FILE__) . '/ext/vendor2/foo/acp/fail_info.php';
require_once dirname(__FILE__) . '/ext/vendor2/bar/acp/a_info.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/acp/acp_modules.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_module.php';

class phpbb_extension_modules_test extends phpbb_test_case
{
	protected $extension_manager;
	protected $finder;
	protected $module_manager;

	public function setUp()
	{
		global $phpbb_extension_manager;

		$this->extension_manager = new phpbb_mock_extension_manager(
			dirname(__FILE__) . '/',
			array(
				'vendor2/foo' => array(
					'ext_name' => 'vendor2/foo',
					'ext_active' => '1',
					'ext_path' => 'ext/vendor2/foo/',
				),
				'vendor3/bar' => array(
					'ext_name' => 'vendor3/bar',
					'ext_active' => '1',
					'ext_path' => 'ext/vendor3/bar/',
				),
			));
		$phpbb_extension_manager = $this->extension_manager;

		$this->module_manager = new \phpbb\module\module_manager(
			new \phpbb\cache\driver\dummy(),
			$this->getMock('\phpbb\db\driver\driver_interface'),
			$this->extension_manager,
			MODULES_TABLE,
			dirname(__FILE__) . '/',
			'php'
		);
	}

	public function test_get_module_infos()
	{
		global $phpbb_root_path;

//		$this->markTestIncomplete('Modules no speak namespace! Going to get rid of db modules altogether and fix this test after.');

		// Correctly set the root path for this test to this directory, so the classes can be found
		$phpbb_root_path = dirname(__FILE__) . '/';

		// Find acp module info files
		$acp_modules = $this->module_manager->get_module_infos('acp');
		$this->assertEquals(array(
				'vendor2\\foo\\acp\\a_module' => array(
					'filename'	=> 'vendor2\\foo\\acp\\a_module',
					'title'		=> 'Foobar',
					'modes'		=> array(
						'config'		=> array('title' => 'Config',	'auth' => 'ext_vendor2/foo', 'cat' => array('ACP_MODS')),
					),
				),
				'acp_foobar' => array(
					'filename'	=> 'acp_foobar',
					'title'		=> 'ACP Foobar',
					'modes'		=> array(
						'test'		=> array('title' => 'Test', 'auth' => '', 'cat' => array('ACP_GENERAL')),
					),
				),
			), $acp_modules);

		// Find mcp module info files
		$acp_modules = $this->module_manager->get_module_infos('mcp');
		$this->assertEquals(array(
				'vendor2\\foo\\mcp\\a_module' => array(
					'filename'	=> 'vendor2\\foo\\mcp\\a_module',
					'title'		=> 'Foobar',
					'modes'		=> array(
						'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('MCP_MAIN')),
					),
				),
			), $acp_modules);

		// Find a specific module info file (mcp_a_module)
		$acp_modules = $this->module_manager->get_module_infos('mcp', 'mcp_a_module');
		$this->assertEquals(array(
				'vendor2\\foo\\mcp\\a_module' => array(
					'filename'	=> 'vendor2\\foo\\mcp\\a_module',
					'title'		=> 'Foobar',
					'modes'		=> array(
						'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('MCP_MAIN')),
					),
				),
			), $acp_modules);

		// The mcp module info file we're looking for shouldn't exist
		$acp_modules = $this->module_manager->get_module_infos('mcp', 'mcp_a_fail');
		$this->assertEquals(array(), $acp_modules);

		// As there are no ucp modules we shouldn't find any
		$acp_modules = $this->module_manager->get_module_infos('ucp');
		$this->assertEquals(array(), $acp_modules);

		// Get module info of specified extension module
		$acp_modules = $this->module_manager->get_module_infos('acp', 'foo_acp_a_module');
		$this->assertEquals(array(
				'vendor2\\foo\\acp\\a_module' => array (
					'filename' => 'vendor2\\foo\\acp\\a_module',
					'title' => 'Foobar',
					'modes' => array (
						'config'		=> array ('title' => 'Config', 'auth' => 'ext_vendor2/foo', 'cat' => array ('ACP_MODS')),
					),
				),
			), $acp_modules);

		// No specific module and module class set to an incorrect name
		$acp_modules = $this->module_manager->get_module_infos('wcp', '', true);
		$this->assertEquals(array(), $acp_modules);

		// No specific module, no module_class set in the function parameter, and an incorrect module class
		$acp_modules = $this->module_manager->get_module_infos('wcp');
		$this->assertEquals(array(), $acp_modules);

		// No specific module, module class set to false (will default to the above acp)
		// Setting $use_all_available will cause get_module_infos() to also load not enabled extensions (vendor2/bar)
		$acp_modules = $this->module_manager->get_module_infos('acp', '', true);
		$this->assertEquals(array(
				'vendor2\\foo\\acp\\a_module' => array(
					'filename'	=> 'vendor2\\foo\\acp\\a_module',
					'title'		=> 'Foobar',
					'modes'		=> array(
						'config'		=> array('title' => 'Config',	'auth' => 'ext_vendor2/foo', 'cat' => array('ACP_MODS')),
					),
				),
				'acp_foobar' => array(
					'filename'	=> 'acp_foobar',
					'title'		=> 'ACP Foobar',
					'modes'		=> array(
						'test'		=> array('title' => 'Test', 'auth' => '', 'cat' => array('ACP_GENERAL')),
					),
				),
				'vendor2\\bar\\acp\\a_module' => array(
					'filename'	=> 'vendor2\\bar\\acp\\a_module',
					'title'		=> 'Bar',
					'modes'		=> array(
						'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('ACP_MODS')),
					),
				)
			), $acp_modules);

		// Specific module set to disabled extension
		$acp_modules = $this->module_manager->get_module_infos('acp', 'vendor2_bar_acp_a_module', true);
		$this->assertEquals(array(
				'vendor2\\bar\\acp\\a_module' => array(
					'filename'	=> 'vendor2\\bar\\acp\\a_module',
					'title'		=> 'Bar',
					'modes'		=> array(
						'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('ACP_MODS')),
					),
				)
			), $acp_modules);
	}

	public function module_auth_test_data()
	{
		return array(
			// module_auth, expected result
			array('ext_foo', false),
			array('ext_foo/bar', false),
			array('ext_vendor3/bar', false),
			array('ext_vendor2/foo', true),
		);
	}

	/**
	* @dataProvider module_auth_test_data
	*/
	public function test_modules_auth($module_auth, $expected)
	{
		global $phpbb_extension_manager, $phpbb_dispatcher;

		$phpbb_extension_manager = $this->extension_manager = new phpbb_mock_extension_manager(
			dirname(__FILE__) . '/',
			array(
				'vendor2/foo' => array(
					'ext_name' => 'vendor2/foo',
					'ext_active' => '1',
					'ext_path' => 'ext/vendor2/foo/',
				),
				'vendor3/bar' => array(
					'ext_name' => 'vendor3/bar',
					'ext_active' => '0',
					'ext_path' => 'ext/vendor3/bar/',
				),
			)
		);

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		$this->assertEquals($expected, p_master::module_auth($module_auth, 0));
	}
}
