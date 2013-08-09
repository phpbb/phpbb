<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('memberlist');

// Get and set some variables
$mode		= request_var('mode', '');
$session_id	= request_var('s', '');
$start		= request_var('start', 0);
$sort_key	= request_var('sk', 'b');
$sort_dir	= request_var('sd', 'd');
$show_guests= ($config['load_online_guests']) ? request_var('sg', 0) : 0;

// Can this user view profiles/memberlist?
if (!$auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel'))
{
	if ($user->data['user_id'] != ANONYMOUS)
	{
		trigger_error('NO_VIEW_USERS');
	}

	login_box('', $user->lang['LOGIN_EXPLAIN_VIEWONLINE']);
}

$sort_key_text = array('a' => $user->lang['SORT_USERNAME'], 'b' => $user->lang['SORT_JOINED'], 'c' => $user->lang['SORT_LOCATION']);
$sort_key_sql = array('a' => 'u.username_clean', 'b' => 's.session_time', 'c' => 's.session_page');

// Sorting and order
if (!isset($sort_key_text[$sort_key]))
{
	$sort_key = 'b';
}

$order_by = $sort_key_sql[$sort_key] . ' ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');

// Whois requested
if ($mode == 'whois' && $auth->acl_get('a_') && $session_id)
{
	include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

	$user_ip = $user->get_user_ip_from_session($session_id);

	if ($user_ip != null)
	{
		$template->assign_var('WHOIS', user_ipwhois($user_ip));
	}
	// Output the page
	page_header($user->lang['WHO_IS_ONLINE']);

	$template->set_filenames(array(
		'body' => 'viewonline_whois.html')
	);
	make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));

	page_footer();
}

// Forum info
$sql = 'SELECT forum_id, forum_name, parent_id, forum_type, left_id, right_id
	FROM ' . FORUMS_TABLE . '
	ORDER BY left_id ASC';
$result = $db->sql_query($sql, 600);

$forum_data = array();
while ($row = $db->sql_fetchrow($result))
{
	$forum_data[$row['forum_id']] = $row;
}
$db->sql_freeresult($result);

$guest_counter = 0;

// Get number of online guests (if we do not display them)
if (!$show_guests)
{
	$guest_counter = $user->obtain_guest_count();
}

// Get user list
$users = $user->get_users_online(
	$show_guests,
	time() - ($config['load_online_time'] * 60),
	$order_by,
	$phpbb_dispatcher
);

$prev_id = $prev_ip = $user_list = array();
$logged_visible_online = $logged_hidden_online = $counter = 0;

