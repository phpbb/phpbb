<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
if (!defined('PHPBB_ROOT_PATH')) define('PHPBB_ROOT_PATH', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
include(PHPBB_ROOT_PATH . 'common.' . PHP_EXT);
include(PHPBB_ROOT_PATH . 'includes/functions_display.' . PHP_EXT);

// Start session management
phpbb::$user->session_begin();
phpbb::$acl->init(phpbb::$user->data);
phpbb::$user->setup('viewforum');

display_forums('', phpbb::$config['load_moderators']);

// Set some stats, get posts count from forums data if we... hum... retrieve all forums data
$total_posts	= phpbb::$config['num_posts'];
$total_topics	= phpbb::$config['num_topics'];
$total_users	= phpbb::$config['num_users'];

// Grab group details for legend display
if (phpbb::$acl->acl_gets('a_group', 'a_groupadd', 'a_groupdel'))
{
	$sql = 'SELECT group_id, group_name, group_colour, group_type
		FROM ' . GROUPS_TABLE . '
		WHERE group_legend = 1
		ORDER BY group_name ASC';
}
else
{
	$sql = 'SELECT g.group_id, g.group_name, g.group_colour, g.group_type
		FROM ' . GROUPS_TABLE . ' g
		LEFT JOIN ' . USER_GROUP_TABLE . ' ug
			ON (
				g.group_id = ug.group_id
				AND ug.user_id = ' . phpbb::$user->data['user_id'] . '
				AND ug.user_pending = 0
			)
		WHERE g.group_legend = 1
			AND (g.group_type <> ' . GROUP_HIDDEN . ' OR ug.user_id = ' . phpbb::$user->data['user_id'] . ')
		ORDER BY g.group_name ASC';
}
$result = phpbb::$db->sql_query($sql);

$legend = array();
while ($row = phpbb::$db->sql_fetchrow($result))
{
	$colour_text = ($row['group_colour']) ? ' style="color:#' . $row['group_colour'] . '"' : '';
	$group_name = ($row['group_type'] == GROUP_SPECIAL) ? phpbb::$user->lang['G_' . $row['group_name']] : $row['group_name'];

	if ($row['group_name'] == 'BOTS' || (phpbb::$user->data['user_id'] != ANONYMOUS && !phpbb::$acl->acl_get('u_viewprofile')))
	{
		$legend[] = '<span' . $colour_text . '>' . $group_name . '</span>';
	}
	else
	{
		$legend[] = '<a' . $colour_text . ' href="' . phpbb::$url->append_sid('memberlist', 'mode=group&amp;g=' . $row['group_id']) . '">' . $group_name . '</a>';
	}
}
phpbb::$db->sql_freeresult($result);

$legend = implode(', ', $legend);

// Generate birthday list if required ...
$birthday_list = '';
if (phpbb::$config['load_birthdays'] && phpbb::$config['allow_birthdays'])
{
	$now = getdate(time() + phpbb::$user->timezone + phpbb::$user->dst - date('Z'));
	$sql = 'SELECT user_id, username, user_colour, user_birthday
		FROM ' . USERS_TABLE . "
		WHERE user_birthday LIKE '" . phpbb::$db->sql_escape(sprintf('%2d-%2d-', $now['mday'], $now['mon'])) . "%'
			AND user_type IN (" . phpbb::USER_NORMAL . ', ' . phpbb::USER_FOUNDER . ')';
	$result = phpbb::$db->sql_query($sql);

	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		$birthday_list .= (($birthday_list != '') ? ', ' : '') . get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);

		if ($age = (int) substr($row['user_birthday'], -4))
		{
			$birthday_list .= ' (' . ($now['year'] - $age) . ')';
		}
	}
	phpbb::$db->sql_freeresult($result);
}

// Assign index specific vars
phpbb::$template->assign_vars(array(
	'TOTAL_POSTS'	=> phpbb::$user->lang('TOTAL_POSTS_COUNT', $total_posts),
	'TOTAL_TOPICS'	=> phpbb::$user->lang('TOTAL_TOPICS_COUNT', $total_topics),
	'TOTAL_USERS'	=> phpbb::$user->lang('TOTAL_USERS_COUNT', $total_users),
	'NEWEST_USER'	=> phpbb::$user->lang('NEWEST_USER', get_username_string('full', phpbb::$config['newest_user_id'], phpbb::$config['newest_username'], phpbb::$config['newest_user_colour'])),

	'LEGEND'		=> $legend,
	'BIRTHDAY_LIST'	=> $birthday_list,

	'FORUM_IMG'				=> phpbb::$user->img('forum_read', 'NO_NEW_POSTS'),
	'FORUM_NEW_IMG'			=> phpbb::$user->img('forum_unread', 'NEW_POSTS'),
	'FORUM_LOCKED_IMG'		=> phpbb::$user->img('forum_read_locked', 'NO_NEW_POSTS_LOCKED'),
	'FORUM_NEW_LOCKED_IMG'	=> phpbb::$user->img('forum_unread_locked', 'NO_NEW_POSTS_LOCKED'),

	'S_LOGIN_ACTION'			=> phpbb::$url->append_sid('ucp', 'mode=login'),
	'S_DISPLAY_BIRTHDAY_LIST'	=> (phpbb::$config['load_birthdays']) ? true : false,

	'U_MARK_FORUMS'		=> (phpbb::$user->is_registered || phpbb::$config['load_anon_lastread']) ? phpbb::$url->append_sid('index', 'hash=' . generate_link_hash('global') . '&amp;mark=forums') : '',
	'U_MCP'				=> (phpbb::$acl->acl_get('m_') || phpbb::$acl->acl_getf_global('m_')) ? phpbb::$url->append_sid('mcp', 'i=main&amp;mode=front', true, phpbb::$user->session_id) : '')
);

// Output page
page_header(phpbb::$user->lang['INDEX']);

phpbb::$template->set_filenames(array(
	'body' => 'index_body.html')
);

page_footer();

?>