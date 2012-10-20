<?php
/**
*
* @package phpBB3
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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;

class phpbb_event_kernel_subscriber implements EventSubscriberInterface
{
	/**
	* Template object
	* @var phpbb_template
	*/
	protected $template;

	/**
	* User object
	* @var phpbb_user
	*/
	protected $user;

	/**
	* Construct method
	*
	* @param phpbb_template $template Template object
	* @param phpbb_user $user User object
	*/
	public function __construct(phpbb_template $template, phpbb_user $user)
	{
		$this->template = $template;
		$this->user = $user;
	}

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

	/**
	* This listener is run when the KernelEvents::EXCEPTION event is triggered
	*
	* @param GetResponseForExceptionEvent $event
	* @return null
	*/
	public function on_kernel_exception(GetResponseForExceptionEvent $event)
	{
		page_header($this->user->lang('INFORMATION'));

		$this->template->assign_vars(array(
			'MESSAGE_TITLE'		=> $this->user->lang('INFORMATION'),
			'MESSAGE_TEXT'		=> $event->getException()->getMessage(),
		));

		$this->template->set_filenames(array(
			'body'	=> 'message_body.html',
		));

		page_footer(true, false, false);

		$event->setResponse(new Response($this->template->return_display('body'), 404));
	}

	public static function getSubscribedEvents()
	{
		return array(
			KernelEvents::TERMINATE		=> 'on_kernel_terminate',
			KernelEvents::EXCEPTION		=> 'on_kernel_exception',
		);
	}
}
