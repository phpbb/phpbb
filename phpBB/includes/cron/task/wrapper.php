<?php
/**
*
* @package phpBB3
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
class phpbb_cron_task_wrapper
{
	/**
	* Wraps a task $task, which must implement cron_task interface.
	*
	* @return void
	*/
	public function __construct(phpbb_cron_task $task)
	{
		$this->task = $task;
	}

	/**
	* Returns whether this task is parametrized.
	*
	* Parametrized tasks accept parameters during initialization and must
	* normally be scheduled with parameters.
	*
	* @return bool		Whether or not this task is parametrized.
	*/
	public function is_parametrized()
	{
		return $this->task instanceof phpbb_cron_task_parametrized;
	}

	/**
	* Returns whether the wrapped task is ready to run.
	*
	* A task is ready to run when it is runnable according to current configuration
	* and enough time has passed since it was last run.
	*
	* @return bool		Whether the wrapped task is ready to run.
	*/
	public function is_ready()
	{
		return $this->task->is_runnable() && $this->task->should_run();
	}

	/**
	* Returns the name of wrapped task. It is the same as the wrapped class's class name.
	*
	* @return string		Class name of wrapped task.
	*/
	public function get_name()
	{
		return get_class($this->task);
	}

	/**
	* Returns a url through which this task may be invoked via web.
	*
	* @return string		URL
	*/
	public function get_url()
	{
		global $phpbb_root_path, $phpEx;

		$name = $this->get_name();
		if ($this->is_parametrized())
		{
			$params = $this->task->get_parameters();
			$extra = '';
			foreach ($params as $key => $value)
			{
				$extra .= '&amp;' . $key . '=' . urlencode($value);
			}
		}
		else
		{
			$extra = '';
		}
		$url = append_sid($phpbb_root_path . 'cron.' . $phpEx, 'cron_type=' . $name . $extra);
		return $url;
	}

	/**
	* Forwards all other method calls to the wrapped task implementation.
	*
	* @return mixed
	*/
	public function __call($name, $args)
	{
		return call_user_func_array(array($this->task, $name), $args);
	}
}
