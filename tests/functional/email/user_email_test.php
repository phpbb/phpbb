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
class phpbb_functional_user_email_test extends phpbb_functional_email_test_case
{
	public function test_user_registration_self_activation()
	{
		$this->configure_registration(USER_ACTIVATION_SELF);

		$username = 'emailselfact';
		$email = 'emailselfact@example.com';

		$crawler = $this->register_user($username, $email);
		$this->assertContainsLang('ACCOUNT_INACTIVE', $crawler->filter('#message')->text());
		$this->assert_email_sent($email, 'Welcome to', 'activate your account');

		$user_row = $this->get_user_row($this->get_user_id($username));
		$this->assertNotEmpty($user_row['user_actkey']);

		$this->clear_captured_emails();

		$crawler = self::request('GET', 'ucp.php?mode=activate&u=' . $user_row['user_id'] . '&k=' . $user_row['user_actkey']);
		$this->assertContainsLang('ACCOUNT_ACTIVE', $crawler->text());
	}

	public function test_user_resend_activation()
	{
		$this->configure_registration(USER_ACTIVATION_SELF);

		$username = 'emailresend';
		$email = 'emailresend@example.com';

		$crawler = $this->register_user($username, $email);
		$this->assertContainsLang('ACCOUNT_INACTIVE', $crawler->filter('#message')->text());

		$user_id = $this->get_user_id($username);
		$this->expire_activation_key($user_id);
		$this->clear_captured_emails();

		$crawler = $this->submit_resend_activation($username, $email);
		$this->assertContainsLang('ACTIVATION_EMAIL_SENT', $crawler->text());
		$this->assert_email_sent($email, 'Welcome to', 'activate your account');
	}

	public function test_coppa_registration_and_resend()
	{
		$this->configure_registration(USER_ACTIVATION_NONE, true);
		self::set_board_config([
			'coppa_fax'		=> '555-0100',
			'coppa_mail'	=> 'COPPA Department',
		]);

		$username = 'emailcoppa';
		$email = 'emailcoppa@example.com';

		$crawler = $this->register_user($username, $email, 'RegistrationPass1', true);
		$this->assertContainsLang('ACCOUNT_COPPA', $crawler->filter('#message')->text());
		$this->assert_email_sent($email, 'Welcome to', 'In compliance with the COPPA');

		$user_id = $this->get_user_id($username);
		$this->expire_activation_key($user_id);
		$this->clear_captured_emails();

		$crawler = $this->submit_resend_activation($username, $email);
		$this->assertContainsLang('ACTIVATION_EMAIL_SENT', $crawler->text());
		$this->assert_email_sent($email, 'Welcome to', 'In compliance with the COPPA');
	}

	public function test_admin_activation_flow()
	{
		$this->configure_registration(USER_ACTIVATION_ADMIN);

		$admin_id = $this->get_user_id('admin');
		$admin_email = $this->get_admin_email();
		$this->enable_email_notification($admin_id, 'notification.type.admin_activate_user');

		$username = 'emailadminact';
		$email = 'emailadminact@example.com';

		$crawler = $this->register_user($username, $email);
		$this->assertContainsLang('ACCOUNT_INACTIVE_ADMIN', $crawler->filter('#message')->text());
		$this->assert_email_sent($email, 'Welcome to', 'approved by an administrator');
		$this->assert_email_sent($admin_email, 'Activate user account', $username);

		$user_row = $this->get_user_row($this->get_user_id($username));
		$this->assertNotEmpty($user_row['user_actkey']);

		$this->clear_captured_emails();

		$this->login();
		$crawler = self::request('GET', 'ucp.php?mode=activate&u=' . $user_row['user_id'] . '&k=' . $user_row['user_actkey']);
		$this->assertContainsLang('ACCOUNT_ACTIVE_ADMIN', $crawler->text());

		$this->assert_email_sent($email, 'Account activated', $username);
	}

	public function test_email_change_self_activation()
	{
		$this->configure_registration(USER_ACTIVATION_SELF);

		$username = 'emailchangeself';
		$new_email = 'emailchangeself-new@example.com';
		$this->create_user($username, 'emailchangeself@example.com');

		$this->login($username);
		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=reg_details&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'email'			=> $new_email,
			'cur_password'	=> $username . $username,
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('ACCOUNT_EMAIL_CHANGED', $crawler->text());

		$this->assert_email_sent($new_email, 'Reactivate your account', $username);
	}

	public function test_email_change_admin_activation()
	{
		$this->configure_registration(USER_ACTIVATION_ADMIN);

		$admin_id = $this->get_user_id('admin');
		$admin_email = $this->get_admin_email();
		$this->enable_email_notification($admin_id, 'notification.type.admin_activate_user');

		$username = 'emailchangeadmin';
		$new_email = 'emailchangeadmin-new@example.com';
		$this->create_user($username, 'emailchangeadmin@example.com');

		$this->login($username);
		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=reg_details&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'email'			=> $new_email,
			'cur_password'	=> $username . $username,
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('ACCOUNT_EMAIL_CHANGED_ADMIN', $crawler->text());

		$this->assert_email_sent($new_email, 'Your account has been deactivated', $username);
		$this->assert_email_sent($admin_email, 'Activate user account', $username);
	}

	public function test_user_reactivate_account()
	{
		$username = 'emailreactivate';
		$email = 'emailreactivate@example.com';
		$user_id = $this->create_user($username, $email);
		$this->assertGreaterThan(0, $user_id);

		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=acp_users&mode=overview&u=' . $user_id . '&sid=' . $this->sid);
		$form = $crawler->filter('#user_quick_tools')->selectButton($this->lang('SUBMIT'))->form();
		$form['action']->select('reactivate');
		self::submit($form);

		$this->assert_email_sent($email, 'Reactivate your account on', $username);
	}

