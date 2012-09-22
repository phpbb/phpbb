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

/**
* Controller manager class
* @package phpBB3
*/
class phpbb_controller_resolver implements ControllerResolverInterface
{
	/**
	* User object
	* @var phpbb_user
	*/
	protected $user;

	/**
	* Construct method
	*
	* @param phpbb_user $user User Object
	*/
	public function __construct(phpbb_user $user)
	{
		$this->user = $user;
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
		}
		else
		{
			$service = $controller_service;
			$method = 'handle';
		}

		return array($service, $method);
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
				throw new RuntimeException($user->lang('CONTROLLER_ARGUMENT_VALUE_MISSING', $param->getPosition() + 1, get_class($object) . ':' . $method, $param->name));
			}
		}

		return $arguments;
	}
}
