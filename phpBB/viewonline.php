<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : viewonline.php
// STARTED   : Sat Dec 16, 2000
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ]
//
// -------------------------------------------------------------

define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);

// Start session management
$user->start();
$auth->acl($user->data);
$user->setup();

// Get and set some variables
$mode		= request_var('mode', '');
$session_id	= request_var('s', '');
$start		= request_var('start', 0);
$sort_key	= request_var('sk', 'b');
$sort_dir	= request_var('sd', 'd');
$show_guests= ($config['load_online_guests']) ? request_var('sg', 0) : 0;

$sort_key_text = array('a' => $user->lang['SORT_USERNAME'], 'b' => $user->lang['SORT_LOCATION'], 'c' => $user->lang['SORT_JOINED']);
$sort_key_sql = array('a' => 'u.username', 'b' => 's.session_time', 'c' => 's.session_page');

// Sorting and order
$order_by = $sort_key_sql[$sort_key] . ' ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');

// Whois requested
if ($mode == 'whois')
{
	include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

	$sql = 'SELECT u.user_id, u.username, u.user_type, s.session_ip
		FROM ' . USERS_TABLE . ' u, ' . SESSIONS_TABLE . " s
		WHERE s.session_id = '" . $db->sql_escape($session_id) . "'
			AND	u.user_id = s.session_user_id";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$whois = user_ipwhois($row['session_ip']);

		$whois = preg_replace('#(\s+?)([\w\-\._\+]+?@[\w\-\.]+?)(\s+?)#s', '\1<a href="mailto:\2">\2</a>\3', $whois);
		$whois = preg_replace('#(\s+?)(http://.*?)(\s+?)#s', '\1<a href="\2" target="_blank">\2</a>\3', $whois);

		$template->assign_vars(array(
			'WHOIS'	=> trim($whois))
		);
	}
	$db->sql_freeresult($result);

	// Output the page
	page_header($user->lang['WHO_IS_ONLINE']);

	$template->set_filenames(array(
		'body' => 'viewonline_whois.html')
	);
	make_jumpbox('viewforum.'.$phpEx);

	page_footer();
}

// Forum info
$sql = 'SELECT forum_id, forum_name, parent_id, forum_type, left_id, right_id
	FROM ' . FORUMS_TABLE . '
	ORDER BY left_id ASC';
$result = $db->sql_query($sql, 600);

while ($row = $db->sql_fetchrow($result))
{
	$forum_data[$row['forum_id']] = $row['forum_name'];
}
$db->sql_freeresult($result);

$guest_counter = 0;

// Get number of online guests (if we do not display them)
if (!$show_guests)
{
	$sql = 'SELECT COUNT(DISTINCT session_ip) as num_guests FROM ' . SESSIONS_TABLE . '
		WHERE session_user_id = ' . ANONYMOUS . '
			AND session_time >= ' . (time() - ($config['load_online_time'] * 60));
	$result = $db->sql_query($sql);
	$guest_counter = (int) $db->sql_fetchfield('num_guests', 0, $result);
	$db->sql_freeresult($result);
}

// Get user list
$sql = 'SELECT u.user_id, u.username, u.user_type, u.user_allow_viewonline, u.user_colour, s.session_id, s.session_time, s.session_page, s.session_ip, s.session_viewonline
	FROM ' . USERS_TABLE . ' u, ' . SESSIONS_TABLE . ' s
	WHERE u.user_id = s.session_user_id
		AND s.session_time >= ' . (time() - ($config['load_online_time'] * 60)) . 
		((!$show_guests) ? ' AND s.session_user_id <> ' . ANONYMOUS : '') . '
	ORDER BY ' . $order_by;
$result = $db->sql_query($sql);

$prev_id = $prev_ip = $user_list = array();
$logged_visible_online = $logged_hidden_online = $counter = 0;

