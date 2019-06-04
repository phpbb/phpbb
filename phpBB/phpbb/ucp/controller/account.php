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

namespace phpbb\ucp\controller;

use phpbb\exception\http_exception;
use Symfony\Component\HttpFoundation\Response;

class account
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\ucp\controller\activate */
	protected $ucp_activate;

	/** @var \phpbb\ucp\controller\confirm */
	protected $ucp_confirm;

	/** @var \phpbb\ucp\controller\login_link */
	protected $ucp_login_link;

	/** @var \phpbb\ucp\controller\permissions */
	protected $ucp_permissions;

	/** @var \phpbb\ucp\controller\register */
	protected $ucp_register;

	/** @var \phpbb\ucp\controller\remind */
	protected $ucp_remind;

	/** @var \phpbb\ucp\controller\resend */
	protected $ucp_resend;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config				$config
	 * @param \phpbb\event\dispatcher			$dispatcher
	 * @param \phpbb\controller\helper			$helper
	 * @param \phpbb\language\language			$lang
	 * @param \phpbb\request\request			$request
	 * @param \phpbb\template\template			$template
	 * @param \phpbb\user						$user
	 * @param \phpbb\ucp\controller\activate	$ucp_activate
	 * @param \phpbb\ucp\controller\confirm		$ucp_confirm
	 * @param \phpbb\ucp\controller\login_link	$ucp_login_link
	 * @param \phpbb\ucp\controller\permissions	$ucp_permissions
	 * @param \phpbb\ucp\controller\register	$ucp_register
	 * @param \phpbb\ucp\controller\remind		$ucp_remind
	 * @param \phpbb\ucp\controller\resend		$ucp_resend
	 * @param string							$root_path
	 * @param string							$php_ext
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $lang,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		activate $ucp_activate,
		confirm $ucp_confirm,
		login_link $ucp_login_link,
		permissions $ucp_permissions,
		register $ucp_register,
		remind $ucp_remind,
		resend $ucp_resend,
		$root_path,
		$php_ext
	)
	{
		$this->config			= $config;
		$this->dispatcher		= $dispatcher;
		$this->helper			= $helper;
		$this->lang				= $lang;
		$this->request			= $request;
		$this->template			= $template;
		$this->user				= $user;

		$this->ucp_activate		= $ucp_activate;
		$this->ucp_confirm		= $ucp_confirm;
		$this->ucp_login_link	= $ucp_login_link;
		$this->ucp_permissions	= $ucp_permissions;
		$this->ucp_register		= $ucp_register;
		$this->ucp_remind		= $ucp_remind;
		$this->ucp_resend		= $ucp_resend;

		$this->root_path		= $root_path;
		$this->php_ext			= $php_ext;
	}

	/**
	 * Distributor for specific account actions.
	 *
	 * @param string	$mode	The account mode
	 * @return Response
	 */
	function main($mode)
	{
		switch ($mode)
		{
			case 'activate':
				return $this->ucp_activate->main();

			case 'resend_activation':
				return $this->ucp_resend->main();

			case 'send_password':
				return $this->ucp_remind->main();

			case 'register':
				return $this->ucp_register->main();

			case 'confirm':
				// @todo not sure what this should return?
				return $this->ucp_confirm->main();

			case 'login_link':
				return $this->ucp_login_link->main();

			case 'permissions_switch':
				return $this->ucp_permissions->permissions_switch();

			case 'permissions_restore':
				return $this->ucp_permissions->permissions_restore();

			case 'terms':
			case 'privacy':
				return $this->terms_and_privacy($mode);

			case 'login':
			case 'logout':
			case 'delete_cookies':
				return $this->$mode();

			default:
				throw new http_exception(400, 'NO_MODE');
		}
	}

	/**
	 * Handle "login" action
	 *
	 * @return Response
	 */
	protected function login()
	{
		if ($this->user->data['is_registered'])
		{
			return redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
		}

		return login_box($this->request->variable('redirect', "index.{$this->php_ext}"));
	}

	/**
	 * Handle the "logout" action
	 *
	 * @return Response
	 */
	protected function logout()
	{
		if ($this->user->data['user_id'] != ANONYMOUS && $this->request->is_set('sid') && $this->request->variable('sid', '') === $this->user->session_id)
		{
			$this->user->session_kill();
		}
		else if ($this->user->data['user_id'] != ANONYMOUS)
		{
			$this->helper->assign_meta_refresh_var(3, append_sid("{$this->root_path}index.{$this->php_ext}"));

			$return = $this->lang->lang('RETURN_INDEX', '<a href="' . append_sid("{$this->root_path}index.{$this->php_ext}") . '">', '</a> ');

			return $this->helper->message($this->lang->lang('LOGOUT_FAILED') . '<br /><br />' . $return);
		}

		return redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
	}

	/**
	 * Display the "Terms of Use" or "Privacy Policy".
	 *
	 * @param string	$mode		The mode (terms|privacy)
	 * @return Response
	 */
	protected function terms_and_privacy($mode)
	{
		$title = $mode === 'terms' ? 'TERMS_USE' : 'PRIVACY';
		$message = $mode === 'terms' ? 'TERMS_OF_USE_CONTENT' : 'PRIVACY_POLICY';

		if (!$this->lang->is_set($message))
		{
			if ($this->user->data['is_registered'])
			{
				return redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
			}

			return login_box();
		}

		$this->template->assign_vars([
			'S_AGREEMENT'		=> true,

			'AGREEMENT_TITLE'	=> $this->lang->lang($title),
			'AGREEMENT_TEXT'	=> $this->lang->lang($message, $this->config['sitename'], generate_board_url()),

			'L_BACK'			=> $this->lang->lang('BACK_TO_PREV'),
			'U_BACK'			=> $this->helper->route('ucp_account', ['mode' => 'login']),
		]);

		return $this->helper->render('ucp_agreement.html', $this->lang->lang($title));
	}

	/**
	 * Delete cookies with dynamic names.
	 *
	 * Do NOT delete poll cookies!
	 *
	 * @return Response
	 */
	protected function delete_cookies()
	{
		if (confirm_box(true))
		{
			$set_time = time() - 31536000;

			foreach ($this->request->variable_names(\phpbb\request\request_interface::COOKIE) as $cookie_name)
			{
				// Only delete board cookies, no other ones...
				if (strpos($cookie_name, $this->config['cookie_name'] . '_') !== 0)
				{
					continue;
				}

				$cookie_name = str_replace($this->config['cookie_name'] . '_', '', $cookie_name);
				$retain_cookie = false;

				/**
				 * Event to save custom cookies from deletion
				 *
				 * @event core.ucp_delete_cookies
				 * @var	string	cookie_name		Cookie name to checking
				 * @var	bool	retain_cookie	Do we retain our cookie or not, true if retain
				 * @since 3.1.3-RC1
				 */
				$vars = ['cookie_name', 'retain_cookie'];
				extract($this->dispatcher->trigger_event('core.ucp_delete_cookies', compact($vars)));

				if ($retain_cookie)
				{
					continue;
				}

				// Polls are stored as {cookie_name}_poll_{topic_id}, cookie_name_ got removed, therefore checking for poll_
				if (strpos($cookie_name, 'poll_') !== 0)
				{
					$this->user->set_cookie($cookie_name, '', $set_time);
				}
			}

			$this->user->set_cookie('track', '', $set_time);
			$this->user->set_cookie('u', '', $set_time);
			$this->user->set_cookie('k', '', $set_time);
			$this->user->set_cookie('sid', '', $set_time);

			// We destroy the session here, the user will be logged out nevertheless
			$this->user->session_kill();
			$this->user->session_begin();

			$this->helper->assign_meta_refresh_var(3, append_sid("{$this->root_path}index.{$this->php_ext}"));

			$return = $this->lang->lang('RETURN_INDEX', '<a href="' . append_sid("{$this->root_path}index.{$this->php_ext}") . '">', '</a>');

			return $this->helper->message($this->lang->lang('COOKIES_DELETED') . '<br /><br />' . $return);
		}
		else
		{
			confirm_box(false, 'DELETE_COOKIES', '', 'confirm_body.html', $this->helper->get_current_url());

			return redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
		}
	}
}
