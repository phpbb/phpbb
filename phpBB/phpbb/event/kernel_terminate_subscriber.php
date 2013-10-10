<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\event;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

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
	* @return null
	*/
	public function on_kernel_terminate(PostResponseEvent $event)
	{
		exit_handler();
	}

	public static function getSubscribedEvents()
	{
		return array(
			KernelEvents::TERMINATE		=> 'on_kernel_terminate',
		);
	}
}
