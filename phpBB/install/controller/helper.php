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

use Symfony\Component\HttpFoundation\Response;

/**
 * A duplicate of \phpbb\controller\helper
 *
 * This class is necessary because of controller\helper's legacy function calls
 * to page_header() page_footer() functions which has unavailable dependencies.
 */
class helper
{
	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

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

	public function __construct(\phpbb\language\language $language, \phpbb\language\language_file_helper $lang_helper, \phpbb\install\helper\navigation\navigation_provider $nav, \phpbb\template\template $template, \phpbb\path_helper $path_helper, \phpbb\symfony_request $request, \phpbb\routing\router $router, $phpbb_root_path)
	{
		$this->language = $language;
		$this->lang_helper = $lang_helper;
		$this->navigation_provider = $nav;
		$this->template = $template;
		$this->path_helper = $path_helper;
		$this->request = $request;
		$this->router = $router;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpbb_admin_path = $phpbb_root_path . 'adm/';
	}

	/**
	 * Automate setting up the page and creating the response object.
	 *
	 * @param string	$template_file	The template handle to render
	 * @param string	$page_title		The title of the page to output
	 * @param int		$status_code	The status code to be sent to the page header
	 *
	 * @return Response object containing rendered page
	 */
	public function render($template_file, $page_title = '', $status_code = 200)
	{
		$this->page_header($page_title);

		$this->template->set_filenames(array(
			'body'	=> $template_file,
		));

		return new Response($this->template->assign_display('body'), $status_code);
	}

	/**
	 * Set default template variables
	 *
	 * @param string	$page_title
	 */
	protected function page_header($page_title)
	{
		$this->template->assign_vars(array(
				'L_CHANGE'				=> $this->language->lang('CHANGE'),
				'L_COLON'				=> $this->language->lang('COLON'),
				'L_INSTALL_PANEL'		=> $this->language->lang('INSTALL_PANEL'),
				'L_SELECT_LANG'			=> $this->language->lang('SELECT_LANG'),
				'L_SKIP'				=> $this->language->lang('SKIP'),
				'PAGE_TITLE'			=> $this->language->lang($page_title),
				'T_IMAGE_PATH'			=> htmlspecialchars($this->phpbb_admin_path) . 'images/',
				'T_JQUERY_LINK'			=> $this->path_helper->get_web_root_path() . 'assets/javascript/jquery.min.js',
				'T_TEMPLATE_PATH'		=> $this->path_helper->get_web_root_path() . 'adm/style',
				'T_ASSETS_PATH'			=> $this->path_helper->get_web_root_path() . 'assets/',

				'S_CONTENT_DIRECTION' 	=> $this->language->lang('DIRECTION'),
				'S_CONTENT_FLOW_BEGIN'	=> ($this->language->lang('DIRECTION') === 'ltr') ? 'left' : 'right',
				'S_CONTENT_FLOW_END'	=> ($this->language->lang('DIRECTION') === 'ltr') ? 'right' : 'left',
				'S_CONTENT_ENCODING' 	=> 'UTF-8',

				'S_USER_LANG'			=> $this->language->lang('USER_LANG'),
			)
		);

		$this->render_navigation();
	}

	/**
	 * Render navigation
	 */
	protected function render_navigation()
	{
		// Get navigation items
		$nav_array = $this->navigation_provider->get();

		// @todo Sort navs by order

		$active_main_menu = $this->get_active_main_menu($nav_array);

		// Pass navigation to template
		foreach ($nav_array as $key => $entry)
		{
			$this->template->assign_block_vars('t_block1', array(
				'L_TITLE'		=> $this->language->lang($entry['label']),
				'S_SELECTED'	=> ($active_main_menu === $key),
				'U_TITLE'		=> $this->route($entry['route']),
			));

			if (is_array($entry[0]) && $active_main_menu === $key)
			{
				// @todo Sort navs by order

				foreach ($entry[0] as $sub_entry)
				{
					$this->template->assign_block_vars('l_block1', array(
						'L_TITLE'		=> $this->language->lang($sub_entry['label']),
						'S_SELECTED'	=> (isset($sub_entry['route']) && $sub_entry['route'] === $this->request->get('_route')),
						'U_TITLE'		=> $this->route($sub_entry['route']),
					));
				}
			}
		}
	}

	/**
	 * Returns path from route name
	 *
	 * @param string	$route_name
	 *
	 * @return string
	 */
	public function route($route_name)
	{
		$url = $this->router->generate($route_name);

		return $url;
	}

	/**
	 * Render language select form
	 */
	protected function render_language_select()
	{
		$langs = $this->lang_helper->get_available_languages();
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
					if (isset($sub_menus['route']) &&$sub_menus['route'] === $active_route)
					{
						return $current_menu;
					}
				}
			}
		}

		return false;
	}
}
