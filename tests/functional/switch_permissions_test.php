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
class phpbb_functional_switch_permissions_test extends phpbb_functional_test_case
{
	private const TEST_USER = 'switch-permissions-test';

	protected function setUp(): void
	{
		parent::setUp();

		$this->login();
		$this->admin_login();

		$this->add_lang(['common', 'ucp']);
	}

	public function test_switch_permissions_acp()
	{
		$user_id = $this->create_user(self::TEST_USER);

		// Open user administration page for new user
		$crawler = self::request('GET', "adm/index.php?i=users&mode=overview&u={$user_id}&sid={$this->sid}");

		// Use permissions
		$link = $crawler->selectLink($this->lang('USE_PERMISSIONS'))->link();
		$crawler = self::$client->click($link);

		// Check that we switched permissions to test user
		$this->assertStringContainsString(
			str_replace('<br />', '<br>', $this->lang('PERMISSIONS_TRANSFERRED', self::TEST_USER)),
			$crawler->html()
		);

		// Check that ACP pages get forced to acp main with restore permission info
		$this->add_lang('acp/common');
		$crawler = self::request('GET', "adm/index.php?i=users&mode=overview&u={$user_id}&sid={$this->sid}");
		$this->assertStringContainsString(
			$this->lang('PERMISSIONS_TRANSFERRED'),
			$crawler->text()
		);

		// Check that restore permissions link exists
		$crawler = self::$client->request('GET', '../index.php?sid=' . $this->sid);
		$this->assertStringContainsString(
			$this->lang('RESTORE_PERMISSIONS'),
			$crawler->text()
		);

		// Check that restore permissions works
		$crawler = self::$client->request('GET', 'ucp.php?mode=restore_perm&sid=' . $this->sid);
		$this->assertStringContainsString(
			$this->lang('PERMISSIONS_RESTORED'),
			$crawler->text()
		);
	}

	/**
	 * @depends test_switch_permissions_acp
	 */
	public function test_switch_permissions_ucp()
	{
		$db = $this->get_db();
		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . "
			WHERE username = '" . self::TEST_USER . "'";
		$result = $db->sql_query($sql);
		$user_id = $db->sql_fetchfield('user_id');
		$db->sql_freeresult($result);

		// Open memberlist profile page for user
		$crawler = self::request('GET', "memberlist.php?mode=viewprofile&u={$user_id}&sid={$this->sid}");

		// Use permissions
		$link = $crawler->selectLink($this->lang('USE_PERMISSIONS'))->link();
		$crawler = self::$client->click($link);

		// Check that we switched permissions to test user
		$this->assertStringContainsString(
			str_replace('<br />', '<br>', $this->lang('PERMISSIONS_TRANSFERRED', self::TEST_USER)),
			$crawler->html()
		);

		// Check that UCP pages don't get forced to UCP main with restore permission info
		$this->add_lang(['memberlist', 'ucp']);
		$crawler = self::request('GET', "ucp.php?i=ucp_profile&mode=profile_info&sid={$this->sid}");
		$this->assertStringContainsString(
			$this->lang('EDIT_PROFILE'),
			$crawler->text()
		);

		// Check that restore permissions link exists
		$crawler = self::$client->request('GET', 'index.php?sid=' . $this->sid);
		$this->assertStringContainsString(
			$this->lang('RESTORE_PERMISSIONS'),
			$crawler->text()
		);

		// Check that restore permissions works
		$crawler = self::$client->request('GET', 'ucp.php?mode=restore_perm&sid=' . $this->sid);
		$this->assertStringContainsString(
			$this->lang('PERMISSIONS_RESTORED'),
			$crawler->text()
		);
	}
}
