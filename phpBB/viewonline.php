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


// Start session management
$user->start();
$user->setup();
$auth->acl($user->data);


$sort_key = (!empty($_REQUEST['sk'])) ? htmlspecialchars($_REQUEST['sk']) : 'b';
$sort_dir = (!empty($_REQUEST['sd'])) ? htmlspecialchars($_REQUEST['sd']) : 'd';



$sort_key_text = array('a' => $user->lang['SORT_USERNAME'], 'b' => $user->lang['SORT_LOCATION'], 'c' => $user->lang['SORT_JOINED']);
$sort_key_sql = array('a' => 'username', 'b' => 'session_time', 'c' => 'session_page');

// Sorting and order
$order_by = $sort_key_sql[$sort_key] . '  ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');


// Forum info
$sql = 'SELECT forum_id, forum_name
	FROM ' . FORUMS_TABLE;
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$forum_data[$row['forum_id']] = $row['forum_name'];
}
$db->sql_freeresult($result);


// Get user list
$sql = 'SELECT u.user_id, u.username, u.user_allow_viewonline, u.user_colour, s.session_time, s.session_page, s.session_ip, s.session_allow_viewonline
	FROM ' . USERS_TABLE . ' u, ' . SESSIONS_TABLE . ' s
	WHERE u.user_id = s.session_user_id
		AND s.session_time >= ' . (time() - ($config['load_online_time'] * 60)) . '
	ORDER BY ' . $order_by;
$result = $db->sql_query($sql);

$prev_ip = '';
$logged_visible_online = $logged_hidden_online = $guests_online = $reg_counter = $guest_counter = $prev_user = 0;
while ($row = $db->sql_fetchrow($result))
{
	$view_online = false;

	if ($row['user_id'] != ANONYMOUS)
	{
		$user_id = $row['user_id'];

		if ($user_id != $prev_user)
		{
			$username = $row['username'];

			if ($row['user_colour'])
			{
				$username = '<b style="color:#' . $row['user_colour'] . '">' . $username . '</b>';
			}

			if (!$row['user_allow_viewonline'] || !$row['session_allow_viewonline'])
			{
				$view_online = ($auth->acl_gets('u_viewonline')) ? true : false;
				$logged_hidden_online++;

				$username = '<i>' . $username . '</i>';
			}
			else
			{
				$view_online = true;
				$logged_visible_online++;
			}

			$which_counter = 'reg_counter';
			$which_row = 'reg_user_row';
			$prev_user = $user_id;
		}
	}
	else
	{
		if ($row['session_ip'] != $prev_ip)
		{
			$username = $user->lang['GUEST'];
			$view_online = true;
			$guests_online++;

			$which_counter = 'guest_counter';
			$which_row = 'guest_user_row';
		}
	}

	$prev_ip = $row['session_ip'];

	if ($view_online)
	{
		preg_match('#([a-z]+)#', $row['session_page'], $on_page);

		switch ($on_page[1])
		{
			case 'index':
				$location = $user->lang['INDEX'];
				$location_url = "index.$phpEx$SID";
				break;

			case 'posting':
			case 'viewforum':
			case 'viewtopic':
				preg_match('#f=([0-9]+)#', $row['session_page'], $forum_id);
				$forum_id = $forum_id[1];

				if ($auth->acl_get('f_list', $forum_id))
				{
					$location = '';
					switch ($on_page[1])
					{
						case 'posting':
							preg_match('#mode=([a-z]+)#', $row['session_page'], $on_page);
							
							switch ($on_page[1])
							{
								case 'reply':
								case 'topicreview':
									$location = sprintf($user->lang['REPLYING_MESSAGE'], $forum_data[$forum_id]);
									break;
								default:
									$location = sprintf($user->lang['POSTING_MESSAGE'], $forum_data[$forum_id]);
									break;
							}
							break;

						case 'viewtopic':
							$location = sprintf($user->lang['READING_TOPIC'], $forum_data[$forum_id]);
							break;

						case 'viewforum':
							$location .= sprintf($user->lang['READING_FORUM'], $forum_data[$forum_id]);
							break;
					}

					$location_url = "viewforum.$phpEx$SID&amp;f=$forum_id";
				}
				else
				{
					$location = $user->lang['INDEX'];
					$location_url = "index.$phpEx$SID";
				}
				break;

			case 'search':
				$location = $user->lang['SEARCHING_FORUMS'];
				$location_url = "search.$phpEx$SID";
				break;

			case 'faq':
				$location = $user->lang['VIEWING_FAQ'];
				$location_url = "faq.$phpEx$SID";
				break;

			case 'viewonline':
				$location = $user->lang['VIEWING_ONLINE'];
				$location_url = "viewonline.$phpEx$SID";
				break;

			case 'memberslist':
				$location = $user->lang['VIEWING_MEMBERS'];
				$location_url = "memberlist.$phpEx$SID";
				break;

			default:
				$location = $user->lang['INDEX'];
				$location_url = "index.$phpEx$SID";
				break;
		}

		$template->assign_block_vars($which_row, array(
			'USERNAME' 		=> $username,
			'LASTUPDATE' 	=> $user->format_date($row['session_time']),
			'FORUM_LOCATION'=> $location, 
			'USER_IP'		=> ($auth->acl_get('a_')) ? $row['session_ip'] : $user->lang['HIDDEN'], 

			'S_ROW_COUNT'	=> $$which_counter,

			'U_USER_PROFILE'	=> "memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=$user_id",
			'U_FORUM_LOCATION'	=> $location_url)
		);

		$$which_counter++;
	}
}
$db->sql_freeresult($result);


