<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : functions_display.php
// STARTED   : Thu Nov 07, 2002
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

function display_forums($root_data = '', $display_moderators = TRUE)
{
	global $config, $db, $template, $auth, $user, $phpEx, $SID, $forum_moderators, $phpbb_root_path;

	// Get posted/get info
	$mark_read = request_var('mark', '');

	$forum_id_ary = $active_forum_ary = $forum_rows = $subforums = $forum_moderators = $mark_forums = array();
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

	// Display list of active topics for this category?
	$show_active = (isset($root_data['forum_flags']) && $root_data['forum_flags'] & 16) ? true : false;

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
	$forum_ids		= array($root_data['forum_id']);

	while ($row = $db->sql_fetchrow($result))
	{
		if ($mark_read == 'forums' && $user->data['user_id'] != ANONYMOUS)
		{
			if ($auth->acl_get('f_list', $row['forum_id']))
			{
				$forum_id_ary[] = $row['forum_id'];
			}

			continue;
		}

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

		// Display active topics from this forum?
		if ($show_active && $row['forum_type'] == FORUM_POST && $auth->acl_get('f_read', $forum_id) && ($row['forum_flags'] & 16))
		{
			$active_forum_ary['forum_id'][]		= $forum_id;
			$active_forum_ary['enable_icons'][] = $row['enable_icons'];
			$active_forum_ary['forum_topics']	+= ($auth->acl_get('m_approve', $forum_id)) ? $row['forum_topics_real'] : $row['forum_topics'];
			$active_forum_ary['forum_posts']	+= $row['forum_posts'];
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
			$forum_rows[$parent_id]['forum_topics'] += ($auth->acl_get('m_approve', $forum_id)) ? $row['forum_topics_real'] : $row['forum_topics'];
			
			// Do not list redirects in LINK Forums as Posts.
			if ($row['forum_type'] != FORUM_LINK)
			{
				$forum_rows[$parent_id]['forum_posts'] += $row['forum_posts'];
			}

			if (isset($forum_rows[$parent_id]) && $row['forum_last_post_time'] > $forum_rows[$parent_id]['forum_last_post_time'])
			{
				$forum_rows[$parent_id]['forum_last_post_id'] = $row['forum_last_post_id'];
				$forum_rows[$parent_id]['forum_last_post_time'] = $row['forum_last_post_time'];
				$forum_rows[$parent_id]['forum_last_poster_id'] = $row['forum_last_poster_id'];
				$forum_rows[$parent_id]['forum_last_poster_name'] = $row['forum_last_poster_name'];
				$forum_rows[$parent_id]['forum_id_last_post'] = $forum_id;
			}
			else
			{
				$forum_rows[$parent_id]['forum_id_last_post'] = $forum_id;
			}
		}

		if (!isset($row['mark_time']))
		{
			$row['mark_time'] = 0;
		}

		$mark_time_forum = ($config['load_db_lastread']) ? $row['mark_time'] : ((isset($tracking_topics[$forum_id][0])) ? base_convert($tracking_topics[$forum_id][0], 36, 10) + $config['board_startdate'] : 0);

		if ($mark_time_forum < $row['forum_last_post_time'] && $user->data['user_id'] != ANONYMOUS)
		{
			$forum_unread[$parent_id] = true;
		}
	}
	$db->sql_freeresult();

	// Handle marking posts
	if ($mark_read == 'forums')
	{
		markread('mark', $forum_id_ary);

		$redirect = (!empty($_SERVER['REQUEST_URI'])) ? preg_replace('#^(.*?)&(amp;)?mark=.*$#', '\1', htmlspecialchars($_SERVER['REQUEST_URI'])) : "index.$phpEx$SID";
		meta_refresh(3, $redirect);

		$message = (strpos($redirect, 'viewforum') !== false) ? 'RETURN_FORUM' : 'RETURN_INDEX';
		$message = $user->lang['FORUMS_MARKED'] . '<br /><br />' . sprintf($user->lang[$message], '<a href="' . $redirect . '">', '</a> ');
		trigger_error($message);
	}

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
				'U_VIEWFORUM'		=>	"viewforum.$phpEx$SID&amp;f=" . $hold['forum_id'])
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
			'S_IS_CAT'			=> false, 
			'S_IS_LINK'			=> ($row['forum_type'] != FORUM_LINK) ? false : true, 

			'LAST_POST_IMG'		=> $user->img('icon_post_latest', 'VIEW_LATEST_POST'), 

			'FORUM_ID'			=> $row['forum_id'], 
			'FORUM_FOLDER_IMG'	=> ($row['forum_image']) ? '<img src="' . $phpbb_root_path . $row['forum_image'] . '" alt="' . $folder_alt . '" border="0" />' : $user->img($folder_image, $folder_alt),
			'FORUM_NAME'		=> $row['forum_name'],
			'FORUM_DESC'		=> $row['forum_desc'], 
			$l_post_click_count	=> $post_click_count,
			'TOPICS'			=> ($auth->acl_get('m_approve', $row['forum_id'])) ? $row['forum_topics_real'] : $row['forum_topics'],
			'LAST_POST_TIME'	=> $last_post_time,
			'LAST_POSTER'		=> $last_poster,
			'MODERATORS'		=> $moderators_list,
			'SUBFORUMS'			=> $subforums_list,
			
			'L_SUBFORUM_STR'	=> $l_subforums,
			'L_MODERATOR_STR'	=> $l_moderator,
			'L_FORUM_FOLDER_ALT'=> $folder_alt,
			
			'U_LAST_POSTER'		=> $last_poster_url, 
			'U_LAST_POST'		=> $last_post_url, 
			'U_VIEWFORUM'		=> ($row['forum_type'] != FORUM_LINK || $row['forum_flags'] & 1) ? "viewforum.$phpEx$SID&amp;f=" . $row['forum_id'] : $row['forum_link'])
		);
	}

	$template->assign_vars(array(
		'U_MARK_FORUMS'		=> "viewforum.$phpEx$SID&amp;f=" . $root_data['forum_id'] . '&amp;mark=forums', 

		'S_HAS_SUBFORUM'	=>	($visible_forums) ? true : false,

		'L_SUBFORUM'		=>	($visible_forums == 1) ? $user->lang['SUBFORUM'] : $user->lang['SUBFORUMS'])
	);

	return $active_forum_ary;
}

