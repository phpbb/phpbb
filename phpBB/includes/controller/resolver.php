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
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;

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
		$context = new RequestContext();
		$context->fromRequest($request);
		try
		{
			$matcher = new UrlMatcher($this->controllers, $context);
			$request->attributes->add($matcher->match($request->getPathInfo()));
		}
		catch (RuntimeException $e)
		{
			// This is done because the exception thrown when a path is not
			// matched does not have a message. Basically, this says that if
			// no message was given, use the language string supplied instead
			throw new RuntimeException($e->getMessage() ?: $this->user->lang('CONTROLLER_NOT_FOUND'), 404, $e);
		}

		$controller_service = $request->attributes->get('_controller');
		$controller_name = $request->attributes->get('_route');

		if (!$controller_service)
		{
			throw new RuntimeException($this->user->lang['CONTROLLER_NOT_SPECIFIED'], 404);
		}

		// Allow individual controller methods to be used as controllers
		// Otherwise, return the service name
		if (stripos($controller_service, ':') !== false)
		{
			list($service, $method) = explode(':', $controller_service);
			return array($service, $method);
		}

		return $controller_service;
	}

	/**
	* Dependencies should be specified in the service definition and can be
	* then accessed in __construct(). Arguments are sent through the URL path
	* and should match the parameters of the method you are using as your
	* controller.
	*
	* @param Symfony\Component\HttpFoundation\Request $request Symfony Request object
	* @param string $controller Controller class name
	* @return bool False
	*/
	public function getArguments(Request $request, $controller)
	{
		// At this point, $controller contains the object and method name
		// If no method name was specified, it defaults to handle()
		list($object, $method) = $controller;
		$mirror = new ReflectionMethod($object, $method);

		$arguments = array();
		$parameters = $mirror->getParameters();
		$attributes = $request->attributes->all();
		foreach ($parameters as $param)
		{
			if (array_key_exists($param->name, $attributes))
			{
				$arguments[] = $attributes[$param->name];
			}
			else if ($param->isDefaultValueAvailable())
			{
				$arguments[] = $param->getDefaultValue();
			}
			else
			{
				throw new RuntimeException(/** @todo Language string complaining that there isn't a value for a required argument. */);
			}
		}

		return $arguments;
	}
}
