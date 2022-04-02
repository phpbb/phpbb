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

use phpbb\auth\auth;
use phpbb\cache\driver\driver_interface as cache_interface;
use phpbb\config\config;
use phpbb\cron\manager;
use phpbb\db\driver\driver_interface;
use phpbb\event\dispatcher;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\routing\helper as routing_helper;
use phpbb\symfony_request;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
* Controller helper class, contains methods that do things for controllers
*/
class helper
{
	/** @var auth */
	protected $auth;

	/** @var cache_interface */
	protected $cache;

	/** @var config */
	protected $config;

	/** @var manager */
	protected $cron_manager;

	/** @var driver_interface */
	protected $db;

	/** @var dispatcher */
	protected $dispatcher;

	/** @var language */
	protected $language;

	/* @var request_interface */
	protected $request;

	/** @var routing_helper */
	protected $routing_helper;

	/* @var symfony_request */
	protected $symfony_request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var string */
	protected $admin_path;

	/** @var string */
	protected $php_ext;

	/** @var bool $sql_explain */
	protected $sql_explain;

	/**
	 * Constructor
	 *
	 * @param auth $auth Auth object
	 * @param cache_interface $cache
	 * @param config $config Config object
	 * @param manager $cron_manager
	 * @param driver_interface $db DBAL object
	 * @param dispatcher $dispatcher
	 * @param language $language
	 * @param request_interface $request phpBB request object
	 * @param routing_helper $routing_helper Helper to generate the routes
	 * @param symfony_request $symfony_request Symfony Request object
	 * @param template $template Template object
	 * @param user $user User object
	 * @param string $root_path phpBB root path
	 * @param string $admin_path Admin path
	 * @param string $php_ext PHP extension
	 * @param bool $sql_explain Flag whether to display sql explain
	 */
	public function __construct(auth $auth, cache_interface $cache, config $config, manager $cron_manager,
								driver_interface $db, dispatcher $dispatcher, language $language,
								request_interface $request, routing_helper $routing_helper,
								symfony_request $symfony_request, template $template, user $user, $root_path,
								$admin_path, $php_ext, $sql_explain = false)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->cron_manager = $cron_manager;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->language = $language;
		$this->template = $template;
		$this->user = $user;
		$this->config = $config;
		$this->symfony_request = $symfony_request;
		$this->request = $request;
		$this->routing_helper = $routing_helper;
		$this->admin_path = $root_path . $admin_path;
		$this->php_ext = $php_ext;
		$this->sql_explain = $sql_explain;
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
	* @param bool $send_headers Whether headers should be sent by page_header(). Defaults to false for controllers.
	*
	* @return Response object containing rendered page
	*/
	public function render($template_file, $page_title = '', $status_code = 200, $display_online_list = false, $item_id = 0, $item = 'forum', $send_headers = false)
	{
		page_header($page_title, $display_online_list, $item_id, $item, $send_headers);

		$this->template->set_filenames(array(
			'body'	=> $template_file,
		));

		$run_cron = true;
		$page_footer_override = false;

		/**
		 * Execute code and/or overwrite page_footer()
		 *
		 * @event core.page_footer
		 * @var	bool	run_cron			Shall we run cron tasks
		 * @var	bool	page_footer_override	Shall we skip displaying the page footer
		 * @since 3.1.0-a1
		 * @changed 3.3.1-RC1 Added to controller helper render() method for backwards compatibility
		 */
		$vars = ['run_cron', 'page_footer_override'];
		extract($this->dispatcher->trigger_event('core.page_footer', compact($vars)));

		if (!$page_footer_override)
		{
			$this->display_footer($run_cron);
		}

		$headers = !empty($this->user->data['is_bot']) ? ['X-PHPBB-IS-BOT' => 'yes'] : [];

		$display_template = true;
		$exit_handler = true; // not used

		/**
		 * Execute code and/or modify output before displaying the template.
		 *
		 * @event core.page_footer_after
		 * @var	bool display_template	Whether or not to display the template
		 * @var	bool exit_handler		Whether or not to run the exit_handler() (no effect on controller pages)
		 *
		 * @since 3.1.0-RC5
		 * @changed 3.3.1-RC1 Added to controller helper render() method for backwards compatibility
		 */
		$vars = ['display_template', 'exit_handler'];
		extract($this->dispatcher->trigger_event('core.page_footer_after', compact($vars)));

		$response = new Response($display_template ? $this->template->assign_display('body') : '', $status_code, $headers);

		/**
		 * Modify response before output
		 *
		 * @event core.controller_helper_render_response
		 * @var	Response response	Symfony response object
		 *
		 * @since 3.3.1-RC1
		 */
		$vars = ['response'];
		extract($this->dispatcher->trigger_event('core.controller_helper_render_response', compact($vars)));

		return $response;
	}

