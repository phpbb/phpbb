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

// request_var, the array way
function get_array($var, $default_value)
{
	$ids = request_var($var, $default_value);
	
	if (!is_array($ids))
	{
		if (!$ids)
		{
			return $default_value;
		}

		$ids = array($ids);
	}

	$ids = array_unique($ids);

	if (sizeof($ids) == 1 && !$ids[0])
	{
		return $default_value;
	}

	return $ids;
}

//
// LITTLE HELPER

// Build simple hidden fields from array
function build_hidden_fields($field_ary)
{
	$s_hidden_fields = '';

	foreach ($field_ary as $name => $vars)
	{
		if (is_array($vars))
		{
			foreach ($vars as $key => $value)
			{
				$s_hidden_fields .= '<input type="hidden" name="' . $name . '[' . $key . ']" value="' . $value . '" />';
			}
		}
		else
		{
			$s_hidden_fields .= '<input type="hidden" name="' . $name . '" value="' . $vars . '" />';
		}
	}

	return $s_hidden_fields;
}

// Get simple topic data
function get_topic_data($topic_ids, $acl_list = false)
{
	global $auth, $db;
	$rowset = array();

	if (implode(', ', $topic_ids) == '')
	{
		return array();
	}

	$sql = 'SELECT f.*, t.*
		FROM ' . TOPICS_TABLE . ' t
			LEFT JOIN ' . FORUMS_TABLE . ' f ON t.forum_id = f.forum_id
		WHERE t.topic_id IN (' . implode(', ', $topic_ids) . ')';
	$result = $db->sql_query($sql);
		
	while ($row = $db->sql_fetchrow($result))
	{
		if ($acl_list && !$auth->acl_get($acl_list, $row['forum_id']))
		{
			continue;
		}

		$rowset[$row['topic_id']] = $row;
	}

	return $rowset;
}

// Get simple post data
function get_post_data($post_ids, $acl_list = false)
{
	global $db, $auth;
	$rowset = array();

	$sql = 'SELECT p.*, u.*, t.*, f.*
		FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u, ' . TOPICS_TABLE . ' t
			LEFT JOIN ' . FORUMS_TABLE . ' f ON f.forum_id = p.forum_id
		WHERE p.post_id IN (' . implode(', ', $post_ids) . ')
			AND u.user_id = p.poster_id
			AND t.topic_id = p.topic_id';
	$result = $db->sql_query($sql);
		
	while ($row = $db->sql_fetchrow($result))
	{
		if ($acl_list && !$auth->acl_get($acl_list, $row['forum_id']))
		{
			continue;
		}

		if (!$row['post_approved'] && !$auth->acl_get('m_approve', $row['forum_id']))
		{
			// Moderators without the permission to approve post should at least not see them. ;)
			continue;
		}

		$rowset[$row['post_id']] = $row;
	}

	return $rowset;
}

function get_forum_data($forum_id, $acl_list = 'f_list')
{
	global $auth, $db;
	$rowset = array();

	$sql = 'SELECT *
		FROM ' . FORUMS_TABLE . '
		WHERE forum_id ' . ((is_array($forum_id)) ? 'IN (' . implode(', ', $forum_id) . ')' : "= $forum_id");
	$result = $db->sql_query($sql);
		
	while ($row = $db->sql_fetchrow($result))
	{
		if ($acl_list && !$auth->acl_get($acl_list, $row['forum_id']))
		{
			continue;
		}
		if ($auth->acl_get('m_approve', $row['forum_id']))
		{
			$row['forum_topics'] = $row['forum_topics_real'];
		}

		$rowset[$row['forum_id']] = $row;
	}

	return $rowset;
}

