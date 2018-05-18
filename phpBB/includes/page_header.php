<?php
/***************************************************************************
 *                              page_header.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: page_header.php,v 1.1 2010/10/10 15:05:27 orynider Exp $
 *
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

if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
}

define('HEADER_INC', TRUE);

//
// gzip_compression
//
$do_gzip_compress = FALSE;
if ( $board_config['gzip_compress'] )
{
	$phpver = phpversion();

	$useragent = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : getenv('HTTP_USER_AGENT');

	if ( $phpver >= '4.0.4pl1' && ( strstr($useragent,'compatible') || strstr($useragent,'Gecko') ) )
	{
		if ( extension_loaded('zlib') )
		{
			ob_start('ob_gzhandler');
		}
	}
	else if ( $phpver > '4.0' )
	{
		if ( strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') )
		{
			if ( extension_loaded('zlib') )
			{
				$do_gzip_compress = TRUE;
				ob_start();
				ob_implicit_flush(0);

				header('Content-Encoding: gzip');
			}
		}
	}
}

//
// Obtain number of new private messages
// if user is logged in
if(!isset($user) || !is_object($user))
{
	$user = new user();
}

//
// Obtain number of new private messages
// if cache is logged in
if(!isset($cache) || !is_object($cache))
{
	$cache = new cache();
}

//
// Load common language file from phpBB3
//
$user->set_lang($user->lang, $user->help, 'common');
$lang = &$user->lang;

//
// Parse and show the overall header.
//
$template->set_filenames(array(
	'overall_header' => ( empty($gen_simple_header) ) ? 'overall_header.tpl' : 'simple_header.tpl')
);

//
// Generate logged in/logged out status
//
if ( $user->data['session_logged_in'] )
{
	$u_login_logout = 'login.'.$phpEx.'?logout=true&amp;sid=' . $user->data['session_id'];
	$l_login_logout = $lang['Logout'] . ' [ ' . $user->data['username'] . ' ]';
}
else
{
	$u_login_logout = 'login.'.$phpEx;
	$l_login_logout = $lang['Login'];
}

$s_last_visit = ( $user->data['session_logged_in'] ) ? create_date($board_config['default_dateformat'], $user->data['user_lastvisit'], $board_config['board_timezone']) : '';


//
// Get basic (usernames + totals) online
// situation
//
$logged_visible_online = 0;
$logged_hidden_online = 0;
$guests_online = 0;
// Get users online list ... if required
$l_online_users = $online_userlist = $l_online_record = $l_online_time = '';

$l_online_record = $user->lang('RECORD_ONLINE_USERS', (int) $board_config['record_online_users'], $user->format_date($board_config['record_online_date'], false, true));

/**
* Load online data:
*/
if (defined('SHOW_ONLINE'))
{

	$user_forum_sql = ( !empty($forum_id) ) ? "AND s.session_page = " . intval($forum_id) : '';
	$sql = "SELECT u.username, u.user_id, u.user_allow_viewonline, u.user_level, s.session_logged_in, s.session_ip
		FROM ".USERS_TABLE." u, ".SESSIONS_TABLE." s
		WHERE u.user_id = s.session_user_id
			AND s.session_time >= ".( time() - 300 ) . "
			$user_forum_sql
		ORDER BY u.username ASC, s.session_ip ASC";
	if( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not obtain user/online information', '', __LINE__, __FILE__, $sql);
	}

	$userlist_ary = array();
	$userlist_visible = array();

	$prev_user_id = 0;
	$prev_user_ip = $prev_session_ip = '';	
	
	while( $row = $db->sql_fetchrow($result) )
	{
		// User is logged in and therefor not a guest
		if ( $row['session_logged_in'] )
		{
			// Skip multiple sessions for one user
			if ( $row['user_id'] != $prev_user_id )
			{
				$style_color = '';
				if ( $row['user_level'] == ADMIN )
				{
					$row['username'] = '<b>' . $row['username'] . '</b>';
					$style_color = 'style="color:#' . $theme['fontcolor3'] . '"';
				}
				else if ( $row['user_level'] == MOD )
				{
					$row['username'] = '<b>' . $row['username'] . '</b>';
					$style_color = 'style="color:#' . $theme['fontcolor2'] . '"';
				}

				if ( $row['user_allow_viewonline'] )
				{
					$user_online_link = '<a href="' . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=" . $row['user_id']) . '"' . $style_color .'>' . $row['username'] . '</a>';
					$logged_visible_online++;
				}
				else
				{
					$user_online_link = '<a href="' . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=" . $row['user_id']) . '"' . $style_color .'><i>' . $row['username'] . '</i></a>';
					$logged_hidden_online++;
				}

				if ( $row['user_allow_viewonline'] || $userdata['user_level'] == ADMIN )
				{
					$online_userlist .= ( !empty($online_userlist) ) ? ', ' . $user_online_link : $user_online_link;
				}
			}

			$prev_user_id = $row['user_id'];
		}
		else
		{
			// Skip multiple sessions for one user
			if ( $row['session_ip'] != $prev_session_ip )
			{
				$guests_online++;
			}
		}

		$prev_session_ip = $row['session_ip'];
	}
	$db->sql_freeresult($result);

	if ( empty($online_userlist) )
	{
		$online_userlist = $lang['None'];
	}
	
	$online_userlist = ( ( isset($forum_id) ) ? $lang['Browsing_forum'] : $lang['Registered_users'] ) . ' ' . $online_userlist;

	$total_online_users = $logged_visible_online + $logged_hidden_online + $guests_online;

	if ( $total_online_users > $board_config['record_online_users'])
	{
		$board_config['record_online_users'] = $total_online_users;
		$board_config['record_online_date'] = time();

		$sql = "UPDATE " . CONFIG_TABLE . "
			SET config_value = '$total_online_users'
			WHERE config_name = 'record_online_users'";
		if ( !$db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, 'Could not update online user record (nr of users)', '', __LINE__, __FILE__, $sql);
		}

		$sql = "UPDATE " . CONFIG_TABLE . "
			SET config_value = '" . $board_config['record_online_date'] . "'
			WHERE config_name = 'record_online_date'";
		if ( !$db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, 'Could not update online user record (date)', '', __LINE__, __FILE__, $sql);
		}
	}

	$l_online_record = $user->lang('RECORD_ONLINE_USERS', (int) $board_config['record_online_users'], $user->format_date($board_config['record_online_date'], false, true));

	$l_online_time = $user->lang('VIEW_ONLINE_TIMES', (int) 60);
		
	if ( $total_online_users == 0 )
	{
		$l_t_user_s = $lang['Online_users_zero_total'];
	}
	else if ( $total_online_users == 1 )
	{
		$l_t_user_s = $lang['Online_user_total'];
	}
	else
	{
		$l_t_user_s = $lang['Online_users_total'];
	}

	if ( $logged_visible_online == 0 )
	{
		$l_r_user_s = $lang['Reg_users_zero_total'];
	}
	else if ( $logged_visible_online == 1 )
	{
		$l_r_user_s = $lang['Reg_user_total'];
	}
	else
	{
		$l_r_user_s = $lang['Reg_users_total'];
	}

	if ( $logged_hidden_online == 0 )
	{
		$l_h_user_s = $lang['Hidden_users_zero_total'];
	}
	else if ( $logged_hidden_online == 1 )
	{
		$l_h_user_s = $lang['Hidden_user_total'];
	}
	else
	{
		$l_h_user_s = $lang['Hidden_users_total'];
	}

	if ( $guests_online == 0 )
	{
		$l_g_user_s = $lang['Guest_users_zero_total'];
	}
	else if ( $guests_online == 1 )
	{
		$l_g_user_s = $lang['Guest_user_total'];
	}
	else
	{
		$l_g_user_s = $lang['Guest_users_total'];
	}

	$l_online_users = sprintf($l_t_user_s, $total_online_users);
	$l_online_users .= sprintf($l_r_user_s, $logged_visible_online);
	$l_online_users .= sprintf($l_h_user_s, $logged_hidden_online);
	$l_online_users .= sprintf($l_g_user_s, $guests_online);		
}

