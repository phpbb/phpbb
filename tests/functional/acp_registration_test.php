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
class phpbb_functional_acp_registration_test extends phpbb_functional_test_case
{
	protected function set_email_enable($db, $status)
	{
		$sql = "UPDATE phpbb_config
			SET config_value = '" . (($status) ? '1' : '0') . "'
			WHERE config_name = 'email_enable'";
		$db->sql_query($sql);

		$this->purge_cache();
	}

	public function test_submitting_activation_method()
	{
		$db = $this->get_db();

		$this->set_email_enable($db, false);

		$this->add_lang('acp/board');
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=registration&sid=' . $this->sid);
		$this->assertContainsLang('ACP_REGISTER_SETTINGS_EXPLAIN', $this->get_content());

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['config[require_activation]']->select(USER_ACTIVATION_ADMIN);
		$crawler = self::submit($form);
		$this->assertContainsLang('ACC_ACTIVATION_WARNING', $crawler->filter('div.main')->text());

		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=registration&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['config[require_activation]']->select(USER_ACTIVATION_NONE);
		$crawler = self::submit($form);
		$this->assertNotContainsLang('ACC_ACTIVATION_WARNING', $crawler->filter('div.main')->text());

		$this->set_email_enable($db, true);
	}
}
