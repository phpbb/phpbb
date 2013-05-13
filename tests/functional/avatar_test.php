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
class phpbb_functional_avatar_test extends phpbb_functional_test_case
{
	private $path;
	private $form_content;

	public function setUp()
	{
		parent::setUp();
		$this->path = __DIR__ . '/fixtures/files/';
		$this->login();
		$this->admin_login();
		$this->add_lang(array('acp/board', 'ucp', 'acp/groups'));
	}

	public function test_acp_settings()
	{
		$crawler = $this->request('GET', 'adm/index.php?i=acp_board&mode=avatar&sid=' . $this->sid);
		$this->assert_response_success();
		// Check the default entries we should have
		$this->assertContains($this->lang('ALLOW_GRAVATAR'), $crawler->text());
		$this->assertContains($this->lang('ALLOW_REMOTE'), $crawler->text());
		$this->assertContains($this->lang('ALLOW_AVATARS'), $crawler->text());
		$this->assertContains($this->lang('ALLOW_LOCAL'), $crawler->text());

		// Now start setting the needed settings
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['config[allow_avatar_local]']->select(1);
		$form['config[allow_avatar_gravatar]']->select(1);
		$form['config[allow_avatar_remote]']->select(1);
		$form['config[allow_avatar_remote_upload]']->select(1);
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang('CONFIG_UPDATED'), $crawler->text());
	}

	public function test_gravatar_avatar()
	{
		// Get ACP settings
		$crawler = $this->request('GET', 'adm/index.php?i=acp_board&mode=avatar&sid=' . $this->sid);
		$this->assert_response_success();
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$this->form_content = $form->getValues();

		// Check if required form elements exist
		$crawler = $this->request('GET', 'ucp.php?i=ucp_profile&mode=avatar&sid=' . $this->sid);
		$this->assert_response_success();
		$this->assertContains($this->lang('AVATAR_TYPE'), $crawler->text());
		$this->assertContains($this->lang('AVATAR_DRIVER_GRAVATAR_TITLE'), $crawler->filter('#avatar_driver')->text());
		$this->assertContains($this->lang('GRAVATAR_AVATAR_EMAIL'), $crawler->text());

		// Submit gravatar with correct email and correct size
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_gravatar');
		$form['avatar_gravatar_email']->setValue('test@example.com');
		$form['avatar_gravatar_width']->setValue(80);
		$form['avatar_gravatar_height']->setValue(80);
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang('PROFILE_UPDATED'), $crawler->text());

		// Submit gravatar with correct mail but incorrect size
		$crawler = $this->request('GET', 'ucp.php?i=ucp_profile&mode=avatar&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_gravatar');
		$form['avatar_gravatar_email']->setValue('test@example.com');
		$form['avatar_gravatar_width']->setValue(120);
		$form['avatar_gravatar_height']->setValue(120);
		$crawler = $this->client->submit($form);
		$this->assertContains(sprintf($this->lang['AVATAR_WRONG_SIZE'],
			$this->form_content['config[avatar_min_width]'],
			$this->form_content['config[avatar_min_height]'],
			$this->form_content['config[avatar_max_width]'],
			$this->form_content['config[avatar_max_height]'],
			'120',
			'120'
		), $crawler->text());

		// Submit gravatar with incorrect email and correct size
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_gravatar');
		$form['avatar_gravatar_email']->setValue('test.example.com');
		$form['avatar_gravatar_width']->setValue(80);
		$form['avatar_gravatar_height']->setValue(80);
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang('EMAIL_INVALID_EMAIL'), $crawler->text());
	}

	public function test_upload_avatar()
	{
		// Check if required form elements exist
		$crawler = $this->request('GET', 'ucp.php?i=ucp_profile&mode=avatar&sid=' . $this->sid);
		$this->assert_response_success();
		$this->assertContains($this->lang('AVATAR_DRIVER_UPLOAD_TITLE'), $crawler->filter('#avatar_driver')->text());
		$this->assertContains($this->lang('UPLOAD_AVATAR_FILE'), $crawler->text());
		$this->assertContains($this->lang('UPLOAD_AVATAR_URL'), $crawler->text());

		// Upload remote avatar with correct size and correct link
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_upload');
		// use default gravatar supplied by test@example.com and default size = 80px
		$form['avatar_upload_url']->setValue('https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0.jpg');
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang('PROFILE_UPDATED'), $crawler->text());

		// This will fail as the upload avatar currently expects a file that ends with an extension, e.g. .jpg
		$crawler = $this->request('GET', 'ucp.php?i=ucp_profile&mode=avatar&sid=' . $this->sid);
		$this->assert_response_success();
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_upload');
		// use default gravatar supplied by test@example.com and size (s) = 80px
		$form['avatar_upload_url']->setValue('https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0?s=80');
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang('AVATAR_URL_INVALID'), $crawler->text());

		// Submit gravatar with correct email and correct size
		$crawler = $this->request('GET', 'ucp.php?i=ucp_profile&mode=avatar&sid=' . $this->sid);
		$this->assert_response_success();
		$this->markTestIncomplete('Test fails due to bug in DomCrawler with Symfony < 2.2: https://github.com/symfony/symfony/issues/4674.');
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_upload');
		$form['avatar_upload_file']->setValue($this->path . 'valid.jpg');
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang('PROFILE_UPDATED'), $crawler->text());
	}

	public function test_remote_avatar()
	{
		// Get ACP settings
		$crawler = $this->request('GET', 'adm/index.php?i=acp_board&mode=avatar&sid=' . $this->sid);
		$this->assert_response_success();
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$this->form_content = $form->getValues();

		// Check if required form elements exist
		$crawler = $this->request('GET', 'ucp.php?i=ucp_profile&mode=avatar&sid=' . $this->sid);
		$this->assert_response_success();
		$this->assertContains($this->lang('AVATAR_DRIVER_REMOTE_TITLE'), $crawler->filter('#avatar_driver')->text());
		$this->assertContains($this->lang('LINK_REMOTE_AVATAR'), $crawler->text());
		$this->assertContains($this->lang('LINK_REMOTE_SIZE'), $crawler->text());

		// Set remote avatar with correct size and correct link
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_remote');
		// use default gravatar supplied by test@example.com and default size = 80px
		$form['avatar_remote_url']->setValue('https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0.jpg');
		$form['avatar_remote_width']->setValue(80);
		$form['avatar_remote_height']->setValue(80);
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang('PROFILE_UPDATED'), $crawler->text());

		// Set remote avatar with incorrect size
		$crawler = $this->request('GET', 'ucp.php?i=ucp_profile&mode=avatar&sid=' . $this->sid);
		$this->assert_response_success();
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_remote');
		// use default gravatar supplied by test@example.com and size (s) = 80px
		$form['avatar_remote_url']->setValue('https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0.jpg');
		$form['avatar_remote_width']->setValue(120);
		$form['avatar_remote_height']->setValue(120);
		$crawler = $this->client->submit($form);
		$this->assertContains(sprintf($this->lang['AVATAR_WRONG_SIZE'],
			$this->form_content['config[avatar_min_width]'],
			$this->form_content['config[avatar_min_height]'],
			$this->form_content['config[avatar_max_width]'],
			$this->form_content['config[avatar_max_height]'],
			'120',
			'120'
		), $crawler->text());

		// Enter correct data in form entries but select incorrect avatar driver
		$crawler = $this->request('GET', 'ucp.php?i=ucp_profile&mode=avatar&sid=' . $this->sid);
		$this->assert_response_success();
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_upload');
		// use default gravatar supplied by test@example.com and size (s) = 80px
		$form['avatar_remote_url']->setValue('https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0.jpg');
		$form['avatar_remote_width']->setValue(80);
		$form['avatar_remote_height']->setValue(80);
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang('NO_AVATAR_SELECTED'), $crawler->text());

		/*
		 * Enter incorrect link to a remote avatar_driver
		 * Due to the fact that this link to phpbb.com will not serve a 404 error but rather a 404 page,
		 * the remote avatar will think that this is a properly working avatar. This Bug also exists in
		 * the current phpBB 3.0.11 release.
		 */
		$crawler = $this->request('GET', 'ucp.php?i=ucp_profile&mode=avatar&sid=' . $this->sid);
		$this->assert_response_success();
		$this->markTestIncomplete('Test currently fails because the remote avatar does not seem to check if it is an image');
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_remote');
		// use random incorrect link to phpBB.com
		$form['avatar_remote_url']->setValue('https://www.phpbb.com/avatar/55502f40dc8b7c769880b10874abc9d0.jpg');
		$form['avatar_remote_width']->setValue(80);
		$form['avatar_remote_height']->setValue(80);
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang('NO_AVATAR_SELECTED'), $crawler->text());
	}


	public function test_group_ucp_settings()
	{
		// Test setting group avatar of admin group
		$crawler = $this->request('GET', 'ucp.php?i=ucp_groups&mode=manage&action=edit&g=5&sid=' . $this->sid);
		$this->assert_response_success();
		$this->assertContains($this->lang('AVATAR_TYPE'), $crawler->text());

		// Test if setting a gravatar avatar properly works
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_gravatar');
		$form['avatar_gravatar_email']->setValue('test@example.com');
		$form['avatar_gravatar_width']->setValue(80);
		$form['avatar_gravatar_height']->setValue(80);
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang('GROUP_UPDATED'), $crawler->text());

		// Go back to previous page
		$crawler = $this->request('GET', 'ucp.php?i=ucp_groups&mode=manage&action=edit&g=5&sid=' . $this->sid);
		$this->assert_response_success();

		// Test uploading a remote avatar
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_upload');
		// use default gravatar supplied by test@example.com and default size = 80px
		$form['avatar_upload_url']->setValue('https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0.jpg');
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang('GROUP_UPDATED'), $crawler->text());

		// Go back to previous page
		$crawler = $this->request('GET', 'ucp.php?i=ucp_groups&mode=manage&action=edit&g=5&sid=' . $this->sid);
		$this->assert_response_success();

		// Submit gravatar with incorrect email and correct size
		$this->markTestIncomplete('No error when submitting incorrect ucp group settings. This needs to be fixed ASAP.');
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['avatar_driver']->select('avatar_driver_gravatar');
		$form['avatar_gravatar_email']->setValue('test.example.com');
		$form['avatar_gravatar_width']->setValue(80);
		$form['avatar_gravatar_height']->setValue(80);
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang('EMAIL_INVALID_EMAIL'), $crawler->text());
	}
}
