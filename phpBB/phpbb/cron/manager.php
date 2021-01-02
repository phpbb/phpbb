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

namespace phpbb\cron;

use phpbb\cron\task\wrapper;
use phpbb\routing\helper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Cron manager class.
*
* Finds installed cron tasks, stores task objects, provides task selection.
*/
class manager
{
	/**
	 * @var ContainerInterface
	 */
	protected $phpbb_container;

	/**
	 * @var helper
	 */
	protected $routing_helper;

	/**
	* Set of \phpbb\cron\task\wrapper objects.
	* Array holding all tasks that have been found.
	*
	* @var array
	*/
	protected $tasks = [];

	/**
	 * Flag indicating if $this->tasks contains tasks registered in the container
	 *
	 * @var bool
	 */
	protected $is_initialised_from_container = false;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var string
	 */
	protected $php_ext;

	/**
	* Constructor. Loads all available tasks.
	*
	* @param ContainerInterface $phpbb_container Container
	* @param helper $routing_helper Routing helper
	* @param string $phpbb_root_path Relative path to phpBB root
	* @param string $php_ext PHP file extension
	*/
	public function __construct(ContainerInterface $phpbb_container, helper $routing_helper, $phpbb_root_path, $php_ext)
	{
		$this->phpbb_container = $phpbb_container;
		$this->routing_helper = $routing_helper;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* Loads tasks given by name, wraps them
	* and puts them into $this->tasks.
	*
	* @param array|\Traversable $tasks		Array of instances of \phpbb\cron\task\task
	*/
	public function load_tasks($tasks)
	{
		foreach ($tasks as $task)
		{
			$this->tasks[] = $this->wrap_task($task);
		}
	}

	/**
	* Loads registered tasks from the container, wraps them
	* and puts them into $this->tasks.
	*/
	public function load_tasks_from_container()
	{
		if (!$this->is_initialised_from_container)
		{
			$this->is_initialised_from_container = true;

			$tasks = $this->phpbb_container->get('cron.task_collection');

			$this->load_tasks($tasks);
		}
	}

	/**
	* Finds a task that is ready to run.
	*
	* If several tasks are ready, any one of them could be returned.
	*
	* If no tasks are ready, null is returned.
	*
	* @return wrapper|null
	*/
	public function find_one_ready_task()
	{
		$this->load_tasks_from_container();

		shuffle($this->tasks);
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
		$this->load_tasks_from_container();

		$tasks = [];
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
	* @return wrapper	A wrapped task corresponding to the given name, or null.
	*/
	public function find_task($name)
	{
		$this->load_tasks_from_container();

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
	* Find all tasks and return them.
	*
	* @return array List of all tasks.
	*/
	public function get_tasks()
	{
		$this->load_tasks_from_container();

		return $this->tasks;
	}

	/**
	* Wraps a task inside an instance of \phpbb\cron\task\wrapper.
	*
	* @param  \phpbb\cron\task\task 			$task The task.
	* @return wrapper	The wrapped task.
	*/
	public function wrap_task(\phpbb\cron\task\task $task)
	{
		return new wrapper($task, $this->routing_helper, $this->phpbb_root_path, $this->php_ext);
	}
}
