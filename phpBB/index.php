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
$mark_read = (isset($_REQUEST['mark'])) ? $_REQUEST['mark'] : '';

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
display_forums();

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

	'FORUM_IMG'			=>	$user->img('forum', 'NO_NEW_POSTS'),
	'FORUM_NEW_IMG'		=>	$user->img('forum_new', 'NEW_POSTS'),
	'FORUM_LOCKED_IMG'	=>	$user->img('forum_locked', 'NO_NEW_POSTS_LOCKED'),

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