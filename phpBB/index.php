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

$viewcat = ( !empty($HTTP_GET_VARS['c']) ) ? intval($HTTP_GET_VARS['c']) : -1;
$forum_id = ( !empty($HTTP_GET_VARS['f']) ) ? intval($HTTP_GET_VARS['f']) : 0;

if ( isset($HTTP_GET_VARS['mark']) || isset($HTTP_POST_VARS['mark']) )
{
	$mark_read = ( isset($HTTP_POST_VARS['mark']) ) ? $HTTP_POST_VARS['mark'] : $HTTP_GET_VARS['mark'];
}
else
{
	$mark_read = '';
}

//
// Start session management
//
$userdata = $session->start();
$acl = new acl($userdata);
//
// End session management
//

//
// Configure style, language, etc.
//
$session->configure($userdata);

//
// Handle marking posts
//
if ( $mark_read == 'forums' )
{
	if ( $userdata['user_id'] )
	{
		setcookie($board_config['cookie_name'] . '_f_all', time(), 0, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
	}

	$template->assign_vars(array(
		'META' => '<meta http-equiv="refresh" content="3;url='  . "index.$phpEx$SID" . '">')
	);

	$message = $lang['Forums_marked_read'] . '<br /><br />' . sprintf($lang['Click_return_index'], '<a href="' . "index.$phpEx$SID" . '">', '</a> ');
	message_die(MESSAGE, $message);
}
//
// End handle marking posts
//

$mark_topics = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_t']) ) ? unserialize(stripslashes($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_t'])) : array();
$mark_forums = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f']) ) ? unserialize(stripslashes($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f'])) : array();

//
// If you don't use these stats on your index you may want to consider
// removing them
//
$total_posts = get_db_stat('postcount');
$total_users = $board_config['num_users'];
$newest_user = $board_config['newest_username'];
$newest_uid = $board_config['newest_user_id'];

if ( $total_posts == 0 )
{
	$l_total_post_s = $lang['Posted_articles_zero_total'];
}
else if ( $total_posts == 1 )
{
	$l_total_post_s = $lang['Posted_article_total'];
}
else
{
	$l_total_post_s = $lang['Posted_articles_total'];
}

if ( $total_users == 0 )
{
	$l_total_user_s = $lang['Registered_users_zero_total'];
}
else if ( $total_users == 1 )
{
	$l_total_user_s = $lang['Registered_user_total'];
}
else
{
	$l_total_user_s = $lang['Registered_users_total'];
}

//
// Start page proper
//
$sql = "SELECT c.cat_id, c.cat_title, c.cat_order
	FROM " . CATEGORIES_TABLE . " c
	ORDER BY c.cat_order";
$result = $db->sql_query($sql);

while ( $category_rows[] = $db->sql_fetchrow($result) );

