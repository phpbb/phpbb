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

namespace phpbb\controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
* Controller helper class, contains methods that do things for controllers
*/
class helper
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
	* config object
	* @var \phpbb\config\config
	*/
	protected $config;

	/* @var \phpbb\symfony_request */
	protected $symfony_request;

	/* @var \phpbb\request\request_interface */
	protected $request;

	/**
	 * @var \phpbb\routing\helper
	 */
	protected $routing_helper;

	/**
	* Constructor
	*
	* @param \phpbb\template\template $template Template object
	* @param \phpbb\user $user User object
	* @param \phpbb\config\config $config Config object
	* @param \phpbb\symfony_request $symfony_request Symfony Request object
	* @param \phpbb\request\request_interface $request phpBB request object
	* @param \phpbb\routing\helper $routing_helper Helper to generate the routes
	*/
	public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\config\config $config, \phpbb\symfony_request $symfony_request, \phpbb\request\request_interface $request, \phpbb\routing\helper $routing_helper)
	{
		$this->template = $template;
		$this->user = $user;
		$this->config = $config;
		$this->symfony_request = $symfony_request;
		$this->request = $request;
		$this->routing_helper = $routing_helper;
	}

	/**
	* Automate setting up the page and creating the response object.
	*
	* @param string $template_file The template handle to render
	* @param string $page_title The title of the page to output
	* @param int $status_code The status code to be sent to the page header
	* @param bool $display_online_list Do we display online users list
	* @param int $item_id Restrict online users to item id
	* @param string $item Restrict online users to a certain session item, e.g. forum for session_forum_id
	*
	* @return Response object containing rendered page
	*/
	public function render($template_file, $page_title = '', $status_code = 200, $display_online_list = false, $item_id = 0, $item = 'forum')
	{
		page_header($page_title, $display_online_list, $item_id, $item);

		$this->template->set_filenames(array(
			'body'	=> $template_file,
		));

		page_footer(true, false, false);

		return new Response($this->template->assign_display('body'), $status_code);
	}

	/**
	* Generate a URL to a route
	*
	* @param string	$route		Name of the route to travel
	* @param array	$params		String or array of additional url parameters
	* @param bool	$is_amp		Is url using &amp; (true) or & (false)
	* @param string|bool		$session_id	Possibility to use a custom session id instead of the global one
	* @param bool|string		$reference_type The type of reference to be generated (one of the constants)
	* @return string The URL already passed through append_sid()
	*/
	public function route($route, array $params = array(), $is_amp = true, $session_id = false, $reference_type = UrlGeneratorInterface::ABSOLUTE_PATH)
	{
		return $this->routing_helper->route($route, $params, $is_amp, $session_id, $reference_type);
	}

	/**
	* Output an error, effectively the same thing as trigger_error
	*
	* @param string $message The error message
	* @param int $code The error code (e.g. 404, 500, 503, etc.)
	* @return Response A Response instance
	*
	* @deprecated 3.1.3 (To be removed: 3.3.0) Use exceptions instead.
	*/
	public function error($message, $code = 500)
	{
		return $this->message($message, array(), 'INFORMATION', $code);
	}

	/**
	 * Output a message
	 *
	 * In case of an error, please throw an exception instead
	 *
	 * @param string $message The message to display (must be a language variable)
	 * @param array $parameters The parameters to use with the language var
	 * @param string $title Title for the message (must be a language variable)
	 * @param int $code The HTTP status code (e.g. 404, 500, 503, etc.)
	 * @return Response A Response instance
	 */
	public function message($message, array $parameters = array(), $title = 'INFORMATION', $code = 200)
	{
		array_unshift($parameters, $message);
		$message_text = call_user_func_array(array($this->user, 'lang'), $parameters);
		$message_title = $this->user->lang($title);

		if ($this->request->is_ajax())
		{
			global $refresh_data;

			return new JsonResponse(
				array(
					'MESSAGE_TITLE'		=> $message_title,
					'MESSAGE_TEXT'		=> $message_text,
					'S_USER_WARNING'	=> false,
					'S_USER_NOTICE'		=> false,
					'REFRESH_DATA'		=> (!empty($refresh_data)) ? $refresh_data : null
				),
				$code
			);
		}

		$this->template->assign_vars(array(
			'MESSAGE_TEXT'	=> $message_text,
			'MESSAGE_TITLE'	=> $message_title,
		));

		return $this->render('message_body.html', $message_title, $code);
	}

	/**
	 * Assigns automatic refresh time meta tag in template
	 *
	 * @param	int		$time	time in seconds, when redirection should occur
	 * @param	string	$url	the URL where the user should be redirected
	 * @return	null
	 */
	public function assign_meta_refresh_var($time, $url)
	{
		$this->template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="' . $time . '; url=' . $url . '" />',
		));
	}

	/**
	* Return the current url
	*
	* @return string
	*/
	public function get_current_url()
	{
		return generate_board_url(true) . $this->request->escape($this->symfony_request->getRequestUri(), true);
	}
}
