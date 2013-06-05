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
	static private $copied_files = array();
	static private $helper;

	/**
	* This should only be called once before the tests are run.
	* This is used to copy the fixtures to the phpBB install
	*/
	static public function setUpBeforeClass()
	{
		global $phpbb_root_path;
		parent::setUpBeforeClass();

		self::$helper = new phpbb_test_case_helpers(self);

		self::$copied_files = array();

		if (file_exists($phpbb_root_path . 'ext/'))
		{
			// First, move any extensions setup on the board to a temp directory
			self::$copied_files = self::$helper->copy_dir($phpbb_root_path . 'ext/', $phpbb_root_path . 'store/temp_ext/');

			// Then empty the ext/ directory on the board (for accurate test cases)
			self::$helper->empty_dir($phpbb_root_path . 'ext/');
		}

		// Copy our ext/ files from the test case to the board
		self::$copied_files = array_merge(self::$copied_files, self::$helper->copy_dir(dirname(__FILE__) . '/fixtures/ext/', $phpbb_root_path . 'ext/'));
	}

	/**
	* This should only be called once after the tests are run.
	* This is used to remove the fixtures from the phpBB install
	*/
	static public function tearDownAfterClass()
	{
		global $phpbb_root_path;

		if (file_exists($phpbb_root_path . 'store/temp_ext/'))
		{
			// Copy back the board installed extensions from the temp directory
			self::$helper->copy_dir($phpbb_root_path . 'store/temp_ext/', $phpbb_root_path . 'ext/');
		}

		// Remove all of the files we copied around (from board ext -> temp_ext, from test ext -> board ext)
		self::$helper->remove_files(self::$copied_files);

		if (file_exists($phpbb_root_path . 'store/temp_ext/'))
		{
			self::$helper->empty_dir($phpbb_root_path . 'store/temp_ext/');
		}
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
		$crawler = $this->request('GET', 'adm/index.php?i=phpbb_ext_foo_bar_acp_main_module&mode=mode&sid=' . $this->sid, array(), true);
		$this->assert_response_success();
		$this->assertContains("Bertie rulez!", $crawler->filter('#main')->text());
		$this->phpbb_extension_manager->purge('foo/bar');
	}
}
