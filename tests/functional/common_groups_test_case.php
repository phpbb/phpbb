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
abstract class phpbb_functional_common_groups_test_case extends phpbb_functional_test_case
{
	abstract protected function get_url();

	/**
	* Get group_manage form
	* @param int $group_id ID of the group that should be managed
	*/
	protected function get_group_manage_form($group_id = 5)
	{
		// Manage Administrators group
		$crawler = self::request('GET', $this->get_url() . "&g=$group_id&sid=" . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		return $form;
	}

	/**
	* Execute login calls and add_lang() calls for tests
	*/
	protected function group_manage_login()
	{
		$this->login();
		$this->admin_login();
		$this->add_lang(array('ucp', 'acp/groups'));
	}

	// Enable all avatars in the ACP
	protected function enable_all_avatars()
	{
		$this->add_lang('acp/board');

		$crawler = self::request('GET', 'adm/index.php?i=board&mode=avatar&sid=' . $this->sid);
		// Check the default entries we should have
		$this->assertContains($this->lang('ALLOW_REMOTE_UPLOAD'), $crawler->text());
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

	public function groups_manage_test_data()
	{
		return array(
			array('', 'GROUP_UPDATED'),
			array('aa0000', 'GROUP_UPDATED'),

			array('AAG000','WRONG_DATA_COLOUR'),
			array('#AA0000', 'WRONG_DATA_COLOUR'),
		);
	}

	/**
	* @dataProvider groups_manage_test_data
	*/
	public function test_groups_manage($input, $expected)
	{
		$this->group_manage_login();

		// Manage Administrators group
		$form = $this->get_group_manage_form();
		$form['group_colour']->setValue($input);
		$crawler = self::submit($form);
		$this->assertContains($this->lang($expected), $crawler->text());
	}

	public function group_avatar_min_max_data()
	{
		return array(
			array('avatar_driver_upload', 'avatar_upload_url', 'foo', 'AVATAR_URL_INVALID'),
			array('avatar_driver_upload', 'avatar_upload_url', 'foobar', 'AVATAR_URL_INVALID'),
			array('avatar_driver_upload', 'avatar_upload_url', 'http://www.phpbb.com/' . str_repeat('f', 240) . '.png', 'TOO_LONG'),
			array('avatar_driver_remote', 'avatar_remote_url', 'foo', 'AVATAR_URL_INVALID'),
			array('avatar_driver_remote', 'avatar_remote_url', 'foobar', 'AVATAR_URL_INVALID'),
			array('avatar_driver_remote', 'avatar_remote_url', 'http://www.phpbb.com/' . str_repeat('f', 240) . '.png', 'TOO_LONG'),
		);
	}

	/**
	* @dataProvider group_avatar_min_max_data
	*/
	public function test_group_avatar_min_max($avatar_type, $form_name, $input, $expected)
	{
		$this->login();
		$this->admin_login();
		$this->add_lang(array('ucp', 'acp/groups'));
		$this->enable_all_avatars();

		$crawler = self::request('GET', $this->get_url() . '&g=5&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->setValue($avatar_type);
		$form[$form_name]->setValue($input);
		$crawler = self::submit($form);
		$this->assertContains($this->lang($expected), $crawler->text());
	}
}
