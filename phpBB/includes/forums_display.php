<?php
/***************************************************************************
 *                             display_forums.php
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

foreach ($forum_rows as $row)
{
	extract($row);
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

	if ($auth->get_acl($forum_id, 'forum', 'list'))
	{
		switch ($forum_status)
		{
			case ITEM_CATEGORY:
				$folder_image = $theme['forum_locked'];
				$folder_alt = $lang['Category'];
			break;

			case ITEM_LOCKED:
				$folder_image = $theme['forum_locked'];
				$folder_alt = $lang['Forum_locked'];
			break;

			default:
				$unread_topics = false;
				if ($userdata['user_id'] && $forum_last_post_time > $userdata['user_lastvisit'])
				{
					$unread_topics = true;
					if (isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f_all']))
					{
						if ($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f_all'] > $forum_last_post_time)
						{
							$unread_topics = false;
						}
					}

					if (isset($mark_topics[$forum_id]) || isset($mark_forums[$forum_id]))
					{
						if ($mark_forums[$forum_id] > $userdata['user_lastvisit'] || !max($mark_topics[$forum_id]))
						{
							$unread_topics = false;
						}
					}
				}

				$folder_image = ($unread_topics) ? $theme['forum_new'] : $theme['forum'];
				$folder_alt = ($unread_topics) ? $lang['New_posts'] : $lang['No_new_posts'];
		}

		if ($forum_last_post_id)
		{
			$last_post = create_date($board_config['default_dateformat'], $forum_last_post_time, $board_config['board_timezone']) . '<br />';

			$last_post .= ($forum_last_poster_id == ANONYMOUS) ? (($forum_last_poster_name != '') ? $forum_last_poster_name . ' ' : $lang['Guest'] . ' ') : '<a href="profile.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u='  . $forum_last_poster_id . '">' . $username . '</a> ';

			$last_post .= '<a href="viewtopic.' . $phpEx . '$SID&amp;f=' . $forum_id . '&amp;p=' . $forum_last_post_id . '#' . $forum_last_post_id . '">' . create_img($theme['goto_post_latest'], $lang['View_latest_post']) . '</a>';
		}
		else
		{
			$last_post = $lang['No_Posts'];
		}

		if (!empty($forum_moderators[$forum_id]))
		{
			$l_moderator = (count($forum_moderators[$forum_id]) == 1) ? $lang['Moderator'] . ': ' : $lang['Moderators'] . ': ' ;
			$moderators_list = implode(', ', $forum_moderators[$forum_id]);
		}
		else
		{
			$l_moderator = '&nbsp;';
			$moderators_list = '&nbsp;';
		}

		if (isset($subforums[$forum_id]))
		{
			$subforums_list = format_subforums_list($subforums[$forum_id]);
			$l_subforums = (count($subforums[$forum_id]) == 1) ? $lang['Subforum'] . ': ' : $lang['Subforums'] . ': ';
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

			'FORUM_FOLDER_IMG'	=>	create_img($folder_image, $folder_alt),
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
}
?>