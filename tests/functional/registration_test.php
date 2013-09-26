<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @group functional
*/
class phpbb_functional_registration_test extends phpbb_functional_test_case
{
	public function test_disable_captcha_on_registration()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', "adm/index.php?i=acp_board&mode=registration&sid={$this->sid}");
		$form = $crawler->selectButton('Submit')->form();
		$form['config[enable_confirm]']->setValue('0');
		$crawler = self::submit($form);

		$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('#main .successbox')->text());
	}

	public function test_register_new_account()
	{
		$this->add_lang('ucp');

		$crawler = self::request('GET', 'ucp.php?mode=register');
		$this->assertContainsLang('REGISTRATION', $crawler->filter('div.content h2')->text());

		$form = $crawler->selectButton('I agree to these terms')->form();
		$crawler = self::submit($form);

		$form = $crawler->selectButton('Submit')->form(array(
			'username'			=> 'user-reg-test',
			'email'				=> 'user-reg-test@phpbb.com',
			'new_password'		=> 'testtest',
			'password_confirm'	=> 'testtest',
		));
		$form['tz']->select('Europe/Berlin');
		$crawler = self::submit($form);

		$this->assertContainsLang('ACCOUNT_ADDED', $crawler->filter('#message')->text());
	}
}
