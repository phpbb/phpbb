<?php
/**
*
* @package controller
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

use Symfony\Component\HttpFoundation\Response;

/**
* Controller helper class, contains methods that do things for controllers
* @package phpBB3
*/
class phpbb_controller_helper
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
	* Request object
	* @var phpbb_request
	*/
	protected $request;

	/**
	* phpBB root path
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* PHP extension
	* @var string
	*/
	protected $php_ext;

	/**
	* Constructor
	*
	* @param phpbb_template $template Template object
	* @param phpbb_user $user User object
	* @param string $phpbb_root_path phpBB root path
	* @param string $php_ext PHP extension
	*/
	public function __construct(phpbb_template $template, phpbb_user $user, phpbb_request $request, $phpbb_root_path, $php_ext)
	{
		$this->template = $template;
		$this->user = $user;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* Automate setting up the page and creating the response object.
	*
	* @param string $handle The template handle to render
	* @param string $page_title The title of the page to output
	* @param int $status_code The status code to be sent to the page header
	* @return Response object containing rendered page
	*/
	public function render($template_file, $page_title = '', $status_code = 200)
	{
		page_header($page_title);

		$this->template->set_filenames(array(
			'body'	=> $template_file,
		));

		page_footer(true, false, false);

		return new Response($this->template->assign_display('body'), $status_code);
	}

	/**
	* Generate a URL
	*
	* @param string	$route		The route to travel
	* @param mixed	$params		String or array of additional url parameters
	* @param bool	$is_amp		Is url using &amp; (true) or & (false)
	* @param string	$session_id	Possibility to use a custom session id instead of the global one
	* @return string The URL already passed through append_sid()
	*/
	public function url($route, $params = false, $is_amp = true, $session_id = false)
	{
		$route_params = '';
		if (($route_delim = strpos($route, '?')) !== false)
		{
			$route_params = substr($route, $route_delim);
			$route = substr($route, 0, $route_delim);
		}

		$request_uri = $this->request->variable('REQUEST_URI', '', false, phpbb_request::SERVER);
		$script_name = $this->request->variable('SCRIPT_NAME', '', false, phpbb_request::SERVER);

		// If the app.php file is being used (no rewrite) keep it in the URL.
		// Otherwise, don't include it.
		$route_prefix = $this->phpbb_root_path;
		$parts = explode('/', $script_name);
		$route_prefix .= strpos($request_uri, $script_name) === 0 ? array_pop($parts) . '/' : '';

		return append_sid($route_prefix . "$route" . $route_params, $params, $is_amp, $session_id);
	}

	/**
	* Output an error, effectively the same thing as trigger_error
	*
	* @param string $message The error message
	* @param string $code The error code (e.g. 404, 500, 503, etc.)
	* @return Response A Reponse instance
	*/
	public function error($message, $code = 500)
	{
		$this->template->assign_vars(array(
			'MESSAGE_TEXT'	=> $message,
			'MESSAGE_TITLE'	=> $this->user->lang('INFORMATION'),
		));

		return $this->render('message_body.html', $this->user->lang('INFORMATION'), $code);
	}
}
