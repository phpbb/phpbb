<?php
/***************************************************************************
 *                               viewforum.php
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
//
// End session management
//

//
// Start initial var setup
//
if ( isset($HTTP_GET_VARS['f']) || isset($HTTP_POST_VARS['f']) )
{
	$forum_id = ( isset($HTTP_GET_VARS['f']) ) ? intval($HTTP_GET_VARS['f']) : intval($HTTP_POST_VARS['f']);
}
else
{
	$forum_id = '';
}

if ( isset($HTTP_GET_VARS['mark']) || isset($HTTP_POST_VARS['mark']) )
{
	$mark_read = ( isset($HTTP_POST_VARS['mark']) ) ? $HTTP_POST_VARS['mark'] : $HTTP_GET_VARS['mark'];
}
else
{
	$mark_read = '';
}

$start = ( isset($HTTP_GET_VARS['start']) ) ? intval($HTTP_GET_VARS['start']) : 0;
//
// End initial var setup
//

//
// Check if the user has actually sent a forum ID with his/her request
// If not give them a nice error page.
//
if ( !empty($forum_id) )
{
	$sql = "SELECT *
		FROM " . FORUMS_TABLE . "
		WHERE forum_id = $forum_id";
	$result = $db->sql_query($sql);
}
else
{
	message_die(MESSAGE, 'Forum_not_exist');
}

if ( !($forum_data = $db->sql_fetchrow($result)) )
{
	message_die(MESSAGE, 'Forum_not_exist');
}

//
// Configure style, language, etc.
//
$acl = new auth('forum', $userdata, $forum_id);
$userdata['user_style'] = ( $forum_data['forum_style'] ) ? $forum_data['user_style'] : $userdata['user_style'];
$session->configure($userdata);

//
// Auth check
//
if ( !$acl->get_acl($forum_id, 'forum', 'list') || !$acl->get_acl($forum_id, 'forum', 'read') )
{
	if ( $userdata['user_id'] == ANONYMOUS )
	{
		$redirect = "f=$forum_id" . ( ( isset($start) ) ? "&start=$start" : '' );
		$header_location = ( @preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE')) ) ? 'Refresh: 0; URL=' : 'Location: ';
		header($header_location . "login.$phpEx$SID&redirect=viewforum.$phpEx&$redirect");
		exit;
	}

	//
	// The user is not authed to read this forum ...
	//
	$message = ( !$acl->get_acl($forum_id, 'forum', 'list') ) ? $lang['Forum_not_exist'] : sprintf($lang['Sorry_auth_read'], $is_auth[$forum_id]['auth_read_type']);

	message_die(MESSAGE, $message);
}
//
// End of auth check
//

//
// Topic read tracking cookie info
//
$tracking_topics = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_t']) ) ? unserialize($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_t']) : '';
$tracking_forums = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f']) ) ? unserialize($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f']) : '';

//
// Handle marking posts
//
if ( $mark_read == 'topics' )
{
	if ( $userdata['user_id']  != ANONYMOUS )
	{
		$sql = "SELECT MAX(post_time) AS last_post 
			FROM " . POSTS_TABLE . " 
			WHERE forum_id = $forum_id";
		$result = $db->sql_query($sql);

		if ( $row = $db->sql_fetchrow($result) )
		{
			if ( ( count($tracking_forums) + count($tracking_topics) ) >= 150 && empty($tracking_forums[$forum_id]) )
			{
				asort($tracking_forums);
				unset($tracking_forums[key($tracking_forums)]);
			}

			if ( $row['last_post'] > $userdata['user_lastvisit'] )
			{
				$tracking_forums[$forum_id] = time();

				setcookie($board_config['cookie_name'] . '_f', serialize($tracking_forums), 0, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
			}
		}

		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="3;url=' . "viewforum.$phpEx$SID&amp;f=$forum_id" . '">')
		);
	}

	$message = $lang['Topics_marked_read'] . '<br /><br />' . sprintf($lang['Click_return_forum'], '<a href="' . "viewforum.$phpEx$SID&amp;f=$forum_id" . '">', '</a> ');
	message_die(MESSAGE, $message);
}
//
// End handle marking posts
//

//
// Do the forum Prune
//
if ( $acl->get_acl($forum_id, 'mod', 'prune') && $board_config['prune_enable'] )
{
	if ( $forum_data['prune_next'] < time() && $forum_data['prune_enable'] )
	{
		require($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
		auto_prune($forum_id);
	}
}
//
// End of forum prune
//

//
// Forum rules, subscription info and word censors
//
$s_watching_forum = '';
$s_watching_forum_img = '';
watch_topic_forum('forum', $s_watching_forum, $s_watching_forum_img, $userdata['user_id'], $forum_id);

$s_forum_rules = '';
get_forum_rules('forum', $s_forum_rules, $forum_id);

$orig_word = array();
$replacement_word = array();
obtain_word_list($orig_word, $replacement_word);

//
// Topic ordering options
//
$previous_days = array(0 => $lang['All_Topics'], 1 => $lang['1_Day'], 7 => $lang['7_Days'], 14 => $lang['2_Weeks'], 30 => $lang['1_Month'], 90 => $lang['3_Months'], 180 => $lang['6_Months'], 364 => $lang['1_Year']);
$sort_by_text = array('a' => $lang['Author'], 't' => $lang['Post_time'], 'r' => $lang['Replies'], 's' => $lang['Subject'], 'v' => $lang['Views']);
$sort_by = array('a' => 'u.username', 't' => 't.topic_last_post_id', 'r' => 't.topic_replies', 's' => 't.topic_title', 'v' => 't.topic_views');

if ( isset($HTTP_POST_VARS['sort']) )
{
	if ( !empty($HTTP_POST_VARS['sort_days']) )
	{
		$sort_days = ( !empty($HTTP_POST_VARS['sort_days']) ) ? intval($HTTP_POST_VARS['sort_days']) : intval($HTTP_GET_VARS['sort_days']);
		$min_topic_time = time() - ($sort_days * 86400);

		$sql = "SELECT COUNT(t.topic_id) AS forum_topics 
			FROM " . TOPICS_TABLE . " t, " . POSTS_TABLE . " p 
			WHERE t.forum_id = $forum_id 
				AND p.post_id = t.topic_last_post_id 
				AND p.post_time >= $min_topic_time"; 
		$result = $db->sql_query($sql);

		$start = 0;
		$topics_count = ( $row = $db->sql_fetchrow($result) ) ? $row['forum_topics'] : 0;
		$limit_topics_time = "AND p.post_time >= $min_topic_time";
	}
	else
	{
		$topics_count = ( $forum_data['forum_topics'] ) ? $forum_data['forum_topics'] : 1;
	}

	$sort_key = ( isset($HTTP_POST_VARS['sort_key']) ) ? $HTTP_POST_VARS['sort_key'] : $HTTP_GET_VARS['sort_key'];
	$sort_dir = ( isset($HTTP_POST_VARS['sort_dir']) ) ? $HTTP_POST_VARS['sort_dir'] : $HTTP_GET_VARS['sort_dir'];
}
else
{
	$topics_count = ( $forum_data['forum_topics'] ) ? $forum_data['forum_topics'] : 1;
	$limit_topics_time = '';

	$sort_days = 0;
	$sort_key = 't';
	$sort_dir = 'd';
}

$sort_order = $sort_by[$sort_key] . ' ' . ( ( $sort_dir == 'd' ) ? 'DESC' : 'ASC' );

$select_sort_days = '<select name="sort_days">';
foreach ( $previous_days as $day => $text )
{
	$selected = ( $sort_days == $day ) ? ' selected="selected"' : '';
	$select_sort_days .= '<option value="' . $day . '"' . $selected . '>' . $text . '</option>';
}
$select_sort_days .= '</select>';

$select_sort = '<select name="sort_key">';
foreach ( $sort_by_text as $key => $text )
{
	$selected = ( $sort_key == $key ) ? ' selected="selected"' : '';
	$select_sort .= '<option value="' . $key . '"' . $selected . '>' . $text . '</option>';
}
$select_sort .= '</select>';

$select_sort_dir = '<select name="sort_dir">';
$select_sort_dir .= ( $sort_dir == 'a' ) ? '<option value="a" selected="selected">' . $lang['Ascending'] . '</option><option value="d">' . $lang['Descending'] . '</option>' : '<option value="a">' . $lang['Ascending'] . '</option><option value="d" selected="selected">' . $lang['Descending'] . '</option>';
$select_sort_dir .= '</select>';

$post_alt = ( $forum_data['forum_status'] == FORUM_LOCKED ) ? $lang['Forum_locked'] : $lang['Post_new_topic'];
$post_img = '<img src=' . (( $forum_data['forum_status'] == FORUM_LOCKED ) ? $theme['post_locked'] : $theme['post_new'] ) . ' border="0" alt="' . $post_alt . '" title="' . $post_alt . '" />';

$template->assign_vars(array(
	'FORUM_ID' => $forum_id,
	'FORUM_NAME' => $forum_data['forum_name'],
	'POST_IMG' => $post_img, 
	'PAGINATION' => generate_pagination("viewforum.$phpEx$SID&amp;f=$forum_id&amp;topicdays=$topic_days", $topics_count, $board_config['topics_per_page'], $start),
	'PAGE_NUMBER' => sprintf($lang['Page_of'], ( floor( $start / $board_config['topics_per_page'] ) + 1 ), ceil( $topics_count / $board_config['topics_per_page'] )), 

	'FOLDER_IMG' => create_img($theme['folder'], $lang['No_new_posts']), 
	'FOLDER_NEW_IMG' => create_img($theme['folder_new'], $lang['New_posts']), 
	'FOLDER_HOT_IMG' => create_img($theme['folder_hot'], $lang['No_new_posts_hot']), 
	'FOLDER_HOT_NEW_IMG' => create_img($theme['folder_hot_new'], $lang['New_posts_hot']), 
	'FOLDER_LOCKED_IMG' => create_img($theme['folder_locked'], $lang['No_new_posts_locked']),
	'FOLDER_LOCKED_NEW_IMG' => create_img($theme['folder_locked_new'], $lang['New_posts_locked']),
	'FOLDER_STICKY_IMG' => create_img($theme['folder_sticky'], $lang['Post_Sticky']),
	'FOLDER_STICKY_NEW_IMG' => create_img($theme['folder_sticky_new'], $lang['Post_Sticky']),
	'FOLDER_ANNOUNCE_IMG' => create_img($theme['folder_announce'], $lang['Post_Announcement']),
	'FOLDER_ANNOUNCE_NEW_IMG' => create_img($theme['folder_announce_new'], $lang['Post_Announcement']),

	'L_TOPICS' => $lang['Topics'], 
	'L_REPLIES' => $lang['Replies'], 
	'L_VIEWS' => $lang['Views'], 
	'L_POSTS' => $lang['Posts'], 
	'L_LASTPOST' => $lang['Last_Post'], 
	'L_VIEW_MODERATORS' => $lang['View_moderators'], 
	'L_DISPLAY_TOPICS' => $lang['Display_topics'], 
	'L_SORT_BY' => $lang['Sort_by'], 
	'L_MARK_TOPICS_READ' => $lang['Mark_all_topics'], 
	'L_NO_NEW_POSTS' => $lang['No_new_posts'], 
	'L_NEW_POSTS' => $lang['New_posts'], 
	'L_NO_NEW_POSTS_LOCKED' => $lang['No_new_posts_locked'], 
	'L_NEW_POSTS_LOCKED' => $lang['New_posts_locked'], 
	'L_NO_NEW_POSTS_HOT' => $lang['No_new_posts_hot'], 
	'L_NEW_POSTS_HOT' => $lang['New_posts_hot'], 
	'L_ANNOUNCEMENT' => $lang['Post_Announcement'], 
	'L_STICKY' => $lang['Post_Sticky'], 
	'L_POSTED' => $lang['Posted'], 
	'L_JOINED' => $lang['Joined'], 
	'L_AUTHOR' => $lang['Author'], 
	'L_NO_TOPICS' => ( $forum_data['forum_status'] == FORUM_LOCKED ) ? $lang['Forum_locked'] : $lang['No_topics_post_one'], 
	'L_GOTO_PAGE' => $lang['Goto_page'], 

	'S_SELECT_SORT_DIR' => $select_sort_dir, 
	'S_SELECT_SORT_KEY' => $select_sort, 
	'S_SELECT_SORT_DAYS' => $select_sort_days,
	'S_AUTH_LIST' => $s_forum_rules, 
	'S_WATCH_FORUM' => $s_watching_forum,
	'S_FORUM_ACTION' => 'viewforum.' . $phpEx . $SID . '&amp;f=' . $forum_id . "&amp;start=$start", 

	'U_POST_NEW_TOPIC' => 'posting.' . $phpEx . $SID . '&amp;mode=newtopic&amp;f=' . $forum_id,
	'U_VIEW_FORUM' => 'viewforum.' . $phpEx . $SID . '&amp;f=' . $forum_id,
	'U_VIEW_MODERATORS' => 'memberslist.' . $phpEx . $SID . '&amp;mode=moderators&amp;f=' . $forum_id, 
	'U_MARK_READ' => 'viewforum.' . $phpEx . $SID . '&amp;f=' . $forum_id . '&amp;mark=topics')
);

//
// Grab all the basic data. If we're not on page 1 we also grab any
// announcements that may exist.
//
$total_topics = 0;
$topic_rowset = array();

if ( $start )
{
	$sql = "SELECT t.*, i.icons_url, i.icons_width, i.icons_height, u.username, u.user_id, u2.username as user2, u2.user_id as id2, p.post_time, p.post_username AS post_username2   
		FROM " . TOPICS_TABLE . " t, " . ICONS_TABLE . " i, " . USERS_TABLE . " u, " . POSTS_TABLE . " p, " . USERS_TABLE . " u2   
		WHERE t.forum_id = $forum_id 
			AND t.topic_type = " . POST_ANNOUNCE . " 
			AND i.icons_id = t.topic_icon 
			AND u.user_id = t.topic_poster 
			AND p.post_id = t.topic_last_post_id 
			AND u2.user_id = p.poster_id 
		ORDER BY $sort_order 
		LIMIT " . $board_config['topics_per_page'];
	$result = $db->sql_query($sql);

	while( $row = $db->sql_fetchrow($result) )
	{
		$topic_rowset[] = $row;
		$total_topics++;
	}
}

$sql = "SELECT t.*, i.icons_url, i.icons_width, i.icons_height, u.username, u.user_id, u2.username as user2, u2.user_id as id2, p.post_username, p2.post_username AS post_username2, p2.post_time 
	FROM " . TOPICS_TABLE . " t, " . ICONS_TABLE . " i, " . USERS_TABLE . " u, " . POSTS_TABLE . " p, " . POSTS_TABLE . " p2, " . USERS_TABLE . " u2 
	WHERE t.forum_id = $forum_id 
		AND i.icons_id = t.topic_icon 
		AND u.user_id = t.topic_poster 
		AND p.post_id = t.topic_first_post_id 
		AND p2.post_id = t.topic_last_post_id 
		AND u2.user_id = p2.poster_id 
		$limit_topics_time 
	ORDER BY t.topic_type DESC, $sort_order  
	LIMIT $start, " . $board_config['topics_per_page'];
$result = $db->sql_query($sql);

while( $row = $db->sql_fetchrow($result) )
{
	$topic_rowset[] = $row;
	$total_topics++;
}

$db->sql_freeresult($result);

//
// Okay, lets dump out the page ...
//
if ( $total_topics )
{
	$row_count = 0;

	for($i = 0; $i < $total_topics; $i++)
	{
		$topic_id = $topic_rowset[$i]['topic_id'];

		$topic_title = ( count($orig_word) ) ? preg_replace($orig_word, $replacement_word, $topic_rowset[$i]['topic_title']) : $topic_rowset[$i]['topic_title'];

		$topic_type = $topic_rowset[$i]['topic_type'];

		$topic_type = '';
		if ( $topic_rowset[$i]['topic_status'] == TOPIC_MOVED )
		{
			$topic_type = $lang['Topic_Moved'] . ' ';
			$topic_id = $topic_rowset[$i]['topic_moved_id'];

			$folder_image =  $theme['folder'];
			$folder_alt = $lang['Topic_Moved'];
			$newest_post_img = '';
		}
		else
		{
			switch ( $topic_rowset[$i]['topic_type'] )
			{
				case POST_ANNOUNCE:
					$topic_type = $lang['Topic_Announcement'] . ' ';
					$folder = $theme['folder_announce'];
					$folder_new = $theme['folder_announce_new'];
					break;
				case POST_STICKY:
					$topic_type = $lang['Topic_Sticky'] . ' ';
					$folder = $theme['folder_sticky'];
					$folder_new = $theme['folder_sticky_new'];
					break;
				case TOPIC_LOCKED:
					$folder = $theme['folder_locked'];
					$folder_new = $theme['folder_locked_new'];
					break;
				default:
					if ( $replies >= $board_config['hot_threshold'] )
					{
						$folder = $theme['folder_hot'];
						$folder_new = $theme['folder_hot_new'];
					}
					else
					{
						$folder = $theme['folder'];
						$folder_new = $theme['folder_new'];
					}
					break;
			}

			$newest_post_img = '';
			if ( $userdata['user_id'] != ANONYMOUS )
			{
				if ( $topic_rowset[$i]['post_time'] > $userdata['user_lastvisit'] ) 
				{
					if ( !empty($tracking_topics) || !empty($tracking_forums) || isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f_all']) )
					{
						$unread_topics = true;

						if ( !empty($tracking_topics[$topic_id]) )
						{
							if ( $tracking_topics[$topic_id] >= $topic_rowset[$i]['post_time'] )
							{
								$unread_topics = false;
							}
						}

						if ( !empty($tracking_forums[$forum_id]) )
						{
							if ( $tracking_forums[$forum_id] >= $topic_rowset[$i]['post_time'] )
							{
								$unread_topics = false;
							}
						}

						if ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f_all']) )
						{
							if ( $HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f_all'] >= $topic_rowset[$i]['post_time'] )
							{
								$unread_topics = false;
							}
						}

						if ( $unread_topics )
						{
							$folder_image = $folder_new;
							$folder_alt = $lang['New_posts'];

							$newest_post_img = '<a href="viewtopic.' . $phpEx . $SID . '&amp;t=' . $topic_id  . '&amp;view=newest">' . create_img($theme['goto_post_newest'], $lang['View_newest_post']) . '</a> ';
						}
						else
						{
							$folder_image = $folder;
							$folder_alt = ( $topic_rowset[$i]['topic_status'] == TOPIC_LOCKED ) ? $lang['Topic_locked'] : $lang['No_new_posts'];

							$newest_post_img = '';
						}
					}
					else
					{
						$folder_image = $folder_new;
						$folder_alt = ( $topic_rowset[$i]['topic_status'] == TOPIC_LOCKED ) ? $lang['Topic_locked'] : $lang['No_new_posts'];

						$newest_post_img = '<a href="viewtopic.' . $phpEx . $SID . '&amp;t=' . $topic_id . '&amp;view=newest">' . create_img($theme['goto_post_newest'], $lang['View_newest_post']) . '</a> ';
					}
				}
				else 
				{
					$folder_image = $folder;
					$folder_alt = ( $topic_rowset[$i]['topic_status'] == TOPIC_LOCKED ) ? $lang['Topic_locked'] : $lang['No_new_posts'];

					$newest_post_img = '';
				}
			}
			else
			{
				$folder_image = $folder;
				$folder_alt = ( $topic_rowset[$i]['topic_status'] == TOPIC_LOCKED ) ? $lang['Topic_locked'] : $lang['No_new_posts'];

				$newest_post_img = '';
			}
		}

		if ( $topic_rowset[$i]['topic_vote'] )
		{
			$topic_type .= $lang['Topic_Poll'] . ' ';
		}

		if ( ( $replies + 1 ) > $board_config['posts_per_page'] )
		{
			$total_pages = ceil( ( $replies + 1 ) / $board_config['posts_per_page'] );
			$goto_page = ' [ <img src=' . $theme['goto_post'] . ' alt="' . $lang['Goto_page'] . '" title="' . $lang['Goto_page'] . '" />' . $lang['Goto_page'] . ': ';

			$times = 1;
			for($j = 0; $j < $replies + 1; $j += $board_config['posts_per_page'])
			{
				$goto_page .= '<a href="viewtopic.' . $phpEx . $SID . '&amp;t=' . $topic_id . '&amp;start=' . $j . '">' . $times . '</a>';
				if ( $times == 1 && $total_pages > 4 )
				{
					$goto_page .= ' ... ';
					$times = $total_pages - 3;
					$j += ( $total_pages - 4 ) * $board_config['posts_per_page'];
				}
				else if ( $times < $total_pages )
				{
					$goto_page .= ', ';
				}
				$times++;
			}
			$goto_page .= ' ] ';
		}
		else
		{
			$goto_page = '';
		}
		
		$view_topic_url = 'viewtopic.' . $phpEx . $SID . '&amp;f=' . $forum_id . '&amp;t=' . $topic_id;

		$topic_author = ( $topic_rowset[$i]['user_id'] != ANONYMOUS ) ? '<a href="profile.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $topic_rowset[$i]['user_id'] . '">' : '';
		$topic_author .= ( $topic_rowset[$i]['user_id'] != ANONYMOUS ) ? $topic_rowset[$i]['username'] : ( ( $topic_rowset[$i]['post_username'] != '' ) ? $topic_rowset[$i]['post_username'] : $lang['Guest'] );

		$topic_author .= ( $topic_rowset[$i]['user_id'] != ANONYMOUS ) ? '</a>' : '';

		$first_post_time = create_date($board_config['default_dateformat'], $topic_rowset[$i]['topic_time'], $board_config['board_timezone']);

		$last_post_time = create_date($board_config['default_dateformat'], $topic_rowset[$i]['post_time'], $board_config['board_timezone']);

		$last_post_author = ( $topic_rowset[$i]['id2'] == ANONYMOUS ) ? ( ( $topic_rowset[$i]['post_username2'] != '' ) ? $topic_rowset[$i]['post_username2'] . ' ' : $lang['Guest'] . ' ' ) : '<a href="profile.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u='  . $topic_rowset[$i]['id2'] . '">' . $topic_rowset[$i]['user2'] . '</a>';

		$last_post_url = '<a href="viewtopic.' . $phpEx . $SID . '&amp;p=' . $topic_rowset[$i]['topic_last_post_id'] . '#' . $topic_rowset[$i]['topic_last_post_id'] . '">' . create_img($theme['goto_post_latest'], $lang['View_latest_post']) . '</a>';

		$views = $topic_rowset[$i]['topic_views'];
		$replies = $topic_rowset[$i]['topic_replies'];

		$topic_icon = ( !empty($topic_rowset[$i]['icons_url']) ) ? '<img src="' . $board_config['icons_path'] . '/' . $topic_rowset[$i]['icons_url'] . '" width="' . $topic_rowset[$i]['icons_width'] . '" height="' . $topic_rowset[$i]['icons_height'] . '" alt="" title="" />' : '';

		$topic_rating = ( !empty($topic_rowset[$i]['topic_rating']) ) ? '<img src=' . str_replace('{RATE}', $topic_rowset[$i]['topic_rating'], $theme['rating']) . ' alt="' . $topic_rowset[$i]['topic_rating'] . '" title="' . $topic_rowset[$i]['topic_rating'] . '" />' : '';

		$template->assign_block_vars('topicrow', array(
			'FORUM_ID' => $forum_id,
			'TOPIC_ID' => $topic_id,
			'TOPIC_FOLDER_IMG' => create_img($folder_image, $folder_alt), 
			'TOPIC_AUTHOR' => $topic_author, 
			'GOTO_PAGE' => $goto_page,
			'REPLIES' => $replies,
			'NEWEST_POST_IMG' => $newest_post_img, 
			'TOPIC_TITLE' => $topic_title,
			'TOPIC_TYPE' => $topic_type,
			'TOPIC_ICON' => $topic_icon, 
			'TOPIC_RATING' => $topic_rating, 
			'VIEWS' => $views,
			'FIRST_POST_TIME' => $first_post_time, 
			'LAST_POST_TIME' => $last_post_time, 
			'LAST_POST_AUTHOR' => $last_post_author, 
			'LAST_POST_IMG' => $last_post_url, 

			'S_ROW_COUNT' => $i, 

			'U_VIEW_TOPIC' => $view_topic_url)
		);

		$row_count++;
	}
}

//
// Dump out the page header and load viewforum template
//
$page_title = $lang['View_forum'] . ' - ' . $forum_data['forum_name'];

$nav_links['up'] = array(
	'url' => 'index.' . $phpEx . $SID,
	'title' => sprintf($lang['Forum_Index'], $board_config['sitename'])
);

include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	'body' => 'viewforum_body.html')
);
make_jumpbox('viewforum.'.$phpEx);

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>