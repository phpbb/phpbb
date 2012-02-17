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
* Cron manager class.
*
* Finds installed cron tasks, stores task objects, provides task selection.
*
* @package phpBB3
*/
class phpbb_cron_manager
{
	/**
	* Set of phpbb_cron_task_wrapper objects.
	* Array holding all tasks that have been found.
	*
	* @var array
	*/
	protected $tasks = array();

	/**
	* Constructor. Loads all available tasks.
	*
	* @param array|Traversable $task_names Provides an iterable set of task names
	*/
	public function __construct($task_names)
	{
		$this->load_tasks($task_names);
	}

	/**
	* Loads tasks given by name, wraps them
	* and puts them into $this->tasks.
	*
	* @param array|Traversable $task_names		Array of strings
	*
	* @return void
	*/
	public function load_tasks($task_names)
	{
		foreach ($task_names as $task_name)
		{
			$task = new $task_name();
			$wrapper = new phpbb_cron_task_wrapper($task);
			$this->tasks[] = $wrapper;
		}
	}

	/**
	* Finds a task that is ready to run.
	*
	* If several tasks are ready, any one of them could be returned.
	*
	* If no tasks are ready, null is returned.
	*
	* @return phpbb_cron_task_wrapper|null
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
	* @return array		List of tasks which are ready to run (wrapped in phpbb_cron_task_wrapper).
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
	* @return phpbb_cron_task	A task corresponding to the given name, or null.
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
	* Creates an instance of parametrized cron task $name with args $args.
	* The constructed task is wrapped with cron task wrapper before being returned.
	*
	* @param string $name		The task name, which is the same as cron task class name.
	* @param array $args		Will be passed to the task class's constructor.
	*
	* @return phpbb_cron_task_wrapper|null
	*/
	public function instantiate_task($name, array $args)
	{
		$task = $this->find_task($name);
		if ($task)
		{
			// task here is actually an instance of cron task wrapper
			$class = $task->get_name();
			$task = new $class($args);
			// need to wrap the new task too
			$task = new phpbb_cron_task_wrapper($task);
		}
		return $task;
	}
}
