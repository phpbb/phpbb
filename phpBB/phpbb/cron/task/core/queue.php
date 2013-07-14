<?php
/**
*
* @package phpBB3
* @copyright (c) 2010 phpBB Group
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
* Queue cron task. Sends email and jabber messages queued by other scripts.
*
* @package phpBB3
*/
class phpbb_cron_task_core_queue extends phpbb_cron_task_base
{
	protected $phpbb_root_path;
	protected $php_ext;
	protected $config;

	/**
	* Constructor.
	*
	* @param string $phpbb_root_path The root path
	* @param string $php_ext The PHP extension
	* @param phpbb_config $config The config
	*/
	public function __construct($phpbb_root_path, $php_ext, phpbb_config $config)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->config = $config;
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		if (!class_exists('queue'))
		{
			include($this->phpbb_root_path . 'includes/functions_messenger.' . $this->php_ext);
		}
		$queue = new queue();
		$queue->process();
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* Queue task is only run if the email queue (file) exists.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		return file_exists($this->phpbb_root_path . 'cache/queue.' . $this->php_ext);
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* The interval between queue runs is specified in board configuration.
	*
	* @return bool
	*/
	public function should_run()
	{
		return $this->config['last_queue_run'] < time() - $this->config['queue_interval_config'];
	}
}
