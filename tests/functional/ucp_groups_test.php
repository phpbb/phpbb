<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/common_groups_test.php';

/**
* @group functional
*/
class phpbb_functional_ucp_groups_test extends phpbb_functional_common_groups_test
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
		$this->assertContains($this->lang('GROUP_UPDATED'), $crawler->text());
		$this->assertEquals($teampage_settings, $this->get_teampage_settings());
	}

	// Enable all avatars in the ACP
	private function enable_all_avatars()
	{
		$this->add_lang('acp/board');

		$crawler = self::request('GET', 'adm/index.php?i=board&mode=avatar&sid=' . $this->sid);
		// Check the default entries we should have
		$this->assertContains($this->lang('ALLOW_REMOTE'), $crawler->text());
		$this->assertContains($this->lang('ALLOW_AVATARS'), $crawler->text());
		$this->assertContains($this->lang('ALLOW_LOCAL'), $crawler->text());

		// Now start setting the needed settings
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['config[allow_avatar_local]']->select(1);
		$form['config[allow_avatar_remote]']->select(1);
		$form['config[allow_avatar_remote_upload]']->select(1);
		$crawler = self::submit($form);
		$this->assertContains($this->lang('CONFIG_UPDATED'), $crawler->text());
	}

	public function group_avatar_min_max_data()
	{
		return array(
			array('uploadurl', 'foo', 'TOO_SHORT'),
			array('uploadurl', 'foobar', 'AVATAR_URL_INVALID'),
			array('uploadurl', str_repeat('f', 256), 'TOO_LONG'),
			array('remotelink', 'foo', 'TOO_SHORT'),
			array('remotelink', 'foobar', 'AVATAR_URL_INVALID'),
			array('remotelink', str_repeat('f', 256), 'TOO_LONG'),
		);
	}

	/**
	* @dataProvider group_avatar_min_max_data
	*/
	public function test_group_avatar_min_max($form_name, $input, $expected)
	{
		$this->login();
		$this->admin_login();
		$this->add_lang(array('ucp', 'acp/groups'));
		$this->enable_all_avatars();

		$crawler = self::request('GET', $this->get_url() . '&g=5&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form[$form_name]->setValue($input);
		$crawler = self::submit($form);
		$this->assertContains($this->lang($expected), $crawler->text());
	}
}
