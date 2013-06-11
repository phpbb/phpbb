<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/db/db_tools.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/acp/acp_modules.php';

/**
* @group functional
*/
class phpbb_functional_extension_module_test extends phpbb_functional_test_case
{
	protected $phpbb_extension_manager;

	static private $helper;

	static protected $fixtures = array(
		'./',
	);

	static public function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		self::$helper = new phpbb_test_case_helpers(self);
		self::$helper->copy_ext_fixtures(dirname(__FILE__) . '/fixtures/ext/', self::$fixtures);
	}

	static public function tearDownAfterClass()
	{
		parent::tearDownAfterClass();

		self::$helper->restore_original_ext_dir();
	}

	public function setUp()
	{
		global $db;

		parent::setUp();

		$this->phpbb_extension_manager = $this->get_extension_manager();
		$this->phpbb_extension_manager->enable('foo/bar');

		$modules = new acp_modules();
		$db = $this->get_db();

		$sql = 'SELECT module_id
			FROM ' . MODULES_TABLE . "
			WHERE module_langname = 'acp'
				AND module_class = 'ACP_CAT_DOT_MODS'";
		$result = $db->sql_query($sql);
		$module_id = (int) $db->sql_fetchfield('module_id');
		$db->sql_freeresult($result);

		$parent_data = array(
			'module_basename'	=> '',
			'module_enabled'	=> 1,
			'module_display'	=> 1,
			'parent_id'			=> $module_id,
			'module_class'		=> 'acp',
			'module_langname'	=> 'ACP_FOOBAR_TITLE',
			'module_mode'		=> '',
			'module_auth'		=> '',
		);
		$modules->update_module_data($parent_data, true);

		$module_data = array(
			'module_basename'	=> 'phpbb_ext_foo_bar_acp_main_module',
			'module_enabled'	=> 1,
			'module_display'	=> 1,
			'parent_id'			=> $parent_data['module_id'],
			'module_class'		=> 'acp',
			'module_langname'	=> 'ACP_FOOBAR_TITLE',
			'module_mode'		=> 'mode',
			'module_auth'		=> '',
		);
		$modules->update_module_data($module_data, true);

		$this->purge_cache();
	}

	/**
	* Check a controller for extension foo/bar.
	*/
	public function test_foo_bar()
	{
		$this->login();
		$this->admin_login();
		$crawler = self::request('GET', 'adm/index.php?i=phpbb_ext_foo_bar_acp_main_module&mode=mode&sid=' . $this->sid);
		$this->assertContains("Bertie rulez!", $crawler->filter('#main')->text());
		$this->phpbb_extension_manager->purge('foo/bar');
	}
}
