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
	protected $db;

	protected function get_url()
	{
		return 'ucp.php?i=groups&mode=manage&action=edit';
	}

	protected function get_teampage_settings()
	{
		if (!isset($this->db))
		{
			$this->db = $this->get_db();
		}
		$sql = 'SELECT g.group_legend AS group_legend, t.teampage_position AS group_teampage
			FROM ' . GROUPS_TABLE . ' g
			LEFT JOIN ' . TEAMPAGE_TABLE . ' t
				ON (t.group_id = g.group_id)
			WHERE g.group_id = 5';
		$result = $this->db->sql_query($sql);
		$group_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $group_row;
	}

	public function test_ucp_groups_teampage()
	{
		$this->group_manage_login();

		// Test if group_legend or group_teampage are modified while
		// submitting the ucp_group_manage page
		$form = $this->get_group_manage_form();
		$teampage_settings = $this->get_teampage_settings();
		$crawler = self::submit($form);
		$this->assertStringContainsString($this->lang('GROUP_UPDATED'), $crawler->text());
		$this->assertEquals($teampage_settings, $this->get_teampage_settings());
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
