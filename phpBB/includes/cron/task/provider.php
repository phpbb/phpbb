<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
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

use Symfony\Component\DependencyInjection\TaggedContainerInterface;

/**
* Provides cron manager with tasks
*
* Finds installed cron tasks and makes them available to the cron manager.
*
* @package phpBB3
*/
class phpbb_cron_task_provider implements IteratorAggregate
{
	private $container;

	public function __construct(TaggedContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	* Retrieve an iterator over all items
	*
	* @return ArrayIterator An iterator for the array of cron tasks
	*/
	public function getIterator()
	{
		$definitions = $this->container->findTaggedServiceIds('cron.task');

		$tasks = array();
		foreach ($definitions as $name => $definition)
		{
			$task = $this->container->get($name);
			if ($task instanceof phpbb_cron_task_base)
			{
				$task->set_name($name);
			}

			$tasks[] = $task;
		}

		return new ArrayIterator($tasks);
	}
}
