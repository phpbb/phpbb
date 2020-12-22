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

	/**
	* @depends test_disable_captcha_on_registration
	*/
	public function test_register_new_account()
	{
		$this->add_lang('ucp');

		// Check that we can't skip
		self::request('GET', 'ucp.php?mode=register&agreed=1');
		$this->assertContainsLang('AGREE', $this->get_content());

		$crawler = self::request('GET', 'ucp.php?mode=register');
		$this->assertContainsLang('REGISTRATION', $crawler->filter('div.content h2')->text());

		$form = $crawler->selectButton('I agree to these terms')->form();
		$crawler = self::submit($form);

		$form = $crawler->selectButton('Submit')->form(array(
			'username'			=> 'user-reg-test',
			'email'				=> 'user-reg-test@phpbb.com',
			'new_password'		=> 'user-reg-testuser-reg-test',
			'password_confirm'	=> 'user-reg-testuser-reg-test',
		));
		$form['tz']->select('Europe/Berlin');
		$crawler = self::submit($form);

		$this->assertContainsLang('ACCOUNT_ADDED', $crawler->filter('#message')->text());
	}

	/**
	 * @depends test_register_new_account
	 */
	public function test_default_subscription_options()
	{
		$this->login('user-reg-test');
		$crawler = self::request('GET', 'ucp.php?i=ucp_notifications&mode=notification_options&sid=' . $this->sid);
		$this->assert_checkbox_is_checked($crawler, 'notification.type.post_notification.method.email');
		$this->assert_checkbox_is_checked($crawler, 'notification.type.topic_notification.method.email');
	}

	/**
	 * @depends test_disable_captcha_on_registration
	 */
	public function test_register_coppa_account()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', "adm/index.php?i=acp_board&mode=registration&sid={$this->sid}");
		$form = $crawler->selectButton('Submit')->form();
		$form['config[coppa_enable]']->setValue('1');
		$crawler = self::submit($form);

		$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('#main .successbox')->text());
		$this->logout();

		$this->add_lang('ucp');

		// Check that we can't skip
		$crawler = self::request('GET', 'ucp.php?mode=register&coppa=1');
		$this->assertContainsLang('COPPA_BIRTHDAY', $crawler->html());

		$form = $crawler->selectButton('coppa_yes')->form();
		$crawler = self::submit($form);

		$this->assertContainsLang('REGISTRATION', $crawler->filter('div.content h2')->text());

		$form = $crawler->selectButton('I agree to these terms')->form();
		$crawler = self::submit($form);

		$form = $crawler->selectButton('Submit')->form(array(
			'username'			=> 'user-coppa-test',
			'email'				=> 'user-coppa-test@phpbb.com',
			'new_password'		=> 'user-coppa-testuser-coppa-test',
			'password_confirm'	=> 'user-coppa-testuser-coppa-test',
		));
		$form['tz']->select('Europe/Berlin');
		$crawler = self::submit($form);

		$this->assertContainsLang('ACCOUNT_COPPA', $crawler->filter('#message')->text());

		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', "adm/index.php?i=acp_board&mode=registration&sid={$this->sid}");
		$form = $crawler->selectButton('Submit')->form();
		$form['config[coppa_enable]']->setValue('0');
		$crawler = self::submit($form);
	}

	/**
	* @depends test_disable_captcha_on_registration
	*/
	public function test_register_new_account_with_cpf_numbers()
	{
		$this->add_lang(['ucp', 'acp/profile']);

		// Create "Numbers" type CPF but don't set its default value
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', "adm/index.php?i=acp_profile&mode=profile&sid={$this->sid}");
		$form = $crawler->selectButton('submit')->form([ // Create new field
			'field_type'		=> 'profilefields.type.int',
		]);
		$crawler = self::submit($form);

		$this->assertContainsLang('STEP_1_TITLE_CREATE', $this->get_content());
		$this->assertStringContainsString('Numbers', $crawler->filter('dl > dd > strong')->text());

		$form = $crawler->selectButton('next')->form([ // Go to Profile type specific options
			'field_ident'		=> 'Numbers',
			'lang_name'			=> 'Numbers CPF',
		]);
		$form['field_show_on_reg']->tick();
		$crawler = self::submit($form);

		$this->assertContainsLang('STEP_2_TITLE_CREATE', $this->get_content());

		$form = $crawler->selectButton('Save')->form(); // Save CPF
		self::submit($form);
		$this->assertContainsLang('ADDED_PROFILE_FIELD', $this->get_content());

		$this->logout();

		// Get into registration process
		// Check that we can't skip
		self::request('GET', 'ucp.php?mode=register&agreed=1');
		$this->assertContainsLang('AGREE', $this->get_content());

		$crawler = self::request('GET', 'ucp.php?mode=register');
		$this->assertContainsLang('REGISTRATION', $crawler->filter('div.content h2')->text());

		$form = $crawler->selectButton('I agree to these terms')->form();
		$crawler = self::submit($form);

		// Check if Numbers CPF displayed on registration
		$this->assertStringContainsString('Numbers CPF', $crawler->filter('label[for="pf_numbers"]')->text());

		$form = $crawler->selectButton('Submit')->form(array(
			'username'			=> 'user-reg-test1',
			'email'				=> 'user-reg-test1@phpbb.com',
			'new_password'		=> 'user-reg-testuser-reg-test1',
			'password_confirm'	=> 'user-reg-testuser-reg-test1',
		));
		$form['tz']->select('Europe/Berlin');
		$crawler = self::submit($form);

		$this->assertContainsLang('ACCOUNT_ADDED', $crawler->filter('#message')->text());
	}
}