if ( ( $total_categories = count($category_rows) ) )
{
	//
	// Define appropriate SQL
	//
	switch ( SQL_LAYER )
	{
		case 'oracle':
			$sql = "SELECT f.*, u.username, u.user_id
				FROM " . FORUMS_TABLE . " f, " . USERS_TABLE . " u
				WHERE u.user_id = p.poster_id(+)
				ORDER BY f.cat_id, f.forum_order";
			break;

		default:
			$sql = "SELECT f.*, u.username, u.user_id
				FROM ( " . FORUMS_TABLE . " f
				LEFT JOIN " . USERS_TABLE . " u ON u.user_id = f.forum_last_poster_id )
				ORDER BY f.cat_id, f.forum_order";
			break;
	}
	$result = $db->sql_query($sql);

	$forum_data = array();
	while ( $row = $db->sql_fetchrow($result) )
	{
		$forum_data[] = $row;
	}

	//
	// Obtain list of moderators of each forum
	// First users, then groups ... broken into two queries
	//
	$forum_moderators = array();
	get_moderators($forum_moderators);

	$template->assign_vars(array(
		'TOTAL_POSTS' => sprintf($l_total_post_s, $total_posts),
		'TOTAL_USERS' => sprintf($l_total_user_s, $total_users),
		'NEWEST_USER' => sprintf($lang['Newest_user'], '<a href="' . "profile.$phpEx$SID&amp;mode=viewprofile&amp;u=$newest_uid" . '">', $newest_user, '</a>'),

		'FORUM_IMG' => create_img($theme['forum'], $lang['No_new_posts']),
		'FORUM_NEW_IMG' => create_img($theme['forum_new'], $lang['New_posts']),
		'FORUM_LOCKED_IMG' => create_img($theme['forum_locked'], $lang['No_new_posts_locked']),

		'L_FORUM' => $lang['Forum'],
		'L_TOPICS' => $lang['Topics'],
		'L_REPLIES' => $lang['Replies'],
		'L_VIEWS' => $lang['Views'],
		'L_POSTS' => $lang['Posts'],
		'L_LASTPOST' => $lang['Last_Post'],
		'L_NO_NEW_POSTS' => $lang['No_new_posts'],
		'L_NEW_POSTS' => $lang['New_posts'],
		'L_NO_NEW_POSTS_LOCKED' => $lang['No_new_posts_locked'],
		'L_NEW_POSTS_LOCKED' => $lang['New_posts_locked'],
		'L_ONLINE_EXPLAIN' => $lang['Online_explain'],

		'L_VIEW_MODERATORS' => $lang['View_moderators'],
		'L_FORUM_LOCKED' => $lang['Forum_is_locked'],
		'L_MARK_FORUMS_READ' => $lang['Mark_all_forums'],
		'L_LEGEND' => $lang['Legend'],
		'L_NO_FORUMS' => $lang['No_forums'],

		'U_MARK_READ' => "index.$phpEx$SID&amp;mark=forums")
	);

	//
	// Okay, let's build the index
	//
	for($i = 0; $i < $total_categories; $i++)
	{
		$cat_id = $category_rows[$i]['cat_id'];

		//
		// Should we display this category/forum set?
		//
		$display_forums = false;
		for($j = 0; $j < sizeof($forum_data); $j++)
		{
			if ( $acl->get_acl($forum_data[$j]['forum_id'], 'forum', 'list') && $forum_data[$j]['cat_id'] == $cat_id )
			{
				$display_forums = true;
			}
		}

		//
		// Yes, we should, so first dump out the category
		// title, then, if appropriate the forum list
		//
		if ( $display_forums )
		{
			$template->assign_block_vars('catrow', array(
				'CAT_ID' => $cat_id,
				'CAT_DESC' => $category_rows[$i]['cat_title'],
				'U_VIEWCAT' => "index.$phpEx$SID&amp;c=$cat_id",
				'HAVE_FORUMS' => true)
			);

			if ( $viewcat == $cat_id || $viewcat == -1 )
			{
				for($j = 0; $j < sizeof($forum_data); $j++)
				{
					if ( $forum_data[$j]['cat_id'] == $cat_id )
					{
						$row_forum_id = $forum_data[$j]['forum_id'];

						if ( $acl->get_acl($row_forum_id, 'forum', 'list') )
						{
							if ( $forum_data[$j]['forum_status'] == FORUM_LOCKED )
							{
								$folder_image = $theme['forum_locked'];
								$folder_alt = $lang['Forum_locked'];
							}
							else
							{
								$unread_topics = false;
								if ( $userdata['user_id'] && $forum_data[$j]['forum_last_post_time'] > $userdata['user_lastvisit'] )
								{
									$unread_topics = true;
									if ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f_all']) )
									{
										if ( $HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f_all'] > $forum_data[$j]['forum_last_post_time'] )
										{
											$unread_topics = false;
										}
									}

									if ( isset($mark_topics[$row_forum_id]) || isset($mark_forums[$row_forum_id]) )
									{
										if ( $mark_forums[$row_forum_id] > $userdata['user_lastvisit'] || !max($mark_topics[$row_forum_id]) )
										{
											$unread_topics = false;
										}
									}
								}

								$folder_image = ( $unread_topics ) ? $theme['forum_new'] : $theme['forum'];
								$folder_alt = ( $unread_topics ) ? $lang['New_posts'] : $lang['No_new_posts'];
							}

							$posts = $forum_data[$j]['forum_posts'];
							$topics = $forum_data[$j]['forum_topics'];

							if ( $forum_data[$j]['forum_last_post_id'] )
							{
								$last_post_time = create_date($board_config['default_dateformat'], $forum_data[$j]['forum_last_post_time'], $board_config['board_timezone']);

								$last_post = $last_post_time . '<br />';

								$last_post .= ( $forum_data[$j]['user_id'] == ANONYMOUS ) ? ( ($forum_data[$j]['forum_last_poster_name'] != '' ) ? $forum_data[$j]['forum_last_poster_name'] . ' ' : $lang['Guest'] . ' ' ) : '<a href="' . "profile.$phpEx$SID&amp;mode=viewprofile&amp;u="  . $forum_data[$j]['user_id'] . '">' . $forum_data[$j]['username'] . '</a> ';

								$last_post .= '<a href="' . "viewtopic.$phpEx$SID&amp;f=$row_forum_id&amp;p=" . $forum_data[$j]['forum_last_post_id'] . '#' . $forum_data[$j]['forum_last_post_id'] . '">' . create_img($theme['goto_post_latest'], $lang['View_latest_post']) . '</a>';
							}
							else
							{
								$last_post = $lang['No_Posts'];
							}

							if ( count($forum_moderators[$row_forum_id]) > 0 )
							{
								$l_moderators = ( count($forum_moderators[$row_forum_id]) == 1 ) ? $lang['Moderator'] . ':' : $lang['Moderators'] . ':' ;
								$moderator_list = implode(', ', $forum_moderators[$row_forum_id]);
							}
							else
							{
								$l_moderators = '&nbsp;';
								$moderator_list = '&nbsp;';
							}

							$template->assign_block_vars('catrow.forumrow',	array(
								'ROW_COUNT' => $i,
								'FORUM_FOLDER_IMG' => create_img($folder_image, $folder_alt),
								'FORUM_NAME' => $forum_data[$j]['forum_name'],
								'FORUM_DESC' => $forum_data[$j]['forum_desc'],
								'POSTS' => $forum_data[$j]['forum_posts'],
								'TOPICS' => $forum_data[$j]['forum_topics'],
								'LAST_POST' => $last_post,
								'MODERATORS' => $moderator_list,

								'FORUM_IMG' => $forum_data[$j]['forum_image'],

								'L_MODERATOR' => $l_moderators,
								'L_FORUM_FOLDER_ALT' => $folder_alt,

								'U_VIEWFORUM' => "viewforum.$phpEx$SID&amp;f=$row_forum_id")
							);
						}
					}
				}
			}
		}
	} // for ... categories

}// if ... total_categories

//
// Start output of page
//
$page_title = $lang['Index'];
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	'body' => 'index_body.html')
);

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>