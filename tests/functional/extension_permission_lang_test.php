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
class phpbb_functional_extension_permission_lang_test extends phpbb_functional_test_case
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

		$this->login();
		$this->admin_login();
		$this->add_lang('acp/permissions');
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

	public function test_auto_include_permission_lang_from_extensions()
	{
		// User permissions
		$crawler = self::request('GET', 'adm/index.php?i=acp_permissions&icat=16&mode=setting_user_global&sid=' . $this->sid);

		// Select admin
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$data = array('username[0]' => 'admin');
		$form->setValues($data);
		$crawler = self::submit($form);

		// language from language/en/acp/permissions_phpbb.php
		$this->assertStringContainsString('Can attach files', $crawler->filter('body')->text());

		// language from ext/foo/bar/language/en/permissions_foo.php
		$this->assertStringContainsString('Can view foobar', $crawler->filter('body')->text());
	}
}
