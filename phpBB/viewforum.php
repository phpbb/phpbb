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


// Start initial var setup
$forum_id = (isset($_GET['f'])) ? max(intval($_GET['f']), 0) : 0;
$start = (isset($_GET['start'])) ? max(intval($_GET['start']), 0) : 0;
$mark_read = (!empty($_GET['mark'])) ? $_GET['mark'] : '';

$sort_days = (!empty($_REQUEST['st'])) ? max(intval($_REQUEST['st']), 0) : 0;
$sort_key = (!empty($_REQUEST['sk'])) ? htmlspecialchars($_REQUEST['sk']) : 't';
$sort_dir = (!empty($_REQUEST['sd'])) ? htmlspecialchars($_REQUEST['sd']) : 'd';


// Start session
//$user->fetch_data(array());
$user->start();


// Check if the user has actually sent a forum ID with his/her request
// If not give them a nice error page.
if (!$forum_id)
{
	trigger_error('NO_FORUM');
}


// Grab appropriate forum data
if ($user->data['user_id'] == ANONYMOUS)
{
	$sql = 'SELECT * 
		FROM ' . FORUMS_TABLE . ' 
		WHERE forum_id = ' . $forum_id;
}
else
{
	switch (SQL_LAYER)
	{
		case 'oracle':
			//TODO
			break;

		default:
/*
			$sql = 'SELECT f.*, tw.topics_list, fw.notify_status
					FROM ' . FORUMS_TABLE . ' f
					LEFT JOIN ' . TOPICS_PREFETCH_TABLE . " tw ON tw.start = $start AND tw.forum_id = f.forum_id
					LEFT JOIN " . FORUMS_WATCH_TABLE . ' fw ON fw.user_id = ' . $user->data['user_id'] . ' AND f.forum_id = fw.forum_id
					WHERE f.forum_id = ' . $forum_id;
*/
			$sql = 'SELECT f.*, fw.notify_status 
					FROM (' . FORUMS_TABLE . ' f
					LEFT JOIN ' . FORUMS_WATCH_TABLE . ' fw ON fw.forum_id = f.forum_id
						AND fw.user_id = ' . $user->data['user_id'] . ')
					WHERE f.forum_id = ' . $forum_id;
			// UNION if necessary?
/*			$sql = "SELECT * 
					FROM " . FORUMS_TABLE . "
					WHERE forum_id = $forum_id 
					UNION
					SELECT notify_status, NULL, NULL, ...
					FROM " .  FORUMS_WATCH_TABLE . " 
					WHERE forum_id = $forum_id 
						AND user_id = " . $user->data['user_id'];*/
	}
}
$result = $db->sql_query($sql);
if (!$forum_data = $db->sql_fetchrow($result))
{
	trigger_error('NO_FORUM');
}
$db->sql_freeresult($result);


// Configure style, language, etc.
$user->setup(false, $forum_data['forum_style']);
$auth->acl($user->data, $forum_id);


// Permissions check
if (!$auth->acl_gets('f_read', 'm_', 'a_', $forum_id))
{
	if ($user->data['user_id'] == ANONYMOUS)
	{
		redirect("login.$phpEx$SID&redirect=viewforum.$phpEx&f=$forum_id" . ((isset($start)) ? "&start=$start" : ''));
	}

	trigger_error('SORRY_AUTH_READ');
}


// Build navigation links
generate_forum_nav($forum_data);


// Do we have subforums?
$moderators = array();

if ($forum_data['left_id'] != $forum_data['right_id'] - 1)
{
	include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
	display_forums($forum_data);
}
else
{
	$template->assign_var('S_HAS_SUBFORUM', FALSE);
	get_moderators($moderators, $forum_id);
}


