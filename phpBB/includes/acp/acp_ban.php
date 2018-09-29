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
		global $user, $template, $request, $phpbb_dispatcher, $phpbb_container;
		global $phpbb_root_path, $phpEx;

		/** @var \phpbb\ban\manager $ban_manager */
		$ban_manager = $phpbb_container->get('ban.manager');

		if (!function_exists('user_ban'))
		{
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		}

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
		global $user, $template, $phpbb_container;

		// Ban length options
		$ban_end_text = array(0 => $user->lang['PERMANENT'], 30 => $user->lang['30_MINS'], 60 => $user->lang['1_HOUR'], 360 => $user->lang['6_HOURS'], 1440 => $user->lang['1_DAY'], 10080 => $user->lang['7_DAYS'], 20160 => $user->lang['2_WEEKS'], 40320 => $user->lang['1_MONTH'], -1 => $user->lang['UNTIL'] . ' -&gt; ');

		$ban_end_options = '';
		foreach ($ban_end_text as $length => $text)
		{
			$ban_end_options .= '<option value="' . $length . '">' . $text . '</option>';
		}

		/** @var \phpbb\ban\manager $ban_manager */
		$ban_manager = $phpbb_container->get('ban.manager');
		$ban_rows = $ban_manager->get_bans($mode);

		$banned_options = array();
		foreach ($ban_rows as $ban_row)
		{
			$banned_options[] = '<option value="' . $ban_row['ban_id'] . '">' . $ban_row['ban_item'] . '</option>';

			$time_length = ($ban_row['ban_end']) ? ($ban_row['ban_end'] - $ban_row['ban_start']) / 60 : 0;

			if ($time_length == 0)
			{
				// Banned permanently
				$ban_length = $user->lang['PERMANENT'];
			}
			else if (isset($ban_end_text[$time_length]))
			{
				// Banned for a given duration
				$ban_length = $user->lang('BANNED_UNTIL_DURATION', $ban_end_text[$time_length], $user->format_date($ban_row['ban_end'], false, true));
			}
			else
			{
				// Banned until given date
				$ban_length = $user->lang('BANNED_UNTIL_DATE', $user->format_date($ban_row['ban_end'], false, true));
			}

			$template->assign_block_vars('bans', array(
				'BAN_ID'		=> (int) $ban_row['ban_id'],
				'LENGTH'		=> $ban_length,
				'A_LENGTH'		=> addslashes($ban_length),
				'REASON'		=> $ban_row['ban_reason'],
				'A_REASON'		=> addslashes($ban_row['ban_reason']),
				'GIVE_REASON'	=> $ban_row['ban_reason_display'],
				'A_GIVE_REASON'	=> addslashes($ban_row['ban_reason_display']),
			));
		}

		$options = '';
		if ($banned_options)
		{
			$options .= '<optgroup label="' . $user->lang['OPTIONS_BANNED'] . '">';
			$options .= implode('', $banned_options);
			$options .= '</optgroup>';
		}

		$template->assign_vars(array(
			'S_BAN_END_OPTIONS'	=> $ban_end_options,
			'S_BANNED_OPTIONS'	=> $banned_options ? true : false,
			'BANNED_OPTIONS'	=> $options,
		));
	}
}
