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
class phpbb_functional_metadata_manager_test extends phpbb_functional_test_case
{
	protected $phpbb_extension_manager;

	private static $helper;

	protected static $fixtures = array(
		'./',
	);

	protected function tearDown(): void
	{
		$this->uninstall_ext('foo/bar');

		parent::tearDown();
	}

	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		self::$helper = new phpbb_test_case_helpers(__CLASS__);
		self::$helper->copy_ext_fixtures(__DIR__ . '/fixtures/ext/', self::$fixtures);
	}

	public static function tearDownAfterClass(): void
	{
		parent::tearDownAfterClass();

		self::$helper->restore_original_ext_dir();
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->login();
		$this->admin_login();
		$this->add_lang('acp/extensions');
	}

	protected static function setup_extensions()
	{
		return ['foo/bar'];
	}

	public function test_extensions_list()
	{
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&sid=' . $this->sid);
		$this->assertStringContainsString($this->lang('EXTENSIONS_EXPLAIN'), $crawler->filter('#main')->text());
		$this->assertStringContainsString('phpBB 3.1 Extension Testing', $crawler->filter('#main')->text());
		$this->assertStringContainsString('Details', $crawler->filter('#main')->text());
	}

	public function test_extensions_details()
	{
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=details&ext_name=foo%2Fbar&sid=' . $this->sid);

		// Test whether the details are displayed
		$this->assertStringContainsString($this->lang('CLEAN_NAME'), $crawler->filter('#main')->text());
		$this->assertStringContainsString('foo/bar', $crawler->filter('#meta_name')->text());

		$this->assertStringContainsString($this->lang('PHP_VERSION'), $crawler->filter('#main')->text());
		$this->assertStringContainsString('>=5.3', $crawler->filter('#require_php')->text());
		// Details should be html escaped
		// However, text() only returns the displayed text, so HTML Special Chars are decoded.
		// So we test this directly on the content of the response.
		$this->assertStringContainsString('<span id="require_php">&gt;=5.3</span>', $this->get_content());
	}

	public function test_extensions_details_notexists()
	{
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=details&ext_name=not%2Fexists&sid=' . $this->sid);

		// Error message because the files do not exist
		$this->assertStringContainsString($this->lang('FILE_NOT_FOUND', ''), $crawler->filter('#main')->text());
	}
}
