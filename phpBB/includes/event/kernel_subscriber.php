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
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\Routing\RequestContext;

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
	* Extension finder object
	* @var phpbb_extension_finder
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
	* @param phpbb_template $template Template object
	* @param phpbb_user $user User object
	* @param phpbb_extension_finder $finder Extension finder object
	* @param string $root_path Root path
	* @param string $php_ext PHP extension
	*/
	public function __construct(phpbb_template $template, phpbb_user $user, phpbb_extension_finder $finder, $root_path, $php_ext)
	{
		$this->template = $template;
		$this->user = $user;
		$this->finder = $finder;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
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

		$event->setResponse(new Response($this->template->assign_display('body'), 404));
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
		$request = $event->getRequest();
		$context = new RequestContext();
		$context->fromRequest($request);

		$matcher = phpbb_create_url_matcher($this->finder, $context, $this->root_path, $this->php_ext);

		$router_listener = new RouterListener($matcher, $context);
		$router_listener->onKernelRequest($event);
	}

	public static function getSubscribedEvents()
	{
		return array(
			KernelEvents::REQUEST		=> 'on_kernel_request',
			KernelEvents::TERMINATE		=> 'on_kernel_terminate',
			KernelEvents::EXCEPTION		=> 'on_kernel_exception',
		);
	}
}
