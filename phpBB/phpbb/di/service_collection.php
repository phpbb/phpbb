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

use phpbb\di\exception\multiple_service_definitions_exception;
use phpbb\di\exception\service_not_found_exception;
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
	public function getIterator(): \Iterator
	{
		return new service_collection_iterator($this);
	}

	/**
	* {@inheritdoc}
	*/
	public function offsetGet($key): mixed
	{
		return $this->container->get($key);
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
	 * @throws \RuntimeException if the service isn't found
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
					throw new multiple_service_definitions_exception('DI_MULTIPLE_SERVICE_DEFINITIONS', [$class]);
				}

				$service_id = $id;
			}
		}

		if ($service_id === null)
		{
			throw new service_not_found_exception('DI_SERVICE_NOT_FOUND', [$class]);
		}

		return $this->offsetGet($service_id);
	}
}
