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

namespace phpbb\acp\controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class helper extends \phpbb\controller\helper
{
	/** @var \phpbb\acp\functions\acp */
	protected $acp_functions;

	/** @var \phpbb\language\language */
	protected $lang;

	/**
	 * Set ACP functions.
	 *
	 * @param \phpbb\acp\functions\acp	$acp_functions	ACP Functions
	 * @return void
	 */
	public function set_acp_functions(\phpbb\acp\functions\acp $acp_functions)
	{
		$this->acp_functions = $acp_functions;
	}

	/**
	 * Set language object.
	 *
	 * @param \phpbb\language\language	$lang		Language object
	 * @return void
	 */
	public function set_language(\phpbb\language\language $lang)
	{
		$this->lang = $lang;
	}

	/**
	 * Automate setting up the page and creating the response object.
	 *
	 * @param string	$template_file			The template handle to render
	 * @param string	$page_title				The title of the page to output
	 * @param int		$status_code			The status code to be sent to the page header
	 * @param bool		$display_online_list	Do we display online users list
	 * @param int		$item_id				Restrict online users to item id
	 * @param string	$item					Restrict online users to a certain session item,
	 *                     							e.g. forum for session_forum_id
	 * @param bool		$send_headers			Whether headers should be sent by page_header().
	 * 												Defaults to false for controllers.
	 * @return Response							Object containing rendered page
	 */
	public function render($template_file, $page_title = '', $status_code = 200, $display_online_list = false, $item_id = 0, $item = 'forum', $send_headers = false)
	{
		$this->acp_functions->adm_page_header($page_title);

		$this->template->set_filenames([
			'body'	=> $template_file,
		]);

		$this->acp_functions->adm_page_footer();

		return new Response($this->template->assign_display('body'), $status_code);
	}

	public function assign_errors(array $errors)
	{
		$s_errors = (bool) count($errors);

		$this->template->assign_vars([
			'S_ERROR'		=> $s_errors,
			'ERROR_MESSAGE'	=> $s_errors ? implode('<br>', $errors) : '',
		]);
	}

	/**
	 * Output a message.
	 *
	 * In case of an error, please throw an exception instead
	 *
	 * @param string	$message		The message to display
	 * @param array		$parameters		The parameters to use with the language variable
	 * @param string	$title			Title for the message
	 * @param int		$code			The HTTP status code (e.g. 404, 500, 503, etc.)
	 * @return Response|JsonResponse	A Response instance
	 */
	public function message($message, array $parameters = [], $title = 'INFORMATION', $code = 200)
	{
		array_unshift($parameters, $message);
		$message_text = call_user_func_array([$this->lang, 'lang'], $parameters);
		$message_title = $this->lang->lang($title);

		return $this->display_message($message_title, $message_text, $code);
	}

	/**
	 * Output a message with a "back to previous page"-link.
	 *
	 * @param string	$link			The link to the previous page
	 * @param string	$message		The message text
	 * @param array		$parameters		The parameter to use with the language variable
	 * @param string	$title			The message title
	 * @param int		$code			The HTTP status code (e.g. 404, 500, 503, etc.)
	 * @return Response|JsonResponse	A Response instance
	 */
	public function message_back($link, $message, array $parameters = [], $title = 'INFORMATION', $code = 200)
	{
		array_unshift($parameters, $message);
		$message_text = call_user_func_array([$this->lang, 'lang'], $parameters);
		$message_text .= $this->acp_functions->adm_back_link($link);
		$message_title = $this->lang->lang($title);

		return $this->display_message($message_title, $message_text, $code);
	}

	/**
	 * Display the message.
	 *
	 * @param string	$title			The message title
	 * @param string	$message		The message text
	 * @param int		$code			The HTTP status code (e.g. 404, 500, 503, etc.)
	 * @return Response|JsonResponse	A Response instance
	 * @access public
	 */
	protected function display_message($title, $message, $code)
	{
		if ($this->request->is_ajax())
		{
			global $refresh_data;

			return new JsonResponse(
				[
					'MESSAGE_TITLE'		=> $title,
					'MESSAGE_TEXT'		=> $message,
					'S_USER_WARNING'	=> false,
					'S_USER_NOTICE'		=> false,
					'REFRESH_DATA'		=> (!empty($refresh_data)) ? $refresh_data : null
				],
				$code
			);
		}

		$this->template->assign_vars([
			'MESSAGE_TITLE'	=> $title,
			'MESSAGE_TEXT'	=> $message,

			'S_USER_NOTICE'	=> $code === 200,
		]);

		return $this->render('message_body.html', $title, $code);
	}
}
