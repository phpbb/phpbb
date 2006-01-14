<?php
/** 
*
* @package mcp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package mcp
* mcp_notes
* Displays notes about a user
*/
class mcp_notes
{

	var $p_master;
	
	function mcp_main(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($id, $mode)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx, $SID;

		$action = request_var('action', array('' => ''));

		if (is_array($action))
		{
			list($action, ) = each($action);
		}

		switch ($mode)
		{
			case 'front':
				$template->assign_vars(array(
					'U_FIND_MEMBER'		=> "memberlist.$phpEx$SID&amp;mode=searchuser&amp;field=username",
					'U_POST_ACTION'		=> "mcp.$phpEx$SID&amp;i=notes&amp;mode=user_notes",
					)
				);

				$this->tpl_name = 'mcp_notes_front';
				break;
			case 'user_notes':
				mcp_notes_user_view($id, $mode, $action);
				$this->tpl_name = 'mcp_notes_user';
				break;
		}
	}
}

/**
* @package module_install
*/
class mcp_notes_info
{
	function module()
	{
		return array(
			'filename'	=> 'mcp_notes',
			'title'		=> 'MCP_NOTES',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'front'				=> array('title' => 'MCP_NOTES_FRONT', 'auth' => ''),
				'user_notes'		=> array('title' => 'MCP_NOTES_USER', 'auth' => ''),
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

//
// Functions
//

function mcp_notes_user_view($id, $mode, $action)
{
	global $SID, $phpEx, $phpbb_root_path, $config;
	global $template, $db, $user, $auth;

	$user_id = request_var('u', 0);
	$username = request_var('username', '');
	$start = request_var('start', 0);
	$st	= request_var('st', 0);
	$sk	= request_var('sk', 'b');
	$sd	= request_var('sd', 'd');

	$sql_where = ($user_id) ? "user_id = $user_id" : "username = '" . $db->sql_escape($username) . "'";

	$sql = 'SELECT * FROM ' . USERS_TABLE . " WHERE $sql_where";
	$result = $db->sql_query($sql);

	if (!$userrow = $db->sql_fetchrow($result))
	{
		trigger_error($user->lang['NO_USER']);
	}
	$db->sql_freeresult($result);

	$user_id = $userrow['user_id'];

	$deletemark = ($action == 'del_marked') ? true : false;
	$deleteall	= ($action == 'del_all') ? true : false;
	$marked		= request_var('marknote', 0);
	$usernote	= request_var('usernote', '');

	// Handle any actions
	if (($deletemark || $deleteall) && $auth->acl_get('a_clearlogs'))
	{
		$where_sql = " AND reportee_id = $user_id";
		if ($deletemark && $marked)
		{
			$sql_in = array();
			foreach ($marked as $mark)
			{
				$sql_in[] = $mark;
			}
			$where_sql = ' AND log_id IN (' . implode(', ', $sql_in) . ')';
			unset($sql_in);
		}

		$sql = 'DELETE FROM ' . LOG_TABLE . '
			WHERE log_type = ' . LOG_USERS . " 
				$where_sql";
		$db->sql_query($sql);

		add_log('admin', 'LOG_CLEAR_USER', $userrow['username']);

		$msg = ($deletemark) ? 'MARKED_DELETED' : 'ALL_DELETED';
		$redirect = "mcp.$phpEx$SID&amp;i=$id&amp;mode=$mode&amp;u=$user_id";
		meta_refresh(2, $redirect);
		trigger_error($user->lang[$msg] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>'));
	}

	if ($usernote && $action == 'add_feedback')
	{
		add_log('admin', 'LOG_USER_FEEDBACK', $userrow['username']);
		add_log('user', $user_id, 'LOG_USER_GENERAL', $usernote);

		$redirect = "mcp.$phpEx$SID&amp;i=$id&amp;mode=$mode&amp;u=$user_id";
		meta_refresh(2, $redirect);
		trigger_error($user->lang['USER_FEEDBACK_ADDED'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>'));
	}

	// Generate the appropriate user information for the user we are looking at
	$rank_title = $rank_img = '';
//	get_user_rank($userrow['user_rank'], $userrow['user_posts'], $rank_title, $rank_img);

	$avatar_img = '';
	if (!empty($userrow['user_avatar']))
	{
		switch ($userrow['user_avatar_type'])
		{
			case AVATAR_UPLOAD:
				$avatar_img = $config['avatar_path'] . '/';
				break;
			case AVATAR_GALLERY:
				$avatar_img = $config['avatar_gallery_path'] . '/';
				break;
		}
		$avatar_img .= $userrow['user_avatar'];

		$avatar_img = '<img src="' . $avatar_img . '" width="' . $userrow['user_avatar_width'] . '" height="' . $userrow['user_avatar_height'] . '" border="0" alt="" />';
	}
	else
	{
		$avatar_img = '<img src="adm/images/no_avatar.gif" alt="" />';
	}

	$limit_days = array(0 => $user->lang['ALL_ENTRIES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
	$sort_by_text = array('a' => $user->lang['SORT_USERNAME'], 'b' => $user->lang['SORT_DATE'], 'c' => $user->lang['SORT_IP'], 'd' => $user->lang['SORT_ACTION']);
	$sort_by_sql = array('a' => 'l.user_id', 'b' => 'l.log_time', 'c' => 'l.log_ip', 'd' => 'l.log_operation');

	$s_limit_days = $s_sort_key = $s_sort_dir = '';
	gen_sort_selects($limit_days, $sort_by_text, $st, $sk, $sd, $s_limit_days, $s_sort_key, $s_sort_dir);

	// Define where and sort sql for use in displaying logs
	$sql_where = ($st) ? (time() - ($st * 86400)) : 0;
	$sql_sort = $sort_by_sql[$sk] . ' ' . (($sd == 'd') ? 'DESC' : 'ASC');

	$log_data = array();
	$log_count = 0;
	view_log('user', $log_data, $log_count, $config['posts_per_page'], $start, 0, 0, $user_id, $sql_where, $sql_sort);

	if ($log_count)
	{
		$template->assign_var('S_USER_NOTES', true);

		foreach ($log_data as $row)
		{
			$template->assign_block_vars('usernotes', array(
				'REPORT_BY'		=> $row['username'],
				'REPORT_AT'		=> $user->format_date($row['time']),
				'ACTION'		=> $row['action'],
				'ID'			=> $row['id'])
			);
		}
	}

	$template->assign_vars(array(
		'U_POST_ACTION'		=> "mcp.$phpEx$SID&amp;i=$id&amp;mode=$mode&amp;u=$user_id",
		'S_CLEAR_ALLOWED'	=> ($auth->acl_get('a_clearlogs')) ? true : false,
		'S_SELECT_SORT_DIR' 	=> $s_sort_dir,
		'S_SELECT_SORT_KEY' 	=> $s_sort_key,
		'S_SELECT_SORT_DAYS' 	=> $s_limit_days,

		'PAGE_NUMBER'		=> on_page($log_count, $config['posts_per_page'], $start),
		'PAGINATION'		=> generate_pagination("mcp.$phpEx$SID&amp;i=$id&amp;mode=$mode&amp;u=$user_id&amp;st=$st&amp;sk=$sk&amp;sd=$sd", $log_count, $config['posts_per_page'], $start),
		'TOTAL_REPORTS'		=> ($log_count == 1) ? $user->lang['LIST_REPORT'] : sprintf($user->lang['LIST_REPORTS'], $log_count),

		'USERNAME'			=> $userrow['username'],
		'USER_COLOR'		=> (!empty($userrow['user_colour'])) ? $userrow['user_colour'] : '',
		'RANK_TITLE'		=> $rank_title,
		'JOINED'			=> $user->format_date($userrow['user_regdate'], $user->lang['DATE_FORMAT']),
		'POSTS'				=> ($userrow['user_posts']) ? $userrow['user_posts'] : 0,
		'WARNINGS'			=> ($userrow['user_warnings']) ? $userrow['user_warnings'] : 0,

		'AVATAR_IMG'		=> $avatar_img,
		'RANK_IMG'			=> $rank_img,
		)
	);

}

?>