//
// Obtain number of new private messages
// if user is logged in
if(!isset($auth) || !is_object($auth))
{
	$auth = new auth();
}
$auth->acl($user->data);

// Output the notifications
$total_msgs = $notifications = false;
if ( ($user->data['session_logged_in']) && (empty($gen_simple_header)) )
{
	if ( $user->data['user_new_privmsg'] )
	{
		$l_message_new = ($userdata['user_new_privmsg'] == 1) ? $lang['New_pm'] : $lang['New_pms'];
		$l_privmsgs_text = sprintf($l_message_new, $userdata['user_new_privmsg']);

		if ( $userdata['user_last_privmsg'] > $userdata['user_lastvisit'] )
		{
			$sql = "UPDATE " . USERS_TABLE . "
				SET user_last_privmsg = " . $userdata['user_lastvisit'] . "
				WHERE user_id = " . $userdata['user_id'];
			if ( !$db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, 'Could not update private message new/read time for user', '', __LINE__, __FILE__, $sql);
			}

			$s_privmsg_new = 1;
			$icon_pm = !empty($images['pm_new_msg']) ? $images['pm_new_msg'] : 'new_msg';
		}
		else
		{
			$s_privmsg_new = 0;
			$icon_pm = !empty($images['pm_new_msg']) ? $images['pm_new_msg'] : 'new_msg';
		}
	}
	else
	{
		$l_privmsgs_text = $lang['No_new_pm'];

		$s_privmsg_new = 0;
		$icon_pm = !empty($images['pm_no_new_msg']) ? $images['pm_no_new_msg'] : 'no_new_msg';
	}

	if ( $userdata['user_unread_privmsg'] )
	{
		$l_message_unread = ( $userdata['user_unread_privmsg'] == 1 ) ? $lang['Unread_pm'] : $lang['Unread_pms'];
		$l_privmsgs_text_unread = sprintf($l_message_unread, $userdata['user_unread_privmsg']);
	}
	else
	{
		$l_privmsgs_text_unread = $lang['No_unread_pm'];
	}
	//
	// SQL to pull appropriate message, prevents nosey people
	// reading other peoples messages ... hopefully!
	//
	$privmsgs_id = request_var(POST_POST_URL, $s_privmsg_new);
	$l_box_name = $lang['Inbox'];
	$pm_sql_user = "AND pm.privmsgs_to_userid = " . $user->data['user_id'] . " 
		AND ( pm.privmsgs_type = " . PRIVMSGS_READ_MAIL . " 
			OR pm.privmsgs_type = " . PRIVMSGS_NEW_MAIL . " 
			OR pm.privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . " )";
	//
	// Major query obtains the message ...
	//
	$sql = "SELECT u.username AS username_1, u.user_id AS user_id_1, u2.username AS username_2, u2.user_id AS user_id_2, u.user_sig_bbcode_uid, u.user_regdate AS user_regdate1,u.user_posts AS user_posts1, u.user_from AS user_from1, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_msnm, u.user_viewemail, u.user_rank AS user_rank1, u.user_sig, u.user_avatar AS user_avatar1,u.user_avatar_type AS user_avatar_type1, pm.*, pmt.privmsgs_bbcode_uid, pmt.privmsgs_text
		FROM " . PRIVMSGS_TABLE . " pm, " . PRIVMSGS_TEXT_TABLE . " pmt, " . USERS_TABLE . " u, " . USERS_TABLE . " u2 
		WHERE pm.privmsgs_id = pmt.privmsgs_text_id 
			$pm_sql_user 
			AND u.user_id = pm.privmsgs_from_userid 
			AND u2.user_id = pm.privmsgs_to_userid";
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not query private message post information', '', __LINE__, __FILE__, $sql);
	}

	//
	// Did the query return any data?
	//
	$notifications = array();	
	if ( $privmsg = $db->sql_fetchrow($result) )
	{
		$notifications[] = $privmsg;
		$privmsg_id = $privmsg['privmsgs_id'];
	}
	else
	{	
		$privmsg_id = request_var(POST_POST_URL, $s_privmsg_new);	
	}
	$db->sql_freeresult($result);
	if ( !($total_msgs = count($notifications)) )
	{
		$total_msgs = 0;
		
		// Merge default options
		$notifications = array_merge(array(
			'notification_id'	=> 0,
			'notification_time'	=> time(),
			'user_id'			=> $user->data['user_id'],
			'order_by'			=> 'notification_time',
			'older_unread'		=> 1,
			'order_dir'			=> 'DESC',
			'all_unread'		=> 0,		
			'unread_count'		=> 0,	
			'limit'				=> 5,
			'start'				=> 0,
			'REASON'			=> 'Could not query private message post information',
			'count_unread'		=> 0,
			'count_total'		=> $total_msgs,
		), $notifications);	
	}	
		
	// Merge default options
	$notifications = array_merge(array(
		'notification_id'	=> $privmsg_id,
		'notification_time'	=> !empty($privmsg['privmsgs_date']) ? $privmsg['privmsgs_date'] : time(),
		'user_id'			=> $user->data['user_id'],
		'order_by'			=> 'notification_time',
		'older_unread'		=> $s_privmsg_new,
		'order_dir'			=> 'DESC',
		'all_unread'		=> $s_privmsg_new,		
		'unread_count'		=> $s_privmsg_new,	
		'limit'				=> 5,
		'start'				=> 0,
		'REASON'			=> $privmsg['privmsgs_text'],
		'count_unread'		=> $s_privmsg_new,
		'count_total'		=> $total_msgs,
	), $notifications);	
}
else
{
	$icon_pm = !empty($images['pm_no_new_msg']) ? $images['pm_no_new_msg'] : 'no_new_msg';
	$l_privmsgs_text = $lang['Login_check_pm'];
	$l_privmsgs_text_unread = '';
	$s_privmsg_new = 0;
	$privmsg_id = 0;
	$notifications = array(
		'all_unread'	=> $s_privmsg_new,
		'unread_count'	=> $s_privmsg_new,	
		'limit'			=> 5,
	);
	// Merge default options
	$notifications = array_merge(array(
		'notification_id'	=> false,
		'notification_time'	=> time(),
		'user_id'			=> $user->data['user_id'],
		'order_by'			=> 'notification_time',
		'order_dir'			=> 'DESC',
		'limit'				=> 5,
		'start'				=> 0,
		'all_unread'		=> $s_privmsg_new,
		'count_unread'		=> $s_privmsg_new,
		'count_total'		=> false,
		'S_ROW_COUNT'		=> 0,
		'S_NUM_ROWS'		=> 0,
		'UNREAD'			=> 0,
		'STYLING'			=> 0,
		'URL'				=> 0,
		'U_MARK_READ'		=> 0,
		'AVATAR'			=> 0,
		'T_THEME_PATH'		=> 0,
		'FORMATTED_TITLE'	=> 0,
		'REFERENCE'			=> 0,
		'FORUM'				=> 0,
		'REASON'			=> 0,
		'TIME'				=> 0,
		'U_MARK_READ'		=> 0,		
	), $notifications);	
}
$notification_mark_hash = generate_link_hash('mark_all_notifications_read');
$mark_hash = generate_link_hash('mark_notification_read');
$u_mark_read = append_sid($phpbb_root_path . 'privmsg.' . $phpEx . '?mark_notification=' . $notifications['notification_id'] . '&amp;folder=inbox&amp;hash=' . $mark_hash);

