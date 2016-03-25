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
class phpbb_functional_acp_users_test extends phpbb_functional_test_case
{
	public function setUp()
	{
		parent::setUp();

		$this->login();
		$this->admin_login();
		$this->add_lang('acp/users');
	}

	public function test_founder_deletion()
	{
		$username = 'founder-account';
		$user_id = $this->create_user($username);
		$this->make_founder($user_id);

		$crawler = self::request('GET', "adm/index.php?i=users&mode=overview&u=$user_id&sid={$this->sid}");
		$form = $crawler->filter('#user_delete')->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form);
		$this->assertContains($this->lang('CANNOT_REMOVE_FOUNDER'), $this->get_content());
	}

	protected function make_founder($user_id)
	{
		$crawler = self::request('GET', "adm/index.php?i=users&mode=overview&u=$user_id&sid={$this->sid}");
		$form = $crawler->filter('#user_overview')->selectButton($this->lang('SUBMIT'))->form();
		$data = array('user_founder' => '1');
		$form->setValues($data);
		$crawler = self::submit($form);
		$this->assertContains($this->lang('USER_OVERVIEW_UPDATED'), $this->get_content());
	}
}
