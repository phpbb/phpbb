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

namespace phpbb\files;

class factory
{
	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerInterface
	 */
	private $container;

	/**
	 * Constructor
	 *
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 */
	public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * Get files service
	 *
	 * @param string $name Service name
	 *
	 * @return object|false Requested service or false if service could not be
	 *				found by the container
	 */
	public function get($name)
	{
		$service = false;

		try
		{
			$service = $this->container->get($name);
		}
		catch (\Exception $e)
		{
			// do nothing
		}

		return $service;
	}

	/**
	 * Magic function for handling get calls, e.g. get_fileupload() or
	 * get_filespec() and turning them into call for files. services like
	 * files.fileupload.
	 *
	 * @param string $name Name of called function
	 * @param mixed $arguments Possible supplied arguments
	 *
	 * @return object|false Requested service or false if service could not be
	 *				found by the container
	 */
	public function __call($name, $arguments)
	{
		if (substr($name, 0, 4) === 'get_')
		{
			return $this->get('files.' . substr($name, 4));
		}
		else
		{
			return false;
		}
	}
}
