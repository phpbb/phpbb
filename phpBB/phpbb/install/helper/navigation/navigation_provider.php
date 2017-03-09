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

namespace phpbb\install\helper\navigation;

use phpbb\di\service_collection;

/**
 * Installers navigation provider
 */
class navigation_provider
{
	/**
	 * @var array
	 */
	private $menu_collection;

	/**
	 * Constructor
	 *
	 * @param service_collection		$plugins
	 */
	public function __construct(service_collection $plugins)
	{
		$this->menu_collection	= array();

		foreach ($plugins as $plugin => $plugin_instance)
		{
			$this->register($plugin_instance);
		}
	}

	/**
	 * Returns navigation array
	 *
	 * @return array
	 */
	public function get()
	{
		return $this->menu_collection;
	}

	/**
	 * Registers a navigation provider's navigation items
	 *
	 * @param navigation_interface	$navigation
	 */
	public function register(navigation_interface $navigation)
	{
		$nav_arry = $navigation->get();
		$this->menu_collection = $this->merge($nav_arry, $this->menu_collection);
	}

	/**
	 * Set a property in the navigation array
	 *
	 * @param array	$nav_element	Array to the navigation elem
	 * @param array	$property_array	Array with the properties to set
	 */
	public function set_nav_property($nav_element, $property_array)
	{
		$array_pointer = array();
		$array_root_pointer = &$array_pointer;
		foreach ($nav_element as $array_path)
		{
			$array_pointer[$array_path] = array();
			$array_pointer = &$array_pointer[$array_path];
		}

		$array_pointer = $property_array;

		$this->menu_collection = $this->merge($array_root_pointer, $this->menu_collection);
	}

	/**
	 * Recursive array merge
	 *
	 * This function is necessary to be able to replace the options of
	 * already set navigation items.
	 *
	 * @param array	$array_to_merge
	 * @param array	$array_to_merge_into
	 *
	 * @return array	Merged array
	 */
	private function merge($array_to_merge, $array_to_merge_into)
	{
		$merged_array = $array_to_merge_into;

		foreach ($array_to_merge as $key => $value)
		{
			if (isset($array_to_merge_into[$key]))
			{
				if (is_array($array_to_merge_into[$key]) && is_array($value))
				{
					$merged_array[$key] = $this->merge($value, $array_to_merge_into[$key]);
				}
				else
				{
					$merged_array[$key] = $value;
				}
			}
			else
			{
				$merged_array[$key] = $value;
			}
		}

		return $merged_array;
	}
}
