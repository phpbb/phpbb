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
	global $config, $db, $template, $auth, $user, $phpEx, $SID, $forum_moderators;

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
		switch (SQL_LAYER)
		{
			case 'oracle':
				break;

			default:
				$sql_from = '(' . FORUMS_TABLE . ' f LEFT JOIN ' . FORUMS_TRACK_TABLE . ' ft ON (ft.user_id = ' . $user->data['user_id'] . ' AND ft.forum_id = f.forum_id))';
				break;
		}
		$lastread_select = ', ft.mark_time ';
	}
	else
	{
		$sql_from = FORUMS_TABLE . ' f ';
		$lastread_select = $sql_lastread = '';

		$tracking_topics = (isset($_COOKIE[$config['cookie_name'] . '_track'])) ? unserialize(stripslashes($_COOKIE[$config['cookie_name'] . '_track'])) : array();
	}

	$sql = "SELECT f.* $lastread_select 
		FROM $sql_from 
		$sql_where
		ORDER BY f.left_id";
	$result = $db->sql_query($sql);

	$branch_root_id = $root_data['forum_id'];
	$forum_rows = $subforums = $forum_moderators = $mark_forums = array();
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

		if ($row['forum_type'] == FORUM_CAT && ($row['left_id'] + 1 == $row['right_id']))
		{
			// Non-postable forum with no subforums: don't display
			continue;
		}

		$forum_id = $row['forum_id'];

		if (!$auth->acl_get('f_list', $forum_id))
		{
			// if the user does not have permissions to list this forum, skip everything until next branch
			$right_id = $row['right_id'];
			continue;
		}

		if ($row['parent_id'] == $root_data['forum_id'] || $row['parent_id'] == $branch_root_id)
		{
			// Direct child
			$parent_id = $forum_id;
			$forum_rows[$forum_id] = $row;
			$forum_ids[] = $forum_id;

			if (!$row['parent_id'] && $row['forum_type'] == FORUM_CAT && $row['parent_id'] == $root_data['forum_id'])
			{
				$branch_root_id = $forum_id;
			}
			$forum_rows[$parent_id]['forum_id_last_post'] = $row['forum_id'];
		}
		elseif ($row['forum_type'] != FORUM_CAT)
		{
			$subforums[$parent_id]['display'] = ($row['display_on_index']) ? true : false;;
			$subforums[$parent_id]['name'][$forum_id] = $row['forum_name'];

			// Include subforum topic/post counts in parent counts
			$forum_rows[$parent_id]['forum_topics'] += $row['forum_topics'];
			$forum_rows[$parent_id]['forum_posts'] += $row['forum_posts'];

			if (isset($forum_rows[$parent_id]) && $row['forum_last_post_time'] > $forum_rows[$parent_id]['forum_last_post_time'])
			{
				$forum_rows[$parent_id]['forum_last_post_id'] = $row['forum_last_post_id'];
				$forum_rows[$parent_id]['forum_last_post_time'] = $row['forum_last_post_time'];
				$forum_rows[$parent_id]['forum_last_poster_id'] = $row['forum_last_poster_id'];
				$forum_rows[$parent_id]['forum_last_poster_name'] = $row['forum_last_poster_name'];
				$forum_rows[$parent_id]['forum_id_last_post'] = $row['forum_id'];
			}
			else
			{
				$forum_rows[$parent_id]['forum_id_last_post'] = $row['forum_id'];
			}
		}

		$mark_time_forum = ($config['load_db_lastread']) ? $row['mark_time'] : ((isset($tracking_topics[$forum_id][0])) ? base_convert($tracking_topics[$forum_id][0], 36, 10) + $config['board_startdate'] : 0);

		if ($mark_time_forum < $row['forum_last_post_time'] && $user->data['user_id'] != ANONYMOUS)
		{
			$forum_unread[$parent_id] = true;
		}
	}
	$db->sql_freeresult();

	// Grab moderators ... if necessary
	if ($display_moderators)
	{
		get_moderators($forum_moderators, $forum_ids);
	}

	// Loop through the forums
	$root_id = $root_data['forum_id'];
	foreach ($forum_rows as $row)
	{
		if ($row['parent_id'] == $root_id && !$row['parent_id'])
		{
			if ($row['forum_type'] == FORUM_CAT)
			{
				$hold = $row;
				continue;
			}
			else
			{
				unset($hold);
			}
		}
		else if (!empty($hold))
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


		// Generate list of subforums if we need to
		if (isset($subforums[$forum_id]))
		{
			if ($subforums[$forum_id]['display'])
			{
				$alist = array();
				foreach ($subforums[$forum_id]['name'] as $sub_forum_id => $subforum_name)
				{
					if (!empty($subforum_name))
					{
						$alist[$sub_forum_id] = $subforum_name;
					}
				}

				if (sizeof($alist))
				{
					$links = array();
					foreach ($alist as $subforum_id => $subforum_name)
					{
						$links[] = '<a href="viewforum.' . $phpEx . $SID . '&amp;f=' . $subforum_id . '">' . $subforum_name . '</a>';
					}
					$subforums_list = implode(', ', $links);

					$l_subforums = (count($subforums[$forum_id]) == 1) ? $user->lang['SUBFORUM'] . ': ' : $user->lang['SUBFORUMS'] . ': ';
				}
			}

			$folder_image = (!empty($forum_unread[$forum_id])) ? 'sub_forum_new' : 'sub_forum';
		}
		else
		{
			switch ($row['forum_type'])
			{
				case FORUM_POST:
					$folder_image = (!empty($forum_unread[$forum_id])) ? 'forum_new' : 'forum';
					break;

				case FORUM_LINK:
					$folder_image = 'forum_link';
					break;
			}

			$subforums_list = '';
			$l_subforums = '';
		}


		// Which folder should we display?
		if ($row['forum_status'] == ITEM_LOCKED)
		{
			$folder_image = 'forum_locked';
			$folder_alt = 'FORUM_LOCKED';
		}
		else
		{
			$folder_alt = (!empty($forum_unread[$forum_id])) ? 'NEW_POSTS' : 'NO_NEW_POSTS';
		}


		// Create last post link information, if appropriate
		if ($row['forum_last_post_id'])
		{
			$last_post_time = $user->format_date($row['forum_last_post_time']);

			$last_poster = ($row['forum_last_poster_name'] != '') ? $row['forum_last_poster_name'] : $user->lang['GUEST'];
			$last_poster_url = ($row['forum_last_poster_id'] == ANONYMOUS) ? '' : "memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u="  . $row['forum_last_poster_id'];

			$last_post_url = "viewtopic.$phpEx$SID&amp;f=" . $row['forum_id_last_post'] . '&amp;p=' . $row['forum_last_post_id'] . '#' . $row['forum_last_post_id'];
		}
		else
		{
			$last_post_time = $last_poster = $last_poster_url = $last_post_url = '';
		}


		// Output moderator listing ... if applicable
		$l_moderator = $moderators_list = '';
		if ($display_moderators && !empty($forum_moderators[$forum_id]))
		{
			$l_moderator = (count($forum_moderators[$forum_id]) == 1) ? $user->lang['MODERATOR'] : $user->lang['MODERATORS'];
			$moderators_list = implode(', ', $forum_moderators[$forum_id]);
		}

		$l_post_click_count = ($row['forum_type'] == FORUM_LINK) ? 'CLICKS' : 'POSTS';
		$post_click_count = ($row['forum_type'] != FORUM_LINK || $row['forum_flags'] & 1) ? $row['forum_posts'] : '';

		$template->assign_block_vars('forumrow', array(
			'S_IS_CAT'			=>	false, 
			'S_IS_LINK'			=>	($row['forum_type'] != FORUM_LINK) ? false : true, 

			'FORUM_IMG'			=>	$row['forum_image'], 
			'LAST_POST_IMG'		=>	$user->img('icon_post_latest', 'VIEW_LATEST_POST'), 

			'FORUM_FOLDER_IMG'	=>	$user->img($folder_image, $folder_alt),
			'FORUM_NAME'		=>	$row['forum_name'],
			'FORUM_DESC'		=>	$row['forum_desc'], 
			$l_post_click_count	=>	$post_click_count,
			'TOPICS'			=>	$row['forum_topics'],
			'LAST_POST_TIME'	=>	$last_post_time,
			'LAST_POSTER'		=>	$last_poster,
			'MODERATORS'		=>	$moderators_list,
			'SUBFORUMS'			=>	$subforums_list,

			'L_SUBFORUM_STR'	=>	$l_subforums,
			'L_MODERATOR_STR'	=>	$l_moderator,
			'L_FORUM_FOLDER_ALT'=>	$folder_alt,
			
			'U_LAST_POSTER'		=>	$last_poster_url, 
			'U_LAST_POST'		=>	$last_post_url, 
			'U_VIEWFORUM'		=>	($row['forum_type'] != FORUM_LINK || $row['forum_flags'] & 1) ? 'viewforum.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'] : $row['forum_link'])
		);
	}

	$template->assign_vars(array(
		'S_HAS_SUBFORUM'	=>	($visible_forums) ? true : false,
		'L_SUBFORUM'		=>	($visible_forums == 1) ? $user->lang['SUBFORUM'] : $user->lang['SUBFORUMS'])
	);
}

