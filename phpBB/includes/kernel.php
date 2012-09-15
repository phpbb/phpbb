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
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
	* User object
	* @var phpbb_user
	*/
	protected $user;

	/**
	* Constructor
	*
	* @param EventDispatcherInterface $dispatcher An EventDispatcherInterface instance
	* @param ControllerResolverInterface $resolver   A ControllerResolverInterface instance
	*/
    public function __construct(EventDispatcherInterface $dispatcher, ControllerResolverInterface $resolver, ContainerBuilder $container, phpbb_user $user)
    {
        $this->dispatcher = $dispatcher;
        $this->resolver = $resolver;
        $this->container = $container;
        $this->user = $user;
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
			$controller_data = $this->resolver->getController($request);
			if (!isset($controller_data['service']))
			{
				throw new RuntimeException($this->user->lang('CONTROLLER_SERVICE_NOT_GIVEN', $request->query->get('controller')));
			}
			else if (!$this->container->has($controller_data['service']))
			{
				throw new RuntimeException($this->user->lang('CONTROLLER_SERVICE_UNDEFINED', $controller_data['service']));
			}

			$controller = $this->container->get($controller_data['service']);
			if (!$controller instanceof phpbb_controller_interface)
			{
				throw new RuntimeException($this->user->lang('CONTROLLER_OBJECT_TYPE_INVALID', gettype($controller)));
			}

			$response = $controller->handle();
			if (!$response instanceof Response)
			{
				throw new RuntimeException($this->user->lang('CONTROLLER_RETURN_TYPE_INVALID', gettype($controller)));
			}

			return $response;
		}
		catch (RuntimeException $e)
		{
			if ($catch)
			{
				trigger_error($e->getMessage());
			}

			throw new RuntimeException($e);
		}
	}
}
