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

namespace phpbb\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\Routing\RequestContext;

class kernel_request_subscriber implements EventSubscriberInterface
{
	/**
	* Extension manager object
	* @var \phpbb\extension\manager
	*/
	protected $manager;

	/**
	* PHP file extension
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
	* @param \phpbb\extension\manager $manager Extension manager object
	* @param string $root_path Root path
	* @param string $php_ext PHP file extension
	*/
	public function __construct(\phpbb\extension\manager $manager, $root_path, $php_ext)
	{
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->manager = $manager;
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
		$request = $event->getRequest();
		$context = new RequestContext();
		$context->fromRequest($request);

		$matcher = phpbb_get_url_matcher($this->manager, $context, $this->root_path, $this->php_ext);
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
