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
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
	* ContainerBuilder object
	* @var ContainerBuilder
	*/
	protected $container;

	/**
	* Construct method
	*
	* @param phpbb_user $user User Object
	* @param ContainerBuilder $container ContainerBuilder object
	*/
	public function __construct(phpbb_user $user, ContainerBuilder $container)
	{
		$this->user = $user;
		$this->container = $container;
	}

	/**
	* Load a controller callable
	*
	* @param Symfony\Component\HttpFoundation\Request $request Symfony Request object
	* @return bool|Callable Callable or false
	* @throws RuntimeException
	*/
	public function getController(Request $request)
	{
		$controller_service = $request->attributes->get('_controller');

		if (!$controller_service)
		{
			throw new RuntimeException($this->user->lang['CONTROLLER_NOT_SPECIFIED']);
		}

		// Require a method name along with the service name
		if (stripos($controller_service, ':') === false)
		{
			throw new RuntimeException($this->user->lang['CONTROLLER_METHOD_NOT_SPECIFIED']);
		}

		list($service, $method) = explode(':', $controller_service);

		if (!$this->container->has($service))
		{
			throw new RuntimeException($this->user->lang['CONTROLLER_SERVICE_UNDEFINED']);
		}

		$controller = $this->container->get($service);

		return array($controller, $method);
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
