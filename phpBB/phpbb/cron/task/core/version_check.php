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

	/** @var user */
	protected $user;

	/** @var manager */
	protected $notification_manager;

	/**
	 * Constructor
	 *
	 * @param config $config
	 * @param version_helper $version_helper
	 * @param driver_interface $db
	 * @param user $user
	 * @param manager $notification_manager
	 */
	public function __construct(config $config, version_helper $version_helper, driver_interface $db, user $user, manager $notification_manager)
	{
		$this->config = $config;
		$this->version_helper = $version_helper;
		$this->db = $db;
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

			// Update the last check time
			$this->config->set('version_check_last_cron', time());
		}
		catch (\phpbb\exception\runtime_exception $e)
		{
			// Log the exception but don't throw it, as we don't want to break the cron task if the version check fails
			// @todo: add logging here if needed
		}
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
		$template = $this->get_template_name($update_data);
		$type_data = [
			'item_id' => (int) sprintf('%u', crc32($template . $update_data['current'])),
			'template' => $template,
			'current_version' => $this->config['version'],
			'new_version' => $update_data['current'],
			'announcement' => $update_data['announcement'],
			'download' => $update_data['download'],
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

		if (!empty($update_data['urgent']) && $this->version_helper->compare($update_data['urgent'], $current_version, '>'))
		{
			return 'update_urgent';
		}

		if (!empty($update_data['security']) && $this->version_helper->compare($update_data['security'], $current_version, '>'))
		{
			return 'update_security';
		}

		return 'update_maintenance';
	}
}
