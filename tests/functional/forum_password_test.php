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
class phpbb_functional_forum_password_test extends phpbb_functional_test_case
{
	public function test_setup_forum_with_password()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', "adm/index.php?i=acp_forums&mode=manage&sid={$this->sid}");
		$form = $crawler->selectButton('addforum')->form(array(
			'forum_name'	=> 'Password protected',
		));
		$crawler = self::submit($form);
		$form = $crawler->selectButton('update')->form(array(
			'forum_perm_from'		=> 2,
			'forum_password'		=> 'foobar',
			'forum_password_confirm'	=> 'foobar',
		));
		$crawler = self::submit($form);
	}

	public function data_enter_forum_with_password()
	{
		return array(
			array('foowrong', 'WRONG_PASSWORD'),
			array('foobar', 'NO_TOPICS'),
		);
	}

	/**
	* @dataProvider data_enter_forum_with_password
	*/
	public function test_enter_forum_with_password($password, $message)
	{
		$crawler = self::request('GET', "index.php?sid={$this->sid}");
		preg_match('/.?f=([0-9])/', $crawler->selectLink('Password protected')->link()->getUri(), $match);
		$crawler = self::request('GET', "viewforum.php?f={$match[1]}&sid={$this->sid}");
		$form = $crawler->selectButton('login')->form(array(
			'password'	=> $password,
		));
		$crawler = self::submit($form);
		$this->assertContainsLang($message, $crawler->text());
	}
}