// Generate reg/hidden/guest online text
$vars_online = array(
	'REG'	=> array('logged_visible_online', 'l_r_user_s'),
	'HIDDEN'=> array('logged_hidden_online', 'l_h_user_s'),
	'GUEST'	=> array('guests_online', 'l_g_user_s')
);

foreach ($vars_online as $l_prefix => $var_ary)
{
	switch ($$var_ary[0])
	{
		case 0:
			$$var_ary[1] = $user->lang[$l_prefix . '_USERS_ZERO_ONLINE'];
			break;

		case 1:
			$$var_ary[1] = $user->lang[$l_prefix . '_USER_ONLINE'];
			break;

		default:
			$$var_ary[1] = $user->lang[$l_prefix . '_USERS_ONLINE'];
			break;
	}
}
unset($vars_online);


// Grab group details for legend display
$sql = "SELECT group_name, group_colour, group_type  
	FROM " . GROUPS_TABLE . " 
	WHERE group_colour <> '' 
		AND group_display = 1";
$result = $db->sql_query($sql);

$legend = '';
while ($row = $db->sql_fetchrow($result))
{
	$legend .= (($legend != '') ? ', ' : '') . '<span style="color:#' . $row['group_colour'] . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</span>';
}
$db->sql_freeresult($result);


// Send data to template
$template->assign_vars(array(
	'TOTAL_REGISTERED_USERS_ONLINE'	=> sprintf($l_r_user_s, $logged_visible_online) . sprintf($l_h_user_s, $logged_hidden_online),
	'TOTAL_GUEST_USERS_ONLINE'		=> sprintf($l_g_user_s, $guests_online),
	'LEGEND'	=> $legend, 
	'META'		=> '<meta http-equiv="refresh" content="60; url=viewonline.' . $phpEx . $SID . '">',

	'U_SORT_USERNAME'	=> "viewonline.$phpEx$SID&amp;sk=a&amp;sd=" . (($sort_key == 'a' && $sort_dir == 'a') ? 'd' : 'a'), 
	'U_SORT_UPDATED'	=> "viewonline.$phpEx$SID&amp;sk=b&amp;sd=" . (($sort_key == 'b' && $sort_dir == 'a') ? 'd' : 'a'), 
	'U_SORT_LOCATION'	=> "viewonline.$phpEx$SID&amp;sk=c&amp;sd=" . (($sort_key == 'c' && $sort_dir == 'a') ? 'd' : 'a'))
);


// Output the page
page_header($user->lang['WHO_IS_ONLINE']);

$template->set_filenames(array(
	'body' => 'viewonline_body.html')
);
make_jumpbox('viewforum.'.$phpEx);

page_footer();

?>