<?php
/** 
*
* @package ucp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package ucp
* ucp_main
* UCP Front Panel
*/
class ucp_main
{
	var $p_master;
	
	function ucp_main(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $SID, $template, $phpbb_root_path, $phpEx;

		switch ($mode)
		{
			case 'front':

				$user->add_lang('memberlist');

				$sql_from = TOPICS_TABLE . ' t ';
				$sql_select = '';

				if ($config['load_db_track'])
				{
					$sql_from .= ' LEFT JOIN ' . TOPICS_POSTED_TABLE . ' tp ON (tp.topic_id = t.topic_id 
						AND tp.user_id = ' . $user->data['user_id'] . ')';
					$sql_select .= ', tp.topic_posted';
				}

				if ($config['load_db_lastread'])
				{
					$sql_from .= ' LEFT JOIN ' . TOPICS_TRACK_TABLE . ' tt ON (tt.topic_id = t.topic_id
						AND tt.user_id = ' . $user->data['user_id'] . ')';
					$sql_select .= ', tt.mark_time';
				}

				$topic_type = $user->lang['VIEW_TOPIC_ANNOUNCEMENT'];
				$folder = 'folder_announce';
				$folder_new = $folder . '_new';

				// Determine first forum the user is able to read into - for global announcement link
				$forum_ary = $auth->acl_getf('f_read');
				$g_forum_id = 0;

				foreach ($forum_ary as $forum_id => $allowed)
				{
					if (!$allowed['f_read'])
					{
						unset($forum_ary[$forum_id]);
					}
				}
				$forum_ary = array_unique(array_keys($forum_ary));

				$sql = 'SELECT forum_id 
					FROM ' . FORUMS_TABLE . '
					WHERE forum_type = ' . FORUM_POST . '
						AND forum_id IN (' . implode(', ', $forum_ary) . ')';
				$result = $db->sql_query_limit($sql, 1);
				$g_forum_id = (int) $db->sql_fetchfield('forum_id', 0, $result);
				$db->sql_freeresult($result);

				$sql = "SELECT t.* $sql_select 
					FROM $sql_from
					WHERE t.forum_id = 0
						AND t.topic_type = " . POST_GLOBAL . '
					ORDER BY t.topic_last_post_time DESC';
				$result = $db->sql_query($sql);

