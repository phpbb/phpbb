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
	public function setUp()
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
		$this->assertContains($this->lang('ACP_PERMISSIONS_EXPLAIN'), $this->get_content());
	}

	public function test_select_user()
	{
		// User permissions
		$crawler = self::request('GET', 'adm/index.php?i=acp_permissions&icat=16&mode=setting_user_global&sid=' . $this->sid);
		$this->assertContains($this->lang('ACP_USERS_PERMISSIONS_EXPLAIN'), $this->get_content());

		// Select admin
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$data = array('username[0]' => 'admin');
		$form->setValues($data);
		$crawler = self::submit($form);
		$this->assertContains($this->lang('ACL_SET'), $crawler->filter('h1')->eq(1)->text());
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
		$crawler = self::request('GET', "adm/index.php?i=acp_permissions&icat=16&mode=$mode&${object_name}[0]=$object_id&type=$permission_type&sid=" . $this->sid);
		$this->assertContains($this->lang('ACL_SET'), $crawler->filter('h1')->eq(1)->text());

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
		$this->assertContains($this->lang('AUTH_UPDATED'), $crawler->text());

		// check acl again
		$auth = new \phpbb\auth\auth;
		// XXX hardcoded id
		$user_data = $auth->obtain_user_data(2);
		$auth->acl($user_data);
		$this->assertEquals(0, $auth->acl_get($permission));
	}
}
