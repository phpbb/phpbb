<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/ext/foo/acp/a_info.php';
require_once dirname(__FILE__) . '/ext/foo/mcp/a_info.php';
require_once dirname(__FILE__) . '/ext/foo/acp/fail_info.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/acp/acp_modules.php';

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
				'foo' => array(
					'ext_name' => 'foo',
					'ext_active' => '1',
					'ext_path' => 'ext/foo/',
				),
				'bar' => array(
					'ext_name' => 'bar',
					'ext_active' => '1',
					'ext_path' => 'ext/bar/',
				),
			));
		$phpbb_extension_manager = $this->extension_manager;

		$this->acp_modules = new acp_modules();
	}

	public function test_get_module_infos()
	{
		global $phpbb_root_path;

		// Correctly set the root path for this test to this directory, so the classes can be found
		$phpbb_root_path = dirname(__FILE__) . '/';

		$this->acp_modules->module_class = 'acp';
		$acp_modules = $this->acp_modules->get_module_infos();
		$this->assertEquals(array(
				'phpbb_ext_foo_acp_a_module' => array(
					'filename'	=> 'phpbb_ext_foo_acp_a_module',
					'title'		=> 'Foobar',
					'version'	=> '3.1.0-dev',
					'modes'		=> array(
						'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('ACP_MODS')),
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

		$this->acp_modules->module_class = 'mcp';
		$acp_modules = $this->acp_modules->get_module_infos();
		$this->assertEquals(array(
				'phpbb_ext_foo_mcp_a_module' => array(
					'filename'	=> 'phpbb_ext_foo_mcp_a_module',
					'title'		=> 'Foobar',
					'version'	=> '3.1.0-dev',
					'modes'		=> array(
						'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('MCP_MAIN')),
					),
				),
			), $acp_modules);

		$this->acp_modules->module_class = 'mcp';
		$acp_modules = $this->acp_modules->get_module_infos('mcp_a_module');
		$this->assertEquals(array(
				'phpbb_ext_foo_mcp_a_module' => array(
					'filename'	=> 'phpbb_ext_foo_mcp_a_module',
					'title'		=> 'Foobar',
					'version'	=> '3.1.0-dev',
					'modes'		=> array(
						'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('MCP_MAIN')),
					),
				),
			), $acp_modules);

		$this->acp_modules->module_class = 'mcp';
		$acp_modules = $this->acp_modules->get_module_infos('mcp_a_fail');
		$this->assertEquals(array(), $acp_modules);

		$this->acp_modules->module_class = 'ucp';
		$acp_modules = $this->acp_modules->get_module_infos();
		$this->assertEquals(array(), $acp_modules);
	}
}
