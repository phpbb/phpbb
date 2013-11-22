<?php
/**
*
* @package phpBB3
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\cron\task\core;

/**
* Tidy cache cron task.
*
* @package phpBB3
*/
class tidy_cache extends \phpbb\cron\task\base
{
	protected $config;
	protected $cache;

	/**
	* Constructor.
	*
	* @param \phpbb\config\config $config The config
	* @param \phpbb\cache\driver\driver_interface $cache The cache driver
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\cache\driver\driver_interface $cache)
	{
		$this->config = $config;
		$this->cache = $cache;
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		$this->cache->tidy();
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* Tidy cache cron task runs if the cache implementation in use
	* supports tidying.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		return true;
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* The interval between cache tidying is specified in board
	* configuration.
	*
	* @return bool
	*/
	public function should_run()
	{
		return $this->config['cache_last_gc'] < time() - $this->config['cache_gc'];
	}
}