while ($row = $db->sql_fetchrow($result))
{
	if ($row['user_id'] != ANONYMOUS && !isset($prev_id[$row['user_id']]))
	{
		$view_online = false;
	
		if ($row['user_colour'])
		{
			$row['username'] = '<b style="color:#' . $row['user_colour'] . '">' . $row['username'] . '</b>';
		}

		if (!$row['user_allow_viewonline'] || !$row['session_viewonline'])
		{
			$view_online = ($auth->acl_get('u_viewonline')) ? true : false;
			$logged_hidden_online++;

			$row['username'] = '<i>' . $row['username'] . '</i>';
		}
		else
		{
			$view_online = true;
			$logged_visible_online++;
		}

		$prev_id[$row['user_id']] = 1;

		if ($view_online)
		{
			$counter++;
		}

		if (!$view_online || $counter > $start + $config['topics_per_page'] || $counter <= $start)
		{
			continue;
		}
	}
	else if ($show_guests && $row['user_id'] == ANONYMOUS && !isset($prev_ip[$row['session_ip']]))
	{
		$prev_ip[$row['session_ip']] = 1;
		$guest_counter++;
		$counter++;

		if ($counter > $start + $config['topics_per_page'] || $counter <= $start)
		{
			continue;
		}

		$row['username'] = $user->lang['GUEST'];
	}
	else
	{
		continue;
	}

	preg_match('#^([a-z]+)#i', $row['session_page'], $on_page);
	if (!sizeof($on_page))
	{
		$on_page[1] = '';
	}

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
			$forum_id = (sizeof($forum_id)) ? $forum_id[1] : 0;

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
						$location = sprintf($user->lang['READING_FORUM'], $forum_data[$forum_id]);
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

		case 'memberlist':
			$location = $user->lang['VIEWING_MEMBERS'];
			$location_url = "memberlist.$phpEx$SID";
			break;

		case 'ucp':
			$location = $user->lang['VIEWING_UCP'];
			$location_url = '';

		default:
			$location = $user->lang['INDEX'];
			$location_url = "index.$phpEx$SID";
			break;
	}

	$template->assign_block_vars('user_row', array(
		'USERNAME' 		=> $row['username'],
		'LASTUPDATE' 	=> $user->format_date($row['session_time']),
		'FORUM_LOCATION'=> $location,
		'USER_IP'		=> ($auth->acl_get('a_')) ? (($mode == 'lookup' && $session_id == $row['session_id']) ? gethostbyaddr($row['session_ip']) : $row['session_ip']) : '',

		'U_USER_PROFILE'	=> ($row['user_type'] != USER_IGNORE && $row['user_id'] != ANONYMOUS) ? "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['user_id'] : '',
		'U_USER_IP'			=> "{$phpbb_root_path}viewonline.$phpEx$SID" . (($mode != 'lookup' || $row['session_id'] != $session_id) ? '&amp;s=' . $row['session_id'] : '') . "&amp;mode=lookup&amp;sg=$show_guests&amp;start=$start&amp;sk=$sort_key&amp;sd=$sort_dir",
		'U_WHOIS'			=> "{$phpbb_root_path}viewonline.$phpEx$SID&amp;mode=whois&amp;s=" . $row['session_id'],
		'U_FORUM_LOCATION'	=> $phpbb_root_path . $location_url,
		
		'S_GUEST'			=> ($row['user_id'] == ANONYMOUS) ? true : false,
		'S_USER_TYPE'		=> $row['user_type'])
	);
}
$db->sql_freeresult($result);
unset($prev_id, $prev_ip);

// Generate reg/hidden/guest online text
$vars_online = array(
	'REG'	=> array('logged_visible_online', 'l_r_user_s'),
	'HIDDEN'=> array('logged_hidden_online', 'l_h_user_s'),
	'GUEST'	=> array('guest_counter', 'l_g_user_s')
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

$pagination = generate_pagination("{$phpbb_root_path}viewonline.$phpEx$SID&amp;sg=$show_guests&amp;sk=$sort_key&amp;sd=$sort_dir", $counter, $config['topics_per_page'], $start);

// Grab group details for legend display
$sql = 'SELECT group_id, group_name, group_colour, group_type
	FROM ' . GROUPS_TABLE . '
	WHERE group_legend = 1';
$result = $db->sql_query($sql);

$legend = '';
while ($row = $db->sql_fetchrow($result))
{
	$legend .= (($legend != '') ? ', ' : '') . '<a style="color:#' . $row['group_colour'] . '" href="memberlist.' . $phpEx . $SID . '&amp;mode=group&amp;g=' . $row['group_id'] . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</a>';
}
$db->sql_freeresult($result);

// Send data to template
$template->assign_vars(array(
	'TOTAL_REGISTERED_USERS_ONLINE'	=> sprintf($l_r_user_s, $logged_visible_online) . sprintf($l_h_user_s, $logged_hidden_online),
	'TOTAL_GUEST_USERS_ONLINE'		=> sprintf($l_g_user_s, $guest_counter),
	'LEGEND'		=> $legend,
	'META'			=> '<meta http-equiv="refresh" content="60; url=viewonline.' . $phpEx . $SID . '">',
	'PAGINATION'	=> $pagination,
	'PAGE_NUMBER' 	=> on_page($counter, $config['topics_per_page'], $start),

	'U_SORT_USERNAME'		=> "{$phpbb_root_path}viewonline.$phpEx$SID&amp;sk=a&amp;sd=" . (($sort_key == 'a' && $sort_dir == 'a') ? 'd' : 'a'),
	'U_SORT_UPDATED'		=> "{$phpbb_root_path}viewonline.$phpEx$SID&amp;sk=b&amp;sd=" . (($sort_key == 'b' && $sort_dir == 'a') ? 'd' : 'a'),
	'U_SORT_LOCATION'		=> "{$phpbb_root_path}viewonline.$phpEx$SID&amp;sk=c&amp;sd=" . (($sort_key == 'c' && $sort_dir == 'a') ? 'd' : 'a'),

	'U_SWITCH_GUEST_DISPLAY'	=> "{$phpbb_root_path}viewonline.$phpEx$SID&amp;sg=" . ((int) !$show_guests),
	'L_SWITCH_GUEST_DISPLAY'	=> ($show_guests) ? $user->lang['HIDE_GUESTS'] : $user->lang['DISPLAY_GUESTS'],
	'S_SWITCH_GUEST_DISPLAY'	=> ($config['load_online_guests']) ? true : false)
);

// We do not need to load the who is online box here. ;)
$config['load_online'] = false;

// Output the page
page_header($user->lang['WHO_IS_ONLINE']);

$template->set_filenames(array(
	'body' => 'viewonline_body.html')
);
make_jumpbox('viewforum.'.$phpEx);

page_footer();

?>