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
* Tidy search cron task.
*
* Will only run when the currently selected search backend supports tidying.
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
	* @param string $php_ext The PHP file extension
	* @param \phpbb\auth\auth $auth The auth
	* @param \phpbb\config\config $config The config
	* @param \phpbb\db\driver\driver_interface $db The db connection
	* @param \phpbb\user $user The user
	*/
	public function __construct($phpbb_root_path, $php_ext, \phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user)
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
		$search_type = $this->config['search_type'];

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
		return class_exists($this->config['search_type']);
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
