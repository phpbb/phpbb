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
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('memberlist');

// Get and set some variables
$mode		= $request->variable('mode', '');
$session_id	= $request->variable('s', '');
$start		= $request->variable('start', 0);
$sort_key	= $request->variable('sk', 'b');
$sort_dir	= $request->variable('sd', 'd');
$show_guests	= ($config['load_online_guests']) ? $request->variable('sg', 0) : 0;

// Can this user view profiles/memberlist?
if (!$auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel'))
{
	if ($user->data['user_id'] != ANONYMOUS)
	{
		send_status_line(403, 'Forbidden');
		trigger_error('NO_VIEW_USERS');
	}

	login_box('', $user->lang['LOGIN_EXPLAIN_VIEWONLINE']);
}

/* @var $pagination \phpbb\pagination */
$pagination = $phpbb_container->get('pagination');

/* @var $viewonline_helper \phpbb\viewonline_helper */
$viewonline_helper = $phpbb_container->get('viewonline_helper');

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
	if (!function_exists('user_get_id_name'))
	{
		include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
	}

	$sql = 'SELECT u.user_id, u.username, u.user_type, s.session_ip
		FROM ' . USERS_TABLE . ' u, ' . SESSIONS_TABLE . " s
		WHERE s.session_id = '" . $db->sql_escape($session_id) . "'
			AND	u.user_id = s.session_user_id";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$template->assign_var('WHOIS', user_ipwhois($row['session_ip']));
	}
	$db->sql_freeresult($result);

	// Output the page
	page_header($user->lang['WHO_IS_ONLINE']);

	$template->set_filenames(array(
		'body' => 'viewonline_whois.html')
	);
	make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));

	page_footer();
}

$user->update_session_infos();

// Forum info
$sql_ary = array(
	'SELECT'	=> 'f.forum_id, f.forum_name, f.parent_id, f.forum_type, f.left_id, f.right_id',
	'FROM'		=> array(
		FORUMS_TABLE	=> 'f',
	),
	'ORDER_BY'	=> 'f.left_id ASC',
);

/**
* Modify the forum data SQL query for getting additional fields if needed
*
* @event core.viewonline_modify_forum_data_sql
* @var	array	sql_ary			The SQL array
* @since 3.1.5-RC1
*/
$vars = array('sql_ary');
extract($phpbb_dispatcher->trigger_event('core.viewonline_modify_forum_data_sql', compact($vars)));

$result = $db->sql_query($db->sql_build_query('SELECT', $sql_ary), 600);
unset($sql_ary);

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
	switch ($db->get_sql_layer())
	{
		case 'sqlite3':
			$sql = 'SELECT COUNT(session_ip) as num_guests
				FROM (
					SELECT DISTINCT session_ip
						FROM ' . SESSIONS_TABLE . '
						WHERE session_user_id = ' . ANONYMOUS . '
							AND session_time >= ' . (time() - ($config['load_online_time'] * 60)) .
				')';
		break;

		default:
			$sql = 'SELECT COUNT(DISTINCT session_ip) as num_guests
				FROM ' . SESSIONS_TABLE . '
				WHERE session_user_id = ' . ANONYMOUS . '
					AND session_time >= ' . (time() - ($config['load_online_time'] * 60));
		break;
	}
	$result = $db->sql_query($sql);
	$guest_counter = (int) $db->sql_fetchfield('num_guests');
	$db->sql_freeresult($result);
}

// Get user list
$sql_ary = array(
	'SELECT'	=> 'u.user_id, u.username, u.username_clean, u.user_type, u.user_colour, s.session_id, s.session_time, s.session_page, s.session_ip, s.session_browser, s.session_viewonline, s.session_forum_id',
	'FROM'		=> array(
		USERS_TABLE		=> 'u',
		SESSIONS_TABLE	=> 's',
	),
	'WHERE'		=> 'u.user_id = s.session_user_id
		AND s.session_time >= ' . (time() - ($config['load_online_time'] * 60)) .
		((!$show_guests) ? ' AND s.session_user_id <> ' . ANONYMOUS : ''),
	'ORDER_BY'	=> $order_by,
);

/**
* Modify the SQL query for getting the user data to display viewonline list
*
* @event core.viewonline_modify_sql
* @var	array	sql_ary			The SQL array
* @var	bool	show_guests		Do we display guests in the list
* @var	int		guest_counter	Number of guests displayed
* @var	array	forum_data		Array with forum data
* @since 3.1.0-a1
* @changed 3.1.0-a2 Added vars guest_counter and forum_data
*/
$vars = array('sql_ary', 'show_guests', 'guest_counter', 'forum_data');
extract($phpbb_dispatcher->trigger_event('core.viewonline_modify_sql', compact($vars)));