function mcp_sorting($mode, &$sort_days, &$sort_key, &$sort_dir, &$sort_by_sql, &$sort_order_sql, &$total, $forum_id = 0, $topic_id = 0, $where_sql = 'WHERE')
{
	global $db, $user, $auth, $template;

	$sort_days = request_var('sort_days', 0);
	$min_time = ($sort_days) ? time() - ($sort_days * 86400) : 0;

	switch ($mode)
	{
		case 'viewforum':
			$type = 'topics';
			$default_key = 't';
			$default_dir = 'd';
			$sql = 'SELECT COUNT(topic_id) AS total
				FROM ' . TOPICS_TABLE . "
				$where_sql forum_id = $forum_id
					AND topic_type NOT IN (" . POST_ANNOUNCE . ', ' . POST_GLOBAL . ")
					AND topic_last_post_time >= $min_time";

			if (!$auth->acl_get('m_approve', $forum_id))
			{
				$sql .= 'AND topic_approved = 1';
			}
			break;

		case 'viewtopic':
			$type = 'posts';
			$default_key = 't';
			$default_dir = 'a';
			$sql = 'SELECT COUNT(post_id) AS total
				FROM ' . POSTS_TABLE . "
				$where_sql topic_id = $topic_id
					AND post_time >= $min_time";

			if (!$auth->acl_get('m_approve', $forum_id))
			{
				$sql .= 'AND post_approved = 1';
			}
			break;

		case 'unapproved_posts':
			$type = 'posts';
			$default_key = 't';
			$default_dir = 'd';
			$sql = 'SELECT COUNT(post_id) AS total
				FROM ' . POSTS_TABLE . "
				$where_sql forum_id IN (" . (($forum_id) ? $forum_id : implode(', ', get_forum_list('m_approve'))) . ')
					AND post_approved = 0
					AND post_time >= ' . $min_time;
			break;

		case 'unapproved_topics':
			$type = 'topics';
			$default_key = 't';
			$default_dir = 'd';
			$sql = 'SELECT COUNT(topic_id) AS total
				FROM ' . TOPICS_TABLE . "
				$where_sql forum_id IN (" . (($forum_id) ? $forum_id : implode(', ', get_forum_list('m_approve'))) . ')
					AND topic_approved = 0
					AND topic_time >= ' . $min_time;
			break;

		case 'reports':
			$type = 'reports';
			$default_key = 'p';
			$default_dir = 'd';
			$limit_time_sql = ($min_time) ? "AND r.report_time >= $min_time" : '';

			if ($topic_id)
			{
				$where_sql .= ' p.topic_id = ' . $topic_id;
			}
			else if ($forum_id)
			{
				$where_sql .= ' p.forum_id = ' . $forum_id;
			}
			else
			{
				$where_sql .= ' p.forum_id IN (' . implode(', ', get_forum_list('m_')) . ')';
			}
			$sql = 'SELECT COUNT(r.report_id) AS total
				FROM ' . REPORTS_TABLE . ' r, ' . POSTS_TABLE . " p
				$where_sql
					AND p.post_id = r.post_id
					$limit_time_sql";
			break;

		case 'viewlogs':
			$type = 'logs';
			$default_key = 't';
			$default_dir = 'd';
			$sql = 'SELECT COUNT(log_id) AS total
				FROM ' . LOG_TABLE . "
				$where_sql forum_id IN (" . (($forum_id) ? $forum_id : implode(', ', get_forum_list('m_'))) . ')
					AND log_time >= ' . $min_time . ' 
					AND log_type = ' . LOG_MOD;
			break;
	}

	$sort_key = request_var('sk', $default_key);
	$sort_dir = request_var('sd', $default_dir);
	$sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);

	switch ($type)
	{
		case 'topics':
			$limit_days = array(0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
			$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 'tt' => $user->lang['TOPIC_TIME'], 'r' => $user->lang['REPLIES'], 's' => $user->lang['SUBJECT'], 'v' => $user->lang['VIEWS']);

			$sort_by_sql = array('a' => 't.topic_first_poster_name', 't' => 't.topic_last_post_time', 'tt' => 't.topic_time', 'r' => (($auth->acl_get('m_approve', $forum_id)) ? 't.topic_replies_real' : 't.topic_replies'), 's' => 't.topic_title', 'v' => 't.topic_views');
			$limit_time_sql = ($min_time) ? "AND t.topic_last_post_time >= $min_time" : '';
			break;

		case 'posts':
			$limit_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
			$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 's' => $user->lang['SUBJECT']);
			$sort_by_sql = array('a' => 'u.username', 't' => 'p.post_id', 's' => 'p.post_subject');
			$limit_time_sql = ($min_time) ? "AND p.post_time >= $min_time" : '';
			break;

		case 'reports':
			$limit_days = array(0 => $user->lang['ALL_REPORTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
			$sort_by_text = array('p' => $user->lang['REPORT_PRIORITY'], 'r' => $user->lang['REPORTER'], 't' => $user->lang['REPORT_TIME']);
			$sort_by_sql = array('p' => 'rr.reason_priority', 'r' => 'u.username', 't' => 'r.report_time');
			break;

		case 'logs':
			$limit_days = array(0 => $user->lang['ALL_ENTRIES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
			$sort_by_text = array('u' => $user->lang['SORT_USERNAME'], 't' => $user->lang['SORT_DATE'], 'i' => $user->lang['SORT_IP'], 'o' => $user->lang['SORT_ACTION']);

			$sort_by_sql = array('u' => 'l.user_id', 't' => 'l.log_time', 'i' => 'l.log_ip', 'o' => 'l.log_operation');
			$limit_time_sql = ($min_time) ? "AND l.log_time >= $min_time" : '';
			break;
	}

	$sort_order_sql = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

	$s_limit_days = $s_sort_key = $s_sort_dir = $sort_url = '';
	gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $sort_url);

	$template->assign_vars(array(
		'S_SELECT_SORT_DIR'	=>	$s_sort_dir,
		'S_SELECT_SORT_KEY' =>	$s_sort_key,
		'S_SELECT_SORT_DAYS'=>	$s_limit_days)
	);

	if (($sort_days && $mode != 'viewlogs') || $mode == 'reports')
	{
		$result = $db->sql_query($sql);
		$total = ($row = $db->sql_fetchrow($result)) ? $row['total'] : 0;
	}
	else
	{
		$total = -1;
	}
}

//
function check_ids(&$ids, $table, $sql_id, $acl_list = false)
{
	global $db, $auth;

	if (!is_array($ids) || !$ids)
	{
		return 0;
	}

	// a small logical error, since global announcement are assigned to forum_id == 0
	// If the first topic id is a global announcement, we can force the forum. Though only global announcements can be
	// tricked... i really do not know how to prevent this atm.

	// With those two queries we make sure all ids are within one forum...
	$sql = "SELECT forum_id FROM $table
		WHERE $sql_id = {$ids[0]}";
	$result = $db->sql_query($sql);
	$forum_id = (int) $db->sql_fetchfield('forum_id', 0, $result);
	$db->sql_freeresult($result);

	if (!$forum_id)
	{
		// Global Announcement?
		$forum_id = request_var('f', 0);
	}

	if ($acl_list && !$auth->acl_get($acl_list, $forum_id))
	{
		trigger_error('NOT_AUTHORIZED');
	}

	if (!$forum_id)
	{
		trigger_error('Missing forum_id, has to be in url if global announcement...');
	}

	$sql = "SELECT $sql_id FROM $table
		WHERE $sql_id IN (" . implode(', ', $ids) . ")
			AND (forum_id = $forum_id OR forum_id = 0)";
	$result = $db->sql_query($sql);

	$ids = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$ids[] = $row[$sql_id];
	}
	$db->sql_freeresult($result);

	return $forum_id;
}

// LITTLE HELPER
//

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
	global $auth, $user, $db, $SID, $phpEx, $phpbb_root_path, $template;
	global $_POST, $_REQUEST;

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
				$next_id = $db->sql_nextid();

				// Mark Shadow topic read
				markread('topic', $row['forum_id'], $next_id);
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

// Delete Topics
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

?>