// Output forum listing if it is postable
if ($forum_data['forum_postable'])
{
	// Handle marking posts
	if ($mark_read == 'topics')
	{
		if ($user->data['user_id'] != ANONYMOUS)
		{
			markread('mark', $forum_id);

			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="3;url=' . "viewforum.$phpEx$SID&amp;f=$forum_id" . '">')
			);
		}

		$message = $user->lang['TOPICS_MARKED_READ'] . '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], '<a href="' . "viewforum.$phpEx$SID&amp;f=$forum_id" . '">', '</a> ');
		trigger_error($message);
	}


	// Do the forum Prune - cron type job ...
	if ($config['prune_enable'] && $auth->acl_get('a_'))
	{
		if ($forum_data['prune_next'] < time() && $forum_data['prune_enable'])
		{
			require($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
			auto_prune($forum_id);
		}
	}


	// Forum rules, subscription info and word censors
	$s_watching_forum = $s_watching_forum_img = '';
	$notify_status = (isset($forum_data['notify_status'])) ? $forum_data['notify_status'] : NULL;
	watch_topic_forum('forum', $s_watching_forum, $s_watching_forum_img, $user->data['user_id'], $forum_id, $notify_status);

	$s_forum_rules = '';
	gen_forum_rules('forum', $forum_id);

	$censors = array();
	obtain_word_list($censors);


	// Topic ordering options
	$limit_days = array(0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);

	$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 'r' => $user->lang['REPLIES'], 's' => $user->lang['SUBJECT'], 'v' => $user->lang['VIEWS']);
	$sort_by_sql = array('a' => 't.topic_first_poster_name', 't' => 't.topic_last_post_id', 'r' => 't.topic_replies', 's' => 't.topic_title', 'v' => 't.topic_views');

	$s_limit_days = $s_sort_key = $s_sort_dir = '';
	gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir);

	// Limit topics to certain time frame, obtain correct topic count
	if ($sort_days) 
	{
		$min_topic_time = time() - ($sort_days * 86400);

		// ref type on as rows as topics ... also not great
		$sql = "SELECT COUNT(topic_id) AS forum_topics
			FROM " . TOPICS_TABLE . "
			WHERE  forum_id = $forum_id
				AND topic_last_post_time >= $min_topic_time";
		$result = $db->sql_query($sql);

		$start = 0;
		$topics_count = ($row = $db->sql_fetchrow($result)) ? $row['forum_topics'] : 0;
		$limit_topics_time = "AND t.topic_last_post_time >= $min_topic_time";
	}
	else
	{
		$topics_count = ($forum_data['forum_topics']) ? $forum_data['forum_topics'] : 1;
		$limit_topics_time = '';
	}

	// Select the sort order
	$sort_order_sql = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');


	// Basic pagewide vars
	$post_alt = (intval($forum_data['forum_status']) == ITEM_LOCKED) ? 'FORUM_LOCKED' : 'POST_NEW_TOPIC';

	$template->assign_vars(array(
		'PAGINATION'	=> generate_pagination("viewforum.$phpEx$SID&amp;f=$forum_id&amp;st=$sort_days&amp;sk=$sort_key&amp;sd=$sort_dir", $topics_count, $config['topics_per_page'], $start),
		'PAGE_NUMBER'	=> on_page($topics_count, $config['topics_per_page'], $start), 
		'MOD_CP' 		=> ($auth->acl_gets('m_', 'a_', $forum_id)) ? sprintf($user->lang['MCP'], '<a href="mcp.' . $phpEx . '?sid=' . $user->session_id . '&amp;f=' . $forum_id . '">', '</a>') : '', 
		'MODERATORS'	=> (!empty($moderators[$forum_id])) ? implode(', ', $moderators[$forum_id]) : $user->lang['NONE'],

		'POST_IMG' 				=> (intval($forum_data['forum_status']) == ITEM_LOCKED) ? $user->img('post_locked', $post_alt) : $user->img('post_new', $post_alt),
		'FOLDER_IMG' 			=> $user->img('folder', 'NO_NEW_POSTS'),
		'FOLDER_NEW_IMG' 		=> $user->img('folder_new', 'NEW_POSTS'),
		'FOLDER_HOT_IMG' 		=> $user->img('folder_hot', 'NO_NEW_POSTS_HOT'),
		'FOLDER_HOT_NEW_IMG'	=> $user->img('folder_hot_new', 'NEW_POSTS_HOT'),
		'FOLDER_LOCKED_IMG' 	=> $user->img('folder_locked', 'NO_NEW_POSTS_LOCKED'),
		'FOLDER_LOCKED_NEW_IMG' => $user->img('folder_locked_new', 'NEW_POSTS_LOCKED'),
		'FOLDER_STICKY_IMG' 	=> $user->img('folder_sticky', 'POST_STICKY'),
		'FOLDER_STICKY_NEW_IMG' => $user->img('folder_sticky_new', 'POST_STICKY'),
		'FOLDER_ANNOUNCE_IMG' 	=> $user->img('folder_announce', 'POST_ANNOUNCEMENT'),
		'FOLDER_ANNOUNCE_NEW_IMG'=> $user->img('folder_announce_new', 'POST_ANNOUNCEMENT'),

		'L_NO_TOPICS' 			=> ($forum_data['forum_status'] == ITEM_LOCKED) ? $user->lang['POST_FORUM_LOCKED'] : $user->lang['NO_TOPICS'],

		'S_IS_POSTABLE'		=>	TRUE,
		'S_SELECT_SORT_DIR' => $s_sort_dir,
		'S_SELECT_SORT_KEY' => $s_sort_key,
		'S_SELECT_SORT_DAYS'=> $s_limit_days,
		'S_TOPIC_ICONS'		=> ($forum_data['enable_icons']) ? true : false, 
		'S_WATCH_FORUM' 	=> $s_watching_forum,
		'S_FORUM_ACTION' 	=> 'viewforum.' . $phpEx . $SID . '&amp;f=' . $forum_id . "&amp;start=$start",
		'S_SEARCHBOX_ACTION'=> "search.$phpEx$SID&amp;f=$forum_id", 

		'U_POST_NEW_TOPIC'	=> 'posting.' . $phpEx . $SID . '&amp;mode=post&amp;f=' . $forum_id,
		'U_MARK_READ' 		=> 'viewforum.' . $phpEx . $SID . '&amp;f=' . $forum_id . '&amp;mark=topics')
	);


	// Grab icons
	$icons = array();
	obtain_icons($icons);


	// Grab all topic data
	$total_topics = 0;
	$topics_list = '';
	$row_ary = array();

