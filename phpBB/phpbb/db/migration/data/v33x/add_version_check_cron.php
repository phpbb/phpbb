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

namespace phpbb\db\migration\data\v33x;

use phpbb\auth\auth;
use phpbb\db\migration\container_aware_migration;

class add_version_check_cron extends container_aware_migration
{
	/** @var string Update notification type */
	protected const NOTIFICATION_TYPE_UPDATE = 'notification.type.update_maintenance';

	public function effectively_installed(): bool
	{
		return $this->config->offsetExists('version_check_last_cron');
	}

	public function update_data(): array
	{
		return [
			['config.add', ['version_check_interval', 60]], // 60 minutes
			['config.add', ['version_check_last_cron', 0]], // Last run timestamp
			['custom', [[$this, 'add_default_email_notifications']]],
		];
	}

	public function revert_data(): array
	{
		return [
			['custom', [[$this, 'remove_default_email_notifications']]],
		];
	}

	/**
	 * Enable email notifications by default for admins and founders
	 */
	public function add_default_email_notifications(): void
	{
		/** @var auth $auth */
		$auth = $this->container->get('auth');

		$admin_ary = $auth->acl_get_list(false, 'a_board');
		$users = (!empty($admin_ary[0]['a_board'])) ? $admin_ary[0]['a_board'] : [];

		$sql = 'SELECT user_id
			FROM ' . $this->table_prefix . 'users
			WHERE user_type = ' . USER_FOUNDER;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$users[] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		if (empty($users))
		{
			return;
		}

		/** @var \phpbb\notification\manager $notification_manager */
		$notification_manager = $this->container->get('notification_manager');

		foreach (array_unique($users) as $user_id)
		{
			$notification_manager->add_subscription(self::NOTIFICATION_TYPE_UPDATE, 0, 'notification.method.email', $user_id);
		}
	}

	/**
	 * Remove the default email notifications added by add_default_email_notifications()
	 */
	public function remove_default_email_notifications(): void
	{
		$sql = 'DELETE FROM ' . USER_NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . self::NOTIFICATION_TYPE_UPDATE . "'
				AND method = 'notification.method.email'";
		$this->sql_query($sql);
	}
}
