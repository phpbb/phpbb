<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @group functional
*/
class phpbb_functional_user_password_reset_test extends phpbb_functional_test_case
{
	public function test_password_reset()
	{
		$this->add_lang('ucp');
		$this->create_user('reset-password-test-user');

		$crawler = self::request('GET', "ucp.php?mode=sendpassword&sid={$this->sid}");
		$form = $crawler->selectButton('submit')->form(array(
			'username'	=> 'reset-password-test-user',
		));
		$crawler = self::submit($form);
		$this->assertContainsLang('NO_EMAIL_USER', $crawler->text());

		$crawler = self::request('GET', "ucp.php?mode=sendpassword&sid={$this->sid}");
		$form = $crawler->selectButton('submit')->form(array(
			'username'	=> 'reset-password-test-user',
			'email'		=> 'nobody@example.com',
		));
		$crawler = self::submit($form);
		$this->assertContainsLang('PASSWORD_UPDATED', $crawler->text());
	}
}
