<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\di;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Collection of services to be configured at container compile time.
*
* @package phpBB3
*/
class service_collection extends \ArrayObject
{
	/**
	* Constructor
	*
	* @param ContainerInterface $container Container object
	*/
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	* {@inheritdoc}
	*/
	public function getIterator()
	{
		return new service_collection_iterator($this->container, $this);
	}

	// Because of a PHP issue we have to redefine offsetExists
	// (even <with a call to the parent):
	// 		https://bugs.php.net/bug.php?id=66834
	// 		https://bugs.php.net/bug.php?id=67067
	// But it triggers a sniffer issue that we have to skip
	// @codingStandardsIgnoreStart
	/**
	* {@inheritdoc}
	*/
	public function offsetExists($index)
	{
		return parent::offsetExists($index);
	}
	// @codingStandardsIgnoreEnd

	/**
	* {@inheritdoc}
	*/
	public function offsetGet($index)
	{
		$task = parent::offsetGet($index);
		if ($task == null)
		{
			$task = $this->container->get($index);
			$this->offsetSet($index, $task);
		}

		return $task;
	}

	/**
	* Add a service to the collection
	*
	* @param string $name The service name
	* @return null
	*/
	public function add($name)
	{
		$this->offsetSet($name, null);
	}
}
