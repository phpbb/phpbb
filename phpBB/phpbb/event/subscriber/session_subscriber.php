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

namespace phpbb\event\subscriber;

use phpbb\auth\auth;
use phpbb\user;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class session_subscriber implements EventSubscriberInterface
{
	/** @var user */
	protected $user;

	/** @var auth */
	private $auth;

	/**
	 * Construct method
	 *
	 * @param user $user
	 * @param auth $auth
	 */
	public function __construct(user $user, auth $auth)
	{
		$this->user = $user;
		$this->auth = $auth;
	}

	/**
	* This listener is run when the KernelEvents::EXCEPTION event is triggered
	*
	* @param GetResponseEvent $event
	*/
	public function on_kernel_request(GetResponseEvent $event)
	{
		// Start session management
		$this->user->session_begin();
		$this->auth->acl($this->user->data);
		$this->user->setup('app');
	}

	static public function getSubscribedEvents()
	{
		return array(
			KernelEvents::REQUEST => ['on_kernel_request', 1024],
		);
	}
}
