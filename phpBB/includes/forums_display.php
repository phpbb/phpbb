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

function display_forums($left_id=0, $right_id=0)
{
	global $db, $template, $auth;

	$where_sql = ($left_id && $right_id) ? " WHERE left_id > $left_id AND left_id < $right_id" : '';
	$sql = 'SELECT * FROM ' . FORUMS_TABLE . $where_sql . ' ORDER BY left_id ASC';
	$result = $db->sql_query($sql);

	$cat_header =
	while ($row = $db->sql_fetchrow($result))
	{
		
	}
	
}

foreach ($forum_rows as $row)
{
	extract($row);
	if (!$auth->acl_get('f_list', $forum_id))
	{
		continue;
	}

	if ($parent_id == $root_id)
	{
		if ($forum_status == ITEM_CATEGORY)
		{
			$stored_cat = $row;
			continue;
		}
		else
		{
			unset($stored_cat);
		}
	}
	elseif (!empty($stored_cat))
	{
		$template->assign_block_vars('forumrow', array(
			'S_IS_CAT'	=>	TRUE,
			'CAT_ID'	=>	$stored_cat['forum_id'],
			'CAT_NAME'	=>	$stored_cat['forum_name'],
			'U_VIEWCAT'	=>	'index.' . $phpEx . $SID . '&amp;c=' . $stored_cat['forum_id']
		));
		unset($stored_cat);
	}

	switch ($forum_status)
	{
		case ITEM_CATEGORY:
			$folder_image = 'sub_forum';
			$folder_alt = 'Category';
		break;

		case ITEM_LOCKED:
			$folder_image = 'forum_locked';
			$folder_alt = 'Forum_locked';
		break;

		default:
			$unread_topics = false;
			if ($user->data['user_id'] && $forum_last_post_time > $user->data['user_lastvisit'])
			{
				$unread_topics = true;
			}

			$folder_image = ($unread_topics) ? 'forum_new' : 'forum';
			$folder_alt = ($unread_topics) ? 'New_posts' : 'No_new_posts';
	}

	if ($forum_last_post_id)
	{
		$last_post = $user->format_date($forum_last_post_time) . '<br />';

		$last_post .= ($forum_last_poster_id == ANONYMOUS) ? (($forum_last_poster_name != '') ? $forum_last_poster_name . ' ' : $user->lang['Guest'] . ' ') : '<a href="profile.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u='  . $forum_last_poster_id . '">' . $forum_last_poster_name . '</a> ';

		$last_post .= '<a href="viewtopic.' . $phpEx . $SID . '&amp;f=' . $forum_id . '&amp;p=' . $forum_last_post_id . '#' . $forum_last_post_id . '">' . $user->img('goto_post_latest', 'View_latest_post') . '</a>';
	}
	else
	{
		$last_post = $user->lang['No_Posts'];
	}

	if (!empty($forum_moderators[$forum_id]))
	{
		$l_moderator = (count($forum_moderators[$forum_id]) == 1) ? $user->lang['Moderator'] . ': ' : $user->lang['Moderators'] . ': ' ;
		$moderators_list = implode(', ', $forum_moderators[$forum_id]);
	}
	else
	{
		$l_moderator = '&nbsp;';
		$moderators_list = '&nbsp;';
	}

	if (isset($subforums[$forum_id]))
	{
		foreach ($subforums[$forum_id] as $row)
		{
			$alist[$row['forum_id']] = $row['forum_name'];
		}
		asort($alist);

		$links = array();
		foreach ($alist as $subforum_id => $subforum_name)
		{
			$links[] = '<a href="viewforum.' . $phpEx . $SID . '&f=' . $subforum_id . '">' . htmlspecialchars($subforum_name) . '</a>';
		}
		$subforums_list = implode(', ', $links);

		$l_subforums = (count($subforums[$forum_id]) == 1) ? $user->lang['Subforum'] . ': ' : $user->lang['Subforums'] . ': ';
	}
	else
	{
		$subforums_list = '';
		$l_subforums = '';
	}

	switch ($forum_status)
	{
		case ITEM_CATEGORY:
			$forum_link = 'index.' . $phpEx . $SID . '&amp;c=' . $forum_id;
			$forum_type_switch = 'S_IS_SUBCAT';
		break;

		default:
			$forum_link = 'viewforum.' . $phpEx . $SID . '&amp;f=' . $forum_id;
			if ($parent_id == $root_id)
			{
				$forum_type_switch = 'S_IS_ROOTFORUM';
			}
			else
			{
				$forum_type_switch = 'S_IS_FORUM';
			}
	}

	$template->assign_block_vars('forumrow', array(
		$forum_type_switch	=>	TRUE,

		'FORUM_FOLDER_IMG'	=>	$user->img($folder_image, $folder_alt),
		'FORUM_NAME'		=>	$forum_name,
		'FORUM_DESC'		=>	$forum_desc,

		'POSTS'				=>	$forum_posts,
		'TOPICS'			=>	$forum_topics,
		'LAST_POST'			=>	$last_post,
		'MODERATORS'		=>	$moderators_list,
		'SUBFORUMS'			=>	$subforums_list,

		'FORUM_IMG'			=>	$forum_image,

		'L_SUBFORUM'		=>	$l_subforums,
		'L_MODERATOR'		=>	$l_moderator,
		'L_FORUM_FOLDER_ALT'=>	$folder_alt,

		'U_VIEWFORUM'		=>	$forum_link
	));
}
?>