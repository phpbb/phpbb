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

namespace phpbb\storage;

use phpbb\storage\exception\exception;
use phpbb\storage\adapter\adapter_interface;

class file_info
{
	/**
	 * @var \phpbb\storage\adapter\adapter_interface
	 */
	protected $adapter;

	/**
	 * Path of the file
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Stores the properties of $path file, so dont have to be consulted  multiple times.
	 * For example, when you need the width of an image, using getimagesize() you get
	 * both dimensions, so you store both here, and when you get the height, you dont have
	 * to call getimagesize() again
	 *
	 * @var array
	 */
	protected $properties;

	/**
	 * Constructor
	 *
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $adapter
	 * @param string $path
	 */
	public function __construct(adapter_interface $adapter, $path)
	{
		$this->adapter = $adapter;
		$this->path = $path;
		$this->properties = [];
	}

	/**
	 * Load propertys lazily
	 *
	 * @param string	name		The property name.
	 *
	 * @return string	Returns the property value
	 */
	public function get($name)
	{
		if (!isset($this->properties[$name]))
		{
			if (!method_exists($this->adapter, 'file_' . $name))
			{
				throw new exception('STORAGE_METHOD_NOT_IMPLEMENTED');
			}

			$this->properties = array_merge($this->properties, call_user_func([$this->adapter, 'file_' . $name], $this->path));
		}

		return $this->properties[$name];
	}

	/**
	 * Alias of \phpbb\storage\file_info->get()
	 */
	public function __get($name)
	{
		return $this->get($name);
	}
}