foreach ($users as $current_user)
{
	if ($current_user['user_id'] != ANONYMOUS && !isset($prev_id[$current_user['user_id']]))
	{
		$view_online = $s_user_hidden = false;
		$user_colour = ($current_user['user_colour']) ? ' style="color:#' . $current_user['user_colour'] . '" class="username-coloured"' : '';

		$username_full = ($current_user['user_type'] != USER_IGNORE) ? get_username_string('full', $current_user['user_id'], $current_user['username'], $current_user['user_colour']) : '<span' . $user_colour . '>' . $current_user['username'] . '</span>';

		if (!$current_user['session_viewonline'])
		{
			$view_online = ($auth->acl_get('u_viewonline')) ? true : false;
			$logged_hidden_online++;

			$username_full = '<em>' . $username_full . '</em>';
			$s_user_hidden = true;
		}
		else
		{
			$view_online = true;
			$logged_visible_online++;
		}

		$prev_id[$current_user['user_id']] = 1;

		if ($view_online)
		{
			$counter++;
		}

		if (!$view_online || $counter > $start + $config['topics_per_page'] || $counter <= $start)
		{
			continue;
		}
	}
	else if ($show_guests && $current_user['user_id'] == ANONYMOUS && !isset($prev_ip[$current_user['session_ip']]))
	{
		$prev_ip[$current_user['session_ip']] = 1;
		$guest_counter++;
		$counter++;

		if ($counter > $start + $config['topics_per_page'] || $counter <= $start)
		{
			continue;
		}

		$s_user_hidden = false;
		$username_full = get_username_string('full', $current_user['user_id'], $user->lang['GUEST']);
	}
	else
	{
		continue;
	}

	preg_match('#^([a-z0-9/_-]+)#i', $current_user['session_page'], $on_page);
	if (!sizeof($on_page))
	{
		$on_page[1] = '';
	}

	switch ($on_page[1])
	{
		case 'index':
			$location = $user->lang['INDEX'];
			$location_url = append_sid("{$phpbb_root_path}index.$phpEx");
		break;

		case $phpbb_adm_relative_path . 'index':
			$location = $user->lang['ACP'];
			$location_url = append_sid("{$phpbb_root_path}index.$phpEx");
		break;

		case 'posting':
		case 'viewforum':
		case 'viewtopic':
			$forum_id = $current_user['session_forum_id'];

			if ($forum_id && $auth->acl_get('f_list', $forum_id))
			{
				$location = '';
				$location_url = append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id);

				if ($forum_data[$forum_id]['forum_type'] == FORUM_LINK)
				{
					$location = sprintf($user->lang['READING_LINK'], $forum_data[$forum_id]['forum_name']);
					break;
				}

				switch ($on_page[1])
				{
					case 'posting':
						preg_match('#mode=([a-z]+)#', $current_user['session_page'], $on_page);
						$posting_mode = (!empty($on_page[1])) ? $on_page[1] : '';

						switch ($posting_mode)
						{
							case 'reply':
							case 'quote':
								$location = sprintf($user->lang['REPLYING_MESSAGE'], $forum_data[$forum_id]['forum_name']);
							break;

							default:
								$location = sprintf($user->lang['POSTING_MESSAGE'], $forum_data[$forum_id]['forum_name']);
							break;
						}
					break;

					case 'viewtopic':
						$location = sprintf($user->lang['READING_TOPIC'], $forum_data[$forum_id]['forum_name']);
					break;

					case 'viewforum':
						$location = sprintf($user->lang['READING_FORUM'], $forum_data[$forum_id]['forum_name']);
					break;
				}
			}
			else
			{
				$location = $user->lang['INDEX'];
				$location_url = append_sid("{$phpbb_root_path}index.$phpEx");
			}
		break;

		case 'search':
			$location = $user->lang['SEARCHING_FORUMS'];
			$location_url = append_sid("{$phpbb_root_path}search.$phpEx");
		break;

		case 'faq':
			$location = $user->lang['VIEWING_FAQ'];
			$location_url = append_sid("{$phpbb_root_path}faq.$phpEx");
		break;

		case 'viewonline':
			$location = $user->lang['VIEWING_ONLINE'];
			$location_url = append_sid("{$phpbb_root_path}viewonline.$phpEx");
		break;

		case 'memberlist':
			$location = (strpos($current_user['session_page'], 'mode=viewprofile') !== false) ? $user->lang['VIEWING_MEMBER_PROFILE'] : $user->lang['VIEWING_MEMBERS'];
			$location_url = append_sid("{$phpbb_root_path}memberlist.$phpEx");
		break;

		case 'mcp':
			$location = $user->lang['VIEWING_MCP'];
			$location_url = append_sid("{$phpbb_root_path}index.$phpEx");
		break;

		case 'ucp':
			$location = $user->lang['VIEWING_UCP'];

			// Grab some common modules
			$url_params = array(
				'mode=register'		=> 'VIEWING_REGISTER',
				'i=pm&mode=compose'	=> 'POSTING_PRIVATE_MESSAGE',
				'i=pm&'				=> 'VIEWING_PRIVATE_MESSAGES',
				'i=profile&'		=> 'CHANGING_PROFILE',
				'i=prefs&'			=> 'CHANGING_PREFERENCES',
			);

			foreach ($url_params as $param => $lang)
			{
				if (strpos($current_user['session_page'], $param) !== false)
				{
					$location = $user->lang[$lang];
					break;
				}
			}

			$location_url = append_sid("{$phpbb_root_path}index.$phpEx");
		break;

		case 'download/file':
			$location = $user->lang['DOWNLOADING_FILE'];
			$location_url = append_sid("{$phpbb_root_path}index.$phpEx");
		break;

		case 'report':
			$location = $user->lang['REPORTING_POST'];
			$location_url = append_sid("{$phpbb_root_path}index.$phpEx");
		break;

		default:
			$location = $user->lang['INDEX'];
			$location_url = append_sid("{$phpbb_root_path}index.$phpEx");
		break;
	}

	/**
	* Overwrite the location's name and URL, which are displayed in the list
	*
	* @event core.viewonline_overwrite_location
	* @var	array	on_page			File name and query string
	* @var	array	row				Array with the users sql row
	* @var	string	location		Page name to displayed in the list
	* @var	string	location_url	Page url to displayed in the list
	* @since 3.1-A1
	*/
	$vars = array('on_page', 'row', 'location', 'location_url');
	extract($phpbb_dispatcher->trigger_event('core.viewonline_overwrite_location', compact($vars)));

	$template->assign_block_vars('user_row', array(
		'USERNAME' 			=> $current_user['username'],
		'USERNAME_COLOUR'	=> $current_user['user_colour'],
		'USERNAME_FULL'		=> $username_full,
		'LASTUPDATE'		=> $user->format_date($current_user['session_time']),
		'FORUM_LOCATION'	=> $location,
		'USER_IP'			=> ($auth->acl_get('a_')) ? (($mode == 'lookup' && $session_id == $current_user['session_id']) ? gethostbyaddr($current_user['session_ip']) : $current_user['session_ip']) : '',
		'USER_BROWSER'		=> ($auth->acl_get('a_user')) ? $current_user['session_browser'] : '',

		'U_USER_PROFILE'	=> ($current_user['user_type'] != USER_IGNORE) ? get_username_string('profile', $current_user['user_id'], '') : '',
		'U_USER_IP'			=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'mode=lookup' . (($mode != 'lookup' || $current_user['session_id'] != $session_id) ? '&amp;s=' . $current_user['session_id'] : '') . "&amp;sg=$show_guests&amp;start=$start&amp;sk=$sort_key&amp;sd=$sort_dir"),
		'U_WHOIS'			=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'mode=whois&amp;s=' . $current_user['session_id']),
		'U_FORUM_LOCATION'	=> $location_url,

		'S_USER_HIDDEN'		=> $s_user_hidden,
		'S_GUEST'			=> ($current_user['user_id'] == ANONYMOUS) ? true : false,
		'S_USER_TYPE'		=> $current_user['user_type'],
	));
}
unset($prev_id, $prev_ip);

