<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : ucp_main.php
// STARTED   : Mon May 19, 2003
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

class ucp_main extends module  
{
	function ucp_main($id, $mode)
	{
		global $config, $db, $user, $auth, $SID, $template, $phpbb_root_path, $phpEx;

		switch ($mode)
		{
			case 'front':

				$user->add_lang('memberlist');

				if ($config['load_db_lastread'] || $config['load_db_track'])
				{
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

					switch (SQL_LAYER)
					{
						case 'oracle':
							break;

						default:
							$sql_from = '(' . TOPICS_TABLE . ' t LEFT JOIN ' . TOPICS_TRACK_TABLE . ' tt ON (tt.topic_id = t.topic_id AND tt.user_id = ' . $user->data['user_id'] . '))';
							break;
					}
					$sql_select = ', tt.mark_type, tt.mark_time';

				}
				else
				{
					$sql_from = TOPICS_TABLE . ' t ';
					$sql_select = '';
				}

				$tracking_topics = (isset($_COOKIE[$config['cookie_name'] . '_track'])) ? unserialize(stripslashes($_COOKIE[$config['cookie_name'] . '_track'])) : array();

				$i = 0;
				$topic_type = $user->lang['VIEW_TOPIC_ANNOUNCEMENT'];
				$folder = 'folder_announce';
				$folder_new = $folder . '_new';

				$sql = "SELECT t.* $sql_select 
					FROM $sql_from
					WHERE t.forum_id = 0
						AND t.topic_type = " . POST_GLOBAL . '
					ORDER BY t.topic_last_post_time DESC';
				$result = $db->sql_query($sql);

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

					$unread_topic = true;

					$topic_check = (!$config['load_db_lastread']) ? base_convert($tracking_topics[0][base_convert($topic_id, 10, 36)], 36, 10) + $config['board_startdate'] : $row['mark_time'];

					if (!$config['load_db_lastread'])
					{
						$forum_check = '';
						foreach ($tracking_topics as $forum_id => $tracking_time)
						{
							if ($tracking_time[0] > $forum_check)
							{
								$forum_check = $tracking_time[0];
							}
						}
						$forum_check = base_convert($forum_check, 36, 10) + $config['board_startdate'];
					}
					else
					{
						$forum_check = $track_data['mark_time'];
					}

					
					if ($topic_check > $row['topic_last_post_time'] || $forum_check > $row['topic_last_post_time'])
					{
						$unread_topic = false;
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

					$last_post_img = "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;p=" . $row['topic_last_post_id'] . '#' . $row['topic_last_post_id'] . '">' . $user->img('icon_post_latest', 'VIEW_LATEST_POST') . '</a>';

					$last_post_author = ($row['topic_last_poster_id'] == ANONYMOUS) ? (($row['topic_last_poster_name'] != '') ? $row['topic_last_poster_name'] . ' ' : $user->lang['GUEST'] . ' ') : "<a href=\"memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u="  . $row['topic_last_poster_id'] . '">' . $row['topic_last_poster_name'] . '</a>';

					$template->assign_block_vars('topicrow', array(
						'FORUM_ID' 			=> $forum_id,
						'TOPIC_ID' 			=> $topic_id,
						'LAST_POST_TIME'	=> $user->format_date($row['topic_last_post_time']),
						'LAST_POST_AUTHOR' 	=> $last_post_author,
						'TOPIC_TITLE' 		=> censor_text($row['topic_title']),
						'TOPIC_TYPE' 		=> $topic_type,

						'LAST_POST_IMG' 	=> $last_post_img,
						'NEWEST_POST_IMG' 	=> $newest_post_img,
						'TOPIC_FOLDER_IMG' 	=> $user->img($folder_img, $folder_alt),
						'ATTACH_ICON_IMG'	=> ($auth->acl_gets('f_download', 'u_download', $forum_id) && $row['topic_attachment']) ? $user->img('icon_attach', '') : '',

						'S_ROW_COUNT'		=> $i, 
						'S_USER_POSTED'		=> (!empty($row['mark_type'])) ? true : false, 

						'U_VIEW_TOPIC'	=> $view_topic_url)
					);

					$i++;
				}
				$db->sql_freeresult($result);

				$post_count_ary = $auth->acl_getf('f_postcount');
				
				$forum_ary = array();
				foreach ($post_count_ary as $forum_id => $allowed)
				{
					if ($allowed)
					{
						$forum_ary[] = $forum_id;
					}
				}

				$post_count_sql = (sizeof($forum_ary)) ? 'AND f.forum_id IN (' . implode(', ', $forum_ary) . ')' : '';
				unset($forum_ary, $post_count_ary);

				if ($post_count_sql)
				{
					// Grab all the relevant data
					$sql = 'SELECT COUNT(p.post_id) AS num_posts   
						FROM ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . ' f
						WHERE p.poster_id = ' . $user->data['user_id'] . " 
							AND f.forum_id = p.forum_id 
							$post_count_sql";
					$result = $db->sql_query($sql);

					$num_real_posts = min($user->data['user_posts'], $db->sql_fetchfield('num_posts', 0, $result));
					$db->sql_freeresult($result);

					$sql = 'SELECT f.forum_id, f.forum_name, COUNT(post_id) AS num_posts   
						FROM ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . ' f 
						WHERE p.poster_id = ' . $user->data['user_id'] . " 
							AND f.forum_id = p.forum_id 
							$post_count_sql
						GROUP BY f.forum_id, f.forum_name  
						ORDER BY num_posts DESC"; 
					$result = $db->sql_query_limit($sql, 1);

					$active_f_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					$sql = 'SELECT t.topic_id, t.topic_title, COUNT(p.post_id) AS num_posts   
						FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f  
						WHERE p.poster_id = ' . $user->data['user_id'] . " 
							AND t.topic_id = p.topic_id  
							AND f.forum_id = t.forum_id 
							$post_count_sql
						GROUP BY t.topic_id, t.topic_title  
						ORDER BY num_posts DESC";
					$result = $db->sql_query_limit($sql, 1);

					$active_t_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);
				}
				else
				{
					$num_real_posts = 0;
					$active_f_row = $active_t_row = array();
				}

				// Do the relevant calculations 
				$memberdays = max(1, round((time() - $user->data['user_regdate']) / 86400));
				$posts_per_day = $user->data['user_posts'] / $memberdays;
				$percentage = ($config['num_posts']) ? min(100, ($num_real_posts / $config['num_posts']) * 100) : 0;

				$active_f_name = $active_f_id = $active_f_count = $active_f_pct = '';
				if (!empty($active_f_row['num_posts']))
				{
					$active_f_name = $active_f_row['forum_name'];
					$active_f_id = $active_f_row['forum_id'];
					$active_f_count = $active_f_row['num_posts'];
					$active_f_pct = ($active_f_count / $user->data['user_posts']) * 100;
				}
				unset($active_f_row);

				$active_t_name = $active_t_id = $active_t_count = $active_t_pct = '';
				if (!empty($active_t_row['num_posts']))
				{
					$active_t_name = $active_t_row['topic_title'];
					$active_t_id = $active_t_row['topic_id'];
					$active_t_count = $active_t_row['num_posts'];
					$active_t_pct = ($active_t_count / $user->data['user_posts']) * 100;
				}
				unset($active_t_row);


				$template->assign_vars(array(
					'USER_COLOR'		=> (!empty($user->data['user_colour'])) ? $user->data['user_colour'] : '', 
					'KARMA'				=> ($config['enable_karma']) ? $user->lang['KARMA'][$user->data['user_karma']] : '', 
					'JOINED'			=> $user->format_date($user->data['user_regdate'], $user->lang['DATE_FORMAT']),
					'VISITED'			=> (empty($last_visit)) ? ' - ' : $user->format_date($last_visit, $user->lang['DATE_FORMAT']),
					'POSTS'				=> ($user->data['user_posts']) ? $user->data['user_posts'] : 0,
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

					'KARMA_IMG'			=> ($config['enable_karma']) ? $user->img('karma_center', $user->lang['KARMA'][$user->data['user_karma']], false, (int) $user->data['user_karma']) : '', 
					'KARMA_LEFT_IMG'	=> $user->img('karma_left', ''),
					'KARMA_RIGHT_IMG'	=> $user->img('karma_right', ''),

//					'S_GROUP_OPTIONS'	=> $group_options, 

					'U_SEARCH_USER'		=> ($auth->acl_get('u_search')) ? "search.$phpEx$SID&amp;search_author=" . urlencode($user->data['username']) . "&amp;show_results=posts" : '',  
					'U_ACTIVE_FORUM'	=> "viewforum.$phpEx$SID&amp;f=$active_f_id",
					'U_ACTIVE_TOPIC'	=> "viewtopic.$phpEx$SID&amp;t=$active_t_id",)
				);
				break;

			case 'subscribed':

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
							$sql_from = '(' . FORUMS_TABLE . ' f  LEFT JOIN ' . FORUMS_TRACK_TABLE . ' ft ON (ft.user_id = ' . $user->data['user_id'] . ' AND ft.forum_id = f.forum_id))';
							break;
					}
					$lastread_select = ', ft.mark_time ';
				}
				else
				{
					$sql_from = FORUMS_TABLE . ' f ';
					$lastread_select = '';

					$tracking_topics = (isset($_COOKIE[$config['cookie_name'] . '_track'])) ? unserialize(stripslashes($_COOKIE[$config['cookie_name'] . '_track'])) : array();
				}

				$sql = "SELECT f.*$lastread_select 
					FROM $sql_from, " . FORUMS_WATCH_TABLE . ' fw
					WHERE fw.user_id = ' . $user->data['user_id'] . ' 
						AND f.forum_id = fw.forum_id 
					ORDER BY left_id';
				$result = $db->sql_query($sql);

				$i = 0;
				while ($row = $db->sql_fetchrow($result))
				{
					$forum_id = $row['forum_id'];

					$unread_forum = false;
					$forum_check = (!$config['load_db_lastread']) ? $tracking_topics[$forum_id][0] : $row['mark_time'];

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
				$sql_from = ($config['load_db_lastread'] || $config['load_db_track']) ? '(' . TOPICS_TABLE . ' t LEFT JOIN ' . TOPICS_TRACK_TABLE . ' tt ON (tt.topic_id = t.topic_id AND tt.user_id = ' . $user->data['user_id'] . '))' : TOPICS_TABLE . ' t';
//				$sql_f_tracking = ($config['load_db_lastread']) ? 'LEFT JOIN ' . FORUMS_TRACK_TABLE . ' ft ON (ft.forum_id = t.forum_id AND ft.user_id = ' . $user->data['user_id'] . ')' : '';

				$sql_t_select = ($config['load_db_lastread'] || $config['load_db_track']) ? ', tt.mark_type, tt.mark_time' : '';
//				$sql_f_select = ($config['load_db_lastread']) ? ', ft.mark_time AS forum_mark_time' : '';

				$sql = "SELECT t.* $sql_f_select $sql_t_select 
					FROM $sql_from, " . TOPICS_WATCH_TABLE . ' tw
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
						$topic_check = (!$config['load_db_lastread']) ? ((isset($tracking_topics[$forum_id][base_convert($topic_id, 10, 36)])) ? base_convert($tracking_topics[$forum_id36][$topic_id36], 36, 10) + $config['board_startdate'] : 0) : $row['mark_time'];
						$forum_check = (!$config['load_db_lastread']) ? ((isset($tracking_topics[$forum_id][0])) ? base_convert($tracking_topics[$forum_id][0], 36, 10) + $config['board_startdate'] : 0) : $row['forum_mark_time'];

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

					if (($replies + 1) > $config['posts_per_page'])
					{
						$total_pages = ceil(($replies + 1) / $config['posts_per_page']);
						$goto_page = ' [ ' . $user->img('icon_post', 'GOTO_PAGE') . $user->lang['GOTO_PAGE'] . ': ';

						$times = 1;
						for($j = 0; $j < $replies + 1; $j += $config['posts_per_page'])
						{
							$goto_page .= "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;start=$j\">$times</a>";
							if ($times == 1 && $total_pages > 4)
							{
								$goto_page .= ' ... ';
								$times = $total_pages - 3;
								$j += ($total_pages - 4) * $config['posts_per_page'];
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
						'TOPIC_TITLE' 		=> censor_text($row['topic_title']),
						'TOPIC_TYPE' 		=> $topic_type,

						'LAST_POST_IMG' 	=> $last_post_img,
						'NEWEST_POST_IMG' 	=> $newest_post_img,
						'TOPIC_FOLDER_IMG' 	=> $user->img($folder_img, $folder_alt),
						'ATTACH_ICON_IMG'	=> ($auth->acl_gets('f_download', 'u_download', $forum_id) && $row['topic_attachment']) ? $user->img('icon_attach', '') : '',

						'S_ROW_COUNT'		=> $i++, 
						'S_USER_POSTED'		=> (!empty($row['mark_type'])) ? true : false, 

						'U_VIEW_TOPIC'		=> $view_topic_url)
					);
				}
				$db->sql_freeresult($result);

				break;

			case 'bookmarks':
				
				if (!$config['allow_bookmarks'])
				{
					$template->assign_vars(array(
						'S_NO_DISPLAY_BOOKMARKS'	=> true)
					);
					break;
				}

				$move_up = request_var('move_up', 0);
				$move_down = request_var('move_down', 0);

				$sql = 'SELECT MAX(order_id) as max_order_id FROM ' . BOOKMARKS_TABLE . '
					WHERE user_id = ' . $user->data['user_id'];
				$result = $db->sql_query($sql);
				$max_order_id = $db->sql_fetchfield('max_order_id', 0, $result);
				$db->sql_freeresult($result);

				if ($move_up || $move_down)
				{
					if (($move_up && $move_up != 1) || ($move_down && $move_down != $max_order_id))
					{
						$order = ($move_up) ? $move_up : $move_down;
						$order_total = $order * 2 + (($move_up) ? -1 : 1);
		
						$sql = 'UPDATE ' . BOOKMARKS_TABLE . "
							SET order_id = $order_total - order_id
							WHERE order_id IN ($order, " . (($move_up) ? $order - 1 : $order + 1) . ')
								AND user_id = ' . $user->data['user_id'];
						$db->sql_query($sql);
					}
				}
				
				if (isset($_POST['unbookmark']))
				{
					$s_hidden_fields = '<input type="hidden" name="unbookmark" value="1" />';
					$topics = (isset($_POST['t'])) ? array_map('intval', array_keys($_POST['t'])) : array();
					$url = "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=main&amp;mode=bookmarks";
					
					if (!sizeof($topics))
					{
						trigger_error('NO_BOOKMARKS_SELECTED');
					}
					
					foreach ($topics as $topic_id)
					{
						$s_hidden_fields .= '<input type="hidden" name="t[' . $topic_id . ']" value="1" />';
					}

					if (confirm_box(true))
					{
						$sql = 'DELETE FROM ' . BOOKMARKS_TABLE . '
							WHERE user_id = ' . $user->data['user_id'] . '
								AND topic_id IN (' . implode(', ', $topics) . ')';
						$db->sql_query($sql);

						// Re-Order bookmarks (possible with one query? This query massaker is not really acceptable...)
						$sql = 'SELECT topic_id FROM ' . BOOKMARKS_TABLE . '
							WHERE user_id = ' . $user->data['user_id'] . '
							ORDER BY order_id ASC';
						$result = $db->sql_query($sql);

						$i = 1;
						while ($row = $db->sql_fetchrow($result))
						{
							$db->sql_query('UPDATE ' . BOOKMARKS_TABLE . "
								SET order_id = $i
								WHERE topic_id = {$row['topic_id']}
									AND user_id = {$user->data['user_id']}");
							$i++;
						}
						$db->sql_freeresult($result);

						meta_refresh(3, $url);
						$message = $user->lang['BOOKMARKS_REMOVED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $url . '">', '</a>');
						trigger_error($message);
					}
					else
					{
						confirm_box(false, 'REMOVE_SELECTED_BOOKMARKS', $s_hidden_fields);
					}
				}

				// We grab deleted topics here too...
				// NOTE: At the moment bookmarks are not removed with topics, might be useful later (not really sure how though. :D)
				// But since bookmarks are sensible to the user, they should not be deleted without notice.
				$sql = 'SELECT b.order_id, b.topic_id as b_topic_id, t.*, f.forum_name
					FROM ' . BOOKMARKS_TABLE . ' b
						LEFT JOIN ' . TOPICS_TABLE . ' t ON b.topic_id = t.topic_id
						LEFT JOIN ' . FORUMS_TABLE . ' f ON t.forum_id = f.forum_id
					WHERE b.user_id = ' . $user->data['user_id'] . '
					ORDER BY b.order_id ASC';
				$result = $db->sql_query($sql);
				
				$i = 0;
				while ($row = $db->sql_fetchrow($result))
				{
					$forum_id = $row['forum_id'];
					$topic_id = $row['b_topic_id'];

					$replies = ($auth->acl_get('m_approve')) ? $row['topic_replies_real'] : $row['topic_replies'];
					
					$topic_type = '';
					switch ($row['topic_type'])
					{
						case POST_ANNOUNCE:
							$topic_type = $user->lang['VIEW_TOPIC_ANNOUNCEMENT'];
							$folder = 'folder_announce';
							break;

						case POST_STICKY:
							$topic_type = $user->lang['VIEW_TOPIC_STICKY'];
							$folder = 'folder_sticky';
							break;

						default:
							if ($replies >= intval($config['hot_threshold']))
							{
								$folder = 'folder_hot';
							}
							else
							{
								$folder = 'folder';
							}
							break;
					}

					if ($row['topic_status'] == ITEM_LOCKED)
					{
						$topic_type = $user->lang['VIEW_TOPIC_LOCKED'];
						$folder = 'folder_locked';
					}

					$folder_alt = ($row['topic_status'] == ITEM_LOCKED) ? 'TOPIC_LOCKED' : 'TOPIC';
					$view_topic_url = "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id";
					$last_post_img = "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;p=" . $row['topic_last_post_id'] . '#' . $row['topic_last_post_id'] . '">' . $user->img('icon_post_latest', 'VIEW_LATEST_POST') . '</a>';

					$template->assign_block_vars('topicrow', array(
						'FORUM_ID' 			=> $forum_id,
						'TOPIC_ID' 			=> $topic_id,
						'S_DELETED_TOPIC'	=> (!$row['topic_id']) ? true : false,
						'TOPIC_TITLE' 		=> censor_text($row['topic_title']),
						'TOPIC_TYPE' 		=> $topic_type,
						'FORUM_NAME'		=> $row['forum_name'],
						'POSTED_AT'			=> $user->format_date($row['topic_time']),
						
						'TOPIC_FOLDER_IMG' 	=> $user->img($folder, $folder_alt),
						'ATTACH_ICON_IMG'	=> ($auth->acl_gets('f_download', 'u_download', $forum_id) && $row['topic_attachment']) ? $user->img('icon_attach', '') : '',

						'U_VIEW_TOPIC'		=> $view_topic_url,
						'U_VIEW_FORUM'		=> "{$phpbb_root_path}viewforum.$phpEx$SID&amp;f={$row['forum_id']}",
						'U_MOVE_UP'			=> ($row['order_id'] != 1) ? "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=main&amp;mode=bookmarks&amp;move_up={$row['order_id']}" : '',
						'U_MOVE_DOWN'		=> ($row['order_id'] != $max_order_id) ? "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=main&amp;mode=bookmarks&amp;move_down={$row['order_id']}" : '',
							
						'S_ROW_COUNT'		=> $i++)
					);
				}

				break;

			case 'drafts':
				global $ucp;
				
				$pm_drafts = ($ucp->name == 'pm') ? true : false;

				$user->add_lang('posting');

				$edit = (isset($_REQUEST['edit'])) ? true : false;
				$submit = (isset($_POST['submit'])) ? true : false;
				$draft_id = ($edit) ? intval($_REQUEST['edit']) : 0;

				$s_hidden_fields = ($edit) ? '<input type="hidden" name="edit" value="' . $draft_id . '" />' : '';
				$draft_subject = $draft_message = '';

				if ($_POST['delete'])
				{
					$drafts = (isset($_POST['d'])) ? implode(', ', array_map('intval', array_keys($_POST['d']))) : '';

					if ($drafts)
					{
						$sql = 'DELETE FROM ' . DRAFTS_TABLE . "
							WHERE draft_id IN ($drafts) 
								AND user_id = " .$user->data['user_id'];
						$db->sql_query($sql);

						$message = $user->lang['DRAFTS_DELETED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], "<a href=\"ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode\">", '</a>');

						meta_refresh(3, "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode");
						trigger_error($message);
					}
				}

				if ($submit && $edit)
				{
					$draft_subject = preg_replace('#&amp;(\#[0-9]+;)#', '&\1', request_var('subject', ''));
					$draft_message = (isset($_POST['message'])) ? htmlspecialchars(trim(str_replace(array('\\\'', '\\"', '\\0', '\\\\'), array('\'', '"', '\0', '\\'), $_POST['message']))) : '';
					$draft_message = preg_replace('#&amp;(\#[0-9]+;)#', '&\1', $draft_message);

					if ($draft_message && $draft_subject)
					{
						$draft_row = array(
							'draft_subject' => $draft_subject,
							'draft_message' => $draft_message
						);

						$sql = 'UPDATE ' . DRAFTS_TABLE . ' 
							SET ' . $db->sql_build_array('UPDATE', $draft_row) . " 
							WHERE draft_id = $draft_id
								AND user_id = " . $user->data['user_id'];
						$db->sql_query($sql);

						$message = $user->lang['DRAFT_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], "<a href=\"ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode\">", '</a>');

						meta_refresh(3, "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode");
						trigger_error($message);
					}
					else
					{
						$template->assign_var('ERROR', ($draft_message == '') ? $user->lang['EMPTY_DRAFT'] : (($draft_subject == '') ? $user->lang['EMPTY_DRAFT_TITLE'] : ''));
					}
				}

				if (!$pm_drafts)
				{
					$sql = 'SELECT d.*, f.forum_name
						FROM ' . DRAFTS_TABLE . ' d, ' . FORUMS_TABLE . ' f
						WHERE d.user_id = ' . $user->data['user_id'] . ' ' .
							(($edit) ? "AND d.draft_id = $draft_id" : '') . '
							AND f.forum_id = d.forum_id
							ORDER BY d.save_time DESC';
				}
				else
				{
					$sql = 'SELECT * FROM ' . DRAFTS_TABLE . '
						WHERE user_id = ' . $user->data['user_id'] . ' ' .
							(($edit) ? "AND draft_id = $draft_id" : '') . '
							AND forum_id = 0 
							AND topic_id = 0
							ORDER BY save_time DESC';
				}
				$result = $db->sql_query($sql);
				
				$draftrows = $topic_ids = array();

				while ($row = $db->sql_fetchrow($result))
				{
					if ($row['topic_id'])
					{
						$topic_ids[] = (int) $row['topic_id'];
					}
					$draftrows[] = $row;
				}
				$db->sql_freeresult($result);
				
				if (sizeof($topic_ids))
				{
					$sql = 'SELECT topic_id, forum_id, topic_title
						FROM ' . TOPICS_TABLE . '
						WHERE topic_id IN (' . implode(',', array_unique($topic_ids)) . ')';
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$topic_rows[$row['topic_id']] = $row;
					}
					$db->sql_freeresult($result);
				}
				unset($topic_ids);
				
				$template->assign_var('S_EDIT_DRAFT', $edit);

				$row_count = 0;
				foreach ($draftrows as $draft)
				{
					$link_topic = $link_forum = $link_pm = false;
					$insert_url = $view_url = $title = '';

					if (isset($topic_rows[$draft['topic_id']]) && $auth->acl_get('f_read', $topic_rows[$draft['topic_id']]['forum_id']))
					{
						$link_topic = true;
						$view_url = "viewtopic.$phpEx$SID&amp;f=" . $topic_rows[$draft['topic_id']]['forum_id'] . "&amp;t=" . $draft['topic_id'];
						$title = $topic_rows[$draft['topic_id']]['topic_title'];

						$insert_url = "posting.$phpEx$SID&amp;f=" . $topic_rows[$draft['topic_id']]['forum_id'] . '&amp;t=' . $draft['topic_id'] . '&amp;mode=reply&amp;d=' . $draft['draft_id'];
					}
					else if ($auth->acl_get('f_read', $draft['forum_id']))
					{
						$link_forum = true;
						$view_url = "viewforum.$phpEx$SID&amp;f=" . $draft['forum_id'];
						$title = $draft['forum_name'];

						$insert_url = "posting.$phpEx$SID&amp;f=" . $draft['forum_id'] . '&amp;mode=post&amp;d=' . $draft['draft_id'];
					}
					else if ($pm_drafts)
					{
						$link_pm = true;
						$insert_url = "ucp.$phpEx$SID&amp;i=$id&amp;mode=compose&amp;d=" . $draft['draft_id'];
					}
						
					$template_row = array(
						'DATE'			=> $user->format_date($draft['save_time']),
						'DRAFT_MESSAGE'	=> ($submit) ? $draft_message : $draft['draft_message'],
						'DRAFT_SUBJECT'	=> ($submit) ? $draft_subject : $draft['draft_subject'],
						'TITLE'			=> $title,

						'DRAFT_ID'	=> $draft['draft_id'],
						'FORUM_ID'	=> $draft['forum_id'],
						'TOPIC_ID'	=> $draft['topic_id'],

						'U_VIEW'		=> $view_url,
						'U_VIEW_EDIT'	=> "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode&amp;edit=" . $draft['draft_id'],
						'U_INSERT'		=> $insert_url,

						'S_ROW_COUNT'		=> $row_count++,
						'S_LINK_TOPIC'		=> $link_topic,
						'S_LINK_FORUM'		=> $link_forum,
						'S_LINK_PM'			=> $link_pm,
						'S_HIDDEN_FIELDS'	=> $s_hidden_fields
					);
						
					($edit) ? $template->assign_vars($template_row) : $template->assign_block_vars('draftrow', $template_row);
				}

				if (!$edit)
				{
					$template->assign_var('S_DRAFT_ROWS', $row_count);
				}

				break;
		}


		$template->assign_vars(array( 
			'L_TITLE'			=> $user->lang['UCP_MAIN_' . strtoupper($mode)],

			'S_DISPLAY_MARK_ALL'=> ($mode == 'watched' || ($mode == 'drafts' && !isset($_GET['edit']))) ? true : false, 
			'S_HIDDEN_FIELDS'	=> (isset($s_hidden_fields)) ? $s_hidden_fields : '',
			'S_UCP_ACTION'		=> $phpbb_root_path . "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode")
		);

		$this->display($user->lang['UCP_MAIN'], 'ucp_main_' . $mode . '.html');
	}

	function install()
	{
	}

	function uninstall()
	{
	}

	function module()
	{
		$details = array(
			'name'			=> 'UCP - Main',
			'description'	=> 'Front end for User Control Panel', 
			'filename'		=> 'main',
			'version'		=> '1.0.0', 
			'phpbbversion'	=> '2.2.0'
		);
		return $details;
	}
}

?>