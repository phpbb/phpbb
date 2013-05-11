<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @group functional
*/
class phpbb_functional_extension_permission_lang_test extends phpbb_functional_test_case
{
	protected $phpbb_extension_manager;

	static private $helper;

	static private $copied_files = array();

	static protected $fixtures = array(
		'foo/bar/language/en/',
	);

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
		self::$copied_files = array_merge(self::$copied_files, self::$helper->copy_dir(dirname(__FILE__) . '/fixtures/ext/' . $fixture, $phpbb_root_path . 'ext/' . $fixture));
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
		parent::setUp();
		
		$this->get_db();
		
		$acl_ary = array(
			'auth_option'	=> 'u_foo',
			'is_global'		=> 1,
		);

		$sql = 'INSERT INTO phpbb_acl_options ' . $this->db->sql_build_array('INSERT', $acl_ary);
		$this->db->sql_query($sql);

		$this->phpbb_extension_manager = $this->get_extension_manager();

		$this->purge_cache();

		$this->login();
		$this->admin_login();
		$this->add_lang('acp/permissions');
	}

	public function test_auto_include_permission_lang_from_extensions()
	{
		$this->phpbb_extension_manager->enable('foo/bar');

		// User permissions
		$crawler = $this->request('GET', 'adm/index.php?i=acp_permissions&icat=16&mode=setting_user_global&sid=' . $this->sid);
		$this->assert_response_success();

		// Select admin
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$data = array('username[0]' => 'admin');
		$form->setValues($data);
		$crawler = $this->client->submit($form);
		$this->assert_response_success();

		// language from language/en/acp/permissions_phpbb.php
		$this->assertContains('Can attach files', $crawler->filter('body')->text());

		// language from ext/foo/bar/language/en/permissions_foo.php
		$this->assertContains('Can view foo', $crawler->filter('body')->text());
	}
}
