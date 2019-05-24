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

use phpbb\exception\http_exception;

class constructor implements \phpbb\cp\constructor_interface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB admin path */
	protected $admin_path;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpBB web path */
	protected $web_path;

	/** @var string php File extension */
	protected $php_ext;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth			$auth			Auth object
	 * @param \phpbb\controller\helper	$helper			Controller helper object
	 * @param \phpbb\language\language	$lang			Language object
	 * @param \phpbb\path_helper		$path_helper	Path helper object
	 * @param \phpbb\template\template	$template		Template object
	 * @param \phpbb\user				$user			User object
	 * @param string					$admin_path		phpBB admin path
	 * @param string					$root_path		phpBB root path
	 * @param string					$php_ext		php File extension
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $lang,
		\phpbb\path_helper $path_helper,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$admin_path,
		$root_path,
		$php_ext
	)
	{
		$this->auth			= $auth;
		$this->helper		= $helper;
		$this->lang			= $lang;
		$this->template		= $template;
		$this->user			= $user;

		$this->admin_path	= $admin_path;
		$this->root_path	= $root_path;
		$this->web_path		= $path_helper->update_web_root_path($root_path);
		$this->php_ext		= $php_ext;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup()
	{
		define('ADMIN_START', true);

		require($this->root_path . 'includes/functions_acp.' . $this->php_ext);
		require($this->root_path . 'includes/functions_admin.' . $this->php_ext);

		$this->lang->add_lang('acp/common');

		// Have they authenticated (again) as an admin for this session?
		if (!isset($this->user->data['session_admin']) || !$this->user->data['session_admin'])
		{
			login_box($this->helper->route('acp_index'), $this->lang->lang('LOGIN_ADMIN_CONFIRM'), $this->lang->lang('LOGIN_ADMIN_SUCCESS'), true, false);
		}

		// Is user any type of admin? No, then stop here, each script needs to
		// check specific permissions but this is a catchall
		if (!$this->auth->acl_get('a_'))
		{
			throw new http_exception(403, 'NO_ADMIN');
		}

		// We define the admin variables now, because the user is now able to use the admin related features...
		define('IN_ADMIN', true);

		// Set custom style for admin area
		$this->template->set_custom_style([
			[
				'name'		=> 'adm',
				'ext_path'	=> 'adm/style/',
			],
		], $this->admin_path . 'style');

		$this->template->assign_vars([
			'T_ASSETS_PATH'		=> $this->web_path . 'assets',
			'T_TEMPLATE_PATH'	=> $this->web_path . $this->admin_path . 'style',
		]);
	}
}
