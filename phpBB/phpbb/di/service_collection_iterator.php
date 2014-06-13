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
* Iterator which loads the services when they are requested
*/
class service_collection_iterator extends \ArrayIterator
{
	/**
	* @var \Symfony\Component\DependencyInjection\ContainerInterface
	*/
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
	// (even with a call to the parent):
	// 		https://bugs.php.net/bug.php?id=66834
	// 		https://bugs.php.net/bug.php?id=67067
	// But it triggers a sniffer issue that we have to skip
	// @codingStandardsIgnoreStart
	/**
	* {@inheritdoc}
	*/
	public function offsetExists($index)
	{
		parent::offsetExists($index);
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
