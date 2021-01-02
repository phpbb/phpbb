<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\controller;

use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
* Controller manager class
*/
class resolver implements ControllerResolverInterface
{
	/**
	* ContainerInterface object
	* @var ContainerInterface
	*/
	protected $container;

	/**
	* phpbb\template\template object
	* @var \phpbb\template\template
	*/
	protected $template;

	/**
	* Request type cast helper object
	* @var \phpbb\request\type_cast_helper
	*/
	protected $type_cast_helper;

	/**
	* phpBB root path
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* Construct method
	*
	* @param ContainerInterface $container ContainerInterface object
	* @param string $phpbb_root_path Relative path to phpBB root
	* @param \phpbb\template\template $template
	*/
	public function __construct(ContainerInterface $container, $phpbb_root_path, \phpbb\template\template $template = null)
	{
		$this->container = $container;
		$this->template = $template;
		$this->type_cast_helper = new \phpbb\request\type_cast_helper();
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	* Load a controller callable
	*
	* @param Request $request Symfony Request object
	* @return bool|Callable Callable or false
	* @throws \phpbb\controller\exception
	*/
	public function getController(Request $request)
	{
		$controller = $request->attributes->get('_controller');

		if (!$controller)
		{
			throw new \phpbb\controller\exception('CONTROLLER_NOT_SPECIFIED');
		}

		// Require a method name along with the service name
		if (stripos($controller, ':') === false)
		{
			throw new \phpbb\controller\exception('CONTROLLER_METHOD_NOT_SPECIFIED');
		}

		list($service, $method) = explode(':', $controller);

		if (!$this->container->has($service))
		{
			throw new \phpbb\controller\exception('CONTROLLER_SERVICE_UNDEFINED', array($service));
		}

		$controller_object = $this->container->get($service);

		/*
		* If this is an extension controller, we'll try to automatically set
		* the style paths for the extension (the ext author can change them
		* if necessary).
		*/
		$controller_dir = explode('\\', get_class($controller_object));

		// 0 vendor, 1 extension name, ...
		if (!is_null($this->template) && isset($controller_dir[1]))
		{
			$controller_style_dir = 'ext/' . $controller_dir[0] . '/' . $controller_dir[1] . '/styles';

			if (is_dir($this->phpbb_root_path . $controller_style_dir))
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
	* @param Request $request Symfony Request object
	* @param mixed $controller A callable (controller class, method)
	* @return array An array of arguments to pass to the controller
	* @throws \phpbb\controller\exception
	*/
	public function getArguments(Request $request, $controller)
	{
		// At this point, $controller should be a callable
		if (is_array($controller))
		{
			list($object, $method) = $controller;
			$mirror = new \ReflectionMethod($object, $method);
		}
		else if (is_object($controller) && !$controller instanceof \Closure)
		{
			$mirror = new \ReflectionObject($controller);
			$mirror = $mirror->getMethod('__invoke');
		}
		else
		{
			$mirror = new \ReflectionFunction($controller);
		}

		$arguments = array();
		$parameters = $mirror->getParameters();
		$attributes = $request->attributes->all();
		foreach ($parameters as $param)
		{
			if (array_key_exists($param->name, $attributes))
			{
				if (is_string($attributes[$param->name]))
				{
					$value = $attributes[$param->name];
					$this->type_cast_helper->set_var($value, $attributes[$param->name], 'string', true, false);
					$arguments[] = $value;
				}
				else
				{
					$arguments[] = $attributes[$param->name];
				}
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
				throw new \phpbb\controller\exception('CONTROLLER_ARGUMENT_VALUE_MISSING', array($param->getPosition() + 1, get_class($object) . ':' . $method, $param->name));
			}
		}

		return $arguments;
	}
}
