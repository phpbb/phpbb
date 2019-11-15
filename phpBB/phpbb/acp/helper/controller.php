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

namespace phpbb\acp\helper;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class controller extends \phpbb\controller\helper
{
	/** @var functions */
	protected $functions;

	/** @var string phpBB admin path */
	protected $admin_path;

	/**
	 * @param functions $functions
	 * @return void
	 */
	public function set_functions(functions $functions)
	{
		$this->functions	= $functions;
	}

	/**
	 * @param string $admin_path
	 * @return void
	 */
	public function set_admin_path($admin_path)
	{
		$this->admin_path	= $admin_path;
	}

	/**
	 * Automate setting up the page and creating the response object.
	 *
	 * @param string	$template_file				The template handle to render
	 * @param string	$page_title					The title of the page to output
	 * @param int		$status_code				The status code to be sent to the page header
	 * @param bool		$display_online_list		Not used
	 * @param int		$item_id					Not used
	 * @param string	$item						Not used
	 * @param bool		$send_headers				Not used
	 * @return Response								Response object containing the rendered page
	 */
	public function render($template_file, $page_title = '', $status_code = 200, $display_online_list = false, $item_id = 0, $item = 'forum', $send_headers = false)
	{
		$this->template->set_custom_style([[
			'name'		=> 'adm',
			'ext_path'	=> 'adm/style/',
		]], $this->admin_path . 'style');

		$this->functions->adm_page_header($page_title);

		$this->template->set_filenames(['body' => $template_file]);

		$this->functions->adm_page_footer(true);

		return new Response($this->template->assign_display('body'), $status_code);
	}

	/**
	 * Output a message
	 *
	 * In case of an error, please throw an exception instead
	 *
	 * @param string	$message		The message to display (must be a language variable)
	 * @param string	$route			The message back route (return to previous page)
	 * @param array		$params			The parameters to use with the route
	 * @param array		$parameters		The parameters to use with the language var
	 * @param string	$title			Title for the message (must be a language variable)
	 * @param int		$code			The HTTP status code (e.g. 404, 500, 503, etc.)
	 * @return Response|JsonResponse	A Response instance
	 */
	public function message_back($message, $route, array $params = [], array $parameters = [], $title = 'INFORMATION', $code = 200)
	{
		array_unshift($parameters, $message);
		$message_text = call_user_func_array([$this->user, 'lang'], $parameters);
		$message_title = $this->user->lang($title);
		$message_back = $this->user->lang('RETURN_PAGE', '<a href="' . $this->route($route, $params) . '">', '</a>');

		if ($this->request->is_ajax())
		{
			global $refresh_data;

			return new JsonResponse([
				'MESSAGE_TITLE'		=> $message_title,
				'MESSAGE_TEXT'		=> $message_text,
				'MESSAGE_BACK'		=> $message_back,
				'S_USER_WARNING'	=> false,
				'S_USER_NOTICE'		=> false,
				'REFRESH_DATA'		=> (!empty($refresh_data)) ? $refresh_data : null,
			], $code);
		}

		$this->template->assign_vars([
			'MESSAGE_TEXT'		=> $message_text,
			'MESSAGE_TITLE'		=> $message_title,
			'MESSAGE_BACK'		=> $message_back,
			'S_USER_NOTICE'		=> $code === 200,
		]);

		return $this->render('message_body.html', $message_title, $code);
	}

	/**
	 * Generate a back link from a route to be appended to a message.
	 *
	 * @param string	$route		The route name for the link back to the previous page
	 * @param array		$params		The route parameters
	 * @return string
	 */
	public function adm_back_route($route, array $params = [])
	{
		return $this->functions->adm_back_link($this->route($route, $params));
	}

	/**
	 * Generate a back link to be appended to a message.
	 *
	 * @param string	$link		The link back to the previous page
	 * @return string
	 */
	public function adm_back_link($link)
	{
		return $this->functions->adm_back_link($link);
	}
}
