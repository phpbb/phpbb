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

class mcp_main extends module
{

	function mcp_main($id, $mode, $url)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx, $SID;
		
		$action = request_var('action', '');
		$quickmod = request_var('quickmod', '');

		if (is_array($action))
		{
			list($action, ) = each($action);
		}

		switch ($mode)
		{
			case 'lock':
			case 'unlock':
				$topic_ids = get_array((!$quickmod) ? 'topic_id_list' : 't', 0);
		
				if (!$topic_ids)
				{
					trigger_error('NO_TOPIC_SELECTED');
				}

				lock_unlock($mode, $topic_ids);
				break;

			case 'lock_post':
			case 'unlock_post':

				$post_ids = get_array((!$quickmod) ? 'post_id_list' : 'p', 0);
		
				if (!$post_ids)
				{
					trigger_error('NO_POST_SELECTED');
				}

				lock_unlock($mode, $post_ids);
				break;

			case 'make_announce':
			case 'make_sticky':
			case 'make_global':
			case 'make_normal':
				
				$topic_ids = get_array((!$quickmod) ? 'topic_id_list' : 't', 0);
		
				if (!$topic_ids)
				{
					trigger_error('NO_TOPIC_SELECTED');
				}

				change_topic_type($mode, $topic_ids);
			
				break;

			case 'move':
				$user->add_lang('viewtopic');

				$topic_ids = get_array((!$quickmod) ? 'topic_id_list' : 't', 0);
		
				if (!$topic_ids)
				{
					trigger_error('NO_TOPIC_SELECTED');
				}

				mcp_move_topic($topic_ids);
			
				break;

			case 'fork':
				$user->add_lang('viewtopic');

				$topic_ids = get_array((!$quickmod) ? 'topic_id_list' : 't', 0);
		
				if (!$topic_ids)
				{
					trigger_error('NO_TOPIC_SELECTED');
				}

				mcp_fork_topic($topic_ids);
			
				break;

			case 'delete_topic':
				$user->add_lang('viewtopic');

				$topic_ids = get_array((!$quickmod) ? 'topic_id_list' : 't', 0);
		
				if (!$topic_ids)
				{
					trigger_error('NO_TOPIC_SELECTED');
				}

				mcp_delete_topic($topic_ids);
				break;

			case 'delete_post':
				$user->add_lang('posting');

				$post_ids = get_array((!$quickmod) ? 'post_id_list' : 'p', 0);
		
				if (!$post_ids)
				{
					trigger_error('NO_POST_SELECTED');
				}

				mcp_delete_post($post_ids);
				break;

			case 'front':
				include($phpbb_root_path . 'includes/mcp/mcp_front.' . $phpEx);

				mcp_front_view($id, $mode, $action, $url);

				$this->display($user->lang['MCP'], 'mcp_front.html');
				break;

			case 'forum_view':
				include($phpbb_root_path . 'includes/mcp/mcp_forum.' . $phpEx);
				
				$user->add_lang('viewforum');

				$forum_id = request_var('f', 0);

				$forum_info = get_forum_data($forum_id, 'm_');

				if (!sizeof($forum_info))
				{
					$this->mcp_main('mcp', 'front', $url);
					exit;
				}

				$forum_info = $forum_info[$forum_id];

				mcp_forum_view($id, $mode, $action, $url, $forum_info);
				
				$this->display($user->lang['MCP'], 'mcp_forum.html');
				break;

			case 'topic_view':
				include($phpbb_root_path . 'includes/mcp/mcp_topic.' . $phpEx);
				
				mcp_topic_view($id, $mode, $action, $url);
				
				$this->display($user->lang['MCP'], 'mcp_topic.html');
				break;
				
			case 'post_details':
				include($phpbb_root_path . 'includes/mcp/mcp_post.' . $phpEx);
				
				mcp_post_details($id, $mode, $action, $url);
				
				$this->display($user->lang['MCP'], 'mcp_post.html');
				break;			

			default:
				trigger_error("Unknown mode: $mode");
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




// Lock/Unlock Topic/Post
function lock_unlock($mode, $ids)
{
	global $auth, $user, $db, $SID, $phpEx, $phpbb_root_path;

	if ($mode == 'lock' || $mode == 'unlock')
	{
		$table = TOPICS_TABLE;
		$sql_id = 'topic_id';
		$set_id = 'topic_status';
		$l_prefix = 'TOPIC';
	}
	else
	{
		$table = POSTS_TABLE;
		$sql_id = 'post_id';
		$set_id = 'post_edit_locked';
		$l_prefix = 'POST';
	}
	
	if (!($forum_id = check_ids($ids, $table, $sql_id, 'm_lock')))
	{
		return;
	}
	
	$redirect = request_var('redirect', $user->data['session_page']);

	$s_hidden_fields = build_hidden_fields(array(
		$sql_id . '_list'	=> $ids,
		'mode'				=> $mode,
		'redirect'			=> $redirect)
	);
	$success_msg = '';

	if (confirm_box(true))
	{
		$sql = "UPDATE $table
			SET $set_id = " . (($mode == 'lock' || $mode == 'lock_post') ? ITEM_LOCKED : ITEM_UNLOCKED) . "
			WHERE $sql_id IN (" . implode(', ', $ids) . ")";
		$db->sql_query($sql);

		$data = ($mode == 'lock' || $mode == 'unlock') ? get_topic_data($ids) : get_post_data($ids);

		foreach ($data as $id => $row)
		{
			add_log('mod', $forum_id, $row['topic_id'], 'LOG_' . strtoupper($mode), $row['topic_title']);
		}
		
		$success_msg = $l_prefix . ((sizeof($ids) == 1) ? '' : 'S') . '_' . (($mode == 'lock' || $mode == 'lock_post') ? 'LOCKED' : 'UNLOCKED') . '_SUCCESS';
	}
	else
	{
		confirm_box(false, strtoupper($mode) . '_' . $l_prefix . ((sizeof($ids) == 1) ? '' : 'S'), $s_hidden_fields);
	}

	$redirect = request_var('redirect', "index.$phpEx$SID");

	if (strpos($redirect, '?') === false)
	{
		$redirect = substr_replace($redirect, ".$phpEx$SID&", strpos($redirect, '&'), 1);
	}

	if (!$success_msg)
	{
		redirect($redirect);
	}
	else
	{
		meta_refresh(2, $redirect);
		trigger_error($user->lang[$success_msg] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>'));
	}
}

// Change Topic Type
function change_topic_type($mode, $topic_ids)
{
	global $auth, $user, $db, $SID, $phpEx, $phpbb_root_path;

	if (!($forum_id = check_ids($topic_ids, TOPICS_TABLE, 'topic_id', 'm_')))
	{
		return;
	}

	switch ($mode)
	{
		case 'make_announce':
			$new_topic_type = POST_ANNOUNCE;
			$check_acl = 'f_announce';
			$l_new_type = (sizeof($topic_ids) == 1) ? 'MCP_MAKE_ANNOUNCEMENT' : 'MCP_MAKE_ANNOUNCEMENTS';
			break;
		case 'make_global':
			$new_topic_type = POST_GLOBAL;
			$check_acl = 'f_announce';
			$l_new_type = (sizeof($topic_ids) == 1) ? 'MCP_MAKE_GLOBAL' : 'MCP_MAKE_GLOBALS';
			break;
		case 'make_sticky':
			$new_topic_type = POST_STICKY;
			$check_acl = 'f_sticky';
			$l_new_type = (sizeof($topic_ids) == 1) ? 'MCP_MAKE_STICKY' : 'MCP_MAKE_STICKIES';
			break;
		default:
			$new_topic_type = POST_NORMAL;
			$check_acl = '';
			$l_new_type = (sizeof($topic_ids) == 1) ? 'MCP_MAKE_NORMAL' : 'MCP_MAKE_NORMALS';
			break;
	}

	$redirect = request_var('redirect', $user->data['session_page']);

	$s_hidden_fields = build_hidden_fields(array(
		'topic_id_list'	=> $topic_ids,
		'f'				=> $forum_id,
		'mode'			=> $mode,
		'redirect'		=> $redirect)
	);
	$success_msg = '';

	if (confirm_box(true))
	{
		if ($new_topic_type != POST_GLOBAL)
		{
			$sql = 'UPDATE ' . TOPICS_TABLE . "
				SET topic_type = $new_topic_type
				WHERE topic_id IN (" . implode(', ', $topic_ids) . ')
					AND forum_id <> 0';
			$db->sql_query($sql);

			// Reset forum id if a global topic is within the array
			if ($forum_id)
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . "
					SET topic_type = $new_topic_type, forum_id = $forum_id
						WHERE topic_id IN (" . implode(', ', $topic_ids) . ')
						AND forum_id = 0';
				$db->sql_query($sql);
			}
		}
		else
		{
			$sql = 'UPDATE ' . TOPICS_TABLE . "
				SET topic_type = $new_topic_type, forum_id = 0
				WHERE topic_id IN (" . implode(', ', $topic_ids) . ")";
			$db->sql_query($sql);
		}

		$success_msg = (sizeof($topic_ids) == 1) ? 'TOPIC_TYPE_CHANGED' : 'TOPICS_TYPE_CHANGED';

		$data = get_topic_data($topic_ids);

		foreach ($data as $topic_id => $row)
		{
			add_log('mod', $forum_id, $topic_id, 'LOG_TOPIC_TYPE_CHANGED', $row['topic_title']);
		}
	}
	else
	{
		confirm_box(false, $l_new_type, $s_hidden_fields);
	}

	$redirect = request_var('redirect', "index.$phpEx$SID");

	if (strpos($redirect, '?') === false)
	{
		$redirect = substr_replace($redirect, ".$phpEx$SID&", strpos($redirect, '&'), 1);
	}

	if (!$success_msg)
	{
		redirect($redirect);
	}
	else
	{
		meta_refresh(2, $redirect);
		trigger_error($user->lang[$success_msg] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>'));
	}
}

// Move Topic
function mcp_move_topic($topic_ids)
{
	global $auth, $user, $db, $template;
	global $SID, $phpEx, $phpbb_root_path;

	if (!($forum_id = check_ids($topic_ids, TOPICS_TABLE, 'topic_id', 'm_move')))
	{
		return;
	}
				
	$to_forum_id = request_var('to_forum_id', 0);
	$redirect = request_var('redirect', $user->data['session_page']);
	$additional_msg = $success_msg = '';

	$s_hidden_fields = build_hidden_fields(array(
		'topic_id_list'	=> $topic_ids,
		'f'				=> $forum_id,
		'mode'			=> 'move',
		'redirect'		=> $redirect)
	);

	if ($to_forum_id)
	{
		$forum_data = get_forum_data($to_forum_id);

		if (!sizeof($forum_data))
		{
			$additional_msg = $user->lang['FORUM_NOT_EXIST'];
		}
		else
		{
			$forum_data = $forum_data[$to_forum_id];
	
			if ($forum_data['forum_type'] != FORUM_POST)
			{
				$additional_msg = $user->lang['FORUM_NOT_POSTABLE'];
			}
			else if (!$auth->acl_get('f_post', $to_forum_id))
			{
				$additional_msg = $user->lang['USER_CANNOT_POST'];
			}
			else if ($forum_id == $to_forum_id)
			{
				$additional_msg = $user->lang['CANNOT_MOVE_SAME_FORUM'];
			}
		}
	}

	if (!$to_forum_id || $additional_msg)
	{
		unset($_POST['confirm']);
	}
	
	if (confirm_box(true))
	{
		$topic_data = get_topic_data($topic_ids);
		$leave_shadow = (isset($_POST['move_leave_shadow'])) ? true : false;

		// Move topics, but do not resync yet
		move_topics($topic_ids, $to_forum_id, false);

		$forum_ids = array($to_forum_id);
		foreach ($topic_data as $topic_id => $row)
		{
			// Get the list of forums to resync, add a log entry
			$forum_ids[] = $row['forum_id'];
			add_log('mod', $to_forum_id, $topic_id, 'LOG_MOVE', $row['forum_name']);

			// Leave a redirection if required and only if the topic is visible to users
			if ($leave_shadow && $row['topic_approved'])
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
					'topic_bumped'			=>	(int) $row['topic_bumped'],
					'topic_bumper'			=>	(int) $row['topic_bumper'],
					'poll_title'			=>	(string) $row['poll_title'],
					'poll_start'			=>	(int) $row['poll_start'],
					'poll_length'			=>	(int) $row['poll_length'],
					'poll_max_options'		=>	(int) $row['poll_max_options'],
					'poll_last_vote'		=>	(int) $row['poll_last_vote']
				);

				$db->sql_query('INSERT INTO ' . TOPICS_TABLE . $db->sql_build_array('INSERT', $shadow));
				
				// $next_id = $db->sql_nextid();
				// Mark Shadow topic read
				// markread('topic', $row['forum_id'], $next_id);
			}
		}
		unset($topic_data);

		// Now sync forums
		sync('forum', 'forum_id', $forum_ids);

		$success_msg = (sizeof($topic_ids) == 1) ? 'TOPIC_MOVED_SUCCESS' : 'TOPICS_MOVED_SUCCESS';
	}
	else
	{
		$template->assign_vars(array(
			'S_FORUM_SELECT'		=> make_forum_select($to_forum_id, $forum_id, false, true, true),
			'S_CAN_LEAVE_SHADOW'	=> true,
			'ADDITIONAL_MSG'		=> $additional_msg)
		);

		confirm_box(false, 'MOVE_TOPIC' . ((sizeof($topic_ids) == 1) ? '' : 'S'), $s_hidden_fields, 'mcp_move.html');
	}

	$redirect = request_var('redirect', "index.$phpEx$SID");

	if (strpos($redirect, '?') === false)
	{
		$redirect = substr_replace($redirect, ".$phpEx$SID&", strpos($redirect, '&'), 1);
	}

	if (!$success_msg)
	{
		redirect($redirect);
	}
	else
	{
		meta_refresh(3, $redirect);
		trigger_error($user->lang[$success_msg] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>') . '<br /><br />' . sprintf($user->lang['RETURN_NEW_FORUM'], '<a href="viewforum.' . $phpEx . $SID . '&amp;f=' . $to_forum_id . '">', '</a>'));
	}
}

// Delete Topics
function mcp_delete_topic($topic_ids)
{
	global $auth, $user, $db, $SID, $phpEx, $phpbb_root_path;

	if (!($forum_id = check_ids($topic_ids, TOPICS_TABLE, 'topic_id', 'm_delete')))
	{
		return;
	}

	$redirect = request_var('redirect', $user->data['session_page']);

	$s_hidden_fields = build_hidden_fields(array(
		'topic_id_list'	=> $topic_ids,
		'f'				=> $forum_id,
		'mode'			=> 'delete_topic',
		'redirect'		=> $redirect)
	);
	$success_msg = '';

	if (confirm_box(true))
	{
		$success_msg = (sizeof($topic_ids) == 1) ? 'TOPIC_DELETED_SUCCESS' : 'TOPICS_DELETED_SUCCESS';

		$data = get_topic_data($topic_ids);

		foreach ($data as $topic_id => $row)
		{
			add_log('mod', $forum_id, 0, 'LOG_TOPIC_DELETED', $row['topic_title']);
		}

		$return = delete_topics('topic_id', $topic_ids, true);

		// TODO: Adjust total post count...
	}
	else
	{
		confirm_box(false, (sizeof($topic_ids) == 1) ? 'DELETE_TOPIC' : 'DELETE_TOPICS', $s_hidden_fields);
	}

	$redirect = request_var('redirect', "index.$phpEx$SID");

	if (strpos($redirect, '?') === false)
	{
		$redirect = substr_replace($redirect, ".$phpEx$SID&", strpos($redirect, '&'), 1);
	}

	if (!$success_msg)
	{
		redirect($redirect);
	}
	else
	{
		meta_refresh(3, "viewforum.$phpEx$SID&amp;f=$forum_id");
		trigger_error($user->lang[$success_msg] . '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], '<a href="viewforum.' . $phpEx . $SID . '&amp;f=' . $forum_id . '">', '</a>'));
	}
}

// Delete Posts
function mcp_delete_post($post_ids)
{
	global $auth, $user, $db, $SID, $phpEx, $phpbb_root_path;

	if (!($forum_id = check_ids($post_ids, POSTS_TABLE, 'post_id', 'm_delete')))
	{
		return;
	}

	$redirect = request_var('redirect', $user->data['session_page']);

	$s_hidden_fields = build_hidden_fields(array(
		'post_id_list'	=> $post_ids,
		'f'				=> $forum_id,
		'mode'			=> 'delete_post',
		'redirect'		=> $redirect)
	);
	$success_msg = '';

	if (confirm_box(true))
	{
		// Count the number of topics that are affected
		// I did not use COUNT(DISTINCT ...) because I remember having problems
		// with it on older versions of MySQL -- Ashe

		$sql = 'SELECT DISTINCT topic_id
			FROM ' . POSTS_TABLE . '
			WHERE post_id IN (' . implode(', ', $post_ids) . ')';
		$result = $db->sql_query($sql);

		$topic_id_list = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$topic_id_list[] = $row['topic_id'];
		}
		$affected_topics = sizeof($topic_id_list);
		$db->sql_freeresult($result);

		// Now delete the posts, topics and forums are automatically resync'ed
		delete_posts('post_id', $post_ids);
					
		$sql = 'SELECT COUNT(topic_id) AS topics_left
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id IN (' . implode(', ', $topic_id_list) . ')';
		$result = $db->sql_query_limit($sql, 1);

		$deleted_topics = ($row = $db->sql_fetchrow($result)) ? ($affected_topics - $row['topics_left']) : $affected_topics;
		$db->sql_freeresult($result);

		$topic_id = request_var('t', 0);

		// Return links
		$return_link = array();
		if ($affected_topics == 1 && !$deleted_topics && $topic_id)
		{
			$return_link[] = sprintf($user->lang['RETURN_TOPIC'], "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id\">", '</a>');
		}
		$return_link[] = sprintf($user->lang['RETURN_FORUM'], "<a href=\"viewforum.$phpEx$SID&amp;f=$forum_id\">", '</a>');

		if (sizeof($post_ids) == 1)
		{
			if ($deleted_topics)
			{
				// We deleted the only post of a topic, which in turn has
				// been removed from the database
				$success_msg = $user->lang['TOPIC_DELETED_SUCCESS'];
			}
			else
			{
				$success_msg = $user->lang['POST_DELETED_SUCCESS'];
			}
		}
		else
		{
			if ($deleted_topics)
			{
				// Some of topics disappeared
				$success_msg = $user->lang['POSTS_DELETED_SUCCESS'] . '<br /><br />' . $user->lang['EMPTY_TOPICS_REMOVED_WARNING'];
			}
			else
			{
				$success_msg = $user->lang['POSTS_DELETED_SUCCESS'];
			}
		}
	}
	else
	{
		confirm_box(false, (sizeof($post_ids) == 1) ? 'DELETE_POST' : 'DELETE_POSTS', $s_hidden_fields);
	}

	$redirect = request_var('redirect', "index.$phpEx$SID");

	if (strpos($redirect, '?') === false)
	{
		$redirect = substr_replace($redirect, ".$phpEx$SID&", strpos($redirect, '&'), 1);
	}

	if (!$success_msg)
	{
		redirect($redirect);
	}
	else
	{
		meta_refresh(3, $redirect);
		trigger_error($success_msg . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>') . '<br /><br />' . implode('<br /><br />', $return_link));
	}
}

// Fork Topic
function mcp_fork_topic($topic_ids)
{
	global $auth, $user, $db, $template, $config;
	global $SID, $phpEx, $phpbb_root_path;

	if (!($forum_id = check_ids($topic_ids, TOPICS_TABLE, 'topic_id', 'm_')))
	{
		return;
	}
	
	$to_forum_id = request_var('to_forum_id', 0);
	$redirect = request_var('redirect', $user->data['session_page']);
	$additional_msg = $success_msg = '';

	$s_hidden_fields = build_hidden_fields(array(
		'topic_id_list'	=> $topic_ids,
		'f'				=> $forum_id,
		'mode'			=> 'fork',
		'redirect'		=> $redirect)
	);

	if ($to_forum_id)
	{
		$forum_data = get_forum_data($to_forum_id);

		if (!sizeof($topic_ids))
		{
			$additional_msg = $user->lang['NO_TOPICS_SELECTED'];
		}
		else if (!sizeof($forum_data))
		{
			$additional_msg = $user->lang['FORUM_NOT_EXIST'];
		}
		else
		{
			$forum_data = $forum_data[$to_forum_id];
	
			if ($forum_data['forum_type'] != FORUM_POST)
			{
				$additional_msg = $user->lang['FORUM_NOT_POSTABLE'];
			}
			else if (!$auth->acl_get('f_post', $to_forum_id))
			{
				$additional_msg = $user->lang['USER_CANNOT_POST'];
			}
		}
	}

	if (!$to_forum_id || $additional_msg)
	{
		unset($_POST['confirm']);
	}
	
	if (confirm_box(true))
	{
		$topic_data = get_topic_data($topic_ids);
		
		$total_posts = 0;
		$new_topic_id_list = array();
		foreach ($topic_data as $topic_id => $topic_row)
		{
			$sql_ary = array(
				'forum_id'					=> (int) $to_forum_id,
				'icon_id'					=> (int) $topic_row['icon_id'],
				'topic_attachment'			=> (int) $topic_row['topic_attachment'],
				'topic_approved'			=> 1,
				'topic_reported'			=> 0,
				'topic_title'				=> (string) $topic_row['topic_title'],
				'topic_poster'				=> (int) $topic_row['topic_poster'],
				'topic_time'				=> (int) $topic_row['topic_time'],
				'topic_replies'				=> (int) $topic_row['topic_replies_real'],
				'topic_replies_real'		=> (int) $topic_row['topic_replies_real'],
				'topic_status'				=> (int) $topic_row['topic_status'],
				'topic_type'				=> (int) $topic_row['topic_type'],
				'topic_first_poster_name'	=> (string) $topic_row['topic_first_poster_name'],
				'topic_last_poster_id'		=> (int) $topic_row['topic_last_poster_id'],
				'topic_last_poster_name'	=> (string) $topic_row['topic_last_poster_name'],
				'topic_last_post_time'		=> (int) $topic_row['topic_last_post_time'],
				'topic_last_view_time'		=> (int) $topic_row['topic_last_view_time'],
				'topic_bumped'				=> (int) $topic_row['topic_bumped'],
				'topic_bumper'				=> (int) $topic_row['topic_bumper'],
				'poll_title'				=> (string) $topic_row['poll_title'],
				'poll_start'				=> (int) $topic_row['poll_start'],
				'poll_length'				=> (int) $topic_row['poll_length']
			);

			$db->sql_query('INSERT INTO ' . TOPICS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
			$new_topic_id = $db->sql_nextid();
			$new_topic_id_list[$topic_id] = $new_topic_id;

			markread('topic', $to_forum_id, $new_topic_id);

			if ($topic_row['poll_start'])
			{
				$poll_rows = array();

				$sql = 'SELECT * 
					FROM ' . POLL_OPTIONS_TABLE . " 
					WHERE topic_id = $topic_id";
				$result = $db->sql_query($sql);

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
	
			$post_rows = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$post_rows[] = $row;
			}
			$db->sql_freeresult();

			if (!sizeof($post_rows))
			{
				continue;
			}

			$total_posts += sizeof($post_rows);
			foreach ($post_rows as $row)
			{
				$sql_ary = array(
					'topic_id'			=> (int) $new_topic_id,
					'forum_id'			=> (int) $to_forum_id,
					'poster_id'			=> (int) $row['poster_id'],
					'icon_id'			=> (int) $row['icon_id'],
					'poster_ip'			=> (string) $row['poster_ip'],
					'post_time'			=> (int) $row['post_time'],
					'post_approved'		=> 1,
					'post_reported'		=> 0,
					'enable_bbcode'		=> (int) $row['enable_bbcode'],
					'enable_html'		=> (int) $row['enable_html'],
					'enable_smilies'	=> (int) $row['enable_smilies'],
					'enable_magic_url'	=> (int) $row['enable_magic_url'],
					'enable_sig'		=> (int) $row['enable_sig'],
					'post_username'		=> (string) $row['post_username'],
					'post_subject'		=> (string) $row['post_subject'],
					'post_text'			=> (string) $row['post_text'],
					'post_edit_reason'	=> (string) $row['post_edit_reason'],
					'post_edit_user'	=> (int) $row['post_edit_user'],
					'post_checksum'		=> (string) $row['post_checksum'],
					'post_encoding'		=> (string) $row['post_encoding'],
					'post_attachment'	=> (int) $row['post_attachment'],
					'bbcode_bitfield'	=> (int) $row['bbcode_bitfield'],
					'bbcode_uid'		=> (string) $row['bbcode_uid'],
					'post_edit_time'	=> (int) $row['post_edit_time'],
					'post_edit_count'	=> (int) $row['post_edit_count'],
					'post_edit_locked'	=> (int) $row['post_edit_locked']
				);

				$db->sql_query('INSERT INTO ' . POSTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
				$new_post_id = $db->sql_nextid();

				// Copy Attachments
				if ($row['post_attachment'])
				{
					$sql = 'SELECT * FROM ' . ATTACHMENTS_TABLE . "
						WHERE post_msg_id = {$row['post_id']}
							AND topic_id = $topic_id
							AND in_message = 0";
					$result = $db->sql_query($sql);
					
					while ($attach_row = $db->sql_fetchrow($result))
					{
						$sql_ary = array(
							'post_msg_id'		=> (int) $new_post_id,
							'topic_id'			=> (int) $new_topic_id,
							'in_message'		=> 0,
							'poster_id'			=> (int) $attach_row['poster_id'],
							'physical_filename'	=> (string) $attach_row['physical_filename'],
							'real_filename'		=> (string) $attach_row['real_filename'],
							'download_count'	=> (int) $attach_row['download_count'],
							'comment'			=> (string) $attach_row['comment'],
							'extension'			=> (string) $attach_row['extension'],
							'mimetype'			=> (string) $attach_row['mimetype'],
							'filesize'			=> (int) $attach_row['filesize'],
							'filetime'			=> (int) $attach_row['filetime'],
							'thumbnail'			=> (int) $attach_row['thumbnail']
						);
						
						$db->sql_query('INSERT INTO ' . ATTACHMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
					}
					$db->sql_freeresult($result);
				}
			}
		}

		// Sync new topics, parent forums and board stats
		sync('topic', 'topic_id', $new_topic_id_list, true);
		sync('forum', 'forum_id', $to_forum_id, true);
		set_config('num_topics', $config['num_topics'] + sizeof($new_topic_id_list));
		set_config('num_posts', $config['num_posts'] + $total_posts);

		foreach ($new_topic_id_list as $topic_id => $new_topic_id)
		{
			add_log('mod', $to_forum_id, $new_topic_id, 'LOG_FORK', $topic_row['forum_name']);
		}

		$success_msg = (sizeof($topic_ids) == 1) ? 'TOPIC_FORKED_SUCCESS' : 'TOPICS_FORKED_SUCCESS';
	}
	else
	{
		$template->assign_vars(array(
			'S_FORUM_SELECT'		=> make_forum_select($to_forum_id, false, false, true, true),
			'S_CAN_LEAVE_SHADOW'	=> false,
			'ADDITIONAL_MSG'		=> $additional_msg)
		);

		confirm_box(false, 'FORK_TOPIC' . ((sizeof($topic_ids) == 1) ? '' : 'S'), $s_hidden_fields, 'mcp_move.html');
	}

	$redirect = request_var('redirect', "index.$phpEx$SID");

	if (strpos($redirect, '?') === false)
	{
		$redirect = substr_replace($redirect, ".$phpEx$SID&", strpos($redirect, '&'), 1);
	}

	if (!$success_msg)
	{
		redirect($redirect);
	}
	else
	{
		meta_refresh(3, "viewforum.$phpEx$SID&amp;f=$forum_id");
		$return_link = sprintf($user->lang['RETURN_FORUM'], '<a href="viewforum.' . $phpEx . $SID . '&amp;f=' . $forum_id . '">', '</a>');

		if ($forum_id != $to_forum_id)
		{
			$return_link .= '<br /><br />' . sprintf($user->lang['RETURN_NEW_FORUM'], '<a href="viewforum.' . $phpEx . $SID . '&amp;f=' . $to_forum_id . '">', '</a>');
		}

		trigger_error($user->lang[$success_msg] . '<br /><br />' . $return_link);
	}
}

?>