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
class phpbb_functional_email_transport_test extends phpbb_functional_email_test_case
{
	public function test_test_email_transport_mime()
	{
		$this->add_lang(['acp/common', 'acp/board']);
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=email&sid=' . $this->sid);
		$body = 'Transport MIME test ' . uniqid();

		$form = $crawler->selectButton($this->lang('SEND_TEST_EMAIL'))->form([
			'send_test_email_text' => $body,
		]);
		self::submit($form);

		$admin_email = $this->get_admin_email();
		$sent = $this->assert_sent_email($admin_email, '', $body);
		$this->assert_email_mime($sent, $admin_email, $body);
	}

	public function test_notification_transport_mime()
	{
		$author_id = $this->create_user('transportauthor', 'transportauthor@example.com');
		$this->create_user('transportreplier', 'transportreplier@example.com');
		$this->assertGreaterThan(0, $author_id);

		$this->enable_email_notification($author_id, 'notification.type.post');

		$topic_title = 'Transport notification topic ' . uniqid();

		$this->login('transportauthor');
		$topic = $this->create_topic(2, $topic_title, 'Original transport test post', ['notify' => 1]);
		$this->logout();

		$this->clear_captured_emails();

		$this->login('transportreplier');
		$this->create_post(2, $topic['topic_id'], 'Re: ' . $topic_title, 'Transport notification reply');
		$this->logout();

		$sent = $this->assert_sent_email('transportauthor@example.com', $topic_title, 'transportreplier');
		$this->assert_email_mime($sent, 'transportauthor@example.com', 'transportreplier', $topic_title);
		$this->assertStringContainsString('List-Unsubscribe:', $sent['raw']);
	}

	public function test_registration_transport_mime()
	{
		$this->configure_registration(USER_ACTIVATION_NONE);

		$username = 'transportreg';
		$email = 'transportreg@example.com';

		$this->register_user($username, $email);

		$sent = $this->assert_sent_email($email, 'Welcome to', $username);
		$this->assert_email_mime($sent, $email, $username, 'Welcome to');
	}
}
