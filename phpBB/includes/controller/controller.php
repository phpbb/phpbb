<?php
/**
*
* @package controller
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
    exit;
}

/**
* @package controller
*/
class phpbb_controller
{
	/**
	* Extension Manager object
	* @var phpbb_extension_manager
	*/
	protected $extension_manager;

	/**
	* Extension Finder object
	* @var phpbb_extension_finder
	*/
	protected $finder;

	/**
	* Cache object
	* @var phpbb_cache_driver_base
	*/
	protected $cache;

	/**
	* Controllers mapped to an array
	* @var array
	*/
	protected $controllers;

	/**
	* Constructor class
	*
	* @param phpbb_extension_manager $extension_manager Extension Manager object
	* @param phpbb_cache_driver_base $cache Cache object
	*/
	public function __construct(phpbb_extension_manager $extension_manager, phpbb_cache_service $cache)
	{
		$this->extension_manager = $extension_manager;
		$this->finder = $extension_manager->get_finder();
		$this->cache = $cache;

		$this->controllers = $this->get_controllers_map();
	}

	/**
	* Get all controllers mapped into an array
	*
	* @return array Associative array of controller_access_name => controller_class
	*/
	public function get_controllers_map()
	{
		if (($controllers = $this->cache->get('_controllers')) === false)
		{
			$found_controllers = $this->finder
				->core_suffix('_controller')
				->core_path('includes/')
				->extension_directory('/controllers')
				->get_classes();

			$controllers = array();

			foreach ($found_controllers as $controller)
			{
				$controller_object = new $controller;
				if ($controller_object instanceof phpbb_controller_base)
				{
					$controllers[$controller_object->get_access_name()] = $controller;
				}
			}

			$this->cache->put('_controllers', $controllers);
		}

		return $controllers;
	}

	/**
	* Get controller array
	*
	* @return array Associative array of controller_access_name => controller_class
	*/
	public function get_controllers()
	{
		return $this->controllers ?: array();
	}
}
