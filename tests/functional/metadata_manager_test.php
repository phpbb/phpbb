<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @group functional
*/
class phpbb_functional_metadata_manager_test extends phpbb_functional_test_case
{
	protected $phpbb_extension_manager;

	static protected $fixtures = array(
		'foo/bar/composer.json',
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

		rmdir("{$phpbb_root_path}ext/foo/bar");
		rmdir("{$phpbb_root_path}ext/foo");
	}

	public function setUp()
	{
		parent::setUp();

		$this->phpbb_extension_manager = $this->get_extension_manager();

		$this->purge_cache();
		$this->phpbb_extension_manager->enable('foo/bar');

		$this->login();
		$this->admin_login();
		$this->add_lang('acp/extensions');
	}

	public function test_extensions_list()
	{
		$crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&sid=' . $this->sid);
		$this->assert_response_success();

		$this->assertContains($this->lang('EXTENSIONS_EXPLAIN'), $this->client->getResponse()->getContent());
		$this->assertContains('phpBB 3.1 Extension Testing', $this->client->getResponse()->getContent());
		$this->assertContains('Details', $this->client->getResponse()->getContent());
	}

	public function test_permissions_tab()
	{
		$crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=details&ext_name=foo%2Fbar&sid=' . $this->sid);
		$this->assert_response_success();

		// Test whether the details are displayed
		$this->assertContains($this->lang('CLEAN_NAME'), $this->client->getResponse()->getContent());
		$this->assertContains('foo/bar', $this->client->getResponse()->getContent());

		// Details should be html escaped
		$this->assertContains($this->lang('PHP_VERSION'), $this->client->getResponse()->getContent());
		$this->assertContains('&gt;=5.3', $this->client->getResponse()->getContent());
	}
}
