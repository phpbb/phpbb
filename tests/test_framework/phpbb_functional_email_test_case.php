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
 * writes one JSON object per email to store/email_catcher.jsonl so that the
 * PHPUnit process can inspect messages produced by the web server process.
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
			'pass_complex',
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
	 * Get the path to the email capture file.
	 *
	 * @return string
	 */
	protected static function get_email_catcher_file(): string
	{
		global $phpbb_root_path;

		return $phpbb_root_path . 'store/email_catcher.jsonl';
	}

	/**
	 * Remove all previously captured emails.
	 *
	 * @return void
	 */
	protected function clear_captured_emails(): void
	{
		@unlink(self::get_email_catcher_file());
	}

	/**
	 * Get all captured emails.
	 *
	 * @return array
	 */
	protected function get_captured_emails(): array
	{
		$file = self::get_email_catcher_file();
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
	 * Assert that an email was sent.
	 *
	 * @param string $to					Recipient address
	 * @param string $subject_contains		Optional subject substring
	 * @param string $body_contains			Optional text body substring
	 * @return array Captured email
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

		return $email;
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
