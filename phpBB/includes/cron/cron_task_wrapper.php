<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
* Cron task wrapper class.
* Enhances cron tasks with convenience methods that work identically for all tasks.
*
* @package phpBB3
*/
class cron_task_wrapper
{
	public function __construct($task)
	{
		$this->task = $task;
	}

	/**
	* Returns whether the wrapped task is ready to run.
	*
	* A task is ready to run when it is runnable according to current configuration
	* and enough time has passed since it was last run.
	*/
	public function is_ready()
	{
		return $this->task->is_runnable() && $this->task->should_run();
	}

	/**
	* Returns the name of wrapped task.
	*/
	public function get_name()
	{
		$class = get_class($this->task);
		return preg_replace('/^cron_task_/', '', $class);
	}

	public function get_url()
	{
		global $phpbb_root_path, $phpEx;

		$name = $this->get_name();
		$url = append_sid($phpbb_root_path . 'cron.' . $phpEx, 'cron_type=' . $name);
		return $url;
	}

	/**
	* Forwards all other method calls to the wrapped task implementation.
	*/
	public function __call($name, $args)
	{
		return call_user_func_array(array($this->task, $name), $args);
	}
}
