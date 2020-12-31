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
	* @var ContainerInterface
	*/
	protected $container;

	/**
	* @var array
	*/
	protected $service_classes;

	/**
	* Constructor
	*
	* @param ContainerInterface $container Container object
	*/
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->service_classes = array();
	}

	/**
	* {@inheritdoc}
	*/
	public function getIterator()
	{
		return new service_collection_iterator($this);
	}

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
	* @param string $name The service name
	* @return void
	*/
	public function add($name)
	{
		$this->offsetSet($name, false);
	}

	/**
	* Add a service's class to the collection
	*
	* @param string	$service_id
	* @param string	$class
	*/
	public function add_service_class($service_id, $class)
	{
		$this->service_classes[$service_id] = $class;
	}

	/**
	* Get services' classes
	*
	* @return array
	*/
	public function get_service_classes()
	{
		return $this->service_classes;
	}

	/**
	 * Returns the service associated to a class
	 *
	 * @return mixed
	 * @throw \RuntimeException if the
	 */
	public function get_by_class($class)
	{
		$service_id = null;

		foreach ($this->service_classes as $id => $service_class)
		{
			if ($service_class === $class)
			{
				if ($service_id !== null)
				{
					throw new \RuntimeException('More than one service definitions found for class "'.$class.'" in collection.');
				}

				$service_id = $id;
			}
		}

		if ($service_id === null)
		{
			throw new \RuntimeException('No service found for class "'.$class.'" in collection.');
		}

		return $this->offsetGet($service_id);
	}
}
