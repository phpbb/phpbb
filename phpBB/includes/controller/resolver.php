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

use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* Controller manager class
* @package phpBB3
*/
class phpbb_controller_resolver implements ControllerResolverInterface
{
	/**
	* Controller provider
	* @var phpbb_controller_provider
	*/
	protected $provider;

	/**
	* Cache object
	* @var phpbb_cache_driver_interface
	*/
	protected $cache;

	/**
	* User object
	* @var phpbb_user
	*/
	protected $user;

	/**
	* Controllers
	* @var array
	*/
	protected $controllers;

	/**
	* Construct method
	*
	* @param phpbb_extension_manager $extension_manager Extension Manager object
	* @param phpbb_cache_driver_interface $cache Cache object
	* @param phpbb_user $user User Object
	*/
	public function __construct(phpbb_extension_manager $extension_manager, phpbb_cache_driver_interface $cache, phpbb_user $user)
	{
		$route_provider = new phpbb_controller_route_provider($extension_manager->get_finder());
		$this->provider = new phpbb_controller_provider($route_provider->find());
		$this->cache = $cache;
		$this->user = $user;

		// The following method internally sets the "controllers" property
		$this->load_controller_map();
	}

	/**
	* Load the map of controllers and access names into an array
	*
	* @return array
	*/
	public function load_controller_map()
	{
		$this->controllers = $this->provider->find();
	}


	/**
	* Load and execute a controller, with error handling
	*
	* @param Symfony\Component\HttpFoundation\Request $request Symfony Request object
	*/
	public function load(Request $request)
	{
		try
		{
			$controller_class = $this->getController($request);
			$controller = new $controller_class;
			$response = call_user_func(array($controller, 'handle'));

			if (!$response instanceof Response)
			{
				trigger_error($this->user->lang());
			}
		}
		catch (RuntimeException $e)
		{
			trigger_error($e->getMessage());
		}
	}

	/**
	* Load a controller class name, without error handling
	*
	* @param Symfony\Component\HttpFoundation\Request $request Symfony Request object
	* @return string Controller class name
	* @throws RuntimeException
	*/
	public function getController(Request $request)
	{
		$controller = $request->query->get('controller');

		if (!$controller)
		{
			throw new RuntimeException('no controller specified');
		}

		if (!isset($this->controllers[$controller]) && !in_array($controller, $this->controllers))
		{
			throw new RuntimeException('controller <strong>' . $controller . '</strong> not found');
		}

		return isset($this->controllers[$controller]) ? $this->controllers[$controller] : $controller;
	}

	/**
	* We do not use this method (expected by the ControllerResolverInterface)
	* because we will use phpBB's request class to grab request information
	*
	* @param Symfony\Component\HttpFoundation\Request $request Symfony Request object
	* @param string $controller Controller class name
	* @return bool False
	*/
	public function getArguments(Request $request, $controller)
	{
		return false;
	}
}
