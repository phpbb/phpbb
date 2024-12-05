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
class phpbb_functional_acp_permissions_test extends phpbb_functional_test_case
{
	protected function setUp(): void
	{
		parent::setUp();

		$this->login();
		$this->admin_login();
		$this->add_lang('acp/permissions');
	}

	public function test_permissions_tab()
	{
		// Permissions tab
		// XXX hardcoded id
		$crawler = self::request('GET', 'adm/index.php?i=16&sid=' . $this->sid);
		// these language strings are html
		$this->assertStringContainsString($this->lang('ACP_PERMISSIONS_EXPLAIN'), $this->get_content());
	}

	public function test_select_user()
	{
		// User permissions
		$crawler = self::request('GET', 'adm/index.php?i=acp_permissions&icat=16&mode=setting_user_global&sid=' . $this->sid);
		$this->assertStringContainsString($this->lang('ACP_USERS_PERMISSIONS_EXPLAIN'), $this->get_content());

		// Select admin
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$data = array('username[0]' => 'admin');
		$form->setValues($data);
		$crawler = self::submit($form);
		$this->assertStringContainsString($this->lang('ACL_SET'), $crawler->filter('h1')->eq(1)->text());
	}

	public function permissions_data()
	{
		return array(
			// description
			// permission type
			// permission name
			// mode
			// object name
			// object id
			array(
				'user permission',
				'u_',
				'u_hideonline',
				'setting_user_global',
				'user_id',
				2,
			),
			array(
				'moderator permission',
				'm_',
				'm_ban',
				'setting_mod_global',
				'group_id',
				4,
			),
			/* Admin does not work yet, probably because founder can do everything
			array(
				'admin permission',
				'a_',
				'a_forum',
				'setting_admin_global',
				'group_id',
				5,
			),
			*/
		);
	}

	/**
	* @dataProvider permissions_data
	*/
	public function test_change_permission($description, $permission_type, $permission, $mode, $object_name, $object_id)
	{
		// Get the form
		$crawler = self::request('GET', "adm/index.php?i=acp_permissions&icat=16&mode=$mode&{$object_name}[0]=$object_id&type=$permission_type&sid=" . $this->sid);
		$this->assertStringContainsString($this->lang('ACL_SET'), $crawler->filter('h1')->eq(1)->text());

		// XXX globals for \phpbb\auth\auth, refactor it later
		global $db, $cache;
		$db = $this->get_db();
		$cache = new phpbb_mock_null_cache;

		$auth = new \phpbb\auth\auth;
		// XXX hardcoded id
		$user_data = $auth->obtain_user_data(2);
		$auth->acl($user_data);
		$this->assertEquals(1, $auth->acl_get($permission));

		// Set u_hideonline to never
		$form = $crawler->selectButton($this->lang('APPLY_PERMISSIONS'))->form();
		// initially it should be a yes
		$values = $form->getValues();
		$this->assertEquals(1, $values["setting[$object_id][0][$permission]"]);
		// set to never
		$data = array("setting[$object_id][0][$permission]" => '0');
		$form->setValues($data);
		$crawler = self::submit($form);
		$this->assertStringContainsString($this->lang('AUTH_UPDATED'), $crawler->text());

		// check acl again
		$auth = new \phpbb\auth\auth;
		// XXX hardcoded id
		$user_data = $auth->obtain_user_data(2);
		$auth->acl($user_data);
		$this->assertEquals(0, $auth->acl_get($permission));
	}

	public function test_forum_permissions_misc()
	{
		// Open forum moderators permissions page
		$crawler = self::request('GET', "adm/index.php?i=acp_permissions&icat=16&mode=setting_mod_local&sid=" . $this->sid);

		// Select "Your first forum"
		$form = $crawler->filter('#select_victim')->form(['forum_id' => [2]]);
		$crawler = self::submit($form);

		// Select "Global moderators"
		$form = $crawler->filter('#add_groups')->form(['group_id' => [4]]);
		$crawler = self::submit($form);

		// Check that global permissions are not displayed
		$this->add_lang('acp/permissions_phpbb');
		$page_text = $crawler->text();
		$this->assertNotContainsLang('ACL_M_BAN', $page_text);
		$this->assertNotContainsLang('ACL_M_PM_REPORT', $page_text);
		$this->assertNotContainsLang('ACL_M_WARN', $page_text);

		// Check that other permissions exist
		$this->assertContainsLang('ACL_M_EDIT', $page_text);
		$this->assertContainsLang('ACL_M_MOVE', $page_text);
	}

	public function test_tracing_user_based_permissions()
	{
		$this->create_user('newlyregistereduser');

		// Open user-based permissions masks page
		$crawler = self::request('GET', "adm/index.php?i=acp_permissions&icat=16&mode=view_user_global&sid=" . $this->sid);

		// Select newlyregistereduser
		$form = $crawler->filter('#add_user')->form(['username' => ['newlyregistereduser']]);
		$crawler = self::submit($form);

		// Test 1st "Yes" permission tracing result match
		$trace_link_yes = $crawler->filter('td.yes')->eq(0)->siblings()->filter('th > a.trace')->link();
		$crawler_trace_yes = self::$client->click($trace_link_yes);
		$this->assertEquals(1, $crawler_trace_yes->filter('tr.row2 > td.yes')->count());

		// Test 1st "Never" permission tracing result match
		$trace_link_never = $crawler->filter('td.never')->eq(0)->siblings()->filter('th > a.trace')->link();
		$crawler_trace_never = self::$client->click($trace_link_never);
		$this->assertEquals(1, $crawler_trace_never->filter('tr.row2 > td.never')->count());
	}
}
