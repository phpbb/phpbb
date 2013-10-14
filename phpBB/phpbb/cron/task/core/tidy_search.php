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
* Tidy search cron task.
*
* Will only run when the currently selected search backend supports tidying.
*
* @package phpBB3
*/
class tidy_search extends \phpbb\cron\task\base
{
	protected $phpbb_root_path;
	protected $php_ext;
	protected $auth;
	protected $config;
	protected $db;
	protected $user;

	/**
	* Constructor.
	*
	* @param string $phpbb_root_path The root path
	* @param string $php_ext The PHP extension
	* @param \phpbb\auth\auth $auth The auth
	* @param \phpbb\config\config $config The config
	* @param \phpbb\db\driver\driver $db The db connection
	* @param \phpbb\user $user The user
	*/
	public function __construct($phpbb_root_path, $php_ext, \phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver $db, \phpbb\user $user)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->user = $user;
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		// Select the search method
		$search_type = basename($this->config['search_type']);

		// We do some additional checks in the module to ensure it can actually be utilised
		$error = false;
		$search = new $search_type($error, $this->phpbb_root_path, $this->php_ext, $this->auth, $this->config, $this->db, $this->user);

		if (!$error)
		{
			$search->tidy();
		}
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* Search cron task is runnable in all normal use. It may not be
	* runnable if the search backend implementation selected in board
	* configuration does not exist.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		// Select the search method
		$search_type = basename($this->config['search_type']);

		return class_exists($search_type);
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* The interval between search tidying is specified in board
	* configuration.
	*
	* @return bool
	*/
	public function should_run()
	{
		return $this->config['search_last_gc'] < time() - $this->config['search_gc'];
	}
}
