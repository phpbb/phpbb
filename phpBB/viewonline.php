<?php
/***************************************************************************
 *                              viewonline.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
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

//
// Start session management
//
$userdata = $session->start();
$acl = new acl('list', $userdata);
//
// End session management
//

//
// Configure style, language, etc.
//
$session->configure($userdata);

//
// Forum info
//
$sql = "SELECT forum_id, forum_name
	FROM " . FORUMS_TABLE;
$result = $db->sql_query($sql);

while( $row = $db->sql_fetchrow($result) )
{
	$forum_data[$row['forum_id']] = $row['forum_name'];
}

//
// Get user list
//
$sql = "SELECT u.user_id, u.username, u.user_allow_viewonline, u.user_colour, s.session_time, s.session_page, s.session_ip
	FROM " . USERS_TABLE . " u, " . SESSIONS_TABLE . " s
	WHERE u.user_id = s.session_user_id
		AND s.session_time >= ".( time() - 300 ) . "
	ORDER BY u.username ASC, s.session_ip ASC, s.session_time DESC";
$result = $db->sql_query($sql);

$guest_users = 0;
$registered_users = 0;
$hidden_users = 0;

$reg_counter = 0;
$guest_counter = 0;
$prev_user = 0;
$prev_ip = '';

while ( $row = $db->sql_fetchrow($result) )
{
	$view_online = false;

	if ( $row['user_id'] != ANONYMOUS )
	{
		$user_id = $row['user_id'];

		if ( $user_id != $prev_user )
		{
			$username = $row['username'];

			if ( $row['user_colour'] )
			{
				$username = '<b style="color:#' . $row['user_colour'] . '">' . $username . '</b>';
			}

			if ( !$row['user_allow_viewonline'] )
			{
				$view_online = ( $acl->get_admin_acl() ) ? true : false;
				$hidden_users++;

				$username = '<i>' . $username . '</i>';
			}
			else
			{
				$view_online = true;
				$registered_users++;
			}

			$which_counter = 'reg_counter';
			$which_row = 'reg_user_row';
			$prev_user = $user_id;
		}
	}
	else
	{
		if ( $row['session_ip'] != $prev_ip )
		{
			$username = $lang['Guest'];
			$view_online = true;
			$guest_users++;

			$which_counter = 'guest_counter';
			$which_row = 'guest_user_row';
		}
	}

	$prev_ip = $row['session_ip'];

	if ( $view_online )
	{
		preg_match('/\/?([a-z]+)\.' . $phpEx . '/', $row['session_page'], $on_page);

		switch ( $on_page[1] )
		{
			case 'index':
				$location = $lang['Forum_index'];
				$location_url = "index.$phpEx$SID";
				break;

			case 'posting':
			case 'viewforum':
			case 'viewtopic':
				preg_match('/f=([0-9]+)/', $row['session_page'], $forum_id);
				$forum_id = $forum_id[1];

				if ( $acl->get_acl($forum_id, 'forum' , 'list') )
				{
					$location = '';
					switch ( $on_page[1] )
					{
						case 'posting':
							$location = sprintf($lang['Posting_message'], $forum_data[$forum_id]);
							break;
						case 'viewtopic':
							$location = sprintf($lang['Reading_topic'], $forum_data[$forum_id]);
							break;
						case 'viewforum':
							$location .= $forum_data[$forum_id];
							break;
					}
					$location_url = "viewforum.$phpEx$SID&amp;f=$forum_id";
				}
				else
				{
					$location = $lang['Forum_index'];
					$location_url = "index.$phpEx$SID";
				}
				break;

			case 'search':
				$location = $lang['Searching_forums'];
				$location_url = "search.$phpEx$SID";
				break;

			case 'profile':
				$location = $lang['Viewing_profile'];
				$location_url = "index.$phpEx$SID";
				break;

			case 'faq':
				$location = $lang['Viewing_FAQ'];
				$location_url = "faq.$phpEx$SID";
				break;

			case 'viewonline':
				$location = $lang['Viewing_online'];
				$location_url = "viewonline.$phpEx$SID";
				break;

			case 'memberslist':
				$location = $lang['Viewing_member_list'];
				$location_url = "memberlist.$phpEx$SID";
				break;

			default:
				$location = $lang['Forum_index'];
				$location_url = "index.$phpEx$SID";
				break;
		}

		$template->assign_block_vars("$which_row", array(
			'USERNAME' => $username,
			'LASTUPDATE' => create_date($board_config['default_dateformat'], $row['session_time'], $board_config['board_timezone']),
			'FORUM_LOCATION' => $location,

			'S_ROW_COUNT' => $$which_counter,

			'U_USER_PROFILE' => "profile.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $user_id,
			'U_FORUM_LOCATION' => $location_url)
		);

		$$which_counter++;
	}
}

if( $registered_users == 0 )
{
	$l_r_user_s = $lang['Reg_users_zero_online'];
}
else if( $registered_users == 1 )
{
	$l_r_user_s = $lang['Reg_user_online'];
}
else
{
	$l_r_user_s = $lang['Reg_users_online'];
}

if( $hidden_users == 0 )
{
	$l_h_user_s = $lang['Hidden_users_zero_online'];
}
else if( $hidden_users == 1 )
{
	$l_h_user_s = $lang['Hidden_user_online'];
}
else
{
	$l_h_user_s = $lang['Hidden_users_online'];
}

if( $guest_users == 0 )
{
	$l_g_user_s = $lang['Guest_users_zero_online'];
}
else if( $guest_users == 1 )
{
	$l_g_user_s = $lang['Guest_user_online'];
}
else
{
	$l_g_user_s = $lang['Guest_users_online'];
}

$template->assign_vars(array(
	'TOTAL_REGISTERED_USERS_ONLINE' => sprintf($l_r_user_s, $registered_users) . sprintf($l_h_user_s, $hidden_users),
	'TOTAL_GUEST_USERS_ONLINE' => sprintf($l_g_user_s, $guest_users),

	'L_WHOSONLINE' => $lang['Who_is_online'],
	'L_ONLINE_EXPLAIN' => $lang['Online_explain'],
	'L_USERNAME' => $lang['Username'],
	'L_FORUM_LOCATION' => $lang['Forum_Location'],
	'L_LAST_UPDATE' => $lang['Last_updated'],
	'L_NO_GUESTS_BROWSING' => $lang['No_users_browsing'],
	'L_NO_REGISTERED_USERS_BROWSING' => $lang['No_users_browsing'])
);

$page_title = $lang['Who_is_online'];
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	'body' => 'viewonline_body.html')
);
make_jumpbox('viewforum.'.$phpEx);

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>