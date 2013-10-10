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
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Tidy warnings cron task.
*
* Will only run when warnings are configured to expire.
*
* @package phpBB3
*/
class tidy_warnings extends \phpbb\cron\task\base
{
	protected $phpbb_root_path;
	protected $php_ext;
	protected $config;

	/**
	* Constructor.
	*
	* @param string $phpbb_root_path The root path
	* @param string $php_ext The PHP extension
	* @param \phpbb\config\config $config The config
	*/
	public function __construct($phpbb_root_path, $php_ext, \phpbb\config\config $config)
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
		if (!function_exists('tidy_warnings'))
		{
			include($this->phpbb_root_path . 'includes/functions_admin.' . $this->php_ext);
		}
		tidy_warnings();
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* If warnings are set to never expire, this cron task will not run.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		return (bool) $this->config['warnings_expire_days'];
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* The interval between warnings tidying is specified in board
	* configuration.
	*
	* @return bool
	*/
	public function should_run()
	{
		return $this->config['warnings_last_gc'] < time() - $this->config['warnings_gc'];
	}
}
