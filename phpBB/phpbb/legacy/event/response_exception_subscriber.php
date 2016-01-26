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

namespace phpbb\legacy\event;

use phpbb\legacy\exception\exit_with_response_exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class response_exception_subscriber implements EventSubscriberInterface
{
	/**
	* This listener is run when the KernelEvents::EXCEPTION event is triggered
	*
	* @param GetResponseForExceptionEvent $event
	*/
	public function on_kernel_exception(GetResponseForExceptionEvent $event)
	{
		if (! $event->getException() instanceof exit_with_response_exception) {
			return;
		}

		/** @var exit_with_response_exception $exception */
		$exception = $event->getException();

		$response = $exception->get_response();
		$response->headers->add(['X-Status-Code' => $response->getStatusCode()]);

		$event->setResponse($response);
	}

	static public function getSubscribedEvents()
	{
		return array(
			KernelEvents::EXCEPTION		=> ['on_kernel_exception', 100000],
		);
	}
}