//	if (empty($forum_data['topics_list']))
//	{
		$sql = 'SELECT t.*, lr.lastread_time, lr.lastread_type
			FROM (' . TOPICS_TABLE . ' t
			LEFT JOIN ' . LASTREAD_TABLE . ' lr ON lr.topic_id = t.topic_id 
				AND lr.user_id = ' . $user->data['user_id'] . ")
			WHERE (t.forum_id = $forum_id 
				OR t.forum_id = 0)
				AND t.topic_type = " . POST_ANNOUNCE . "
			ORDER BY $sort_order_sql
			LIMIT " . $config['topics_per_page'];
		$result = $db->sql_query($sql);

		while($row = $db->sql_fetchrow($result))
		{
//			$topics_list .= '.' . str_pad(base_convert($row['topic_id'], 10, 36), 5, '0', STR_PAD_LEFT);
			$row_ary[] = $row;
			$total_topics++;
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT t.*, lr.lastread_time, lr.lastread_type
			FROM (' . TOPICS_TABLE . ' t
			LEFT JOIN ' . LASTREAD_TABLE . ' lr ON lr.topic_id = t.topic_id
				AND lr.user_id = ' . $user->data['user_id'] . ")
			WHERE t.forum_id = $forum_id 
				AND t.topic_approved = 1 
				AND t.topic_type <> " . POST_ANNOUNCE . " 
				$limit_topics_time
			ORDER BY t.topic_type DESC, $sort_order_sql
			LIMIT $start, " . $config['topics_per_page'];
/*	}
	else
	{

		$topic_ids = array();
		preg_match_all('/.{5,5}/', $forum_data['topics_list'], $m);// explode('.' ?
		foreach ($m[0] as $topic_id)
		{
			$topic_ids[] = base_convert($topic_id, 36, 10);
		}

		$sql = "SELECT t.*, u.username, u.user_id, u2.username as user2, u2.user_id as id2
			FROM " . TOPICS_TABLE . " t, " . USERS_TABLE . " u, " . USERS_TABLE . " u2
			WHERE t.topic_id IN (" . implode(', ', $topic_ids) . ")
				AND u.user_id = t.topic_poster
				AND u2.user_id = t.topic_last_poster_id
			ORDER BY $sort_order";
	}*/
	$result = $db->sql_query($sql);

	while($row = $db->sql_fetchrow($result))
	{
//		$topics_list .= str_pad(base_convert($row['topic_id'], 10, 36), 5, '0', STR_PAD_LEFT);
		$row_ary[] = $row;
		$total_topics++;
	}
	$db->sql_freeresult($result);

/*
	if (empty($forum_data['topics_list']) && !empty($topics_list))
	{
		$sql = 'INSERT INTO ' . TOPICS_PREFETCH_TABLE . " (forum_id, start, sort_key, sort_dir, topics_list) 
			VALUES ($forum_id, $start, '$sort_key', '$sort_dir', '$topics_list')";
		$db->sql_query($sql);
	}
*/

	// Okay, lets dump out the page ...
	if ($total_topics)
	{
		$i = $s_type_switch = 0;
		foreach ($row_ary as $row)
		{
			$topic_id = $row['topic_id'];

			// Type and folder
			$topic_type = '';
			if ($row['topic_status'] == ITEM_MOVED)
			{
				$topic_type = $user->lang['TOPIC_MOVED'] . ' ';
				$topic_id = $row['topic_moved_id'];

				$folder_image =  'folder';
				$folder_alt = 'Topic_Moved';
				$newest_post_img = '';
			}
			else
			{
				switch ($row['topic_type'])
				{
					case POST_ANNOUNCE:
						$topic_type = $user->lang['TOPIC_ANNOUNCEMENT'] . ' ';
						$folder = 'folder_announce';
						$folder_new = 'folder_announce_new';
						break;

					case POST_STICKY:
						$topic_type = $user->lang['TOPIC_STICKY'] . ' ';
						$folder = 'folder_sticky';
						$folder_new = 'folder_sticky_new';
						break;

					case ITEM_LOCKED:
						$folder = 'folder_locked';
						$folder_new = 'folder_locked_new';
						break;

					default:
						if ($replies >= intval($config['hot_threshold']))
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

				$unread_topic = true;

				if ($user->data['user_id'] != ANONYMOUS && 
					($row['topic_last_post_time'] <= $row['lastread_time'] || 
					$row['topic_last_post_time'] < (time() - $config['lastread']))
				)
				{
					$unread_topic = false;
				}


				$newest_post_img = ($unread_topic) ? '<a href="viewtopic.' . $phpEx . $SID . '&amp;f=' . $forum_id . '&amp;t=' . $topic_id  . '&amp;view=newest">' . $user->img('goto_post_newest', 'VIEW_NEWEST_POST') . '</a> ' : '';
				$folder_img = ($unread_topic) ? $folder_new : $folder;
				$folder_alt = ($unread_topic) ? 'NEW_POSTS' : (($row['topic_status'] == ITEM_LOCKED) ? 'TOPIC_LOCKED' : 'NO_NEW_POSTS');


				if ($row['lastread_type'] == LASTREAD_POSTED)
				{
					$folder_img .= '_posted';
				}
			}


			if (intval($row['poll_start']))
			{
				$topic_type .= $user->lang['Topic_Poll'] . ' ';
			}


			// Goto message generation
			$replies = $row['topic_replies'];

			if (($replies + 1) > intval($config['posts_per_page']))
			{
				$total_pages = ceil(($replies + 1) / intval($config['posts_per_page']));
				$goto_page = ' [ ' . $user->img('goto_post', 'GOTO_PAGE') . $user->lang['GOTO_PAGE'] . ': ';

				$times = 1;
				for($j = 0; $j < $replies + 1; $j += intval($config['posts_per_page']))
				{
					$goto_page .= '<a href="viewtopic.' . $phpEx . $SID . '&amp;t=' . $topic_id . '&amp;start=' . $j . '">' . $times . '</a>';
					if ($times == 1 && $total_pages > 4)
					{
						$goto_page .= ' ... ';
						$times = $total_pages - 3;
						$j += ($total_pages - 4) * intval($config['posts_per_page']);
					}
					else if ($times < $total_pages)
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

			$last_post_img = '<a href="viewtopic.' . $phpEx . $SID . '&amp;f=' . $forum_id . '&amp;p=' . $row['topic_last_post_id'] . '#' . $row['topic_last_post_id'] . '">' . $user->img('goto_post_latest', 'VIEW_LATEST_POST') . '</a>';

			$topic_author = ($row['topic_poster'] != ANONYMOUS) ? '<a href="ucp.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $row['topic_poster'] . '">' : '';
			$topic_author .= ($row['topic_poster'] != ANONYMOUS) ? $row['topic_first_poster_name'] : (($row['topic_first_poster_name'] != '') ? $row['topic_first_poster_name'] : $user->lang['GUEST']);
			$topic_author .= ($row['topic_poster'] != ANONYMOUS) ? '</a>' : '';

			$last_post_author = ($row['topic_last_poster_id'] == ANONYMOUS) ? (($row['topic_last_poster_name'] != '') ? $row['topic_last_poster_name'] . ' ' : $user->lang['GUEST'] . ' ') : '<a href="ucp.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u='  . $row['topic_last_poster_id'] . '">' . $row['topic_last_poster_name'] . '</a>';

			$first_post_time = $user->format_date($row['topic_time'], $config['board_timezone']);

			$last_post_time = $user->format_date($row['topic_last_post_time']);


			// This will allow the style designer to output a different header 
			// or even seperate the list of announcements from sticky and normal
			// topics
			$s_type_switch_test = ($row['topic_type'] == POST_ANNOUNCE) ? 1 : 0;


			// Send vars to template
			$template->assign_block_vars('topicrow', array(
				'FORUM_ID' 			=> $forum_id,
				'TOPIC_ID' 			=> $topic_id,
				'TOPIC_AUTHOR' 		=> $topic_author,
				'FIRST_POST_TIME' 	=> $first_post_time,
				'LAST_POST_TIME'	=> $last_post_time,
				'LAST_POST_AUTHOR' 	=> $last_post_author,
				'GOTO_PAGE' 		=> $goto_page, 
				'REPLIES' 			=> $row['topic_replies'],
				'VIEWS' 			=> $row['topic_views'],
				'TOPIC_TITLE' 		=> (!empty($censors)) ? preg_replace($censors['match'], $censors['replace'], $row['topic_title']) : $row['topic_title'],
				'TOPIC_TYPE' 		=> $topic_type,

				'LAST_POST_IMG' 	=> $last_post_img,
				'NEWEST_POST_IMG' 	=> $newest_post_img,
				'TOPIC_FOLDER_IMG' 	=> $user->img($folder_img, $folder_alt),
				'TOPIC_ICON_IMG'	=> (!empty($icons[$row['icon_id']])) ? '<img src="' . $config['icons_path'] . '/' . $icons[$row['icon_id']]['img'] . '" width="' . $icons[$row['icon_id']]['width'] . '" height="' . $icons[$row['icon_id']]['height'] . '" alt="" title="" />' : '',

				'S_ROW_COUNT'			=> $i, 
				'S_TOPIC_TYPE_SWITCH'	=> ($s_type_switch == $s_type_switch_test) ? -1 : $s_type_switch_test, 
				'S_TOPIC_TYPE'			=> $row['topic_type'], 
				'S_USER_POSTED'			=> ($row['lastread_type'] == LASTREAD_POSTED) ? true : false, 

				'U_VIEW_TOPIC'	=> $view_topic_url)
			);


			$s_type_switch = ($row['topic_type'] == POST_ANNOUNCE) ? 1 : 0;
			$i++;
		}
	}


	if ($user->data['user_id'] != ANONYMOUS)
	{
		// $mark_topics isn't set as of now
		//setcookie($config['cookie_name'] . '_t', serialize($mark_topics), 0, $config['cookie_path'], $config['cookie_domain'], $config['cookie_secure']);
	}
}


// Mozilla navigation links
$nav_links['up'] = array(
	'url' 	=> 'index.' . $phpEx . $SID,
	'title' => sprintf($user->lang['FORUM_INDEX'], $config['sitename'])
);


// Dump out the page header and load viewforum template
$page_title = $user->lang['VIEW_FORUM'] . ' - ' . $forum_data['forum_name'];
include($phpbb_root_path . 'includes/page_header.'.$phpEx);


$template->set_filenames(array(
	'body' => 'viewforum_body.html')
);
make_jumpbox("viewforum.$phpEx$SID", $forum_id);


include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>