<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : index.php 
// STARTED   : Sat Feb 13, 2001
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);

// Get posted/get info
$mark_read = (isset($_REQUEST['mark'])) ? $_REQUEST['mark'] : '';

// Start session management
$user->start();
$auth->acl($user->data);
$user->setup();

// Handle marking posts
if ($mark_read == 'forums')
{
	if ($userdata['user_id'] != ANONYMOUS)
	{
		markread('markall');
	}

	meta_refresh(3, "index.$phpEx$SID");

	$message = $user->lang['FORUMS_MARKED'] . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . "index.$phpEx$SID" . '">', '</a> ');
	trigger_error($message);
}

// Set some stats, get posts count from forums data if we... hum... retrieve all forums data
$total_posts = $config['num_posts'];
$total_topics = $config['num_topics'];
$total_users = $config['num_users'];
$newest_user = $config['newest_username'];
$newest_uid = $config['newest_user_id'];

if ($total_users == 0)
{
	$l_total_user_s = $user->lang['REGISTERED_USERS_ZERO_TOTAL'];
}
else if ($total_users == 1)
{
	$l_total_user_s = $user->lang['REGISTERED_USER_TOTAL'];
}
else
{
	$l_total_user_s = $user->lang['REGISTERED_USERS_TOTAL'];
}

include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
display_forums();

if ($total_posts == 0)
{
	$l_total_post_s = $user->lang['POSTED_ARTICLES_ZERO_TOTAL'];
}
else if ($total_posts == 1)
{
	$l_total_post_s = $user->lang['POSTED_ARTICLE_TOTAL'];
}
else
{
	$l_total_post_s = $user->lang['POSTED_ARTICLES_TOTAL'];
}

if ($total_topics == 0)
{
	$l_total_topic_s = $user->lang['POSTED_TOPICS_ZERO_TOTAL'];
}
else if ($total_topics == 1)
{
	$l_total_topic_s = $user->lang['POSTED_TOPIC_TOTAL'];
}
else
{
	$l_total_topic_s = $user->lang['POSTED_TOPICS_TOTAL'];
}


// Grab group details for legend display
$sql = 'SELECT group_name, group_colour, group_type  
	FROM ' . GROUPS_TABLE . " 
	WHERE group_colour <> '' 
		AND group_type <> " . GROUP_HIDDEN;
$result = $db->sql_query($sql);

$legend = '';
while ($row = $db->sql_fetchrow($result))
{
	$legend .= (($legend != '') ? ', ' : '') . '<span style="color:#' . $row['group_colour'] . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</span>';
}
$db->sql_freeresult($result);


// Generate birthday list if required ...
$birthday_list = '';
if ($config['load_birthdays'])
{
	$now = getdate();
	$sql = 'SELECT user_id, username, user_colour, user_birthday 
		FROM ' . USERS_TABLE . " 
		WHERE user_birthday LIKE '" . sprintf('%2d-%2d-', $now['mday'], $now['mon']) . "%'";
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$user_colour = ($row['user_colour']) ? ' style="color:#' . $row['user_colour'] .'"' : '';
		$birthday_list .= (($birthday_list != '') ? ', ' : '') . '<a' . $user_colour . " href=\"memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['user_id'] . '">' . $row['username'] . '</a>';
		
		if ($age = (int)substr($row['user_birthday'], -4))
		{
			$birthday_list .= ' (' . ($now['year'] - $age) . ')';
		}
	}
	$db->sql_freeresult($result);
}

// Assign index specific vars
$template->assign_vars(array(
	'TOTAL_POSTS'	=> sprintf($l_total_post_s, $total_posts),
	'TOTAL_USERS'	=> sprintf($l_total_user_s, $total_users),
	'NEWEST_USER'	=> sprintf($user->lang['NEWEST_USER'], "<a href=\"memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=$newest_uid \">", $newest_user, '</a>'), 
	'LEGEND'		=> $legend, 
	'BIRTHDAY_LIST'	=> $birthday_list, 

	'FORUM_IMG'			=>	$user->img('forum', 'NO_NEW_POSTS'),
	'FORUM_NEW_IMG'		=>	$user->img('forum_new', 'NEW_POSTS'),
	'FORUM_LOCKED_IMG'	=>	$user->img('forum_locked', 'NO_NEW_POSTS_LOCKED'),

	'S_LOGIN_ACTION'			=> "ucp.php?$SID&amp;mode=login", 
	'S_DISPLAY_BIRTHDAY_LIST'	=> ($config['load_birthdays']) ? true : false, 

	'U_MARK_READ' => "index.$phpEx$SID&amp;mark=forums")
);

// Output page
page_header($user->lang['INDEX']);

$template->set_filenames(array(
	'body' => 'index_body.html')
);

page_footer();

?>