$result = $db->sql_query($db->sql_build_query('SELECT', $sql_ary));
$session_data_rowset = $db->sql_fetchrowset($result);
$db->sql_freeresult($result);

$prev_id = $prev_ip = $user_list = array();
$logged_visible_online = $logged_hidden_online = $counter = 0;

/** @var \phpbb\controller\helper $controller_helper */
$controller_helper = $phpbb_container->get('controller.helper');

/** @var \phpbb\group\helper $group_helper */
$group_helper = $phpbb_container->get('group_helper');

// Get forum IDs for session pages which have only 't' parameter
$viewonline_helper->get_forum_ids($session_data_rowset);

foreach ($session_data_rowset as $row)
{
	if ($row['user_id'] != ANONYMOUS && !isset($prev_id[$row['user_id']]))
	{
		$view_online = $s_user_hidden = false;
		$user_colour = ($row['user_colour']) ? ' style="color:#' . $row['user_colour'] . '" class="username-coloured"' : '';

		$username_full = ($row['user_type'] != USER_IGNORE) ? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) : '<span' . $user_colour . '>' . $row['username'] . '</span>';

		if (!$row['session_viewonline'])
		{
			$view_online = ($auth->acl_get('u_viewonline') || $row['user_id'] === $user->data['user_id']) ? true : false;
			$logged_hidden_online++;

			$username_full = '<em>' . $username_full . '</em>';
			$s_user_hidden = true;
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

		$s_user_hidden = false;
		$username_full = get_username_string('full', $row['user_id'], $user->lang['GUEST']);
	}
	else
	{
		continue;
	}

	$on_page = $viewonline_helper->get_user_page($row['session_page']);

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
			$forum_id = $row['session_forum_id'];

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
						preg_match('#mode=([a-z]+)#', $row['session_page'], $on_page);
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

		case 'viewonline':
			$location = $user->lang['VIEWING_ONLINE'];
			$location_url = append_sid("{$phpbb_root_path}viewonline.$phpEx");
		break;

		case 'memberlist':
			$location_url = append_sid("{$phpbb_root_path}memberlist.$phpEx");

			if (strpos($row['session_page'], 'mode=viewprofile') !== false)
			{
				$location = $user->lang['VIEWING_MEMBER_PROFILE'];
			}
			else if (strpos($row['session_page'], 'mode=contactadmin') !== false)
			{
				$location = $user->lang['VIEWING_CONTACT_ADMIN'];
				$location_url = append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=contactadmin');
			}
			else
			{
				$location = $user->lang['VIEWING_MEMBERS'];
			}
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
				if (strpos($row['session_page'], $param) !== false)
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

			if ($row['session_page'] === 'app.' . $phpEx . '/help/faq' ||
				$row['session_page'] === 'app.' . $phpEx . '/help/bbcode')
			{
				$location = $user->lang['VIEWING_FAQ'];
				$location_url = $controller_helper->route('phpbb_help_faq_controller');
			}
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
	* @var	array	forum_data		Array with forum data
	* @since 3.1.0-a1
	* @changed 3.1.0-a2 Added var forum_data
	*/
	$vars = array('on_page', 'row', 'location', 'location_url', 'forum_data');
	extract($phpbb_dispatcher->trigger_event('core.viewonline_overwrite_location', compact($vars)));

	$template_row = array(
		'USERNAME' 			=> $row['username'],
		'USERNAME_COLOUR'	=> $row['user_colour'],
		'USERNAME_FULL'		=> $username_full,
		'LASTUPDATE'		=> $user->format_date($row['session_time']),
		'FORUM_LOCATION'	=> $location,
		'USER_IP'			=> ($auth->acl_get('a_')) ? (($mode == 'lookup' && $session_id == $row['session_id']) ? gethostbyaddr($row['session_ip']) : $row['session_ip']) : '',
		'USER_BROWSER'		=> ($auth->acl_get('a_user')) ? $row['session_browser'] : '',

		'U_USER_PROFILE'	=> ($row['user_type'] != USER_IGNORE) ? get_username_string('profile', $row['user_id'], '') : '',
		'U_USER_IP'			=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'mode=lookup' . (($mode != 'lookup' || $row['session_id'] != $session_id) ? '&amp;s=' . $row['session_id'] : '') . "&amp;sg=$show_guests&amp;start=$start&amp;sk=$sort_key&amp;sd=$sort_dir"),
		'U_WHOIS'			=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'mode=whois&amp;s=' . $row['session_id']),
		'U_FORUM_LOCATION'	=> $location_url,

		'S_USER_HIDDEN'		=> $s_user_hidden,
		'S_GUEST'			=> ($row['user_id'] == ANONYMOUS) ? true : false,
		'S_USER_TYPE'		=> $row['user_type'],
	);

	/**
	* Modify viewonline template data before it is displayed in the list
	*
	* @event core.viewonline_modify_user_row
	* @var	array	on_page			File name and query string
	* @var	array	row				Array with the users sql row
	* @var	array	forum_data		Array with forum data
	* @var	array	template_row	Array with template variables for the user row
	* @since 3.1.0-RC4
	*/
	$vars = array('on_page', 'row', 'forum_data', 'template_row');
	extract($phpbb_dispatcher->trigger_event('core.viewonline_modify_user_row', compact($vars)));

	$template->assign_block_vars('user_row', $template_row);
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

