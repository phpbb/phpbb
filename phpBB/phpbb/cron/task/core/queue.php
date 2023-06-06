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

use \phpbb\config\config;
use \phpbb\messenger\queue;

/**
* Queue cron task. Sends email and jabber messages queued by other scripts.
*/
class queue extends \phpbb\cron\task\base
{
	/** var config */
	protected $config;

	/** var queue */
	protected $queue;

	/** var string */
	protected $queue_cache_file;

	/**
	 * Constructor.
	 *
	 * @param config $config The config
	 * @param string $queue_cache_file The messenger file queue cache filename
	 * @param queue $queue The messenger file queue object
	 */
	public function __construct(config $config, queue $queue, $queue_cache_file)
	{
		$this->config = $config;
		$this->queue = $queue;
		$this->queue_cache_file = $queue_cache_file;
	}

	/**
	* Runs this cron task.
	*
	* @return void
	*/
	public function run()
	{
		$this->queue->process();
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
		return file_exists($this->queue_cache_file);
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
