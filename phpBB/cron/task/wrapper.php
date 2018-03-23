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

namespace phpbb\cron\task;

/**
* Cron task wrapper class.
* Enhances cron tasks with convenience methods that work identically for all tasks.
*/
class wrapper
{
	protected $task;
	protected $phpbb_root_path;
	protected $php_ext;

	/**
	* Constructor.
	*
	* Wraps a task $task, which must implement cron_task interface.
	*
	* @param \phpbb\cron\task\task $task The cron task to wrap.
	* @param string $phpbb_root_path Relative path to phpBB root
	* @param string $php_ext PHP file extension
	*/
	public function __construct(\phpbb\cron\task\task $task, $phpbb_root_path, $php_ext)
	{
		$this->task = $task;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* Returns whether the wrapped task is parametrised.
	*
	* Parametrized tasks accept parameters during initialization and must
	* normally be scheduled with parameters.
	*
	* @return bool		Whether or not this task is parametrized.
	*/
	public function is_parametrized()
	{
		return $this->task instanceof \phpbb\cron\task\parametrized;
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
	* Returns a url through which this task may be invoked via web.
	*
	* When system cron is not in use, running a cron task is accomplished
	* by outputting an image with the url returned by this function as
	* source.
	*
	* @return string		URL through which this task may be invoked.
	*/
	public function get_url()
	{
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
		$url = append_sid($this->phpbb_root_path . 'cron.' . $this->php_ext, 'cron_type=' . $name . $extra);
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
