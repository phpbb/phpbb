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

namespace phpbb\install\controller;

use phpbb\install\helper\config;
use phpbb\install\helper\navigation\navigation_provider;
use phpbb\language\language;
use phpbb\language\language_file_helper;
use phpbb\path_helper;
use phpbb\request\request;
use phpbb\request\request_interface;
use phpbb\routing\router;
use phpbb\symfony_request;
use phpbb\template\template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * A duplicate of \phpbb\controller\helper
 *
 * This class is necessary because of controller\helper's legacy function calls
 * to page_header() page_footer() functions which has unavailable dependencies.
 */
class helper
{
	/**
	 * @var config
	 */
	protected $installer_config;

	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	/**
	 * @var bool|string
	 */
	protected $language_cookie;

	/**
	 * @var \phpbb\language\language_file_helper
	 */
	protected $lang_helper;

	/**
	 * @var \phpbb\install\helper\navigation\navigation_provider
	 */
	protected $navigation_provider;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * @var \phpbb\path_helper
	 */
	protected $path_helper;

	/**
	 * @var \phpbb\request\request
	 */
	protected $phpbb_request;

	/**
	 * @var \phpbb\symfony_request
	 */
	protected $request;

	/**
	 * @var \phpbb\routing\router
	 */
	protected $router;

	/**
	 * @var string
	 */
	protected $phpbb_admin_path;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * Constructor
	 *
	 * @param config				$config
	 * @param language				$language
	 * @param language_file_helper	$lang_helper
	 * @param navigation_provider	$nav
	 * @param template				$template
	 * @param path_helper			$path_helper
	 * @param request				$phpbb_request
	 * @param symfony_request		$request
	 * @param router				$router
	 * @param string				$phpbb_root_path
	 */
	public function __construct(config $config, language $language, language_file_helper $lang_helper, navigation_provider $nav, template $template, path_helper $path_helper, request $phpbb_request, symfony_request $request, router $router, $phpbb_root_path)
	{
		$this->installer_config = $config;
		$this->language = $language;
		$this->language_cookie = false;
		$this->lang_helper = $lang_helper;
		$this->navigation_provider = $nav;
		$this->template = $template;
		$this->path_helper = $path_helper;
		$this->phpbb_request = $phpbb_request;
		$this->request = $request;
		$this->router = $router;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpbb_admin_path = $phpbb_root_path . 'adm/';
	}

	/**
	 * Automate setting up the page and creating the response object.
	 *
	 * @param string	$template_file		The template handle to render
	 * @param string	$page_title			The title of the page to output
	 * @param bool		$selected_language	True to enable language selector it, false otherwise
	 * @param int		$status_code		The status code to be sent to the page header
	 *
	 * @return Response object containing rendered page
	 */
	public function render($template_file, $page_title = '', $selected_language = false, $status_code = 200)
	{
		$this->page_header($page_title, $selected_language);

		$this->template->set_filenames(array(
			'body'	=> $template_file,
		));

		$response = new Response($this->template->assign_display('body'), $status_code);

		// Set language cookie
		if ($this->language_cookie !== false)
		{
			$cookie = new Cookie('lang', $this->language_cookie, time() + 3600);
			$response->headers->setCookie($cookie);

			$this->language_cookie = false;
		}

		return $response;
	}

	/**
	 * Returns path from route name
	 *
	 * @param string	$route_name
	 * @param array		$parameters
	 *
	 * @return string
	 */
	public function route($route_name, $parameters = array())
	{
		$url = $this->router->generate($route_name, $parameters);

		return $url;
	}

	/**
	 * Handles language selector form
	 */
	public function handle_language_select()
	{
		$lang = null;

		// Check if language form has been submited
		$submit = $this->phpbb_request->variable('change_lang', '');
		if (!empty($submit))
		{
			$lang = $this->phpbb_request->variable('language', '');
		}

		// Retrieve language from cookie
		$lang_cookie = $this->phpbb_request->variable('lang', '', false, request_interface::COOKIE);
		if (empty($lang) && !empty($lang_cookie))
		{
			$lang = $lang_cookie;
		}

		$lang = (!empty($lang) && strpos($lang, '/') === false) ? $lang : null;
		$this->language_cookie = $lang;

		$this->render_language_select($lang);

		if ($lang !== null)
		{
			$this->language->set_user_language($lang, true);
			$this->installer_config->set('user_language', $lang);
		}
	}

	/**
	 * Process navigation data to reflect active/completed stages
	 *
	 * @param \phpbb\install\helper\iohandler\iohandler_interface|null	$iohandler
	 */
	public function handle_navigation($iohandler = null)
	{
		$nav_data = $this->installer_config->get_navigation_data();

		// Set active navigation stage
		if (isset($nav_data['active']) && is_array($nav_data['active']))
		{
			if ($iohandler !== null)
			{
				$iohandler->set_active_stage_menu($nav_data['active']);
			}

			$this->navigation_provider->set_nav_property($nav_data['active'], array(
				'selected'	=> true,
				'completed'	=> false,
			));
		}

		// Set finished navigation stages
		if (isset($nav_data['finished']) && is_array($nav_data['finished']))
		{
			foreach ($nav_data['finished'] as $finished_stage)
			{
				if ($iohandler !== null)
				{
					$iohandler->set_finished_stage_menu($finished_stage);
				}

				$this->navigation_provider->set_nav_property($finished_stage, array(
					'selected'	=> false,
					'completed'	=> true,
				));
			}
		}
	}

