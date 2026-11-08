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

namespace phpbb\notification\method;

use phpbb\messenger\method\messenger_interface;
use phpbb\notification\type\type_interface;
use phpbb\routing\router;
use phpbb\user;
use phpbb\user_loader;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\di\service_collection;

/**
* Email notification method class
* This class handles sending emails for notifications
*/

class email extends messenger_base
{
	/** @var user */
	protected $user;

	/** @var config */
	protected $config;

	/** @var driver_interface */
	protected $db;

	/** @var string Notification emails table */
	protected $notification_emails_table;

	/** @var service_collection */
	protected $messenger;

	/** @var router */
	protected $router;

	/** @var string|null Path of the unsubscribe route, loaded on first use */
	protected $unsubscribe_path;

	/**
	 * Notification Method email Constructor
	 *
	 * @param user_loader $user_loader
	 * @param user $user
	 * @param config $config
	 * @param driver_interface $db
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 * @param string $notification_emails_table
	 * @param service_collection $messenger
	 * @param router $router
	 */
	public function __construct(
		user_loader $user_loader,
		user $user,
		config $config,
		driver_interface $db,
		$phpbb_root_path,
		$php_ext,
		$notification_emails_table,
		service_collection $messenger,
		router $router
	)
	{
		parent::__construct($messenger, $user_loader, $phpbb_root_path, $php_ext);

		$this->user = $user;
		$this->config = $config;
		$this->db = $db;
		$this->notification_emails_table = $notification_emails_table;
		$this->router = $router;
	}

