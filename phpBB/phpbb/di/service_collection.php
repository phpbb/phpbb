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
	* Add a service to the collection
	*
	* @param string $name The service name
	* @return null
	*/
	public function add($name)
	{
		$task = $this->container->get($name);

		$this->offsetSet($name, $task);
	}
}