// Login box?
//
if ( !$user->data['session_logged_in'] )
{
	$template->assign_block_vars('switch_user_logged_out', array());
	//
	// Allow autologin?
	//
	if (!isset($board_config['allow_autologin']) || $board_config['allow_autologin'] )
	{
		$template->assign_block_vars('switch_allow_autologin', array());
		$template->assign_block_vars('switch_user_logged_out.switch_allow_autologin', array());
	}
	$template->assign_block_vars('notifications', array());	
}
else
{
	$template->assign_block_vars('switch_user_logged_in', array());
	
	//if ( !empty($userdata['user_popup_pm']) )
	//{
		$template->assign_block_vars('switch_enable_pm_popup', array());
		
		$template->assign_block_vars('notifications', array(
			'NOTIFICATION_ID'	=> $notifications['notification_id'],

			'USER_ID' =>  $notifications['user_id'], 
			'ORDER_BY' =>  $notifications['order_by'], 
			'ORDER_DIR' =>  $notifications['order_dir'], 
			'ALL_UNREAD' =>  $notifications['all_unread'],
			'UNREAD_COUNT' =>  $notifications['unread_count'],
			'LIMIT' =>  $notifications['limit'], 
			'START' =>  $notifications['start'], 
			'COUNT_UNREAD' =>  $notifications['count_unread'], 
			'COUNT_TOTAL' =>  $notifications['count_total'], 			
			
			'STYLING'			=> 'notification-reported',
			'AVATAR'			=> phpbb_get_user_avatar($user->data),
			'FORMATTED_TITLE'	=> $user->lang('NOTIFICATION', $user->data['username'], false),
			
			'REFERENCE'			=> $user->lang('NOTIFICATION_REFERENCE', censor_text($l_privmsgs_text)),
			'FORUM'				=> append_sid('privmsg.'.$phpEx.'?folder=inbox'), //$this->get_forum(),
			'REASON'			=> $notifications['REASON'], //$this->get_reason(),
			'URL'				=> append_sid('privmsg.'.$phpEx.'?folder=inbox'), //$this->get_url(),
			
			'TIME'	   			=> $user->format_date($notifications['notification_time']),
			
			'UNREAD'			=> $l_privmsgs_text_unread,
			'U_MARK_READ'		=> (!$user->data['user_unread_privmsg']) ? $u_mark_read : '',				
		));		
	//}
}