// Display Attachments
function display_attachments($attachment_data, &$update_count, $force_physical = false)
{
	global $extensions, $template;
	global $config, $user, $phpbb_root_path, $phpEx, $SID;

	if (empty($extensions) || !is_array($extensions))
	{
		obtain_attach_extensions($extensions);
	}

	$update_count = array();

	foreach ($attachment_data as $attachment)
	{
		// Some basics...
		$attachment['extension'] = strtolower(trim($attachment['extension']));
		$filename = $config['upload_dir'] . '/' . $attachment['physical_filename'];
		$thumbnail_filename = $config['upload_dir'] . '/thumbs/t_' . $attachment['physical_filename'];

		$upload_image = '';

		if ($user->img('icon_attach', '') != '' && $extensions[$attachment['extension']]['upload_icon'] == '')
		{
			$upload_image = $user->img('icon_attach', '');
		}
		else if ($extensions[$attachment['extension']]['upload_icon'] != '')
		{
			$upload_image = '<img src="' . $phpbb_root_path . 'images/upload_icons/' . trim($extensions[$attachment['extension']]['upload_icon']) . '" alt="" border="0" />';
		}
	
		$filesize = $attachment['filesize'];
		$size_lang = ($filesize >= 1048576) ? $user->lang['MB'] : ( ($filesize >= 1024) ? $user->lang['KB'] : $user->lang['BYTES'] );

		$filesize = ($filesize >= 1048576) ? round((round($filesize / 1048576 * 100) / 100), 2) : (($filesize >= 1024) ? round((round($filesize / 1024 * 100) / 100), 2) : $filesize);

		$display_name = $attachment['real_filename']; 
		$comment = stripslashes(trim(str_replace("\n", '<br />', $attachment['comment'])));

		$denied = FALSE;
			
		if (!in_array($attachment['extension'], $extensions['_allowed_']))
		{
			$denied = TRUE;

			$template->assign_block_vars('postrow.attachment', array(
				'IS_DENIED'		=> TRUE,

				'L_DENIED'		=> sprintf($user->lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachment['extension']))
			);
		} 

		if (!$denied)
		{
			$l_downloaded_viewed = '';
			$download_link = '';
			$additional_array = array();
				
			$display_cat = $extensions[$attachment['extension']]['display_cat'];

			if ($display_cat == IMAGE_CAT)
			{
				if ($attachment['thumbnail'])
				{
					$display_cat = THUMB_CAT;
				}
				else
				{
					if ($config['img_display_inlined'])
					{
						if ($config['img_link_width'] || $config['img_link_height'])
						{
							list($width, $height) = getimagesize($filename);

							$display_cat = (!$width && !$height) ? IMAGE_CAT : (($width <= $config['img_link_width'] && $height <= $config['img_link_height']) ? IMAGE_CAT : NONE_CAT);
						}
					}
					else
					{
						$display_cat = NONE_CAT;
					}
				}
			}

			switch ($display_cat)
			{
				// Images
				case IMAGE_CAT:
					$img_source = $filename;
					$update_count[] = $attachment['attach_id'];

					$l_downloaded_viewed = $user->lang['VIEWED'];
					$download_link = $img_source;
					break;
					
				// Images, but display Thumbnail
				case THUMB_CAT:
					$thumb_source = $thumbnail_filename;

					$l_downloaded_viewed = $user->lang['VIEWED'];
					$download_link = (!$force_physical) ? $phpbb_root_path . "download.$phpEx$SID&amp;id=" . $attachment['attach_id'] : $filename;

					$additional_array = array(
						'THUMB_IMG' => $thumb_source
					);
					break;

				// Windows Media Streams
				case WM_CAT:
					$l_downloaded_viewed = $user->lang['VIEWED'];
					$download_link = $filename;

					// Viewed/Heared File ... update the download count (download.php is not called here)
					$update_count[] = $attachment['attach_id'];
					break;

				// Real Media Streams
				case RM_CAT:
					$l_downloaded_viewed = $user->lang['VIEWED'];
					$download_link = $filename;

					$additional_array = array(
						'U_FORUM' => generate_board_url(),
						'ATTACH_ID' => $attachment['attach_id']
					);

					// Viewed/Heared File ... update the download count (download.php is not called here)
					$update_count[] = $attachment['attach_id'];
					break;
/*			
				// Macromedia Flash Files
				case SWF_CAT:
					list($width, $height) = swf_getdimension($filename);

					$l_downloaded_viewed = $user->lang['VIEWED'];
					$download_link = $filename;
					
					$additional_array = array(
						'WIDTH' => $width,
						'HEIGHT' => $height
					);

					// Viewed/Heared File ... update the download count (download.php is not called here)
					$update_count[] = $attachment['attach_id'];
					break;
*/
				default:
					$l_downloaded_viewed = $user->lang['DOWNLOADED'];
					$download_link = (!$force_physical) ? $phpbb_root_path . "download.$phpEx$SID&amp;id=" . $attachment['attach_id'] : $filename;
					break;
			}

			$template_array = array_merge($additional_array, array(
//				'IS_FLASH'		=> ($display_cat == SWF_CAT) ? true : false,
				'IS_WM_STREAM'	=> ($display_cat == WM_CAT) ? true : false,
				'IS_RM_STREAM'	=> ($display_cat == RM_CAT) ? true : false,
				'IS_THUMBNAIL'	=> ($display_cat == THUMB_CAT) ? true : false,
				'IS_IMAGE'		=> ($display_cat == IMAGE_CAT) ? true : false,
				'DOWNLOAD_NAME' => $display_name,
				'FILESIZE'		=> $filesize,
				'SIZE_VAR'		=> $size_lang,
				'COMMENT'		=> $comment,

				'U_DOWNLOAD_LINK' => $download_link,

				'UPLOAD_IMG' => $upload_image,

				'L_DOWNLOADED_VIEWED'	=> $l_downloaded_viewed,
				'L_DOWNLOAD_COUNT'		=> sprintf($user->lang['DOWNLOAD_NUMBER'], $attachment['download_count']))
			);

			$template->assign_block_vars('postrow.attachment', $template_array);
		}
	}
}

?>