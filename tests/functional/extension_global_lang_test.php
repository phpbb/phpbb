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

	private static $helper;

	protected static $fixtures = array(
		'./',
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
		$this->purge_cache();
	}

	protected function tearDown(): void
	{
		$this->uninstall_ext('foo/bar');

		parent::tearDown();
	}

	protected static function setup_extensions()
	{
		return ['foo/bar'];
	}

	public function test_load_extension_lang_globally()
	{
		// The board index, which should contain an overwritten translation
		$crawler = self::request('GET', 'index.php');

		// language from language/en/common.php
		$this->assertStringNotContainsString('Skip to content', $crawler->filter('.skiplink')->text());

		// language from ext/foo/bar/language/en/foo_global.php
		$this->assertStringContainsString('Overwritten by foo', $crawler->filter('.skiplink')->text());
	}
}
