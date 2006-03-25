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
* mcp_warn
* Handling warning the users
*/
class mcp_warn
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
				mcp_warn_front_view($id, $mode);
				$this->tpl_name = 'mcp_warn_front';
				break;
			case 'list':
				mcp_warn_list_view($id, $mode, $action);
				$this->tpl_name = 'mcp_warn_list';
				break;
			case 'warn_post':
				mcp_warn_post_view($id, $mode, $action);
				$this->tpl_name = 'mcp_warn_post';
				break;
			case 'warn_user':
				mcp_warn_user_view($id, $mode, $action);
				$this->tpl_name = 'mcp_warn_user';
				break;
		}
	}
}

//
// Functions
//

/**
* Generates the summary on the main page of the warning module
*/
function mcp_warn_front_view($id, $mode)
{
	global $SID, $phpEx, $phpbb_root_path, $config;
	global $template, $db, $user, $auth;

	$template->assign_vars(array(
		'U_FIND_MEMBER'		=> "memberlist.$phpEx$SID&amp;mode=searchuser&amp;form=mcp&amp;field=username",
		'U_POST_ACTION'		=> "mcp.$phpEx$SID&amp;i=warn&amp;mode=warn_user",
		)
	);

	// Obtain a list of the 5 naughtiest users....
	// These are the 5 users with the highest warning count

	$highest = array();
	$count = 0;

	view_warned_users($highest, $count, 5);

	foreach ($highest as $row)
	{
		$template->assign_block_vars('highest', array(
			'U_NOTES'		=> 'mcp.' . $phpEx . $SID . '&amp;i=notes&amp;mode=user_notes&amp;u=' . $row['user_id'],
			'U_USER'		=> 'memberlist.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $row['user_id'],

			'USERNAME'		=> $row['username'],
			'WARNING_TIME'	=> $user->format_date($row['user_last_warning']),
			'WARNINGS'		=> $row['user_warnings'],
			)
		);
	}

	// And now the 5 most recent users to get in trouble

	$sql = 'SELECT u.user_id, u.username, u.user_warnings, w.warning_time
		FROM ' . USERS_TABLE . ' u, ' . WARNINGS_TABLE . ' w
		WHERE u.user_id = w.user_id
		ORDER BY w.warning_time DESC';
	$result = $db->sql_query_limit($sql, 5);

	while ($row = $db->sql_fetchrow($result))
	{
		$template->assign_block_vars('latest', array(
			'U_NOTES'		=> 'mcp.' . $phpEx . $SID . '&amp;i=notes&amp;mode=user_notes&amp;u=' . $row['user_id'],
			'U_USER'		=> 'memberlist.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $row['user_id'],

			'USERNAME'		=> $row['username'],
			'WARNING_TIME'	=> $user->format_date($row['warning_time']),
			'WARNINGS'		=> $row['user_warnings'],
			)
		);
	}
	$db->sql_freeresult($result);
}

