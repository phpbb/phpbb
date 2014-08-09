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
class phpbb_functional_forgot_password_test extends phpbb_functional_test_case
{
	public function test_forgot_password_enabled()
	{
		global $config;
		$this->add_lang('ucp');
		$crawler = self::request('GET', 'ucp.php?mode=sendpassword');
		$this->assertEquals($this->lang('SEND_PASSWORD'), $crawler->filter('h2')->text());
	}

	public function test_forgot_password_disabled()
	{
		$this->login();
		$this->admin_login();
		$this->add_lang('ucp');
		$crawler = self::request('GET', 'adm/index.php?sid=' . $this->sid . '&i=acp_board&mode=security');

		$form = $crawler->selectButton('Submit')->form();
		$values = $form->getValues();

		$values["config[allow_password_reset]"] = 0;
		$form->setValues($values);
		$crawler = self::submit($form);

		$this->logout();

		$crawler = self::request('GET', 'ucp.php?mode=sendpassword');
		$this->assertContains($this->lang('UCP_PASSWORD_RESET_DISABLED', '', ''), $crawler->text());

	}

	public function tearDown()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?sid=' . $this->sid . '&i=acp_board&mode=security');

		// Enable allow_password_reset again after test
		$form = $crawler->selectButton('Submit')->form(array(
			'config[allow_password_reset]'	=> 1,
		));
		$crawler = self::submit($form);
	}
}
