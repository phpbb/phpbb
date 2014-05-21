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

namespace phpbb\di;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Collection of services to be configured at container compile time.
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

	/**
	* {@inheritdoc}
	*/
	public function offsetGet($index)
	{
		if (($task = parent::offsetGet($index)) == null)
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


/**
* Iterator which load the services when they are requested
*
* @package phpBB3
*/
class service_collection_iterator extends \ArrayIterator
{
	protected $container;

	/**
	* Construct an ArrayIterator for service_collection
	*
	* @param ContainerInterface $container Container object
	* @param array $array The array or object to be iterated on.
	* @param int $flags Flags to control the behaviour of the ArrayObject object.
	* @see ArrayObject::setFlags()
	*/
	public function __construct(ContainerInterface $container, $array = array() , $flags = 0)
	{
		parent::__construct($array, $flags);
		$this->container = $container;
	}

	/**
	* {@inheritdoc}
	*/
	public function offsetGet($index)
	{
		if (($task = parent::offsetGet($index)) == null)
		{
			$task = $this->container->get($index);
			$this->offsetSet($index, $task);
		}

		return $task;
	}


	/**
	* {@inheritdoc}
	*/
	public function current()
	{
		if (($task = parent::current()) == null)
		{
			$name = $this->key();
			$task = $this->container->get($name);
			$this->offsetSet($name, $task);
		}

		return $task;
	}
}
