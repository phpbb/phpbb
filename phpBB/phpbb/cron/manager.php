<?php
/**
*
* @package phpBB3
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\cron;

/**
* Cron manager class.
*
* Finds installed cron tasks, stores task objects, provides task selection.
*
* @package phpBB3
*/
class manager
{
	/**
	* Set of \phpbb\cron\task\wrapper objects.
	* Array holding all tasks that have been found.
	*
	* @var array
	*/
	protected $tasks = array();

	protected $phpbb_root_path;
	protected $php_ext;

	/**
	* Constructor. Loads all available tasks.
	*
	* @param array|Traversable $tasks Provides an iterable set of task names
	*/
	public function __construct($tasks, $phpbb_root_path, $php_ext)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->load_tasks($tasks);
	}

	/**
	* Loads tasks given by name, wraps them
	* and puts them into $this->tasks.
	*
	* @param array|Traversable $tasks		Array of instances of \phpbb\cron\task\task
	*
	* @return null
	*/
	public function load_tasks($tasks)
	{
		foreach ($tasks as $task)
		{
			$this->tasks[] = $this->wrap_task($task);
		}
	}

	/**
	* Finds a task that is ready to run.
	*
	* If several tasks are ready, any one of them could be returned.
	*
	* If no tasks are ready, null is returned.
	*
	* @return \phpbb\cron\task\wrapper|null
	*/
	public function find_one_ready_task()
	{
		foreach ($this->tasks as $task)
		{
			if ($task->is_ready())
			{
				return $task;
			}
		}
		return null;
	}

	/**
	* Finds all tasks that are ready to run.
	*
	* @return array		List of tasks which are ready to run (wrapped in \phpbb\cron\task\wrapper).
	*/
	public function find_all_ready_tasks()
	{
		$tasks = array();
		foreach ($this->tasks as $task)
		{
			if ($task->is_ready())
			{
				$tasks[] = $task;
			}
		}
		return $tasks;
	}

	/**
	* Finds a task by name.
	*
	* If there is no task with the specified name, null is returned.
	*
	* Web runner uses this method to resolve names to tasks.
	*
	* @param string				$name Name of the task to look up.
	* @return \phpbb\cron\task\task	A task corresponding to the given name, or null.
	*/
	public function find_task($name)
	{
		foreach ($this->tasks as $task)
		{
			if ($task->get_name() == $name)
			{
				return $task;
			}
		}
		return null;
	}

	/**
	* Wraps a task inside an instance of \phpbb\cron\task\wrapper.
	*
	* @param  \phpbb\cron\task\task 			$task The task.
	* @return \phpbb\cron\task\wrapper	The wrapped task.
	*/
	public function wrap_task(\phpbb\cron\task\task $task)
	{
		return new \phpbb\cron\task\wrapper($task, $this->phpbb_root_path, $this->php_ext);
	}
}
