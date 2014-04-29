<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

class kernel_request_subscriber implements EventSubscriberInterface
{
	/**
	* ContainerInterface object
	* @var ContainerInterface
	*/
	protected $phpbb_container;

	/**
	* PHP extension
	* @var string
	*/
	protected $php_ext;

	/**
	* Root path
	* @var string
	*/
	protected $root_path;

	/**
	* Construct method
	*
	* @param ContainerInterface $service_container
	* @param string $root_path Root path
	* @param string $php_ext PHP extension
	*/
	public function __construct(ContainerInterface $service_container, $root_path, $php_ext)
	{
		$this->phpbb_container = $service_container;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* This listener is run when the KernelEvents::REQUEST event is triggered
	*
	* This is responsible for setting up the routing information
	*
	* @param GetResponseEvent $event
	* @return null
	*/
	public function on_kernel_request(GetResponseEvent $event)
	{
		$finder = $this->phpbb_container->get('ext.finder');

		$request = $event->getRequest();
		$context = new RequestContext();
		$context->fromRequest($request);

		$matcher = phpbb_get_url_matcher($finder, $context, $this->root_path, $this->php_ext);
		$router_listener = new RouterListener($matcher, $context);
		$router_listener->onKernelRequest($event);
	}

	public static function getSubscribedEvents()
	{
		return array(
			KernelEvents::REQUEST		=> 'on_kernel_request',
		);
	}
}