	/**
	 * Set default template variables
	 *
	 * @param string	$page_title			Title of the page
	 * @param bool		$selected_language	True to enable language selector it, false otherwise
	 */
	protected function page_header($page_title, $selected_language = false)
	{
		// Path to templates
		$paths = array($this->phpbb_root_path . 'install/update/new/adm/', $this->phpbb_admin_path);
		$paths = array_filter($paths, 'is_dir');
		$path = array_shift($paths);
		$path = substr($path, strlen($this->phpbb_root_path));

		$this->template->assign_vars(array(
			'L_CHANGE'				=> $this->language->lang('CHANGE'),
			'L_COLON'				=> $this->language->lang('COLON'),
			'L_INSTALL_PANEL'		=> $this->language->lang('INSTALL_PANEL'),
			'L_SELECT_LANG'			=> $this->language->lang('SELECT_LANG'),
			'L_SKIP'				=> $this->language->lang('SKIP'),
			'PAGE_TITLE'			=> $this->language->lang($page_title),
			'T_IMAGE_PATH'			=> $this->path_helper->get_web_root_path() . $path . 'images',
			'T_JQUERY_LINK'			=> $this->path_helper->get_web_root_path() . $path . '../assets/javascript/jquery.min.js',
			'T_TEMPLATE_PATH'		=> $this->path_helper->get_web_root_path() . $path . 'style',
			'T_ASSETS_PATH'			=> $this->path_helper->get_web_root_path() . $path . '../assets',

			'S_CONTENT_DIRECTION' 	=> $this->language->lang('DIRECTION'),
			'S_CONTENT_FLOW_BEGIN'	=> ($this->language->lang('DIRECTION') === 'ltr') ? 'left' : 'right',
			'S_CONTENT_FLOW_END'	=> ($this->language->lang('DIRECTION') === 'ltr') ? 'right' : 'left',
			'S_CONTENT_ENCODING' 	=> 'UTF-8',
			'S_LANG_SELECT'			=> $selected_language,

			'S_USER_LANG'			=> $this->language->lang('USER_LANG'),
		));

		$this->render_navigation();
	}

	/**
	 * Render navigation
	 */
	protected function render_navigation()
	{
		// Get navigation items
		$nav_array = $this->navigation_provider->get();
		$nav_array = $this->sort_navigation_level($nav_array);

		$active_main_menu = $this->get_active_main_menu($nav_array);

		// Pass navigation to template
		foreach ($nav_array as $key => $entry)
		{
			$this->template->assign_block_vars('t_block1', array(
				'L_TITLE' => $this->language->lang($entry['label']),
				'S_SELECTED' => ($active_main_menu === $key),
				'U_TITLE' => $this->route($entry['route']),
			));

			if (is_array($entry[0]) && $active_main_menu === $key)
			{
				$entry[0] = $this->sort_navigation_level($entry[0]);

				foreach ($entry[0] as $name => $sub_entry)
				{
					if (isset($sub_entry['stage']) && $sub_entry['stage'] === true)
					{
						$this->template->assign_block_vars('l_block2', array(
							'L_TITLE' => $this->language->lang($sub_entry['label']),
							'S_SELECTED' => (isset($sub_entry['selected']) && $sub_entry['selected'] === true),
							'S_COMPLETE' => (isset($sub_entry['completed']) && $sub_entry['completed'] === true),
							'STAGE_NAME' => $name,
						));
					}
					else
					{
						$this->template->assign_block_vars('l_block1', array(
							'L_TITLE' => $this->language->lang($sub_entry['label']),
							'S_SELECTED' => (isset($sub_entry['route']) && $sub_entry['route'] === $this->request->get('_route')),
							'U_TITLE' => $this->route($sub_entry['route']),
						));
					}
				}
			}
		}
	}

	/**
	 * Render language select form
	 *
	 * @param string	$selected_language
	 */
	protected function render_language_select($selected_language = null)
	{
		$langs = $this->lang_helper->get_available_languages();
		foreach ($langs as $lang)
		{
			$this->template->assign_block_vars('language_select_item', array(
				'VALUE' => $lang['iso'],
				'NAME' => $lang['local_name'],
				'SELECTED' => ($lang['iso'] === $selected_language),
			));
		}
	}

	/**
	 * Returns the name of the active main menu item
	 *
	 * @param array	$nav_array
	 *
	 * @return string|bool	Returns the name of the active main menu element, if the element not found, returns false
	 */
	protected function get_active_main_menu($nav_array)
	{
		$active_route = $this->request->get('_route');

		foreach ($nav_array as $nav_name => $nav_options)
		{
			$current_menu = $nav_name;

			if (isset($nav_options['route']) && $nav_options['route'] === $active_route)
			{
				return $nav_name;
			}

			if (is_array($nav_options[0]))
			{
				foreach ($nav_options[0] as $sub_menus)
				{
					if (isset($sub_menus['route']) && $sub_menus['route'] === $active_route)
					{
						return $current_menu;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Sorts the top level of navigation array
	 *
	 * @param array	$nav_array	Navigation array
	 *
	 * @return array
	 */
	protected function sort_navigation_level($nav_array)
	{
		$sorted = array();
		foreach ($nav_array as $key => $nav)
		{
			$order = (isset($nav['order'])) ? $nav['order'] : 0;
			$sorted[$order][$key] = $nav;
		}

		// Linearization of navigation array
		$nav_array = array();
		ksort($sorted);
		foreach ($sorted as $nav)
		{
			$nav_array = array_merge($nav_array, $nav);
		}

		return $nav_array;
	}
}
