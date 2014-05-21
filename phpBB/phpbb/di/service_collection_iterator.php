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
	public function __construct(ContainerInterface $container, $array = array(), $flags = 0)
	{
		parent::__construct($array, $flags);
		$this->container = $container;
	}

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
	* {@inheritdoc}
	*/
	public function offsetExists($index)
	{
		parent::offsetExists($index);
	}

	/**
	* {@inheritdoc}
	*/
	public function current()
	{
		$task = parent::current();
		if ($task == null)
		{
			$name = $this->key();
			$task = $this->container->get($name);
			$this->offsetSet($name, $task);
		}

		return $task;
	}
}
