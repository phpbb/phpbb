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

	static private $helper;

	static protected $fixtures = array(
		'foo/bar/',
	);

	/**
	* This should only be called once before the tests are run.
	* This is used to copy the fixtures to the phpBB install
	*/
	static public function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		self::$helper = new phpbb_test_case_helpers(self);
		self::$helper->copy_ext_fixtures(dirname(__FILE__) . '/fixtures/ext/', self::$fixtures);
	}

	/**
	* This should only be called once after the tests are run.
	* This is used to remove the fixtures from the phpBB install
	*/
	static public function tearDownAfterClass()
	{
		parent::tearDownAfterClass();

		self::$helper->restore_original_ext_dir();
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
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&sid=' . $this->sid);
		$this->assertContains($this->lang('EXTENSIONS_EXPLAIN'), $crawler->filter('#main')->text());
		$this->assertContains('phpBB 3.1 Extension Testing', $crawler->filter('#main')->text());
		$this->assertContains('Details', $crawler->filter('#main')->text());
	}

	public function test_extensions_details()
	{
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=details&ext_name=foo%2Fbar&sid=' . $this->sid);

		// Test whether the details are displayed
		$this->assertContains($this->lang('CLEAN_NAME'), $crawler->filter('#main')->text());
		$this->assertContains('foo/bar', $crawler->filter('#meta_name')->text());

		$this->assertContains($this->lang('PHP_VERSION'), $crawler->filter('#main')->text());
		$this->assertContains('>=5.3', $crawler->filter('#require_php')->text());
		// Details should be html escaped
		// However, text() only returns the displayed text, so HTML Special Chars are decoded.
		// So we test this directly on the content of the response.
		$this->assertContains('<p id="require_php">&gt;=5.3</p>', $this->get_content());
	}

	public function test_extensions_details_notexists()
	{
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=details&ext_name=not%2Fexists&sid=' . $this->sid);

		// Error message because the files do not exist
		$this->assertContains('The required file does not exist:', $crawler->filter('#main')->text());
	}
}
