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
		markread('markall');
	}

	$template->assign_vars(array(
		'META' => '<meta http-equiv="refresh" content="3;url='  . "index.$phpEx$SID" . '">')
	);

	$message = $user->lang['Forums_marked_read'] . '<br /><br />' . sprintf($user->lang['Click_return_index'], '<a href="' . "index.$phpEx$SID" . '">', '</a> ');
	message_die(MESSAGE, $message);
}

// Set some stats, get posts count from forums data if we... hum... retrieve all forums data
$total_posts = $config['num_posts'];
$total_topics = $config['num_topics'];
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

include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
display_forums(array('forum_id' => 0));

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

if ($total_topics == 0)
{
	$l_total_topic_s = $user->lang['Posted_topics_zero_total'];
}
else if ($total_topics == 1)
{
	$l_total_topic_s = $user->lang['Posted_topic_total'];
}
else
{
	$l_total_topic_s = $user->lang['Posted_topics_total'];
}

$template->assign_vars(array(
	'TOTAL_POSTS'	=>	sprintf($l_total_post_s, $total_posts),
	'TOTAL_USERS'	=>	sprintf($l_total_user_s, $total_users),
	'NEWEST_USER'	=>	sprintf($user->lang['Newest_user'], '<a href="ucp.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $newest_uid . '">', $newest_user, '</a>'),

	'FORUM_IMG'			=>	$user->img('forum', $user->lang['No_new_posts']),
	'FORUM_NEW_IMG'		=>	$user->img('forum_new', $user->lang['New_posts']),
	'FORUM_LOCKED_IMG'	=>	$user->img('forum_locked', $user->lang['No_new_posts_locked']),

	'L_ONLINE_EXPLAIN'		=>	$user->lang['Online_explain'],

	'L_VIEW_MODERATORS'		=>	$user->lang['View_moderators'],
	'L_FORUM_LOCKED'		=>	$user->lang['Forum_is_locked'],
	'L_MARK_FORUMS_READ'	=>	$user->lang['Mark_all_forums'],
	'L_LEGEND'				=>	$user->lang['Legend'],
	'L_NO_FORUMS'			=>	$user->lang['No_forums'],

	'U_MARK_READ' => "index.$phpEx$SID&amp;mark=forums")
);

// Start output of page
$page_title = $user->lang['Index'];
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	'body' => 'index_body.html'
));

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>