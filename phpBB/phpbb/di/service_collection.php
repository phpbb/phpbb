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
	* @var \Symfony\Component\DependencyInjection\ContainerInterface
	*/
	protected $container;

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
		return new service_collection_iterator($this);
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
		return parent::offsetExists($index);
	}
	// @codingStandardsIgnoreEnd

	/**
	* {@inheritdoc}
	*/
	public function offsetGet($index)
	{
		return $this->container->get($index);
	}

	/**
	* Add a service to the collection
	*
	* Note: Calling this method should not initialize the services.
	*
	* @param string $name The service name
	* @return null
	*/
	public function add($name)
	{
		$this->offsetSet($name, null);
	}

	/**
	* Get service names
	*
	* Note: Calling this method should not initialize the services.
	*
	* @return array	Array of service names in the collection
	*/
	public function get_service_names()
	{
		$copy_array = $this->getArrayCopy();
		return array_keys($copy_array);
	}
}
