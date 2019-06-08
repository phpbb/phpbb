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

class permissions
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\acp\helper\auth_admin */
	protected $auth_admin;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth					$auth			Auth object
	 * @param \phpbb\acp\helper\auth_admin		$auth_admin		Auth admin object
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\event\dispatcher			$dispatcher		Event dispatcher object
	 * @param \phpbb\controller\helper			$helper			Controller helper object
	 * @param \phpbb\language\language			$lang			Language object
	 * @param \phpbb\log\log					$log			Log object
	 * @param \phpbb\request\request			$request		Request object
	 * @param \phpbb\user						$user			User object
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 * @param array								$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\acp\helper\auth_admin $auth_admin,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth				= $auth;
		$this->auth_admin		= $auth_admin;
		$this->db				= $db;
		$this->dispatcher		= $dispatcher;
		$this->helper			= $helper;
		$this->lang				= $lang;
		$this->log				= $log;
		$this->request			= $request;
		$this->user				= $user;

		$this->root_path		= $root_path;
		$this->php_ext			= $php_ext;
		$this->tables			= $tables;
	}

	/**
	 * Handle the permission switching.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function permissions_switch()
	{
		$user_id = $this->request->variable('u', 0);

		$sql = 'SELECT *
			FROM ' . $this->tables['users'] . '
			WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$user_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$this->auth->acl_get('a_switchperm') || !$user_row || $user_id == $this->user->data['user_id'] || !check_link_hash($this->request->variable('hash', ''), 'switchperm'))
		{
			return redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
		}

		if (!$this->auth_admin->ghost_permissions($user_id, $this->user->data['user_id']))
		{
			return redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
		}

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACL_TRANSFER_PERMISSIONS', false, [$user_row['username']]);

		$message = $this->lang->lang('PERMISSIONS_TRANSFERRED', $user_row['username']) . '<br /><br />' . $this->lang->lang('RETURN_INDEX', '<a href="' . append_sid("{$this->root_path}index.{$this->php_ext}") . '">', '</a>');

		/**
		 * Event to run code after permissions are switched.
		 *
		 * @event core.ucp_switch_permissions
		 * @var	int		user_id		User ID to switch permission to
		 * @var	array	user_row	User data
		 * @var	string	message		Success message
		 * @since 3.1.11-RC1
		 */
		$vars = ['user_id', 'user_row', 'message'];
		extract($this->dispatcher->trigger_event('core.ucp_switch_permissions', compact($vars)));

		return $this->helper->message($message);
	}

	/**
	 * Handle restoring a permission switch.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function permissions_restore()
	{
		if (!$this->user->data['user_perm_from'] || !$this->auth->acl_get('a_switchperm'))
		{
			return redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
		}

		$this->auth->acl_cache($this->user->data);

		$sql = 'SELECT username
			FROM ' . $this->tables['users'] . '
			WHERE user_id = ' . (int) $this->user->data['user_perm_from'];
		$result = $this->db->sql_query($sql);
		$username = $this->db->sql_fetchfield('username');
		$this->db->sql_freeresult($result);

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACL_RESTORE_PERMISSIONS', false, [$username]);

		$message = $this->lang->lang('PERMISSIONS_RESTORED') . '<br /><br />' . $this->lang->lang('RETURN_INDEX', '<a href="' . append_sid("{$this->root_path}index.{$this->php_ext}") . '">', '</a>');

		/**
		 * Event to run code after permissions are restored
		 *
		 * @event core.ucp_restore_permissions
		 * @var	string	username	User name
		 * @var	string	message		Success message
		 * @since 3.1.11-RC1
		 */
		$vars = ['username', 'message'];
		extract($this->dispatcher->trigger_event('core.ucp_restore_permissions', compact($vars)));

		return $this->helper->message($message);
	}
}
