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
class phpbb_functional_extension_global_lang_test extends phpbb_functional_test_case
{
	protected $phpbb_extension_manager;

	static private $helper;

	static protected $fixtures = array(
		'foo/bar/config/',
		'foo/bar/event/',
		'foo/bar/language/en/',
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
		parent::setUp();
		
		$this->get_db();

		$this->phpbb_extension_manager = $this->get_extension_manager();

		$this->purge_cache();
	}

	public function test_load_extension_lang_globally()
	{
		$this->phpbb_extension_manager->enable('foo/bar');

		// The board index, which should contain an overwritten translation
		$crawler = self::request('GET', 'index.php');

		// language from language/en/common.php
		$this->assertNotContains('Skip to content', $crawler->filter('.skiplink')->text());

		// language from ext/foo/bar/language/en/foo_global.php
		$this->assertContains('Overwritten by foo', $crawler->filter('.skiplink')->text());
	}
}
