<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Prune notifications cron task.
*
* @package phpBB3
*/
class phpbb_cron_task_core_prune_notifications extends phpbb_cron_task_base
{
	protected $config;
	protected $notification_manager;

	/**
	* Constructor.
	*
	* @param phpbb_config $config The config
	* @param phpbb_notification_manager $notification_manager Notification manager
	*/
	public function __construct(phpbb_config $config, phpbb_notification_manager $notification_manager)
	{
		$this->config = $config;
		$this->notification_manager = $notification_manager;
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		// time minus expire days in seconds
		$timestamp = time() - ($this->config['read_notification_expire_days'] * 60 * 60 * 24);
		$this->notification_manager->prune_notifications($timestamp);
	}

	/**
	* Returns whether this cron task can run, given current board configuration.=
	*
	* @return bool
	*/
	public function is_runnable()
	{
		return (bool) $this->config['read_notification_expire_days'];
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* The interval between prune notifications is specified in board
	* configuration.
	*
	* @return bool
	*/
	public function should_run()
	{
		return $this->config['read_notification_last_gc'] < time() - $this->config['read_notification_gc'];
	}
}