// Display Attachments
function display_attachments($forum_id, $blockname, &$attachment_data, &$update_count, $force_physical = false, $return = false)
{
	global $extensions, $template, $cache, $attachment_tpl;
	global $config, $user, $phpbb_root_path, $phpEx, $SID;

//	$starttime = explode(' ', microtime());
//	$starttime = $starttime[1] + $starttime[0];
	$return_tpl = array();

	$blocks = array(ATTACHMENT_CATEGORY_WM => 'WM_STREAM', ATTACHMENT_CATEGORY_RM => 'RM_STREAM', ATTACHMENT_CATEGORY_THUMB => 'THUMBNAIL', ATTACHMENT_CATEGORY_IMAGE => 'IMAGE');

	if (!isset($attachment_tpl))
	{
		if ($cache->exists('attachment_tpl'))
		{
			$attachment_tpl = $cache->get('attachment_tpl');
		}
		else
		{
			$attachment_tpl = array();

			// Generate Template
			// TODO: secondary template
			$template_filename = $phpbb_root_path . 'styles/' . $user->theme['primary']['template_path'] . '/template/attachment.html';
			if (!($fp = @fopen($template_filename, 'rb')))
			{
				trigger_error('Could not load attachment template');
			}
			$attachment_template = fread($fp, filesize($template_filename));
			@fclose($fp);

			// replace \ with \\ and then ' with \'.
			$attachment_template = str_replace('\\', '\\\\', $attachment_template);
			$attachment_template = str_replace("'", "\'", $attachment_template);
			
			preg_match_all('#<!-- BEGIN (.*?) -->(.*?)<!-- END (.*?) -->#s', $attachment_template, $tpl);
			
			foreach ($tpl[1] as $num => $block_name)
			{
				$attachment_tpl[$block_name] = $tpl[2][$num];
			}
			unset($tpl);

			$cache->put('attachment_tpl', $attachment_tpl);
		}
	}
	
	if (empty($extensions) || !is_array($extensions))
	{
		$extensions = array();
		obtain_attach_extensions($extensions);
	}

	foreach ($attachment_data as $attachment)
	{
		// Some basics...
		$attachment['extension'] = strtolower(trim($attachment['extension']));
		$filename = $config['upload_dir'] . '/' . $attachment['physical_filename'];
		$thumbnail_filename = $config['upload_dir'] . '/thumb_' . $attachment['physical_filename'];

		$upload_image = '';

		if ($user->img('icon_attach', '') && !$extensions[$attachment['extension']]['upload_icon'])
		{
			$upload_image = $user->img('icon_attach', '');
		}
		else if ($extensions[$attachment['extension']]['upload_icon'])
		{
			$upload_image = '<img src="' . $phpbb_root_path . $config['upload_icons_path'] . '/' . trim($extensions[$attachment['extension']]['upload_icon']) . '" alt="" border="0" />';
		}
	
		$filesize = $attachment['filesize'];
		$size_lang = ($filesize >= 1048576) ? $user->lang['MB'] : ( ($filesize >= 1024) ? $user->lang['KB'] : $user->lang['BYTES'] );

		$filesize = ($filesize >= 1048576) ? round((round($filesize / 1048576 * 100) / 100), 2) : (($filesize >= 1024) ? round((round($filesize / 1024 * 100) / 100), 2) : $filesize);

		$display_name = $attachment['real_filename']; 
		$comment = str_replace("\n", '<br />', censor_text($attachment['comment']));

		$denied = false;
			
		if (!extension_allowed($forum_id, $attachment['extension'], $extensions))
		{
			$denied = true;

			$template_array['VAR'] = array('{L_DENIED}');
			$template_array['VAL'] = array(sprintf($user->lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachment['extension']));

			$tpl = str_replace($template_array['VAR'], $template_array['VAL'], $attachment_tpl['DENIED']);

			// Replace {L_*} lang strings
			$tpl = preg_replace('/{L_([A-Z_]+)}/e', "(!empty(\$user->lang['\$1'])) ? \$user->lang['\$1'] : ucwords(strtolower(str_replace('_', ' ', '\$1')))", $tpl);

			if (!$return)
			{
				$template->assign_block_vars($blockname, array(
					'DISPLAY_ATTACHMENT' => $tpl)
				);
			}
			else
			{
				$return_tpl[] = $tpl;
			}
		} 

		if (!$denied)
		{
			$l_downloaded_viewed = '';
			$download_link = '';
			$additional_array['VAR'] = $additional_array['VAL'] = array();
				
			$display_cat = $extensions[$attachment['extension']]['display_cat'];

			if ($display_cat == ATTACHMENT_CATEGORY_IMAGE)
			{
				if ($attachment['thumbnail'])
				{
					$display_cat = ATTACHMENT_CATEGORY_THUMB;
				}
				else
				{
					if ($config['img_display_inlined'])
					{
						if ($config['img_link_width'] || $config['img_link_height'])
						{
							list($width, $height) = getimagesize($filename);

							$display_cat = (!$width && !$height) ? ATTACHMENT_CATEGORY_IMAGE : (($width <= $config['img_link_width'] && $height <= $config['img_link_height']) ? ATTACHMENT_CATEGORY_IMAGE : ATTACHMENT_CATEGORY_NONE);
						}
					}
					else
					{
						$display_cat = ATTACHMENT_CATEGORY_NONE;
					}
				}
			}

			switch ($display_cat)
			{
				// Images
				case ATTACHMENT_CATEGORY_IMAGE:
					$img_source = $filename;
					$update_count[] = $attachment['attach_id'];

					$l_downloaded_viewed = $user->lang['VIEWED'];
					$download_link = $img_source;
					break;
					
				// Images, but display Thumbnail
				case ATTACHMENT_CATEGORY_THUMB:
					$thumb_source = $thumbnail_filename;

					$l_downloaded_viewed = $user->lang['VIEWED'];
					$download_link = (!$force_physical) ? $phpbb_root_path . "download.$phpEx$SID&amp;id=" . $attachment['attach_id'] : $filename;

					$additional_array['VAR'][] = '{THUMB_IMG}';
					$additional_array['VAL'][] = $thumb_source;
					break;

				// Windows Media Streams
				case ATTACHMENT_CATEGORY_WM:
					$l_downloaded_viewed = $user->lang['VIEWED'];
					$download_link = $filename;

					// Viewed/Heared File ... update the download count (download.php is not called here)
					$update_count[] = $attachment['attach_id'];
					break;

				// Real Media Streams
				case ATTACHMENT_CATEGORY_RM:
					$l_downloaded_viewed = $user->lang['VIEWED'];
					$download_link = $filename;

					$additional_array['VAR'][] = '{U_FORUM}';
					$additional_array['VAL'][] = generate_board_url();
					$additional_array['VAR'][] = '{ATTACH_ID}';
					$additional_array['VAL'][] = $attachment['attach_id'];

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

			$l_download_count = (!isset($attachment['download_count']) || $attachment['download_count'] == 0) ? $user->lang['DOWNLOAD_NONE'] : (($attachment['download_count'] == 1) ? sprintf($user->lang['DOWNLOAD_COUNT'], $attachment['download_count']) : sprintf($user->lang['DOWNLOAD_COUNTS'], $attachment['download_count']));

			$current_block = ($display_cat) ? $blocks[$display_cat] : 'FILE';
			
			$template_array['VAR'] = array_merge($additional_array['VAR'], array(
				'{DOWNLOAD_NAME}', '{FILESIZE}', '{SIZE_VAR}', '{COMMENT}', '{U_DOWNLOAD_LINK}', '{UPLOAD_IMG}', '{L_DOWNLOADED_VIEWED}', '{L_DOWNLOAD_COUNT}')
			);

			$template_array['VAL'] = array_merge($additional_array['VAL'], array(
				$display_name, $filesize, $size_lang, $comment, $download_link, $upload_image, $l_downloaded_viewed, $l_download_count)
			);

			$tpl = str_replace($template_array['VAR'], $template_array['VAL'], $attachment_tpl[$current_block]);

			// Replace {L_*} lang strings
			$tpl = preg_replace('/{L_([A-Z_]+)}/e', "(!empty(\$user->lang['\$1'])) ? \$user->lang['\$1'] : ucwords(strtolower(str_replace('_', ' ', '\$1')))", $tpl);

			if (!$return)
			{
				$template->assign_block_vars($blockname, array(
					'DISPLAY_ATTACHMENT' => $tpl)
				);
			}
			else
			{
				$return_tpl[] = $tpl;
			}
		}
	}

	return $return_tpl;
//	$mtime = explode(' ', microtime());
//	$totaltime = $mtime[0] + $mtime[1] - $starttime;
}

?>