if ( !defined('PHPBB_VERSION') )
{
	define('PHPBB_VERSION', '2'.$board_config['version']);
}
//
// Generate HTML required for Mozilla Navigation bar
//
if (!isset($nav_links))
{
	$nav_links = array();
}

$nav_links_html = '';
$nav_link_proto = '<link rel="%s" href="%s" title="%s" />' . "\n";
while( list($nav_item, $nav_array) = @each($nav_links) )
{
	if ( !empty($nav_array['url']) )
	{
		$nav_links_html .= sprintf($nav_link_proto, $nav_item, append_sid($nav_array['url']), $nav_array['title']);
	}
	else
	{
		// We have a nested array, used for items like <link rel='chapter'> that can occur more than once.
		while( list(,$nested_array) = each($nav_array) )
		{
			$nav_links_html .= sprintf($nav_link_proto, $nav_item, $nested_array['url'], $nested_array['title']);
		}
	}
}

$forum_id = request_var('f', (isset($forum_id) ? $forum_id : 0));
$topic_id = request_var('t', (isset($topic_id) ? $topic_id : 0));

$s_feed_news = false;

$img_lang = $board_config['default_lang'];

// Format Timezone. We are unable to use array_pop here, because of PHP3 compatibility
$l_timezone = explode('.', $board_config['board_timezone']);
$l_timezone = (count($l_timezone) > 1 && $l_timezone[count($l_timezone)-1] != 0) ? $lang[sprintf('%.1f', $board_config['board_timezone'])] : $lang[number_format($board_config['board_timezone'])];

$default_lang = ($user->data['user_lang']) ? $user->data['user_lang'] : $board_config['default_lang'];

$server_name = !empty($board_config['server_name']) ? preg_replace('/^\/?(.*?)\/?$/', "\\1", trim($board_config['server_name'])) : 'localhost';
$server_protocol = ($board_config['cookie_secure'] ) ? 'https://' : 'http://';
$server_port = (($board_config['server_port']) && ($board_config['server_port'] <> 80)) ? ':' . trim($board_config['server_port']) . '/' : '/';
$script_name_phpbb = preg_replace('/^\/?(.*?)\/?$/', "\\1", trim($board_config['script_path'])) . '/';		
$server_url = $server_protocol . str_replace("//", "/", $server_name . $server_port . $server_name . '/'); //On some server the slash is not added and this trick will fix it	
$corrected_url = $server_protocol . $server_name . $server_port . $script_name_phpbb;
$board_url = $server_url . $script_name_phpbb;
$web_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? $board_url : $corrected_url;

// Send a proper content-language to the output
$user_lang = !empty($user->lang['USER_LANG']) ? $user->lang['USER_LANG'] : $user->encode_lang($user->lang_name);
if (strpos($user_lang, '-x-') !== false)
{
	$user_lang = substr($user_lang, 0, strpos($user_lang, '-x-'));
}
	
$phpbb_version_parts = explode('.', PHPBB_VERSION, 3);
$phpbb_major = $phpbb_version_parts[0] . '.' . $phpbb_version_parts[1];

@define('USER_ACTIVATION_NONE', 0);
@define('USER_ACTIVATION_SELF', 1);
@define('USER_ACTIVATION_ADMIN', 2);
@define('USER_ACTIVATION_DISABLE', 3);

//
// Show the overall footer.
//
$admin_link = ($user->data['user_level'] == ADMIN) ? '<a href="admin/index.' . $phpEx . '?sid=' . $user->data['session_id'] . '">' . $lang['Admin_panel'] . '</a><br /><br />' : '';

