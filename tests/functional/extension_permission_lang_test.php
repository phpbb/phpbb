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

	static protected $fixtures = array(
		'foo/bar/language/en/permissions_foo.php',
	);

	/**
	* This should only be called once before the tests are run.
	* This is used to copy the fixtures to the phpBB install
	*/
	static public function setUpBeforeClass()
	{
		global $phpbb_root_path;
		parent::setUpBeforeClass();

		$directories = array(
			$phpbb_root_path . 'ext/foo/bar/',
			$phpbb_root_path . 'ext/foo/bar/language/',
			$phpbb_root_path . 'ext/foo/bar/language/en/',
		);

		foreach ($directories as $dir)
		{
			if (!is_dir($dir))
			{
				mkdir($dir, 0777, true);
			}
		}

		foreach (self::$fixtures as $fixture)
		{
			copy(
				"tests/functional/fixtures/ext/$fixture",
				"{$phpbb_root_path}ext/$fixture");
		}
	}

	/**
	* This should only be called once after the tests are run.
	* This is used to remove the fixtures from the phpBB install
	*/
	static public function tearDownAfterClass()
	{
		global $phpbb_root_path;

		foreach (self::$fixtures as $fixture)
		{
			unlink("{$phpbb_root_path}ext/$fixture");
		}

		rmdir("{$phpbb_root_path}ext/foo/bar/language/en");
		rmdir("{$phpbb_root_path}ext/foo/bar/language");
		rmdir("{$phpbb_root_path}ext/foo/bar");
		rmdir("{$phpbb_root_path}ext/foo");
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
		$this->assertContains('Can view foo', $crawler->filter('body')->text());
	}
}
