<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/db/db_tools.php';

/**
* @group functional
*/
class phpbb_functional_metadata_manager_test extends phpbb_functional_test_case
{
	protected $phpbb_extension_manager;

	static private $helpers;

	static protected $fixtures = array(
		'foo/bar/',
	);

	/**
	* This should only be called once before the tests are run.
	* This is used to copy the fixtures to the phpBB install
	*/
	static public function setUpBeforeClass()
	{
		global $phpbb_root_path;
		parent::setUpBeforeClass();

		self::$helpers = new phpbb_test_case_helpers(self);

		if (!file_exists($phpbb_root_path . 'ext/foo/bar/'))
		{
			self::$helpers->makedirs($phpbb_root_path . 'ext/foo/bar/');
		}

		foreach (self::$fixtures as $fixture)
		{
			self::$helpers->copy_dir(dirname(__FILE__) . '/fixtures/ext/' . $fixture, $phpbb_root_path . 'ext/' . $fixture);
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
			self::$helpers->empty_dir($phpbb_root_path . 'ext/' . $fixture);
		}
		self::$helpers->empty_dir($phpbb_root_path . 'ext/foo/');
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

		$this->assertContains($this->lang('EXTENSIONS_EXPLAIN'), $crawler->filter('#main')->text());
		$this->assertContains('phpBB 3.1 Extension Testing', $crawler->filter('#main')->text());
		$this->assertContains('Details', $crawler->filter('#main')->text());
	}

	public function test_extensions_details()
	{
		$crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=details&ext_name=foo%2Fbar&sid=' . $this->sid);
		$this->assert_response_success();

		// Test whether the details are displayed
		$this->assertContains($this->lang('CLEAN_NAME'), $crawler->filter('#main')->text());
		$this->assertContains('foo/bar', $crawler->filter('#meta_name')->text());

		$this->assertContains($this->lang('PHP_VERSION'), $crawler->filter('#main')->text());
		$this->assertContains('>=5.3', $crawler->filter('#require_php')->text());
		// Details should be html escaped
		// However, text() only returns the displayed text, so HTML Special Chars are decoded.
		// So we test this directly on the content of the response.
		$this->assertContains('<p id="require_php">&gt;=5.3</p>', $this->client->getResponse()->getContent());
	}

	public function test_extensions_details_notexists()
	{
		$crawler = $this->request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=details&ext_name=not%2Fexists&sid=' . $this->sid);
		$this->assert_response_success();

		// Error message because the files do not exist
		$this->assertContains('The required file does not exist:', $crawler->filter('#main')->text());
	}
}
