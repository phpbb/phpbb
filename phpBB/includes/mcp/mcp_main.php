<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : mcp_main.php
// STARTED   : Mon Sep 02, 2003
// COPYRIGHT : © 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

class mcp_main extends mcp
{
	function init()
	{
		// Validate input
		$this->mcp_init();

		if (!$this->post_id)
		{
			unset($this->modules[$this->id]['subs']['post_details']);
		}
		if (!$this->topic_id)
		{
			unset($this->modules[$this->id]['subs']['topic_view']);
		}
	}

	function main($mode)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx, $SID;

		$this->mode = $mode;

		switch ($mode)
		{
			case 'resync':
				if (!$topic_id_list = $this->get_topic_ids('m_'))
				{
					$template->assign_var('MESSAGE', $user->lang['NO_TOPIC_SELECTED']);
					$this->main('forum_view');
				}

				// Sync everything and perform extra checks separately
				sync('topic_reported', 'topic_id', $topic_id_list, FALSE, TRUE);
				sync('topic_attachment', 'topic_id', $topic_id_list, FALSE, TRUE);
				sync('topic', 'topic_id', $topic_id_list, TRUE, FALSE);


				$sql = 'SELECT topic_id, forum_id, topic_title
					FROM ' . TOPICS_TABLE . '
					WHERE topic_id IN (' . implode(', ', $topic_id_list) . ')';
				$result = $db->sql_query($sql);

				// Log this action
				while ($row = $db->sql_fetchrow($result))
				{
					add_log('mod', $row['forum_id'], $row['topic_id'], 'LOG_TOPIC_RESYNC', $row['topic_title']);
				}

				$msg = (count($topic_id_list) == 1) ? $user->lang['TOPIC_RESYNC_SUCCESS'] : $user->lang['TOPICS_RESYNC_SUCCESS'];
				$template->assign_var('MESSAGE', $msg);

				// Back to the topics list
				$this->main('forum_view');
			break;

			case 'lock':
			case 'unlock':
				if (!$topic_id_list = $this->get_topic_ids('m_lock'))
				{
					$template->assign_var('MESSAGE', $user->lang['NO_TOPIC_SELECTED']);
					$this->main('forum_view');
				}

				if (count($topic_id_list) == 1)
				{
					$message = ($mode == 'lock') ? $user->lang['TOPIC_LOCKED_SUCCESS'] : $user->lang['TOPIC_UNLOCKED_SUCCESS'];
				}
				else
				{
					$message = ($mode == 'lock') ? $user->lang['TOPICS_LOCKED_SUCCESS'] : $user->lang['TOPICS_UNLOCKED_SUCCESS'];
				}

				$sql = 'UPDATE ' . TOPICS_TABLE . '
					SET topic_status = ' . (($mode == 'lock') ? ITEM_LOCKED : ITEM_UNLOCKED) . '
					WHERE topic_id IN (' . implode(', ', $topic_id_list) . ')';
				$db->sql_query($sql);

				$topic_data = $this->get_topic_data($topic_id_list);
				foreach ($topic_data as $topic_id => $row)
				{
					add_log('mod', $this->forum_id, $topic_id, 'LOG_' . strtoupper($mode), $row['topic_title']);
				}

				// Where are we going to be redirected?
				$return_topic = "viewtopic.$phpEx$SID&amp;f={$this->forum_id}&amp;t={$this->topic_id}&amp;start={$this->start}";
				$return_forum = "viewforum.$phpEx$SID&amp;f={$this->forum_id}";

				if ($this->quickmod)
				{
					meta_refresh(3, $return_topic);

					$message .= '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], '<a href="' . $return_topic . '">', '</a>');
					$message .= '<br \><br \>' . sprintf($user->lang['RETURN_FORUM'], '<a href="' . $return_forum . '">', '</a>');

					trigger_error($message);
				}
				else
				{
					return_link('RETURN_TOPIC', $return_topic);
					return_link('RETURN_FORUM', $return_forum);

					$template->assign_var('MESSAGE', $message);
					$this->main('forum_view');
				}
			break;

			case 'front':
				// -------------
				// Latest 5 unapproved
				$forum_list = get_forum_list('m_approve');
				$post_list = array();

				$template->assign_var('S_SHOW_UNAPPROVED', (!empty($forum_list)) ? TRUE : FALSE);
				if (!empty($forum_list))
				{
					$sql = 'SELECT COUNT(post_id) AS total
						FROM ' . POSTS_TABLE . '
						WHERE forum_id IN (' . implode(', ', $forum_list) . ')
							AND post_approved = 0';
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$total = $row['total'];

					if ($total)
					{
						// KNOWN BUG: does not work with global announcements
						$sql = 'SELECT post_id
							FROM ' . POSTS_TABLE . '
							WHERE forum_id IN (' . implode(', ', $forum_list) . ')
								AND post_approved = 0
							ORDER BY post_id DESC';
						$result = $db->sql_query_limit($sql, 5);
						while ($row = $db->sql_fetchrow($result))
						{
							$post_list[] = $row['post_id'];
						}

						$sql = 'SELECT p.post_id, p.post_subject, p.post_time, p.poster_id, p.post_username, u.username, t.topic_id, t.topic_title, t.topic_first_post_id, f.forum_id, f.forum_name
							FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f, ' . USERS_TABLE . ' u
							WHERE p.post_id IN (' . implode(', ', $post_list) . ')
								AND t.topic_id = p.topic_id
								AND f.forum_id = p.forum_id
								AND p.poster_id = u.user_id
							ORDER BY p.post_id DESC';
						$result = $db->sql_query($sql);

						while ($row = $db->sql_fetchrow($result))
						{
							if ($row['poster_id'] == ANONYMOUS)
							{
								$author = ($row['post_username']) ? $row['post_username'] : $user->lang['GUEST'];
							}
							else
							{
								$author = '<a href="memberlist.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $row['poster_id'] . '">' . $row['username'] . '</a>';
							}

							$template->assign_block_vars('unapproved', array(
								'U_POST_DETAILS'=>	$this->url . '&amp;mode=post_details',
								'FORUM'				=>	(!empty($row['forum_id'])) ? '<a href="viewforum.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'] . '">' . $row['forum_name'] . '</a>' : $user->lang['POST_GLOBAL'],
								'TOPIC'					=>	'<a href="viewtopic.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id'] . '">' . $row['topic_title'] . '</a>',
								'AUTHOR'				=>	$author,
								'SUBJECT'				=>	'<a href="mcp.' . $phpEx . $SID . '&amp;p=' . $row['post_id'] . '&amp;mode=post_details">' . (($row['post_subject']) ? $row['post_subject'] : $user->lang['NO_SUBJECT']) . '</a>',
								'POST_TIME'			=>	$user->format_date($row['post_time'])
							));				
						}
					}

