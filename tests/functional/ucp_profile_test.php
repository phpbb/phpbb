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
class phpbb_functional_ucp_profile_test extends phpbb_functional_test_case
{
	public function test_submitting_profile_info()
	{
		$this->add_lang('ucp');
		$this->add_lang('memberlist');
		$this->login();

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=profile_info');
		$this->assertContainsLang('UCP_PROFILE_PROFILE_INFO', $crawler->filter('#cp-main h2')->text());

		$form = $crawler->selectButton('Submit')->form(array(
			'pf_phpbb_facebook'	=> 'phpbb',
			'pf_phpbb_location'	=> 'Bertie´s Empire',
			'pf_phpbb_skype'	=> 'phpbb.skype.account',
			'pf_phpbb_twitter'	=> 'phpbb_twitter',
			'pf_phpbb_youtube' => 'user/phpbb.youtube',
		));

		$crawler = self::submit($form);
		$this->assertContainsLang('PROFILE_UPDATED', $crawler->filter('#message')->text());

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=profile_info');
		$form = $crawler->selectButton('Submit')->form();

		$this->assertEquals('phpbb', $form->get('pf_phpbb_facebook')->getValue());
		$this->assertEquals('Bertie´s Empire', $form->get('pf_phpbb_location')->getValue());
		$this->assertEquals('phpbb.skype.account', $form->get('pf_phpbb_skype')->getValue());
		$this->assertEquals('phpbb_twitter', $form->get('pf_phpbb_twitter')->getValue());
		$this->assertEquals('user/phpbb.youtube', $form->get('pf_phpbb_youtube')->getValue());

		$crawler = self::request('GET', 'memberlist.php?mode=viewprofile&un=admin');
		$link = $crawler->selectLink($this->lang('VIEW_YOUTUBE_PROFILE'));
		$this->assertSame('https://youtube.com/user/phpbb.youtube', $link->attr('href'));
	}

	public function test_submitting_emoji()
	{
		$this->add_lang('ucp');
		$this->login();

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=profile_info');
		$this->assertContainsLang('UCP_PROFILE_PROFILE_INFO', $crawler->filter('#cp-main h2')->text());

		$form = $crawler->selectButton('Submit')->form([
			'pf_phpbb_location' => '😁', // grinning face with smiling eyes Emoji
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('PROFILE_UPDATED', $crawler->filter('#message')->text());

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=profile_info');
		$form = $crawler->selectButton('Submit')->form();
		$this->assertEquals('😁', $form->get('pf_phpbb_location')->getValue());
	}

	public function test_autologin_keys_manage()
	{
		$this->add_lang('ucp');
		$this->login('admin', true);
		$db = $this->get_db();

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=autologin_keys');
		$this->assertContainsLang('UCP_PROFILE_AUTOLOGIN_KEYS', $crawler->filter('#cp-main h2')->text());

		$profile_url = $crawler->filter('a[title="Profile"]')->attr('href');
		$user_id = $this->get_parameter_from_link($profile_url, 'u');

		$sql_ary = [
			'SELECT'	=> 'sk.key_id',
			'FROM'		=> [SESSIONS_KEYS_TABLE	=> 'sk'],
			'WHERE'		=> 'sk.user_id = ' . (int) $user_id,
			'ORDER_BY'	=> 'sk.last_login ASC',
		];
		$result = $db->sql_query_limit($db->sql_build_query('SELECT', $sql_ary), 1);
		$key_id = substr($db->sql_fetchfield('key_id'), 0, 8);
		$db->sql_freeresult($result);

		$this->assertStringContainsString($key_id, $crawler->filter('label[for="' . $key_id . '"]')->text());

		$form = $crawler->selectButton('submit')->form();
		foreach ($form['keys'] as $key)
		{
			$key->tick();
		}
		$crawler = self::submit($form);
		$this->assertStringContainsString($this->lang('AUTOLOGIN_SESSION_KEYS_DELETED'), $crawler->filter('html')->text());

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=autologin_keys');
		$this->assertStringContainsString($this->lang('PROFILE_NO_AUTOLOGIN_KEYS'), $crawler->filter('tbody > tr > td[class="bg1"]')->text());
	}
}
