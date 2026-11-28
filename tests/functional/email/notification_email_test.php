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
class phpbb_functional_notification_email_test extends phpbb_functional_email_test_case
{
	public function test_acp_test_email()
	{
		$this->add_lang(['acp/common', 'acp/board']);
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=email&sid=' . $this->sid);
		$this->assertContainsLang('ACP_EMAIL_SETTINGS_EXPLAIN', $this->get_content());

		$sql = 'SELECT user_email
			FROM ' . USERS_TABLE . "
			WHERE username_clean = 'admin'";
		$result = $this->db->sql_query($sql);
		$admin_email = (string) $this->db->sql_fetchfield('user_email');
		$this->db->sql_freeresult($result);

		$body = 'Functional email capture test ' . uniqid();

		$form = $crawler->selectButton($this->lang('SEND_TEST_EMAIL'))->form([
			'send_test_email_text' => $body,
		]);
		$crawler = self::submit($form);

		$this->assertGreaterThan(0, $crawler->filter('div.successbox')->count());

		$email = $this->assert_email_sent($admin_email, '', $body);
		$this->assertStringContainsString('X-Mailer: phpBB', $email['headers']);
	}

	public function test_topic_reply_email_notification()
	{
		$author_id = $this->create_user('emailauthor', 'emailauthor@example.com');
		$this->create_user('emailreplier', 'emailreplier@example.com');
		$this->assertGreaterThan(0, $author_id);

		$this->enable_email_notification($author_id, 'notification.type.post');

		$topic_title = 'Email capture topic ' . uniqid();

		$this->login('emailauthor');
		$topic = $this->create_topic(2, $topic_title, 'Original post body', ['notify' => 1]);
		$this->logout();

		$this->clear_captured_emails();

		$this->login('emailreplier');
		$this->create_post(2, $topic['topic_id'], 'Re: ' . $topic_title, 'Reply body for email capture');
		$this->logout();

		$this->assert_email_sent('emailauthor@example.com', $topic_title, 'emailreplier');
	}

	public function test_private_message_email_notification()
	{
		$recipient_id = $this->create_user('emailpmrcpt', 'emailpmrcpt@example.com');
		$this->create_user('emailpmsend', 'emailpmsend@example.com');
		$this->assertGreaterThan(0, $recipient_id);

		$this->enable_email_notification($recipient_id, 'notification.type.pm');

		$pm_subject = 'Email capture PM ' . uniqid();

		$this->login('emailpmsend');
		$this->create_private_message($pm_subject, 'Private message body for email capture', [$recipient_id]);
		$this->logout();

		$this->assert_email_sent('emailpmrcpt@example.com', 'New private message', $pm_subject);
	}

	public function test_password_reset_email_notification()
	{
		$this->create_user('emailreset', 'emailreset@example.com');
		$this->add_lang('ucp');

		$crawler = self::request('GET', 'index.php/user/forgot_password');
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'email' => 'emailreset@example.com',
		]);
		$crawler = self::submit($form);

		$this->assertContainsLang('PASSWORD_RESET_LINK_SENT', $crawler->text());
		$this->assert_email_sent('emailreset@example.com', 'Account password reset', 'emailreset');
	}

	public function test_registration_welcome_email_notification()
	{
		$this->configure_registration(USER_ACTIVATION_NONE);

		$crawler = $this->register_user('emailreguser', 'emailreguser@example.com');

		$this->assertContainsLang('ACCOUNT_ADDED', $crawler->filter('#message')->text());
		$this->assert_email_sent('emailreguser@example.com', 'Welcome to', 'emailreguser');

		$this->logout();
	}
}
