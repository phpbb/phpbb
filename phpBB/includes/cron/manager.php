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
	* Directory containing cron tasks
	* @var string
	*/
	protected $task_path;

	/**
	* PHP file extension
	* @var string
	*/
	protected $phpEx;

	/**
	* Cache driver
	* @var phpbb_cache_driver_interface
	*/
	protected $cache;

	/**
	* Constructor. Loads all available tasks.
	*
	* @param string $task_path                   Directory containing cron tasks
	* @param string $phpEx                       PHP file extension
	* @param phpbb_cache_driver_interface $cache Cache for task names (optional)
	*/
	public function __construct($task_path, $phpEx, phpbb_cache_driver_interface $cache = null)
	{
		$this->task_path = $task_path;
		$this->phpEx = $phpEx;
		$this->cache = $cache;

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
	* @return array		List of task names
	*/
	public function find_cron_task_names()
	{
		if ($this->cache)
		{
			$task_names = $this->cache->get('_cron_tasks');

			if ($task_names !== false)
			{
				return $task_names;
			}
		}

		$task_names = array();
		$ext = '.' . $this->phpEx;
		$ext_length = strlen($ext);

		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->task_path));

		foreach ($iterator as $fileinfo)
		{
			$file = preg_replace('#^' . preg_quote($this->task_path, '#') . '#', '', $fileinfo->getPathname());

			// skip directories and files direclty in the task root path
			if ($fileinfo->isFile() && strpos($file, '/') !== false)
			{
				$task_name = str_replace('/', '_', substr($file, 0, -$ext_length));
				if (substr($file, -$ext_length) == $ext && $this->is_valid_name($task_name))
				{
					$task_names[] = 'phpbb_cron_task_' . $task_name;
				}
			}
		}

		if ($this->cache)
		{
			$this->cache->put('_cron_tasks', $task_names, 3600);
		}

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
