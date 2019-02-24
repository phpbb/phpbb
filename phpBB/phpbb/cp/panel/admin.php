<?php

namespace phpbb\cp\panel;

class admin
{
	protected $acp_collection;
	protected $auth;
	protected $lang;
	protected $path_helper;
	protected $template;
	protected $user;
	protected $admin_path;
	protected $root_path;

	public function __construct(
		$acp_collection,
		\phpbb\auth\auth $auth,
		\phpbb\language\language $lang,
		\phpbb\path_helper $path_helper,
		\phpbb\template\template $template,
		\phpbb\user $user
	)
	{
		$this->acp_collection	= $acp_collection;
		$this->auth				= $auth;
		$this->lang				= $lang;
		$this->path_helper		= $path_helper;
		$this->template			= $template;
		$this->user				= $user;

		$this->root_path		= $path_helper->get_phpbb_root_path();
		$this->admin_path		= $this->root_path . $path_helper->get_adm_relative_path();
	}

	public function build()
	{
		define('ADMIN_START', true);

		if (!function_exists('get_database_size'))
		{
			include($this->root_path . 'includes/functions_admin.php');
		}

		if (!function_exists('adm_page_header'))
		{
			include($this->root_path . 'includes/functions_acp.php');
		}

		// Have they authenticated (again) as an admin for this session?
		if (!isset($user->data['session_admin']) || !$this->user->data['session_admin'])
		{
	#		login_box('', $this->lang->lang('LOGIN_ADMIN_CONFIRM'), $this->lang->lang('LOGIN_ADMIN_SUCCESS'), true, false);
		}

		// Is user any type of admin? No, then stop here, each script needs to
		// check specific permissions but this is a catchall
		if (!$this->auth->acl_get('a_'))
		{
			throw new \phpbb\exception\http_exception(403, 'NO_ADMIN');
		}

		// We define the admin variables now, because the user is now able to use the admin related features...
		define('IN_ADMIN', true);

		$this->template->set_custom_style(array(
			array(
				'name' 		=> 'adm',
				'ext_path' 	=> 'style',
			),
		), $this->admin_path . 'style');

		$this->template->assign_var('T_ASSETS_PATH', $this->path_helper->update_web_root_path($this->root_path) . 'assets');
		$this->template->assign_var('T_TEMPLATE_PATH', $this->path_helper->update_web_root_path($this->admin_path) . 'style');
	}

	public function get_collection()
	{
		return $this->acp_collection;
	}

	public function get_menu()
	{
		return [
			'phpbb.acp.info.management'	=> [
				'phpbb.acp.info.index'		=> null,
			],
			'phpbb.acp.info.settings'	=> [
				'phpbb.acp.info.settings.general'	=> [
					'phpbb.acp.info.settings.board',
					'phpbb.acp.info.settings.features',
					'phpbb.acp.info.settings.post',
					'phpbb.acp.info.settings.pm',
					'phpbb.acp.info.settings.avatar',
					'phpbb.acp.info.settings.signature',
					'phpbb.acp.info.settings.registration',
					'phpbb.acp.info.settings.feed',
				],
				'phpbb.acp.info.settings.server.cat'	=> [
					'phpbb.acp.info.settings.server',
					'phpbb.acp.info.settings.cookie',
					'phpbb.acp.info.settings.security',
					'phpbb.acp.info.settings.load',
				],
				'phpbb.acp.info.settings.client'	=> [
					'phpbb.acp.info.settings.auth',
					'phpbb.acp.info.settings.email',
				],
			],
		];
	}
}
