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
	* \phpbb\extension\finder object
	* @var ContainerInterface
	*/
	protected $finder;

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
	* @param string $root_path Root path
	* @param string $php_ext PHP extension
	*/
	public function __construct($root_path, $php_ext)
	{
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	public function setExtFinder(\phpbb\extension\finder $finder = null)
	{
		$this->finder = $finder;
	}

	/**
	* This listener is run when the KernelEvents::REQUEST event is triggered
	*
	* This is responsible for setting up the routing information
	*
	* @param GetResponseEvent $event
	* @throws \BadMethodCallException
	* @return null
	*/
	public function on_kernel_request(GetResponseEvent $event)
	{
		if ($this->finder === null)
		{
			throw new \BadMethodCallException("The finder should not be null");
		}

		$request = $event->getRequest();
		$context = new RequestContext();
		$context->fromRequest($request);

		$matcher = phpbb_get_url_matcher($this->finder, $context, $this->root_path, $this->php_ext);
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
