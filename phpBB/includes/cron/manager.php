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
	private $tasks = array();

	/**
	* Constructor. Loads all available tasks.
	*
	* @return void
	*/
	public function __construct()
	{
		$task_names = $this->find_cron_task_names();
		$this->load_tasks($task_names);
	}

	/**
	* Finds cron task names.
	*
	* A cron task file must follow the naming convention:
	* includes/cron/task/$mod/$name.php.
	* $mod is core for tasks that are part of phpbb.
	* Modifications should use their name as $mod.
	* $name is the name of the cron task.
	* Cron task is expected to be a class named phpbb_cron_task_${mod}_${name}.
	*
	* Todo: consider caching found task file list in global cache.
	*
	* @return array		List of task names
	*/
	public function find_cron_task_names()
	{
		global $phpbb_root_path, $phpEx;

		$tasks_root_path = $phpbb_root_path . 'includes/cron/task/';

		$task_names = array();
		$ext = '.' . $phpEx;
		$ext_length = strlen($ext);

		$dh = opendir($tasks_root_path);
		while (($mod = readdir($dh)) !== false)
		{
			// ignore ., .. and dot directories
			// todo: change is_dir to account for symlinks
			if ($mod[0] == '.' || !is_dir($tasks_root_path . $mod))
			{
				continue;
			}

			$dh2 = opendir($tasks_root_path . $mod);
			while (($file = readdir($dh2)) !== false)
			{
				$task_name = substr($file, 0, -$ext_length);
				if (substr($file, -$ext_length) == $ext && $this->is_valid_name($mod) && $this->is_valid_name($task_name))
				{
					$full_task_name = $mod . '_' . $task_name;
					$task_names[] = $full_task_name;
				}
			}
			closedir($dh2);
		}
		closedir($dh);

		return $task_names;
	}

	/**
	* Checks whether $name is a valid identifier, and
	* therefore part of valid cron task class name.
	*
	* @param string $name		Name to check
	*
	* @return bool
	*/
	public function is_valid_name($name)
	{
		return (bool) preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $name);
	}

	/**
	* Loads tasks given by name, wraps them
	* and puts them into $this->tasks.
	*
	* @param array $task_names		Array of strings
	*
	* @return void
	*/
	public function load_tasks($task_names)
	{
		foreach ($task_names as $task_name)
		{
			$class = "phpbb_cron_task_$task_name";
			$task = new $class();
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
	public function instantiate_task($name, $args)
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
