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
* Tidy sessions cron task.
*/
class tidy_sessions extends \phpbb\cron\task\base
{
	protected $config;
	protected $user;

	/**
	* Constructor.
	*
	* @param \phpbb\config\config $config The config
	* @param \phpbb\user $user The user
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\user $user)
	{
		$this->config = $config;
		$this->user = $user;
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		$this->user->session_gc();
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* The interval between session tidying is specified in board
	* configuration.
	*
	* @return bool
	*/
	public function should_run()
	{
		return $this->config['session_last_gc'] < time() - $this->config['session_gc'];
	}
}
