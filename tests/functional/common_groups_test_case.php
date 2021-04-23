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
		$this->assertStringContainsString($this->lang('ALLOW_AVATARS'), $crawler->text());
		$this->assertStringContainsString($this->lang('ALLOW_LOCAL'), $crawler->text());

		// Now start setting the needed settings
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['config[allow_avatar_local]']->select(1);
		$crawler = self::submit($form);
		$this->assertStringContainsString($this->lang('CONFIG_UPDATED'), $crawler->text());
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
		$this->assertStringContainsString($this->lang($expected), $crawler->text());
	}
}
