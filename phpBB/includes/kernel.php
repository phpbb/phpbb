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
	protected function load_controller_map($base_path = '')
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
			$context = new RequestContext();
			$context->fromRequest($request);

			// Register the RouterListener
			$this->dispatcher->addListener('core.kernel_request', array(
				new RouterListener(new UrlMatcher($this->controllers, $context), $context),
				'onKernelRequest',
			));

			$event = new GetResponseEvent($this, $request, $type);

			/**
			* The core.kernel_request event occurs at the very beginning of
			* request dispatching
			*
			* This event allows you to create a response for a request before
			* any other code in the framework is executed. The event listener
			* method receives a
			* Symfony\Component\HttpKernel\Event\GetResponseEvent instance.
			*
			* @event core.kernel_controller
			* @var	GetResponseEvent	event	GetResponseEvent object
			* @since 3.1-A1
			*/
			$vars = array('event');
			extract($this->dispatcher->trigger_event('core.kernel_request', compact($vars)));

			if ($event->hasResponse())
			{
				return $this->filter_response($event->getResponse(), $request, $type);
			}

			$service = $method = false;
			$controller = $this->resolver->getController($request);

			list($service, $method) = $controller;

			if ($service === false || !$this->container->has($service))
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

			/**
			* The core.kernel_controller event occurs once a controller has
			* been found to a request.
			*
			* This event allows you to change the controller callable
			* that will handle the request.
			* The event listener method receives a
			* Symfony\Component\HttpKernel\Event\FilterControllerEvent
			* instance.
			*
			* @event core.kernel_controller
			* @var	FilterControllerEvent	event	FilterControllerEvent object
			* @since 3.1-A1
			*/
			$vars = array('event');
			extract($this->dispatcher->trigger_event('core.kernel_controller', compact($vars)));

			$controller = $event->getController();

			$arguments = $this->resolver->getArguments($request, $controller_callable);
			$response = call_user_func_array($controller_callable, $arguments);

			if (!$response instanceof Response)
			{
				$event = new GetResponseForControllerResultEvent($this, $request, $type, $response);

				/**
				* This event occurs when the return value of a controller
				* is not a Response instance
				*
				* This event allows you to create a response for the return value of the
				* controller. The event listener method receives a
				* Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent
				* instance.
				*
				* @event core.kernel_view
				* @var	GetResponseForControllerResultEvent	event GetResponseForControllerResultEvent object
				* @since 3.1-A1
				*/
				$vars = array('event');
				extract($this->dispatcher->trigger_event('core.kernel_view', compact($vars)));

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
			if ($catch === false)
			{
				throw $e;
			}

			return $this->handle_exception($e, $request, $type);
		}
	}

	/**
	* Filter the response and dispatch response event
	*
	* @param Response $response Response object
	* @param Request $request Request object
	* @param int $type Type of request
	* @return Response
	*/
	protected function filter_response(Response $response, Request $request, $type)
	{
		$filter = new FilterResponseEvent($this, $request, $type, $response);

		/**
		* Use this event manipulate the response before it is returned.
		*
		* You can call getResponse() to retrieve the current response. With
		* setResponse() you can set a new response that will be returned to
		* the browser.
		*
		* @event core.kernel_response
		* @var	FilterResponseEvent	filter	FilterResponseEvent object
		* @since 3.1-A1
		*/
		$vars = array('filter');
		extract($this->dispatcher->trigger_event('core.kernel_response', compact($vars)));

		return $filter->getResponse();
	}

	/**
	* Attempt to convert an exception into a response
	*
	* @param Exception $e The exception thrown
	* @param Request $request The Request object
	* @param int $type The type of request
	* @return Response
	*/
	protected function handle_exception(Exception $e, Request $request, $type)
	{
		$event = new GetResponseForExceptionEvent($this, $request, $type, $e);

		/**
		* The core.kernel_exception event occurs when an uncaught exception appears
		*
		* This event allows you to create a response for a thrown exception or
		* to modify the thrown exception. The event listener method receives
		* a Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent
		* instance.
		*
		* @event core.kernel_exception
		* @var	GetResponseForExceptionEvent event GetResponseForExceptionEvent object
		* @since 3.1-A1
		*/
		$vars = array('event');
		extract($this->dispatcher->trigger_event('core.kernel_exception', compact($vars)));

		// If a listener has changed the exception, use it
		$e = $event->getException();

		// If the event still does not manage to return a response,
		// throw the exception again.
		if (!$event->hasResponse())
		{
			throw $e;
		}

		$response = $event->getResponse();

		try
		{
			return $this->filter_response($response, $request, $type);
		}
		catch (Exception $e)
		{
			return $response;
		}
	}
}