/**
* Lists all users with warnings
*/
function mcp_warn_list_view($id, $mode, $action)
{
	global $SID, $phpEx, $phpbb_root_path, $config;
	global $template, $db, $user, $auth;

	$user->add_lang('memberlist');

	$start = request_var('start', 0);
	$st	= request_var('st', 0);
	$sk	= request_var('sk', 'b');
	$sd	= request_var('sd', 'd');

	$limit_days = array(0 => $user->lang['ALL_ENTRIES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
	$sort_by_text = array('a' => $user->lang['SORT_USERNAME'], 'b' => $user->lang['SORT_DATE'], 'c' => $user->lang['SORT_WARNINGS']);
	$sort_by_sql = array('a' => 'username', 'b' => 'user_last_warning', 'c' => 'user_warnings');

	$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
	gen_sort_selects($limit_days, $sort_by_text, $st, $sk, $sd, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

	// Define where and sort sql for use in displaying logs
	$sql_where = ($st) ? (time() - ($st * 86400)) : 0;
	$sql_sort = $sort_by_sql[$sk] . ' ' . (($sd == 'd') ? 'DESC' : 'ASC');

	$users = array();
	$user_count = 0;

	view_warned_users($users, $user_count, $config['topics_per_page'], $start, $sql_where, $sql_sort);

	foreach ($users as $row)
	{
		$template->assign_block_vars('user', array(
			'U_NOTES'		=> 'mcp.' . $phpEx . $SID . '&amp;i=notes&amp;mode=user_notes&amp;u=' . $row['user_id'],
			'U_USER'		=> 'memberlist.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $row['user_id'],

			'USERNAME'		=> $row['username'],
			'WARNING_TIME'	=> $user->format_date($row['user_last_warning']),
			'WARNINGS'		=> $row['user_warnings'],
			)
		);
	}

	$template->assign_vars(array(
		'U_POST_ACTION'		=> "mcp.$phpEx$SID&amp;i=$id&amp;mode=$mode",
		'S_CLEAR_ALLOWED'	=> ($auth->acl_get('a_clearlogs')) ? true : false,
		'S_SELECT_SORT_DIR' 	=> $s_sort_dir,
		'S_SELECT_SORT_KEY' 	=> $s_sort_key,
		'S_SELECT_SORT_DAYS' 	=> $s_limit_days,

		'PAGE_NUMBER'		=> on_page($user_count, $config['topics_per_page'], $start),
		'PAGINATION'		=> generate_pagination("mcp.$phpEx$SID&amp;i=$id&amp;mode=$mode&amp;st=$st&amp;sk=$sk&amp;sd=$sd", $user_count, $config['topics_per_page'], $start),
		'TOTAL_USERS'		=> ($user_count == 1) ? $user->lang['LIST_USER'] : sprintf($user->lang['LIST_USERS'], $user_count),
		)
	);

}

/**
* Handles warning the user when the warning is for a specific post
*/
function mcp_warn_post_view($id, $mode, $action)
{
	global $SID, $phpEx, $phpbb_root_path, $config;
	global $template, $db, $user, $auth;

	$post_id = request_var('p', 0);
	$notify = (isset($_REQUEST['notify_user'])) ? true : false;
	$warning = request_var('warning', '');

	$sql = 'SELECT u.*, p.* FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u 
		WHERE post_id = $post_id
		AND u.user_id = p.poster_id";
	$result = $db->sql_query($sql);

	if (!$userrow = $db->sql_fetchrow($result))
	{
		trigger_error($user->lang['NO_POST']);
	}
	$db->sql_freeresult($result);

	// There is no point issuing a warning to ignored users (ie anonymous and bots)
	if ($userrow['user_type'] == USER_IGNORE)
	{
		trigger_error($user->lang['CANNOT_WARN_ANONYMOUS']);
	}

	// Check if there is already a warning for this post to prevent multiple
	// warnings for the same offence
	$sql = 'SELECT * FROM ' . WARNINGS_TABLE . "
		WHERE post_id = $post_id";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		trigger_error($user->lang['ALREADY_WARNED']);
	}
	$db->sql_freeresult($result);
	
	$user_id = $userrow['user_id'];

	if ($warning && $action == 'add_warning')
	{
		add_warning($userrow, $warning, $notify, $post_id);

		$redirect = "mcp.$phpEx$SID&amp;i=notes&amp;mode=user_notes&amp;u=$user_id";
		meta_refresh(2, $redirect);
		trigger_error($user->lang['USER_WARNING_ADDED'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>'));
	}

	// OK, they didn't submit a warning so lets build the page for them to do so
	
	// We want to make the message available here as a reminder
	// Parse the message and subject
	$message = $userrow['post_text'];

	// Second parse bbcode here
	if ($userrow['bbcode_bitfield'])
	{
		include_once($phpbb_root_path . 'includes/bbcode.'.$phpEx);
		$bbcode = new bbcode($post_info['bbcode_bitfield']);
		$bbcode->bbcode_second_pass($message, $row['bbcode_uid'], $row['bbcode_bitfield']);
	}

	// Always process smilies after parsing bbcodes
	$message = smiley_text($message);

	// Replace naughty words such as farty pants
	$message = str_replace("\n", '<br />', censor_text($message));

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

	$template->assign_vars(array(
		'U_POST_ACTION'		=> "mcp.$phpEx$SID&amp;i=$id&amp;mode=$mode&amp;p=$post_id",

		'POST'				=> $message,
		'USERNAME'			=> $userrow['username'],
		'USER_COLOR'		=> (!empty($userrow['user_colour'])) ? $userrow['user_colour'] : '',
		'RANK_TITLE'		=> $rank_title,
		'JOINED'			=> $user->format_date($userrow['user_regdate']),
		'POSTS'				=> ($userrow['user_posts']) ? $userrow['user_posts'] : 0,

		'AVATAR_IMG'		=> $avatar_img,
		'RANK_IMG'			=> $rank_img,
		)
	);
}

/**
* Handles warning the user
*/
function mcp_warn_user_view($id, $mode, $action)
{
	global $SID, $phpEx, $phpbb_root_path, $config;
	global $template, $db, $user, $auth;

	$user_id = request_var('u', 0);
	$username = request_var('username', '');
	$notify = (isset($_REQUEST['notify_user'])) ? true : false;
	$warning = request_var('warning', '');

	$sql_where = ($user_id) ? "user_id = $user_id" : "username = '" . $db->sql_escape($username) . "'";

	$userrow = get_userdata($user_id);

	$user_id = $userrow['user_id'];

	if ($warning && $action == 'add_warning')
	{
		add_warning($userrow, $warning, $notify);

		$redirect = "mcp.$phpEx$SID&amp;i=notes&amp;mode=user_notes&amp;u=$user_id";
		meta_refresh(2, $redirect);
		trigger_error($user->lang['USER_WARNING_ADDED'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>'));
	}

	// OK, they didn't submit a warning so lets build the page for them to do so
	$template->assign_vars(array(
		'U_POST_ACTION'		=> "mcp.$phpEx$SID&amp;i=$id&amp;mode=$mode&amp;u=$user_id",

		'USERNAME'			=> $userrow['username'],
		'USER_COLOR'		=> (!empty($userrow['user_colour'])) ? $userrow['user_colour'] : '',
		'RANK_TITLE'		=> $userrow['rank_title'],
		'JOINED'			=> $user->format_date($userrow['user_regdate']),
		'POSTS'				=> ($userrow['user_posts']) ? $userrow['user_posts'] : 0,
		'WARNINGS'			=> ($userrow['user_warnings']) ? $userrow['user_warnings'] : 0,

		'AVATAR_IMG'		=> $userrow['avatar_img'],
		'RANK_IMG'			=> $userrow['rank_img'],
		)
	);
}

/**
* Insert the warning into the database
*/
function add_warning($userrow, $warning, $send_pm = true, $post_id = 0)
{
	global $SID, $phpEx, $phpbb_root_path, $config;
	global $template, $db, $user, $auth;

	if ($send_pm)
	{
		include($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
		include($phpbb_root_path . 'includes/message_parser.'.$phpEx);

		$userrow['user_lang'] = (file_exists($phpbb_root_path . 'language/' . $userrow['user_lang'] . "/mcp.$phpEx")) ? $userrow['user_lang'] : $config['default_lang'];
		include($phpbb_root_path . 'language/' . $userrow['user_lang'] . "/mcp.$phpEx");

		$message_parser = new parse_message();
		$message_parser->message = sprintf($lang['WARNING_PM_BODY'], $warning);
		$message_parser->parse(true, true, true, false, false, true);

		$pm_data = array(
			'from_user_id'			=> $user->data['user_id'],
			'from_user_ip'			=> $user->data['user_ip'],
			'from_username'			=> $user->data['username'],
			'enable_sig'			=> false,
			'enable_bbcode'			=> true,
			'enable_smilies'		=> true,
			'enable_urls'			=> false,
			'icon_id'				=> 0,
			'bbcode_bitfield'		=> (int) $message_parser->bbcode_bitfield,
			'bbcode_uid'			=> $message_parser->bbcode_uid,
			'message'				=> $message_parser->message,
			'address_list'			=> array('u' => array($userrow['user_id'] => 'to')),
		);

		submit_pm('post', $lang['WARNING_PM_SUBJECT'], $pm_data, false, false);
	}

	add_log('admin', 'LOG_USER_WARNING', $userrow['username']);
	$log_id = add_log('user', $userrow['user_id'], 'LOG_USER_WARNING_BODY', $warning);

	$sql_ary = array(
		'user_id'		=> $userrow['user_id'],
		'post_id'		=> $post_id,
		'log_id'		=> $log_id,
		'warning_time'	=> time(),
	);

	$db->sql_query('INSERT INTO ' . WARNINGS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

	$sql = 'UPDATE ' . USERS_TABLE . ' 
		SET user_warnings = user_warnings + 1,
			user_last_warning = ' . time() . '
		WHERE user_id = ' . $userrow['user_id'];
	$db->sql_query($sql);
}
?>