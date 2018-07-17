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
use phpbb\db\driver\driver_interface;

class file_info
{
	/**
	 * @var \phpbb\storage\adapter\adapter_interface
	 */
	protected $adapter;

	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var string
	 */
	protected $storage_name;

	/**
	 * @var string
	 */
	protected $storage_table;

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
	public function __construct(adapter_interface $adapter, driver_interface $db, $storage_name, $storage_table, $path)
	{
		$this->adapter = $adapter;
		$this->db = $db;
		$this->storage_name = $storage_name;
		$this->storage_table = $storage_table;
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
		// Try to read properties from db if isn't set
		if (!isset($this->properties[$name]))
		{
			$sql = 'SELECT metadata
				FROM ' .  $this->storage_table . "
				WHERE storage = '" . $this->db->sql_escape($this->storage_name) . "'
					AND file_path = '" . $this->db->sql_escape($this->path) . "'";
			$result = $this->db->sql_query($sql);
			$metadata = json_decode($this->db->sql_fetchfield('metadata'), true);
			$this->db->sql_freeresult($result);

			// If is not a valid json (for example when the field is empty)
			if (!is_array($metadata))
			{
				$metadata = [];
			}

			// If the attribute we are looking for isn't in the database,
			// check with the adapter and update the database
			if (!isset($metadata[$name]))
			{
				if (!method_exists($this->adapter, 'file_' . $name))
				{
					throw new exception('STORAGE_METHOD_NOT_IMPLEMENTED');
				}

				$metadata = array_merge($metadata, call_user_func([$this->adapter, 'file_' . $name], $this->path));

				// Save new property to db
				$sql = 'UPDATE ' .  $this->storage_table . "
					SET metadata = '" .  $this->db->sql_escape(json_encode($metadata)) . "'
					WHERE storage = '" . $this->db->sql_escape($this->storage_name) . "'
						AND file_path = '" . $this->db->sql_escape($this->path) . "'";
				$this->db->sql_query($sql);
			}

			$this->properties = $metadata;
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
