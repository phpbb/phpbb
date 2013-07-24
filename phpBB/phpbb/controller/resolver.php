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
	* phpbb_template object
	* @var phpbb_template
	*/
	protected $template;

	/**
	* Construct method
	*
	* @param phpbb_user $user User Object
	* @param ContainerInterface $container ContainerInterface object
	* @param phpbb_template_interface $template
	*/
	public function __construct(phpbb_user $user, ContainerInterface $container, phpbb_template $template = null)
	{
		$this->user = $user;
		$this->container = $container;
		$this->template = $template;
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

		/*
		* If this is an extension controller, we'll try to automatically set
		* the style paths for the extension (the ext author can change them
		* if necessary).
		*/
		$controller_dir = explode('_', get_class($controller_object));

		// 0 phpbb, 1 ext, 2 vendor, 3 extension name, ...
		if (!is_null($this->template) && isset($controller_dir[3]) && $controller_dir[1] === 'ext')
		{
			$controller_style_dir = 'ext/' . $controller_dir[2] . '/' . $controller_dir[3] . '/styles';

			if (is_dir($controller_style_dir))
			{
				$this->template->set_style(array($controller_style_dir, 'styles'));
			}
		}

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