	/**
	* Generate a URL to a route
	*
	* @param string	$route		Name of the route to travel
	* @param array	$params		String or array of additional url parameters
	* @param bool	$is_amp		Is url using &amp; (true) or & (false)
	* @param string|bool		$session_id	Possibility to use a custom session id instead of the global one
	* @param int	$reference_type	The type of reference to be generated (one of the constants)
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
	* @deprecated 3.1.3 (To be removed: 4.0.0) Use exceptions instead.
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
		$message_text = call_user_func_array(array($this->language, 'lang'), $parameters);
		$message_title = $this->language->lang($title);

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
	 * @return	void
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

	/**
	 * Handle display actions for footer, e.g. SQL report and credit line
	 *
	 * @param bool $run_cron Flag whether cron should be run
	 *
	 * @return void
	 */
	public function display_footer($run_cron = true)
	{
		$this->display_sql_report();

		$this->template->assign_vars([
				'DEBUG_OUTPUT'			=> phpbb_generate_debug_output($this->db, $this->config, $this->auth, $this->user, $this->dispatcher),
				'TRANSLATION_INFO'		=> $this->language->is_set('TRANSLATION_INFO') ? $this->language->lang('TRANSLATION_INFO') : '',
				'CREDIT_LINE'			=> $this->language->lang('POWERED_BY', '<a href="https://www.phpbb.com/">phpBB</a>&reg; Forum Software &copy; phpBB Limited'),

				'U_ACP'					=> ($this->auth->acl_get('a_') && !empty($this->user->data['is_registered'])) ? append_sid("{$this->admin_path}index.{$this->php_ext}", false, true, $this->user->session_id) : '',
		]);

		if ($run_cron)
		{
			$this->set_cron_task();
		}
	}

	/**
	 * Display SQL report
	 *
	 * @return void
	 */
	public function display_sql_report()
	{
		if ($this->sql_explain && $this->request->variable('explain', false) && $this->auth->acl_get('a_'))
		{
			$this->db->sql_report('display');
		}
	}

	/**
	 * Set cron task for footer
	 *
	 * @return void
	 */
	protected function set_cron_task()
	{
		// Call cron-type script
		$call_cron = false;
		if (!defined('IN_CRON') && !$this->config['use_system_cron'] && !$this->config['board_disable'] && !$this->user->data['is_bot'] && !$this->cache->get('_cron.lock_check'))
		{
			$call_cron = true;
			$time_now = (!empty($this->user->time_now) && is_int($this->user->time_now)) ? $this->user->time_now : time();

			// Any old lock present?
			if (!empty($this->config['cron_lock']))
			{
				$cron_time = explode(' ', $this->config['cron_lock']);

				// If 1 hour lock is present we do not set a cron task
				if ($cron_time[0] + 3600 >= $time_now)
				{
					$call_cron = false;
				}
			}
		}

		// Call cron job?
		if ($call_cron)
		{
			$task = $this->cron_manager->find_one_ready_task();

			if ($task)
			{
				$cron_task_tag = $task->get_html_tag();
				$this->template->assign_var('RUN_CRON_TASK', $cron_task_tag);
			}
			else
			{
				$this->cache->put('_cron.lock_check', true, 60);
			}
		}
	}
}
