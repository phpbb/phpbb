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
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class kernel_terminate_subscriber implements EventSubscriberInterface
{
	/**
	* This listener is run when the KernelEvents::TERMINATE event is triggered
	* This comes after a Response has been sent to the server; this is
	* primarily cleanup stuff.
	*
	* @param PostResponseEvent $event
	* @return void
	*/
	public function on_kernel_terminate(PostResponseEvent $event)
	{
		garbage_collection();

		exit_handler();
	}

	static public function getSubscribedEvents()
	{
		return array(
			KernelEvents::TERMINATE		=> array('on_kernel_terminate', ~PHP_INT_MAX),
		);
	}
}
