<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
