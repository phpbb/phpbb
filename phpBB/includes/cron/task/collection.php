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
* Collects cron tasks
*
* @package phpBB3
*/
class phpbb_cron_task_collection extends ArrayObject
{
	/**
	* Constructor
	*
	* @param TaggedContainerInterface $container Container object
	*/
	public function __construct(TaggedContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	* Add a cron task to the collection
	*
	* @param string $name The service name of the cron task
	* @return null
	*/
	public function add($name)
	{
		$task = $this->container->get($name);
		$task->set_name($name);
		$this->offsetSet($name, $task);
	}
}