				$topic_list = $rowset = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$topic_list[] = $row['topic_id'];
					$rowset[$row['topic_id']] = $row;
				}
				$db->sql_freeresult($result);

				$topic_tracking_info = get_topic_tracking(0, $topic_list, $rowset, array(0 => false), $topic_list);

				foreach ($topic_list as $topic_id)
				{
					$row = &$rowset[$topic_id];

					$forum_id = $row['forum_id'];
					$topic_id = $row['topic_id'];

					$unread_topic = (isset($topic_tracking_info[$topic_id]) && $row['topic_last_post_time'] > $topic_tracking_info[$topic_id]) ? true : false;

					if ($row['topic_status'] == ITEM_LOCKED)
					{
						$topic_type = $user->lang['VIEW_TOPIC_LOCKED'];
						$folder = 'folder_locked';
						$folder_new = 'folder_locked_new';
					}

					$folder_img = ($unread_topic) ? $folder_new : $folder;
					$folder_alt = ($unread_topic) ? 'NEW_POSTS' : (($row['topic_status'] == ITEM_LOCKED) ? 'TOPIC_LOCKED' : 'NO_NEW_POSTS');

					// Posted image?
					if (!empty($row['topic_posted']) && $row['topic_posted'])
					{
						$folder_img .= '_posted';
					}

					$template->assign_block_vars('topicrow', array(
						'FORUM_ID' 			=> $forum_id,
						'TOPIC_ID' 			=> $topic_id,
						'LAST_POST_TIME'	=> $user->format_date($row['topic_last_post_time']),
						'LAST_POST_AUTHOR' 	=> ($row['topic_last_poster_id'] == ANONYMOUS) ? (($row['topic_last_poster_name'] != '') ? $row['topic_last_poster_name'] . ' ' : $user->lang['GUEST'] . ' ') : $row['topic_last_poster_name'],
						'TOPIC_TITLE' 		=> censor_text($row['topic_title']),
						'TOPIC_TYPE' 		=> $topic_type,

						'LAST_POST_IMG' 	=> $user->img('icon_post_latest', 'VIEW_LATEST_POST'),
						'NEWEST_POST_IMG' 	=> $user->img('icon_post_newest', 'VIEW_NEWEST_POST'),
						'TOPIC_FOLDER_IMG' 	=> $user->img($folder_img, $folder_alt),
						'TOPIC_FOLDER_IMG_SRC' => $user->img($folder_img, $folder_alt, false, '', 'src'),
						'ATTACH_ICON_IMG'	=> ($auth->acl_gets('f_download', 'u_download', $forum_id) && $row['topic_attachment']) ? $user->img('icon_attach', '') : '',

						'S_USER_POSTED'		=> (!empty($row['topic_posted']) && $row['topic_posted']) ? true : false,
						'S_UNREAD'			=> $unread_topic,

						'U_LAST_POST'		=> "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;f=$g_forum_id&amp;t=$topic_id&amp;p=" . $row['topic_last_post_id'] . '#' . $row['topic_last_post_id'],
						'U_LAST_POST_AUTHOR'=> ($row['topic_last_poster_id'] != ANONYMOUS) ? "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u="  . $row['topic_last_poster_id'] : '',
						'U_NEWEST_POST'		=> "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;f=$g_forum_id&amp;t=$topic_id&amp;view=unread#unread",
						'U_VIEW_TOPIC'		=> "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;f=$g_forum_id&amp;t=$topic_id")
					);
				}

				$post_count_ary = $auth->acl_getf('f_postcount');
				
				$forum_ary = array();
				foreach ($post_count_ary as $forum_id => $allowed)
				{
					if ($allowed['f_read'] && $allowed['f_postcount'])
					{
						$forum_ary[] = $forum_id;
					}
				}

				$post_count_sql = (sizeof($forum_ary)) ? 'AND f.forum_id IN (' . implode(', ', $forum_ary) . ')' : '';
				unset($forum_ary, $post_count_ary);

				if ($post_count_sql)
				{
					// NOTE: The following three queries could be a problem for big boards
					
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
					$active_f_pct = ($user->data['user_posts']) ? ($active_f_count / $user->data['user_posts']) * 100 : 0;
				}
				unset($active_f_row);

				$active_t_name = $active_t_id = $active_t_count = $active_t_pct = '';
				if (!empty($active_t_row['num_posts']))
				{
					$active_t_name = $active_t_row['topic_title'];
					$active_t_id = $active_t_row['topic_id'];
					$active_t_count = $active_t_row['num_posts'];
					$active_t_pct = ($user->data['user_posts']) ? ($active_t_count / $user->data['user_posts']) * 100 : 0;
				}
				unset($active_t_row);


				$template->assign_vars(array(
					'USER_COLOR'		=> (!empty($user->data['user_colour'])) ? $user->data['user_colour'] : '', 
					'JOINED'			=> $user->format_date($user->data['user_regdate'], $user->lang['DATE_FORMAT']),
					'VISITED'			=> (empty($last_visit)) ? ' - ' : $user->format_date($last_visit, $user->lang['DATE_FORMAT']),
					'WARNINGS'			=> ($user->data['user_warnings']) ? $user->data['user_warnings'] : 0,
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

//					'S_GROUP_OPTIONS'	=> $group_options, 

					'U_SEARCH_USER'		=> ($auth->acl_get('u_search')) ? "{$phpbb_root_path}search.$phpEx$SID&amp;search_author=" . urlencode($user->data['username']) . "&amp;show_results=posts" : '',  
					'U_ACTIVE_FORUM'	=> "{$phpbb_root_path}viewforum.$phpEx$SID&amp;f=$active_f_id",
					'U_ACTIVE_TOPIC'	=> "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;t=$active_t_id",)
				);
				break;

			case 'subscribed':

				include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
				$user->add_lang('viewforum');

				$unwatch = (isset($_POST['unwatch'])) ? true : false;
				
				if ($unwatch)
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

						$message = $user->lang['UNWATCHED' . $l_unwatch] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], "<a href=\"ucp.$phpEx$SID&amp;i=$id&amp;mode=subscribed\">", '</a>');

						meta_refresh(3, "ucp.$phpEx$SID&amp;i=$id&amp;mode=subscribed");
						trigger_error($message);
					}
				}

				if ($config['load_db_lastread'])
				{
					$sql_join = ' LEFT JOIN ' . FORUMS_TRACK_TABLE . ' ft ON (ft.user_id = ' . $user->data['user_id'] . ' AND ft.forum_id = f.forum_id)';
					$lastread_select = ', ft.mark_time ';
				}
				else
				{
					$sql_join = '';
					$lastread_select = '';

					$tracking_topics = (isset($_COOKIE[$config['cookie_name'] . '_track'])) ? unserialize(stripslashes($_COOKIE[$config['cookie_name'] . '_track'])) : array();
				}

				$sql = "SELECT f.*$lastread_select 
					FROM (" . FORUMS_TABLE . ' f, ' . FORUMS_WATCH_TABLE . " fw)
					$sql_join
					WHERE fw.user_id = " . $user->data['user_id'] . ' 
						AND f.forum_id = fw.forum_id 
					ORDER BY left_id';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$forum_id = $row['forum_id'];

					if ($config['load_db_lastread'])
					{
						$forum_check = (!empty($row['mark_time'])) ? $row['mark_time'] : $user->data['user_lastmark'];
					}
					else
					{
						$forum_check = (isset($tracking_topics['f'][$forum_id])) ? base_convert($tracking_topics['f'][$forum_id], 36, 10) + $config['board_startdate'] : $user->data['user_lastmark'];
					}

					$unread_forum = ($row['forum_last_post_time'] > $forum_check) ? true : false;

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
						$last_poster_url = ($row['forum_last_poster_id'] == ANONYMOUS) ? '' : "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u="  . $row['forum_last_poster_id'];

						$last_post_url = "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;f=$forum_id&amp;p=" . $row['forum_last_post_id'] . '#' . $row['forum_last_post_id'];
					}
					else
					{
						$last_post_time = $last_poster = $last_poster_url = $last_post_url = '';
					}

					$template->assign_block_vars('forumrow', array(
						'FORUM_ID'			=> $forum_id, 
						'FORUM_FOLDER_IMG'	=> $user->img($folder_image, $folder_alt),
						'FORUM_FOLDER_IMG_SRC'	=> $user->img($folder_image, $folder_alt, false, '', 'src'),
						'FORUM_NAME'		=> $row['forum_name'],
						'LAST_POST_IMG'		=> $user->img('icon_post_latest', 'VIEW_LATEST_POST'),
						'LAST_POST_TIME'	=> $last_post_time,
						'LAST_POST_AUTHOR'	=> $last_poster,
						
						'U_LAST_POST_AUTHOR'=> $last_poster_url, 
						'U_LAST_POST'		=> $last_post_url, 
						'U_VIEWFORUM'		=> "{$phpbb_root_path}viewforum.$phpEx$SID&amp;f=" . $row['forum_id'])
					);
				}
				$db->sql_freeresult($result);


				// Subscribed Topics
				$start = request_var('start', 0);
	
				$sql = 'SELECT COUNT(topic_id) as topics_count
					FROM ' . TOPICS_WATCH_TABLE . '
					WHERE user_id = ' . $user->data['user_id'];
				$result = $db->sql_query($sql);
				$topics_count = (int) $db->sql_fetchfield('topics_count', 0, $result);
				$db->sql_freeresult($result);

				if ($topics_count)
				{
					$template->assign_vars(array(
						'PAGINATION'	=> generate_pagination("ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode", $topics_count, $config['topics_per_page'], $start),
						'PAGE_NUMBER'	=> on_page($topics_count, $config['topics_per_page'], $start),
						'TOTAL_TOPICS'	=> ($topics_count == 1) ? $user->lang['VIEW_FORUM_TOPIC'] : sprintf($user->lang['VIEW_FORUM_TOPICS'], $topics_count))
					);
				}
				
				$sql_join = ($config['load_db_lastread']) ? ' LEFT JOIN ' . FORUMS_TRACK_TABLE . ' ft ON (ft.forum_id = t.forum_id AND ft.user_id = ' . $user->data['user_id'] . ')' : '';
				$sql_f_select = ($config['load_db_lastread']) ? ', ft.mark_time AS forum_mark_time' : '';
				$sql_t_select = '';

				if ($config['load_db_track'])
				{
					$sql_join .= ' LEFT JOIN ' . TOPICS_POSTED_TABLE . ' tp ON (tp.topic_id = t.topic_id 
						AND tp.user_id = ' . $user->data['user_id'] . ')';
					$sql_t_select .= ', tp.topic_posted';
				}

				if ($config['load_db_lastread'])
				{
					$sql_join .= ' LEFT JOIN ' . TOPICS_TRACK_TABLE . ' tt ON (tt.topic_id = t.topic_id
						AND tt.user_id = ' . $user->data['user_id'] . ')';
					$sql_t_select .= ', tt.mark_time';
				}


				$sql = "SELECT t.* $sql_f_select $sql_t_select 
					FROM (" . TOPICS_TABLE . ' t, ' . TOPICS_WATCH_TABLE . " tw
					$sql_join )
					WHERE tw.user_id = " . $user->data['user_id'] . '
						AND t.topic_id = tw.topic_id 
					ORDER BY t.topic_last_post_time DESC';
				$result = $db->sql_query_limit($sql, $config['topics_per_page'], $start);

				$topic_list = $global_announce_list = $rowset = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$topic_list[] = $row['topic_id'];
					$rowset[$row['topic_id']] = $row;

					if ($row['topic_type'] == POST_GLOBAL)
					{
						$global_announce_list[] = $row['topic_id'];
					}
				}
				$db->sql_freeresult($result);

				/**
				* @todo get_topic_tracking able to fetch from multiple forums
				*/
				$topic_tracking_info = get_topic_tracking(0, $topic_list, $rowset, array(0 => false), $global_announce_list);

				foreach ($topic_list as $topic_id)
				{
					$row = &$rowset[$topic_id];

					$forum_id = $row['forum_id'];
					$topic_id = $row['topic_id'];

					$unread_topic = (isset($topic_tracking_info[$topic_id]) && $row['topic_last_post_time'] > $topic_tracking_info[$topic_id]) ? true : false;

					// Replies
					$replies = ($auth->acl_get('m_approve', $forum_id)) ? $row['topic_replies_real'] : $row['topic_replies'];

					if ($row['topic_status'] == ITEM_MOVED)
					{
						$topic_id = $row['topic_moved_id'];
					}

					// Get folder img, topic status/type related informations
					$folder_img = $folder_alt = $topic_type = '';
					topic_status($row, $replies, $unread_topic, $folder_img, $folder_alt, $topic_type);
					
					$view_topic_url = "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id";
					
					// Send vars to template
					$template->assign_block_vars('topicrow', array(
						'FORUM_ID' 			=> $forum_id,
						'TOPIC_ID' 			=> $topic_id,
						'TOPIC_AUTHOR' 		=> topic_topic_author($row),
						'FIRST_POST_TIME' 	=> $user->format_date($row['topic_time']),
						'LAST_POST_TIME'	=> $user->format_date($row['topic_last_post_time']),
						'LAST_VIEW_TIME'	=> $user->format_date($row['topic_last_view_time']),
						'LAST_POST_AUTHOR' 	=> ($row['topic_last_poster_name'] != '') ? $row['topic_last_poster_name'] : $user->lang['GUEST'],
						'PAGINATION' 		=> topic_generate_pagination($replies, "viewtopic.$phpEx$SID&amp;f=" . (($row['forum_id']) ? $row['forum_id'] : $forum_id) . "&amp;t=$topic_id"),
						'REPLIES' 			=> $replies,
						'VIEWS' 			=> $row['topic_views'],
						'TOPIC_TITLE' 		=> censor_text($row['topic_title']),
						'TOPIC_TYPE' 		=> $topic_type,

						'LAST_POST_IMG' 	=> $user->img('icon_post_latest', 'VIEW_LATEST_POST'),
						'NEWEST_POST_IMG' 	=> $user->img('icon_post_newest', 'VIEW_NEWEST_POST'),
						'TOPIC_FOLDER_IMG' 	=> $user->img($folder_img, $folder_alt),
						'TOPIC_FOLDER_IMG_SRC' 	=> $user->img($folder_img, $folder_alt, false, '', 'src'),
						'TOPIC_ICON_IMG'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['img'] : '',
						'TOPIC_ICON_IMG_WIDTH'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['width'] : '',
						'TOPIC_ICON_IMG_HEIGHT'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['height'] : '',
						'ATTACH_ICON_IMG'	=> ($auth->acl_gets('f_download', 'u_download', $forum_id) && $row['topic_attachment']) ? $user->img('icon_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',

						'S_TOPIC_TYPE'			=> $row['topic_type'],
						'S_USER_POSTED'			=> (!empty($row['topic_posted'])) ? true : false,
						'S_UNREAD_TOPIC'		=> $unread_topic,

						'U_NEWEST_POST'		=> "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;view=unread#unread",
						'U_LAST_POST'		=> $view_topic_url . '&amp;p=' . $row['topic_last_post_id'] . '#' . $row['topic_last_post_id'],
						'U_LAST_POST_AUTHOR'=> ($row['topic_last_poster_id'] != ANONYMOUS && $row['topic_last_poster_id']) ? "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u={$row['topic_last_poster_id']}" : '',
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

				include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
				$user->add_lang('viewforum');

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
					$url = "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode";
					
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
						LEFT JOIN ' . TOPICS_TABLE . ' t ON (b.topic_id = t.topic_id)
						LEFT JOIN ' . FORUMS_TABLE . ' f ON (t.forum_id = f.forum_id)
					WHERE b.user_id = ' . $user->data['user_id'] . '
					ORDER BY b.order_id ASC';
				$result = $db->sql_query($sql);
				
				while ($row = $db->sql_fetchrow($result))
				{
					$forum_id = $row['forum_id'];
					$topic_id = $row['b_topic_id'];
					
					$replies = ($auth->acl_get('m_approve', $forum_id)) ? $row['topic_replies_real'] : $row['topic_replies'];
					
					// Get folder img, topic status/type related informations
					$folder_img = $folder_alt = $topic_type = '';
					$unread_topic = false; // TODO: get proper unread status
					
					topic_status($row, $replies, $unread_topic, $folder_img, $folder_alt, $topic_type);
					$view_topic_url = "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id";
					
					$template->assign_block_vars('topicrow', array(
						'FORUM_ID' 			=> $forum_id,
						'TOPIC_ID' 			=> $topic_id,
						'S_DELETED_TOPIC'	=> (!$row['topic_id']) ? true : false,
						'TOPIC_TITLE' 		=> censor_text($row['topic_title']),
						'TOPIC_TYPE' 		=> $topic_type,
						'FORUM_NAME'		=> $row['forum_name'],
						
						'TOPIC_AUTHOR' 		=> topic_topic_author($row),
						'FIRST_POST_TIME' 	=> $user->format_date($row['topic_time']),
						'LAST_POST_TIME'	=> $user->format_date($row['topic_last_post_time']),
						'LAST_VIEW_TIME'	=> $user->format_date($row['topic_last_view_time']),
						'LAST_POST_AUTHOR' 	=> ($row['topic_last_poster_name'] != '') ? $row['topic_last_poster_name'] : $user->lang['GUEST'],
						'PAGINATION' 		=> topic_generate_pagination($replies, "viewtopic.$phpEx$SID&amp;f=" . (($row['forum_id']) ? $row['forum_id'] : $forum_id) . "&amp;t=$topic_id"),

						'POSTED_AT'			=> $user->format_date($row['topic_time']),
						
						'TOPIC_FOLDER_IMG' 	=> $user->img($folder_img, $folder_alt),
						'TOPIC_FOLDER_IMG_SRC' => $user->img($folder_img, $folder_alt, false, '', 'src'),
						'ATTACH_ICON_IMG'	=> ($auth->acl_gets('f_download', 'u_download', $forum_id) && $row['topic_attachment']) ? $user->img('icon_attach', '') : '',
						'LAST_POST_IMG' 	=> $user->img('icon_post_latest', 'VIEW_LATEST_POST'),

						'U_LAST_POST'		=> $view_topic_url . '&amp;p=' . $row['topic_last_post_id'] . '#' . $row['topic_last_post_id'],
						'U_LAST_POST_AUTHOR'=> ($row['topic_last_poster_id'] != ANONYMOUS && $row['topic_last_poster_id']) ? "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u={$row['topic_last_poster_id']}" : '',
						'U_VIEW_TOPIC'		=> $view_topic_url,
						'U_VIEW_FORUM'		=> "{$phpbb_root_path}viewforum.$phpEx$SID&amp;f=$forum_id}",
						'U_MOVE_UP'			=> ($row['order_id'] != 1) ? "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=main&amp;mode=bookmarks&amp;move_up={$row['order_id']}" : '',
						'U_MOVE_DOWN'		=> ($row['order_id'] != $max_order_id) ? "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=main&amp;mode=bookmarks&amp;move_down={$row['order_id']}" : '')
					);
				}

				break;

			case 'drafts':
				
				$pm_drafts = ($this->p_master->p_name == 'pm') ? true : false;

				$user->add_lang('posting');

				$edit = (isset($_REQUEST['edit'])) ? true : false;
				$submit = (isset($_POST['submit'])) ? true : false;
				$draft_id = ($edit) ? intval($_REQUEST['edit']) : 0;
				$delete = (isset($_POST['delete'])) ? true : false;

				$s_hidden_fields = ($edit) ? '<input type="hidden" name="edit" value="' . $draft_id . '" />' : '';
				$draft_subject = $draft_message = '';

				if ($delete)
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
					$draft_subject = request_var('subject', '', true);
					$draft_message = request_var('message', '', true);

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
						$view_url = "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;f=" . $topic_rows[$draft['topic_id']]['forum_id'] . "&amp;t=" . $draft['topic_id'];
						$title = $topic_rows[$draft['topic_id']]['topic_title'];

						$insert_url = "{$phpbb_root_path}posting.$phpEx$SID&amp;f=" . $topic_rows[$draft['topic_id']]['forum_id'] . '&amp;t=' . $draft['topic_id'] . '&amp;mode=reply&amp;d=' . $draft['draft_id'];
					}
					else if ($auth->acl_get('f_read', $draft['forum_id']))
					{
						$link_forum = true;
						$view_url = "{$phpbb_root_path}viewforum.$phpEx$SID&amp;f=" . $draft['forum_id'];
						$title = $draft['forum_name'];

						$insert_url = "{$phpbb_root_path}posting.$phpEx$SID&amp;f=" . $draft['forum_id'] . '&amp;mode=post&amp;d=' . $draft['draft_id'];
					}
					else if ($pm_drafts)
					{
						$link_pm = true;
						$insert_url = "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id&amp;mode=compose&amp;d=" . $draft['draft_id'];
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
						'U_VIEW_EDIT'	=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode&amp;edit=" . $draft['draft_id'],
						'U_INSERT'		=> $insert_url,

						'S_LINK_TOPIC'		=> $link_topic,
						'S_LINK_FORUM'		=> $link_forum,
						'S_LINK_PM'			=> $link_pm,
						'S_HIDDEN_FIELDS'	=> $s_hidden_fields
					);
					$row_count++;
						
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

		// Set desired template
		$this->tpl_name = 'ucp_main_' . $mode;
	}
}

/**
* @package module_install
*/
class ucp_main_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_main',
			'title'		=> 'UCP_MAIN',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'front'			=> array('title' => 'UCP_MAIN_FRONT', 'auth' => ''),
				'subscribed'	=> array('title' => 'UCP_MAIN_SUBSCRIBED', 'auth' => ''),
				'bookmarks'		=> array('title' => 'UCP_MAIN_BOOKMARKS', 'auth' => 'cfg_allow_bookmarks'),
				'drafts'		=> array('title' => 'UCP_MAIN_DRAFTS', 'auth' => ''),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>