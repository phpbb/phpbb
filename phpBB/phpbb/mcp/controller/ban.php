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

namespace phpbb\mcp\controller;

class ban
{
	/** @var \phpbb\acp\controller\ban */
	protected $acp_ban;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\acp\controller\ban			$acp_ban		ACP Ban controller
	 * @param \phpbb\auth\auth					$auth			Auth object
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\event\dispatcher			$dispatcher		Event dispatcher object
	 * @param \phpbb\controller\helper			$helper			Controller helper object
	 * @param \phpbb\language\language			$language		Language object
	 * @param \phpbb\request\request			$request		Request object
	 * @param \phpbb\template\template			$template		Template object
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 * @param array								$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\acp\controller\ban $acp_ban,
		\phpbb\auth\auth $auth,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->acp_ban		= $acp_ban;
		$this->auth			= $auth;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->helper		= $helper;
		$this->language		= $language;
		$this->request		= $request;
		$this->template		= $template;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	public function main($mode)
	{
		$this->language->add_lang(['acp/ban', 'acp/users']);

		if (!function_exists('user_ban'))
		{
			include($this->root_path . 'includes/functions_user.' . $this->php_ext);
		}

		$l_mode = $this->language->lang(strtoupper($mode) . '_BAN');
		$u_mode = $this->helper->route("mcp_ban_{$mode}");
		$return = '<br><br>' . $this->language->lang('RETURN_PAGE', '<a href="' . $u_mode . '">&laquo; ', '</a>');

		$bansubmit		= $this->request->is_set_post('bansubmit');
		$unbansubmit	= $this->request->is_set_post('unbansubmit');


		/**
		 * Use this event to pass perform actions when a ban is issued or revoked
		 *
		 * @event core.mcp_ban_main
		 * @var bool	bansubmit	True if a ban is issued
		 * @var bool	unbansubmit	True if a ban is removed
		 * @var string	mode		Mode of the ban that is being worked on
		 * @since 3.1.0-RC5
		 */
		$vars = ['bansubmit', 'unbansubmit', 'mode'];
		extract($this->dispatcher->trigger_event('core.mcp_ban_main', compact($vars)));

		// Ban submitted?
		if ($bansubmit)
		{
			// Grab the list of entries
			$ban				= $this->request->variable('ban', '', $mode === 'user');
			$ban_length			= $this->request->variable('banlength', 0);
			$ban_length_other	= $this->request->variable('banlengthother', '');
			$ban_exclude		= $this->request->variable('banexclude', 0);
			$ban_reason			= $this->request->variable('banreason', '', true);
			$ban_give_reason	= $this->request->variable('bangivereason', '', true);

			if ($ban)
			{
				if (confirm_box(true))
				{
					$abort_ban = false;

					/**
					 * Use this event to modify the ban details before the ban is performed
					 *
					 * @event core.mcp_ban_before
					 * @var	string	mode				One of the following: user, ip, email
					 * @var	string	ban					Either string or array with usernames, ips or email addresses
					 * @var	int		ban_length			Ban length in minutes
					 * @var	string	ban_length_other	Ban length as a date (YYYY-MM-DD)
					 * @var	bool	ban_exclude			Are we banning or excluding from another ban
					 * @var	string	ban_reason			Ban reason displayed to moderators
					 * @var	string	ban_give_reason		Ban reason displayed to the banned user
					 * @var	mixed	abort_ban			Either false, or an error message that is displayed to the user.
					 *									If a string is given the bans are not issued.
					 * @since 3.1.0-RC5
					 */
					$vars = [
						'mode',
						'ban',
						'ban_length',
						'ban_length_other',
						'ban_exclude',
						'ban_reason',
						'ban_give_reason',
						'abort_ban',
					];
					extract($this->dispatcher->trigger_event('core.mcp_ban_before', compact($vars)));

					if ($abort_ban)
					{
						return trigger_error($abort_ban . $return, E_USER_WARNING);
					}

					user_ban($mode, $ban, $ban_length, $ban_length_other, $ban_exclude, $ban_reason, $ban_give_reason);

					/**
					 * Use this event to perform actions after the ban has been performed
					 *
					 * @event core.mcp_ban_after
					 * @var	string	mode				One of the following: user, ip, email
					 * @var	string	ban					Either string or array with usernames, ips or email addresses
					 * @var	int		ban_length			Ban length in minutes
					 * @var	string	ban_length_other	Ban length as a date (YYYY-MM-DD)
					 * @var	bool	ban_exclude			Are we banning or excluding from another ban
					 * @var	string	ban_reason			Ban reason displayed to moderators
					 * @var	string	ban_give_reason		Ban reason displayed to the banned user
					 * @since 3.1.0-RC5
					 */
					$vars = [
						'mode',
						'ban',
						'ban_length',
						'ban_length_other',
						'ban_exclude',
						'ban_reason',
						'ban_give_reason',
					];
					extract($this->dispatcher->trigger_event('core.mcp_ban_after', compact($vars)));

					return $this->helper->message($this->language->lang('BAN_UPDATE_SUCCESSFUL') . $return);
				}
				else
				{
					$hidden_fields = [
						'mode'				=> $mode,
						'ban'				=> $ban,
						'bansubmit'			=> true,
						'banlength'			=> $ban_length,
						'banlengthother'	=> $ban_length_other,
						'banexclude'		=> $ban_exclude,
						'banreason'			=> $ban_reason,
						'bangivereason'		=> $ban_give_reason,
					];

					/**
					 * Use this event to pass data from the ban form to the confirmation screen
					 *
					 * @event core.mcp_ban_confirm
					 * @var	array	hidden_fields	Hidden fields that are passed through the confirm screen
					 * @since 3.1.0-RC5
					 */
					$vars = ['hidden_fields'];
					extract($this->dispatcher->trigger_event('core.mcp_ban_confirm', compact($vars)));

					confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields($hidden_fields));

					return redirect($u_mode);
				}
			}
		}
		else if ($unbansubmit)
		{
			$ban = $this->request->variable('unban', ['']);

			if ($ban)
			{
				if (confirm_box(true))
				{
					user_unban($mode, $ban);

					return $this->helper->message($this->language->lang('BAN_UPDATE_SUCCESSFUL') . $return);
				}
				else
				{
					confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
						'mode'			=> $mode,
						'unbansubmit'	=> true,
						'unban'			=> $ban,
					]));