	/**
	* Get notification method name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'notification.method.email';
	}

	/**
	* Is this method available for the user?
	* This is checked on the notifications options
	*
	* @param type_interface|null $notification_type  An optional instance of a notification type. If provided, this
	*											method additionally checks if the type provides an email template.
	* @return bool
	*/
	public function is_available(type_interface|null $notification_type = null)
	{
		return parent::is_available($notification_type) && $this->config['email_enable'] && !empty($this->user->data['user_email']);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_notified_users($notification_type_id, array $options)
	{
		$notified_users = [];

		$sql = 'SELECT user_id
			FROM ' . $this->notification_emails_table . '
			WHERE notification_type_id = ' . (int) $notification_type_id .
			(isset($options['item_id']) ? ' AND item_id = ' . (int) $options['item_id'] : '') .
			(isset($options['item_parent_id']) ? ' AND item_parent_id = ' . (int) $options['item_parent_id'] : '') .
			(isset($options['user_id']) ? ' AND user_id = ' . (int) $options['user_id'] : '');
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$notified_users[$row['user_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		return $notified_users;
	}

	/**
	* Parse the queue and notify the users
	*/
	public function notify()
	{
		$insert_buffer = new \phpbb\db\sql_insert_buffer($this->db, $this->notification_emails_table);

		/** @var type_interface $notification */
		foreach ($this->queue as $notification)
		{
			$data = self::clean_data($notification->get_insert_array());
			$insert_buffer->insert($data);
		}

		$insert_buffer->flush();

		$this->notify_using_messenger('messenger.method.email');
	}

	/**
	* {@inheritdoc}
	*/
	public function mark_notifications($notification_type_id, $item_id, $user_id, $time = false, $mark_read = true)
	{
		$sql = 'DELETE FROM ' . $this->notification_emails_table . '
			WHERE ' . ($notification_type_id !== false ? $this->db->sql_in_set('notification_type_id', is_array($notification_type_id) ? $notification_type_id : [$notification_type_id]) : '1=1') .
			($user_id !== false ? ' AND ' . $this->db->sql_in_set('user_id', $user_id) : '') .
			($item_id !== false ? ' AND ' . $this->db->sql_in_set('item_id', $item_id) : '');
		$this->db->sql_query($sql);
	}

	/**
	* {@inheritdoc}
	*/
	public function mark_notifications_by_parent($notification_type_id, $item_parent_id, $user_id, $time = false, $mark_read = true)
	{
		$sql = 'DELETE FROM ' . $this->notification_emails_table . '
			WHERE ' . ($notification_type_id !== false ? $this->db->sql_in_set('notification_type_id', is_array($notification_type_id) ? $notification_type_id : [$notification_type_id]) : '1=1') .
			($user_id !== false ? ' AND ' . $this->db->sql_in_set('user_id', $user_id) : '') .
			($item_parent_id !== false ? ' AND ' . $this->db->sql_in_set('item_parent_id', $item_parent_id, false, true) : '');
		$this->db->sql_query($sql);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_message_vars(messenger_interface $messenger_method, type_interface $notification, array $user): array
	{
		$vars = parent::get_message_vars($messenger_method, $notification, $user);

		$watch_item = $notification->get_unsubscribe_watch_item();
		$unsubscribe_url = $this->get_unsubscribe_url($user, $notification->get_type(), $watch_item['mode'] ?? '', $watch_item['id'] ?? 0);

		if ($unsubscribe_url === null)
		{
			$vars['U_UNSUBSCRIBE'] = $vars['U_NOTIFICATION_SETTINGS'];

			return $vars;
		}

		$messenger_method->set_list_unsubscribe($unsubscribe_url);
		$vars['U_UNSUBSCRIBE'] = $unsubscribe_url;

		// Stop watching links should work without login as well
		if (isset($vars['U_STOP_WATCHING_FORUM']))
		{
			$vars['U_STOP_WATCHING_FORUM'] = $unsubscribe_url;
		}

		if (isset($vars['U_STOP_WATCHING_TOPIC']))
		{
			$vars['U_STOP_WATCHING_TOPIC'] = $unsubscribe_url;
		}

		return $vars;
	}

	/**
	 * Create the signed parameters of an unsubscribe link
	 *
	 * Signatures cover the user's current email address, so changing the address
	 * invalidates all earlier links of that user without storing anything per link.
	 *
	 * @param array $user_row		User row, requires user_id and user_email
	 * @param string $type_name		Notification type name the email was sent for
	 * @param string $watch_mode	'topic' or 'forum' if the email was caused by watching an item, empty otherwise
	 * @param int $watch_id			ID of the watched topic or forum, 0 otherwise
	 *
	 * @return array|null Link parameters, null if they can't be signed
	 */
	public function get_unsubscribe_params(array $user_row, string $type_name, string $watch_mode = '', int $watch_id = 0): array|null
	{
		$params = [
			'user_id'		=> (int) $user_row['user_id'],
			'type'			=> $type_name,
			'watch_mode'	=> $watch_mode,
			'watch_id'		=> $watch_id,
			'time'			=> time(),
		];

		$signature = $this->sign_unsubscribe($user_row, array_values($params));

		return $signature === null ? null : $params + ['hash' => $signature];
	}

	/**
	 * Create the absolute URL of an unsubscribe link
	 *
	 * Links in emails are opened outside of the board, so the URL is built without
	 * the controller helper. It has to be absolute, use plain & separators and must
	 * not depend on the request the email is sent from, e.g. cron or the CLI.
	 *
	 * @param array $user_row		User row, requires user_id and user_email
	 * @param string $type_name		Notification type name the email was sent for
	 * @param string $watch_mode	'topic' or 'forum' if the email was caused by watching an item, empty otherwise
	 * @param int $watch_id			ID of the watched topic or forum, 0 otherwise
	 *
	 * @return string|null URL, null if the link can't be signed
	 */
	public function get_unsubscribe_url(array $user_row, string $type_name, string $watch_mode = '', int $watch_id = 0): string|null
	{
		$params = $this->get_unsubscribe_params($user_row, $type_name, $watch_mode, $watch_id);

		if ($params === null)
		{
			return null;
		}

		return generate_board_url() . "/index.{$this->php_ext}" . $this->get_unsubscribe_path() . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
	}

	/**
	 * Get the path of the unsubscribe route
	 *
	 * Only the path is taken from the router, its URL generator depends on the
	 * request context of the current request.
	 *
	 * @return string
	 */
	protected function get_unsubscribe_path(): string
	{
		if ($this->unsubscribe_path === null)
		{
			$this->unsubscribe_path = $this->router->get_routes()->get('phpbb_notifications_unsubscribe')->getPath();
		}

		return $this->unsubscribe_path;
	}

	/**
	 * Check the signature of unsubscribe link parameters
	 *
	 * @param array $user_row	User row, requires user_id and user_email
	 * @param array $params		Link parameters with the keys user_id, type, watch_mode, watch_id, time and hash
	 *
	 * @return bool True if the link is valid for this user
	 */
	public function check_unsubscribe_params(array $user_row, array $params): bool
	{
		// Skip signing if user id doesn't even match
		if ((int) $params['user_id'] !== ((int) $user_row['user_id']))
		{
			return false;
		}

		$signature = $this->sign_unsubscribe($user_row, [
			(int) $params['user_id'],
			(string) $params['type'],
			(string) $params['watch_mode'],
			(int) $params['watch_id'],
			(int) $params['time'],
		]);

		return $signature !== null && hash_equals($signature, (string) $params['hash']);
	}

	/**
	 * Check if an unsubscribe link is older than the board allows
	 *
	 * @param array $params	Link parameters, requires time
	 *
	 * @return bool True if the link has expired
	 */
	public function is_unsubscribe_link_expired(array $params): bool
	{
		$expire_days = (int) $this->config['email_unsubscribe_expire_days'];

		return $expire_days > 0 && (int) $params['time'] < time() - $expire_days * 86400;
	}

	/**
	 * Create the signature of an unsubscribe link
	 *
	 * Runs while notifications are being sent, so an address with invalid encoding
	 * must not throw and stop the emails to other recipients.
	 *
	 * @param array $user_row	User row, requires user_email
	 * @param array $fields		Fields to sign
	 *
	 * @return string|null Signature, null if there is no secret or email address to sign with
	 */
	protected function sign_unsubscribe(array $user_row, array $fields): string|null
	{
		$secret = (string) $this->config['email_unsubscribe_secret'];
		$email = (string) ($user_row['user_email'] ?? '');

		if (empty($secret) || empty($email))
		{
			return null;
		}

		return hash_hmac('sha256', json_encode([$email, $fields], JSON_INVALID_UTF8_SUBSTITUTE), $secret);
	}

	/**
	 * Clean data to contain only what we need for email notifications table
	 *
	 * @param array $data Notification data
	 * @return array Cleaned notification data
	 */
	public static function clean_data(array $data)
	{
		$row = [
			'notification_type_id'	=> null,
			'item_id'				=> null,
			'item_parent_id'		=> null,
			'user_id'				=> null,
		];

		return array_intersect_key($data, $row);
	}
}
