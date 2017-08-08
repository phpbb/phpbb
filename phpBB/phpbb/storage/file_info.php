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

use phpbb\storage\exception\not_implemented;

class file_info
{
	/**
	 * @var \phpbb\storage\adapter\adapter_interface
	 */
	protected $adapter;

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var array
	 */
	protected $properties;

	public function __construct($adapter, $path)
	{
		$this->adapter = $adapter;
		$this->path = $path;
	}

	/**
	 * Load propertys lazily.
	 *
	 * @param string	path		The file path.
	 */
	protected function fill_properties($path)
	{
		if ($this->properties === null)
		{
			$this->properties = [];

			foreach ($this->adapter->file_properties($this->path) as $name => $value)
			{
				$this->properties[$name] = $value;
			}
		}
	}

	/**
	 * Load propertys lazily.
	 *
	 * @param string	name		The property name.
	 *
	 * @return string	Returns the property value
	 */
	public function get($name)
	{
		$this->fill_properties($this->path);

		if (!isset($this->properties[$name]))
		{
			if (!method_exists($this->adapter, 'file_' . $name))
			{
				throw new not_implemented();
			}

			$this->properties[$name] = call_user_func([$this->adapter, 'file_' . $name], $this->path);
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
