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

namespace phpbb\cron\task\core;

use phpbb\config\config;
use phpbb\cron\task\base;
use phpbb\db\driver\driver_interface;
use phpbb\exception\runtime_exception;
use phpbb\log\log_interface;
use phpbb\notification\manager;
use phpbb\user;
use phpbb\version_helper;

class version_check extends base
{
	/** @var config */
	protected $config;

	/** @var version_helper */
	protected $version_helper;

	/** @var driver_interface */
	protected $db;

	/** @var log_interface */
	protected $log;

	/** @var user */
	protected $user;

	/** @var manager */
	protected $notification_manager;

	/** @var string Template name for critical updates */
	public const UPDATE_CRITICAL = 'update_critical';

	/** @var string Template name for maintenance updates */
	public const UPDATE_MAINTENANCE = 'update_maintenance';

	/** @var string Template name for security updates */
	public const UPDATE_SECURITY = 'update_security';

	/**
	 * Constructor
	 *
	 * @param config $config
	 * @param version_helper $version_helper
	 * @param log_interface $log
	 * @param user $user
	 * @param manager $notification_manager
	 */
	public function __construct(config $config, version_helper $version_helper, log_interface $log, user $user, manager $notification_manager)
	{
		$this->config = $config;
		$this->version_helper = $version_helper;
		$this->log = $log;
		$this->user = $user;
		$this->notification_manager = $notification_manager;
	}

	/**
	 * Run the cron task.
	 *
	 * @return void
	 */
	public function run()
	{
		try
		{
			// Always force update here
			$updates_available = $this->version_helper->get_update_on_branch(true);

			if (!empty($updates_available))
			{
				$this->notify_admins($updates_available);
			}
		}
		catch (runtime_exception $e)
		{
			// Log the exception but don't throw it, as we don't want to break the cron task if the version check fails
			$this->log->add('admin', $this->user->id(), $this->user->ip, 'LOG_VERSION_CHECK_FAIL', false, [$e->getMessage()]);
		}

		// Update the last check time
		$this->config->set('version_check_last_cron', time());
	}

	/**
	 * {@inheritdoc}
	 */
	public function should_run(): bool
	{
		return isset($this->config['version_check_last_cron']) && $this->config['version_check_last_cron'] < time() - ((int) $this->config['version_check_interval']) * 60;
	}

	/**
	 * Send update notifications to members of the administrators group.
	 *
	 * @param array $update_data Update information from version_helper
	 * @return void
	 */
	protected function notify_admins(array $update_data): void
	{
		// We need at least the current version info, skip if it's not available
		if (empty($update_data['current']))
		{
			return;
		}

		$template = $this->get_template_name($update_data);
		$type_data = [
			'item_id' => crc32($template . $update_data['current']) & 0xFFFFFF,
			'template' => $template,
			'current_version' => $this->config['version'],
			'new_version' => $update_data['current'],
			'announcement' => $update_data['announcement'] ?? '',
			'download' => $update_data['download'] ?? '',
		];

		$this->notification_manager->add_notifications('notification.type.update_maintenance', $type_data);
	}

	/**
	 * Get the notification template name based on the available update.
	 *
	 * @param array $update_data Update information from version_helper
	 * @return string
	 */
	protected function get_template_name(array $update_data): string
	{
		$current_version = $this->config['version'];

		if (!empty($update_data['critical']) && $this->version_helper->compare($update_data['critical'], $current_version, '>'))
		{
			return self::UPDATE_CRITICAL;
		}

		if (!empty($update_data['security']) && $this->version_helper->compare($update_data['security'], $current_version, '>'))
		{
			return self::UPDATE_SECURITY;
		}

		return self::UPDATE_MAINTENANCE;
	}
}
