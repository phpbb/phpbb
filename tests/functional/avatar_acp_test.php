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
class phpbb_functional_avatar_acp_test extends phpbb_functional_test_case
{
	private $path;
	private $form_content;

	public function setUp()
	{
		parent::setUp();
		$this->path = __DIR__ . '/fixtures/files/';
		$this->login();
		$this->admin_login();
		$this->add_lang(array('acp/board', 'ucp', 'acp/users', 'acp/groups'));
	}

	public function test_acp_settings()
	{
		$crawler = $this->request('GET', 'adm/index.php?i=acp_board&mode=avatar&sid=' . $this->sid);
		$this->assert_response_success();
		// Check the default entries we should have
		$this->assertContainsLang('ALLOW_GRAVATAR', $crawler->text());
		$this->assertContainsLang('ALLOW_REMOTE', $crawler->text());
		$this->assertContainsLang('ALLOW_AVATARS', $crawler->text());
		$this->assertContainsLang('ALLOW_LOCAL', $crawler->text());

		// Now start setting the needed settings
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['config[allow_avatar_local]']->select(1);
		$form['config[allow_avatar_gravatar]']->select(1);
		$form['config[allow_avatar_remote]']->select(1);
		$form['config[allow_avatar_remote_upload]']->select(1);
		$crawler = $this->client->submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->text());
	}

	public function test_user_acp_settings()
	{
		$crawler = $this->request('GET', 'adm/index.php?i=users&u=2&sid=' . $this->sid);
		$this->assert_response_success();

		// Select "Avatar" in the drop-down menu
		$form = $crawler->selectButton($this->lang('GO'))->form();
		$form['mode']->select('avatar');
		$crawler = $this->client->submit($form);
		$this->assertContainsLang('AVATAR_TYPE', $crawler->text());

		// Test if setting a gravatar avatar properly works
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_gravatar');
		$form['avatar_gravatar_email']->setValue('test@example.com');
		$form['avatar_gravatar_width']->setValue(80);
		$form['avatar_gravatar_height']->setValue(80);
		$crawler = $this->client->submit($form);
		$this->assertContainsLang('USER_AVATAR_UPDATED', $crawler->text());

		// Go back to previous page
		$crawler = $this->request('GET', 'adm/index.php?i=users&u=2&sid=' . $this->sid);
		$this->assert_response_success();

		// Select "Avatar" in the drop-down menu
		$form = $crawler->selectButton($this->lang('GO'))->form();
		$form['mode']->select('avatar');
		$crawler = $this->client->submit($form);
		$this->assertContainsLang('AVATAR_TYPE', $crawler->text());

		// Test uploading a remote avatar
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_upload');
		// use default gravatar supplied by test@example.com and default size = 80px
		$form['avatar_upload_url']->setValue('https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0.jpg');
		$crawler = $this->client->submit($form);
		$this->assertContainsLang('USER_AVATAR_UPDATED', $crawler->text());

		// Go back to previous page
		$crawler = $this->request('GET', 'adm/index.php?i=users&u=2&sid=' . $this->sid);
		$this->assert_response_success();

		// Select "Avatar" in the drop-down menu
		$form = $crawler->selectButton($this->lang('GO'))->form();
		$form['mode']->select('avatar');
		$crawler = $this->client->submit($form);
		$this->assertContainsLang('AVATAR_TYPE', $crawler->text());

		// Submit gravatar with incorrect email and correct size
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_gravatar');
		$form['avatar_gravatar_email']->setValue('test.example.com');
		$form['avatar_gravatar_width']->setValue(80);
		$form['avatar_gravatar_height']->setValue(80);
		$crawler = $this->client->submit($form);
		$this->assertContainsLang('EMAIL_INVALID_EMAIL', $crawler->text());
	}

	public function test_group_acp_settings()
	{
		// Test setting group avatar of admin group
		$crawler = $this->request('GET', 'adm/index.php?i=acp_groups&mode=manage&action=edit&g=5&sid=' . $this->sid);
		$this->assert_response_success();
		$this->assertContainsLang('AVATAR_TYPE', $crawler->text());

		// Test if setting a gravatar avatar properly works
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_gravatar');
		$form['avatar_gravatar_email']->setValue('test@example.com');
		$form['avatar_gravatar_width']->setValue(80);
		$form['avatar_gravatar_height']->setValue(80);
		$crawler = $this->client->submit($form);
		$this->assertContainsLang('GROUP_UPDATED', $crawler->text());

		// Go back to previous page
		$crawler = $this->request('GET', 'adm/index.php?i=acp_groups&mode=manage&action=edit&g=5&sid=' . $this->sid);
		$this->assert_response_success();

		// Test uploading a remote avatar
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_upload');
		// use default gravatar supplied by test@example.com and default size = 80px
		$form['avatar_upload_url']->setValue('https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0.jpg');
		$crawler = $this->client->submit($form);
		$this->assertContainsLang('GROUP_UPDATED', $crawler->text());

		// Go back to previous page
		$crawler = $this->request('GET', 'adm/index.php?i=acp_groups&mode=manage&action=edit&g=5&sid=' . $this->sid);
		$this->assert_response_success();

		// Submit gravatar with incorrect email and correct size
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_gravatar');
		$form['avatar_gravatar_email']->setValue('test.example.com');
		$form['avatar_gravatar_width']->setValue(80);
		$form['avatar_gravatar_height']->setValue(80);
		$crawler = $this->client->submit($form);
		$this->assertContainsLang('EMAIL_INVALID_EMAIL', $crawler->text());
	}
}
