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
	* User object
	* @var phpbb_user
	*/
	protected $user;

	/**
	* Controllers mapped to an array
	* @var array
	*/
	protected $controllers;

	/**
	* Constructor class
	*
	* @param string $controller Access name for the controller to load
	* @param phpbb_extension_manager $extension_manager Extension Manager object
	* @param phpbb_cache_driver_base $cache Cache object
	* @param phpbb_user $user User object
	*/
	public function __construct($controller, phpbb_extension_manager $extension_manager, phpbb_cache_service $cache, phpbb_user $user)
	{
		$this->extension_manager = $extension_manager;
		$this->finder = $extension_manager->get_finder();
		$this->cache = $cache;
		$this->user = $user;

		$this->controllers = $this->get_controllers();
		$this->load_controller($controller);
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
				$controllers[$controller_object->get_access_name()] = $controller;
			}

			$this->cache->put('_controllers', $controllers);
		}

		return $controllers;
	}

	/**
	* Get a single controller class from an access name
	*
	* @param string $access_name The access name associated with the controller class
	*/
	public function load_controller($access_name)
	{
		$controller_class = isset($this->controllers[$access_name]) ? $this->controllers[$access_name] : false;

		if ($controller_class === false)
		{
			send_status_line(404, 'Not Found');
			trigger_error($this->user->lang('CONTROLLER_NOT_FOUND', $controller));
		}

		$controller_object = new $controller_class;
		if (!($controller_object instanceof phpbb_controller_interface))
		{
			send_status_line(500, 'Internal Server Error');
			trigger_error($this->user->lang('CONTROLLER_BAD_TYPE', $controller_class));
		}

		$controller_object->handle();
		exit_handler();
	}

	/**
	* Get controller array
	*
	* @return array Associative array of controller_access_name => controller_class
	*/
	public function get_controllers()
	{
		if (empty($this->controllers))
		{
			$this->controllers = $this->get_controllers_map();
		}

		return $this->controllers;
	}
}
