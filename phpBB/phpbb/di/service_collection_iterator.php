<?php
/**
*
* @package phpBB3
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\di;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Iterator which loads the services when they are requested
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
