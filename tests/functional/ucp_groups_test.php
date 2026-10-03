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

require_once __DIR__ . '/common_groups_test_case.php';

/**
* @group functional
*/
class phpbb_functional_ucp_groups_test extends phpbb_functional_common_groups_test_case
{
	protected function get_url()
	{
		return 'ucp.php?i=groups&mode=manage&action=edit';
	}

	/**
	* Create a normal group with the admin user as group leader
	*
	* Special groups can no longer be managed from the UCP, so the UCP
	* management tests need a regular group led by the admin user.
	*
	* @return int Group id of the created group
	*/
	protected function create_manage_group()
	{
		$group_id = $this->get_group_id('manage-group');
		if ($group_id)
		{
			return $group_id;
		}

		$crawler = self::request('GET', 'adm/index.php?i=acp_groups&mode=manage&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, array('group_name' => 'manage-group'));

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, array('group_name' => 'manage-group'));

		$this->assertContainsLang('GROUP_CREATED', $crawler->filter('#main')->text());

		$group_id = $this->get_group_id('manage-group');

		// Make admin group leader
		$crawler = self::request('GET', 'adm/index.php?i=acp_groups&mode=manage&action=list&g=' . $group_id . '&sid=' . $this->sid);
		$form = $crawler->filter('input[name=addusers]')->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, [
			'leader'	=> 1,
			'usernames'	=> 'admin',
		]);

		$this->assertContainsLang('GROUP_MODS_ADDED', $crawler->filter('#main')->text());

		return $group_id;
	}

	protected function get_teampage_settings($group_id)
	{
		$sql = 'SELECT g.group_legend AS group_legend, t.teampage_position AS group_teampage
			FROM ' . GROUPS_TABLE . ' g
			LEFT JOIN ' . TEAMPAGE_TABLE . ' t
				ON (t.group_id = g.group_id)
			WHERE g.group_id = ' . (int) $group_id;
		$result = $this->db->sql_query($sql);
		$group_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $group_row;
	}

	public function test_ucp_groups_teampage()
	{
		$this->group_manage_login();
		$group_id = $this->create_manage_group();

		// Test if group_legend or group_teampage are modified while
		// submitting the ucp_group_manage page
		$form = $this->get_group_manage_form($group_id);
		$teampage_settings = $this->get_teampage_settings($group_id);
		$crawler = self::submit($form);
		$this->assertStringContainsString($this->lang('GROUP_UPDATED'), $crawler->text());
		$this->assertEquals($teampage_settings, $this->get_teampage_settings($group_id));
	}

	/**
	* @dataProvider groups_manage_test_data
	*/
	public function test_groups_manage($input, $expected)
	{
		$this->group_manage_login();
		$group_id = $this->create_manage_group();

		$form = $this->get_group_manage_form($group_id);
		$form['group_colour']->setValue($input);
		$crawler = self::submit($form);
		$this->assertStringContainsString($this->lang($expected), $crawler->text());
	}

	public function test_manage_special_group_denied()
	{
		$this->group_manage_login();

		// The Administrators group is not listed on the UCP manage groups page
		// even though the admin user is its group leader
		$crawler = self::request('GET', 'ucp.php?i=groups&mode=manage&sid=' . $this->sid);
		$this->assertStringNotContainsString('action=edit&amp;g=5', $crawler->html());

		// Special groups can not be managed from the UCP
		$crawler = self::request('GET', 'ucp.php?i=groups&mode=manage&action=edit&g=5&sid=' . $this->sid);
		$this->assertContainsLang('NOT_ALLOWED_MANAGE_GROUP', $crawler->text());

		$crawler = self::request('GET', 'ucp.php?i=groups&mode=manage&action=list&g=5&sid=' . $this->sid);
		$this->assertContainsLang('NOT_ALLOWED_MANAGE_GROUP', $crawler->text());
	}

	public function test_create_request_group()
	{
		$this->login();
		$this->admin_login();
		$this->add_lang('acp/groups');

		$crawler = self::request('GET', 'adm/index.php?i=acp_groups&mode=manage&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, array('group_name' => 'request-group'));

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, array('group_name' => 'request-group'));

		$this->assertContainsLang('GROUP_CREATED', $crawler->filter('#main')->text());

		$group_id = $this->get_group_id('request-group');

		// Make admin group leader
		$crawler = self::request('GET', 'adm/index.php?i=acp_groups&mode=manage&action=list&g=' . $group_id . '&sid=' . $this->sid);
		$form = $crawler->filter('input[name=addusers]')->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, [
			'leader'	=> 1,
			'usernames'	=> 'admin',
		]);

		$this->assertContainsLang('GROUP_MODS_ADDED', $crawler->filter('#main')->text());
	}

	/**
	 * @depends test_create_request_group
	 */
	public function test_request_group_membership()
	{
		$this->create_user('request-group-user');
		$this->login('request-group-user');
		$this->add_lang('groups');

		$group_id = $this->get_group_id('request-group');

		$crawler = self::request('GET', 'ucp.php?i=ucp_groups&mode=membership&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, ['selected' => $group_id, 'action' => 'join']);
		$this->assertContainsLang('GROUP_JOIN_PENDING_CONFIRM', $crawler->text());

		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('GROUP_JOINED_PENDING', $crawler->text());
	}

	/**
	 * @depends test_request_group_membership
	 */
	public function test_approve_group_membership()
	{
		$this->login();
		$this->add_lang('acp/groups');

		$group_id = $this->get_group_id('request-group');
		$crawler = self::request('GET', 'ucp.php?i=ucp_groups&mode=manage&action=list&g=' . $group_id . '&sid=' . $this->sid);
		$form = $crawler->filter('input[name=update]')->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, [
			'mark'	=> [$crawler->filter('input[name="mark[]"]')->first()->attr('value')],
			'action'	=> 'approve',
		]);

		$this->assertContainsLang('USERS_APPROVED', $crawler->text());
	}
}
