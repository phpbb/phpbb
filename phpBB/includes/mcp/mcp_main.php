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
	var $module_name = 'main';

	function mcp_main($module_id)
	{
		$this->module_id = $module_id;
		$this->url = $module_url;

		$this->mcp_init();

		$this->submodules = array(
			'MCP_FRONT'	=>	'&amp;i=' . $module_id . '&amp;mode=front',
			'MCP_FORUM'	=>	'&amp;i=' . $module_id . '&amp;mode=forum_view'
		);

		if ($this->topic_id)
		{
			$this->submodules['MCP_TOPIC'] = '&amp;i=' . $module_id . '&amp;mode=topic_view';
		}
		if ($this->post_id)
		{
			$this->submodules['MCP_POST'] = '&amp;i=' . $module_id . '&amp;mode=post_details';
		}
	}

	function main($mode)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx, $SID, $start;

		switch ($mode)
		{
			case 'merge_posts':
				if (!$this->to_topic_id)
				{
					redirect(str_replace('&amp;', '&', $this->url . $this->selected_ids) . '&i=' . $this->module_id . '&mode=merge_select');
				}
			break;

			case 'merge_select':
			case 'forum_view':
				$this->menu('MCP_FORUM');
				$this->mcp_jumpbox($this->url . '&amp;mode=forum_view', 'm_', $this->forum_id);
			
				if (!$this->forum_id)
				{
					$this->message_die('PLEASE_SELECT_FORUM');
				}

				if (!$auth->acl_get('m_', $this->forum_id))
				{
					trigger_error('NOT_MODERATOR');
				}

				$forum_info = $this->get_forum_data($this->forum_id, 'm_');
				$topics_per_page = ($forum_info['forum_topics_per_page']) ? $forum_info['forum_topics_per_page'] : $config['topics_per_page'];

				$this->mcp_sorting('viewforum', $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $this->forum_id);
				$forum_topics = ($total == -1) ? $forum_info['forum_topics'] : $total;
				$limit_time_sql = ($sort_days) ? 'AND t.topic_last_post_time >= ' . (time() - ($sort_days * 86400)) : '';

				$template->assign_vars(array(
					'FORUM_NAME' => $forum_info['forum_name'],

					'S_CAN_DELETE'	=>	$auth->acl_get('m_delete', $this->forum_id),
					'S_CAN_MOVE'	=>	$auth->acl_get('m_move', $this->forum_id),
					'S_CAN_FORK'	=>	$auth->acl_get('m_', $this->forum_id),
					'S_CAN_LOCK'	=>	$auth->acl_get('m_lock', $this->forum_id),
					'S_CAN_SYNC'	=>	$auth->acl_get('m_', $this->forum_id),

					'U_VIEW_FORUM'		=>	"viewforum.$phpEx$SID&amp;f=" . $this->forum_id,
					'S_MCP_ACTION'		=>	"mcp.$phpEx$SID&amp;mode=$mode&ampf=$this->forum_id&amp;start=$start" . (($mode == 'merge_select') ? $this->selected_ids : ''),

					'PAGINATION' => generate_pagination($this->url . "&amp;mode={$mode}&amp;f=" . $this->forum_id . (($mode == 'merge_select') ? $this->selected_ids : ''), $forum_topics, $topics_per_page, $start),
					'PAGE_NUMBER' => on_page($forum_topics, $config['topics_per_page'], $start)
				));


				// Define censored word matches
				$censors = array();
				obtain_word_list($censors);

				$topic_rows = array();

				$sql = 'SELECT t.*
					FROM ' . TOPICS_TABLE . " t
					WHERE t.forum_id = {$this->forum_id}
						" . (($auth->acl_gets('m_approve', $this->forum_id)) ? '' : 'AND t.topic_approved = 1') . "
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
						" . (($auth->acl_gets('m_approve', $this->forum_id)) ? '' : 'AND t.topic_approved = 1') . "
					AND t.topic_type <> " . POST_ANNOUNCE . " 
					$limit_time_sql
				ORDER BY t.topic_type DESC, $sort_order_sql";
				$result = $db->sql_query_limit($sql, $config['topics_per_page'], $start);

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

					if ($row['topic_type'] == POST_ANNOUNCE)
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

						'TOPIC_FOLDER_IMG'	=>	$folder_img,
						'TOPIC_TYPE'		=>	$topic_type,
						'TOPIC_TITLE'		=>	$topic_title,
						'REPLIES'			=>	$row['topic_replies'],
						'LAST_POST_TIME'	=>	$user->format_date($row['topic_last_post_time']),
						'TOPIC_ID'			=>	$row['topic_id'],

						'S_TOPIC_REPORTED'	=>	($row['topic_reported']) ? TRUE : FALSE,
						'S_TOPIC_UNAPPROVED'=>	($row['topic_approved']) ? FALSE : TRUE
					));
				}
				unset($topoic_rows);

				$this->display($user->lang['MCP'], 'mcp_forum.html');
			break;

			case 'merge':
			case 'split':
			case 'delete':
			case 'topic_view':
				$this->menu('MCP_TOPIC');
				$this->mcp_jumpbox($this->url . '&amp;mode=forum_view', 'm_', $this->forum_id);

				$topic_info = $this->get_topic_data($this->topic_id, 'm_');

				if (!$this->topic_id)
				{
					$this->message_die('TOPIC_NOT_EXIST');
				}
				if (!$auth->acl_get('m_', $topic_info['forum_id']))
				{
					trigger_error('NOT_MODERATOR');
				}

				$topics_per_page = ($forum_info['forum_topics_per_page']) ? $forum_info['forum_topics_per_page'] : $config['topics_per_page'];

				$this->mcp_sorting('viewtopic', $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $topic_info['forum_id'], $this->topic_id);

				$forum_topics = ($total == -1) ? $forum_info['forum_topics'] : $total;
				$limit_time_sql = ($sort_days) ? 'AND t.topic_last_post_time >= ' . (time() - ($sort_days * 86400)) : '';

				if ($total == -1)
				{
					$total = $topic_info['topic_replies'] + 1;
				}
				$posts_per_page = (isset($_REQUEST['posts_per_page'])) ? max(0, intval($_REQUEST['posts_per_page'])) : $config['posts_per_page'];

				$sql = 'SELECT u.username, u.user_colour, p.*
					FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u
					WHERE p.topic_id = {$this->topic_id}
						AND p.poster_id = u.user_id
					ORDER BY $sort_order_sql";
				$result = $db->sql_query_limit($sql, $posts_per_page, $start);

				$rowset = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$rowset[] = $row;
					$bbcode_bitfield |= $row['bbcode_bitfield'];
				}

				if ($bbcode_bitfield)
				{
					include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
					$bbcode = new bbcode($bbcode_bitfield);
				}

				foreach ($rowset as $i => $row)
				{
					$has_unapproved_posts = FALSE;
					$poster = (!empty($row['username'])) ? $row['username'] : ((!$row['post_username']) ? $user->lang['GUEST'] : $row['post_username']);
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
						'S_DISPLAY_MODES'	=>	($i % 10 == 0) ? TRUE : FALSE,
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
									'ICON_HEIGHT' 	=> $data['height']
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
					'TO_TOPIC_INFO'	=>	($this->to_topic_id) ? sprintf($user->lang['YOU_SELECTED_TOPIC'], $this->to_topic_id, '<a href="viewtopic.' . $phpEx . $SID . '&amp;f=' . $to_topic_info['forum_id'] . '&amp;t=' . $this->to_topic_id . '" target="_new">' . $to_topic_info['topic_title'] . '</a>') : '',

					'SPLIT_SUBJECT'		=>	$subject,
					'POSTS_PER_PAGE'	=>	$posts_per_page,
					'MODE'				=>	$mode,

					'REPORTED_IMG'		=> $user->img('icon_reported', 'POST_REPORTED', FALSE, TRUE),
					'UNAPPROVED_IMG'	=> $user->img('icon_unapproved', 'POST_UNAPPROVED', FALSE, TRUE),

					'S_FORM_ACTION'		=>	"mcp.$phpEx$SID&amp;mode=$mode&amp;t=" . $topic_info['topic_id'] . '&amp;start=' . $start,
					'S_FORUM_SELECT'	=>	'<select name="to_forum_id">' . make_forum_select($this->to_forum_id) . '</select>',
					'S_CAN_SPLIT'		=>	($auth->acl_get('m_split', $topic_info['forum_id'])) ? TRUE : FALSE,
					'S_CAN_MERGE'		=>	($auth->acl_get('m_merge', $topic_info['forum_id'])) ? TRUE : FALSE,
					'S_CAN_DELETE'		=>	($auth->acl_get('m_delete', $topic_info['forum_id'])) ? TRUE : FALSE,
					'S_CAN_APPROVE'		=>	($has_unapproved_posts && $auth->acl_get('m_approve', $topic_info['forum_id'])) ? TRUE : FALSE,
					'S_SHOW_TOPIC_ICONS'=>	(!empty($s_topic_icons)) ? TRUE : FALSE,

					'PAGE_NUMBER'		=>	on_page($total, $posts_per_page, $start),
					'PAGINATION'		=>	(!$posts_per_page) ? '' : generate_pagination("mcp.$phpEx$SID&amp;t=" . $topic_info['topic_id'] . "&amp;mode=$mode&amp;posts_per_page=$posts_per_page&amp;st=$sort_days&amp;sk=$sort_key&amp;sd=$sort_dir", $total, $posts_per_page, $start)
				));

				$this->display('MCP', 'mcp_topic.html');
			break;

			default:
				$this->menu('MCP_FRONT');
				// -------------
				// Latest 5 unapproved
				$forum_list = get_forum_list('m_approve');

				$template->assign_var('S_SHOW_UNAPPROVED', (!empty($forum_list)) ? TRUE : FALSE);
				if (!empty($forum_list))
				{
					$sql = 'SELECT p.post_id, p.post_subject, p.post_time, p.poster_id, p.post_username, u.username, t.topic_id, t.topic_title, t.topic_first_post_id, f.forum_id, f.forum_name
						FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . ' u
						LEFT JOIN ' . FORUMS_TABLE . ' f ON f.forum_id = p.forum_id
						WHERE p.topic_id = t.topic_id
							AND p.poster_id = u.user_id
							AND p.post_approved = 0
							AND p.forum_id IN (0,' . implode(', ', $forum_list) . ')
						ORDER BY p.post_time DESC';
					$result = $db->sql_query_limit($sql, 5);

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

					$sql = 'SELECT COUNT(post_id) AS total
						FROM ' . POSTS_TABLE . '
						WHERE post_approved = 0
							AND forum_id IN (0, ' . implode(', ', $forum_list) . ')';
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);

					if ($row['total'] == 0)
					{
						$template->assign_vars(array(
							'L_UNAPPROVED_TOTAL'		=>	$user->lang['UNAPPROVED_POSTS_ZERO_TOTAL'],
							'S_HAS_UNAPPROVED_POSTS'	=>	FALSE
						));
					}
					elseif ($row['total'] == 1)
					{
						$template->assign_vars(array(
							'L_UNAPPROVED_TOTAL'		=>	$user->lang['UNAPPROVED_POST_TOTAL'],
							'S_HAS_UNAPPROVED_POSTS'	=>	TRUE
						));
					}
					else
					{
						$template->assign_vars(array(
							'L_UNAPPROVED_TOTAL'		=>	sprintf($user->lang['UNAPPROVED_POSTS_TOTAL'], $row['total']),
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
					$sql = 'SELECT r.*, p.post_id, p.post_subject, u.username, t.topic_id, t.topic_title, f.forum_id, f.forum_name
						FROM ' . REPORTS_TABLE . ' r, ' . REASONS_TABLE . ' rr,' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . ' u
						LEFT JOIN ' . FORUMS_TABLE . ' f ON f.forum_id = p.forum_id
						WHERE r.post_id = p.post_id
							AND r.reason_id = rr.reason_id
							AND p.topic_id = t.topic_id
							AND r.user_id = u.user_id
							AND p.forum_id IN (0, ' . implode(', ', $forum_list) . ')
						ORDER BY p.post_time DESC';
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

					$sql = 'SELECT COUNT(r.report_id) AS total
						FROM ' . REPORTS_TABLE . ' r, ' . POSTS_TABLE . ' p
						WHERE r.post_id = p.post_id
							AND p.forum_id IN (0, ' . implode(', ', $forum_list) . ')';
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);

					if ($row['total'] == 0)
					{
						$template->assign_vars(array(
							'L_REPORTS_TOTAL'	=>	$user->lang['REPORTS_ZERO_TOTAL'],
							'S_HAS_REPORTS'		=>	FALSE
						));
					}
					elseif ($row['total'] == 1)
					{
						$template->assign_vars(array(
							'L_REPORTS_TOTAL'	=>	$user->lang['REPORT_TOTAL'],
							'S_HAS_REPORTS'		=>	TRUE
						));
					}
					else
					{
						$template->assign_vars(array(
							'L_REPORTS_TOTAL'	=>	sprintf($user->lang['REPORTS_TOTAL'], $row['total']),
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
				$this->display('MCP', 'mcp_front.html');
		}
	}
}
?>