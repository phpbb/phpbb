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
	* @param string $base_path Base path to prepend to all paths
	*/
	public function __construct(phpbb_controller_route_provider $route_provider, phpbb_cache_driver_interface $cache, phpbb_user $user, $base_path = '')
	{
		$this->provider = new phpbb_controller_provider($route_provider->find());
		$this->cache = $cache;
		$this->user = $user;

		// The following method internally sets the "controllers" property
		$this->load_controller_map($base_path);
	}

	/**
	* Load the map of controllers and access names into an array
	*
	* @param string $base_path Base path to prepend to all paths
	* @return array
	*/
	public function load_controller_map($base_path = '')
	{
		$this->controllers = $this->provider->find($base_path);
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
			throw new RuntimeException($this->user->lang['CONTROLLER_NOT_SPECIFIED']);
		}

		if (!isset($this->controllers[$controller]))
		{
			throw new RuntimeException($this->user->lang('CONTROLLER_NOT_FOUND', $controller));
		}

		return isset($this->controllers[$controller]) ? $this->controllers[$controller] : $controller;
	}

	/**
	* Arguments/Dependencies are defined in the controller's service
	* definition in the extension's config/services.yml file. As such, we do
	* not use this method. It is included because it is required by the
	* interface.
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
