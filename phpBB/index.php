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

//
// Start session management
//
$userdata = $session->start();
$acl = new acl('list', $userdata);

$session->configure($userdata);
//
// End session management
//

//
// Handle marking posts
//
if ( isset($HTTP_GET_VARS['mark']) || isset($HTTP_POST_VARS['mark']) )
{
	if ( $userdata['user_id'] != ANONYMOUS )
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

$tracking_topics = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_t']) ) ? unserialize($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_t']) : array();
$tracking_forums = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f']) ) ? unserialize($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f']) : array();

//
//
//
$forum_id = ( !empty($HTTP_GET_VARS['f']) ) ? "WHERE f2.forum_id = " . intval($HTTP_GET_VARS['f']) : '';

//
// If you don't use these stats on your index you may want to consider
// removing them
//
$total_posts = get_db_stat('postcount');
$total_users = $board_config['num_users'];
$newest_user = $board_config['newest_username'];
$newest_uid = $board_config['newest_user_id'];

$l_total_post_s = ( $total_posts > 1 ) ? $lang['Posted_articles_total'] : ( ( $total_posts == 0 ) ? $lang['Posted_articles_zero_total'] : $lang['Posted_article_total'] );
$l_total_user_s = ( $total_users > 1 ) ? $lang['Registered_users_total'] : ( ( $total_users == 1 ) ? $lang['Registered_user_total'] : $lang['Registered_users_zero_total'] );


switch ( SQL_LAYER )
{
	case 'oracle':
		break;

	default:
/*		$sql = "SELECT f1.*, u.username, u.user_id
			FROM ( " . FORUMS_TABLE . " f1
			LEFT JOIN " . USERS_TABLE . " u ON u.user_id = f1.forum_last_poster_id )
			$forum_id
			ORDER BY f1.forum_id";*/

		$sql = "SELECT f1.*, u.username, u.user_id
			FROM (( " . FORUMS_TABLE . " f1
			LEFT JOIN " . FORUMS_TABLE . " f2 ON f1.left_id > f2.left_id AND f1.left_id < f2.right_id )
			LEFT JOIN " . USERS_TABLE . " u ON u.user_id = f1.forum_last_poster_id )
			$forum_id
			ORDER BY f2.forum_id";
		break;
}
$result = $db->sql_query($sql);

$forum_data = array();
if ( $row = $db->sql_fetchrow($result) )
{
	$last_forum_right_id = 0;
	do
	{
		$row_forum_id = $row['forum_id'];

		//
		// A non-postable forum on the index is treated as a category
		//
		if ( ( $row['forum_status'] == 2 || $row_forum_id == $forum_id ) && $row['right_id'] - $row['left_id'] > 1 )
		{
			$template->assign_block_vars('catrow', array(
				'CAT_ID' => $forum_id,
				'CAT_DESC' => $row['forum_name'],

				'U_VIEWCAT' => "index.$phpEx?$SID&amp;f=$row_forum_id")
			);
		}
		else
		{
			if ( $acl->get_acl($row_forum_id, 'forum', 'list') )
			{
				if ( $row['forum_status'] == FORUM_LOCKED )
				{
					$folder_image = $theme['forum_locked'];
					$folder_alt = $lang['Forum_locked'];
				}
				else
				{
					$unread_topics = false;
					if ( $userdata['user_id'] != ANONYMOUS )
					{
						if ( $row['post_time'] > $last_visit )
						{
							$unread_topics = true;

							if ( !empty($tracking_forums[$row_forum_id]) )
							{
								if ( $tracking_forums[$row_forum_id] > $last_visit )
								{
									$unread_topics = false;
								}
							}

							if ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f_all']) )
							{
								if ( $HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f_all'] > $last_visit )
								{
									$unread_topics = false;
								}
							}
						}
					}

					$folder_image = ( $unread_topics ) ? $theme['forum_new'] : $theme['forum'];
					$folder_alt = ( $unread_topics ) ? $lang['New_posts'] : $lang['No_new_posts'];
				}

				$posts = $row['forum_posts'];
				$topics = $row['forum_topics'];

				if ( $row['forum_last_post_id'] )
				{
					$last_post_time = create_date($board_config['default_dateformat'], $row['post_time'], $board_config['board_timezone']);

					$last_post = $last_post_time . '<br />';

					$last_post .= ( $row['user_id'] == ANONYMOUS ) ? ( ($row['post_username'] != '' ) ? $row['post_username'] . ' ' : $lang['Guest'] . ' ' ) : '<a href="' . "profile.$phpEx$SID&amp;mode=viewprofile&amp;u="  . $row['user_id'] . '">' . $row['username'] . '</a> ';

					$last_post .= '<a href="' . "viewtopic.$phpEx$SID&amp;f=$row_forum_id&amp;p=" . $forum_data[$j]['forum_last_post_id'] . '#' . $forum_data[$j]['forum_last_post_id'] . '">' . create_img($theme['goto_post_latest'], $lang['View_latest_post']) . '</a>';

				}
				else
				{
					$last_post = $lang['No_Posts'];
				}

				if ( count($forum_moderators[$row_forum_id]) > 0 )
				{
					$l_moderators = ( count($forum_moderators[$row_forum_id]) == 1 ) ? $lang['Moderator'] : $lang['Moderators'];
					$moderator_list = implode(', ', $forum_moderators[$row_forum_id]);
				}
				else
				{
					$l_moderators = '&nbsp;';
					$moderator_list = '&nbsp;';
				}

				$template->assign_block_vars('catrow.forumrow',	array(
					'FORUM_FOLDER_IMG' => create_img($folder_image, $folder_alt),
					'FORUM_NAME' => $row['forum_name'],
					'FORUM_DESC' => $row['forum_desc'],
					'POSTS' => $row['forum_posts'],
					'TOPICS' => $row['forum_topics'],
					'LAST_POST' => $last_post,
					'MODERATORS' => $moderator_list,

					'L_MODERATOR' => $l_moderators,
					'L_FORUM_FOLDER_ALT' => $folder_alt,

					'U_VIEWFORUM' => "viewforum.$phpEx$SID&amp;f=$row_forum_id")
				);
			}
		}
	}
	while ( $row = $db->sql_fetchrow($result) );

}

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