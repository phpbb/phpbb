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

if (preg_match('/^c([0-9]+)$/', $_POST['f'], $m))
{
	$header_location = (@preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE'))) ? 'Refresh: 0; URL=' : 'Location: ';
	header($header_location . "index.$phpEx?sid=" . $_GET['sid'] . '&c=' . $m[1]);
	exit;
}

include($phpbb_root_path . 'common.'.$phpEx);

// Start initial var setup
if ( isset($_GET['f']) || isset($_POST['f']) )
{
	$forum_id = ( isset($_GET['f']) ) ? intval($_GET['f']) : intval($_POST['f']);
}
else
{
	$forum_id = '';
}

if ( isset($_GET['mark']) || isset($_POST['mark']) )
{
	$mark_read = ( isset($_POST['mark']) ) ? $_POST['mark'] : $_GET['mark'];
}
else
{
	$mark_read = '';
}

$start = ( isset($_GET['start']) ) ? intval($_GET['start']) : 0;
// End initial var setup

// Start session
$user->start();

// Check if the user has actually sent a forum ID with his/her request
// If not give them a nice error page.
if (empty($forum_id))
{
	message_die(MESSAGE, 'Forum_not_exist');
}

if (!$forum_branch = get_forum_branch($forum_id))
{
	message_die(MESSAGE, 'Forum_not_exist');
}

// Configure style, language, etc.
$user->setup(false, $forum_branch['forum_style']);
$auth->acl($user->data, $forum_id);

// Auth check
if (!$auth->acl_get('f_read', $forum_id))
{
	if ( !$user->data['user_id'] )
	{
		redirect("login.$phpEx$SID&redirect=viewforum.$phpEx&f=$forum_id" . ((isset($start)) ? "&start=$start" : ''));
	}

	trigger_error( $user->lang['Sorry_auth_read']);
}
// End of auth check

// Build subforums list if applicable
$type = 'parent';
$forum_rows = array();

$s_has_subforums = FALSE;
foreach ($forum_branch as $row)
{
	if ($type == 'parent')
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

		if ($row['forum_id'] == $forum_id)
		{
			$branch_root_id = 0;
			$forum_data = $row;
			$type = 'child';
		}
	}
	else
	{
		if ($row['parent_id'] == $forum_data['forum_id'])
		{
			// Root-level forum
			$forum_rows[] = $row;
			$parent_id = $row['forum_id'];

			if ($row['forum_status'] == ITEM_CATEGORY)
			{
				$branch_root_id = $row['forum_id'];
			}
			else
			{
				$s_has_subforums = TRUE;
			}
		}
		elseif ($row['parent_id'] == $branch_root_id)
		{
			// Forum directly under a category
			$forum_rows[] = $row;
			$parent_id = $row['forum_id'];

			if ($row['forum_status'] != ITEM_CATEGORY)
			{
				$s_has_subforums = TRUE;
			}
		}
		elseif ($row['forum_status'] != ITEM_CATEGORY)
		{
			// Subforum
			if ($auth->acl_get('f_list', $row['forum_id']))
			{
				$subforums[$parent_id][] = $row;
			}
		}
	}
}

// Topic read tracking cookie info
$mark_topics = ( isset($_COOKIE[$board_config['cookie_name'] . '_t']) ) ? unserialize(stripslashes($_COOKIE[$board_config['cookie_name'] . '_t'])) : array();
$mark_forums = ( isset($_COOKIE[$board_config['cookie_name'] . '_f']) ) ? unserialize(stripslashes($_COOKIE[$board_config['cookie_name'] . '_f'])) : array();

// Handle marking posts
if ( $mark_read == 'topics' )
{
	if ( $user->data['user_id'] )
	{
		$mark_forums[$forum_id] = time();

		setcookie($board_config['cookie_name'] . '_f', serialize($mark_forums), 0, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);

		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="3;url=' . "viewforum.$phpEx$SID&amp;f=$forum_id" . '">')
		);
	}

	$message = $user->lang['Topics_marked_read'] . '<br /><br />' . sprintf($user->lang['Click_return_forum'], '<a href="' . "viewforum.$phpEx$SID&amp;f=$forum_id" . '">', '</a> ');
	trigger_error($message);
}
// End handle marking posts

