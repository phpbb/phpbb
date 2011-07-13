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
	* An extension manager to search for cron tasks in extensions.
	* @var phpbb_extension_manager
	*/
	protected $extension_manager;

	/**
	* Constructor. Loads all available tasks.
	*
	* Tasks will be looked up in the core task directory located in
	* includes/cron/task/core/ and in extensions. Task classes will be
	* autoloaded and must be named according to autoloading naming conventions.
	*
	* Tasks in extensions must be located in a directory called cron or a subdir
	* of a directory called cron. The class and filename must end in a _task
	* suffix.
	*
	* @param phpbb_extension_manager $extension_manager phpBB extension manager
	*/
	public function __construct(phpbb_extension_manager $extension_manager)
	{
		$this->extension_manager = $extension_manager;

		$task_names = $this->find_cron_task_names();
		$this->load_tasks($task_names);
	}

	/**
	* Finds cron task names using the extension manager.
	*
	* All PHP files in includes/cron/task/core/ are considered tasks. Tasks
	* in extensions have to be located in a directory called cron or a subdir
	* of a directory called cron. The class and filename must end in a _task
	* suffix.
	*
	* @return array		List of task names
	*/
	public function find_cron_task_names()
	{
		$finder = $this->extension_manager->get_finder();

		return $finder
			->suffix('_task')
			->directory('/cron')
			->default_path('includes/cron/task/core/')
			->default_suffix('')
			->default_directory('')
			->get_classes();
	}

	/**
	* Loads tasks given by name, wraps them
	* and puts them into $this->tasks.
	*
	* @param array $task_names		Array of strings
	*
	* @return void
	*/
	public function load_tasks(array $task_names)
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
