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
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

class kernel_exception_subscriber implements EventSubscriberInterface
{
	/**
	* Template object
	* @var \phpbb\template\template
	*/
	protected $template;

	/**
	* User object
	* @var \phpbb\user
	*/
	protected $user;

	/**
	* Construct method
	*
	* @param \phpbb\template\template $template Template object
	* @param \phpbb\user $user User object
	*/
	public function __construct(\phpbb\template\template $template, \phpbb\user $user)
	{
		$this->template = $template;
		$this->user = $user;
	}

	/**
	* This listener is run when the KernelEvents::EXCEPTION event is triggered
	*
	* @param GetResponseForExceptionEvent $event
	* @return null
	*/
	public function on_kernel_exception(GetResponseForExceptionEvent $event)
	{
		page_header($this->user->lang('INFORMATION'));

		$exception = $event->getException();

		$this->template->assign_vars(array(
			'MESSAGE_TITLE'		=> $this->user->lang('INFORMATION'),
			'MESSAGE_TEXT'		=> $exception->getMessage(),
		));

		$this->template->set_filenames(array(
			'body'	=> 'message_body.html',
		));

		page_footer(true, false, false);

		$status_code = $exception instanceof HttpException ? $exception->getStatusCode() : 500;
		$response = new Response($this->template->assign_display('body'), $status_code);
		$event->setResponse($response);
	}

	public static function getSubscribedEvents()
	{
		return array(
			KernelEvents::EXCEPTION		=> 'on_kernel_exception',
		);
	}
}
