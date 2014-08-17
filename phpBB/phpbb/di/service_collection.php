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
	* @var array Contains the association between the alias and the real class.
	*/
	protected $alias = array();

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

	/**
	* {@inheritdoc}
	*/
	public function offsetExists($index)
	{
		return parent::offsetExists($index) || isset($this->alias[$index]);
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
	* @return null
	*/
	public function add($name)
	{
		$this->offsetSet($name, null);
	}

	/**
	* Add an alias to the collection
	*
	* @param string $alias_id The alias id
	* @param string $service_id The aliased service id
	* @return null
	*/
	public function add_alias($alias_id, $service_id)
	{
		$this->alias[$alias_id] = $service_id;
	}
}
