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
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class kernel_request_subscriber implements EventSubscriberInterface
{
	/** @var \phpbb\cp\manager */
	protected $cp_manager;

	/** @var \phpbb\cp\constructor */
	protected $cp_constructor;

	/**
	 * Constructor.
	 *
	 * The services are optional, as they are not available in the installer.
	 *
	 * @param \phpbb\cp\manager			$cp_manager			Control panel manager object
	 * @param \phpbb\cp\constructor		$cp_constructor		Control panel constructor object
	 */
	public function __construct(\phpbb\cp\manager $cp_manager = null, \phpbb\cp\constructor $cp_constructor = null)
	{
		$this->cp_manager		= $cp_manager;
		$this->cp_constructor	= $cp_constructor;
	}

	/**
	 * This listener is run when the KernelEvents::REQUEST event is triggered.
	 *
	 * It checks if the used route is for a control panel item.
	 *
	 * @param GetResponseEvent		$event			The event object
	 * @return void
	 */
	public function on_kernel_request(GetResponseEvent $event)
	{
		if ($this->cp_manager !== null && $this->cp_constructor !== null)
		{
			$route = $event->getRequest()->attributes->get('_route');

			$route = str_replace($this->cp_manager->get_route_pagination(), '', $route);

			/** @var \phpbb\di\service_collection $services */
			foreach ($this->cp_manager->get_collections() as $cp => $services)
			{
				if ($services->offsetExists($route))
				{
					$this->cp_constructor->setup($cp, $route);

					break;
				}
			}
		}
	}

	/**
	 * Assign functions defined in this class to event listeners in the Symfony framework.
	 *
	 * @return array
	 * @static
	 */
	static public function getSubscribedEvents()
	{
		return [
			KernelEvents::REQUEST	=> 'on_kernel_request',
		];
	}
}