					if ($total == 0)
					{
						$template->assign_vars(array(
							'L_UNAPPROVED_TOTAL'		=>	$user->lang['UNAPPROVED_POSTS_ZERO_TOTAL'],
							'S_HAS_UNAPPROVED_POSTS'	=>	FALSE
						));
					}
					elseif ($total == 1)
					{
						$template->assign_vars(array(
							'L_UNAPPROVED_TOTAL'		=>	$user->lang['UNAPPROVED_POST_TOTAL'],
							'S_HAS_UNAPPROVED_POSTS'	=>	TRUE
						));
					}
					else
					{
						$template->assign_vars(array(
							'L_UNAPPROVED_TOTAL'		=>	sprintf($user->lang['UNAPPROVED_POSTS_TOTAL'], $total),
							'S_HAS_UNAPPROVED_POSTS'	=>	TRUE
						));
					}
				}
				// -------------

				// -------------
				// Latest 5 reported
				$forum_list = get_forum_list('m_');
				
				$template->assign_var('S_SHOW_REPORTS', (!empty($forum_list)) ? TRUE : FALSE);
				if (!empty($forum_list))
				{
					$sql = 'SELECT COUNT(r.report_id) AS total
						FROM ' . REPORTS_TABLE . ' r, ' . POSTS_TABLE . ' p
						WHERE r.post_id = p.post_id
							AND p.forum_id IN (0, ' . implode(', ', $forum_list) . ')';
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$total = $row['total'];

					if ($total)
					{
						$sql = 'SELECT r.*, p.post_id, p.post_subject, u.username, t.topic_id, t.topic_title, f.forum_id, f.forum_name
							FROM ' . REPORTS_TABLE . ' r, ' . REASONS_TABLE . ' rr,' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . ' u
							LEFT JOIN ' . FORUMS_TABLE . ' f ON f.forum_id = p.forum_id
							WHERE r.post_id = p.post_id
								AND r.reason_id = rr.reason_id
								AND p.topic_id = t.topic_id
								AND r.user_id = u.user_id
								AND p.forum_id IN (0, ' . implode(', ', $forum_list) . ')
							ORDER BY p.post_id DESC';
						$result = $db->sql_query_limit($sql, 5);

						while ($row = $db->sql_fetchrow($result))
						{
							$template->assign_block_vars('report', array(
								'U_POST_DETAILS'	=>	$this->url . '&amp;mode=post_details',
								'FORUM'				=>	(!empty($row['forum_id'])) ? '<a href="viewforum.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'] . '">' . $row['forum_name'] . '</a>' : $user->lang['POST_GLOBAL'],
								'TOPIC'				=>	'<a href="viewtopic.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id'] . '">' . $row['topic_title'] . '</a>',
								'REPORTER'			=>	($row['user_id'] == ANONYMOUS) ? $user->lang['GUEST'] : '<a href="memberlist.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $row['user_id'] . '">' . $row['username'] . '</a>',
								'SUBJECT'			=>	'<a href="mcp.' . $phpEx . $SID . '&amp;p=' . $row['post_id'] . '&amp;mode=post_details">' . (($row['post_subject']) ? $row['post_subject'] : $user->lang['NO_SUBJECT']) . '</a>',
								'REPORT_TIME'		=>	$user->format_date($row['report_time'])
							));				
						}
					}

					if ($total == 0)
					{
						$template->assign_vars(array(
							'L_REPORTS_TOTAL'	=>	$user->lang['REPORTS_ZERO_TOTAL'],
							'S_HAS_REPORTS'		=>	FALSE
						));
					}
					elseif ($total == 1)
					{
						$template->assign_vars(array(
							'L_REPORTS_TOTAL'	=>	$user->lang['REPORT_TOTAL'],
							'S_HAS_REPORTS'		=>	TRUE
						));
					}
					else
					{
						$template->assign_vars(array(
							'L_REPORTS_TOTAL'	=>	sprintf($user->lang['REPORTS_TOTAL'], total),
							'S_HAS_REPORTS'		=>	TRUE
						));
					}
				}
				// -------------

				// -------------
				// Latest 5 logs
				$forum_list = get_forum_list(array('m_', 'a_general'));

				if (!empty($forum_list))
				{
					// Add forum_id 0 for global announcements
					$forum_list[] = 0;

					$log_count = 0;
					$log = array();
					view_log('mod', $log, $log_count, 5, 0, $forum_list);

					foreach ($log as $row)
					{
						$template->assign_block_vars('log', array(
							'USERNAME'		=>	$row['username'],
							'IP'			=>	$row['ip'],
							'TIME'			=>	$user->format_date($row['time']),
							'ACTION'		=>	$row['action'],
							'U_VIEWTOPIC'	=>	$row['viewtopic'],
							'U_VIEWLOGS'	=>	$row['viewlogs']
						));
					}
				}
				$template->assign_vars(array(
					'S_SHOW_LOGS'	=>	(!empty($forum_list)) ? TRUE : FALSE,
					'S_HAS_LOGS'	=>	(!empty($log)) ? TRUE : FALSE
				));
				// -------------

				$template->assign_var('S_MCP_ACTION', $this->url);
				$this->mcp_jumpbox($this->url . '&amp;mode=forum_view', 'm_', $this->forum_id);
				$this->display($user->lang['MCP'], 'mcp_front.html');
			break;

			case 'merge_select':
				// Change current mode for the menu
				$this->mode = 'forum_view';

				// Fixes a "bug" that makes forum_view use the same ordering as topic_view
				unset($_POST['sk'], $_POST['sd'], $_REQUEST['sk'], $_REQUEST['sd']);

				// No break; here

			case 'forum_view':
				if (!$forum_info = $this->get_forum_data($this->forum_id, 'm_', TRUE))
				{
					$this->main($id, 'front');
				}

				$this->mcp_jumpbox($this->url . '&amp;mode=' . $mode . (($mode == 'merge_select') ? $this->selected_ids : ''), 'm_', $this->forum_id);

				$topics_per_page = ($forum_info['forum_topics_per_page']) ? $forum_info['forum_topics_per_page'] : $config['topics_per_page'];

				$this->mcp_sorting('viewforum', $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $this->forum_id);
				$forum_topics = ($total == -1) ? $forum_info['forum_topics'] : $total;
				$limit_time_sql = ($sort_days) ? 'AND t.topic_last_post_time >= ' . (time() - ($sort_days * 86400)) : '';

				$template->assign_vars(array(
					'FORUM_NAME' => $forum_info['forum_name'],

					'REPORTED_IMG'			=> $user->img('icon_reported', 'TOPIC_REPORTED'),
					'UNAPPROVED_IMG'		=> $user->img('icon_unapproved', 'TOPIC_UNAPPROVED'),

					'S_CAN_DELETE'	=>	$auth->acl_get('m_delete', $this->forum_id),
					'S_CAN_MOVE'	=>	$auth->acl_get('m_move', $this->forum_id),
					'S_CAN_FORK'	=>	$auth->acl_get('m_', $this->forum_id),
					'S_CAN_LOCK'	=>	$auth->acl_get('m_lock', $this->forum_id),
					'S_CAN_SYNC'	=>	$auth->acl_get('m_', $this->forum_id),

					'U_VIEW_FORUM'		=>	"viewforum.$phpEx$SID&amp;f=" . $this->forum_id,
					'S_MCP_ACTION'		=>	$this->url . "&amp;mode={$mode}&amp;start={$this->start}" . (($mode == 'merge_select') ? $this->selected_ids : ''),

					'PAGINATION' => generate_pagination($this->url . "&amp;mode={$mode}&amp;f=" . $this->forum_id . (($mode == 'merge_select') ? $this->selected_ids : ''), $forum_topics, $topics_per_page, $this->start),
					'PAGE_NUMBER' => on_page($forum_topics, $config['topics_per_page'], $this->start)
				));


				// Define censored word matches
				$censors = array();
				obtain_word_list($censors);

				$topic_rows = array();

