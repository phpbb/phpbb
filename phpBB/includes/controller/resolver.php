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
use Symfony\Component\DependencyInjection\ContainerInterface;
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
	* ContainerInterface object
	* @var ContainerInterface
	*/
	protected $container;

	/**
	* Construct method
	*
	* @param phpbb_user $user User Object
	* @param ContainerInterface $container ContainerInterface object
	*/
	public function __construct(phpbb_user $user, ContainerInterface $container)
	{
		$this->user = $user;
		$this->container = $container;
	}

	/**
	* Load a controller callable
	*
	* @param Symfony\Component\HttpFoundation\Request $request Symfony Request object
	* @return bool|Callable Callable or false
	* @throws phpbb_controller_exception
	*/
	public function getController(Request $request)
	{
		$controller = $request->attributes->get('_controller');

		if (!$controller)
		{
			throw new phpbb_controller_exception($this->user->lang['CONTROLLER_NOT_SPECIFIED']);
		}

		// Require a method name along with the service name
		if (stripos($controller, ':') === false)
		{
			throw new phpbb_controller_exception($this->user->lang['CONTROLLER_METHOD_NOT_SPECIFIED']);
		}

		list($service, $method) = explode(':', $controller);

		if (!$this->container->has($service))
		{
			throw new phpbb_controller_exception($this->user->lang('CONTROLLER_SERVICE_UNDEFINED', $service));
		}

		$controller_object = $this->container->get($service);

		return array($controller_object, $method);
	}

	/**
	* Dependencies should be specified in the service definition and can be
	* then accessed in __construct(). Arguments are sent through the URL path
	* and should match the parameters of the method you are using as your
	* controller.
	*
	* @param Symfony\Component\HttpFoundation\Request $request Symfony Request object
	* @param mixed $controller A callable (controller class, method)
	* @return bool False
	* @throws phpbb_controller_exception
	*/
	public function getArguments(Request $request, $controller)
	{
		// At this point, $controller contains the object and method name
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
			else if ($param->getClass() && $param->getClass()->isInstance($request))
			{
				$arguments[] = $request;
			}
			else if ($param->isDefaultValueAvailable())
			{
				$arguments[] = $param->getDefaultValue();
			}
			else
			{
				throw new phpbb_controller_exception($this->user->lang('CONTROLLER_ARGUMENT_VALUE_MISSING', $param->getPosition() + 1, get_class($object) . ':' . $method, $param->name));
			}
		}

		return $arguments;
	}
}
