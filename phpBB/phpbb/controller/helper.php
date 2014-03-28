<?php
/**
*
* @package controller
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;

/**
* Controller helper class, contains methods that do things for controllers
* @package phpBB3
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
	* @param \phpbb\template\template $template Template object
	* @param \phpbb\user $user User object
	* @param \phpbb\config\config $config Config object
	* @param \phpbb\controller\provider $provider Path provider
	* @param string $phpbb_root_path phpBB root path
	* @param string $php_ext PHP extension
	*/
	public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\config\config $config, \phpbb\controller\provider $provider, $phpbb_root_path, $php_ext)
	{
		$this->template = $template;
		$this->user = $user;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->route_collection = $provider->get_routes();
	}

	/**
	* Automate setting up the page and creating the response object.
	*
	* @param string $handle The template handle to render
	* @param string $page_title The title of the page to output
	* @param int $status_code The status code to be sent to the page header
	* @return Response object containing rendered page
	*/
	public function render($template_file, $page_title = '', $status_code = 200, $display_online_list = false)
	{
		page_header($page_title, $display_online_list);

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
	* @param string	$session_id	Possibility to use a custom session id instead of the global one
	* @return string The URL already passed through append_sid()
	*/
	public function route($route, array $params = array(), $is_amp = true, $session_id = false)
	{
		$anchor = '';
		if (isset($params['#']))
		{
			$anchor = '#' . $params['#'];
			unset($params['#']);
		}
		$url_generator = new UrlGenerator($this->route_collection, new RequestContext());
		$route_url = $url_generator->generate($route, $params);

		if (strpos($route_url, '/') === 0)
		{
			$route_url = substr($route_url, 1);
		}

		if ($is_amp)
		{
			$route_url = str_replace(array('&amp;', '&'), array('&', '&amp;'), $route_url);
		}

		// If enable_mod_rewrite is false, we need to include app.php
		$route_prefix = $this->phpbb_root_path;
		if (empty($this->config['enable_mod_rewrite']))
		{
			$route_prefix .= 'app.' . $this->php_ext . '/';
		}

		return append_sid($route_prefix . $route_url . $anchor, false, $is_amp, $session_id);
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
