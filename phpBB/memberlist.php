<?php
/***************************************************************************
 *                              memberlist.php
 *                            -------------------
 *   begin                : Friday, May 11, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

define('IN_PHPBB', true);
$phpbb_root_path = './';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

// Start session management
$userdata = $session->start();
$auth->acl($userdata);
$user = new user($userdata);
// End session management

$start = ( isset($_GET['start']) ) ? intval($_GET['start']) : 0;
$form = ( !empty($_GET['form']) ) ? $_GET['form'] : 0;
$field = ( isset($_GET['field']) ) ? $_GET['field'] : 'username';

$sort_by = ( !empty($_POST['sort_by']) ) ? intval($_POST['sort_by']) : ( ( !empty($_GET['sort_by']) ) ? $_GET['sort_by'] : '4' );
$sort_order = ( !empty($_POST['sort_order']) ) ? $_POST['sort_order'] : ( ( !empty($_GET['sort_order']) ) ? $_GET['sort_order'] : 'd' );

$username = ( !empty($_POST['username']) ) ? $_POST['username'] : ( ( !empty($_GET['username']) ) ? $_GET['username'] : '' );
$email = ( !empty($_POST['email']) ) ? $_POST['email'] : ( ( !empty($_GET['email']) ) ? $_GET['email'] : '' );
$icq = ( !empty($_POST['icq']) ) ? intval($_POST['icq']) : ( ( !empty($_GET['icq']) ) ? $_GET['icq'] : '' );
$aim = ( !empty($_POST['aim']) ) ? $_POST['aim'] : ( ( !empty($_GET['aim']) ) ? $_GET['aim'] : '' );
$yahoo = ( !empty($_POST['yahoo']) ) ? $_POST['yahoo'] : ( ( !empty($_GET['yahoo']) ) ? $_GET['yahoo'] : '' );
$msn = ( !empty($_POST['msn']) ) ? $_POST['msn'] : ( ( !empty($_GET['msn']) ) ? $_GET['msn'] : '' );

$joined_select = ( !empty($_POST['joined_select']) ) ? $_POST['joined_select'] : ( ( !empty($_GET['joined_select']) ) ? $_GET['joined_select'] : 'lt' );
$active_select = ( !empty($_POST['active_select']) ) ? $_POST['active_select'] : ( ( !empty($_GET['active_select']) ) ? $_GET['active_select'] : 'lt' );
$count_select = ( !empty($_POST['count_select']) ) ? $_POST['count_select'] : ( ( !empty($_GET['count_select']) ) ? $_GET['count_select'] : 'eq' );
$joined = ( !empty($_POST['joined']) ) ? explode('-', $_POST['joined']) : ( ( !empty($_GET['joined']) ) ? explode('-', $_GET['joined']) : array() );
$active = ( !empty($_POST['active']) ) ? explode('-', $_POST['active']) : ( ( !empty($_GET['active']) ) ? explode('-', $_GET['active']) : array() );
$count = ( !empty($_POST['count']) ) ? intval($_POST['count']) : ( ( !empty($_GET['count']) ) ? $_GET['count'] : '' );





if ( isset($_POST['order']) )
{
	$sort_order = ($_POST['order'] == 'a') ? 'ASC' : 'DESC';
}
else if ( isset($_GET['order']) )
{
	$sort_order = ($_GET['order'] == 'a') ? 'ASC' : 'DESC';
}
else
{
	$sort_order = 'ASC';
}

//
// Memberlist sorting
//
$mode_types_text = array($lang['Sort_Joined'], $lang['Sort_Username'], $lang['Sort_Location'], $lang['Sort_Posts'], $lang['Sort_Email'],  $lang['Sort_Website'], $lang['Sort_Top_Ten']);
$mode_types = array('joindate', 'username', 'location', 'posts', 'email', 'website', 'topten');

$select_sort_mode = '<select name="mode">';
for($i = 0; $i < count($mode_types_text); $i++)
{
	$selected = ( $mode == $mode_types[$i] ) ? ' selected="selected"' : '';
	$select_sort_mode .= '<option value="' . $mode_types[$i] . '"' . $selected . '>' . $mode_types_text[$i] . '</option>';
}
$select_sort_mode .= '</select>';

$select_sort_order = '<select name="order">';
$select_sort_order .= ( $sort_order == 'a' ) ? '<option value="a" selected="selected">' . $lang['Sort_Ascending'] . '</option><option value="d">' . $lang['Sort_Descending'] . '</option>' : '<option value="a">' . $lang['Sort_Ascending'] . '</option><option value="d" selected="selected">' . $lang['Sort_Descending'] . '</option>';
$select_sort_order .= '</select>';

if ( $mode != 'topten' || $board_config['topics_per_page'] < 10 )
{
	$pagination = generate_pagination("memberlist.$phpEx$SID&amp;mode=$mode&amp;order=$sort_order", $board_config['num_users'], $board_config['topics_per_page'], $start). '&nbsp;';
	$total_members = $board_config['num_users'];
}
else
{
	$pagination = '&nbsp;';
	$total_members = 10;
}

//
// Generate page
//
$template->assign_vars(array(
	'PAGINATION' => $pagination,
	'PAGE_NUMBER' => sprintf($lang['Page_of'], ( floor( $start / $board_config['topics_per_page'] ) + 1 ), ceil( $total_members / $board_config['topics_per_page'] )),

	'L_SELECT_SORT_METHOD' => $lang['Select_sort_method'],
	'L_EMAIL' => $lang['Email'],
	'L_WEBSITE' => $lang['Website'],
	'L_FROM' => $lang['Location'],
	'L_ORDER' => $lang['Order'],
	'L_SORT' => $lang['Sort'],
	'L_SUBMIT' => $lang['Sort'],
	'L_AIM' => $lang['AIM'],
	'L_YIM' => $lang['YIM'],
	'L_MSNM' => $lang['MSNM'],
	'L_ICQ' => $lang['ICQ'],
	'L_JOINED' => $lang['Joined'],
	'L_POSTS' => $lang['Posts'],
	'L_GOTO_PAGE' => $lang['Goto_page'],

	'S_MODE_SELECT' => $select_sort_mode,
	'S_ORDER_SELECT' => $select_sort_order,
	'S_MODE_ACTION' => "memberlist.$phpEx$SID")
);

if ( isset($_GET['mode']) || isset($_POST['mode']) )
{
	$mode = ( isset($_POST['mode']) ) ? $_POST['mode'] : $_GET['mode'];

	switch( $mode )
	{
		case 'topten':
			$order_by = "user_posts DESC LIMIT 10";
			break;
		case 'joined':
			$order_by = "user_regdate ASC LIMIT $start, " . $board_config['topics_per_page'];
			break;
		case 'username':
			$order_by = "username $sort_order LIMIT $start, " . $board_config['topics_per_page'];
			break;
		case 'location':
			$order_by = "user_from $sort_order LIMIT $start, " . $board_config['topics_per_page'];
			break;
		case 'posts':
			$order_by = "user_posts $sort_order LIMIT $start, " . $board_config['topics_per_page'];
			break;
		case 'email':
			$order_by = "user_email $sort_order LIMIT $start, " . $board_config['topics_per_page'];
			break;
		case 'website':
			$order_by = "user_website $sort_order LIMIT $start, " . $board_config['topics_per_page'];
			break;
		default:
			$order_by = "user_regdate $sort_order LIMIT $start, " . $board_config['topics_per_page'];
			break;
	}
}
else
{
	$order_by = "user_regdate $sort_order LIMIT $start, " . $board_config['topics_per_page'];
}

$sql = "SELECT username, user_id, user_viewemail, user_posts, user_regdate, user_from, user_website, user_email, user_icq, user_aim, user_yim, user_msnm, user_avatar, user_avatar_type, user_allowavatar
	FROM " . USERS_TABLE . "
	WHERE user_id <> " . ANONYMOUS . "
	ORDER BY $order_by";
$result = $db->sql_query($sql);

if ( $row = $db->sql_fetchrow($result) )
{
	$i = 0;
	do
	{
		$username = $row['username'];
		$user_id = $row['user_id'];

		$from = ( !empty($row['user_from']) ) ? $row['user_from'] : '&nbsp;';
		$joined = $user->format_date($row['user_regdate'], $lang['DATE_FORMAT']);
		$posts = ( $row['user_posts'] ) ? $row['user_posts'] : 0;

		$poster_avatar = '';
		if ( $row['user_avatar_type'] && $user_id && $row['user_allowavatar'] )
		{
			switch( $row['user_avatar_type'] )
			{
				case USER_AVATAR_UPLOAD:
					$poster_avatar = ( $board_config['allow_avatar_upload'] ) ? '<img src="' . $board_config['avatar_path'] . '/' . $row['user_avatar'] . '" alt="" border="0" />' : '';
					break;
				case USER_AVATAR_REMOTE:
					$poster_avatar = ( $board_config['allow_avatar_remote'] ) ? '<img src="' . $row['user_avatar'] . '" alt="" border="0" />' : '';
					break;
				case USER_AVATAR_GALLERY:
					$poster_avatar = ( $board_config['allow_avatar_local'] ) ? '<img src="' . $board_config['avatar_gallery_path'] . '/' . $row['user_avatar'] . '" alt="" border="0" />' : '';
					break;
			}
		}

		if ( $row['user_viewemail'] || $auth->acl_get('a_') )
		{
			$email_uri = ( $board_config['board_email_form'] ) ? "profile.$phpEx$SID&amp;mode=email&amp;u=" . $user_id : 'mailto:' . $row['user_email'];

			$email_img = '<a href="' . $email_uri . '">' . create_img($theme['icon_email'], $lang['Send_email']) . '</a>';
			$email = '<a href="' . $email_uri . '">' . $lang['Send_email'] . '</a>';
		}
		else
		{
			$email_img = '&nbsp;';
			$email = '&nbsp;';
		}

		$temp_url = "profile.$phpEx$SID&amp;mode=viewprofile&amp;u=$user_id";
		$profile_img = '<a href="' . $temp_url . '">' . create_img($theme['icon_profile'], $lang['Read_profile']) . '</a>';
		$profile = '<a href="' . $temp_url . '">' . $lang['Read_profile'] . '</a>';

		$temp_url = "privmsg.$phpEx$SID&amp;mode=post&amp;u=$user_id";
		$pm_img = '<a href="' . $temp_url . '">' . create_img($theme['icon_pm'], $lang['Send_private_message']) . '</a>';
		$pm = '<a href="' . $temp_url . '">' . $lang['Send_private_message'] . '</a>';

		$www_img = ( $row['user_website'] ) ? '<a href="' . $row['user_website'] . '" target="_userwww">' . create_img($theme['icon_www'], $lang['Visit_website']) . '</a>' : '';
		$www = ( $row['user_website'] ) ? '<a href="' . $row['user_website'] . '" target="_userwww">' . $lang['Visit_website'] . '</a>' : '';

		if ( !empty($row['user_icq']) )
		{
			$icq_status_img = '<a href="http://wwp.icq.com/' . $row['user_icq'] . '#pager"><img src="http://web.icq.com/whitepages/online?icq=' . $row['user_icq'] . '&img=5" width="18" height="18" border="0" /></a>';
			$icq_img = '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $row['user_icq'] . '">' . create_img($theme['icon_icq'], $lang['ICQ']) . '</a>';
			$icq =  '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $row['user_icq'] . '">' . $lang['ICQ'] . '</a>';
		}
		else
		{
			$icq_status_img = '';
			$icq_img = '';
			$icq = '';
		}

		$aim_img = ( $row['user_aim'] ) ? '<a href="aim:goim?screenname=' . $row['user_aim'] . '&amp;message=Hello+Are+you+there?">' . create_img($theme['icon_aim'], $lang['AIM']) . '</a>' : '';
		$aim = ( $row['user_aim'] ) ? '<a href="aim:goim?screenname=' . $row['user_aim'] . '&amp;message=Hello+Are+you+there?">' . $lang['AIM'] . '</a>' : '';

		$temp_url = "profile.$phpEx$SID&amp;mode=viewprofile&amp;u=$user_id";
		$msn_img = ( $row['user_msnm'] ) ? '<a href="' . $temp_url . '">' . create_img($theme['icon_msnm'], $lang['MSNM']) . '</a>' : '';
		$msn = ( $row['user_msnm'] ) ? '<a href="' . $temp_url . '">' . $lang['MSNM'] . '</a>' : '';

		$yim_img = ( $row['user_yim'] ) ? '<a href="http://edit.yahoo.com/config/send_webmesg?.target=' . $row['user_yim'] . '&amp;.src=pg">' . create_img($theme['icon_yim'], $lang['YIM']) . '</a>' : '';
		$yim = ( $row['user_yim'] ) ? '<a href="http://edit.yahoo.com/config/send_webmesg?.target=' . $row['user_yim'] . '&amp;.src=pg">' . $lang['YIM'] . '</a>' : '';

		$temp_url = "search.$phpEx$SID&amp;search_author=" . urlencode($username) . "&amp;showresults=posts";
		$search_img = '<a href="' . $temp_url . '">' . create_img($theme['icon_search'], $lang['Search_user_posts']) . '</a>';
		$search = '<a href="' . $temp_url . '">' . $lang['Search_user_posts'] . '</a>';

		$template->assign_block_vars('memberrow', array(
			'ROW_NUMBER' => $i + ( $start + 1 ),
			'USERNAME' => $username,
			'FROM' => $from,
			'JOINED' => $joined,
			'POSTS' => $posts,
			'AVATAR_IMG' => $poster_avatar,
			'PROFILE_IMG' => $profile_img,
			'PROFILE' => $profile,
			'SEARCH_IMG' => $search_img,
			'SEARCH' => $search,
			'PM_IMG' => $pm_img,
			'PM' => $pm,
			'EMAIL_IMG' => $email_img,
			'EMAIL' => $email,
			'WWW_IMG' => $www_img,
			'WWW' => $www,
			'ICQ_STATUS_IMG' => $icq_status_img,
			'ICQ_IMG' => $icq_img,
			'ICQ' => $icq,
			'AIM_IMG' => $aim_img,
			'AIM' => $aim,
			'MSN_IMG' => $msn_img,
			'MSN' => $msn,
			'YIM_IMG' => $yim_img,
			'YIM' => $yim,

			'S_ROW_COUNT' => $i,

			'U_VIEWPROFILE' => "profile.$phpEx$SID&amp;mode=viewprofile&amp;u=$user_id")
		);

		$i++;
	}
	while ( $row = $db->sql_fetchrow($result) );
}

$page_title = $lang['Memberlist'];
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	'body' => ( $mode == 'searchuser') ? 'search_username.html' : 'memberlist_body.html')
);
make_jumpbox('viewforum.'.$phpEx);

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

// Username search
function username_search()
{
	global $SID, $phpEx, $phpbb_root_path;
	global $db, $board_config, $template, $auth, $lang, $theme, $user;
	global $starttime;

	$form = ( !empty($_GET['form']) ) ? $_GET['form'] : 0;
	$field = ( isset($_GET['field']) ) ? $_GET['field'] : 'username';
	$start = ( isset($_GET['start']) ) ? intval($_GET['start']) : 0;

	$sort_by = ( !empty($_POST['sort_by']) ) ? intval($_POST['sort_by']) : ( ( !empty($_GET['sort_by']) ) ? $_GET['sort_by'] : '4' );
	$sort_order = ( !empty($_POST['sort_order']) ) ? $_POST['sort_order'] : ( ( !empty($_GET['sort_order']) ) ? $_GET['sort_order'] : 'd' );

	$username = ( !empty($_POST['username']) ) ? $_POST['username'] : ( ( !empty($_GET['username']) ) ? $_GET['username'] : '' );
	$email = ( !empty($_POST['email']) ) ? $_POST['email'] : ( ( !empty($_GET['email']) ) ? $_GET['email'] : '' );
	$icq = ( !empty($_POST['icq']) ) ? intval($_POST['icq']) : ( ( !empty($_GET['icq']) ) ? $_GET['icq'] : '' );
	$aim = ( !empty($_POST['aim']) ) ? $_POST['aim'] : ( ( !empty($_GET['aim']) ) ? $_GET['aim'] : '' );
	$yahoo = ( !empty($_POST['yahoo']) ) ? $_POST['yahoo'] : ( ( !empty($_GET['yahoo']) ) ? $_GET['yahoo'] : '' );
	$msn = ( !empty($_POST['msn']) ) ? $_POST['msn'] : ( ( !empty($_GET['msn']) ) ? $_GET['msn'] : '' );

	$joined_select = ( !empty($_POST['joined_select']) ) ? $_POST['joined_select'] : ( ( !empty($_GET['joined_select']) ) ? $_GET['joined_select'] : 'lt' );
	$active_select = ( !empty($_POST['active_select']) ) ? $_POST['active_select'] : ( ( !empty($_GET['active_select']) ) ? $_GET['active_select'] : 'lt' );
	$count_select = ( !empty($_POST['count_select']) ) ? $_POST['count_select'] : ( ( !empty($_GET['count_select']) ) ? $_GET['count_select'] : 'eq' );
	$joined = ( !empty($_POST['joined']) ) ? explode('-', $_POST['joined']) : ( ( !empty($_GET['joined']) ) ? explode('-', $_GET['joined']) : array() );
	$active = ( !empty($_POST['active']) ) ? explode('-', $_POST['active']) : ( ( !empty($_GET['active']) ) ? explode('-', $_GET['active']) : array() );
	$count = ( !empty($_POST['count']) ) ? intval($_POST['count']) : ( ( !empty($_GET['count']) ) ? $_GET['count'] : '' );

	//
	//
	//
	$sort_by_types_text = array($lang['Sort_Username'], $lang['Sort_Email'], $lang['Sort_Post_count'], $lang['Sort_Joined'], $lang['Sort_Last_active']);
	$s_sort_by = '';
	for($i = 0; $i < count($sort_by_types_text); $i++)
	{
		$selected = ( $sort_by == $i ) ? ' selected="selected"' : '';
		$s_sort_by .= '<option value="' . $i . '"' . $selected . '>' . $sort_by_types_text[$i] . '</option>';
	}

	$sort_order_text = array('a' => $lang['Ascending'], 'd' => $lang['Descending']);
	$s_sort_order = '';
	foreach ( $sort_order_text as $key => $value )
	{
		$selected = ( $sort_order == $key ) ? ' selected="selected"' : '';
		$s_sort_order .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
	}

	$find_count = array('lt' => $lang['Less_than'], 'eq' => $lang['Equal_to'], 'gt' => $lang['More_than']);
	$s_find_count = '';
	foreach ( $find_count as $key => $value )
	{
		$selected = ( $count_select == $key ) ? ' selected="selected"' : '';
		$s_find_count .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
	}

	$find_time = array('lt' => $lang['Before'], 'gt' => $lang['After']);
	$s_find_join_time = '';
	foreach ( $find_time as $key => $value )
	{
		$selected = ( $joined_select == $key ) ? ' selected="selected"' : '';
		$s_find_join_time .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
	}
	$s_find_active_time = '';
	foreach ( $find_time as $key => $value )
	{
		$selected = ( $active_select == $key ) ? ' selected="selected"' : '';
		$s_find_active_time .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
	}

	//
	//
	//
	$key_match = array('lt' => '<', 'gt' => '>', 'eq' => '=');
	$sort_by_types = array('username', 'user_email', 'user_posts', 'user_regdate', 'user_lastvisit');

	$where_sql = '';
	$where_sql .= ( $username ) ? " AND username LIKE '" . str_replace('*', '%', $username) ."'" : '';
	$where_sql .= ( $email ) ? " AND user_email LIKE '" . str_replace('*', '%', $email) ."' " : '';
	$where_sql .= ( $icq ) ? " AND user_icq LIKE '" . str_replace('*', '%', $icq) ."' " : '';
	$where_sql .= ( $aim ) ? " AND user_aim LIKE '" . str_replace('*', '%', $aim) ."' " : '';
	$where_sql .= ( $yahoo ) ? " AND user_yim LIKE '" . str_replace('*', '%', $yahoo) ."' " : '';
	$where_sql .= ( $msn ) ? " AND user_msnm LIKE '" . str_replace('*', '%', $msn) ."' " : '';
	$where_sql .= ( $joined ) ? " AND user_regdate " . $key_match[$joined_select] . " " . gmmktime(0, 0, 0, intval($joined[1]), intval($joined[2]), intval($joined[0])) : '';
	$where_sql .= ( $count ) ? " AND user_posts " . $key_match[$count_select] . " $count " : '';
	$where_sql .= ( $active ) ? " AND user_lastvisit " . $key_match[$active_select] . " " . gmmktime(0, 0, 0, $active[1], intval($active[2]), intval($active[0])) : '';

	$order_by = $sort_by_types[$sort_by] . '  ' . ( ( $sort_order == 'a' ) ? 'ASC' : 'DESC' );

	$sql = "SELECT COUNT(user_id) AS total_users
		FROM " . USERS_TABLE . "
		WHERE user_id <> " . ANONYMOUS . "
		$where_sql";
	$result = $db->sql_query($sql);

	$total_users = ( $row = $db->sql_fetchrow($result) ) ? $row['total_users'] : 0;

	$pagination = generate_pagination("search.$phpEx$SID&amp;mode=searchuser&amp;form=$form&amp;field=$field&amp;username=" . urlencode($username) . "&amp;email=" . urlencode($email) . "&amp;icq=$icq&amp;aim=" . urlencode($aim) . "&amp;yahoo=" . urlencode($yahoo) . "&amp;msn=" . urlencode($msn) . "&amp;joined=" . urlencode(implode('-', $joined)) . "&amp;active=" . urlencode(implode('-', $active)) . "&amp;count=$count&amp;sort_order=$sort_order&amp;sort_by=$sort_by&amp;joined_select=$joined_select&amp;active_select=$active_select&amp;count_select=$count_select", $total_users, $board_config['topics_per_page'], $start);

	//
	//
	//
	$page_title = $lang['Search'];
	include($phpbb_root_path . 'includes/page_header.'.$phpEx);

	$template->set_filenames(array(
		'search_user_body' => 'search_username.html')
	);

	$template->assign_vars(array(
		'USERNAME' => $username,
		'EMAIL' => $email,
		'ICQ' => $icq,
		'AIM' => $aim,
		'YAHOO' => $yahoo,
		'MSNM' => $msn,
		'JOINED' => implode('-', $joined),
		'ACTIVE' => implode('-', $active),
		'COUNT' => $count,

		'PAGINATION' => $pagination,
		'PAGE_NUMBER' => sprintf($lang['Page_of'], ( floor( $start / $board_config['topics_per_page'] ) + 1 ), ceil( $total_users / $board_config['topics_per_page'] )),

		'L_SEARCH_USERNAME' => $lang['Find_username'],
		'L_SEARCH_EXPLAIN' => $lang['Find_username_explain'],
		'L_RESET' => $lang['Reset'],
		'L_EMAIL' => $lang['Email'],
		'L_ICQ_NUMBER' => $lang['ICQ'],
		'L_MESSENGER' => $lang['MSNM'],
		'L_YAHOO' => $lang['YIM'],
		'L_AIM' => $lang['AIM'],
		'L_JOINED' => $lang['Joined'],
		'L_ACTIVE' => $lang['Last_active'],
		'L_POSTS' => $lang['Posts'],
		'L_SORT_BY' => $lang['Sort_by'],
		'L_SORT_ASCENDING' => $lang['Sort_Ascending'],
		'L_SORT_DESCENDING' => $lang['Sort_Descending'],
		'L_SELECT_MARKED' => $lang['Select_marked'],
		'L_MARK' => $lang['Mark'],
		'L_MARK_ALL' => $lang['Mark_all'],
		'L_UNMARK_ALL' => $lang['Unmark_all'],

		'S_FORM_NAME' => $form,
		'S_FIELD_NAME' => $field,
		'S_COUNT_OPTIONS' => $s_find_count,
		'S_JOINED_TIME_OPTIONS' => $s_find_join_time,
		'S_ACTIVE_TIME_OPTIONS' => $s_find_active_time,
		'S_SORT_OPTIONS' => $s_sort_by,
		'S_SORT_ORDER' => $s_sort_order,
		'S_USERNAME_OPTIONS' => $username_list,
		'S_SEARCH_ACTION' => "search.$phpEx$SID&amp;mode=searchuser&amp;field=$field")
	);

	$sql = "SELECT username, user_id, user_viewemail, user_posts, user_regdate, user_email, user_lastvisit
		FROM " . USERS_TABLE . "
		WHERE user_id <> " . ANONYMOUS . "
		$where_sql
		ORDER BY $order_by
		LIMIT $start, " . $board_config['topics_per_page'];
	$result = $db->sql_query($sql);

	if ( $row = $db->sql_fetchrow($result) )
	{
		$i = 0;
		do
		{
			$username = $row['username'];
			$user_id = $row['user_id'];

			$joined = $user->format_date($row['user_regdate'], $lang['DATE_FORMAT']);
			$posts = ( $row['user_posts'] ) ? $row['user_posts'] : 0;
			$active = ( !$row['user_lastvisit'] ) ? $lang['Never'] : $user->format_date($row['user_lastvisit'], $lang['DATE_FORMAT']);

			$temp_url = "profile.$phpEx$SID&amp;mode=viewprofile&amp;u=$user_id";
			$profile_img = '<a href="' . $temp_url . '">' . create_img($theme['icon_profile'], $lang['Read_profile']) . '</a>';
			$profile = '<a href="' . $temp_url . '">' . $lang['Read_profile'] . '</a>';

			$template->assign_block_vars('memberrow', array(
				'ROW_NUMBER' => $i + ( $start + 1 ),
				'USERNAME' => $username,
				'JOINED' => $joined,
				'POSTS' => $posts,
				'ACTIVE' => $active,
				'PROFILE_IMG' => $profile_img,
				'PROFILE' => $profile)
			);

			$i++;
		}
		while ( $row = $db->sql_fetchrow($result) );
	}

	$template->display('search_user_body');

	include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	exit;
}

?>