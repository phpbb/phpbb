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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class mcp_ban
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $request, $phpbb_dispatcher;
		global $phpbb_root_path, $phpEx;

		if (!function_exists('user_ban'))
		{
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		}

		// Include the admin banning interface...
		if (!class_exists('acp_ban'))
		{
			include($phpbb_root_path . 'includes/acp/acp_ban.' . $phpEx);
		}

		$bansubmit		= $request->is_set_post('bansubmit');
		$unbansubmit	= $request->is_set_post('unbansubmit');

		$user->add_lang(array('acp/ban', 'acp/users'));
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
		$vars = array(
			'bansubmit',
			'unbansubmit',
			'mode',
		);
		extract($phpbb_dispatcher->trigger_event('core.mcp_ban_main', compact($vars)));

		// Ban submitted?
		if ($bansubmit)
		{
			// Grab the list of entries
			$ban				= $request->variable('ban', '', $mode === 'user');
			$ban_length			= $request->variable('banlength', 0);
			$ban_length_other	= $request->variable('banlengthother', '');
			$ban_exclude		= $request->variable('banexclude', 0);
			$ban_reason			= $request->variable('banreason', '', true);
			$ban_give_reason	= $request->variable('bangivereason', '', true);

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
					$vars = array(
						'mode',
						'ban',
						'ban_length',
						'ban_length_other',
						'ban_exclude',
						'ban_reason',
						'ban_give_reason',
						'abort_ban',
					);
					extract($phpbb_dispatcher->trigger_event('core.mcp_ban_before', compact($vars)));

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
					$vars = array(
						'mode',
						'ban',
						'ban_length',
						'ban_length_other',
						'ban_exclude',
						'ban_reason',
						'ban_give_reason',
					);
					extract($phpbb_dispatcher->trigger_event('core.mcp_ban_after', compact($vars)));

					trigger_error($user->lang['BAN_UPDATE_SUCCESSFUL'] . '<br /><br /><a href="' . $this->u_action . '">&laquo; ' . $user->lang['BACK_TO_PREV'] . '</a>');
				}
				else
				{
					$hidden_fields = array(
						'mode'				=> $mode,
						'ban'				=> $ban,
						'bansubmit'			=> true,
						'banlength'			=> $ban_length,
						'banlengthother'	=> $ban_length_other,
						'banexclude'		=> $ban_exclude,
						'banreason'			=> $ban_reason,
						'bangivereason'		=> $ban_give_reason,
					);

					/**
					* Use this event to pass data from the ban form to the confirmation screen
					*
					* @event core.mcp_ban_confirm
					* @var	array	hidden_fields	Hidden fields that are passed through the confirm screen
					* @since 3.1.0-RC5
					*/
					$vars = array('hidden_fields');
					extract($phpbb_dispatcher->trigger_event('core.mcp_ban_confirm', compact($vars)));

					confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields($hidden_fields));
				}
			}
		}
		else if ($unbansubmit)
		{
			$ban = $request->variable('unban', array(''));

			if ($ban)
			{
				if (confirm_box(true))
				{
					user_unban($mode, $ban);

					trigger_error($user->lang['BAN_UPDATE_SUCCESSFUL'] . '<br /><br /><a href="' . $this->u_action . '">&laquo; ' . $user->lang['BACK_TO_PREV'] . '</a>');
				}
				else
				{
					confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
						'mode'			=> $mode,
						'unbansubmit'	=> true,
						'unban'			=> $ban)));
				}
			}
		}

		// Define language vars
		$this->page_title = $user->lang[strtoupper($mode) . '_BAN'];

		$l_ban_explain = $user->lang[strtoupper($mode) . '_BAN_EXPLAIN'];
		$l_ban_exclude_explain = $user->lang[strtoupper($mode) . '_BAN_EXCLUDE_EXPLAIN'];
		$l_unban_title = $user->lang[strtoupper($mode) . '_UNBAN'];
		$l_unban_explain = $user->lang[strtoupper($mode) . '_UNBAN_EXPLAIN'];
		$l_no_ban_cell = $user->lang[strtoupper($mode) . '_NO_BANNED'];

		switch ($mode)
		{
			case 'user':
				$l_ban_cell = $user->lang['USERNAME'];
			break;

			case 'ip':
				$l_ban_cell = $user->lang['IP_HOSTNAME'];
			break;

			case 'email':
				$l_ban_cell = $user->lang['EMAIL_ADDRESS'];
			break;
		}

		display_ban_end_options();
		display_ban_options($mode);

		$template->assign_vars(array(
			'L_TITLE'				=> $this->page_title,
			'L_EXPLAIN'				=> $l_ban_explain,
			'L_UNBAN_TITLE'			=> $l_unban_title,
			'L_UNBAN_EXPLAIN'		=> $l_unban_explain,
			'L_BAN_CELL'			=> $l_ban_cell,
			'L_BAN_EXCLUDE_EXPLAIN'	=> $l_ban_exclude_explain,
			'L_NO_BAN_CELL'			=> $l_no_ban_cell,

			'S_USERNAME_BAN'	=> ($mode == 'user') ? true : false,

			'U_ACTION'			=> $this->u_action,
			'U_FIND_USERNAME'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=mcp_ban&amp;field=ban'),
		));

		if ($mode === 'email' && !$auth->acl_get('a_user'))
		{
			return;
		}

		// As a "service" we will check if any post id is specified and populate the username of the poster id if given
		$post_id = $request->variable('p', 0);
		$user_id = $request->variable('u', 0);
		$pre_fill = false;

		if ($user_id && $user_id <> ANONYMOUS)
		{
			$sql = 'SELECT username, user_email, user_ip
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . $user_id;
			$result = $db->sql_query($sql);
			switch ($mode)
			{
				case 'user':
					$pre_fill = (string) $db->sql_fetchfield('username');
				break;

				case 'ip':
					$pre_fill = (string) $db->sql_fetchfield('user_ip');
				break;

				case 'email':
					$pre_fill = (string) $db->sql_fetchfield('user_email');
				break;
			}
			$db->sql_freeresult($result);
		}
		else if ($post_id)
		{
			$post_info = phpbb_get_post_data(array($post_id), 'm_ban');

			if (count($post_info) && !empty($post_info[$post_id]))
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
			$template->assign_var('USERNAMES', $pre_fill);
			$template->assign_var('BAN_QUANTIFIER', $pre_fill);
		}
	}
}
