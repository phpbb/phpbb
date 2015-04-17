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
	 * @param \phpbb\di\service_collection                              $plugins
	 */
	public function __construct(\phpbb\di\service_collection $plugins)
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
		$this->merge($nav_arry, $this->menu_collection);
	}

	/**
	 * Recursive array merge
	 *
	 * This function is necessary to be able to replace the options of
	 * already set navigation items.
	 *
	 * @param array	$array_to_merge
	 * @param array	$array_to_merge_into
	 */
	private function merge(&$array_to_merge, &$array_to_merge_into)
	{
		foreach ($array_to_merge as $key => $value)
		{
			if (isset($array_to_merge_into[$key]))
			{
				if (is_array($array_to_merge_into[$key]) && is_array($value))
				{
					$this->merge($value, $array_to_merge_into[$key]);
				}
				else
				{
					$array_to_merge_into[$key] = $value;
				}
			}
			else
			{
				$array_to_merge_into[$key] = $value;
			}
		}
	}
}
