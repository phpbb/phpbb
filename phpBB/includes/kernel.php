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

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;

/**
* Controller manager class
* @package phpBB3
*/
class phpbb_kernel implements HttpKernelInterface
{
	/**
	* Event Dispatcher object
	* @var EventDispatcherInterface
	*/
	protected $dispatcher;

	/**
	* Controller Resolver object
	* @var ControllerResolverInterface
	*/
	protected $resolver;

	/**
	* Container object
	* @var ContainerBuilder
	*/
	protected $container;

	/**
	* Controller provider
	* @var phpbb_controller_provider
	*/
	protected $provider;

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
	* Constructor
	*
	* @param EventDispatcherInterface $dispatcher An EventDispatcherInterface instance
	* @param ControllerResolverInterface $resolver   A ControllerResolverInterface instance
	* @param ContainerBuilder $container A ContainerBuilder instance
	* @param phpbb_user $user A phpbb_user instance
	* @param phpbb_controller_route_provider A phpbb_controller_route_provider instance
	* @param string $base_path Base path to prepend when looking for routing files
	*/
	public function __construct(EventDispatcherInterface $dispatcher, ControllerResolverInterface $resolver, ContainerBuilder $container, phpbb_user $user, phpbb_controller_route_provider $route_provider, $base_path = '')
	{
		$this->dispatcher = $dispatcher;
		$this->resolver = $resolver;
		$this->container = $container;
		$this->provider = new phpbb_controller_provider($route_provider->find());
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
	* Handles a Request to convert it to a Response.
	*
	* When $catch is true, the implementation must catch all exceptions
	* and do its best to convert them to a Response instance.
	*
	* @param Request $request A Request instance
	* @param integer $type    The type of the request
	*                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
	* @param Boolean $catch Whether to catch exceptions or not
	*
	* @return Response A Response instance
	*
	* @throws RuntimeException When an Exception occurs during processing
	*/
	public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
	{
		try
		{
			$event = new GetResponseEvent($this, $request, $type);
			$this->dispatcher->dispatch(KernelEvents::REQUEST, $event);

			if ($event->hasResponse())
			{
				return $this->filter_response($event->getResponse(), $request, $type);
			}

			$context = new RequestContext();
			$context->fromRequest($request);
		
			$matcher = new UrlMatcher($this->controllers, $context);
		
			$router = new RouterListener($matcher, $context);
			$router->onKernelRequest($event);

			$service = $method = false;
			$controller = $this->resolver->getController($request);

			list($service, $method) = $controller;

			if (!$this->container->has($service))
			{
				throw new RuntimeException($this->user->lang('CONTROLLER_SERVICE_UNDEFINED', $service), 404);
			}

			$controller_object = $this->container->get($service);

			if (!$controller_object instanceof phpbb_controller_interface)
			{
				throw new RuntimeException($this->user->lang('CONTROLLER_OBJECT_TYPE_INVALID', get_class($controller_object)), 404);
			}

			$controller_callable = array($controller_object, $method);

			$event = new FilterControllerEvent($this, $controller_callable, $request, $type);
			$this->dispatcher->dispatch(KernelEvents::CONTROLLER, $event);
			$controller = $event->getController();

			$arguments = $this->resolver->getArguments($request, $controller_callable);
			$response = call_user_func_array($controller_callable, $arguments);

			if (!$response instanceof Response)
			{
				$event = new GetResponseForControllerResultEvent($this, $request, $type, $response);
            	$this->dispatcher->dispatch(KernelEvents::VIEW, $event);

            	if ($event->hasResponse())
            	{
            		$response = $event->getResponse();
            	}

            	if (!$response instanceof Response)
            	{
					throw new RuntimeException($this->user->lang('CONTROLLER_RETURN_TYPE_INVALID', get_class($controller_object)), 404);
				}
			}

			return $this->filter_response($response, $request, $type);
		}
		catch (RuntimeException $e)
		{
			// This is done because the exception thrown when a path is not
			// matched does not have a message. Basically, this says that if
			// no message was given, use the language string supplied instead
			$message = $e->getMessage() ?: $this->user->lang('CONTROLLER_NOT_FOUND');
			if ($catch)
			{
				send_status_line(404, 'Not Found');
				trigger_error($message);
			}

			throw new RuntimeException($mesage, $e->getCode(), $e);
		}
	}

	public function filter_response(Response $response, Request $request, $type)
	{
		$filter = new FilterResponseEvent($this, $request, $type, $response);
		$this->dispatcher->dispatch(KernelEvents::RESPONSE, $filter);
		return $filter->getResponse();
	}
}
