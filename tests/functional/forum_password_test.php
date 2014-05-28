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
