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
class phpbb_functional_group_create_test extends phpbb_functional_test_case
{

	public function test_create_group()
	{
		$this->login();
		$this->admin_login();
		$this->add_lang('acp/groups');

		$crawler = self::request('GET', 'adm/index.php?i=acp_groups&mode=manage&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, array('group_name' => 'testtest'));

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, array('group_name' => 'testtest'));

		$this->assertContainsLang('GROUP_CREATED', $crawler->filter('#main')->text());
	}
}
