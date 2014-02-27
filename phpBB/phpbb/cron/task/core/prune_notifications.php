<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\cron\task\core;

/**
* Prune notifications cron task.
*
* @package phpBB3
*/
class prune_notifications extends \phpbb\cron\task\base
{
	protected $config;
	protected $notification_manager;

	/**
	* Constructor.
	*
	* @param \phpbb\config\config $config The config
	* @param \phpbb\notification\manager $notification_manager Notification manager
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\notification\manager $notification_manager)
	{
		$this->config = $config;
		$this->notification_manager = $notification_manager;
	}

	/**
	* {@inheritdoc}
	*/
	public function run()
	{
		// time minus expire days in seconds
		$timestamp = time() - ($this->config['read_notification_expire_days'] * 60 * 60 * 24);
		$this->notification_manager->prune_notifications($timestamp);
	}

	/**
	* {@inheritdoc}
	*/
	public function is_runnable()
	{
		return (bool) $this->config['read_notification_expire_days'];
	}

	/**
	* {@inheritdoc}
	*/
	public function should_run()
	{
		return $this->config['read_notification_last_gc'] < time() - $this->config['read_notification_gc'];
	}
}
