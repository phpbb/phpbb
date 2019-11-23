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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;

class kernel_exception_subscriber implements EventSubscriberInterface
{
	/** @var bool Set to true to show full exception messages */
	protected $debug;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $language;

	protected $acp_functions;

	/** @var \phpbb\request\type_cast_helper */
	protected $type_caster;

	/**
	 * Construct method
	 *
	 * @param \phpbb\template\template		$template		Template object
	 * @param \phpbb\language\language		$language		Language object
	 * @param \phpbb\acp\helper\functions	$acp_functions	ACP Functions object
	 * @param bool							$debug			Set to true to show full exception messages
	 */
	public function __construct(
		\phpbb\template\template $template,
		\phpbb\language\language $language,
		\phpbb\acp\helper\functions $acp_functions,
		$debug = false
	)
	{
		$this->debug = $debug || defined('DEBUG');
		$this->template = $template;
		$this->language = $language;
		$this->acp_functions = $acp_functions;
		$this->type_caster = new \phpbb\request\type_cast_helper();
	}

	/**
	* This listener is run when the KernelEvents::EXCEPTION event is triggered
	*
	* @param GetResponseForExceptionEvent $event
	* @return null
	*/
	public function on_kernel_exception(GetResponseForExceptionEvent $event)
	{
		$exception = $event->getException();

		$message = $exception->getMessage();
		$this->type_caster->set_var($message, $message, 'string', true, false);

		if ($exception instanceof \phpbb\exception\exception_interface)
		{
			$message = $this->language->lang_array($message, $exception->get_parameters());
		}
		else if (!$this->debug && $exception instanceof NotFoundHttpException)
		{
			$message = $this->language->lang('PAGE_NOT_FOUND');
		}

		// Show <strong> text in bold
		$message = preg_replace('#&lt;(/?strong)&gt;#i', '<$1>', $message);

		if (!$event->getRequest()->isXmlHttpRequest())
		{
			if (defined('IN_ADMIN'))
			{
				$this->acp_functions->adm_page_header($this->language->lang('INFORMATION'));
			}
			else
			{
				page_header($this->language->lang('INFORMATION'));
			}

			$this->template->assign_vars(array(
				'MESSAGE_TITLE' => $this->language->lang('INFORMATION'),
				'MESSAGE_TEXT'  => $message,
			));

			$this->template->set_filenames(array(
				'body' => 'message_body.html',
			));

			if (defined('IN_ADMIN'))
			{
				$this->acp_functions->adm_page_footer();
			}
			else
			{
				page_footer(true, false, false);
			}

			$response = new Response($this->template->assign_display('body'), 500);
		}
		else
		{
			$data = array();

			if (!empty($message))
			{
				$data['message'] = $message;
			}

			if ($this->debug)
			{
				$data['trace'] = $exception->getTrace();
			}

			$response = new JsonResponse($data, 500);
		}

		if ($exception instanceof HttpExceptionInterface)
		{
			$response->setStatusCode($exception->getStatusCode());
			$response->headers->add($exception->getHeaders());
		}

		$event->setResponse($response);
	}

	static public function getSubscribedEvents()
	{
		return array(
			KernelEvents::EXCEPTION		=> 'on_kernel_exception',
		);
	}
}
