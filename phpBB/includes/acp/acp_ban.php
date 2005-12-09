<?php
/** 
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package acp
*/
class acp_ban
{
	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $SID, $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix;

		include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

		$bansubmit	= (isset($_POST['bansubmit'])) ? true : false;
		$unbansubmit= (isset($_POST['unbansubmit'])) ? true : false;
		$current_time = time();

		$user->add_lang('acp/ban');
		$this->tpl_name = 'acp_ban';

		$u_action = "{$phpbb_admin_path}index.$phpEx$SID&amp;i=$id&amp;mode=$mode";

		// Ban submitted?
		if ($bansubmit)
		{
			// Grab the list of entries
			$ban				= request_var('ban', '');
			$ban_len			= request_var('banlength', 0);
			$ban_len_other		= request_var('banlengthother', '');
			$ban_exclude		= request_var('banexclude', 0);
			$ban_reason			= request_var('banreason', '');
			$ban_give_reason	= request_var('bangivereason', '');

			user_ban($mode, $ban, $ban_len, $ban_len_other, $ban_exclude, $ban_reason, $ban_give_reason);

			trigger_error($user->lang['BAN_UPDATE_SUCESSFUL'] . adm_back_link($u_action));
		}
		else if ($unbansubmit)
		{
			$ban = request_var('unban', array(''));

			user_unban($mode, $ban);

			trigger_error($user->lang['BAN_UPDATE_SUCESSFUL'] . adm_back_link($u_action));
		}

		// Ban length options
		$ban_end_text = array(0 => $user->lang['PERMANENT'], 30 => $user->lang['30_MINS'], 60 => $user->lang['1_HOUR'], 360 => $user->lang['6_HOURS'], 1440 => $user->lang['1_DAY'], 10080 => $user->lang['7_DAYS'], 20160 => $user->lang['2_WEEKS'], 40320 => $user->lang['1_MONTH'], -1 => $user->lang['UNTIL'] . ' -&gt; ');

		$ban_end_options = '';
		foreach ($ban_end_text as $length => $text)
		{
			$ban_end_options .= '<option value="' . $length . '">' . $text . '</option>';
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

				$field = 'username';
				$l_ban_cell = $user->lang['USERNAME'];

				$sql = 'SELECT b.*, u.user_id, u.username
					FROM ' . BANLIST_TABLE . ' b, ' . USERS_TABLE . ' u
					WHERE (b.ban_end >= ' . time() . '
							OR b.ban_end = 0)
						AND u.user_id = b.ban_userid
						AND b.ban_userid <> 0
						AND u.user_id <> ' . ANONYMOUS . '
					ORDER BY u.user_id ASC';
			break;

			case 'ip':

				$field = 'ban_ip';
				$l_ban_cell = $user->lang['IP_HOSTNAME'];

				$sql = 'SELECT *
					FROM ' . BANLIST_TABLE . '
					WHERE (ban_end >= ' . time() . "
							OR ban_end = 0)
						AND ban_ip <> ''";
			break;

			case 'email':

				$field = 'ban_email';
				$l_ban_cell = $user->lang['EMAIL_ADDRESS'];

				$sql = 'SELECT *
					FROM ' . BANLIST_TABLE . '
					WHERE (ban_end >= ' . time() . "
							OR ban_end = 0)
						AND ban_email <> ''";
			break;
		}
		$result = $db->sql_query($sql);

		$banned_options = '';
		$ban_length = $ban_reasons = $ban_give_reasons = array();

		while ($row = $db->sql_fetchrow($result))
		{
			$banned_options .=  '<option' . (($row['ban_exclude']) ? ' class="sep"' : '') . ' value="' . $row['ban_id'] . '">' . $row[$field] . '</option>';

			$time_length = ($row['ban_end']) ? ($row['ban_end'] - $row['ban_start']) / 60 : 0;
			$ban_length[$row['ban_id']] = (isset($ban_end_text[$time_length])) ? $ban_end_text[$time_length] : $user->lang['UNTIL'] . ' -> ' . $user->format_date($row['ban_end']);

			$ban_reasons[$row['ban_id']] = $row['ban_reason'];
			$ban_give_reasons[$row['ban_id']] = $row['ban_give_reason'];
		}
		$db->sql_freeresult($result);

		if (sizeof($ban_length))
		{
			foreach ($ban_length as $ban_id => $length)
			{
				$template->assign_block_vars('ban_length', array(
					'BAN_ID'	=> $ban_id,
					'LENGTH'	=> $length)
				);
			}
		}

		if (sizeof($ban_reasons))
		{
			foreach ($ban_reasons as $ban_id => $reason)
			{
				$template->assign_block_vars('ban_reason', array(
					'BAN_ID'	=> $ban_id,
					'REASON'	=> addslashes(html_entity_decode($reason)))
				);
			}
		}

		if (sizeof($ban_give_reasons))
		{
			foreach ($ban_give_reasons as $ban_id => $reason)
			{
				$template->assign_block_vars('ban_give_reason', array(
					'BAN_ID'	=> $ban_id,
					'REASON'	=> addslashes(html_entity_decode($reason)))
				);
			}
		}

		$template->assign_vars(array(
			'L_TITLE'				=> $this->page_title,
			'L_EXPLAIN'				=> $l_ban_explain,
			'L_UNBAN_TITLE'			=> $l_unban_title,
			'L_UNBAN_EXPLAIN'		=> $l_unban_explain,
			'L_BAN_CELL'			=> $l_ban_cell,
			'L_BAN_EXCLUDE_EXPLAIN'	=> $l_ban_exclude_explain,
			'L_NO_BAN_CELL'			=> $l_no_ban_cell,

			'S_USERNAME_BAN'	=> ($mode == 'user') ? true : false,
			'S_BAN_END_OPTIONS'	=> $ban_end_options,
			'S_BANNED_OPTIONS'	=> ($banned_options) ? true : false,
			'BANNED_OPTIONS'	=> $banned_options,
			
			'U_ACTION'			=> $u_action,
			'U_FIND_USER'		=> $phpbb_root_path . "memberlist.$phpEx$SID&amp;mode=searchuser&amp;form=acp_ban&amp;field=ban",
			)
		);
	}
}

/**
* @package module_install
*/
class acp_ban_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_ban',
			'title'		=> 'ACP_BAN',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'email'		=> array('title' => 'ACP_BAN_EMAILS', 'auth' => 'acl_a_ban'),
				'ip'		=> array('title' => 'ACP_BAN_IPS', 'auth' => 'acl_a_ban'),
				'user'		=> array('title' => 'ACP_BAN_USERNAMES', 'auth' => 'acl_a_ban'),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>