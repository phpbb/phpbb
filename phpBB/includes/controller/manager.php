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
class phpbb_controller_manager
{
	/**
	* Code for exception thrown when the controller does not exist
	*/
	const CONTROLLER_NOT_FOUND = 1;

	/**
	* Code for exception thrown when the controller is not the correct type
	*/
	const CONTROLLER_BAD_TYPE = 2;

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
	* @param Traversable $controller_classes Any traversable object including
	* 										 an array providing controller
	*										 classes as strings
	* @param phpbb_cache_driver_interface $cache Cache object
	* @param phpbb_user $user User object
	*/
	public function __construct($controller_classes, phpbb_cache_driver_interface $cache, phpbb_user $user)
	{
		$this->cache = $cache;
		$this->user = $user;
		$this->get_controller_map($controller_classes);
	}

	/**
	* Get all controllers mapped into an array
	*
	* @return array Associative array of controller_access_name => controller_class
	*/
	public function get_controller_map($controller_classes)
	{
		if (($this->controllers = $this->cache->get('_controllers')) === false)
		{
			$this->controllers = array();

			foreach ($controller_classes as $controller)
			{
				if (class_exists($controller))
				{
					$controller_object = new $controller;
					$this->controllers[$controller_object->get_access_name()] = $controller;				
				}
			}

			$this->cache->put('_controllers', $this->controllers);
		}		

		return $this->controllers;
	}

	/**
	* Get a single controller class from an access name
	* Does not handle errors or exit
	*
	* @param string $access_name The access name associated with the controller class
	* @return null
	* @throws RuntimeException
	*/
	public function get_controller($access_name)
	{
		$controller_class = isset($this->controllers[$access_name]) ? $this->controllers[$access_name] : false;

		if ($controller_class === false)
		{
			throw new RuntimeException($this->user->lang('CONTROLLER_NOT_FOUND', $access_name), self::CONTROLLER_NOT_FOUND);
		}

		$controller_object = new $controller_class;
		if (!($controller_object instanceof phpbb_controller_interface))
		{
			throw new RuntimeException($this->user->lang('CONTROLLER_BAD_TYPE', $controller_class), self::CONTROLLER_BAD_TYPE);
		}

		$controller_object->handle();
	}

	/**
	* Load a controller, with error and exit handling
	*
	* @param string $access_name The access name associated with the controller class
	* @return null
	*/
	public function load_controller($access_name)
	{
		try
		{
			$this->get_controller($access_name);
		}
		catch (RuntimeException $e)
		{
			switch ($e->getCode())
			{
				case self::CONTROLLER_NOT_FOUND:
				default:
					$status_code = 404;
					$status_message = 'Not Found';
				break;

				case self::CONTROLLER_BAD_TYPE:
					$status_code = 500;
					$status_message = 'Internal Server Error';
				break;
			}

			send_status_line($status_code, $status_message);
			trigger_error($e->getMessage());
		}

		exit_handler();
	}
}