	public function test_user_remind_inactive()
	{
		self::set_board_config(['require_activation' => USER_ACTIVATION_NONE]);

		$username = 'emailremind';
		$email = 'emailremind@example.com';
		$user_id = $this->create_user($username, $email);
		$this->assertGreaterThan(0, $user_id);
		$this->set_user_inactive($user_id, INACTIVE_REGISTER);

		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=acp_inactive&mode=list&users_per_page=999&sid=' . $this->sid);
		$form = $crawler->filter('#inactive')->selectButton($this->lang('SUBMIT'))->form();

		$mark_index = null;
		foreach ($crawler->filter('#inactive input[name="mark[]"]') as $index => $node)
		{
			if ((int) $node->getAttribute('value') === (int) $user_id)
			{
				$mark_index = $index;
				break;
			}
		}
		$this->assertNotNull($mark_index, 'The inactive user is not listed in the ACP.');

		$marks = $form['mark'];
		$marks[$mark_index]->tick();
		$form['action']->select('remind');
		self::submit($form);

		$user_row = $this->get_user_row($user_id);
		$this->assertGreaterThan(0, (int) $user_row['user_reminded'], 'The ACP remind action did not run.');

		$this->assert_email_sent($email, 'Inactive account reminder', $username);
	}

	public function test_admin_mass_email()
	{
		$username = 'emailmass';
		$email = 'emailmass@example.com';
		$user_id = $this->create_user($username, $email);
		$this->assertGreaterThan(0, $user_id);
		$this->update_user($user_id, ['user_allow_massemail' => 1]);

		$subject = 'Mass email ' . uniqid();
		$message = 'Mass email body for functional e-mail test ' . uniqid();

		$this->login();
		$this->admin_login();
		$this->add_lang('acp/email');

		$crawler = self::request('GET', 'adm/index.php?i=acp_email&mode=email&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SEND_EMAIL'))->form([
			'usernames'	=> $username,
			'subject'	=> $subject,
			'message'	=> $message,
		]);
		self::submit($form);

		$this->assert_email_sent($email, $subject, $message);
	}

	public function test_contact_admin_email()
	{
		$this->add_lang('memberlist');
		self::set_board_config(['contact_admin_form_enable' => 1]);

		$board_contact = self::get_board_config(['board_contact'])['board_contact'];
		$subject = 'Contact admin ' . uniqid();
		$message = 'Contact admin body for functional e-mail test ' . uniqid();

		$crawler = self::request('GET', 'memberlist.php?mode=contactadmin');
		$form = $crawler->selectButton($this->lang('SEND_EMAIL'))->form([
			'name'		=> 'Functional Test Sender',
			'email'		=> 'contactsender@example.com',
			'subject'	=> $subject,
			'message'	=> $message,
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('EMAIL_SENT', $crawler->text());

		$this->assert_email_sent($board_contact, $subject, $message);
	}

	public function test_profile_send_email()
	{
		$this->add_lang('memberlist');
		self::set_board_config(['board_email_form' => 1]);

		$sender_username = 'emailprofilesend';
		$recipient_username = 'emailprofilerecv';
		$recipient_email = 'emailprofilerecv@example.com';

		$this->create_user($sender_username, 'emailprofilesend@example.com');
		$recipient_id = $this->create_user($recipient_username, $recipient_email);
		$this->assertGreaterThan(0, $recipient_id);
		$this->update_user($recipient_id, ['user_allow_viewemail' => 1]);

		$subject = 'Profile email ' . uniqid();
		$message = 'Profile email body for functional e-mail test ' . uniqid();

		$this->login($sender_username);
		$crawler = self::request('GET', 'memberlist.php?mode=email&u=' . $recipient_id . '&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SEND_EMAIL'))->form([
			'subject'	=> $subject,
			'message'	=> $message,
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('EMAIL_SENT', $crawler->text());

		$this->assert_email_sent($recipient_email, $subject, $sender_username);
	}

	public function test_installed_email()
	{
		$admin_email = $this->get_admin_email();

		$this->clear_captured_emails();

		// The functional test installer suppresses the installer notification,
		// so trigger the real email method with the installer template instead.
		self::request('GET', 'index.php/email-catcher/trigger?type=installed', [], false);

		$this->assert_email_sent($admin_email, 'phpBB installed', 'successfully installed');
	}

	public function test_email_topic_to_friend()
	{
		$this->add_lang('memberlist');

		$poster_username = 'emailtopicposter';
		$this->create_user($poster_username, 'emailtopicposter@example.com');
		$this->create_user('emailtopicsender', 'emailtopicsender@example.com');

		$topic_title = 'Email topic to friend ' . uniqid();

		$this->login($poster_username);
		$topic = $this->create_topic(2, $topic_title, 'Topic body for email-to-friend test');
		$this->logout();

		$friend_email = 'emailfriend@example.com';
		$message = 'Message for the friend in functional e-mail test';

		$this->login('emailtopicsender');
		$crawler = self::request('GET', 'memberlist.php?mode=email&t=' . $topic['topic_id'] . '&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SEND_EMAIL'))->form([
			'email'		=> $friend_email,
			'name'		=> 'Email Friend',
			'message'	=> $message,
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('EMAIL_SENT', $crawler->text());

		$this->assert_email_sent($friend_email, 'Email a friend', $topic_title);
	}
}
