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
* Tidy sessions cron task.
*
* @package phpBB3
*/
class phpbb_cron_task_core_tidy_sessions extends phpbb_cron_task_base
{
	protected $config;
	protected $user;

	/**
	* Constructor.
	*
	* @param phpbb_config $config The config
	* @param phpbb_user $user The user
	*/
	public function __construct(phpbb_config $config, phpbb_user $user)
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
