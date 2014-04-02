<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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

		$this->acp_modules = new acp_modules();
	}

	public function test_get_module_infos()
	{
		global $phpbb_root_path;

//		$this->markTestIncomplete('Modules no speak namespace! Going to get rid of db modules altogether and fix this test after.');

		// Correctly set the root path for this test to this directory, so the classes can be found
		$phpbb_root_path = dirname(__FILE__) . '/';

		// Find acp module info files
		$this->acp_modules->module_class = 'acp';
		$acp_modules = $this->acp_modules->get_module_infos();
		$this->assertEquals(array(
				'vendor2\\foo\\acp\\a_module' => array(
					'filename'	=> 'vendor2\\foo\\acp\\a_module',
					'title'		=> 'Foobar',
					'version'	=> '3.1.0-dev',
					'modes'		=> array(
						'config'		=> array('title' => 'Config',	'auth' => 'ext_vendor2/foo', 'cat' => array('ACP_MODS')),
					),
				),
				'acp_foobar' => array(
					'filename'	=> 'acp_foobar',
					'title'		=> 'ACP Foobar',
					'version'	=> '3.1.0-dev',
					'modes'		=> array(
						'test'		=> array('title' => 'Test', 'auth' => '', 'cat' => array('ACP_GENERAL')),
					),
				),
			), $acp_modules);

		// Find mcp module info files
		$this->acp_modules->module_class = 'mcp';
		$acp_modules = $this->acp_modules->get_module_infos();
		$this->assertEquals(array(
				'vendor2\\foo\\mcp\\a_module' => array(
					'filename'	=> 'vendor2\\foo\\mcp\\a_module',
					'title'		=> 'Foobar',
					'version'	=> '3.1.0-dev',
					'modes'		=> array(
						'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('MCP_MAIN')),
					),
				),
			), $acp_modules);

		// Find a specific module info file (mcp_a_module)
		$this->acp_modules->module_class = 'mcp';
		$acp_modules = $this->acp_modules->get_module_infos('mcp_a_module');
		$this->assertEquals(array(
				'vendor2\\foo\\mcp\\a_module' => array(
					'filename'	=> 'vendor2\\foo\\mcp\\a_module',
					'title'		=> 'Foobar',
					'version'	=> '3.1.0-dev',
					'modes'		=> array(
						'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('MCP_MAIN')),
					),
				),
			), $acp_modules);

		// Find a specific module info file (mcp_a_module) with passing the module_class
		$this->acp_modules->module_class = '';
		$acp_modules = $this->acp_modules->get_module_infos('mcp_a_module', 'mcp');
		$this->assertEquals(array(
				'vendor2\\foo\\mcp\\a_module' => array(
					'filename'	=> 'vendor2\\foo\\mcp\\a_module',
					'title'		=> 'Foobar',
					'version'	=> '3.1.0-dev',
					'modes'		=> array(
						'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('MCP_MAIN')),
					),
				),
			), $acp_modules);

		// The mcp module info file we're looking for shouldn't exist
		$this->acp_modules->module_class = 'mcp';
		$acp_modules = $this->acp_modules->get_module_infos('mcp_a_fail');
		$this->assertEquals(array(), $acp_modules);

		// As there are no ucp modules we shouldn't find any
		$this->acp_modules->module_class = 'ucp';
		$acp_modules = $this->acp_modules->get_module_infos();
		$this->assertEquals(array(), $acp_modules);

		// Get module info of specified extension module
		$this->acp_modules->module_class = 'acp';
		$acp_modules = $this->acp_modules->get_module_infos('foo_acp_a_module');
		$this->assertEquals(array(
				'vendor2\\foo\\acp\\a_module' => array (
					'filename' => 'vendor2\\foo\\acp\\a_module',
					'title' => 'Foobar',
					'version' => '3.1.0-dev',
					'modes' => array (
						'config'		=> array ('title' => 'Config', 'auth' => 'ext_vendor2/foo', 'cat' => array ('ACP_MODS')),
					),
				),
			), $acp_modules);

		// No specific module and module class set to an incorrect name
		$acp_modules = $this->acp_modules->get_module_infos('', 'wcp', true);
		$this->assertEquals(array(), $acp_modules);

		// No specific module, no module_class set in the function parameter, and an incorrect module class
		$this->acp_modules->module_class = 'wcp';
		$acp_modules = $this->acp_modules->get_module_infos();
		$this->assertEquals(array(), $acp_modules);

		// No specific module, module class set to false (will default to the above acp)
		// Setting $use_all_available will cause get_module_infos() to also load not enabled extensions (vendor2/bar)
		$this->acp_modules->module_class = 'acp';
		$acp_modules = $this->acp_modules->get_module_infos('', false, true);
		$this->assertEquals(array(
				'vendor2\\foo\\acp\\a_module' => array(
					'filename'	=> 'vendor2\\foo\\acp\\a_module',
					'title'		=> 'Foobar',
					'version'	=> '3.1.0-dev',
					'modes'		=> array(
						'config'		=> array('title' => 'Config',	'auth' => 'ext_vendor2/foo', 'cat' => array('ACP_MODS')),
					),
				),
				'acp_foobar' => array(
					'filename'	=> 'acp_foobar',
					'title'		=> 'ACP Foobar',
					'version'	=> '3.1.0-dev',
					'modes'		=> array(
						'test'		=> array('title' => 'Test', 'auth' => '', 'cat' => array('ACP_GENERAL')),
					),
				),
				'vendor2\\bar\\acp\\a_module' => array(
					'filename'	=> 'vendor2\\bar\\acp\\a_module',
					'title'		=> 'Bar',
					'version'	=> '3.1.0-dev',
					'modes'		=> array(
						'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('ACP_MODS')),
					),
				)
			), $acp_modules);

		// Specific module set to disabled extension
		$acp_modules = $this->acp_modules->get_module_infos('vendor2_bar_acp_a_module', 'acp', true);
		$this->assertEquals(array(
				'vendor2\\bar\\acp\\a_module' => array(
					'filename'	=> 'vendor2\\bar\\acp\\a_module',
					'title'		=> 'Bar',
					'version'	=> '3.1.0-dev',
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