// Do the forum Prune
if ( $auth->acl_get('m_prune', $forum_id) && $board_config['prune_enable'] )
{
	if ( $forum_data['prune_next'] < time() && $forum_data['prune_enable'] )
	{
		require($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
		auto_prune($forum_id);
	}
}
// End of forum prune

// Forum rules, subscription info and word censors
$s_watching_forum = '';
$s_watching_forum_img = '';
watch_topic_forum('forum', $s_watching_forum, $s_watching_forum_img, $user->data['user_id'], $forum_id);

$s_forum_rules = '';
get_forum_rules('forum', $s_forum_rules, $forum_id);

$orig_word = array();
$replacement_word = array();
obtain_word_list($orig_word, $replacement_word);

// Topic ordering options
$previous_days = array(0 => $user->lang['All_Topics'], 1 => $user->lang['1_Day'], 7 => $user->lang['7_Days'], 14 => $user->lang['2_Weeks'], 30 => $user->lang['1_Month'], 90 => $user->lang['3_Months'], 180 => $user->lang['6_Months'], 364 => $user->lang['1_Year']);
$sort_by_text = array('a' => $user->lang['Author'], 't' => $user->lang['Post_time'], 'r' => $user->lang['Replies'], 's' => $user->lang['Subject'], 'v' => $user->lang['Views']);
$sort_by = array('a' => 'u.username', 't' => 't.topic_last_post_id', 'r' => 't.topic_replies', 's' => 't.topic_title', 'v' => 't.topic_views');

if ( isset($_POST['sort']) )
{
	if ( !empty($_POST['sort_days']) )
	{
		$sort_days = ( !empty($_POST['sort_days']) ) ? intval($_POST['sort_days']) : intval($_GET['sort_days']);
		$min_topic_time = time() - ( $sort_days * 86400 );

		//
		// ref type on as rows as topics ... also not great
		//
		$sql = "SELECT COUNT(topic_id) AS forum_topics
			FROM " . TOPICS_TABLE . "
			WHERE  forum_id = $forum_id
				AND topic_last_post_time >= $min_topic_time";
		$result = $db->sql_query($sql);

		$start = 0;
		$topics_count = ( $row = $db->sql_fetchrow($result) ) ? $row['forum_topics'] : 0;
		$limit_topics_time = "AND t.topic_last_post_time >= $min_topic_time";
	}
	else
	{
		$topics_count = ( $forum_data['forum_topics'] ) ? $forum_data['forum_topics'] : 1;
	}

	$sort_key = ( isset($_POST['sort_key']) ) ? $_POST['sort_key'] : $_GET['sort_key'];
	$sort_dir = ( isset($_POST['sort_dir']) ) ? $_POST['sort_dir'] : $_GET['sort_dir'];
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
$select_sort_dir .= ( $sort_dir == 'a' ) ? '<option value="a" selected="selected">' . $user->lang['Ascending'] . '</option><option value="d">' . $user->lang['Descending'] . '</option>' : '<option value="a">' . $user->lang['Ascending'] . '</option><option value="d" selected="selected">' . $user->lang['Descending'] . '</option>';
$select_sort_dir .= '</select>';

$post_alt = ( $forum_data['forum_status'] == FORUM_LOCKED ) ? 'Forum_locked' : 'Post_new_topic';

// Basic pagewide vars
$template->assign_vars(array(
	'FORUM_ID' 		=> $forum_id,
	'FORUM_NAME'	=> $forum_data['forum_name'],
	'POST_IMG' 		=> ( $forum_data['forum_status'] == FORUM_LOCKED ) ? $user->img('post_locked', $post_alt) : $user->img('post_new', $post_alt),
	'PAGINATION'	=> generate_pagination("viewforum.$phpEx$SID&amp;f=$forum_id&amp;topicdays=$topic_days", $topics_count, $board_config['topics_per_page'], $start),
	'PAGE_NUMBER'	=> sprintf($user->lang['Page_of'], ( floor( $start / $board_config['topics_per_page'] ) + 1 ), ceil( $topics_count / $board_config['topics_per_page'] )),

	'FOLDER_IMG' 			=> $user->img('folder', 'No_new_posts'),
	'FOLDER_NEW_IMG' 		=> $user->img('folder_new', 'New_posts'),
	'FOLDER_HOT_IMG' 		=> $user->img('folder_hot', 'No_new_posts_hot'),
	'FOLDER_HOT_NEW_IMG'	=> $user->img('folder_hot_new', 'New_posts_hot'),
	'FOLDER_LOCKED_IMG' 	=> $user->img('folder_locked', 'No_new_posts_locked'),
	'FOLDER_LOCKED_NEW_IMG' => $user->img('folder_locked_new', 'New_posts_locked'),
	'FOLDER_STICKY_IMG' 	=> $user->img('folder_sticky', 'Post_Sticky'),
	'FOLDER_STICKY_NEW_IMG' => $user->img('folder_sticky_new', 'Post_Sticky'),
	'FOLDER_ANNOUNCE_IMG' 	=> $user->img('folder_announce', 'Post_Announcement'),
	'FOLDER_ANNOUNCE_NEW_IMG' => $user->img('folder_announce_new', 'Post_Announcement'),

	'L_TOPICS' 				=> $user->lang['Topics'],
	'L_REPLIES' 			=> $user->lang['Replies'],
	'L_VIEWS' 				=> $user->lang['Views'],
	'L_POSTS' 				=> $user->lang['Posts'],
	'L_LASTPOST' 			=> $user->lang['Last_Post'],
	'L_RATING' 				=> $user->lang['Rating'],
	'L_VIEW_MODERATORS' 	=> $user->lang['View_moderators'],
	'L_DISPLAY_TOPICS' 		=> $user->lang['Display_topics'],
	'L_SORT_BY' 			=> $user->lang['Sort_by'],
	'L_MARK_TOPICS_READ' 	=> $user->lang['Mark_all_topics'],
	'L_NO_NEW_POSTS' 		=> $user->lang['No_new_posts'],
	'L_NEW_POSTS' 			=> $user->lang['New_posts'],
	'L_NO_NEW_POSTS_LOCKED' => $user->lang['No_new_posts_locked'],
	'L_NEW_POSTS_LOCKED' 	=> $user->lang['New_posts_locked'],
	'L_NO_NEW_POSTS_HOT' 	=> $user->lang['No_new_posts_hot'],
	'L_NEW_POSTS_HOT' 		=> $user->lang['New_posts_hot'],
	'L_ANNOUNCEMENT' 		=> $user->lang['Post_Announcement'],
	'L_STICKY' 				=> $user->lang['Post_Sticky'],
	'L_POSTED' 				=> $user->lang['Posted'],
	'L_JOINED' 				=> $user->lang['Joined'],
	'L_AUTHOR' 				=> $user->lang['Author'],
	'L_NO_TOPICS' 			=> ( $forum_data['forum_status'] == FORUM_LOCKED ) ? $user->lang['Forum_locked'] : $user->lang['No_topics_post_one'],
	'L_GOTO_PAGE' 			=> $user->lang['Goto_page'],

	'S_SELECT_SORT_DIR' => $select_sort_dir,
	'S_SELECT_SORT_KEY' => $select_sort,
	'S_SELECT_SORT_DAYS'=> $select_sort_days,
	'S_AUTH_LIST' 		=> $s_forum_rules,
	'S_WATCH_FORUM' 	=> $s_watching_forum,
	'S_FORUM_ACTION' 	=> 'viewforum.' . $phpEx . $SID . '&amp;f=' . $forum_id . "&amp;start=$start",

	'U_POST_NEW_TOPIC'	=> 'posting.' . $phpEx . $SID . '&amp;mode=newtopic&amp;f=' . $forum_id,
	'U_VIEW_MODERATORS'	=> 'memberslist.' . $phpEx . $SID . '&amp;mode=moderators&amp;f=' . $forum_id,
	'U_MARK_READ' 		=> 'viewforum.' . $phpEx . $SID . '&amp;f=' . $forum_id . '&amp;mark=topics')
);

// Do we have subforums? if so, let's include this harmless file
if ($s_has_subforums)
{
	$template->assign_vars(array(
		'S_HAS_SUBFORUM'	=>	TRUE,
		'L_SUBFORUM'		=>	(count($forum_rows) == 1) ? $user->lang['Subforum'] : $user->lang['Subforums']
	));

	$root_id = $forum_id;
	include($phpbb_root_path . 'includes/forums_display.' . $phpEx);
}

// Grab all the basic data. If we're not on page 1 we also grab any
// announcements that may exist.
$total_topics = 0;
$topic_rowset = array();

if ( $start )
{
	$sql = "SELECT t.*, i.icons_url, i.icons_width, i.icons_height, u.username, u.user_id, u2.username as user2, u2.user_id as id2
		FROM " . TOPICS_TABLE . " t, " . ICONS_TABLE . " i, " . USERS_TABLE . " u, " . USERS_TABLE . " u2
		WHERE t.forum_id = $forum_id
			AND t.topic_type = " . POST_ANNOUNCE . "
			AND i.icons_id = t.topic_icon
			AND u.user_id = t.topic_poster
			AND u2.user_id = t.topic_last_poster_id
		ORDER BY $sort_order
		LIMIT " . $board_config['topics_per_page'];
	$result = $db->sql_query($sql);

	while( $row = $db->sql_fetchrow($result) )
	{
		$topic_rowset[] = $row;
		$total_topics++;
	}
	$db->sql_freeresult($result);
}

// topic icon join requires full table scan ... not good ... order by is a killer too
/*$sql = "SELECT t.*, i.icons_url, i.icons_width, i.icons_height, u.username, u.user_id, u2.username as user2, u2.user_id as id2
	FROM " . TOPICS_TABLE . " t, " . ICONS_TABLE . " i, " . USERS_TABLE . " u, " . USERS_TABLE . " u2
	WHERE t.forum_id = $forum_id
		AND i.icons_id = t.topic_icon
		AND u.user_id = t.topic_poster
		AND u2.user_id = t.topic_last_poster_id
		$limit_topics_time
	ORDER BY t.topic_type DESC, $sort_order
	LIMIT $start, " . $board_config['topics_per_page'];*/
$sql = "SELECT t.*, u.username, u.user_id, u2.username as user2, u2.user_id as id2
	FROM " . TOPICS_TABLE . " t, " . USERS_TABLE . " u, " . USERS_TABLE . " u2
	WHERE t.forum_id = $forum_id
		AND u.user_id = t.topic_poster
		AND u2.user_id = t.topic_last_poster_id
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

// Okay, lets dump out the page ...
if ( $total_topics )
{
	for($i = 0; $i < $total_topics; $i++)
	{
		$topic_id = $topic_rowset[$i]['topic_id'];

		// Type and folder
		$topic_type = '';
		if ( $topic_rowset[$i]['topic_status'] == TOPIC_MOVED )
		{
			$topic_type = $user->lang['Topic_Moved'] . ' ';
			$topic_id = $topic_rowset[$i]['topic_moved_id'];

			$folder_image =  'folder';
			$folder_alt = 'Topic_Moved';
			$newest_post_img = '';
		}
		else
		{
			switch ( $topic_rowset[$i]['topic_type'] )
			{
				case POST_ANNOUNCE:
					$topic_type = $user->lang['Topic_Announcement'] . ' ';
					$folder = 'folder_announce';
					$folder_new = 'folder_announce_new';
					break;
				case POST_STICKY:
					$topic_type = $user->lang['Topic_Sticky'] . ' ';
					$folder = 'folder_sticky';
					$folder_new = 'folder_sticky_new';
					break;
				case TOPIC_LOCKED:
					$folder = 'folder_locked';
					$folder_new = 'folder_locked_new';
					break;
				default:
					if ( $replies >= $board_config['hot_threshold'] )
					{
						$folder = 'folder_hot';
						$folder_new = 'folder_hot_new';
					}
					else
					{
						$folder = 'folder';
						$folder_new = 'folder_new';
					}
					break;
			}

			$unread_topic = false;
			if ( $user->data['user_id'] && $topic_rowset[$i]['topic_last_post_time'] > $user->data['user_lastvisit'] )
			{
				$unread_topic = true;
			}

			$newest_post_img = ( $unread_topic ) ? '<a href="viewtopic.' . $phpEx . $SID . '&amp;t=' . $topic_id  . '&amp;view=newest">' . $user->img('goto_post_newest', 'View_newest_post') . '</a> ' : '';
			$folder_img = ( $unread_topic ) ? $folder_new : $folder;
			$folder_alt = ( $unread_topic ) ? 'New_posts' : ( ( $topic_rowset[$i]['topic_status'] == TOPIC_LOCKED ) ? 'Topic_locked' : 'No_new_posts' );

		}

		if ( $topic_rowset[$i]['topic_vote'] )
		{
			$topic_type .= $user->lang['Topic_Poll'] . ' ';
		}

		// Goto message
		if ( ( $replies + 1 ) > $board_config['posts_per_page'] )
		{
			$total_pages = ceil( ( $replies + 1 ) / $board_config['posts_per_page'] );
			$goto_page = ' [ <img src=' . $theme['goto_post'] . ' alt="' . $user->lang['Goto_page'] . '" title="' . $user->lang['Goto_page'] . '" />' . $user->lang['Goto_page'] . ': ';

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

		// Generate all the URIs ...
		$view_topic_url = 'viewtopic.' . $phpEx . $SID . '&amp;f=' . $forum_id . '&amp;t=' . $topic_id;

		$topic_author = ( $topic_rowset[$i]['user_id'] != ANONYMOUS ) ? '<a href="profile.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $topic_rowset[$i]['user_id'] . '">' : '';
		$topic_author .= ( $topic_rowset[$i]['user_id'] != ANONYMOUS ) ? $topic_rowset[$i]['username'] : ( ( $topic_rowset[$i]['topic_first_poster_name'] != '' ) ? $topic_rowset[$i]['topic_first_poster_name'] : $user->lang['Guest'] );

		$topic_author .= ( $topic_rowset[$i]['user_id'] ) ? '</a>' : '';

		$first_post_time = $user->format_date($topic_rowset[$i]['topic_time'], $board_config['board_timezone']);

		$last_post_time = $user->format_date($topic_rowset[$i]['topic_last_post_time']);

		$last_post_author = ( !$topic_rowset[$i]['id2'] ) ? ( ( $topic_rowset[$i]['topic_last_poster_name'] != '' ) ? $topic_rowset[$i]['topic_last_poster_name'] . ' ' : $user->lang['Guest'] . ' ' ) : '<a href="profile.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u='  . $topic_rowset[$i]['topic_last_poster_id'] . '">' . $topic_rowset[$i]['user2'] . '</a>';

		$last_post_url = '<a href="viewtopic.' . $phpEx . $SID . '&amp;f=' . $forum_id . '&amp;p=' . $topic_rowset[$i]['topic_last_post_id'] . '#' . $topic_rowset[$i]['topic_last_post_id'] . '">' . $user->img('goto_post_latest', 'View_latest_post') . '</a>';

		//
		// Send vars to template
		//
		$template->assign_block_vars('topicrow', array(
			'FORUM_ID' 			=> $forum_id,
			'TOPIC_ID' 			=> $topic_id,
			'TOPIC_FOLDER_IMG' 	=> $user->img($folder_img, $folder_alt),
			'TOPIC_AUTHOR' 		=> $topic_author,
			'NEWEST_POST_IMG' 	=> $newest_post_img,
			'FIRST_POST_TIME' 	=> $first_post_time,
			'LAST_POST_TIME'	=> $last_post_time,
			'LAST_POST_AUTHOR' 	=> $last_post_author,
			'LAST_POST_IMG' 	=> $last_post_url,
			'GOTO_PAGE' 		=> $goto_page,
			'REPLIES' 			=> $topic_rowset[$i]['topic_replies'],
			'VIEWS' 			=> $topic_rowset[$i]['topic_views'],
			'TOPIC_TITLE' 		=> ( count($orig_word) ) ? preg_replace($orig_word, $replacement_word, $topic_rowset[$i]['topic_title']) : $topic_rowset[$i]['topic_title'],
			'TOPIC_TYPE' 		=> $topic_type,
			'TOPIC_ICON' 		=> ( !empty($topic_rowset[$i]['icons_url']) ) ? '<img src="' . $board_config['icons_path'] . '/' . $topic_rowset[$i]['icons_url'] . '" width="' . $topic_rowset[$i]['icons_width'] . '" height="' . $topic_rowset[$i]['icons_height'] . '" alt="" title="" />' : '',
			'TOPIC_RATING' 		=> ( !empty($topic_rowset[$i]['topic_rating']) ) ? '<img src=' . str_replace('{RATE}', $topic_rowset[$i]['topic_rating'], $theme['rating']) . ' alt="' . $topic_rowset[$i]['topic_rating'] . '" title="' . $topic_rowset[$i]['topic_rating'] . '" />' : '',

			'S_ROW_COUNT'	=> $i,

			'U_VIEW_TOPIC'	=> $view_topic_url)
		);
	}
}

if ($user->data['user_id'] != ANONYMOUS)
{
	setcookie($board_config['cookie_name'] . '_t', serialize($mark_topics), 0, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
}

// Dump out the page header and load viewforum template
$page_title = $user->lang['View_forum'] . ' - ' . $forum_data['forum_name'];

$nav_links['up'] = array(
	'url' => 'index.' . $phpEx . $SID,
	'title' => sprintf($user->lang['Forum_Index'], $board_config['sitename'])
);

include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	'body' => 'viewforum_body.html'
));
make_jumpbox('viewforum.'.$phpEx . $SID, $forum_id);

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>