$legend = [];
while ($row = $db->sql_fetchrow($result))
{
	$colour_text = ($row['group_colour']) ? ' style="color:#' . $row['group_colour'] . '"' : '';
	$group_name = $group_helper->get_name($row['group_name']);

	if ($row['group_name'] == 'BOTS')
	{
		$legend[] = '<span' . $colour_text . '>' . $group_name . '</span>';
	}
	else
	{
		$legend[] = '<a' . $colour_text . ' href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=group&amp;g=' . $row['group_id']) . '">' . $group_name . '</a>';
	}
}
$db->sql_freeresult($result);

$legend = implode($user->lang['COMMA_SEPARATOR'], $legend);

// Refreshing the page every 60 seconds...
meta_refresh(60, append_sid("{$phpbb_root_path}viewonline.$phpEx", "sg=$show_guests&amp;sk=$sort_key&amp;sd=$sort_dir&amp;start=$start"));

$start = $pagination->validate_start($start, $config['topics_per_page'], $counter);
$base_url = append_sid("{$phpbb_root_path}viewonline.$phpEx", "sg=$show_guests&amp;sk=$sort_key&amp;sd=$sort_dir");
$pagination->generate_template_pagination($base_url, 'pagination', 'start', $counter, $config['topics_per_page'], $start);

$template->assign_block_vars('navlinks', array(
	'BREADCRUMB_NAME'	=> $user->lang('WHO_IS_ONLINE'),
	'U_BREADCRUMB'		=> append_sid("{$phpbb_root_path}viewonline.$phpEx"),
));

// Send data to template
$template->assign_vars(array(
	'TOTAL_REGISTERED_USERS_ONLINE'	=> $user->lang('REG_USERS_ONLINE', (int) $logged_visible_online, $user->lang('HIDDEN_USERS_ONLINE', (int) $logged_hidden_online)),
	'TOTAL_GUEST_USERS_ONLINE'		=> $user->lang('GUEST_USERS_ONLINE', (int) $guest_counter),
	'LEGEND'						=> $legend,

	'U_SORT_USERNAME'		=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'sk=a&amp;sd=' . (($sort_key == 'a' && $sort_dir == 'a') ? 'd' : 'a') . '&amp;sg=' . ((int) $show_guests)),
	'U_SORT_UPDATED'		=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'sk=b&amp;sd=' . (($sort_key == 'b' && $sort_dir == 'a') ? 'd' : 'a') . '&amp;sg=' . ((int) $show_guests)),
	'U_SORT_LOCATION'		=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'sk=c&amp;sd=' . (($sort_key == 'c' && $sort_dir == 'a') ? 'd' : 'a') . '&amp;sg=' . ((int) $show_guests)),

	'U_SWITCH_GUEST_DISPLAY'	=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'sg=' . ((int) !$show_guests)),
	'L_SWITCH_GUEST_DISPLAY'	=> ($show_guests) ? $user->lang['HIDE_GUESTS'] : $user->lang['DISPLAY_GUESTS'],
	'S_SWITCH_GUEST_DISPLAY'	=> ($config['load_online_guests']) ? true : false,
	'S_VIEWONLINE'				=> true,
));

// We do not need to load the who is online box here. ;)
$config['load_online'] = false;

// Output the page
page_header($user->lang['WHO_IS_ONLINE']);

$template->set_filenames(array(
	'body' => 'viewonline_body.html')
);
make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));

page_footer();
