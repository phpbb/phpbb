<?php
/***************************************************************************
 *                               ucp_main.php
 *                            -------------------
 *   begin                : Saturday, Feb 21, 2003
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

class ucp_main extends ucp
{
	function main($id)
	{
		global $config, $censors, $db, $user, $auth, $SID, $template, $phpEx;

		$submode = ($_REQUEST['mode']) ? htmlspecialchars($_REQUEST['mode']) : 'front';

		// Setup internal subsection display
		$submodules['FRONT']	= "i=$id&amp;mode=front";
		$submodules['WATCHED']	= "i=$id&amp;mode=watched";

		$this->subsection($submodules, $submode);
		unset($submodules);

		switch ($submode)
		{
			case 'front':

				if ($config['load_db_lastread'])
				{
					$sql = 'SELECT mark_time 
						FROM ' . FORUMS_TRACK_TABLE . ' 
						WHERE forum_id = 0
							AND user_id = ' . $user->data['user_id'];
					$result = $db->sql_query($sql);

					$track_data = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);
				}
				else
				{
					$tracking_topics = (isset($_COOKIE[$config['cookie_name'] . '_t'])) ? unserialize($_COOKIE[$config['cookie_name'] . '_t']) : array();
					$tracking_forums = (isset($_COOKIE[$config['cookie_name'] . '_f'])) ? unserialize($_COOKIE[$config['cookie_name'] . '_f']) : array();
				}

				$i = 0;
				$topic_type = $user->lang['VIEW_TOPIC_ANNOUNCEMENT'];
				$folder = 'folder_announce';
				$folder_new = $folder . '_new';

				$sql_tracking = (($config['load_db_lastread'] || $config['load_db_track']) && $user->data['user_id'] != ANONYMOUS) ? 'LEFT JOIN ' . TOPICS_TRACK_TABLE . ' tt ON (tt.topic_id = t.topic_id AND tt.user_id = ' . $user->data['user_id'] . ')' : '';
				$sql_select = (($config['load_db_lastread'] || $config['load_db_track']) && $user->data['user_id'] != ANONYMOUS) ? ', tt.mark_type, tt.mark_time' : '';
				$sql = "SELECT t.* $sql_select 
					FROM (" . TOPICS_TABLE . " t
						$sql_tracking)
					WHERE t.forum_id = 0
						AND t.topic_type = " . POST_ANNOUNCE . '
					ORDER BY t.topic_last_post_time DESC';
				$result = $db->sql_query_limit($sql, $config['topics_per_page']);

				while ($row = $db->sql_fetchrow($result))
				{
					$forum_id = $row['forum_id'];
					$topic_id = $row['topic_id'];

					if ($row['topic_status'] == ITEM_LOCKED)
					{
						$topic_type = $user->lang['VIEW_TOPIC_LOCKED'];
						$folder = 'folder_locked';
						$folder_new = 'folder_locked_new';
					}

					$unread_topic = ($user->data['user_id'] != ANONYMOUS) ? true : false;
					if ($user->data['user_id'] != ANONYMOUS)
					{
						$topic_check = (!$config['load_db_lastread']) ? $tracking_topics[$topic_id] : $row['mark_time'];
						$forum_check = (!$config['load_db_lastread']) ? $tracking_forums[$forum_id] : $track_data['mark_time'];

						if ($topic_check > $row['topic_last_post_time'] || $forum_check > $row['topic_last_post_time'])
						{
							$unread_topic = false;
						}
					}

					$newest_post_img = ($unread_topic) ? "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;view=unread\">" . $user->img('icon_post_newest', 'VIEW_NEWEST_POST') . '</a> ' : '';
					$folder_img = ($unread_topic) ? $folder_new : $folder;
					$folder_alt = ($unread_topic) ? 'NEW_POSTS' : (($row['topic_status'] == ITEM_LOCKED) ? 'TOPIC_LOCKED' : 'NO_NEW_POSTS');

					// Posted image?
					if (!empty($row['mark_type']))
					{
						$folder_img .= '_posted';
					}

					// Goto message generation
					$replies = ($auth->acl_get('m_approve')) ? $row['topic_replies_real'] : $row['topic_replies'];

					if (($replies + 1) > intval($config['posts_per_page']))
					{
						$total_pages = ceil(($replies + 1) / intval($config['posts_per_page']));
						$goto_page = ' [ ' . $user->img('icon_post', 'GOTO_PAGE') . $user->lang['GOTO_PAGE'] . ': ';

						$times = 1;
						for($j = 0; $j < $replies + 1; $j += intval($config['posts_per_page']))
						{
							$goto_page .= "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;start=$j\">$times</a>";
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

					$view_topic_url = "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id";

					$last_post_img = "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;p=" . $row['topic_last_post_id'] . '#' . $row['topic_last_post_id'] . '">' . $user->img('icon_post_latest', 'VIEW_LATEST_POST') . '</a>';

					$last_post_author = ($row['topic_last_poster_id'] == ANONYMOUS) ? (($row['topic_last_poster_name'] != '') ? $row['topic_last_poster_name'] . ' ' : $user->lang['GUEST'] . ' ') : "<a href=\"memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u="  . $row['topic_last_poster_id'] . '">' . $row['topic_last_poster_name'] . '</a>';

					$template->assign_block_vars('topicrow', array(
						'FORUM_ID' 			=> $forum_id,
						'TOPIC_ID' 			=> $topic_id,
						'LAST_POST_TIME'	=> $user->format_date($row['topic_last_post_time']),
						'LAST_POST_AUTHOR' 	=> $last_post_author,
						'GOTO_PAGE' 		=> $goto_page, 
						'TOPIC_TITLE' 		=> (!empty($censors)) ? preg_replace($censors['match'], $censors['replace'], $row['topic_title']) : $row['topic_title'],
						'TOPIC_TYPE' 		=> $topic_type,

						'LAST_POST_IMG' 	=> $last_post_img,
						'NEWEST_POST_IMG' 	=> $newest_post_img,
						'TOPIC_FOLDER_IMG' 	=> $user->img($folder_img, $folder_alt),
						'ATTACH_ICON_IMG'	=> ($auth->acl_get('f_download', $forum_id) && $row['topic_attachment']) ? $user->img('icon_attach', '') : '',

						'S_ROW_COUNT'			=> $i, 
						'S_USER_POSTED'			=> (!empty($row['mark_type'])) ? true : false, 

						'U_VIEW_TOPIC'	=> $view_topic_url)
					);

					$i++;
				}
				$db->sql_freeresult($result);



				break;

			case 'watched':

				// Subscribed Topics
				if ($config['load_db_lastread'])
				{
					$sql = 'SELECT mark_time 
						FROM ' . FORUMS_TRACK_TABLE . ' 
						WHERE forum_id = 0
							AND user_id = ' . $user->data['user_id'];
					$result = $db->sql_query($sql);

					$track_data = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);
				}
				else
				{
					$tracking_topics = (isset($_COOKIE[$config['cookie_name'] . '_t'])) ? unserialize($_COOKIE[$config['cookie_name'] . '_t']) : array();
					$tracking_forums = (isset($_COOKIE[$config['cookie_name'] . '_f'])) ? unserialize($_COOKIE[$config['cookie_name'] . '_f']) : array();
				}


				$sql_tracking = (($config['load_db_lastread'] || $config['load_db_track']) && $user->data['user_id'] != ANONYMOUS) ? 'LEFT JOIN ' . TOPICS_TRACK_TABLE . ' tt ON (tt.topic_id = t.topic_id AND tt.user_id = ' . $user->data['user_id'] . ')' : '';
				$sql_select = (($config['load_db_lastread'] || $config['load_db_track']) && $user->data['user_id'] != ANONYMOUS) ? ', tt.mark_type, tt.mark_time' : '';
				$sql = "SELECT t.* $sql_select 
					FROM (" . TOPICS_TABLE . " t
						$sql_tracking), " . TOPICS_WATCH_TABLE . ' tw
					WHERE tw.user_id = ' . $user->data['user_id'] . '
						AND t.topic_id = tw.topic_id 
					ORDER BY t.topic_last_post_time DESC';
				$result = $db->sql_query_limit($sql, $config['topics_per_page']);

				$i = 0;
				while ($row = $db->sql_fetchrow($result))
				{
					$forum_id = $row['forum_id'];
					$topic_id = $row['topic_id'];

					// Goto message generation
					$replies = ($auth->acl_get('m_approve')) ? $row['topic_replies_real'] : $row['topic_replies'];

					
					$topic_type = '';
					switch ($row['topic_type'])
					{
						case POST_ANNOUNCE:
							$topic_type = $user->lang['VIEW_TOPIC_ANNOUNCEMENT'];
							$folder = 'folder_announce';
							$folder_new = 'folder_announce_new';
							break;

						case POST_STICKY:
							$topic_type = $user->lang['VIEW_TOPIC_STICKY'];
							$folder = 'folder_sticky';
							$folder_new = 'folder_sticky_new';
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

					if ($row['topic_status'] == ITEM_LOCKED)
					{
						$topic_type = $user->lang['VIEW_TOPIC_LOCKED'];
						$folder = 'folder_locked';
						$folder_new = 'folder_locked_new';
					}

					$unread_topic = ($user->data['user_id'] != ANONYMOUS) ? true : false;
					if ($user->data['user_id'] != ANONYMOUS)
					{
						$topic_check = (!$config['load_db_lastread']) ? $tracking_topics[$topic_id] : $row['mark_time'];
						$forum_check = (!$config['load_db_lastread']) ? $tracking_forums[$forum_id] : $track_data['mark_time'];

						if ($topic_check > $row['topic_last_post_time'] || $forum_check > $row['topic_last_post_time'])
						{
							$unread_topic = false;
						}
					}

					$newest_post_img = ($unread_topic) ? "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;view=unread\">" . $user->img('icon_post_newest', 'VIEW_NEWEST_POST') . '</a> ' : '';
					$folder_img = ($unread_topic) ? $folder_new : $folder;
					$folder_alt = ($unread_topic) ? 'NEW_POSTS' : (($row['topic_status'] == ITEM_LOCKED) ? 'TOPIC_LOCKED' : 'NO_NEW_POSTS');

					// Posted image?
					if (!empty($row['mark_type']))
					{
						$folder_img .= '_posted';
					}

					if (($replies + 1) > intval($config['posts_per_page']))
					{
						$total_pages = ceil(($replies + 1) / intval($config['posts_per_page']));
						$goto_page = ' [ ' . $user->img('icon_post', 'GOTO_PAGE') . $user->lang['GOTO_PAGE'] . ': ';

						$times = 1;
						for($j = 0; $j < $replies + 1; $j += intval($config['posts_per_page']))
						{
							$goto_page .= "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;start=$j\">$times</a>";
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

					$view_topic_url = "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id";

					$last_post_img = "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;p=" . $row['topic_last_post_id'] . '#' . $row['topic_last_post_id'] . '">' . $user->img('icon_post_latest', 'VIEW_LATEST_POST') . '</a>';

					$last_post_author = ($row['topic_last_poster_id'] == ANONYMOUS) ? (($row['topic_last_poster_name'] != '') ? $row['topic_last_poster_name'] . ' ' : $user->lang['GUEST'] . ' ') : "<a href=\"memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u="  . $row['topic_last_poster_id'] . '">' . $row['topic_last_poster_name'] . '</a>';

					$template->assign_block_vars('topicrow', array(
						'FORUM_ID' 			=> $forum_id,
						'TOPIC_ID' 			=> $topic_id,
						'LAST_POST_TIME'	=> $user->format_date($row['topic_last_post_time']),
						'LAST_POST_AUTHOR' 	=> $last_post_author,
						'GOTO_PAGE' 		=> $goto_page, 
						'TOPIC_TITLE' 		=> (!empty($censors)) ? preg_replace($censors['match'], $censors['replace'], $row['topic_title']) : $row['topic_title'],
						'TOPIC_TYPE' 		=> $topic_type,

						'LAST_POST_IMG' 	=> $last_post_img,
						'NEWEST_POST_IMG' 	=> $newest_post_img,
						'TOPIC_FOLDER_IMG' 	=> $user->img($folder_img, $folder_alt),
						'ATTACH_ICON_IMG'	=> ($auth->acl_get('f_download', $forum_id) && $row['topic_attachment']) ? $user->img('icon_attach', '') : '',

						'S_ROW_COUNT'			=> $i, 
						'S_USER_POSTED'			=> (!empty($row['mark_type'])) ? true : false, 

						'U_VIEW_TOPIC'	=> $view_topic_url)
					);

					$i++;
				}
				$db->sql_freeresult($result);


				// Subscribed Forums
				$sql = "SELECT f.forum_id, f.forum_last_post_time, f.forum_last_post_id, f.left_id, f.right_id, f.forum_status, f.forum_name, f.forum_desc 
					FROM " . FORUMS_TABLE . " f, " . FORUMS_WATCH_TABLE . " fw
					WHERE f.forum_id = fw.forum_id 
						AND fw.user_id = " . $user->data['user_id'] . " 
					ORDER BY f.forum_last_post_time DESC";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{

				}
				$db->sql_freeresult($result);

				break;
		}


		$template->assign_vars(array( 
			'L_TITLE'	=> $user->lang['UCP_' . strtoupper($submode)],

			'S_DISPLAY_' . strtoupper($submode)	=> true, 
			'S_HIDDEN_FIELDS'					=> $s_hidden_fields,
			'S_UCP_ACTION'						=> "ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode")
		);

		$this->output($user->lang['UCP_MAIN'], 'ucp_main.html');
	}
}

?>