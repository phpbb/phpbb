<?php
/***************************************************************************
 *                           functions_display.php
 *                             ------------------
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

function display_forums($root_data = '', $display_moderators = TRUE)
{
	global $db, $template, $auth, $user, $phpEx, $SID, $forum_moderators;

	$visible_forums = 0;

	if (!$root_data)
	{
		$root_data = array('forum_id' => 0);
		$sql_where = '';
	}
	else
	{
		$sql_where = ' WHERE left_id > ' . $root_data['left_id'] . ' AND left_id < ' . $root_data['right_id'];
	}

	if ($config['load_db_lastread'] && $user->data['user_id'] != ANONYMOUS)
	{
		$lastread_select = ", lr.lastread_time ";
/*		$sql_lastread = 'LEFT JOIN ' . FORUMS_TRACK_TABLE . ' ft ON (ft.user_id = ' . $user->data['user_id'] . ' 
				AND ft.forum_id = f.forum_id)';*/
		$sql_lastread = 'LEFT JOIN ' . TOPICS_TRACK_TABLE . ' tt ON (tt.user_id = ' . $user->data['user_id'] . ' 
				AND tt.forum_id IN (f.forum_id, -f.forum_id)
				AND tt.lastread_time >= f.forum_last_post_time)';
		$sql_where .= ' GROUP BY f.forum_id';
	}
	else
	{
		$lastread_select = '';
		$lastread_sql = '';

		// Cookie based tracking
	}

	$sql = "SELECT f.* $lastread_select 
		FROM (" . FORUMS_TABLE . " f 
		$sql_lastread)
		$sql_where 
		ORDER BY left_id";
	$result = $db->sql_query($sql);

	$branch_root_id = $root_data['forum_id'];
	$forum_rows = $subforums = $forum_moderators = array();
	$forum_ids = array($root_data['forum_id']);

	while ($row = $db->sql_fetchrow($result))
	{
		if (isset($right_id))
		{
			if ($row['left_id'] < $right_id)
			{
				continue;
			}
			unset($right_id);
		}

		if (!$row['forum_postable'] && ($row['left_id'] + 1 == $row['right_id']))
		{
			// Non-postable forum with no subforums: don't display
			continue;
		}

		if (!$auth->acl_get('f_list', $row['forum_id']))
		{
			// if the user does not have permissions to list this forum, skip everything until next branch

			$right_id = $row['right_id'];
			continue;
		}

		if ($row['parent_id'] == $root_data['forum_id'])
		{
			// Direct child
			$forum_rows[] = $row;
			$parent_id = $row['forum_id'];
			$forum_ids[] = $row['forum_id'];

			if (!$row['forum_postable'])
			{
				$branch_root_id = $row['forum_id'];
			}
		}
		elseif ($row['parent_id'] == $branch_root_id)
		{
			// Forum directly under a category
			$forum_rows[] = $row;
			$parent_id = $row['forum_id'];
			$forum_ids[] = $row['forum_id'];
		}
		elseif ($row['forum_postable'])
		{
			if ($row['display_on_index'])
			{
				$subforums[$parent_id][$row['forum_id']]['forum_name'] = $row['forum_name'];
			}

			$subforums[$parent_id][$row['forum_id']]['unread_count'] = $row['unread_count'];
			$subforums[$parent_id][$row['forum_id']]['forum_last_post_time'] = $row['forum_last_post_time'];

			$subforums[$parent_id][$row['forum_id']]['forum_id'] = $row['forum_id'];
			$subforums[$parent_id][$row['forum_id']]['forum_last_post_id'] = $row['forum_last_post_id'];
			$subforums[$parent_id][$row['forum_id']]['forum_last_post_time'] = $row['forum_last_post_time'];
			$subforums[$parent_id][$row['forum_id']]['forum_last_poster_name'] = $row['forum_last_poster_name'];
			$subforums[$parent_id][$row['forum_id']]['forum_last_poster_id'] = $row['forum_last_poster_id'];
		}
	}
	$db->sql_freeresult();

	if ($display_moderators)
	{
		get_moderators($forum_moderators, $forum_ids);
	}

	$root_id = $root_data['forum_id'];
	foreach ($forum_rows as $row)
	{
		if ($row['parent_id'] == $root_id)
		{
			if (!$row['forum_postable'])
			{
				$hold = $row;
				continue;
			}
			else
			{
				unset($hold);
			}
		}
		elseif (!empty($hold))
		{
			$template->assign_block_vars('forumrow', array(
				'S_IS_CAT'			=>	TRUE,
				'FORUM_ID'			=>	$hold['forum_id'],
				'FORUM_NAME'		=>	$hold['forum_name'],
				'FORUM_DESC'		=>	$hold['forum_desc'],
				'U_VIEWFORUM'		=>	'viewforum.' . $phpEx . $SID . '&amp;f=' . $hold['forum_id'])
			);
			unset($hold);
		}

		$visible_forums++;

		$forum_id = $row['forum_id'];

		//
		$unread_topics = ($user->data['user_id'] != ANONYMOUS && $row['unread_count'] < $row['forum_last_post_time']) ? 1 : 0;

		//
		if (isset($subforums[$forum_id]))
		{

			$alist = array();
			foreach ($subforums[$forum_id] as $sub_forum_id => $subforum_row)
			{
				$unread_topics += ($user->data['user_id'] != ANONYMOUS && $subforum_row['unread_count'] < $subforum_row['forum_last_post_time']) ? 1 : 0;

				if (!empty($subforum_row['forum_name']))
				{
					$alist[$sub_forum_id] = $subforum_row['forum_name'];
				}

				if ($subforum_row['forum_last_post_time'] > $row['forum_last_post_time'])
				{
					$row['forum_last_post_time'] = $subforum_row['forum_last_post_time'];
					$row['forum_last_post_id'] = $subforum_row['forum_last_post_id'];
					$row['forum_last_poster_name'] = $subforum_row['forum_last_poster_name'];
					$row['forum_last_poster_id'] = $subforum_row['forum_last_poster_id'];
					$row['forum_id_last_post'] = $subforum_row['forum_id'];
				}
			}

			if (sizeof($alist))
			{
				@natsort($alist);

				$links = array();
				foreach ($alist as $subforum_id => $subforum_name)
				{
					$links[] = '<a href="viewforum.' . $phpEx . $SID . '&amp;f=' . $subforum_id . '">' . $subforum_name . '</a>';
				}
				$subforums_list = implode(', ', $links);

				$l_subforums = (count($subforums[$forum_id]) == 1) ? $user->lang['SUBFORUM'] . ': ' : $user->lang['SUBFORUMS'] . ': ';
			}

			$folder_image = ($unread_topics) ? 'sub_forum_new' : 'sub_forum';
		}
		else
		{
			$folder_image = ($unread_topics) ? 'forum_new' : 'forum';
			$row['forum_id_last_post'] = $row['forum_id'];

			$subforums_list = '';
			$l_subforums = '';
		}

		//
		if ($row['forum_status'] == ITEM_LOCKED)
		{
			$folder_image = 'forum_locked';
			$folder_alt = 'FORUM_LOCKED';
		}
		else
		{
			$folder_alt = ($unread_topics) ? 'NEW_POSTS' : 'NO_NEW_POSTS';
		}

		//
		if ($row['forum_last_post_id'])
		{
			$last_post_time = $user->format_date($row['forum_last_post_time']);

			$last_poster = ($row['forum_last_poster_name'] != '') ? $row['forum_last_poster_name'] : $user->lang['GUEST'];
			$last_poster_url = ($row['forum_last_poster_id'] == ANONYMOUS) ? '' : "memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u="  . $row['forum_last_poster_id'];

			$last_post_url = "viewtopic.$phpEx$SID&amp;f=" . $row['forum_id_last_post'] . '&amp;p=' . $row['forum_last_post_id'] . '#' . $row['forum_last_post_id'];
		}
		else
		{
			$last_post = $user->lang['NO_POSTS'];
		}

		//
		$l_moderator = $moderators_list = '';
		if ($display_moderators && !empty($forum_moderators[$forum_id]))
		{
			$l_moderator = (count($forum_moderators[$forum_id]) == 1) ? $user->lang['MODERATOR'] : $user->lang['MODERATORS'];
			$moderators_list = implode(', ', $forum_moderators[$forum_id]);
		}

		$template->assign_block_vars('forumrow', array(
			'S_IS_CAT'			=>	FALSE,

			'FORUM_IMG'			=>	$row['forum_image'], 
			'LAST_POST_IMG'		=>	$user->img('icon_post_latest', 'VIEW_LATEST_POST'), 

			'FORUM_FOLDER_IMG'	=>	$user->img($folder_image, $folder_alt),
			'FORUM_NAME'		=>	$row['forum_name'],
			'FORUM_DESC'		=>	$row['forum_desc'], 
			'POSTS'				=>	$row['forum_posts'],
			'TOPICS'			=>	$row['forum_topics'],
			'LAST_POST_TIME'	=>	$last_post_time,
			'LAST_POSTER'		=>	$last_poster,
			'LAST_POST'			=>	$last_post,
			'MODERATORS'		=>	$moderators_list,
			'SUBFORUMS'			=>	$subforums_list,

			'L_SUBFORUM_STR'	=>	$l_subforums,
			'L_MODERATOR_STR'	=>	$l_moderator,
			'L_FORUM_FOLDER_ALT'=>	$folder_alt,

			'U_LAST_POSTER'		=>	$last_poster_url, 
			'U_LAST_POST'		=>	$last_post_url, 
			'U_VIEWFORUM'		=>	'viewforum.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'])
		);
	}

	$template->assign_vars(array(
		'S_HAS_SUBFORUM'	=>	($visible_forums) ? TRUE : FALSE,
		'L_SUBFORUM'		=>	($visible_forums == 1) ? $user->lang['SUBFORUM'] : $user->lang['SUBFORUMS'])
	);
}

?>