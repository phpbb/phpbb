<?php
/***************************************************************************
 *                                index.php
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

// Get posted/get info
$cat_id = (!empty($_GET['c'])) ? intval($_GET['c']) : 0;

if (isset($_GET['mark']) || isset($_POST['mark']))
{
	$mark_read = (isset($_POST['mark'])) ? $_POST['mark'] : $_GET['mark'];
}
else
{
	$mark_read = '';
}

// Start session management
$user->start();
$user->setup();
$auth->acl($user->data);
// End session management

// Handle marking posts
if ($mark_read == 'forums')
{
	if ($userdata['user_id'])
	{
		setcookie($config['cookie_name'] . '_f_all', time(), 0, $config['cookie_path'], $config['cookie_domain'], $config['cookie_secure']);
	}

	$template->assign_vars(array(
		'META' => '<meta http-equiv="refresh" content="3;url='  . "index.$phpEx$SID" . '">')
	);

	$message = $user->lang['Forums_marked_read'] . '<br /><br />' . sprintf($user->lang['Click_return_index'], '<a href="' . "index.$phpEx$SID" . '">', '</a> ');
	message_die(MESSAGE, $message);
}
// End handle marking posts

// Topic/forum marked read info
$mark_topics = (isset($_COOKIE[$config['cookie_name'] . '_t'])) ? unserialize(stripslashes($_COOKIE[$config['cookie_name'] . '_t'])) : array();
$mark_forums = (isset($_COOKIE[$config['cookie_name'] . '_f'])) ? unserialize(stripslashes($_COOKIE[$config['cookie_name'] . '_f'])) : array();

// Set some stats, get posts count from forums data if we... hum... retrieve all forums data
$total_users = $config['num_users'];
$newest_user = $config['newest_username'];
$newest_uid = $config['newest_user_id'];

if ($total_users == 0)
{
	$l_total_user_s = $user->lang['Registered_users_zero_total'];
}
else if ($total_users == 1)
{
	$l_total_user_s = $user->lang['Registered_user_total'];
}
else
{
	$l_total_user_s = $user->lang['Registered_users_total'];
}

// Forum moderators ... a static template var could allow us
// to drop these queries ...
$forum_moderators = array();
get_moderators($forum_moderators);

// Set some vars
$root_id = $branch_root_id = $cat_id;
$forum_rows = $subforums = $nav_forums = array();

if ($cat_id == 0)
{
	$is_nav = FALSE;
	$total_posts = 0;
	switch (SQL_LAYER)
	{
		case 'oracle':
			$sql = 'SELECT f.*, u.username
					FROM ' . FORUMS_TABLE . ' f, ' . USERS_TABLE . 'u
					WHERE f.forum_last_poster_id = u.user_id(+)
					ORDER BY f.left_id';
			break;

		default:
			$sql = 'SELECT f.*, u.username
					FROM ' . FORUMS_TABLE . ' f
					LEFT JOIN ' . USERS_TABLE . ' u ON f.forum_last_poster_id = u.user_id
					ORDER BY f.left_id';
	}
}
else
{
	$is_nav = TRUE;

	if (!$auth->acl_get('f_list', $cat_id))
	{
		// TODO: Deal with hidden categories
		message_die(ERROR, $user->lang['Category_not_exist']);
	}

	// NOTE: make sure that categories post count is set to 0
	$sql = 'SELECT SUM(forum_posts) AS total
			FROM ' . FORUMS_TABLE . '
			WHERE post_count_inc = 1';

	$result = $db->sql_query($sql);
	$total_posts = $db->sql_fetchfield('total', 0, $result);

	$result = $db->sql_query('SELECT left_id, right_id, parent_id FROM ' . FORUMS_TABLE . ' WHERE forum_id = ' . $cat_id);
	$catrow = $db->sql_fetchrow($result);

	switch (SQL_LAYER)
	{
		case 'oracle':
			$sql = 'SELECT f.*, u.username
					FROM ' . FORUMS_TABLE . ' f, ' . USERS_TABLE . 'u
					WHERE (f.left_id BETWEEN ' . $catrow['left_id'] . ' AND ' . $catrow['right_id'] . '
					   OR ' . $catrow['left_id'] . ' BETWEEN f.left_id AND f.right_id)
					  AND f.forum_last_poster_id = u.user_id(+)
					ORDER BY left_id';
		break;

		default:
			$sql = 'SELECT f.*, u.username
					FROM ' . FORUMS_TABLE . ' f
					LEFT JOIN ' . USERS_TABLE . ' u ON f.forum_last_poster_id = u.user_id
					WHERE f.left_id BETWEEN ' . $catrow['left_id'] . ' AND ' . $catrow['right_id'] . '
					   	OR ' . $catrow['left_id'] . ' BETWEEN f.left_id AND f.right_id
					ORDER BY f.left_id';
	}
}

$result = $db->sql_query($sql);
while ($row = $db->sql_fetchrow($result))
{
	if (!$cat_id && $row['post_count_inc'])
	{
		$total_posts += $row['forum_posts'];
	}

	if ($row['forum_id'] == $cat_id)
	{
		$nav_forums[] = $row;
		$forum_rows[] = $row;
		$is_nav = FALSE;
	}
	elseif ($is_nav)
	{
		$nav_forums[] = $row;
	}
	else
	{
		if ($row['parent_id'] == $cat_id)
		{
			// Root-level forum
			$forum_rows[] = $row;
			$parent_id = $row['forum_id'];

			if (!$cat_id && $row['forum_status'] == ITEM_CATEGORY)
			{
				$branch_root_id = $row['forum_id'];
			}
		}
		elseif ($row['parent_id'] == $branch_root_id)
		{
			// Forum directly under a category
			$forum_rows[] = $row;
			$parent_id = $row['forum_id'];
		}
		elseif ($row['display_on_index'] && $row['forum_status'] != ITEM_CATEGORY)
		{
			// Subforum, store it for direct linking
			if ($auth->acl_get('f_list', $row['forum_id']))
			{
				$subforums[$parent_id][] = $row;
			}
		}
	}
}

$root_id = ($cat_id) ? $catrow['parent_id'] : 0;
include($phpbb_root_path . 'includes/forums_display.' . $phpEx);

if ($total_posts == 0)
{
	$l_total_post_s = $user->lang['Posted_articles_zero_total'];
}
else if ($total_posts == 1)
{
	$l_total_post_s = $user->lang['Posted_article_total'];
}
else
{
	$l_total_post_s = $user->lang['Posted_articles_total'];
}

$template->assign_vars(array(
	'TOTAL_POSTS'	=>	sprintf($l_total_post_s, $total_posts),
	'TOTAL_USERS'	=>	sprintf($l_total_user_s, $total_users),
	'NEWEST_USER'	=>	sprintf($user->lang['Newest_user'], '<a href="profile.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $newest_uid . '">', $newest_user, '</a>'),

	'FORUM_IMG'			=>	$user->img('forum', $user->lang['No_new_posts']),
	'FORUM_NEW_IMG'		=>	$user->img('forum_new', $user->lang['New_posts']),
	'FORUM_LOCKED_IMG'	=>	$user->img('forum_locked', $user->lang['No_new_posts_locked']),

	'L_FORUM'			=>	$user->lang['Forum'],
	'L_TOPICS'			=>	$user->lang['Topics'],
	'L_REPLIES'			=>	$user->lang['Replies'],
	'L_VIEWS'			=>	$user->lang['Views'],
	'L_POSTS'			=>	$user->lang['Posts'],
	'L_LASTPOST'		=>	$user->lang['Last_Post'],
	'L_MODERATORS'		=>	$user->lang['Moderators'],
	'L_NO_NEW_POSTS'	=>	$user->lang['No_new_posts'],
	'L_NEW_POSTS'		=>	$user->lang['New_posts'],
	'L_NO_NEW_POSTS_LOCKED'	=>	$user->lang['No_new_posts_locked'],
	'L_NEW_POSTS_LOCKED'	=>	$user->lang['New_posts_locked'],
	'L_ONLINE_EXPLAIN'		=>	$user->lang['Online_explain'],

	'L_VIEW_MODERATORS'		=>	$user->lang['View_moderators'],
	'L_FORUM_LOCKED'		=>	$user->lang['Forum_is_locked'],
	'L_MARK_FORUMS_READ'	=>	$user->lang['Mark_all_forums'],
	'L_LEGEND'				=>	$user->lang['Legend'],
	'L_NO_FORUMS'			=>	$user->lang['No_forums'],

	'U_MARK_READ' => "index.$phpEx$SID&amp;mark=forums")
);

foreach ($nav_forums as $row)
{
	if ($row['forum_status'] == ITEM_CATEGORY)
	{
		$link = 'index.' . $phpEx . $SID . '&amp;c=' . $row['forum_id'];
	}
	else
	{
		$link = 'viewforum.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'];
	}

	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=>	$row['forum_name'],
		'U_VIEW_FORUM'	=>	$link
	));
}

// Start output of page
$page_title = $user->lang['Index'];
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	'body' => 'index_body.html'
));

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>