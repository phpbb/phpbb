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
 * Base class for functional tests that need to inspect outgoing emails.
 *
 * Emails are captured by the test/email_catcher extension. The extension
 * writes one JSON object per generated email to store/email_catcher.jsonl and
 * one JSON object per message accepted by the transport to
 * store/email_catcher_sent.jsonl. This allows the PHPUnit process to inspect
 * both the phpBB-generated message and the prepared MIME message that the
 * web server process passed to Symfony Mailer.
 */
abstract class phpbb_functional_email_test_case extends phpbb_functional_test_case
{
	/** @var phpbb_test_case_helpers|null */
	protected static $email_test_helpers = null;

	/** @var array */
	protected static $fixtures = [
		'test/email_catcher',
	];

	/** @var array */
	protected static $original_email_config = [];

	/** @var array User ID => username created by this test case */
	protected static $created_users = [];

	/**
	 * {@inheritdoc}
	 */
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		self::$email_test_helpers = new phpbb_test_case_helpers(__CLASS__);
		self::$email_test_helpers->copy_ext_fixtures(__DIR__ . '/../functional/fixtures/ext/', static::$fixtures);

		self::$original_email_config = self::get_board_config([
			'email_enable',
			'smtp_delivery',
			'smtp_host',
			'email_package_size',
			'email_check_mx',
			'enable_confirm',
			'require_activation',
			'coppa_enable',
			'coppa_fax',
			'coppa_mail',
			'pass_complex',
			'board_email_form',
			'contact_admin_form_enable',
			'flood_interval',
			'allow_mentions',
			'allow_topic_notify',
			'allow_privmsg',
			'allow_password_reset',
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public static function tearDownAfterClass(): void
	{
		if (!empty(self::$original_email_config))
		{
			self::set_board_config(self::$original_email_config);
		}

		self::delete_test_users(array_keys(self::$created_users));
		self::$created_users = [];

		if (self::$email_test_helpers instanceof phpbb_test_case_helpers)
		{
			try
			{
				foreach (static::setup_extensions() as $extension)
				{
					if (self::is_extension_enabled($extension))
					{
						self::uninstall_ext($extension);
					}
				}
			}
			finally
			{
				self::$email_test_helpers->restore_original_ext_dir();
			}
		}

		@unlink(self::get_email_catcher_file());
		@unlink(self::get_sent_email_catcher_file());
		@unlink(self::get_failed_email_catcher_file());

		parent::tearDownAfterClass();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		self::set_board_config([
			'email_enable'			=> 1,
			'smtp_delivery'			=> 1,
			'smtp_host'				=> '',
			'email_package_size'	=> 0,
			'flood_interval'		=> 0,
			'allow_topic_notify'	=> 1,
			'allow_privmsg'			=> 1,
			'allow_password_reset'	=> 1,
		]);

		$this->clear_captured_emails();
	}

	/**
	 * {@inheritdoc}
	 */
	protected static function setup_extensions()
	{
		return ['test/email_catcher'];
	}

	/**
	 * Get the path to the generated email capture file.
	 *
	 * @return string
	 */
	protected static function get_email_catcher_file(): string
	{
		global $phpbb_root_path;

		return $phpbb_root_path . 'store/email_catcher.jsonl';
	}

	/**
	 * Get the path to the sent email capture file.
	 *
	 * @return string
	 */
	protected static function get_sent_email_catcher_file(): string
	{
		global $phpbb_root_path;

		return $phpbb_root_path . 'store/email_catcher_sent.jsonl';
	}

	/**
	 * Get the path to the failed email capture file.
	 *
	 * @return string
	 */
	protected static function get_failed_email_catcher_file(): string
	{
		global $phpbb_root_path;

		return $phpbb_root_path . 'store/email_catcher_failed.jsonl';
	}

	/**
	 * Remove all previously captured emails.
	 *
	 * @return void
	 */
	protected function clear_captured_emails(): void
	{
		@unlink(self::get_email_catcher_file());
		@unlink(self::get_sent_email_catcher_file());
		@unlink(self::get_failed_email_catcher_file());
	}

	/**
	 * Get all generated emails.
	 *
	 * @return array
	 */
	protected function get_captured_emails(): array
	{
		return $this->read_email_file(self::get_email_catcher_file());
	}

	/**
	 * Get all emails accepted by the transport.
	 *
	 * @return array
	 */
	protected function get_sent_emails(): array
	{
		return $this->read_email_file(self::get_sent_email_catcher_file());
	}

	/**
	 * Get all emails rejected by the transport.
	 *
	 * @return array
	 */
	protected function get_failed_emails(): array
	{
		return $this->read_email_file(self::get_failed_email_catcher_file());
	}

	/**
	 * Read a JSONL email capture file.
	 *
	 * @param string $file File path
	 * @return array
	 */
	protected function read_email_file(string $file): array
	{
		clearstatcache(true, $file);

		if (!file_exists($file))
		{
			return [];
		}

		$emails = [];
		$lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		if ($lines === false)
		{
			return [];
		}

		foreach ($lines as $line)
		{
			$email = json_decode($line, true);

			if (is_array($email))
			{
				$emails[] = $email;
			}
		}

		return $emails;
	}

	/**
	 * Find a captured email.
	 *
	 * @param string $to					Recipient address
	 * @param string $subject_contains		Optional subject substring
	 * @param string $body_contains			Optional text body substring
	 * @return array|null
	 */
	protected function find_email(string $to, string $subject_contains = '', string $body_contains = ''): ?array
	{
		foreach ($this->get_captured_emails() as $email)
		{
			$recipients = array_column($email['to'] ?? [], 'address');

			if (!in_array($to, $recipients, true))
			{
				continue;
			}

			if ($subject_contains !== '' && !str_contains((string) ($email['subject'] ?? ''), $subject_contains))
			{
				continue;
			}

			if ($body_contains !== '' && !str_contains((string) ($email['text'] ?? ''), $body_contains))
			{
				continue;
			}

			return $email;
		}

		return null;
	}

	/**
	 * Find an email accepted by the transport.
	 *
	 * @param string $to					Recipient address
	 * @param string $subject_contains		Optional subject substring
	 * @param string $body_contains			Optional text body substring
	 * @return array|null
	 */
	protected function find_sent_email(string $to, string $subject_contains = '', string $body_contains = ''): ?array
	{
		foreach ($this->get_sent_emails() as $email)
		{
			$recipients = array_column($email['envelope_to'] ?? [], 'address');

			if (!in_array($to, $recipients, true))
			{
				continue;
			}

			if ($subject_contains !== '' && !str_contains((string) ($email['subject'] ?? ''), $subject_contains))
			{
				continue;
			}

			if ($body_contains !== '' && !str_contains((string) ($email['text'] ?? ''), $body_contains))
			{
				continue;
			}

			return $email;
		}

		return null;
	}

	/**
	 * Assert that an email was accepted by the transport.
	 *
	 * @param string $to					Recipient address
	 * @param string $subject_contains		Optional subject substring
	 * @param string $body_contains			Optional text body substring
	 * @return array Sent email
	 */
	protected function assert_sent_email(string $to, string $subject_contains = '', string $body_contains = ''): array
	{
		$email = $this->find_sent_email($to, $subject_contains, $body_contains);

		if ($email === null)
		{
			$this->fail(sprintf(
				'No sent email to "%s" matching subject "%s" and body "%s". Sent emails: %s. Failed emails: %s',
				$to,
				$subject_contains,
				$body_contains,
				json_encode($this->get_sent_emails(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
				json_encode($this->get_failed_emails(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
			));
		}

		$this->assert_common_sent_mime($email);

		return $email;
	}

	/**
	 * Assert headers and encoding shared by all phpBB emails.
	 *
	 * @param array $sent Sent email data
	 * @return void
	 */
	protected function assert_common_sent_mime(array $sent): void
	{
		$this->assertNotEmpty($sent['envelope_from']['address'] ?? '', 'The transport envelope has no sender.');
		$this->assertNotEmpty($sent['envelope_to'] ?? [], 'The transport envelope has no recipients.');
		$this->assertNotEmpty($sent['message_id'] ?? '', 'The sent message has no transport-level message ID.');

		$raw = (string) ($sent['raw'] ?? '');

		$this->assertStringContainsString('MIME-Version: 1.0', $raw);
		$this->assertMatchesRegularExpression('/^Date: .+/m', $raw);
		$this->assertMatchesRegularExpression('/^Message-ID: <.+>/m', $raw);
		$this->assertMatchesRegularExpression('/^From: .+/m', $raw);
		$this->assertMatchesRegularExpression('/^To: .+/m', $raw);
		$this->assertMatchesRegularExpression('/^Subject: .+/m', $raw);
		$this->assertStringContainsString('Return-Path:', $raw);
		$this->assertStringContainsString('Sender:', $raw);
		$this->assertStringContainsString('X-Mailer: phpBB', $raw);
		$this->assertStringContainsString('Content-Type: text/plain; charset=utf-8', $raw);
		$this->assertStringContainsString('Content-Transfer-Encoding: quoted-printable', $raw);
	}

	/**
	 * Assert that an email was generated and accepted by the transport.
	 *
	 * @param string $to					Recipient address
	 * @param string $subject_contains		Optional subject substring
	 * @param string $body_contains			Optional text body substring
	 * @return array Generated email
	 */
	protected function assert_email_sent(string $to, string $subject_contains = '', string $body_contains = ''): array
	{
		$email = $this->find_email($to, $subject_contains, $body_contains);

		if ($email === null)
		{
			$this->fail(sprintf(
				'No captured email to "%s" matching subject "%s" and body "%s". Captured emails: %s',
				$to,
				$subject_contains,
				$body_contains,
				json_encode($this->get_captured_emails(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
			));
		}

		$this->assert_sent_email($to, $subject_contains, $body_contains);

		return $email;
	}

	/**
	 * Assert that the transport did not reject any email.
	 *
	 * @return void
	 */
	protected function assert_no_failed_emails(): void
	{
		$this->assertSame([], $this->get_failed_emails(), 'The transport rejected one or more emails.');
	}

	/**
	 * Assert the prepared MIME message of a sent email.
	 *
	 * @param array $sent				Sent email data
	 * @param string $to				Expected envelope recipient
	 * @param string $body_contains		Expected decoded body substring
	 * @param string $subject_contains	Optional expected subject substring
	 * @return void
	 */
	protected function assert_email_mime(array $sent, string $to, string $body_contains, string $subject_contains = ''): void
	{
		$recipients = array_column($sent['envelope_to'] ?? [], 'address');
		$this->assertContains($to, $recipients, 'The transport envelope does not contain the expected recipient.');
		$this->assert_common_sent_mime($sent);

		$raw = (string) ($sent['raw'] ?? '');

		if ($subject_contains !== '')
		{
			$this->assertStringContainsString($subject_contains, (string) ($sent['subject'] ?? ''));
		}

		$this->assertStringContainsString($body_contains, (string) ($sent['text'] ?? ''));
		$this->assertStringContainsString($body_contains, quoted_printable_decode($raw));

		$this->assert_no_failed_emails();
	}

	/**
	 * Assert that no email was sent.
	 *
	 * @param string $to				Recipient address
	 * @param string $subject_contains	Optional subject substring
	 * @return void
	 */
	protected function assert_email_not_sent(string $to, string $subject_contains = ''): void
	{
		$this->assertNull(
			$this->find_email($to, $subject_contains),
			sprintf(
				'Unexpected email to "%s" matching subject "%s". Captured emails: %s',
				$to,
				$subject_contains,
				json_encode($this->get_captured_emails(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
			)
		);
	}

	/**
	 * Enable the email notification method for a notification type.
	 *
	 * @param int $user_id	User ID
	 * @param string $type	Notification type, e.g. notification.type.post
	 * @return void
	 */
	protected function enable_email_notification(int $user_id, string $type): void
	{
		$db = self::get_db();
		$method = 'notification.method.email';

		$sql = 'SELECT notify
			FROM ' . USER_NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . $db->sql_escape($type) . "'
				AND item_id = 0
				AND user_id = " . (int) $user_id . "
				AND method = '" . $db->sql_escape($method) . "'";
		$result = $db->sql_query($sql);
		$notify = $db->sql_fetchfield('notify');
		$db->sql_freeresult($result);

		if ($notify === false)
		{
			$sql = 'INSERT INTO ' . USER_NOTIFICATIONS_TABLE . ' ' . $db->sql_build_array('INSERT', [
				'item_type'	=> $type,
				'item_id'	=> 0,
				'user_id'	=> (int) $user_id,
				'method'	=> $method,
				'notify'	=> 1,
			]);
			$db->sql_query($sql);
		}
		else if (!$notify)
		{
			$sql = 'UPDATE ' . USER_NOTIFICATIONS_TABLE . "
				SET notify = 1
				WHERE item_type = '" . $db->sql_escape($type) . "'
					AND item_id = 0
					AND user_id = " . (int) $user_id . "
					AND method = '" . $db->sql_escape($method) . "'";
			$db->sql_query($sql);
		}
	}

	/**
	 * Subscribe a user to a topic.
	 *
	 * @param int $user_id	User ID
	 * @param int $topic_id	Topic ID
	 * @return void
	 */
	protected function watch_topic(int $user_id, int $topic_id): void
	{
		$db = self::get_db();

		$sql = 'DELETE FROM ' . TOPICS_WATCH_TABLE . '
			WHERE user_id = ' . (int) $user_id . '
				AND topic_id = ' . (int) $topic_id;
		$db->sql_query($sql);

		$sql = 'INSERT INTO ' . TOPICS_WATCH_TABLE . ' ' . $db->sql_build_array('INSERT', [
			'topic_id'		=> (int) $topic_id,
			'user_id'		=> (int) $user_id,
			'notify_status'	=> NOTIFY_YES,
		]);
		$db->sql_query($sql);
	}

	/**
	 * Subscribe a user to a forum.
	 *
	 * @param int $user_id	User ID
	 * @param int $forum_id	Forum ID
	 * @return void
	 */
	protected function watch_forum(int $user_id, int $forum_id): void
	{
		$db = self::get_db();

		$sql = 'DELETE FROM ' . FORUMS_WATCH_TABLE . '
			WHERE user_id = ' . (int) $user_id . '
				AND forum_id = ' . (int) $forum_id;
		$db->sql_query($sql);

		$sql = 'INSERT INTO ' . FORUMS_WATCH_TABLE . ' ' . $db->sql_build_array('INSERT', [
			'forum_id'		=> (int) $forum_id,
			'user_id'		=> (int) $user_id,
			'notify_status'	=> NOTIFY_YES,
		]);
		$db->sql_query($sql);
	}

	/**
	 * Remove a forum subscription.
	 *
	 * @param int $user_id User ID
	 * @param int $forum_id Forum ID
	 * @return void
	 */
	protected function unwatch_forum(int $user_id, int $forum_id): void
	{
		$db = self::get_db();
		$sql = 'DELETE FROM ' . FORUMS_WATCH_TABLE . '
			WHERE user_id = ' . (int) $user_id . '
				AND forum_id = ' . (int) $forum_id;
		$db->sql_query($sql);
	}

	/**
	 * Get a user row.
	 *
	 * @param int $user_id User ID
	 * @return array
	 */
	protected function get_user_row(int $user_id): array
	{
		$db = self::get_db();
		$sql = 'SELECT *
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $user_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		return $row ?: [];
	}

	/**
	 * Get a user ID by username.
	 *
	 * @param string $username Username
	 * @return int
	 */
	protected function get_user_id(string $username): int
	{
		$db = self::get_db();
		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . "
			WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
		$result = $db->sql_query($sql);
		$user_id = (int) $db->sql_fetchfield('user_id');
		$db->sql_freeresult($result);

		return $user_id;
	}

	/**
	 * Get the administrator e-mail address.
	 *
	 * @return string
	 */
	protected function get_admin_email(): string
	{
		$row = $this->get_user_row($this->get_user_id('admin'));

		return $row['user_email'] ?? '';
	}

	/**
	 * Update a user row.
	 *
	 * @param int $user_id User ID
	 * @param array $data Data to update
	 * @return void
	 */
	protected function update_user(int $user_id, array $data): void
	{
		$db = self::get_db();
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $data) . '
			WHERE user_id = ' . (int) $user_id;
		$db->sql_query($sql);
	}

	/**
	 * Make a user inactive and create an activation key.
	 *
	 * @param int $user_id User ID
	 * @param int $reason Inactive reason
	 * @param string $actkey Optional activation key
	 * @return string Activation key
	 */
	protected function set_user_inactive(int $user_id, int $reason = INACTIVE_REGISTER, string $actkey = ''): string
	{
		$actkey = $actkey ?: gen_rand_string(10);

		$this->update_user($user_id, [
			'user_type'					=> USER_INACTIVE,
			'user_inactive_reason'		=> (int) $reason,
			'user_inactive_time'		=> time(),
			'user_actkey'				=> $actkey,
			'user_actkey_expiration'	=> time() + 86400,
		]);

		return $actkey;
	}

	/**
	 * Expire a user's activation key.
	 *
	 * @param int $user_id User ID
	 * @return void
	 */
	protected function expire_activation_key(int $user_id): void
	{
		$this->update_user($user_id, [
			'user_actkey_expiration' => time() - 3600,
		]);
	}

	/**
	 * Configure the registration settings used by the e-mail tests.
	 *
	 * @param int $require_activation Activation mode
	 * @param bool $coppa Enable COPPA
	 * @return void
	 */
	protected function configure_registration(int $require_activation = USER_ACTIVATION_NONE, bool $coppa = false): void
	{
		self::set_board_config([
			'coppa_enable'			=> $coppa ? 1 : 0,
			'email_check_mx'		=> 0,
			'enable_confirm'		=> 0,
			'pass_complex'			=> 'PASS_TYPE_ANY',
			'require_activation'	=> $require_activation,
		]);
	}

	/**
	 * Register a user through the UCP registration form.
	 *
	 * @param string $username Username
	 * @param string $email E-mail address
	 * @param string $password Password
	 * @param bool $coppa Whether to register as COPPA user
	 * @return \Symfony\Component\DomCrawler\Crawler
	 */
	protected function register_user(string $username, string $email, string $password = 'RegistrationPass1', bool $coppa = false)
	{
		$this->add_lang('ucp');

		$crawler = self::request('GET', 'ucp.php?mode=register' . ($coppa ? '&coppa=1' : ''));

		if ($coppa)
		{
			$form = $crawler->selectButton('coppa_yes')->form();
			$crawler = self::submit($form);
		}

		$form = $crawler->selectButton($this->lang('AGREE'))->form();
		$crawler = self::submit($form);

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'username'			=> $username,
			'email'				=> $email,
			'new_password'		=> $password,
			'password_confirm'	=> $password,
		]);
		$form['tz']->select('Europe/Berlin');

		$crawler = self::submit($form);
		$user_id = $this->get_user_id($username);

		if ($user_id)
		{
			self::$created_users[$user_id] = $username;
		}

		return $crawler;
	}

	/**
	 * Create a user and remember it for cleanup.
	 *
	 * @param string $username Username
	 * @param string $email E-mail address
	 * @return int User ID
	 */
	protected function create_user($username, $email = 'nobody@example.com')
	{
		$user_id = (int) parent::create_user($username, $email);

		if ($user_id)
		{
			self::$created_users[$user_id] = $username;
		}

		return $user_id;
	}

	/**
	 * Delete users created by this test case.
	 *
	 * @param array $user_ids User IDs
	 * @return void
	 */
	protected static function delete_test_users(array $user_ids): void
	{
		$user_ids = array_filter(array_map('intval', $user_ids), static function (int $user_id): bool
		{
			return $user_id > 1;
		});

		if (empty($user_ids))
		{
			return;
		}

		self::request('GET', 'index.php/email-catcher/trigger?type=delete_users&user_ids=' . implode(',', $user_ids), [], false);
	}

	/**
	 * Submit the resend activation form.
	 *
	 * @param string $username Username
	 * @param string $email E-mail address
	 * @return \Symfony\Component\DomCrawler\Crawler
	 */
	protected function submit_resend_activation(string $username, string $email)
	{
		$this->add_lang('ucp');

		$crawler = self::request('GET', 'ucp.php?mode=resend_act');
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'username'	=> $username,
			'email'		=> $email,
		]);

		return self::submit($form);
	}

	/**
	 * Create a group and make a user its leader.
	 *
	 * Requires an administrator session.
	 *
	 * @param string $group_name Group name
	 * @param string $leader Group leader username
	 * @return int Group ID
	 */
	protected function create_group_with_leader(string $group_name, string $leader): int
	{
		$this->add_lang('acp/groups');

		$crawler = self::request('GET', 'adm/index.php?i=acp_groups&mode=manage&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, ['group_name' => $group_name]);

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, ['group_name' => $group_name]);
		$this->assertContainsLang('GROUP_CREATED', $crawler->filter('#main')->text());

		$group_id = $this->get_group_id($group_name);
		$this->assertGreaterThan(0, $group_id);

		$crawler = self::request('GET', 'adm/index.php?i=acp_groups&mode=manage&action=list&g=' . $group_id . '&sid=' . $this->sid);
		$form = $crawler->filter('input[name=addusers]')->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, [
			'leader'	=> 1,
			'usernames'	=> $leader,
		]);
		$this->assertContainsLang('GROUP_MODS_ADDED', $crawler->filter('#main')->text());

		return $group_id;
	}

	/**
	 * Set a group type and purge the cache.
	 *
	 * @param int $group_id Group ID
	 * @param int $group_type Group type
	 * @return void
	 */
	protected function set_group_type(int $group_id, int $group_type): void
	{
		$db = self::get_db();
		$sql = 'UPDATE ' . GROUPS_TABLE . '
			SET group_type = ' . (int) $group_type . '
			WHERE group_id = ' . (int) $group_id;
		$db->sql_query($sql);

		self::purge_test_cache();
	}

	/**
	 * Bookmark a topic for a user.
	 *
	 * @param int $user_id User ID
	 * @param int $topic_id Topic ID
	 * @return void
	 */
	protected function bookmark_topic(int $user_id, int $topic_id): void
	{
		$db = self::get_db();
		$sql = 'INSERT INTO ' . BOOKMARKS_TABLE . ' ' . $db->sql_build_array('INSERT', [
			'topic_id'	=> (int) $topic_id,
			'user_id'	=> (int) $user_id,
		]);
		$db->sql_query($sql);
	}

	/**
	 * Get a report ID.
	 *
	 * @param int $post_id Post ID
	 * @param int $pm_id Private message ID
	 * @return int
	 */
	protected function get_report_id(int $post_id = 0, int $pm_id = 0): int
	{
		$db = self::get_db();
		$where = $post_id ? 'post_id = ' . (int) $post_id : 'pm_id = ' . (int) $pm_id;
		$sql = 'SELECT report_id
			FROM ' . REPORTS_TABLE . '
			WHERE ' . $where . '
			ORDER BY report_id DESC';
		$result = $db->sql_query_limit($sql, 1);
		$report_id = (int) $db->sql_fetchfield('report_id');
		$db->sql_freeresult($result);

		return $report_id;
	}

	/**
	 * Get a topic ID by title.
	 *
	 * @param string $title Topic title
	 * @return int
	 */
	protected function get_topic_id_by_title(string $title): int
	{
		$db = self::get_db();
		$sql = 'SELECT topic_id
			FROM ' . TOPICS_TABLE . "
			WHERE topic_title = '" . $db->sql_escape($title) . "'
			ORDER BY topic_id DESC";
		$result = $db->sql_query_limit($sql, 1);
		$topic_id = (int) $db->sql_fetchfield('topic_id');
		$db->sql_freeresult($result);

		return $topic_id;
	}

	/**
	 * Get a post ID by subject.
	 *
	 * @param string $subject Post subject
	 * @return int
	 */
	protected function get_post_id_by_subject(string $subject): int
	{
		$db = self::get_db();
		$sql = 'SELECT post_id
			FROM ' . POSTS_TABLE . "
			WHERE post_subject = '" . $db->sql_escape($subject) . "'
			ORDER BY post_id DESC";
		$result = $db->sql_query_limit($sql, 1);
		$post_id = (int) $db->sql_fetchfield('post_id');
		$db->sql_freeresult($result);

		return $post_id;
	}

	/**
	 * Get board configuration values.
	 *
	 * @param array $names Config names
	 * @return array
	 */
	protected static function get_board_config(array $names): array
	{
		$db = self::get_db();
		$sql = 'SELECT config_name, config_value
			FROM ' . CONFIG_TABLE . '
			WHERE ' . $db->sql_in_set('config_name', $names);
		$result = $db->sql_query($sql);

		$config = [];
		while ($row = $db->sql_fetchrow($result))
		{
			$config[$row['config_name']] = $row['config_value'];
		}
		$db->sql_freeresult($result);

		return $config;
	}

	/**
	 * Update board configuration values and purge the board cache.
	 *
	 * @param array $values Config name => value
	 * @return void
	 */
	protected static function set_board_config(array $values): void
	{
		$db = self::get_db();

		foreach ($values as $name => $value)
		{
			$value = is_bool($value) ? (int) $value : (string) $value;

			$sql = 'UPDATE ' . CONFIG_TABLE . "
				SET config_value = '" . $db->sql_escape($value) . "'
				WHERE config_name = '" . $db->sql_escape($name) . "'";
			$db->sql_query($sql);
		}

		self::purge_test_cache();
	}

	/**
	 * Purge the board cache.
	 *
	 * This is a static counterpart of phpbb_functional_test_case::purge_cache()
	 * so that it can also be used from tearDownAfterClass().
	 *
	 * @return void
	 */
	protected static function purge_test_cache(): void
	{
		global $phpbb_root_path;

		$cache = new \phpbb\cache\driver\file($phpbb_root_path . 'cache/' . PHPBB_ENVIRONMENT . '/');
		$cache->purge();
		$cache->unload();
		$cache->load();
	}

	/**
	 * Check whether an extension is enabled.
	 *
	 * @param string $extension Extension name
	 * @return bool
	 */
	protected static function is_extension_enabled(string $extension): bool
	{
		$db = self::get_db();
		$sql = 'SELECT ext_active
			FROM ' . EXT_TABLE . "
			WHERE ext_name = '" . $db->sql_escape($extension) . "'";
		$result = $db->sql_query($sql);
		$active = (bool) $db->sql_fetchfield('ext_active');
		$db->sql_freeresult($result);

		return $active;
	}
}
