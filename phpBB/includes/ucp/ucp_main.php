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
		global $censors, $config, $db, $user, $auth, $SID, $template, $phpbb_root_path, $phpEx;

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

					$view_topic_url = "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id";

					$last_post_img = "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;p=" . $row['topic_last_post_id'] . '#' . $row['topic_last_post_id'] . '">' . $user->img('icon_post_latest', 'VIEW_LATEST_POST') . '</a>';

					$last_post_author = ($row['topic_last_poster_id'] == ANONYMOUS) ? (($row['topic_last_poster_name'] != '') ? $row['topic_last_poster_name'] . ' ' : $user->lang['GUEST'] . ' ') : "<a href=\"memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u="  . $row['topic_last_poster_id'] . '">' . $row['topic_last_poster_name'] . '</a>';

					$template->assign_block_vars('topicrow', array(
						'FORUM_ID' 			=> $forum_id,
						'TOPIC_ID' 			=> $topic_id,
						'LAST_POST_TIME'	=> $user->format_date($row['topic_last_post_time']),
						'LAST_POST_AUTHOR' 	=> $last_post_author,
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

				//TODO
/*
				$sql_and = '';
				$sql = 'SELECT COUNT(post_id) AS total_posts
					FROM ' . POSTS_TABLE . '
					WHERE post_time > ' . $user->data['user_lastvisit'] . "
						$sql_and";
				$result = $db->sql_query($sql);
				
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$user_id = $user->data['user_id'];

				// Grab all the relevant data
				$sql = "SELECT COUNT(p.post_id) AS num_posts   
					FROM " . POSTS_TABLE . " p, " . FORUMS_TABLE . " f
					WHERE p.poster_id = $user_id 
						AND f.forum_id = p.forum_id 
						$post_count_sql";
				$result = $db->sql_query($sql);

				$num_real_posts = min($row['user_posts'], $db->sql_fetchfield('num_posts', 0, $result));
				$db->sql_freeresult($result);

				$sql = "SELECT f.forum_id, f.forum_name, COUNT(post_id) AS num_posts   
					FROM " . POSTS_TABLE . " p, " . FORUMS_TABLE . " f 
					WHERE p.poster_id = $user_id 
						AND f.forum_id = p.forum_id 
						$post_count_sql
					GROUP BY f.forum_id, f.forum_name  
					ORDER BY num_posts DESC"; 
				$result = $db->sql_query_limit($sql, 1);

				$active_f_row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$sql = "SELECT t.topic_id, t.topic_title, COUNT(p.post_id) AS num_posts   
					FROM " . POSTS_TABLE . " p, " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f  
					WHERE p.poster_id = $user_id 
						AND t.topic_id = p.topic_id  
						AND f.forum_id = t.forum_id 
						$post_count_sql
					GROUP BY t.topic_id, t.topic_title  
					ORDER BY num_posts DESC";
				$result = $db->sql_query_limit($sql, 1);

				$active_t_row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				// Do the relevant calculations 
				$memberdays = max(1, round((time() - $row['user_regdate']) / 86400));
				$posts_per_day = $row['user_posts'] / $memberdays;
				$percentage = ($config['num_posts']) ? min(100, ($num_real_posts / $config['num_posts']) * 100) : 0;

				$active_f_name = $active_f_id = $active_f_count = $active_f_pct = '';
				if (!empty($active_f_row['num_posts']))
				{
					$active_f_name = $active_f_row['forum_name'];
					$active_f_id = $active_f_row['forum_id'];
					$active_f_count = $active_f_row['num_posts'];
					$active_f_pct = ($active_f_count / $row['user_posts']) * 100;
				}
				unset($active_f_row);

				$active_t_name = $active_t_id = $active_t_count = $active_t_pct = '';
				if (!empty($active_t_row['num_posts']))
				{
					$active_t_name = $active_t_row['topic_title'];
					$active_t_id = $active_t_row['topic_id'];
					$active_t_count = $active_t_row['num_posts'];
					$active_t_pct = ($active_t_count / $row['user_posts']) * 100;
				}
				unset($active_t_row);

				$template->assign_vars(show_profile($row));

				$template->assign_vars(array(
					'POSTS_DAY'			=> sprintf($user->lang['POST_DAY'], $posts_per_day),
					'POSTS_PCT'			=> sprintf($user->lang['POST_PCT'], $percentage),
					'ACTIVE_FORUM'		=> $active_f_name, 
					'ACTIVE_FORUM_POSTS'=> ($active_f_count == 1) ? sprintf($user->lang['USER_POST'], 1) : sprintf($user->lang['USER_POSTS'], $active_f_count), 
					'ACTIVE_FORUM_PCT'	=> sprintf($user->lang['POST_PCT'], $active_f_pct), 
					'ACTIVE_TOPIC'		=> $active_t_name,
					'ACTIVE_TOPIC_POSTS'=> ($active_t_count == 1) ? sprintf($user->lang['USER_POST'], 1) : sprintf($user->lang['USER_POSTS'], $active_t_count), 
					'ACTIVE_TOPIC_PCT'	=> sprintf($user->lang['POST_PCT'], $active_t_pct), 

					'OCCUPATION'	=> (!empty($row['user_occ'])) ? $row['user_occ'] : '',
					'INTERESTS'		=> (!empty($row['user_interests'])) ? $row['user_interests'] : '',

					'S_PROFILE_ACTION'	=> "groupcp.$phpEx$SID", 
					'S_GROUP_OPTIONS'	=> $group_options, 

					'U_ACTIVE_FORUM'	=> "viewforum.$phpEx$SID&amp;f=$active_f_id",
					'U_ACTIVE_TOPIC'	=> "viewtopic.$phpEx$SID&amp;t=$active_t_id",)
				);
*/
				break;

			case 'watched':

				if ($_POST['unwatch'])
				{
					$forums = (isset($_POST['f'])) ? implode(', ', array_map('intval', array_keys($_POST['f']))) : false;
					$topics = (isset($_POST['t'])) ? implode(', ', array_map('intval', array_keys($_POST['t']))) : false;

					if ($forums || $topics)
					{
						$l_unwatch = '';
						if ($forums)
						{
							$sql = 'DELETE FROM ' . FORUMS_WATCH_TABLE . "
								WHERE forum_id IN ($forums) 
									AND user_id = " .$user->data['user_id'];
							$db->sql_query($sql);

							$l_unwatch .= '_FORUMS';
						}

						if ($topics)
						{
							$sql = 'DELETE FROM ' . TOPICS_WATCH_TABLE . "
								WHERE topic_id IN ($topics) 
									AND user_id = " .$user->data['user_id'];
							$db->sql_query($sql);

							$l_unwatch .= '_TOPICS';
						}

						$message = $user->lang['UNWATCHED' . $l_unwatch] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], "<a href=\"ucp.$phpEx$SID&amp;i=$id&amp;mode=watched\">", '</a>');

						meta_refresh(3, "ucp.$phpEx$SID&amp;i=$id&amp;mode=watched");
						trigger_error($message);
					}
				}

				if ($config['load_db_lastread'])
				{
					switch (SQL_LAYER)
					{
						case 'oracle':
							break;

						default:
							$sql_lastread = 'LEFT JOIN ' . FORUMS_TRACK_TABLE . ' ft ON (ft.user_id = ' . $user->data['user_id'] . ' AND ft.forum_id = f.forum_id)';
							break;
					}
					$lastread_select = ', ft.mark_time ';
				}
				else
				{
					$sql_lastread = $lastread_select = '';

					$tracking_topics = (isset($_COOKIE[$config['cookie_name'] . '_t'])) ? unserialize($_COOKIE[$config['cookie_name'] . '_t']) : array();
					$tracking_forums = (isset($_COOKIE[$config['cookie_name'] . '_f'])) ? unserialize($_COOKIE[$config['cookie_name'] . '_f']) : array();
				}

				$sql = "SELECT f.*$lastread_select 
					FROM (" . FORUMS_TABLE . " f 
					$sql_lastread), " . FORUMS_WATCH_TABLE . ' fw
					WHERE fw.user_id = ' . $user->data['user_id'] . ' 
						AND f.forum_id = fw.forum_id 
					ORDER BY left_id';
				$result = $db->sql_query($sql);

				$i = 0;
				while ($row = $db->sql_fetchrow($result))
				{
					$forum_id = $row['forum_id'];

					$unread_forum = false;
					$forum_check = (!$config['load_db_lastread']) ? $tracking_forums[$forum_id] : $row['mark_time'];

					if ($forum_check < $row['forum_last_post_time'])
					{
						$unread_forum = true;
					}
	
					// Which folder should we display?
					if ($row['forum_status'] == ITEM_LOCKED)
					{
						$folder_image = ($unread_forum) ? 'folder_locked_new' : 'folder_locked';
						$folder_alt = 'FORUM_LOCKED';
					}
					else
					{
						$folder_image = ($unread_forum) ? 'folder_new' : 'folder';
						$folder_alt = ($unread_forum) ? 'NEW_POSTS' : 'NO_NEW_POSTS';
					}

					// Create last post link information, if appropriate
					if ($row['forum_last_post_id'])
					{
						$last_post_time = $user->format_date($row['forum_last_post_time']);

						$last_poster = ($row['forum_last_poster_name'] != '') ? $row['forum_last_poster_name'] : $user->lang['GUEST'];
						$last_poster_url = ($row['forum_last_poster_id'] == ANONYMOUS) ? '' : "memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u="  . $row['forum_last_poster_id'];

						$last_post_url = "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;p=" . $row['forum_last_post_id'] . '#' . $row['forum_last_post_id'];
					}
					else
					{
						$last_post_time = $last_poster = $last_poster_url = $last_post_url = '';
					}

					$template->assign_block_vars('forumrow', array(
						'FORUM_ID'			=> $forum_id, 
						'FORUM_FOLDER_IMG'	=> $user->img($folder_image, $folder_alt),
						'FORUM_NAME'		=> $row['forum_name'],
						'LAST_POST_IMG'		=> $user->img('icon_post_latest', 'VIEW_LATEST_POST'), 
						'LAST_POST_TIME'	=> $last_post_time,
						'LAST_POST_AUTHOR'	=> $last_poster,
						
						'U_LAST_POST_AUTHOR'=> $last_poster_url, 
						'U_LAST_POST'		=> $last_post_url, 
						'U_VIEWFORUM'		=> "viewforum.$phpEx$SID&amp;f=" . $row['forum_id'], 

						'S_ROW_COUNT'		=> $i++)
					);
				}
				$db->sql_freeresult($result);


				// Subscribed Topics
				$sql_t_tracking = ($config['load_db_lastread'] || $config['load_db_track']) ? 'LEFT JOIN ' . TOPICS_TRACK_TABLE . ' tt ON (tt.topic_id = t.topic_id AND tt.user_id = ' . $user->data['user_id'] . ')' : '';
				$sql_f_tracking = ($config['load_db_lastread']) ? 'LEFT JOIN ' . FORUMS_TRACK_TABLE . ' ft ON (ft.forum_id = t.forum_id AND ft.user_id = ' . $user->data['user_id'] . ')' : '';

				$sql_t_select = ($config['load_db_lastread'] || $config['load_db_track']) ? ', tt.mark_type, tt.mark_time' : '';
				$sql_f_select = ($config['load_db_lastread']) ? ', ft.mark_time AS forum_mark_time' : '';

				$sql = "SELECT t.* $sql_f_select $sql_t_select 
					FROM ((" . TOPICS_TABLE . " t
						$sql_f_tracking) $sql_t_tracking), " . TOPICS_WATCH_TABLE . ' tw
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
						$forum_check = (!$config['load_db_lastread']) ? $tracking_forums[$forum_id] : $row['forum_mark_time'];

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

						'S_ROW_COUNT'		=> $i++, 
						'S_USER_POSTED'		=> (!empty($row['mark_type'])) ? true : false, 

						'U_VIEW_TOPIC'		=> $view_topic_url)
					);
				}
				$db->sql_freeresult($result);

				break;
		}


		$template->assign_vars(array( 
			'L_TITLE'	=> $user->lang['UCP_' . strtoupper($submode)],

			'S_DISPLAY_MARK_ALL'				=> ($submode == 'watched') ? true : false, 
			'S_DISPLAY_' . strtoupper($submode)	=> true, 
			'S_HIDDEN_FIELDS'					=> $s_hidden_fields,
			'S_UCP_ACTION'						=> "ucp.$phpEx$SID&amp;i=$id&amp;mode=$submode")
		);

		$this->display($user->lang['UCP_MAIN'], 'ucp_main.html');
	}
}

?>