<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @group functional
*/
class phpbb_functional_extension_acp_test extends phpbb_functional_test_case
{
	static private $helper;

	static protected $fixtures = array(
		'./',
	);

	static public function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		self::$helper = new phpbb_test_case_helpers(self);
		self::$helper->copy_ext_fixtures(dirname(__FILE__) . '/../extension/ext/', self::$fixtures);
	}

	static public function tearDownAfterClass()
	{
		parent::tearDownAfterClass();

		self::$helper->restore_original_ext_dir();
	}

	public function setUp()
	{
		parent::setUp();

		$this->get_db();

		// Clear the phpbb_ext table
		$this->db->sql_query('DELETE FROM phpbb_ext');

		// Insert our base data
		$insert_rows = array(
			array(
				'ext_name'		=> 'foo',
				'ext_active'	=> true,
				'ext_state'		=> 'b:0;',
			),
			array(
				'ext_name'		=> 'vendor/moo',
				'ext_active'	=> false,
				'ext_state'		=> 'b:0;',
			),

			// do not exist
			array(
				'ext_name'		=> 'test2',
				'ext_active'	=> true,
				'ext_state'		=> 'b:0;',
			),
			array(
				'ext_name'		=> 'test3',
				'ext_active'	=> false,
				'ext_state'		=> 'b:0;',
			),
		);
		$this->db->sql_multi_insert('phpbb_ext', $insert_rows);

		$this->login();
		$this->admin_login();

		$this->add_lang('acp/extensions');
	}

	public function test_list()
	{
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&sid=' . $this->sid);

		$this->assertCount(1, $crawler->filter('.ext_enabled'));
		$this->assertCount(5, $crawler->filter('.ext_disabled'));

		$this->assertContains('phpBB Foo Extension', $crawler->filter('.ext_enabled')->eq(0)->text());
		$this->assertContainsLang('EXTENSION_UNINSTALL', $crawler->filter('.ext_enabled')->eq(0)->text());

		$this->assertContains('The “test2” extension is not valid.', $crawler->filter('.ext_disabled')->eq(0)->text());

		$this->assertContains('The “test3” extension is not valid.', $crawler->filter('.ext_disabled')->eq(1)->text());

		$this->assertContains('phpBB Moo Extension', $crawler->filter('.ext_disabled')->eq(2)->text());
		$this->assertContainsLang('DETAILS', $crawler->filter('.ext_disabled')->eq(2)->text());
		$this->assertContainsLang('EXTENSION_ENABLE', $crawler->filter('.ext_disabled')->eq(2)->text());
		$this->assertContainsLang('EXTENSION_UNINSTALL', $crawler->filter('.ext_disabled')->eq(2)->text());

		$this->assertContains('The “bar” extension is not valid.', $crawler->filter('.ext_disabled')->eq(3)->text());
	}

	public function test_details()
	{
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=details&ext_name=foo&sid=' . $this->sid);

		$validation = array(
			'DISPLAY_NAME'		=> 'phpBB Foo Extension',
			'CLEAN_NAME'		=> 'foo/example',
			'TYPE'				=> 'phpbb-extension',
			'DESCRIPTION'		=> 'An example/sample extension to be used for testing purposes in phpBB Development.',
			'VERSION'	  		=> '1.0.0',
			'TIME'				=> '2012-02-15 01:01:01',
			'LICENCE'			=> 'GPL-2.0',
			'PHPBB_VERSION'		=> '3.1.*@dev',
			'PHP_VERSION'		=> '>=5.3',
			'AUTHOR_NAME'		=> 'John Smith',
			'AUTHOR_EMAIL'		=> 'email@phpbb.com',
			'AUTHOR_HOMEPAGE'	=> 'http://phpbb.com',
			'AUTHOR_ROLE'		=> 'N/A',
		);

		for ($i = 0; $i < $crawler->filter('dl')->count(); $i++)
		{
			$text = $crawler->filter('dl')->eq($i)->text();

			$match = false;

			foreach ($validation as $language_key => $expected)
			{
				if (strpos($text, $this->lang($language_key)) === 0)
				{
					$match = true;

					$this->assertContains($expected, $text);
				}
			}

			if (!$match)
			{
				$this->fail('Unexpected field: "' . $text . '"');
			}
		}
	}

	public function test_enable_pre()
	{
		// Foo is already enabled (redirect to list)
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=enable_pre&ext_name=foo&sid=' . $this->sid);
		$this->assertContainsLang('EXTENSION_NAME', $crawler->filter('html')->text());
		$this->assertContainsLang('EXTENSION_OPTIONS', $crawler->filter('html')->text());
		$this->assertContainsLang('EXTENSION_ACTIONS', $crawler->filter('html')->text());

		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=enable_pre&ext_name=vendor%2Fmoo&sid=' . $this->sid);
		$this->assertContains($this->lang('EXTENSION_ENABLE_CONFIRM', 'phpBB Moo Extension'), $crawler->filter('html')->text());
	}

	public function test_disable_pre()
	{
		// Moo is not enabled (redirect to list)
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=disable_pre&ext_name=vendor%2Fmoo&sid=' . $this->sid);
		$this->assertContainsLang('EXTENSION_NAME', $crawler->filter('html')->text());
		$this->assertContainsLang('EXTENSION_OPTIONS', $crawler->filter('html')->text());
		$this->assertContainsLang('EXTENSION_ACTIONS', $crawler->filter('html')->text());

		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=disable_pre&ext_name=foo&sid=' . $this->sid);
		$this->assertContains($this->lang('EXTENSION_DISABLE_CONFIRM', 'phpBB Foo Extension'), $crawler->filter('html')->text());
	}

	public function test_purge_pre()
	{
		// test2 is not available (error)
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=purge_pre&ext_name=test2&sid=' . $this->sid);
		$this->assertContains('The required file does not exist', $crawler->filter('html')->text());

		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=purge_pre&ext_name=foo&sid=' . $this->sid);
		$this->assertContains($this->lang('EXTENSION_UNINSTALL_CONFIRM', 'phpBB Foo Extension'), $crawler->filter('html')->text());
	}

	public function test_actions()
	{
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=enable&ext_name=vendor%2Fmoo&sid=' . $this->sid);
		$this->assertContainsLang('EXTENSION_ENABLE_SUCCESS', $crawler->filter('html')->text());

		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=disable&ext_name=vendor%2Fmoo&sid=' . $this->sid);
		$this->assertContainsLang('EXTENSION_DISABLE_SUCCESS', $crawler->filter('html')->text());

		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=purge&ext_name=vendor%2Fmoo&sid=' . $this->sid);
		$this->assertContainsLang('EXTENSION_UNINSTALL_SUCCESS', $crawler->filter('html')->text());
	}
}
