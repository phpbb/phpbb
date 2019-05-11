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
	/** @var \phpbb\acp\ban */
	protected $acp_ban;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var string phpBB users table */
	protected $users_table;

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\acp\ban					$acp_ban		ACP Ban controller
	 * @param \phpbb\auth\auth					$auth			Auth object
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\event\dispatcher			$dispatcher		Event dispatcher object
	 * @param \phpbb\language\language			$lang			Language object
	 * @param \phpbb\request\request			$request		Request object
	 * @param \phpbb\template\template			$template		Template object
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 * @param string							$users_table	phpBB users table
	 */
	public function __construct(
		\phpbb\acp\ban $acp_ban,
		\phpbb\auth\auth $auth,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\language\language $lang,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		$root_path,
		$php_ext,
		$users_table
	)
	{
		$this->acp_ban		= $acp_ban;
		$this->auth			= $auth;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->lang			= $lang;
		$this->request		= $request;
		$this->template		= $template;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->users_table	= $users_table;
	}

	function main($id, $mode)
	{
		$this->lang->add_lang(['acp/ban', 'acp/users']);

		if (!function_exists('user_ban'))
		{
			include($this->root_path . 'includes/functions_user.' . $this->php_ext);
		}

		$bansubmit		= $this->request->is_set_post('bansubmit');
		$unbansubmit	= $this->request->is_set_post('unbansubmit');

		$this->tpl_name = 'mcp_ban';

		/**
		 * Use this event to pass perform actions when a ban is issued or revoked
		 *
		 * @event core.mcp_ban_main
		 * @var	bool	bansubmit	True if a ban is issued
		 * @var	bool	unbansubmit	True if a ban is removed
		 * @var	string	mode		Mode of the ban that is being worked on
		 * @since 3.1.0-RC5
		 */
		$vars = [
			'bansubmit',
			'unbansubmit',
			'mode',
		];
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
						trigger_error($abort_ban);
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

					trigger_error($this->lang->lang('BAN_UPDATE_SUCCESSFUL') . '<br /><br /><a href="' . $this->u_action . '">&laquo; ' . $this->lang->lang('BACK_TO_PREV') . '</a>');
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

					confirm_box(false, $this->lang->lang('CONFIRM_OPERATION'), build_hidden_fields($hidden_fields));
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

					trigger_error($this->lang->lang('BAN_UPDATE_SUCCESSFUL') . '<br /><br /><a href="' . $this->u_action . '">&laquo; ' . $this->lang->lang('BACK_TO_PREV') . '</a>');
				}
				else
				{
					confirm_box(false, $this->lang->lang('CONFIRM_OPERATION'), build_hidden_fields([
						'mode'			=> $mode,
						'unbansubmit'	=> true,
						'unban'			=> $ban,
					]));
				}
			}
		}

		// Ban length options
		$ban_end_text = [0 => $this->lang->lang('PERMANENT'), 30 => $this->lang->lang('30_MINS'), 60 => $this->lang->lang('1_HOUR'), 360 => $this->lang->lang('6_HOURS'), 1440 => $this->lang->lang('1_DAY'), 10080 => $this->lang->lang('7_DAYS'), 20160 => $this->lang->lang('2_WEEKS'), 40320 => $this->lang->lang('1_MONTH'), -1 => $this->lang->lang('UNTIL') . ' -&gt; '];

		$ban_end_options = '';
		foreach ($ban_end_text as $length => $text)
		{
			$ban_end_options .= '<option value="' . $length . '">' . $text . '</option>';
		}

		// Define language vars
		$this->page_title = $this->lang->lang(strtoupper($mode) . '_BAN');
		$l_ban_cell = '';

		switch ($mode)
		{
			case 'user':
				$l_ban_cell = 'USERNAME';
			break;

			case 'ip':
				$l_ban_cell = 'IP_HOSTNAME';
			break;

			case 'email':
				$l_ban_cell = 'EMAIL_ADDRESS';
			break;
		}

		$this->acp_ban->display_ban_options($mode);

		$this->template->assign_vars([
			'L_TITLE'				=> $this->page_title,
			'L_EXPLAIN'				=> $this->lang->lang(strtoupper($mode) . '_BAN_EXPLAIN'),
			'L_BAN_CELL'			=> $this->lang->lang($l_ban_cell),
			'L_BAN_EXCLUDE_EXPLAIN'	=> $this->lang->lang(strtoupper($mode) . '_BAN_EXCLUDE_EXPLAIN'),
			'L_NO_BAN_CELL'			=> $this->lang->lang(strtoupper($mode) . '_NO_BANNED'),
			'L_UNBAN_TITLE'			=> $this->lang->lang(strtoupper($mode) . '_UNBAN'),
			'L_UNBAN_EXPLAIN'		=> $this->lang->lang(strtoupper($mode) . '_UNBAN_EXPLAIN'),

			'S_USERNAME_BAN'		=> $mode === 'user',

			'U_ACTION'				=> $this->u_action,
			'U_FIND_USERNAME'		=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=mcp_ban&amp;field=ban'),
		]);

		if ($mode === 'email' && !$this->auth->acl_get('a_user'))
		{
			return;
		}

		// As a "service" we will check if any post id is specified and populate the username of the poster id if given
		$post_id = $this->request->variable('p', 0);
		$user_id = $this->request->variable('u', 0);
		$pre_fill = false;

		if ($user_id && $user_id <> ANONYMOUS)
		{
			$sql = 'SELECT username, user_email, user_ip
				FROM ' . $this->users_table . '
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

			if (!empty($post_info[$post_id]))
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
	}
}
