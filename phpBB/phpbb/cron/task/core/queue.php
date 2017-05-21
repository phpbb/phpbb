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

/**
* Queue cron task. Sends email and jabber messages queued by other scripts.
*/
class queue extends \phpbb\cron\task\base
{
	protected $phpbb_root_path;
	protected $php_ext;
	protected $cache_dir;
	protected $config;

	/**
	 * Constructor.
	 *
	 * @param string $phpbb_root_path The root path
	 * @param string $php_ext PHP file extension
	 * @param \phpbb\config\config $config The config
	 * @param string $cache_dir phpBB cache directory
	 */
	public function __construct($phpbb_root_path, $php_ext, \phpbb\config\config $config, $cache_dir)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->config = $config;
		$this->cache_dir = $cache_dir;
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
		$queue = new \queue();
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
		return file_exists($this->cache_dir . 'queue.' . $this->php_ext);
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
		return $this->config['last_queue_run'] < time() - $this->config['queue_interval'];
	}
}
