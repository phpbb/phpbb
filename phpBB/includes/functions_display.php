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

function display_forums($root_data=array(), $display_moderators=TRUE)
{
	global $db, $template, $auth, $user, $phpEx, $SID, $forum_moderators;

	$where_sql = ($root_data['forum_id']) ? ' WHERE left_id > ' . $root_data['left_id'] . ' AND left_id < ' . $root_data['right_id'] : '';

	$sql = 'SELECT * FROM ' . FORUMS_TABLE . $where_sql . ' ORDER BY left_id';
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

		if (!$auth->acl_gets('f_list', 'm_', 'a_', intval($row['forum_id'])))
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
				// Subforum
				$subforums[$parent_id][$row['forum_id']] = $row['forum_name'];
			}
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
				'U_VIEWFORUM'		=>	'viewforum.' . $phpEx . $SID . '&amp;f=' . $hold['forum_id']
			));
			unset($hold);
		}

		$forum_id = $row['forum_id'];

		$unread_topics = ($user->data['user_id'] && $row['forum_last_post_time'] > $user->data['user_lastvisit']) ? TRUE : FALSE;

		$folder_image = ($unread_topics) ? 'forum_new' : 'forum';
		$folder_alt = ($unread_topics) ? 'New_posts' : 'No_new_posts';

		if ($row['left_id'] + 1 < $row['right_id'])
		{
			$folder_image = ($unread_topics) ? 'sub_forum_new' : 'sub_forum';
			$folder_alt = ($unread_topics) ? 'New_posts' : 'No_new_posts';
		}
		elseif ($row['forum_status'] == ITEM_LOCKED)
		{
			$folder_image = 'forum_locked';
			$folder_alt = 'Forum_locked';
		}
		else
		{
			$folder_image = ($unread_topics) ? 'forum_new' : 'forum';
			$folder_alt = ($unread_topics) ? 'New_posts' : 'No_new_posts';
		}

		if ($row['forum_last_post_id'])
		{
			$last_post = $user->format_date($row['forum_last_post_time']) . '<br />';

			$last_post .= ($row['forum_last_poster_id'] == ANONYMOUS) ? (($row['forum_last_poster_name'] != '') ? $row['forum_last_poster_name'] . ' ' : $user->lang['Guest'] . ' ') : '<a href="profile.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u='  . $row['forum_last_poster_id'] . '">' . $row['forum_last_poster_name'] . '</a> ';

			$last_post .= '<a href="viewtopic.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'] . '&amp;p=' . $row['forum_last_post_id'] . '#' . $row['forum_last_post_id'] . '">' . $user->img('goto_post_latest', 'View_latest_post') . '</a>';
		}
		else
		{
			$last_post = $user->lang['No_Posts'];
		}

		if (isset($subforums[$forum_id]))
		{
			$alist = array();
			foreach ($subforums[$forum_id] as $sub_forum_id => $forum_name)
			{
				$alist[$sub_forum_id] = $forum_name;
			}
			natsort($alist);

			$links = array();
			foreach ($alist as $subforum_id => $subforum_name)
			{
				$links[] = '<a href="viewforum.' . $phpEx . $SID . '&amp;f=' . $subforum_id . '">' . $subforum_name . '</a>';
			}
			$subforums_list = implode(', ', $links);

			$l_subforums = (count($subforums[$forum_id]) == 1) ? $user->lang['Subforum'] . ': ' : $user->lang['Subforums'] . ': ';
		}
		else
		{
			$subforums_list = '';
			$l_subforums = '';
		}

		$l_moderator = $moderators_list = '';
		if ($display_moderators)
		{
			if (!empty($forum_moderators[$forum_id]))
			{
				$l_moderator = (count($forum_moderators[$forum_id]) == 1) ? $user->lang['Moderator'] : $user->lang['Moderators'];
				$moderators_list = implode(', ', $forum_moderators[$forum_id]);
			}
		}

		$template->assign_block_vars('forumrow', array(
			'S_IS_CAT'			=>	FALSE,

			'FORUM_FOLDER_IMG'	=>	$user->img($folder_image, $folder_alt),
			'FORUM_NAME'		=>	$row['forum_name'],
			'FORUM_DESC'		=>	$row['forum_desc'],

			'POSTS'				=>	$row['forum_posts'],
			'TOPICS'			=>	$row['forum_topics'],
			'LAST_POST'			=>	$last_post,
			'MODERATORS'		=>	$moderators_list,
			'SUBFORUMS'			=>	$subforums_list,

			'FORUM_IMG'			=>	$forum_image,

			'L_SUBFORUM'		=>	$l_subforums,
			'L_MODERATOR'		=>	$l_moderator,
			'L_FORUM_FOLDER_ALT'=>	$folder_alt,

			'U_VIEWFORUM'		=>	'viewforum.' . $phpEx . $SID . '&amp;f=' . $row['forum_id']
		));
	}
}
?>