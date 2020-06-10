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
		$this->login();

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=profile_info');
		$this->assertContainsLang('UCP_PROFILE_PROFILE_INFO', $crawler->filter('#cp-main h2')->text());

		$form = $crawler->selectButton('Submit')->form(array(
			'pf_phpbb_facebook'	=> 'phpbb',
			'pf_phpbb_googleplus' => 'phpbb',
			'pf_phpbb_location'	=> 'Bertie´s Empire',
			'pf_phpbb_skype'	=> 'phpbb.skype.account',
			'pf_phpbb_twitter'	=> 'phpbb_twitter',
			'pf_phpbb_youtube' => 'phpbb.youtube',
		));

		$crawler = self::submit($form);
		$this->assertContainsLang('PROFILE_UPDATED', $crawler->filter('#message')->text());

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=profile_info');
		$form = $crawler->selectButton('Submit')->form();

		$this->assertEquals('phpbb', $form->get('pf_phpbb_facebook')->getValue());
		$this->assertEquals('phpbb', $form->get('pf_phpbb_googleplus')->getValue());
		$this->assertEquals('Bertie´s Empire', $form->get('pf_phpbb_location')->getValue());
		$this->assertEquals('phpbb.skype.account', $form->get('pf_phpbb_skype')->getValue());
		$this->assertEquals('phpbb_twitter', $form->get('pf_phpbb_twitter')->getValue());
		$this->assertEquals('phpbb.youtube', $form->get('pf_phpbb_youtube')->getValue());
	}

	public function test_submitting_emoji_allowed()
	{
		$this->add_lang('ucp');
		$this->login();

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=profile_info');
		$this->assertContainsLang('UCP_PROFILE_PROFILE_INFO', $crawler->filter('#cp-main h2')->text());

		$form = $crawler->selectButton('Submit')->form([
			'pf_phpbb_location'	=> '😁', // grinning face with smiling eyes Emoji
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('PROFILE_UPDATED', $crawler->filter('#message')->text());

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=profile_info');
		$form = $crawler->selectButton('Submit')->form();
		$this->assertEquals('😁', $form->get('pf_phpbb_location')->getValue());
	}

	public function test_submitting_emoji_disallowed()
	{
		$this->add_lang(['ucp', 'acp/permissions']);
		$this->login();
		$this->admin_login();

		// Group global permissions
		$crawler = self::request('GET', 'adm/index.php?i=acp_permissions&icat=16&mode=setting_group_global&sid=' . $this->sid);
		$this->assertContainsLang('ACP_GROUPS_PERMISSIONS_EXPLAIN', $this->get_content());

		// Select Registered users group
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form(['group_id' => [2]]);
		$crawler = self::submit($form);
		$this->assertContainsLang('ACL_SET', $crawler->filter('h1')->eq(1)->text());

		// Globals for \phpbb\auth\auth
		global $db, $cache;
		$db = $this->get_db();
		$cache = new phpbb_mock_null_cache;

		$auth = new \phpbb\auth\auth;
		// Hardcoded user_id
		$user_data = $auth->obtain_user_data(2);
		$auth->acl($user_data);
		$this->assertEquals(1, $auth->acl_get('u_emoji'));

		// Set u_emoji to never
		$form = $crawler->selectButton($this->lang('APPLY_PERMISSIONS'))->form(['setting[2][0][u_emoji]' => '0']);
		$crawler = self::submit($form);
		$this->assertContainsLang('AUTH_UPDATED', $crawler->text());

		// check acl again
		$auth = new \phpbb\auth\auth;
		$user_data = $auth->obtain_user_data(2);
		$auth->acl($user_data);
		$this->assertEquals(0, $auth->acl_get('u_emoji'));

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=profile_info');
		$this->assertContainsLang('UCP_PROFILE_PROFILE_INFO', $crawler->filter('#cp-main h2')->text());

		$form = $crawler->selectButton('Submit')->form([
			'pf_phpbb_location'	=> '😁', // grinning face with smiling eyes Emoji
		]);

		$crawler = self::submit($form);
		$this->assertContains('The field “Location” has invalid characters.', $crawler->filter('p[class="error"]')->text());

		// Set u_emoji back to Yes
		$crawler = self::request('GET', 'adm/index.php?i=acp_permissions&icat=16&mode=setting_group_global&sid=' . $this->sid);
		$this->assertContainsLang('ACP_GROUPS_PERMISSIONS_EXPLAIN', $this->get_content());
		// Select Registered users group
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form(['group_id' => [2]]);
		$crawler = self::submit($form);
		$this->assertContainsLang('ACL_SET', $crawler->filter('h1')->eq(1)->text());
		// Set u_emoji to never
		$form = $crawler->selectButton($this->lang('APPLY_PERMISSIONS'))->form(["setting[2][0][u_emoji]" => '1']);
		$crawler = self::submit($form);
		$this->assertContainsLang('AUTH_UPDATED', $crawler->text());

		// check acl again
		$auth = new \phpbb\auth\auth;
		$user_data = $auth->obtain_user_data(2);
		$auth->acl($user_data);
		$this->assertEquals(1, $auth->acl_get('u_emoji'));
	}
}
