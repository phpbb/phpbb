<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

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

		self::$helper = new phpbb_test_case_helpers(__CLASS__);
		self::$helper->copy_ext_fixtures(dirname(__FILE__) . '/fixtures/ext/', self::$fixtures);
	}

	static public function tearDownAfterClass()
	{
		parent::tearDownAfterClass();

		self::$helper->restore_original_ext_dir();
	}

	public function setUp(): void
	{
		global $db;

		parent::setUp();

		$this->phpbb_extension_manager = $this->get_extension_manager();
		$this->phpbb_extension_manager->enable('foo/bar');

		$this->purge_cache();
	}

	public function test_acp()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'app.php/admin/foo/bar?sid=' . $this->sid);
		$this->assertContains('Bertie rulez!', $crawler->filter('#main')->text());
	}

	public function test_ucp()
	{
		$this->login();

		$crawler = self::request('GET', 'app.php/user/index?sid=' . $this->sid);
		$this->assertContains('UCP_FOO_BAR_CAT', $crawler->filter('#tabs')->text());

		$link = $crawler->selectLink('UCP_FOO_BAR_CAT')->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'app.php/user')));
		$this->assertContains('UCP Extension Template Test Passed!', $crawler->filter('#content')->text());

		$this->phpbb_extension_manager->purge('foo/bar');
	}
}
