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

class acp_ban
{
	var $u_action;

	function main($id, $mode)
	{
		global $user, $template, $request, $phpbb_dispatcher;
		global $phpbb_root_path, $phpEx;

		include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

		$bansubmit	= $request->is_set_post('bansubmit');
		$unbansubmit = $request->is_set_post('unbansubmit');

		$user->add_lang(array('acp/ban', 'acp/users'));
		$this->tpl_name = 'acp_ban';
		$form_key = 'acp_ban';
		add_form_key($form_key);

		if (($bansubmit || $unbansubmit) && !check_form_key($form_key))
		{
			trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Ban submitted?
		if ($bansubmit)
		{
			// Grab the list of entries
			$ban				= $request->variable('ban', '', true);
			$ban_length			= $request->variable('banlength', 0);
			$ban_length_other	= $request->variable('banlengthother', '');
			$ban_exclude		= $request->variable('banexclude', 0);
			$ban_reason			= $request->variable('banreason', '', true);
			$ban_give_reason	= $request->variable('bangivereason', '', true);

			if ($ban)
			{
				$abort_ban = false;
				/**
				* Use this event to modify the ban details before the ban is performed
				*
				* @event core.acp_ban_before
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
				extract($phpbb_dispatcher->trigger_event('core.acp_ban_before', compact($vars)));

				if ($abort_ban)
				{
					trigger_error($abort_ban . adm_back_link($this->u_action));
				}
				user_ban($mode, $ban, $ban_length, $ban_length_other, $ban_exclude, $ban_reason, $ban_give_reason);

				/**
				* Use this event to perform actions after the ban has been performed
				*
				* @event core.acp_ban_after
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
				extract($phpbb_dispatcher->trigger_event('core.acp_ban_after', compact($vars)));

				trigger_error($user->lang['BAN_UPDATE_SUCCESSFUL'] . adm_back_link($this->u_action));
			}
		}
		else if ($unbansubmit)
		{
			$ban = $request->variable('unban', array(''));

			if ($ban)
			{
				user_unban($mode, $ban);

				trigger_error($user->lang['BAN_UPDATE_SUCCESSFUL'] . adm_back_link($this->u_action));
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

		self::display_ban_options($mode);

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
			'U_FIND_USERNAME'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=acp_ban&amp;field=ban'),
		));
	}

	/**
	* Display ban options
	*/
	static public function display_ban_options($mode)
	{
		global $user, $db, $template;

		// Ban length options
		$ban_end_text = array(0 => $user->lang['PERMANENT'], 30 => $user->lang['30_MINS'], 60 => $user->lang['1_HOUR'], 360 => $user->lang['6_HOURS'], 1440 => $user->lang['1_DAY'], 10080 => $user->lang['7_DAYS'], 20160 => $user->lang['2_WEEKS'], 40320 => $user->lang['1_MONTH'], -1 => $user->lang['UNTIL'] . ' -&gt; ');

		$ban_end_options = '';
		foreach ($ban_end_text as $length => $text)
		{
			$ban_end_options .= '<option value="' . $length . '">' . $text . '</option>';
		}

		switch ($mode)
		{
			case 'user':

				$field = 'username';
				$l_ban_cell = $user->lang['USERNAME'];

				$sql = 'SELECT b.*, u.user_id, u.username, u.username_clean
					FROM ' . BANLIST_TABLE . ' b, ' . USERS_TABLE . ' u
					WHERE (b.ban_end >= ' . time() . '
							OR b.ban_end = 0)
						AND u.user_id = b.ban_userid
					ORDER BY u.username_clean ASC';
			break;

			case 'ip':

				$field = 'ban_ip';
				$l_ban_cell = $user->lang['IP_HOSTNAME'];

				$sql = 'SELECT *
					FROM ' . BANLIST_TABLE . '
					WHERE (ban_end >= ' . time() . "
							OR ban_end = 0)
						AND ban_ip <> ''
					ORDER BY ban_ip";
			break;

			case 'email':

				$field = 'ban_email';
				$l_ban_cell = $user->lang['EMAIL_ADDRESS'];

				$sql = 'SELECT *
					FROM ' . BANLIST_TABLE . '
					WHERE (ban_end >= ' . time() . "
							OR ban_end = 0)
						AND ban_email <> ''
					ORDER BY ban_email";
			break;
		}
		$result = $db->sql_query($sql);

		$banned_options = $excluded_options = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$option = '<option value="' . $row['ban_id'] . '">' . $row[$field] . '</option>';

			if ($row['ban_exclude'])
			{
				$excluded_options[] = $option;
			}
			else
			{
				$banned_options[] = $option;
			}

			$time_length = ($row['ban_end']) ? ($row['ban_end'] - $row['ban_start']) / 60 : 0;

			if ($time_length == 0)
			{
				// Banned permanently
				$ban_length = $user->lang['PERMANENT'];
			}
			else if (isset($ban_end_text[$time_length]))
			{
				// Banned for a given duration
				$ban_length = $user->lang('BANNED_UNTIL_DURATION', $ban_end_text[$time_length], $user->format_date($row['ban_end'], false, true));
			}
			else
			{
				// Banned until given date
				$ban_length = $user->lang('BANNED_UNTIL_DATE', $user->format_date($row['ban_end'], false, true));
			}

			$template->assign_block_vars('bans', array(
				'BAN_ID'		=> (int) $row['ban_id'],
				'LENGTH'		=> $ban_length,
				'A_LENGTH'		=> addslashes($ban_length),
				'REASON'		=> $row['ban_reason'],
				'A_REASON'		=> addslashes($row['ban_reason']),
				'GIVE_REASON'	=> $row['ban_give_reason'],
				'A_GIVE_REASON'	=> addslashes($row['ban_give_reason']),
			));
		}
		$db->sql_freeresult($result);

		$options = '';
		if ($excluded_options)
		{
			$options .= '<optgroup label="' . $user->lang['OPTIONS_EXCLUDED'] . '">';
			$options .= implode('', $excluded_options);
			$options .= '</optgroup>';
		}

		if ($banned_options)
		{
			$options .= '<optgroup label="' . $user->lang['OPTIONS_BANNED'] . '">';
			$options .= implode('', $banned_options);
			$options .= '</optgroup>';
		}

		$template->assign_vars(array(
			'S_BAN_END_OPTIONS'	=> $ban_end_options,
			'S_BANNED_OPTIONS'	=> ($banned_options || $excluded_options) ? true : false,
			'BANNED_OPTIONS'	=> $options,
		));
	}
}
