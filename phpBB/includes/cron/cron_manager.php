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

if (!class_exists('cron_task_wrapper'))
{
	include($phpbb_root_path . 'includes/cron/cron_task_wrapper.' . $phpEx);
}

/**
* Cron manager class.
*
* Finds installed cron tasks, stores task objects, provides task selection.
*
* @package phpBB3
*/
class cron_manager
{
	private $tasks = array();

	public function __construct()
	{
		$task_files = $this->find_cron_task_files();
		$this->load_tasks($task_files);
	}

	/**
	* Finds cron task files.
	*
	* A cron task file must follow the naming convention:
	* includes/cron/tasks/$mod/$name.php.
	* $mod is core for tasks that are part of phpbb.
	* Modifications should use their name as $mod.
	* $name is the name of the cron task.
	* Cron task is expected to be a class named cron_task_${mod}_${name}.
	*
	* Todo: consider caching found task file list in global cache.
	*/
	public function find_cron_task_files()
	{
		global $phpbb_root_path, $phpEx;

		$tasks_root_path = $phpbb_root_path . 'includes/cron/tasks';
		$dir = opendir($tasks_root_path);
		$task_dirs = array();
		while (($entry = readdir($dir)) !== false)
		{
			// ignore ., .. and dot directories
			// todo: change is_dir to account for symlinks
			if ($entry[0] == '.' || !is_dir($entry))
			{
				continue;
			}
			$task_dirs[] = $entry;
		}
		closedir($dir);

		$ext = '.' . $phpEx;
		$ext_length = strlen($ext);
		$task_files = array();
		foreach ($task_dirs as $task_dir)
		{
			$path = $phpbb_root_path . 'includes/cron/tasks/' . $task_dir;
			$dir = opendir($path);
			while (($entry = readdir($dir)) !== false && substr($entry, -$ext_length) == $ext)
			{
				$task_file = substr($entry, 0, -$ext_length);
				$task_files[] = array($task_dir, $task_file);
			}
			closedir($dir);
		}
		return $task_files;
	}

	/**
	* Checks whether $name is a valid identifier, and therefore part of valid cron task class name.
	*/
	public function is_valid_name($name)
	{
		return preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $name);
	}

	public function load_tasks($task_files)
	{
		global $phpbb_root_path, $phpEx;

		foreach ($task_files as $task_file)
		{
			list($mod, $filename) = $task_file;
			if ($this->is_valid_name($mod) && $this->is_valid_name($filename))
			{
				$class = "cron_task_${mod}_${filename}";
				if (!class_exists($class))
				{
					include($phpbb_root_path . "includes/cron/$mod/$filename.$phpEx");
				}
				$object = new $class;
				$wrapper = new cron_task_wrapper($object);
				$this->tasks[] = $wrapper;
			}
		}
	}

	/**
	* Finds a task that is ready to run.
	*
	* If several tasks are ready, any one of them could be returned.
	*/
	function find_one_ready_task()
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
	*/
	function find_all_ready_tasks()
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
	* Web runner uses this method to resolve names to tasks.
	*/
	function find_task($name)
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

	function instantiate_task($name, $args)
	{
		$task = $this->find_task($name);
		if ($task)
		{
			$class = get_class($task);
			$task = new $class($args);
		}
		return $task;
	}

	function generate_generic_task_code($cron_type)
	{
		global $phpbb_root_path, $phpEx;
		return '<img src="' . $url . '" width="1" height="1" alt="cron" />';
	}
}