// TODO: no global announcements here
				$sql = 'SELECT t.*
					FROM ' . TOPICS_TABLE . " t
					WHERE t.forum_id = {$this->forum_id}
						" . (($auth->acl_get('m_approve', $this->forum_id)) ? '' : 'AND t.topic_approved = 1') . "
					AND t.topic_type = " . POST_ANNOUNCE . " 
					$limit_time_sql
				ORDER BY $sort_order_sql";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$topic_rows[] = $row;
				}

				$db->sql_freeresult($result);

				$sql = "SELECT t.*
					FROM " . TOPICS_TABLE . " t
					WHERE t.forum_id = {$this->forum_id}
						" . (($auth->acl_get('m_approve', $this->forum_id)) ? '' : 'AND t.topic_approved = 1') . '
					AND t.topic_type IN (' . POST_NORMAL . ', ' . POST_STICKY . ")
					$limit_time_sql
				ORDER BY t.topic_type DESC, $sort_order_sql";
				$result = $db->sql_query_limit($sql, $config['topics_per_page'], $this->start);

				while ($row = $db->sql_fetchrow($result))
				{
					$topic_rows[] = $row;
				}
				$db->sql_freeresult($result);

				foreach ($topic_rows as $row)
				{
					$topic_title = '';

					if ($auth->acl_get('m_approve', $row['forum_id']))
					{
						$row['topic_replies'] = $row['topic_replies_real'];
					}

					if ($row['topic_status'] == ITEM_LOCKED)
					{
						$folder_img = $user->img('folder_locked', 'VIEW_TOPIC_LOCKED');
					}
					else
					{
						if ($row['topic_type'] == POST_ANNOUNCE)
						{
							$folder_img = $user->img('folder_announce', 'VIEW_TOPIC_ANNOUNCEMENT');
						}
						else if ($row['topic_type'] == POST_STICKY)
						{
							$folder_img = $user->img('folder_sticky', 'VIEW_TOPIC_STICKY');
						}
						else
						{
							$folder_img = $user->img('folder', 'NO_NEW_POSTS');
						}
					}

					if ($row['topic_type'] == POST_ANNOUNCE || $row['topic_type'] == POST_GLOBAL)
					{
						$topic_type = $user->lang['VIEW_TOPIC_ANNOUNCEMENT'] . ' ';
					}
					else if ($row['topic_type'] == POST_STICKY)
					{
						$topic_type = $user->lang['VIEW_TOPIC_STICKY'] . ' ';
					}
					else if ($row['topic_status'] == ITEM_MOVED)
					{
						$topic_type = $user->lang['VIEW_TOPIC_MOVED'] . ' ';
					}
					else
					{
						$topic_type = '';
					}

					if (intval($row['poll_start']))
					{
						$topic_type .= $user->lang['VIEW_TOPIC_POLL'] . ' ';
					}

					$topic_title = $row['topic_title'];
					if (count($censors['match']))
					{
						$topic_title = preg_replace($censors['match'], $censors['replace'], $topic_title);
					}

					$template->assign_block_vars('topicrow', array(
						'U_VIEW_TOPIC'		=>	"mcp.$phpEx$SID&amp;t=" . $row['topic_id'] . '&amp;mode=topic_view',

						'S_SELECT_TOPIC'	=>	($mode == 'merge_select' && $row['topic_id'] != $this->topic_id) ? TRUE : FALSE,
						'U_SELECT_TOPIC'	=>	$this->url . '&amp;mode=merge&amp;to_topic_id=' . $row['topic_id'] . $this->selected_ids,
						'U_MCP_QUEUE'		=>	$this->url . '&amp;mode=approve&amp;t=' . $row['topic_id'],
						'U_MCP_REPORT'		=>	$this->url . '&amp;mode=reports&amp;t=' . $row['topic_id'],

						'TOPIC_FOLDER_IMG'	=>	$folder_img,
						'TOPIC_TYPE'		=>	$topic_type,
						'TOPIC_TITLE'		=>	$topic_title,
						'REPLIES'			=>	$row['topic_replies'],
						'LAST_POST_TIME'	=>	$user->format_date($row['topic_last_post_time']),
						'TOPIC_ID'			=>	$row['topic_id'],
						'S_TOPIC_CHECKED'	=>	(in_array($row['topic_id'], $this->topic_id_list)) ? 'checked="checked" ' : '',

						'S_TOPIC_REPORTED'	=>	($row['topic_reported']) ? TRUE : FALSE,
						'S_TOPIC_UNAPPROVED'=>	($row['topic_approved']) ? FALSE : TRUE
					));
				}
				unset($topic_rows);

				$this->display($user->lang['MCP'], 'mcp_forum.html');
			break;

			case 'move':
				if ($this->cancel)
				{
					$this->main('forum_view');
				}
				//
				// KNOWN BUG: won't work with global announcements
				//
				if (!$topic_id_list = $this->get_topic_ids('m_move'))
				{
					$template->assign_var('MESSAGE', $user->lang['NO_TOPIC_SELECTED']);
				}

				if (1 > $this->to_forum_id)
				{
					$this->confirm = FALSE;
				}
				elseif (!$forum_data = $this->get_forum_data($this->to_forum_id))
				{
					$template->assign_var('MESSAGE', $user->lang['FORUM_NOT_EXIST']);
					$this->confirm = FALSE;
				}
				elseif ($forum_data['forum_type'] != FORUM_POST)
				{
					$template->assign_var('MESSAGE', $user->lang['FORUM_NOT_POSTABLE']);
					$this->confirm = FALSE;
				}
				elseif (!$auth->acl_get('f_post', $this->to_forum_id))
				{
					$template->assign_var('MESSAGE', $user->lang['USER_CANNOT_POST']);
					$this->confirm = FALSE;
				}

				if (!$this->confirm)
				{
					$s_hidden_fields = '';
					foreach ($topic_id_list as $topic_id)
					{
						$s_hidden_fields .= '<input type="hidden" name="topic_id_list[]" value="' . $topic_id . '">';
					}

					$template->assign_vars(array(
						'S_MCP_ACTION'			=>	"mcp.$phpEx$SID&amp;mode=move",
						'S_HIDDEN_FIELDS'		=>	$s_hidden_fields,
						'S_FORUM_SELECT'		=>	make_forum_select(),
						'S_CAN_LEAVE_SHADOW'	=>	TRUE,

						'L_MODE_TITLE'		=>	$user->lang['MOVE'],
						'L_MODE_EXPLAIN'	=>	''
					));

					$this->display($user->lang['MCP'], 'mcp_move.html');
				}
				else
				{
					// Get topic data
					$topic_data = $this->get_topic_data($topic_id_list);

					// Check if any of topics is moved to the same forum
					foreach ($topic_data as $topic_id => $row)
					{
						if ($row['forum_id'] == $this->to_forum_id)
						{
							$template->assign_var('MESSAGE', $user->lang['CANNOT_MOVE_SAME_FORUM']);

							// Another convienient way to eliminate redundancy, go back to the move form
							$this->confirm = FALSE;
							$this->main('move');
						}
					}

					// Move topics, but do not resync yet
					move_topics($topic_id_list, $this->to_forum_id, FALSE);

					$forum_ids = array($this->to_forum_id);
					foreach ($topic_data as $topic_id => $row)
					{
						// Get the list of forums to resync, add a log entry
						$forum_ids[] = $row['forum_id'];
						add_log('mod', $this->to_forum_id, $topic_id, 'LOG_MOVE', $row['forum_name']);

						// Leave a redirection if required and only if the topic is visible to users
						if (!empty($_POST['move_leave_shadow']) && $row['topic_approved'])
						{
							$shadow = array(
								'forum_id'				=>	(int) $row['forum_id'],
								'icon_id'				=>	(int) $row['icon_id'],
								'topic_attachment'		=>	(int) $row['topic_attachment'],
								'topic_approved'		=>	1,
								'topic_reported'		=>	(int) $row['topic_reported'],
								'topic_title'			=>	(string) $row['topic_title'],
								'topic_poster'			=>	(int) $row['topic_poster'],
								'topic_time'			=>	(int) $row['topic_time'],
								'topic_time_limit'		=>	(int) $row['topic_time_limit'],
								'topic_views'			=>	(int) $row['topic_views'],
								'topic_replies'			=>	(int) $row['topic_replies'],
								'topic_replies_real'	=>	(int) $row['topic_replies_real'],
								'topic_status'			=>	ITEM_MOVED,
								'topic_type'			=>	(int) $row['topic_type'],
								'topic_first_post_id'	=>	(int) $row['topic_first_post_id'],
								'topic_first_poster_name'=>	(string) $row['topic_first_poster_name'],
								'topic_last_post_id'	=>	(int) $row['topic_last_post_id'],
								'topic_last_poster_id'	=>	(int) $row['topic_last_poster_id'],
								'topic_last_poster_name'=>	(string) $row['topic_last_poster_name'],
								'topic_last_post_time'	=>	(int) $row['topic_last_post_time'],
								'topic_last_view_time'	=>	(int) $row['topic_last_view_time'],
								'topic_moved_id'		=>	(int) $row['topic_id'],
								'poll_title'			=>	(string) $row['poll_title'],
								'poll_start'			=>	(int) $row['poll_start'],
								'poll_length'			=>	(int) $row['poll_length'],
								'poll_max_options'		=>	(int) $row['poll_max_options'],
								'poll_last_vote'		=>	(int) $row['poll_last_vote']
							);

							$db->sql_query('INSERT INTO ' . TOPICS_TABLE . $db->sql_build_array('INSERT', $shadow));
						}
					}
					unset($topic_data);

					// Now sync forums
					sync('forum', 'forum_id', $forum_ids);

					$msg = (count($topic_id_list) == 1) ? 'TOPIC_MOVED_SUCCESS' : 'TOPICS_MOVED_SUCCESS';
					$template->assign_var('MESSAGE', $user->lang[$msg]);

					// Return to the destination forum
					return_link('RETURN_FORUM', "viewforum.$phpEx$SID&amp;f={$this->forum_id}");
					return_link('RETURN_NEW_FORUM', "viewforum.$phpEx$SID&amp;f={$this->to_forum_id}");

					$this->forum_id = $this->to_forum_id;
					$this->main('forum_view');
				}
			break;

			case 'fork':
				if (!$topic_id_list = $this->get_topic_ids('m_'))
				{
					$template->assign_var('MESSAGE', $user->lang['NO_TOPIC_SELECTED']);
				}

				if (1 > $this->to_forum_id)
				{
					$this->confirm = FALSE;
				}
				elseif (!$forum_data = $this->get_forum_data($this->to_forum_id))
				{
					$template->assign_var('MESSAGE', $user->lang['FORUM_NOT_EXIST']);
					$this->confirm = FALSE;
				}
				elseif ($forum_data['forum_type'] != FORUM_POST)
				{
					$template->assign_var('MESSAGE', $user->lang['FORUM_NOT_POSTABLE']);
					$this->confirm = FALSE;
				}
				elseif (!$auth->acl_get('f_post', $this->to_forum_id))
				{
					$template->assign_var('MESSAGE', $user->lang['USER_CANNOT_POST']);
					$this->confirm = FALSE;
				}

				if (!$this->confirm)
				{
					$s_hidden_fields = '';
					foreach ($topic_id_list as $topic_id)
					{
						$s_hidden_fields .= '<input type="hidden" name="topic_id_list[]" value="' . $topic_id . '">';
					}

					$template->assign_vars(array(
						'S_MCP_ACTION'		=>	"mcp.$phpEx$SID&amp;mode=fork",
						'S_HIDDEN_FIELDS'	=>	$s_hidden_fields,
						'S_FORUM_SELECT'	=>	make_forum_select(),

						'L_MODE_TITLE'		=>	$user->lang['FORK'],
						'L_MODE_EXPLAIN'	=>	$user->lang['FORK_EXPLAIN']
					));

					$this->display($user->lang['MCP'], 'mcp_move.html');
				}
				else
				{
					$topic_data = $this->get_topic_data($topic_id_list);

					$total_posts = 0;
					$new_topic_id_list = $post_rows = array();
					foreach ($topic_data as $topic_id => $topic_row)
					{
						$sql_ary = array(
							'forum_id'					=>	(int) $this->to_forum_id,
							'icon_id'					=>	(int) $topic_row['icon_id'],
							'topic_approved'			=>	1,
							'topic_title'				=>	(string) $topic_row['topic_title'],
							'topic_poster'				=>	(int) $topic_row['topic_poster'],
							'topic_time'				=>	(int) $topic_row['topic_time'],
							'topic_replies'				=>	(int) $topic_row['topic_replies_real'],
							'topic_replies_real'		=>	(int) $topic_row['topic_replies_real'],
							'topic_status'				=>	(int) $topic_row['topic_status'],
							'topic_type'				=>	(int) $topic_row['topic_type'],
							'topic_first_poster_name'	=>	(string) $topic_row['topic_first_poster_name'],
							'topic_last_poster_id'		=>	(int) $topic_row['topic_last_poster_id'],
							'topic_last_poster_name'	=>	(string) $topic_row['topic_last_poster_name'],
							'topic_last_post_time'		=>	(int) $topic_row['topic_last_post_time'],
							'poll_title'				=>	(string) $topic_row['poll_title'],
							'poll_start'				=>	(int) $topic_row['poll_start'],
							'poll_length'				=>	(int) $topic_row['poll_length']
						);

						$db->sql_query('INSERT INTO ' . TOPICS_TABLE . $db->sql_build_array('INSERT', $sql_ary));
						$new_topic_id = $db->sql_nextid();
						$new_topic_id_list[$topic_id] = $new_topic_id;

						if ($topic_row['poll_start'])
						{
							$poll_rows = array();

							$result = $db->sql_query('SELECT * FROM ' . POLL_OPTIONS_TABLE . ' WHERE topic_id = ' . $topic_id);
							while ($row = $db->sql_fetchrow($result))
							{
								$sql = 'INSERT INTO ' . POLL_OPTIONS_TABLE . ' (poll_option_id, topic_id, poll_option_text, poll_option_total)
									VALUES (' . $row['poll_option_id'] . ', ' . $new_topic_id . ", '" . $db->sql_escape($row['poll_option_text']) . "', 0)";

								$db->sql_query($sql);
							}
						}

						$sql = 'SELECT *
							FROM ' . POSTS_TABLE . "
							WHERE topic_id = $topic_id
							ORDER BY post_id ASC";
						$result = $db->sql_query($sql);
						while ($row = $db->sql_fetchrow($result))
						{
							$post_rows[] = $row;
						}
						$db->sql_freeresult();

						if (!count($post_rows))
						{
							continue;
						}

						$total_posts += count($post_rows);
						foreach ($post_rows as $row)
						{
							$sql_ary = array(
								'topic_id'			=>	(int) $new_topic_id,
								'forum_id'			=>	(int) $this->to_forum_id,
								'poster_id'			=>	(int) $row['poster_id'],
								'icon_id'			=>	(int) $row['icon_id'],
								'poster_ip'			=>	(string) $row['poster_ip'],
								'post_time'			=>	(int) $row['post_time'],
								'post_approved'		=>	1,
								'enable_bbcode'		=>	(int) $row['enable_bbcode'],
								'enable_html'		=>	(int) $row['enable_html'],
								'enable_smilies'	=>	(int) $row['enable_smilies'],
								'enable_magic_url'	=>	(int) $row['enable_magic_url'],
								'enable_sig'		=>	(int) $row['enable_sig'],
								'post_username'		=>	(string) $row['post_username'],
								'post_subject'		=>	(string) $row['post_subject'],
								'post_text'			=>	(string) $row['post_text'],
								'post_checksum'		=>	(string) $row['post_checksum'],
								'post_encoding'		=>	(string) $row['post_encoding'],
								'bbcode_bitfield'	=>	(int) $row['bbcode_bitfield'],
								'bbcode_uid'		=>	(string) $row['bbcode_uid']
							);

							$db->sql_query('INSERT INTO ' . POSTS_TABLE . $db->sql_build_array('INSERT', $sql_ary));
						}
					}

					// Sync new topics, parent forums and board stats
					sync('topic', 'topic_id', $new_topic_id_list, TRUE);
					sync('forum', 'forum_id', $this->to_forum_id, TRUE);
					set_config('num_topics', $config['num_topics'] + count($topic_id_list));
					set_config('num_posts', $config['num_posts'] + $total_posts);

					foreach ($new_topic_id_list as $topic_id => $new_topic_id)
					{
						add_log('mod', $this->to_forum_id, $new_topic_id, 'LOG_FORK', $topic_row['forum_name']);
					}

					$msg = (count($topic_id_list) == 1) ? 'TOPIC_FORKED_SUCCESS' : 'TOPICS_FORKED_SUCCESS';
					$template->assign_var('MESSAGE', $user->lang[$msg]);

					// Return to the destination forum
					return_link('RETURN_FORUM', "viewforum.$phpEx$SID&amp;f={$this->forum_id}");

					if ($this->forum_id != $this->to_forum_id)
					{
						return_link('RETURN_NEW_FORUM', "viewforum.$phpEx$SID&amp;f={$this->to_forum_id}");
					}

					$this->forum_id = $this->to_forum_id;
					$this->main('forum_view');
				}
			break;

			case 'merge':
			case 'split':
			case 'delete':
			case 'topic_view':
				if (!$topic_info = $this->get_topic_data($this->topic_id, 'm_', TRUE))
				{
					$template->assign_var('MESSAGE', $user->lang['TOPIC_NOT_EXIST']);

					$mode = ($this->forum_id) ? 'forum_view' : 'front';
					$this->main($mode);
				}

				// Whatever mode was selected, the submode is "topic_view"
				$this->mode = 'topic_view';

				// Set up some vars
				$icon_id = request_var('icon', 0);
				$subject = request_var('subject', '');
				$topics_per_page = ($forum_info['forum_topics_per_page']) ? $forum_info['forum_topics_per_page'] : $config['topics_per_page'];

				// Jumpbox, sort selects and that kind of things
				$this->mcp_jumpbox($this->url . '&amp;mode=forum_view', 'm_', $this->forum_id);
				$this->mcp_sorting('viewtopic', $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $topic_info['forum_id'], $this->topic_id);

				$forum_topics = ($total == -1) ? $forum_info['forum_topics'] : $total;
				$limit_time_sql = ($sort_days) ? 'AND t.topic_last_post_time >= ' . (time() - ($sort_days * 86400)) : '';

				if ($total == -1)
				{
					$total = $topic_info['topic_replies'] + 1;
				}
				$posts_per_page = max(0, request_var('posts_per_page', intval($config['posts_per_page'])));

				$sql = 'SELECT u.username, u.user_colour, p.*
					FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u
					WHERE p.topic_id = {$this->topic_id}
						AND p.poster_id = u.user_id
					ORDER BY $sort_order_sql";
				$result = $db->sql_query_limit($sql, $posts_per_page, $this->start);

				$rowset = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$rowset[] = $row;
					$bbcode_bitfield |= $row['bbcode_bitfield'];
				}

				if ($bbcode_bitfield)
				{
					include_once($phpbb_root_path . 'includes/bbcode.' . $phpEx);
					$bbcode = new bbcode($bbcode_bitfield);
				}

				foreach ($rowset as $i => $row)
				{
					$has_unapproved_posts = FALSE;
					$poster = ($row['poster_id'] != ANONYMOUS) ? $row['username'] : ((!$row['post_username']) ? $user->lang['GUEST'] : $row['post_username']);
					$poster = ($row['user_colour']) ? '<span style="color:#' . $row['user_colour'] . '">' . $poster . '</span>' : $poster;

					$message = $row['post_text'];
					$post_subject = ($row['post_subject'] != '') ? $row['post_subject'] : $topic_info['topic_title'];

					// If the board has HTML off but the post has HTML
					// on then we process it, else leave it alone
					if (!$config['allow_html'] && $row['enable_html'])
					{
						$message = preg_replace('#(<)([\/]?.*?)(>)#is', '&lt;\\2&gt;', $message);
					}

					if ($row['bbcode_bitfield'])
					{
						$bbcode->bbcode_second_pass($message, $row['bbcode_uid'], $row['bbcode_bitfield']);
					}

					$message = (empty($config['allow_smilies']) || !$user->data['user_viewsmilies']) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $message) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $config['smilies_path'], $message);

					$message = nl2br($message);

					$checked = (in_array(intval($row['post_id']), $this->post_id_list)) ? 'checked="checked" ' : '';
					$s_checkbox = ($row['post_id'] == $topic_info['topic_first_post_id'] && $mode == 'split') ? '&nbsp;' : '<input type="checkbox" name="post_id_list[]" value="' . $row['post_id'] . '" ' . $checked . '/>';

					if (!$row['post_approved'])
					{
						$has_unapproved_posts = TRUE;
					}

					$template->assign_block_vars('postrow', array(
						'POSTER_NAME'	=>	$poster,
						'POST_DATE'		=>	$user->format_date($row['post_time']),
						'POST_SUBJECT'	=>	$post_subject,
						'MESSAGE'		=>	$message,
						'POST_ID'		=>	$row['post_id'],

						'POST_ICON_IMG' =>	($row['post_time'] > $user->data['user_lastvisit'] && $user->data['user_id'] != ANONYMOUS) ? $user->img('icon_post_new', $user->lang['NEW_POST']) : $user->img('icon_post', $user->lang['POST']),

						'S_CHECKBOX'		=>	$s_checkbox,
						'S_ROW_COUNT'		=>	$i,
						'S_POST_REPORTED'	=>	($row['post_reported']) ? TRUE : FALSE,
						'S_POST_UNAPPROVED'	=>	($row['post_approved']) ? FALSE : TRUE,
						
						'U_POST_DETAILS'	=>	"mcp.$phpEx$SID&amp;f=" . $row['forum_id'] . '&amp;t=' . $row['topic_id'] . '&amp;p=' . $row['post_id'] . '&amp;mode=post_details',
						'U_APPROVE'			=>	"mcp.$phpEx$SID&amp;mode=approve&amp;p=" . $row['post_id']
					));

					unset($rowset[$i]);
				}

				// Display topic icons for split topic
				if ($auth->acl_get('m_split', $topic_info['forum_id']))
				{
					$icons = array();
					obtain_icons($icons);

					if (count($icons))
					{
						$s_topic_icons = true;

						foreach ($icons as $id => $data)
						{
							if ($data['display'])
							{
								$template->assign_block_vars('topic_icon', array(
									'ICON_ID'		=> $id,
									'ICON_IMG'		=> $config['icons_path'] . '/' . $data['img'],
									'ICON_WIDTH'	=> $data['width'],
									'ICON_HEIGHT' 	=> $data['height'],

									'S_CHECKED'	=> ($id == $icon_id) ? TRUE : FALSE
								));
							}
						}
					}
				}

				// Has the user selected a topic for merge?
				if ($this->to_topic_id)
				{
					if (!$to_topic_info = $this->get_topic_data($this->to_topic_id, 'm_merge', TRUE))
					{
						$this->to_topic_id = 0;
					}
				}

				$template->assign_vars(array(
					'TOPIC_TITLE'		=>	$topic_info['topic_title'],
					'U_VIEW_TOPIC'		=>	"viewtopic.$phpEx$SID&amp;f=" . $topic_info['forum_id'] . '&amp;t=' . $topic_info['topic_id'],

					'TO_TOPIC_ID'		=>	($this->to_topic_id) ? $this->to_topic_id : '',
					'TO_TOPIC_INFO'		=>	($this->to_topic_id) ? sprintf($user->lang['YOU_SELECTED_TOPIC'], $this->to_topic_id, '<a href="viewtopic.' . $phpEx . $SID . '&amp;f=' . $to_topic_info['forum_id'] . '&amp;t=' . $this->to_topic_id . '" target="_new">' . $to_topic_info['topic_title'] . '</a>') : '',

					'SPLIT_SUBJECT'		=>	$subject,
					'POSTS_PER_PAGE'	=>	$posts_per_page,
					'MODE'				=>	$mode,

					'REPORTED_IMG'		=> $user->img('icon_reported', 'POST_REPORTED', FALSE, TRUE),
					'UNAPPROVED_IMG'	=> $user->img('icon_unapproved', 'POST_UNAPPROVED', FALSE, TRUE),

					'S_MCP_ACTION'		=>	"mcp.$phpEx$SID&amp;mode=$mode&amp;t=" . $topic_info['topic_id'] . '&amp;start=' . $this->start,
					'S_FORUM_SELECT'	=>	'<select name="to_forum_id">' . (($this->to_forum_id) ? make_forum_select($this->to_forum_id) : make_forum_select($this->forum_id)) . '</select>',
					'S_CAN_SPLIT'		=>	($auth->acl_get('m_split', $topic_info['forum_id'])) ? TRUE : FALSE,
					'S_CAN_MERGE'		=>	($auth->acl_get('m_merge', $topic_info['forum_id'])) ? TRUE : FALSE,
					'S_CAN_DELETE'		=>	($auth->acl_get('m_delete', $topic_info['forum_id'])) ? TRUE : FALSE,
					'S_CAN_APPROVE'		=>	($has_unapproved_posts && $auth->acl_get('m_approve', $topic_info['forum_id'])) ? TRUE : FALSE,

					'S_SHOW_TOPIC_ICONS'=>	(!empty($s_topic_icons)) ? TRUE : FALSE,
					'S_TOPIC_ICON'		=>	$icon_id,

					'PAGE_NUMBER'		=>	on_page($total, $posts_per_page, $this->start),
					'PAGINATION'		=>	(!$posts_per_page) ? '' : generate_pagination("mcp.$phpEx$SID&amp;t=" . $topic_info['topic_id'] . "&amp;mode=$mode&amp;posts_per_page=$posts_per_page&amp;st=$sort_days&amp;sk=$sort_key&amp;sd=$sort_dir", $total, $posts_per_page, $this->start)
				));

				$this->display($user->lang['MCP'], 'mcp_topic.html');
			break;

			case 'merge_posts':
				if (!$this->to_topic_id || !$topic_data = $this->get_topic_data($this->to_topic_id, 'm_merge'))
				{
					// No destination topic? Make the user select one
					$this->main('merge_select');
//					redirect(str_replace('&amp;', '&', $this->url . $this->selected_ids) . '&i=' . $this->id . '&mode=merge_select');
				}

				if (!$post_id_list = $this->get_post_ids('m_merge'))
				{
					$template->assign_var('MESSAGE', $user->lang['NO_POST_SELECTED']);
					$this->main('merge');
				}

				move_posts($post_id_list, $this->to_topic_id);
				add_log('mod', $this->to_forum_id, $this->to_topic_id, 'LOG_MERGE', $topic_data['topic_title']);
				
				// Message and return links
				$template->assign_var('MESSAGE', $user->lang['POSTS_MERGED_SUCCESS']);

				// Does the original topic still exist? If yes, link back to it
				if ($topic_data = $this->get_topic_data($this->topic_id))
				{
					return_link('RETURN_TOPIC', "viewtopic.$phpEx$SID&amp;f={$this->forum_id}&amp;t={$this->topic_id}");
				}

				// Link to the new topic
				return_link('RETURN_NEW_TOPIC', "viewtopic.$phpEx$SID&amp;f={$this->to_forum_id}&amp;t={$this->to_topic_id}");

				// Back to the topics list
				$this->main('forum_view');
			break;

			case 'delete_topic':
				if ($this->cancel)
				{
					if (!$this->topic_id && $this->topic_id_list)
					{
						$this->topic_id = $this->topic_id_list[0];
					}
					if ($this->quickmod)
					{
						redirect("viewtopic.$phpEx$SID?f={$this->forum_id}&t={$this->topic_id}");
					}
					else
					{
						$this->main('forum_view');
					}
				}
				if (!$topic_id_list = $this->get_topic_ids('m_delete'))
				{
					$template->assign_var('MESSAGE', $user->lang['NO_TOPIC_SELECTED']);
					$this->main('forum_view');
				}

				if ($this->confirm)
				{
					return_link('RETURN_FORUM', "viewforum.$phpEx$SID&amp;f={$this->forum_id}");

					$template->assign_var('MESSAGE', (count($topic_id_list) == 1) ? $user->lang['TOPIC_DELETED_SUCCESS'] : $user->lang['TOPICS_DELETED_SUCCESS']);
					$this->main('forum_view');
				}
				else
				{
					$s_hidden_fields = '';
					foreach ($topic_id_list as $topic_id)
					{
						$s_hidden_fields .= '<input type="hidden" name="topic_id_list[]" value="' . $topic_id . '">';
					}

					$template->assign_vars(array(
						'S_CONFIRM_ACTION'	=>	$this->url . '&amp;mode=' . $mode . '&amp;quickmod=' . intval($this->quickmod),
						'S_HIDDEN_FIELDS'	=>	$s_hidden_fields,

						'CONFIRM_MODE'		=>	$mode,
						'CONFIRM_MESSAGE'	=>	(count($topic_id_list) == 1) ? $user->lang['DELETE_TOPIC_CONFIRM'] : $user->lang['DELETE_TOPICS_CONFIRM']
					));

					$this->main('forum_view');
				}
			break;

			case 'delete_post':
				if ($this->cancel)
				{
					$this->main('forum_view');
				}
				if (!$post_id_list = $this->get_post_ids('m_delete'))
				{
					$template->assign_var('MESSAGE', $user->lang['NO_POST_SELECTED']);
					$this->main('topic_view');
				}

				if ($this->confirm)
				{
					// Count the number of topics that are affected
					// I did not use COUNT(DISTINCT ...) because I remember having problems
					// with it on older versions of MySQL -- Ashe

					$sql = 'SELECT DISTINCT topic_id
						FROM ' . POSTS_TABLE . '
						WHERE post_id IN (' . implode(', ', $post_id_list) . ')';

					$result = $db->sql_query_limit($sql);

					$topic_id_list = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$topic_id_list[] = $row['topic_id'];
					}
					$affected_topics = count($topic_id_list);
					$db->sql_freeresult($result);

					// Now delete the posts, topics and forums are automatically resync'ed
					delete_posts('post_id', $post_id_list);
					
					$sql = 'SELECT COUNT(topic_id) AS topics_left
						FROM ' . TOPICS_TABLE . '
						WHERE topic_id IN (' . implode(', ', $topic_id_list) . ')';
					$result = $db->sql_query_limit($sql);

					$deleted_topics = ($row = $db->sql_fetchrow($result)) ? ($affected_topics - $row['topics_left']) : $affected_topics;
					$db->sql_freeresult($result);

					// Return links
					if ($affected_topics == 1 && !$deleted_topics)
					{
						return_link('RETURN_TOPIC', "viewtopic.$phpEx$SID&amp;f={$this->forum_id}&amp;t={$this->topic_id}");
					}
					return_link('RETURN_FORUM', "viewforum.$phpEx$SID&amp;f={$this->forum_id}");

					if (count($post_id_list) == 1)
					{
						if ($deleted_topics)
						{
							// We deleted the only post of a topic, which in turn has
							// been removed from the database
							$template->assign_var('MESSAGE', $user->lang['TOPIC_DELETED_SUCCESS']);
						}
						else
						{
							$template->assign_var('MESSAGE', $user->lang['POST_DELETED_SUCCESS']);
						}
					}
					else
					{
						if ($deleted_topics)
						{
							// Some of topics disappeared
							$template->assign_var('MESSAGE', $user->lang['POSTS_DELETED_SUCCESS'] . '<br /><br />' . $user->lang['EMPTY_TOPICS_REMOVED_WARNING']);
						}
						else
						{
							$template->assign_var('MESSAGE', $user->lang['POSTS_DELETED_SUCCESS']);
						}
					}

					// Back to the appropriate mode
					$mode = ($affected_topics == 1 && !$deleted_topics) ? 'topic_view' : 'forum_view';
					$this->main($mode);
				}
				else
				{
					$s_hidden_fields = '';
					foreach ($post_id_list as $post_id)
					{
						$s_hidden_fields .= '<input type="hidden" name="post_id_list[]" value="' . $post_id . '">';
					}

					$template->assign_vars(array(
						'S_CONFIRM_ACTION'	=>	$this->url . '&amp;mode=' . $mode,
						'S_HIDDEN_FIELDS'	=>	$s_hidden_fields,

						'CONFIRM_MODE'		=>	$mode,
						'CONFIRM_MESSAGE'	=>	(count($post_id_list) == 1) ? $user->lang['DELETE_POST_CONFIRM'] : $user->lang['DELETE_POSTS_CONFIRM']
					));

					$this->main('topic_view');
				}
			break;

			case 'split_all':
			case 'split_beyond':
				if (!$post_id_list = $this->get_post_ids('m_split'))
				{
					// No posts? back to the topic
					$template->assign_var('MESSAGE', $user->lang['NO_POST_SELECTED']);
					$this->main('split');
				}

				$post_id = $post_id_list[0];
				if (!$post_info = $this->get_post_data($post_id))
				{
					// No posts? back to the topic
					$template->assign_var('MESSAGE', $user->lang['NO_POST_SELECTED']);
					$this->main('split');
				}

				if (!$subject = request_var('subject', ''))
				{
					$template->assign_var('MESSAGE', $user->lang['EMPTY_SUBJECT']);
					$this->main('split');
				}
				if ($this->to_forum_id <= 0)
				{
					$template->assign_var('MESSAGE', $user->lang['NO_DESTINATION_FORUM']);
					$this->main('split');
				}
				if (!$forum_info = $this->get_forum_data($this->to_forum_id, 'm_split', TRUE))
				{
					$template->assign_var('MESSAGE', $user->lang['NOT_MODERATOR']);
					$this->main('split');
				}
				if ($forum_info['forum_type'] != FORUM_POST)
				{
					$template->assign_var('MESSAGE', $user->lang['FORUM_NOT_POSTABLE']);
					$this->main('split');
				}

				if ($mode == 'split_beyond')
				{
					$this->mcp_sorting('viewtopic', $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $this->forum_id, $this->topic_id);
					$limit_time_sql = ($sort_days) ? 'AND t.topic_last_post_time >= ' . (time() - ($sort_days * 86400)) : '';

					if ($sort_order_sql{0} == 'u')
					{
						$sql = 'SELECT p.post_id
							FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u
							WHERE p.topic_id = {$this->topic_id}
								AND p.poster_id = u.user_id
								$limit_time_sql
							ORDER BY $sort_order_sql";
					}
					else
					{
						$sql = 'SELECT p.post_id
							FROM ' . POSTS_TABLE . " p
							WHERE p.topic_id = {$this->topic_id}
								$limit_time_sql
							ORDER BY $sort_order_sql";
					}
					$result = $db->sql_query_limit($sql, 0, $this->start);

					$store = FALSE;
					$post_id_list = array();
					while ($row = $db->sql_fetchrow($result))
					{
						// TODO: see unapproved items? perform any action?
						if (!$row['post_approved'] && !$auth->acl_get('m_approve', $row['forum_id']))
						{
//							continue;
						}

						// Start to store post_ids as soon as we see the first post that was selected
						if ($row['post_id'] == $post_id)
						{
							$store = TRUE;
						}
						if ($store)
						{
							$post_id_list[] = $row['post_id'];
						}
					}
				}

				if (!count($post_id_list))
				{
					$template->assign_var('MESSAGE', $user->lang['NO_POST_SELECTED']);
					$this->main('split');
				}

				$icon_id = request_var('icon', 0);
				$sql = 'INSERT INTO ' . TOPICS_TABLE . " (forum_id, topic_title, icon_id, topic_approved)
					VALUES ({$this->to_forum_id}, '" . $db->sql_escape($subject) . "', $icon_id, 1)";
				$db->sql_query($sql);

				$to_topic_id = $db->sql_nextid();
				move_posts($post_id_list, $to_topic_id);

				// Link back to both topics
				return_link('RETURN_TOPIC', "viewtopic.$phpEx$SID&amp;f=" . $post_info['forum_id'] . '&amp;t=' . $post_info['topic_id']);
				return_link('RETURN_NEW_TOPIC', "viewtopic.$phpEx$SID&amp;f={$this->to_forum_id}&amp;t=$to_topic_id");

				$template->assign_var('MESSAGE', $user->lang['TOPIC_SPLIT_SUCCESS']);
				$this->main('forum_view');
			break;

			case 'modoptions':
				if ($this->forum_id && !$auth->acl_get('m_', $this->forum_id))
				{
					trigger_error('NOT_MODERATOR');
				}

				switch ($this->action)
				{
					case 'unlock_post':
					case 'lock_post':
						$sql = 'UPDATE ' . POSTS_TABLE . '
							SET post_edit_locked = ' . (($this->action == 'lock_post') ? '1' : '0') . '
							WHERE post_id = ' . $this->post_id;
						$db->sql_query($sql);

						$msg = ($this->action == 'lock_post') ? $user->lang['POST_LOCKED_SUCCESS'] : $user->lang['POST_UNLOCKED_SUCCESS'];
						$template->assign_var('MESSAGE', $msg);

						add_log('mod', $post_info['forum_id'], $post_info['topic_id'], 'LOG_' . strtoupper($this->action), $post_info['post_subject']);
						$this->main('post_details');
					break;

					case 'chgposter':
						$post_info = $this->get_post_data($this->post_id);
						$user_id = request_var('u', 0);

						if (!empty($post_info) && $user_id)
						{
							$sql = 'UPDATE ' . POSTS_TABLE . "
								SET poster_id = $user_id
								WHERE post_id = {$this->post_id}";
							$db->sql_query($sql);

							if ($post_info['topic_last_post_id'] == $post_info['post_id'] || $post_info['forum_last_post_id'] == $post_info['post_id'])
							{
								sync('topic', 'topic_id', $post_info['topic_id'], FALSE, FALSE);
								sync('forum', 'forum_id', $post_info['forum_id'], FALSE, FALSE);
							}
						}

						$this->main('post_details');
					break;

					case 'chgposter_search':
						$post_info = $this->get_post_data($this->post_id);
						$username = request_var('username', '');

						if (!empty($post_info) && $username)
						{
							$users_ary = array();

							if (strpos($username, '*') === FALSE)
							{
								$username = "*$username*";
							}
							$username = str_replace('*', '%', str_replace('%', '\%', $username));

							$sql = 'SELECT user_id, username
								FROM ' . USERS_TABLE . "
								WHERE username LIKE '" . $db->sql_escape($username) . "'
									AND user_type = " . USER_NORMAL . '
									AND user_id <> ' . $post_info['user_id'];
							$result = $db->sql_query($sql);

							while ($row = $db->sql_fetchrow($result))
							{
								$users_ary[strtolower($row['username'])] = $row;
							}

							$user_select = '';
							ksort($users_ary);
							foreach ($users_ary as $row)
							{
								$user_select .= '<option value="' . $row['user_id'] . '">' . $row['username'] . "</option>\n";
							}
						}

						if (!$user_select)
						{
							$template->assign_var('MESSAGE', $user->lang['NO_MATCHES_FOUND']);
						}

						$template->assign_vars(array(
							'S_USER_SELECT'		=>	$user_select,
							'SEARCH_USERNAME'	=>	request_var('username', '')
						));

						$this->main('post_details');
					break;

					case 'unrate':
						$post_info = $this->get_post_data($this->post_id, 'm_unrate');

						if (!empty($post_info))
						{
							$sql = 'DELETE FROM ' . RATINGS_TABLE . '
								WHERE post_id = ' . $this->post_id;
							$db->sql_query($sql);

							add_log('mod', $post_info['forum_id'], $post_info['topic_id'], 'LOG_UNRATE', $post_info['post_subject']);

							$template->assign_var('MESSAGE', $user->lang['POST_UNRATED_SUCCESS']);

							// TODO: recompute user's rating or something?
						}
						else
						{
							$template->assign_var('MESSAGE', $user->lang['NOT_MODERATOR']);
						}

						$this->main('post_details');
				}

				trigger_error("What the hell is happening here? (action: '$this->action')");
			break;

			case 'post_details':

				// Get post data
				if (!$post_info = $this->get_post_data($this->post_id))
				{
					trigger_error($user->lang['POST_NOT_EXIST']);
				}

				// Set some vars
				$users_ary = array();
				$poster = ($post_info['user_colour']) ? '<span style="color:#' . $post_info['user_colour'] . '">' . $post_info['username'] . '</span>' : $post_info['username'];

				// Process message, leave it uncensored
				$message = $post_info['post_text'];
				if ($post_info['bbcode_bitfield'])
				{
					include($phpbb_root_path . 'includes/bbcode.'.$phpEx);
					$bbcode = new bbcode($post_info['bbcode_bitfield']);
					$bbcode->bbcode_second_pass($message, $post_info['bbcode_uid'], $post_info['bbcode_bitfield']);
				}
				$message = (empty($config['allow_smilies']) || !$user->optionget('viewsmilies')) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $message) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $config['smilies_path'], $message);

				$template->assign_vars(array(
					'S_MCP_ACTION'			=>	$this->url . '&amp;mode=modoptions',
					'S_CHGPOSTER_ACTION'	=>	$this->url . '&amp;mode=modoptions',
					'S_APPROVE_ACTION'		=>	$this->url . '&amp;i=queue&amp;mode=approve&amp;quickmod=' . intval($this->quickmod),

					'S_CAN_VIEWIP'		=>	$auth->acl_get('m_ip', $post_info['forum_id']),
					'S_CAN_CHGPOSTER'	=>	$auth->acl_get('m_', $post_info['forum_id']),
					'S_CAN_LOCK_POST'	=>	$auth->acl_get('m_', $post_info['forum_id']),
					'S_CAN_UNRATE'		=>	$auth->acl_get('m_unrate', $post_info['forum_id']),

					'S_POST_REPORTED'	=>	$post_info['post_reported'],
					'S_POST_UNAPPROVED'	=>	!$post_info['post_approved'],
					'S_POST_LOCKED'		=>	$post_info['post_edit_locked'],
					'S_USER_NOTES'		=>	($post_info['user_notes']) ? TRUE : FALSE,
					'S_USER_WARNINGS'	=>	($post_info['user_warnings']) ? TRUE : FALSE,
					'S_QUICKMOD'		=>	$this->quickmod,

					'U_PROFILE'			=>	"memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['user_id'],
					'U_MCP_USERNOTES'	=>	$this->url . '&amp;i=notes&amp;mode=user_notes',
					'U_MCP_WARNINGS'	=>	$this->url . '&amp;i=warnings&amp;mode=view_user&u=' . $post_info['user_id'],

					'RETURN_TOPIC'	=>	sprintf($user->lang['RETURN_TOPIC'], "<a href=\"viewtopic.$phpEx$SID&amp;p={$this->post_id}#{$this->post_id}\">", '</a>'),
					'RETURN_FORUM'	=>	sprintf($user->lang['RETURN_FORUM'], "<a href=\"viewforum.$phpEx$SID&amp;f={$this->forum_id}&amp;start={$this->start}\">", '</a>'),
					'REPORTED_IMG'		=> $user->img('icon_reported', $user->lang['POST_REPORTED']),
					'UNAPPROVED_IMG'	=> $user->img('icon_unapproved', $user->lang['POST_UNAPPROVED']),

					'POSTER_NAME'	=>	$poster,
					'POST_PREVIEW'	=>	$message,
					'POST_SUBJECT'	=>	$post_info['post_subject'],
					'POST_DATE'		=>	$user->format_date($post_info['post_time']),
					'POST_IP'		=>	$post_info['poster_ip'] . ' (' . @gethostbyaddr($post_info['poster_ip']) . ')'
				));

				// --------
				// IP tools
				if ($auth->acl_get('m_ip', $post_info['forum_id']))
				{
					$rdns_ip_num = request_var('rdns', '');

					if ($rdns_ip_num != 'all')
					{
						$template->assign_vars(array(
							'U_LOOKUP_ALL'	=>	$this->url . '&i=main&mode=post_details&rdns=all'
						));
					}

					// Get other users who've posted under this IP
					$sql = "SELECT u.user_id, u.username, COUNT(*) as postings
						FROM " . USERS_TABLE ." u, " . POSTS_TABLE . " p
						WHERE p.poster_id = u.user_id
							AND p.poster_ip = '" . $post_info['poster_ip'] . "'
							AND p.poster_id <> " . $post_info['user_id'] . '
						GROUP BY u.user_id
						ORDER BY postings DESC';
					$result = $db->sql_query($sql);

					$i = 0;
					while ($row = $db->sql_fetchrow($result))
					{
						// Fill the user select list with users who have posted
						// under this IP
						if ($row['user_id'] != $post_info['poster_id'])
						{
							$users_ary[strtolower($row['username'])] = $row;
						}

						$template->assign_block_vars('userrow', array(
							'S_ROW_COUNT'	=>	$i++,

							'USERNAME'		=>	($row['user_id'] == ANONYMOUS) ? $user->lang['GUEST'] : $row['username'],
							'POSTS'			=>	$row['postings'] . ' ' . (($row['postings'] == 1) ? $user->lang['POST'] : $user->lang['POSTS']),

							'U_PROFILE'		=> ($row['user_id'] == ANONYMOUS) ? '' : "memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['user_id'],
							'U_SEARCHPOSTS' => "search.$phpEx$SID&amp;search_author=" . urlencode($row['username']) . "&amp;showresults=topics"
						));
					}
					$db->sql_freeresult($result);

					// Get other IP's this user has posted under
					$sql = 'SELECT poster_ip, COUNT(*) AS postings
						FROM ' . POSTS_TABLE . '
						WHERE poster_id = ' . $post_info['poster_id'] . '
						GROUP BY poster_ip
						ORDER BY postings DESC';
					$result = $db->sql_query($sql);

					$i = 0;
					while ($row = $db->sql_fetchrow($result))
					{
						$hostname = ($rdns_ip_num == $row['poster_ip'] || $rdns_ip_num == 'all') ? @gethostbyaddr($row['poster_ip']) : '';

						$template->assign_block_vars('iprow', array(
							'S_ROW_COUNT'	=>	$i++,
							'IP'			=>	$row['poster_ip'],
							'HOSTNAME'		=>	$hostname,
							'POSTS'			=>	$row['postings'] . ' ' . (($row['postings'] == 1) ? $user->lang['POST'] : $user->lang['POSTS']),

							'U_LOOKUP_IP'	=>	($rdns_ip_num == $row['poster_ip'] || $rdns_ip_num == 'all') ? '' : $this->url . '&amp;mode=post_details&amp;rdns=' . $row['poster_ip'] . '#ip',
							'U_WHOIS'		=>	$this->url . '&amp;i=iptool&amp;mode=whois&amp;ip=' . $row['poster_ip']
						));
					}
					$db->sql_freeresult($result);

					// If we were not searching for a specific username fill
					// the user_select box with users who have posted under
					// the same IP
					if ($this->action != 'chgposter_search')
					{
						$user_select = '';
						ksort($users_ary);
						foreach ($users_ary as $row)
						{
							$user_select .= '<option value="' . $row['user_id'] . '">' . $row['username'] . "</option>\n";
						}
						$template->assign_var('S_USER_SELECT', $user_select);
					}
				}
				// --------

				$this->display($user->lang['MCP'], 'mcp_post.html');
			break;

			default:
				trigger_error("Unkwnown mode: $mode");
		}
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
			'name'			=> 'MCP - Main',
			'description'	=> 'Front end for Moderator Control Panel', 
			'filename'		=> 'main',
			'version'		=> '0.1.0', 
			'phpbbversion'	=> '2.2.0'
		);
		return $details;
	}
}
?>