					return redirect($u_mode);
				}
			}
		}

		switch ($mode)
		{
			case 'user':
				$l_ban_cell = $this->language->lang('USERNAME');
			break;

			case 'ip':
				$l_ban_cell = $this->language->lang('IP_HOSTNAME');
			break;

			case 'email':
				$l_ban_cell = $this->language->lang('EMAIL_ADDRESS');
			break;

			default:
				$l_ban_cell = '';
			break;
		}

		display_ban_end_options();
		display_ban_options($mode);

		$this->template->assign_vars([
			'L_TITLE'				=> $l_mode,
			'L_EXPLAIN'				=> $this->language->lang(strtoupper($mode) . '_BAN_EXPLAIN'),
			'L_BAN_CELL'			=> $l_ban_cell,
			'L_BAN_EXCLUDE_EXPLAIN'	=> $this->language->lang(strtoupper($mode) . '_BAN_EXCLUDE_EXPLAIN'),
			'L_UNBAN_TITLE'			=> $this->language->lang(strtoupper($mode) . '_UNBAN'),
			'L_UNBAN_EXPLAIN'		=> $this->language->lang(strtoupper($mode) . '_UNBAN_EXPLAIN'),
			'L_NO_BAN_CELL'			=> $this->language->lang(strtoupper($mode) . '_NO_BANNED'),

			'S_USERNAME_BAN'		=> $mode === 'user',

			'U_ACTION'				=> $u_mode,
			'U_FIND_USERNAME'		=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=mcp_ban&amp;field=ban'),
		]);

		if ($mode === 'email' && !$this->auth->acl_get('a_user'))
		{
			return trigger_error('NOT_AUTHORISED', E_USER_WARNING);
		}

		// As a "service" we will check if any post id is specified and populate the username of the poster id if given
		$post_id = $this->request->variable('p', 0);
		$user_id = $this->request->variable('u', 0);
		$pre_fill = false;

		if ($user_id && $user_id <> ANONYMOUS)
		{
			$sql = 'SELECT username, user_email, user_ip
				FROM ' . $this->tables['users'] . '
				WHERE user_id = ' . (int) $user_id;
			$result = $this->db->sql_query($sql);
			switch ($mode)
			{
				case 'user':
					$pre_fill = (string) $this->db->sql_fetchfield('username');
				break;

				case 'ip':
					$pre_fill = (string) $this->db->sql_fetchfield('user_ip');
				break;

				case 'email':
					$pre_fill = (string) $this->db->sql_fetchfield('user_email');
				break;
			}
			$this->db->sql_freeresult($result);
		}
		else if ($post_id)
		{
			$post_info = phpbb_get_post_data([$post_id], 'm_ban');

			if (!empty($post_info) && !empty($post_info[$post_id]))
			{
				switch ($mode)
				{
					case 'user':
						$pre_fill = $post_info[$post_id]['username'];
					break;

					case 'ip':
						$pre_fill = $post_info[$post_id]['poster_ip'];
					break;

					case 'email':
						$pre_fill = $post_info[$post_id]['user_email'];
					break;
				}

			}
		}

		if ($pre_fill)
		{
			// left for legacy template compatibility
			$this->template->assign_var('USERNAMES', $pre_fill);
			$this->template->assign_var('BAN_QUANTIFIER', $pre_fill);
		}

		return $this->helper->render('mcp_ban.html', $l_mode);
	}
}