$order_legend = ($config['legend_sort_groupname']) ? 'group_name' : 'group_legend';
// Grab group details for legend display
if ($auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel'))
{
	$sql = 'SELECT group_id, group_name, group_colour, group_type, group_legend
		FROM ' . GROUPS_TABLE . '
		WHERE group_legend > 0
		ORDER BY ' . $order_legend . ' ASC';
}
else
{
	$sql = 'SELECT g.group_id, g.group_name, g.group_colour, g.group_type, g.group_legend
		FROM ' . GROUPS_TABLE . ' g
		LEFT JOIN ' . USER_GROUP_TABLE . ' ug
			ON (
				g.group_id = ug.group_id
				AND ug.user_id = ' . $user->data['user_id'] . '
				AND ug.user_pending = 0
			)
		WHERE g.group_legend > 0
			AND (g.group_type <> ' . GROUP_HIDDEN . ' OR ug.user_id = ' . $user->data['user_id'] . ')
		ORDER BY g.' . $order_legend . ' ASC';
}
$result = $db->sql_query($sql);

$legend = '';
while ($row = $db->sql_fetchrow($result))
{
	if ($row['group_name'] == 'BOTS')
	{
		$legend .= (($legend != '') ? ', ' : '') . '<span style="color:#' . $row['group_colour'] . '">' . $user->lang['G_BOTS'] . '</span>';
	}
	else
	{
		$legend .= (($legend != '') ? ', ' : '') . '<a style="color:#' . $row['group_colour'] . '" href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=group&amp;g=' . $row['group_id']) . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</a>';
	}
}
$db->sql_freeresult($result);

// Refreshing the page every 60 seconds...
meta_refresh(60, append_sid("{$phpbb_root_path}viewonline.$phpEx", "sg=$show_guests&amp;sk=$sort_key&amp;sd=$sort_dir&amp;start=$start"));

$base_url = append_sid("{$phpbb_root_path}viewonline.$phpEx", "sg=$show_guests&amp;sk=$sort_key&amp;sd=$sort_dir");
phpbb_generate_template_pagination($template, $base_url, 'pagination', 'start', $counter, $config['topics_per_page'], $start);

// Send data to template
$template->assign_vars(array(
	'TOTAL_REGISTERED_USERS_ONLINE'	=> $user->lang('REG_USERS_ONLINE', (int) $logged_visible_online, $user->lang('HIDDEN_USERS_ONLINE', (int) $logged_hidden_online)),
	'TOTAL_GUEST_USERS_ONLINE'		=> $user->lang('GUEST_USERS_ONLINE', (int) $guest_counter),
	'LEGEND'						=> $legend,
	'PAGE_NUMBER'					=> phpbb_on_page($template, $user, $base_url, $counter, $config['topics_per_page'], $start),

	'U_SORT_USERNAME'		=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'sk=a&amp;sd=' . (($sort_key == 'a' && $sort_dir == 'a') ? 'd' : 'a') . '&amp;sg=' . ((int) $show_guests)),
	'U_SORT_UPDATED'		=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'sk=b&amp;sd=' . (($sort_key == 'b' && $sort_dir == 'a') ? 'd' : 'a') . '&amp;sg=' . ((int) $show_guests)),
	'U_SORT_LOCATION'		=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'sk=c&amp;sd=' . (($sort_key == 'c' && $sort_dir == 'a') ? 'd' : 'a') . '&amp;sg=' . ((int) $show_guests)),

	'U_SWITCH_GUEST_DISPLAY'	=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'sg=' . ((int) !$show_guests)),
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
make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));

page_footer();
