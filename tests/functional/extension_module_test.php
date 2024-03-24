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
require_once __DIR__ . '/../../phpBB/includes/acp/acp_modules.php';

/**
* @group functional
*/
class phpbb_functional_extension_module_test extends phpbb_functional_test_case
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

	protected function tearDown(): void
	{
		$this->uninstall_ext('foo/bar');

		parent::tearDown();
	}

	protected static function setup_extensions()
	{
		return ['foo/bar'];
	}

	public function test_acp()
	{
		$this->add_lang('common');
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=-foo-bar-acp-main_module&mode=mode&sid=' . $this->sid);
		$this->assertStringContainsString('ACP_FOOBAR_SETTINGS', $crawler->filter('#main')->text());
		$this->assertContainsLang('GENERAL_SETTINGS', $crawler->filter('fieldset')->text());

		$this->assertStringContainsString('SETTING_0', $crawler->filter('dl')->eq(0)->filter('dt > label[for="setting_0"]')->text());
		$this->assertStringContainsString('SETTING_0_EXPLAIN', $crawler->filter('dl')->eq(0)->filter('dt > span')->text());
		$this->assertEquals(2, $crawler->filter('dl')->eq(0)->filter('dd > input[type="number"]')->count());
		$this->assertEquals(1, $crawler->filter('dl')->eq(0)->filter('dd > input[type="number"]')->eq(0)->attr('value'));
		$this->assertEquals(17, $crawler->filter('dl')->eq(0)->filter('dd > input[type="number"]')->eq(1)->attr('value'));

		$this->assertStringContainsString('SETTING_1', $crawler->filter('dl')->eq(1)->filter('dt > label[for="setting_1"]')->text());
		$this->assertStringContainsString('CUSTOM_LANG_EXPLAIN', $crawler->filter('dl')->eq(1)->filter('dt > span')->text());
		$this->assertEquals(1, $crawler->filter('dl')->eq(1)->filter('dd > input[type="submit"]')->count());
		$this->assertEquals('Test submit button', $crawler->filter('dl')->eq(1)->filter('dd > input[type="submit"]')->attr('value'));

		$this->assertStringContainsString('SETTING_2', $crawler->filter('dl')->eq(2)->filter('dt > label[for="setting_2"]')->text());
		$this->assertEquals(0, $crawler->filter('dl')->eq(2)->filter('dt > span')->count());
		$this->assertEquals(2, $crawler->filter('dl')->eq(2)->filter('dd > label > input[type="radio"]')->count());
		$this->assertContainsLang('YES', $crawler->filter('dl')->eq(2)->filter('dd > label')->eq(0)->text());
		$this->assertEquals(1, $crawler->filter('dl')->eq(2)->filter('dd > label > input[type="radio"]')->eq(0)->attr('value'));
		$this->assertEquals('checked', $crawler->filter('dl')->eq(2)->filter('dd > label > input[type="radio"]')->eq(0)->attr('checked'));
		$this->assertContainsLang('NO', $crawler->filter('dl')->eq(2)->filter('dd > label')->eq(1)->text());
		$this->assertEquals(0, $crawler->filter('dl')->eq(2)->filter('dd > label > input[type="radio"]')->eq(1)->attr('value'));

		$this->assertStringContainsString('SETTING_3', $crawler->filter('dl')->eq(3)->filter('dt > label[for="setting_3"]')->text());
		$this->assertStringContainsString('SETTING_3_EXPLAIN', $crawler->filter('dl')->eq(3)->filter('dt > span')->text());
		$this->assertEquals(1, $crawler->filter('dl')->eq(3)->filter('dd > input[type="number"]')->count());
		$this->assertEquals(15, $crawler->filter('dl')->eq(3)->filter('dd > input[type="number"]')->attr('value'));

		$this->assertStringContainsString('SETTING_4', $crawler->filter('dl')->eq(4)->filter('dt > label[for="setting_4"]')->text());
		$this->assertStringContainsString('SETTING_4_EXPLAIN', $crawler->filter('dl')->eq(4)->filter('dt > span')->text());
		$this->assertEquals(1, $crawler->filter('dl')->eq(4)->filter('dd > select[id="setting_4"]')->count());
		$this->assertEquals(3, $crawler->filter('dl')->eq(4)->filter('dd > select > option')->count());
		$this->assertEquals(2, $crawler->filter('dl')->eq(4)->filter('dd > select > option')->eq(1)->attr('value'));
		$this->assertEquals('selected', $crawler->filter('dl')->eq(4)->filter('dd > select > option')->eq(1)->attr('selected'));

		$this->assertStringContainsString('SETTING_5', $crawler->filter('dl')->eq(5)->filter('dt > label[for="setting_5"]')->text());
		$this->assertStringContainsString('SETTING_5_EXPLAIN', $crawler->filter('dl')->eq(5)->filter('dt > span')->text());
		$this->assertEquals(1, $crawler->filter('dl')->eq(5)->filter('dd > input[type="text"]')->count());
		$this->assertEquals('Setting 5', $crawler->filter('dl')->eq(5)->filter('dd > input[type="text"]')->attr('value'));

		$this->assertStringContainsString('SETTING_6', $crawler->filter('dl')->eq(6)->filter('dt > label[for="setting_6"]')->text());
		$this->assertStringContainsString('SETTING_6_EXPLAIN', $crawler->filter('dl')->eq(6)->filter('dt > span')->text());
		$this->assertEquals(1, $crawler->filter('dl')->eq(6)->filter('dd > input[type="password"]')->count());
		$this->assertEquals('********', $crawler->filter('dl')->eq(6)->filter('dd > input[type="password"]')->attr('value'));

		$this->assertStringContainsString('SETTING_7', $crawler->filter('dl')->eq(7)->filter('dt > label[for="setting_7"]')->text());
		$this->assertStringContainsString('SETTING_7_EXPLAIN', $crawler->filter('dl')->eq(7)->filter('dt > span')->text());
		$this->assertEquals(1, $crawler->filter('dl')->eq(7)->filter('dd > input[type="email"]')->count());
		$this->assertEquals('test@example.dom', $crawler->filter('dl')->eq(7)->filter('dd > input[type="email"]')->attr('value'));

		$this->assertStringContainsString('SETTING_8', $crawler->filter('dl')->eq(8)->filter('dt > label[for="setting_8"]')->text());
		$this->assertStringContainsString('SETTING_8_EXPLAIN', $crawler->filter('dl')->eq(8)->filter('dt > span')->text());
		$this->assertEquals(1, $crawler->filter('dl')->eq(8)->filter('dd > textarea[name="config[setting_8]"]')->count());
		$this->assertEquals('Textarea', $crawler->filter('dl')->eq(8)->filter('dd > textarea[name="config[setting_8]"]')->text());

		$this->assertStringContainsString('SETTING_9', $crawler->filter('dl')->eq(9)->filter('dt > label[for="setting_9"]')->text());
		$this->assertStringContainsString('SETTING_9_EXPLAIN', $crawler->filter('dl')->eq(9)->filter('dt > span')->text());
		$this->assertEquals(2, $crawler->filter('dl')->eq(9)->filter('dd > label > input[type="radio"]')->count());
		$this->assertEquals('checked', $crawler->filter('dl')->eq(9)->filter('dd > label > input[type="radio"]')->eq(0)->attr('checked'));

		$this->assertStringContainsString('SETTING_10', $crawler->filter('dl')->eq(10)->filter('dt > label[for="setting_10"]')->text());
		$this->assertStringContainsString('SETTING_10_EXPLAIN', $crawler->filter('dl')->eq(10)->filter('dt > span')->text());
		$this->assertEquals(3, $crawler->filter('dl')->eq(10)->filter('dd > label > input[type="radio"]')->count());
		$this->assertEquals(1, $crawler->filter('dl')->eq(10)->filter('dd > label > input[type="radio"]')->eq(0)->attr('value'));
		$this->assertStringContainsString('LABEL_1', $crawler->filter('dl')->eq(10)->filter('dd > label')->eq(0)->text());
		$this->assertEquals(3, $crawler->filter('dl')->eq(10)->filter('dd > label > input[type="radio"]')->eq(1)->attr('value'));
		$this->assertEquals('checked', $crawler->filter('dl')->eq(10)->filter('dd > label > input[type="radio"]')->eq(1)->attr('checked'));
		$this->assertStringContainsString('LABEL_3', $crawler->filter('dl')->eq(10)->filter('dd > label')->eq(1)->text());
		$this->assertEquals(2, $crawler->filter('dl')->eq(10)->filter('dd > label > input[type="radio"]')->eq(2)->attr('value'));
		$this->assertStringContainsString('LABEL_2', $crawler->filter('dl')->eq(10)->filter('dd > label')->eq(2)->text());

		$this->assertStringContainsString('SETTING_11', $crawler->filter('dl')->eq(11)->filter('dt > label[for="setting_11"]')->text());
		$this->assertStringContainsString('SETTING_11_EXPLAIN', $crawler->filter('dl')->eq(11)->filter('dt > span')->text());
		$this->assertEquals('1', $crawler->filter('dl')->eq(11)->filter('dd > label > input[type="radio"]')->eq(0)->attr('value'));
		$this->assertEquals('0', $crawler->filter('dl')->eq(11)->filter('dd > label > input[type="radio"]')->eq(1)->attr('value'));
		$this->assertEquals('checked', $crawler->filter('dl')->eq(11)->filter('dd > label > input[type="radio"]')->eq(1)->attr('checked'));
		$this->assertContainsLang('YES', $crawler->filter('dl')->eq(11)->filter('dd > label')->eq(0)->text());
		$this->assertContainsLang('NO', $crawler->filter('dl')->eq(11)->filter('dd > label')->eq(1)->text());

		$this->assertStringContainsString('SETTING_12', $crawler->filter('dl')->eq(12)->filter('dt > label[for="setting_12"]')->text());
		$this->assertStringContainsString('SETTING_12_EXPLAIN', $crawler->filter('dl')->eq(12)->filter('dt > span')->text());
		$this->assertContainsLang('ENABLED', $crawler->filter('dl')->eq(12)->filter('dd > label')->eq(0)->text());
		$this->assertEquals(1, $crawler->filter('dl')->eq(12)->filter('dd > label > input[type="radio"]')->eq(0)->attr('value'));
		$this->assertContainsLang('DISABLED', $crawler->filter('dl')->eq(12)->filter('dd > label')->eq(1)->text());
		$this->assertEquals(0, $crawler->filter('dl')->eq(12)->filter('dd > label > input[type="radio"]')->eq(1)->attr('value'));
		$this->assertEquals('checked', $crawler->filter('dl')->eq(12)->filter('dd > label > input[type="radio"]')->eq(1)->attr('checked'));
	}

	public function test_ucp()
	{
		$this->login();

		$crawler = self::request('GET', 'ucp.php?sid=' . $this->sid);
		$this->assertStringContainsString('UCP_FOOBAR', $crawler->filter('#navigation')->text());

		$link = $crawler->selectLink('UCP_FOOBAR')->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'ucp.')));
		$this->assertStringContainsString('UCP Extension Template Test Passed!', $crawler->filter('#content')->text());
	}

	public function test_mcp()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'mcp.php?sid=' . $this->sid);
		$this->assertStringContainsString('MCP_FOOBAR', $crawler->filter('#navigation')->text());

		$link = $crawler->selectLink('MCP_FOOBAR')->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'mcp.')));
		$this->assertStringContainsString('MCP Extension Template Test Passed!', $crawler->filter('#content')->text());
	}
}