// Forum rules and subscription info
$s_watching_forum = array(
	'link'			=> '',
	'link_toggle'	=> '',
	'title'			=> '',
	'title_toggle'	=> '',
	'is_watching'	=> false,
);

 
//
// The following assigns all _common_ variables that may be used at any point
// in a template.
//
$template->assign_vars(array(
	'SITENAME' 				=> $board_config['sitename'],
	'SITE_DESCRIPTION' 		=> $board_config['site_desc'],
	'PAGE_TITLE' 			=> isset($page_title) ? $page_title : $lang['Index'],
	'LANG' 					=> $img_lang,	
	'SCRIPT_NAME' 			=> str_replace('.' . $phpEx, '', basename(__FILE__)),	
	'LAST_VISIT_DATE' 		=> sprintf($lang['You_last_visit'], $s_last_visit),
	'LAST_VISIT_YOU' 		=> $s_last_visit,	
	'CURRENT_TIME' 			=> sprintf($lang['Current_time'], create_date($board_config['default_dateformat'], time(), $board_config['board_timezone'])),
	'TOTAL_USERS_ONLINE' 	=> $l_online_users,
	'RECORD_USERS' 			=> $l_online_record,	
	'LOGGED_IN_USER_LIST' 	=> $online_userlist,
	'RECORD_USERS' 			=> sprintf($lang['Record_online_users'], $board_config['record_online_users'], create_date($board_config['default_dateformat'], $board_config['record_online_date'], $board_config['board_timezone'])),
	
	'CURRENT_USER_AVATAR'			=> phpbb_get_user_avatar($user->data),
	'CURRENT_USERNAME_SIMPLE'		=> get_username_string('no_profile', $user->data['user_id'], $user->data['username'], $user->data['user_colour']),
	'CURRENT_USERNAME_FULL'			=> get_username_string('full', $user->data['user_id'], $user->data['username'], $user->data['user_colour']),				
	
	'S_NOTIFICATIONS_DISPLAY'		=> true,	
	'S_SHOW_COPPA'					=> false,
	'S_REGISTRATION'				=> true,	
	
	'UNREAD_NOTIFICATIONS_COUNT'	=> ($notifications !== false) ? $notifications['unread_count'] : '',
	'NOTIFICATIONS_COUNT'			=> ($notifications !== false) ? $notifications['unread_count'] : '',
	'U_VIEW_ALL_NOTIFICATIONS'		=> append_sid("{$phpbb_root_path}profile.$phpEx?i=ucp_notifications"),
	'U_MARK_ALL_NOTIFICATIONS'		=> append_sid("{$phpbb_root_path}profile.$phpEx?i=ucp_notifications&amp;mode=notification_list&amp;mark=all&amp;token=" . $notification_mark_hash),
	'U_NOTIFICATION_SETTINGS'		=> append_sid("{$phpbb_root_path}profile.$phpEx?i=ucp_notifications&amp;mode=notification_options"),
	'S_NOTIFICATIONS_DISPLAY'		=> $user->data['user_active'],		

	'loops'							=> '', // To get loops	
	
	'S_PLUPLOAD'					=> false,
	'S_IN_SEARCH'					=> false,	
	'S_DISPLAY_QUICK_LINKS'			=> true,
	
	'S_USER_NEW_PRIVMSG'			=> $user->data['user_new_privmsg'],
	'S_USER_UNREAD_PRIVMSG'			=> $user->data['user_unread_privmsg'],
	'S_USER_NEW'					=> ($user->data['user_active'] == 0) ? true : false,
	
	'PRIVATE_MESSAGE_COUNT'			=> (!empty($userdata['user_unread_privmsg'])) ? $user->data['user_unread_privmsg'] : 0,
	'PRIVATE_MESSAGE_INFO' 			=> $l_privmsgs_text,
	'PRIVATE_MESSAGE_INFO_UNREAD' 	=> $l_privmsgs_text_unread,
	'PRIVATE_MESSAGE_NEW_FLAG' 		=> $s_privmsg_new,

	'PRIVMSG_IMG' 			=> $icon_pm,

	'L_USERNAME' 			=> $lang['Username'],
	'L_PASSWORD' 			=> $lang['Password'],
	'L_LOGIN_LOGOUT' 		=> $l_login_logout,
	'L_LOGIN' 				=> $lang['Login'],
	'L_LOG_ME_IN' 			=> $lang['Log_me_in'],
	'L_AUTO_LOGIN' 			=> $lang['Log_me_in'],
	'L_INDEX' 				=> sprintf($lang['Forum_Index'], $board_config['sitename']),
	'L_REGISTER' 			=> $lang['Register'],
	'L_PROFILE' 			=> $lang['Profile'],
	'L_SEARCH' 				=> $lang['Search'],
	'L_PRIVATEMSGS'			=> $lang['Private_Messages'],
	'L_WHO_IS_ONLINE' 		=> $lang['Who_is_Online'],
	'L_MEMBERLIST' 			=> $lang['Memberlist'],
	'L_FAQ' 				=> $lang['FAQ'],
	'L_USERGROUPS' 			=> $lang['Usergroups'],
	'L_SEARCH_NEW' 			=> $lang['Search_new'],
	'L_SEARCH_UNANSWERED' 	=> $lang['Search_unanswered'],
	'L_SEARCH_SELF' 		=> $lang['Search_your_posts'],
	'L_WHOSONLINE_ADMIN' 	=> sprintf($lang['Admin_online_color'], '<span style="color:#' . $theme['fontcolor3'] . '">', '</span>'),
	'L_WHOSONLINE_MOD' 		=> sprintf($lang['Mod_online_color'], '<span style="color:#' . $theme['fontcolor2'] . '">', '</span>'),
	
	'L_POST_BY_AUTHOR' 		=> $lang['Post_by_author'],
	'L_POSTED_ON_DATE' 		=> $lang['Posted_on_date'],
	'L_IN' 					=> $lang['In'],	
	
	//navbar_footer
	'U_WATCH_FORUM_LINK'	=> $s_watching_forum['link'],
	'U_WATCH_FORUM_TOGGLE'	=> $s_watching_forum['link_toggle'],
	'S_WATCH_FORUM_TITLE'	=> $s_watching_forum['title'],
	'S_WATCH_FORUM_TOGGLE'	=> $s_watching_forum['title_toggle'],
	'S_WATCHING_FORUM'		=> $s_watching_forum['is_watching'],
				
	'U_SEARCH_SELF'			=> append_sid("{$phpbb_root_path}search.$phpEx?search_id=egosearch"),
	'U_SEARCH_NEW'			=> append_sid("{$phpbb_root_path}search.$phpEx?search_id=newposts"),
	'U_SEARCH_UNANSWERED'	=> append_sid("{$phpbb_root_path}search.$phpEx?search_id=unanswered"),
	'U_SEARCH_UNREAD'		=> append_sid("{$phpbb_root_path}search.$phpEx?search_id=unreadposts"),
	'U_SEARCH_ACTIVE_TOPICS'=> append_sid("{$phpbb_root_path}search.$phpEx?search_id=active_topics"),
	
	'U_INDEX' 				=> append_sid("{$phpbb_root_path}index.$phpEx"),
	'U_CANONICAL' 			=> generate_board_url() . '/' . append_sid("index.$phpEx"),		
	'U_SITE_HOME'			=> (!empty($board_config['site_home_url'])) ? $board_config['site_home_url'] : append_sid('./../index.'.$phpEx),	
	'U_REGISTER' 			=> append_sid('profile.'.$phpEx.'?mode=register'),
	'U_PROFILE' 			=> append_sid('profile.'.$phpEx.'?mode=editprofile'),
	'U_RESTORE_PERMISSIONS'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx?mode=restore_perm"),
	'U_USER_PROFILE'		=> get_username_string('profile', $user->data['user_id'], $user->data['username'], false),	
	'U_PRIVATEMSGS' 		=> append_sid('privmsg.'.$phpEx.'?folder=inbox'),
	'U_PRIVATEMSGS_POPUP' 	=> append_sid('privmsg.'.$phpEx.'?mode=newpm'),
	'U_SEARCH' 				=> append_sid('search.'.$phpEx),
	'U_MEMBERLIST' 			=> append_sid('memberlist.'.$phpEx),
	'U_MODCP' 				=> append_sid('modcp.'.$phpEx),
	'U_MCP'					=> (($auth->acl_get('m_') || $auth->acl_getf_global('m_')) ? append_sid("{$phpbb_root_path}modcp.$phpEx?i=main&amp;mode=front" . $user->session_id) : ''),	
	'U_FAQ' 				=> append_sid('faq.'.$phpEx),
	'U_VIEWONLINE' 			=> append_sid('viewonline.'.$phpEx),
	'U_LOGIN_LOGOUT' 		=> append_sid($u_login_logout),
	'U_GROUP_CP' 			=> append_sid('groupcp.'.$phpEx),
	
	'U_SEND_PASSWORD' 		=> ($user->data['user_email']) ? append_sid("{$phpbb_root_path}profile.$phpEx?mode=sendpassword") : '',
	
	'S_VIEWTOPIC' 			=> append_sid("viewtopic.$phpEx?" . "f=" . $forum_id . "&amp;t=" . $topic_id), 
	'S_VIEWFORUM' 			=> append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id"),
	'S_IN_MCP' 				=> defined('IN_MCP') ? true : false,
	'S_IN_PROFILE' 			=> defined('IN_PROFILE') ? true : false,
	'S_IN_UCP' 				=> defined('IN_UCP') ? true : false,	
	
	'U_CONTACT_US'			=> ($user->data['user_last_privmsg']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx?mode=contactadmin") : '',
	'U_TEAM'				=> ($user->data['user_id'] != ANONYMOUS && !$auth->acl_get('u_viewprofile')) ? '' : append_sid("{$phpbb_root_path}memberlist.$phpEx?mode=team"),
	'U_TERMS_USE'			=> append_sid("{$phpbb_root_path}profile.$phpEx?mode=terms"),
	'U_PRIVACY'				=> append_sid("{$phpbb_root_path}profile.$phpEx?mode=privacy"),
	'U_RESTORE_PERMISSIONS'	=> ($user->data['user_perm_from'] && $auth->acl_get('a_switchperm')) ? append_sid("{$phpbb_root_path}profile.$phpEx?mode=restore_perm") : '',
	'U_FEED'				=> '',	
		
	'S_CONTENT_DIRECTION'	=> $lang['DIRECTION'],
	'S_CONTENT_FLOW_BEGIN'	=> ($lang['DIRECTION'] == 'ltr') ? 'left' : 'right',
	'S_CONTENT_FLOW_END'	=> ($lang['DIRECTION'] == 'ltr') ? 'right' : 'left',
	'S_CONTENT_ENCODING'	=> !empty($lang['ENCODING']) ? $lang['ENCODING'] : 'UTF-8',
	'S_CONTENT_DIR_LEFT' 	=> $lang['LEFT'],
	'S_CONTENT_DIR_RIGHT' 	=> $lang['RIGHT'],
	
	'S_LOGIN_ACTION' 		=> append_sid('login.'.$phpEx),
	'S_LOGIN_REDIRECT'		=> build_url(),

	'S_ENABLE_FEEDS'			=> false,
	'S_ENABLE_FEEDS_OVERALL'	=> false,
	'S_ENABLE_FEEDS_FORUMS'		=> false,
	'S_ENABLE_FEEDS_TOPICS'		=> false,
	'S_ENABLE_FEEDS_TOPICS_ACTIVE'	=> false,
	'S_ENABLE_FEEDS_NEWS'		=> false,
	
	'S_USER_LOGGED_IN' 		=> ($user->data['user_id'] != ANONYMOUS) ? true : false,
	'S_AUTOLOGIN_ENABLED'	=> ($board_config['allow_autologin']) ? true : false,
	'S_BOARD_DISABLED'		=> ($board_config['board_disable']) ? true : false,	
	'S_USERNAME'			=> !empty($user->data['username']) ? $user->data['username'] : 'Anonymous',
	'S_REGISTERED_USER'		=> (!empty($user->data['user_active'])) ? true : false,
	'S_IS_BOT'				=> (!empty($user->data['is_bot'])) ? true : false,
	'S_USER_LANG'			=> $user_lang,
	'S_USER_BROWSER'		=> (isset($user->data['session_browser'])) ? $user->data['session_browser'] : $user->lang('UNKNOWN_BROWSER'),

	'S_LOAD_UNREADS'		=> ($user->data['user_id'] != ANONYMOUS) ? true : false,	
	
	'S_TIMEZONE'			=> sprintf($user->lang['All_times'], $l_timezone),
	'S_DISPLAY_ONLINE_LIST'	=> ($l_online_time) ? 1 : 0,
	'S_DISPLAY_SEARCH'		=> (isset($auth) ? ($user->data['user_id'] != ANONYMOUS) : 1),
	'S_DISPLAY_PM'			=> ($user->data['user_id'] != ANONYMOUS) ? true : false,
	'S_DISPLAY_MEMBERLIST'	=> (isset($auth)) ? ($user->data['user_id'] != ANONYMOUS) : 0,
	'S_NEW_PM'				=> ($s_privmsg_new) ? 1 : 0,
	'S_REGISTER_ENABLED'	=> ($board_config['require_activation'] != USER_ACTIVATION_DISABLE) ? true : false,
	'S_FORUM_ID'			=> $forum_id,
	'S_TOPIC_ID	'			=> $topic_id,		

	'S_SIMPLE_MESSAGE'		=> false,

	'SID'				=> !empty($SID) ? $SID : $user->session_id,
	'_SID'				=> !empty($_GET['sid']) ? $_GET['sid'] : $user->session_id,
	'SESSION_ID'		=> !empty($user->data['session_id']) ? $user->data['session_id'] : (isset($_COOKIE[$board_config['cookie_name'] . '_sid'] ) ? $_COOKIE[$board_config['cookie_name'] . '_sid'] : ''),
	'ROOT_PATH'			=> $web_path,
	'FULL_SITE_PATH'	=> $web_path,
	'CMS_PAGE_HOME'		=> $board_url,
	'BOARD_URL'			=> $board_url,
	'PHPBB_VERSION'		=> PHPBB_VERSION,
	'PHPBB_MAJOR'		=> $phpbb_major,
	'S_COOKIE_NOTICE'	=> !empty($board_config['cookie_name']),
	
	'T_ASSETS_VERSION'		=> $phpbb_major,
	'T_ASSETS_PATH'			=> "{$web_path}assets",	
	'T_THEME_PATH'			=> "{$web_path}templates/" . rawurlencode($theme['template_name'] ? $theme['template_name'] : str_replace('.css', '', $theme['head_stylesheet'])) . '/theme',
	'T_TEMPLATE_PATH'		=> "{$web_path}templates/" . rawurlencode($theme['template_name']) . '',
	'T_SUPER_TEMPLATE_PATH'	=> "{$web_path}templates/" . rawurlencode($theme['template_name']) . '/template',
		
	'T_IMAGES_PATH'			=> "{$web_path}images/",
	'T_SMILIES_PATH'		=> "{$web_path}{$board_config['smilies_path']}/",
	'T_AVATAR_GALLERY_PATH'	=> "{$web_path}{$board_config['avatar_gallery_path']}/",
	
	'T_ICONS_PATH'			=> !empty($board_config['icons_path']) ? "{$web_path}{$board_config['icons_path']}/" : $web_path.'/images/icons/',
	'T_RANKS_PATH'			=> !empty($board_config['ranks_path']) ? "{$web_path}{$board_config['ranks_path']}/" : $web_path.'/images/ranks/',
	'T_UPLOAD_PATH'			=> !empty($board_config['upload_path']) ? "{$web_path}{$board_config['upload_path']}/" : $web_path.'/cache/',	
	
	'T_STYLESHEET_LINK'		=> "{$web_path}templates/" . rawurlencode($theme['template_name'] ? $theme['template_name'] : str_replace('.css', '', $theme['head_stylesheet'])) . '/theme/stylesheet.css',
	'T_STYLESHEET_LANG_LINK'=> "{$web_path}templates/" . rawurlencode($theme['template_name'] ? $theme['template_name'] : str_replace('.css', '', $theme['head_stylesheet'])) . '/theme/images/lang_' . $default_lang . '/stylesheet.css',
	'T_FONT_AWESOME_LINK'	=> "{$web_path}assets/css/font-awesome.min.css",
	
	'T_JQUERY_LINK'			=> !empty($board_config['allow_cdn']) && !empty($board_config['load_jquery_url']) ? $board_config['load_jquery_url'] : "{$web_path}assets/javascript/jquery.min.js?assets_version=" . $phpbb_major,
	'S_ALLOW_CDN'			=> !empty($board_config['allow_cdn']),		
	
	'T_THEME_NAME'			=> rawurlencode($theme['template_name']),
	'T_THEME_LANG_NAME'		=> $user->data['user_lang'],
	'T_TEMPLATE_NAME'		=> $theme['template_name'],
	'T_SUPER_TEMPLATE_NAME'	=> rawurlencode($theme['template_name']),
	'T_IMAGES'				=> 'images',
	'T_SMILIES'				=> $board_config['smilies_path'],
	'T_AVATAR_GALLERY'		=> $board_config['avatar_gallery_path'],
	
	'T_ICONS_PATH'		=> !empty($board_config['icons_path']) ? $board_config['icons_path'] : '/images/icons/',
	'T_RANKS_PATH'		=> !empty($board_config['ranks_path']) ? $board_config['ranks_path'] : '/images/ranks/',
	'T_UPLOAD_PATH'		=> !empty($board_config['upload_path']) ? $board_config['upload_path'] : '/cache/',

	'SITE_LOGO_IMG'		=> ($theme['template_name'] == 'subSilver') ? 'logo_phpBB.gif' : 'site_logo.gif',	
	
	'T_HEAD_STYLESHEET' => $theme['head_stylesheet'],
	'T_BODY_BACKGROUND' => $theme['body_background'],
	'T_BODY_BGCOLOR' => '#'.$theme['body_bgcolor'],
	'T_BODY_TEXT' => '#'.$theme['body_text'],
	'T_BODY_LINK' => '#'.$theme['body_link'],
	'T_BODY_VLINK' => '#'.$theme['body_vlink'],
	'T_BODY_ALINK' => '#'.$theme['body_alink'],
	'T_BODY_HLINK' => '#'.$theme['body_hlink'],
	'T_TR_COLOR1' => '#'.$theme['tr_color1'],
	'T_TR_COLOR2' => '#'.$theme['tr_color2'],
	'T_TR_COLOR3' => '#'.$theme['tr_color3'],
	'T_TR_CLASS1' => $theme['tr_class1'],
	'T_TR_CLASS2' => $theme['tr_class2'],
	'T_TR_CLASS3' => $theme['tr_class3'],
	'T_TH_COLOR1' => '#'.$theme['th_color1'],
	'T_TH_COLOR2' => '#'.$theme['th_color2'],
	'T_TH_COLOR3' => '#'.$theme['th_color3'],
	'T_TH_CLASS1' => $theme['th_class1'],
	'T_TH_CLASS2' => $theme['th_class2'],
	'T_TH_CLASS3' => $theme['th_class3'],
	'T_TD_COLOR1' => '#'.$theme['td_color1'],
	'T_TD_COLOR2' => '#'.$theme['td_color2'],
	'T_TD_COLOR3' => '#'.$theme['td_color3'],
	'T_TD_CLASS1' => $theme['td_class1'],
	'T_TD_CLASS2' => $theme['td_class2'],
	'T_TD_CLASS3' => $theme['td_class3'],
	'T_FONTFACE1' => $theme['fontface1'],
	'T_FONTFACE2' => $theme['fontface2'],
	'T_FONTFACE3' => $theme['fontface3'],
	'T_FONTSIZE1' => $theme['fontsize1'],
	'T_FONTSIZE2' => $theme['fontsize2'],
	'T_FONTSIZE3' => $theme['fontsize3'],
	'T_FONTCOLOR1' => '#'.$theme['fontcolor1'],
	'T_FONTCOLOR2' => '#'.$theme['fontcolor2'],
	'T_FONTCOLOR3' => '#'.$theme['fontcolor3'],
	'T_SPAN_CLASS1' => $theme['span_class1'],
	'T_SPAN_CLASS2' => $theme['span_class2'],
	'T_SPAN_CLASS3' => $theme['span_class3'],

	'NAV_LINKS' => $nav_links_html,
	
	'L_ACP' => $lang['Admin_panel'],
	'U_ACP' => ($user->data['user_level'] == ADMIN) ? "{$phpbb_root_path}admin/index.$phpEx?sid=" . $user->session_id : $admin_link)
);



// Add no-cache control for cookies if they are set
//$c_no_cache = (isset($_COOKIE[$board_config['cookie_name'] . '_sid']) || isset($_COOKIE[$board_config['cookie_name'] . '_data'])) ? 'no-cache="set-cookie", ' : '';

// Work around for "current" Apache 2 + PHP module which seems to not
// cope with private cache control setting
if (!empty($_SERVER['SERVER_SOFTWARE']) && strstr($_SERVER['SERVER_SOFTWARE'], 'Apache/2'))
{
	header ('Cache-Control: no-cache, pre-check=0, post-check=0');
}
else
{
	header ('Cache-Control: private, pre-check=0, post-check=0, max-age=0');
}
header ('Expires: 0');
header ('Pragma: no-cache');

$template->pparse('overall_header');

?>