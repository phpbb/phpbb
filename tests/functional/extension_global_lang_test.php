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

	static public function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		self::$helper = new phpbb_test_case_helpers(__CLASS__);
		self::$helper->copy_ext_fixtures(__DIR__ . '/fixtures/ext/', self::$fixtures);
	}

	static public function tearDownAfterClass(): void
	{
		parent::tearDownAfterClass();

		self::$helper->restore_original_ext_dir();
	}

	protected function setUp(): void
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
		$this->assertStringNotContainsString('Skip to content', $crawler->filter('.skiplink')->text());

		// language from ext/foo/bar/language/en/foo_global.php
		$this->assertStringContainsString('Overwritten by foo', $crawler->filter('.skiplink')->text());

		$this->phpbb_extension_manager->purge('foo/bar');
	}
}
