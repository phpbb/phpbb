<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* mcp_main
* Handling mcp actions
*/
class mcp_main
{
	var $p_master;
	var $u_action;

	function mcp_main(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($id, $mode)
	{
		global $auth, $user, $action;
		global $phpbb_root_path, $phpEx, $request;
		global $phpbb_dispatcher;

		$quickmod = ($mode == 'quickmod') ? true : false;

		switch ($action)
		{
			case 'lock':
			case 'unlock':
				$topic_ids = (!$quickmod) ? $request->variable('topic_id_list', array(0)) : array($request->variable('t', 0));

				if (!count($topic_ids))
				{
					trigger_error('NO_TOPIC_SELECTED');
				}

				lock_unlock($action, $topic_ids);
			break;

			case 'lock_post':
			case 'unlock_post':

				$post_ids = (!$quickmod) ? $request->variable('post_id_list', array(0)) : array($request->variable('p', 0));

				if (!count($post_ids))
				{
					trigger_error('NO_POST_SELECTED');
				}

				lock_unlock($action, $post_ids);
			break;

			case 'make_announce':
			case 'make_sticky':
			case 'make_global':
			case 'make_normal':

				$topic_ids = (!$quickmod) ? $request->variable('topic_id_list', array(0)) : array($request->variable('t', 0));

				if (!count($topic_ids))
				{
					trigger_error('NO_TOPIC_SELECTED');
				}

				change_topic_type($action, $topic_ids);
			break;

			case 'move':
				$user->add_lang('viewtopic');

				$topic_ids = (!$quickmod) ? $request->variable('topic_id_list', array(0)) : array($request->variable('t', 0));

				if (!count($topic_ids))
				{
					trigger_error('NO_TOPIC_SELECTED');
				}

				mcp_move_topic($topic_ids);
			break;

			case 'fork':
				$user->add_lang('viewtopic');

				$topic_ids = (!$quickmod) ? $request->variable('topic_id_list', array(0)) : array($request->variable('t', 0));

				if (!count($topic_ids))
				{
					trigger_error('NO_TOPIC_SELECTED');
				}

				mcp_fork_topic($topic_ids);
			break;

			case 'delete_topic':
				$user->add_lang('viewtopic');

				// f parameter is not reliable for permission usage, however we just use it to decide
				// which permission we will check later on. So if it is manipulated, we will still catch it later on.
				$forum_id = $request->variable('f', 0);
				$topic_ids = (!$quickmod) ? $request->variable('topic_id_list', array(0)) : array($request->variable('t', 0));
				$soft_delete = (($request->is_set_post('confirm') && !$request->is_set_post('delete_permanent')) || !$auth->acl_get('m_delete', $forum_id)) ? true : false;

				if (!count($topic_ids))
				{
					trigger_error('NO_TOPIC_SELECTED');
				}

				mcp_delete_topic($topic_ids, $soft_delete, $request->variable('delete_reason', '', true));
			break;

			case 'delete_post':
				$user->add_lang('posting');

				// f parameter is not reliable for permission usage, however we just use it to decide
				// which permission we will check later on. So if it is manipulated, we will still catch it later on.
				$forum_id = $request->variable('f', 0);
				$post_ids = (!$quickmod) ? $request->variable('post_id_list', array(0)) : array($request->variable('p', 0));
				$soft_delete = (($request->is_set_post('confirm') && !$request->is_set_post('delete_permanent')) || !$auth->acl_get('m_delete', $forum_id)) ? true : false;

				if (!count($post_ids))
				{
					trigger_error('NO_POST_SELECTED');
				}

				mcp_delete_post($post_ids, $soft_delete, $request->variable('delete_reason', '', true));
			break;

			case 'restore_topic':
				$user->add_lang('posting');

				$topic_ids = (!$quickmod) ? $request->variable('topic_id_list', array(0)) : array($request->variable('t', 0));

				if (!count($topic_ids))
				{
					trigger_error('NO_TOPIC_SELECTED');
				}

				mcp_restore_topic($topic_ids);
			break;

			default:
				/**
				* This event allows you to handle custom quickmod options
				*
				* @event core.modify_quickmod_actions
				* @var	string	action		Topic quick moderation action name
				* @var	bool	quickmod	Flag indicating whether MCP is in quick moderation mode
				* @since 3.1.0-a4
				* @changed 3.1.0-RC4 Added variables: action, quickmod
				*/
				$vars = array('action', 'quickmod');
				extract($phpbb_dispatcher->trigger_event('core.modify_quickmod_actions', compact($vars)));
			break;
		}

		switch ($mode)
		{
			case 'front':
				include($phpbb_root_path . 'includes/mcp/mcp_front.' . $phpEx);

				$user->add_lang('acp/common');

				mcp_front_view($id, $mode, $action);

				$this->tpl_name = 'mcp_front';
				$this->page_title = 'MCP_MAIN';
			break;

			case 'forum_view':
				include($phpbb_root_path . 'includes/mcp/mcp_forum.' . $phpEx);

				$user->add_lang('viewforum');

				$forum_id = $request->variable('f', 0);

				$forum_info = phpbb_get_forum_data($forum_id, 'm_', true);

				if (!count($forum_info))
				{
					$this->main('main', 'front');
					return;
				}

				$forum_info = $forum_info[$forum_id];

				mcp_forum_view($id, $mode, $action, $forum_info);

				$this->tpl_name = 'mcp_forum';
				$this->page_title = 'MCP_MAIN_FORUM_VIEW';
			break;

			case 'topic_view':
				include($phpbb_root_path . 'includes/mcp/mcp_topic.' . $phpEx);

				mcp_topic_view($id, $mode, $action);

				$this->tpl_name = 'mcp_topic';
				$this->page_title = 'MCP_MAIN_TOPIC_VIEW';
			break;

			case 'post_details':
				include($phpbb_root_path . 'includes/mcp/mcp_post.' . $phpEx);

				mcp_post_details($id, $mode, $action);

				$this->tpl_name = ($action == 'whois') ? 'mcp_whois' : 'mcp_post';
				$this->page_title = 'MCP_MAIN_POST_DETAILS';
			break;

			default:
				if ($quickmod)
				{
					switch ($action)
					{
						case 'lock':
						case 'unlock':
						case 'make_announce':
						case 'make_sticky':
						case 'make_global':
						case 'make_normal':
						case 'make_onindex':
						case 'move':
						case 'fork':
						case 'delete_topic':
							trigger_error('TOPIC_NOT_EXIST');
						break;

						case 'lock_post':
						case 'unlock_post':
						case 'delete_post':
							trigger_error('POST_NOT_EXIST');
						break;
					}
				}

				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}
	}
}

/**
* Lock/Unlock Topic/Post
*/
function lock_unlock($action, $ids)
{
	global $user, $db, $request, $phpbb_log, $phpbb_dispatcher;

	if ($action == 'lock' || $action == 'unlock')
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

	$orig_ids = $ids;

	if (!phpbb_check_ids($ids, $table, $sql_id, array('m_lock')))
	{
		// Make sure that for f_user_lock only the lock action is triggered.
		if ($action != 'lock')
		{
			return;
		}

		$ids = $orig_ids;

		if (!phpbb_check_ids($ids, $table, $sql_id, array('f_user_lock')))
		{
			return;
		}
	}
	unset($orig_ids);

	$redirect = $request->variable('redirect', build_url(array('action', 'quickmod')));
	$redirect = reapply_sid($redirect);

	$s_hidden_fields = build_hidden_fields(array(
		$sql_id . '_list'	=> $ids,
		'action'			=> $action,
		'redirect'			=> $redirect)
	);

	if (confirm_box(true))
	{
		$sql = "UPDATE $table
			SET $set_id = " . (($action == 'lock' || $action == 'lock_post') ? ITEM_LOCKED : ITEM_UNLOCKED) . '
			WHERE ' . $db->sql_in_set($sql_id, $ids);
		$db->sql_query($sql);

		$data = ($action == 'lock' || $action == 'unlock') ? phpbb_get_topic_data($ids) : phpbb_get_post_data($ids);

		foreach ($data as $id => $row)
		{
			$phpbb_log->add('mod', $user->data['user_id'], $user->ip, 'LOG_' . strtoupper($action), false, array(
				'forum_id' => $row['forum_id'],
				'topic_id' => $row['topic_id'],
				'post_id'  => isset($row['post_id']) ? $row['post_id'] : 0,
				$row['topic_title']
			));
		}

		/**
		 * Perform additional actions after locking/unlocking posts/topics
		 *
		 * @event core.mcp_lock_unlock_after
		 * @var	string	action				Variable containing the action we perform on the posts/topics ('lock', 'unlock', 'lock_post' or 'unlock_post')
		 * @var	array	ids					Array containing the post/topic IDs that have been locked/unlocked
		 * @var	array	data				Array containing posts/topics data
		 * @since 3.1.7-RC1
		 */
		$vars = array(
			'action',
			'ids',
			'data',
		);
		extract($phpbb_dispatcher->trigger_event('core.mcp_lock_unlock_after', compact($vars)));

		$success_msg = $l_prefix . ((count($ids) == 1) ? '' : 'S') . '_' . (($action == 'lock' || $action == 'lock_post') ? 'LOCKED' : 'UNLOCKED') . '_SUCCESS';

		meta_refresh(2, $redirect);
		$message = $user->lang[$success_msg];

		if (!$request->is_ajax())
		{
			$message .= '<br /><br />' . $user->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>');
		}
		trigger_error($message);
	}
	else
	{
		confirm_box(false, strtoupper($action) . '_' . $l_prefix . ((count($ids) == 1) ? '' : 'S'), $s_hidden_fields);
	}

	redirect($redirect);
}

/**
* Change Topic Type
*/
function change_topic_type($action, $topic_ids)
{
	global $user, $db, $request, $phpbb_log;

	switch ($action)
	{
		case 'make_announce':
			$new_topic_type = POST_ANNOUNCE;
			$check_acl = 'f_announce';
			$l_new_type = (count($topic_ids) == 1) ? 'MCP_MAKE_ANNOUNCEMENT' : 'MCP_MAKE_ANNOUNCEMENTS';
		break;

		case 'make_global':
			$new_topic_type = POST_GLOBAL;
			$check_acl = 'f_announce_global';
			$l_new_type = (count($topic_ids) == 1) ? 'MCP_MAKE_GLOBAL' : 'MCP_MAKE_GLOBALS';
		break;

		case 'make_sticky':
			$new_topic_type = POST_STICKY;
			$check_acl = 'f_sticky';
			$l_new_type = (count($topic_ids) == 1) ? 'MCP_MAKE_STICKY' : 'MCP_MAKE_STICKIES';
		break;

		default:
			$new_topic_type = POST_NORMAL;
			$check_acl = false;
			$l_new_type = (count($topic_ids) == 1) ? 'MCP_MAKE_NORMAL' : 'MCP_MAKE_NORMALS';
		break;
	}

	$forum_id = phpbb_check_ids($topic_ids, TOPICS_TABLE, 'topic_id', $check_acl, true);

	if ($forum_id === false)
	{
		return;
	}

	$redirect = $request->variable('redirect', build_url(array('action', 'quickmod')));
	$redirect = reapply_sid($redirect);

	$s_hidden_fields = array(
		'topic_id_list'	=> $topic_ids,
		'f'				=> $forum_id,
		'action'		=> $action,
		'redirect'		=> $redirect,
	);

	if (confirm_box(true))
	{
		$sql = 'UPDATE ' . TOPICS_TABLE . "
			SET topic_type = $new_topic_type
			WHERE " . $db->sql_in_set('topic_id', $topic_ids);
		$db->sql_query($sql);

		if (($new_topic_type == POST_GLOBAL) && count($topic_ids))
		{
			// Delete topic shadows for global announcements
			$sql = 'DELETE FROM ' . TOPICS_TABLE . '
				WHERE ' . $db->sql_in_set('topic_moved_id', $topic_ids);
			$db->sql_query($sql);

			$sql = 'UPDATE ' . TOPICS_TABLE . "
				SET topic_type = $new_topic_type
					WHERE " . $db->sql_in_set('topic_id', $topic_ids);
			$db->sql_query($sql);
		}

		$success_msg = (count($topic_ids) == 1) ? 'TOPIC_TYPE_CHANGED' : 'TOPICS_TYPE_CHANGED';

		if (count($topic_ids))
		{
			$data = phpbb_get_topic_data($topic_ids);

			foreach ($data as $topic_id => $row)
			{
				$phpbb_log->add('mod', $user->data['user_id'], $user->ip, 'LOG_TOPIC_TYPE_CHANGED', false, array(
					'forum_id' => $forum_id,
					'topic_id' => $topic_id,
					$row['topic_title']
				));
			}
		}

		meta_refresh(2, $redirect);
		$message = $user->lang[$success_msg];

		if (!$request->is_ajax())
		{
			$message .= '<br /><br />' . $user->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>');
		}
		trigger_error($message);
	}
	else
	{
		confirm_box(false, $l_new_type, build_hidden_fields($s_hidden_fields));
	}

	redirect($redirect);
}

/**
* Move Topic
*/
function mcp_move_topic($topic_ids)
{
	global $auth, $user, $db, $template, $phpbb_log, $request, $phpbb_dispatcher;
	global $phpEx, $phpbb_root_path;

	// Here we limit the operation to one forum only
	$forum_id = phpbb_check_ids($topic_ids, TOPICS_TABLE, 'topic_id', array('m_move'), true);

	if ($forum_id === false)
	{
		return;
	}

	$to_forum_id = $request->variable('to_forum_id', 0);
	$redirect = $request->variable('redirect', build_url(array('action', 'quickmod')));
	$additional_msg = $success_msg = '';

	$s_hidden_fields = build_hidden_fields(array(
		'topic_id_list'	=> $topic_ids,
		'f'				=> $forum_id,
		'action'		=> 'move',
		'redirect'		=> $redirect)
	);

	if ($to_forum_id)
	{
		$forum_data = phpbb_get_forum_data($to_forum_id, 'f_post');

		if (!count($forum_data))
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
			else if (!$auth->acl_get('f_post', $to_forum_id) || (!$auth->acl_get('m_approve', $to_forum_id) && !$auth->acl_get('f_noapprove', $to_forum_id)))
			{
				$additional_msg = $user->lang['USER_CANNOT_POST'];
			}
			else if ($forum_id == $to_forum_id)
			{
				$additional_msg = $user->lang['CANNOT_MOVE_SAME_FORUM'];
			}
		}
	}
	else if (isset($_POST['confirm']))
	{
		$additional_msg = $user->lang['FORUM_NOT_EXIST'];
	}

	if (!$to_forum_id || $additional_msg)
	{
		$request->overwrite('confirm', null, \phpbb\request\request_interface::POST);
		$request->overwrite('confirm_key', null);
	}

	if (confirm_box(true))
	{
		$topic_data = phpbb_get_topic_data($topic_ids);
		$leave_shadow = (isset($_POST['move_leave_shadow'])) ? true : false;

		$forum_sync_data = array();

		$forum_sync_data[$forum_id] = current($topic_data);
		$forum_sync_data[$to_forum_id] = $forum_data;

		$topics_moved = $topics_moved_unapproved = $topics_moved_softdeleted = 0;
		$posts_moved = $posts_moved_unapproved = $posts_moved_softdeleted = 0;

		foreach ($topic_data as $topic_id => $topic_info)
		{
			if ($topic_info['topic_visibility'] == ITEM_APPROVED)
			{
				$topics_moved++;
			}
			else if ($topic_info['topic_visibility'] == ITEM_UNAPPROVED || $topic_info['topic_visibility'] == ITEM_REAPPROVE)
			{
				$topics_moved_unapproved++;
			}
			else if ($topic_info['topic_visibility'] == ITEM_DELETED)
			{
				$topics_moved_softdeleted++;
			}

			$posts_moved += $topic_info['topic_posts_approved'];
			$posts_moved_unapproved += $topic_info['topic_posts_unapproved'];
			$posts_moved_softdeleted += $topic_info['topic_posts_softdeleted'];
		}

		$db->sql_transaction('begin');

		// Move topics, but do not resync yet
		move_topics($topic_ids, $to_forum_id, false);

		if ($request->is_set_post('move_lock_topics') && $auth->acl_get('m_lock', $to_forum_id))
		{
			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_status = ' . ITEM_LOCKED . '
				WHERE ' . $db->sql_in_set('topic_id', $topic_ids);
			$db->sql_query($sql);
		}

		$shadow_topics = 0;
		$forum_ids = array($to_forum_id);
		foreach ($topic_data as $topic_id => $row)
		{
			// Get the list of forums to resync
			$forum_ids[] = $row['forum_id'];

			// We add the $to_forum_id twice, because 'forum_id' is updated
			// when the topic is moved again later.
			$phpbb_log->add('mod', $user->data['user_id'], $user->ip, 'LOG_MOVE', false, array(
				'forum_id'		=> (int) $to_forum_id,
				'topic_id'		=> (int) $topic_id,
				$row['forum_name'],
				$forum_data['forum_name'],
				(int) $row['forum_id'],
				(int) $forum_data['forum_id'],
			));

			// Leave a redirection if required and only if the topic is visible to users
			if ($leave_shadow && $row['topic_visibility'] == ITEM_APPROVED && $row['topic_type'] != POST_GLOBAL)
			{
				$shadow = array(
					'forum_id'				=>	(int) $row['forum_id'],
					'icon_id'				=>	(int) $row['icon_id'],
					'topic_attachment'		=>	(int) $row['topic_attachment'],
					'topic_visibility'		=>	ITEM_APPROVED, // a shadow topic is always approved
					'topic_reported'		=>	0, // a shadow topic is never reported
					'topic_title'			=>	(string) $row['topic_title'],
					'topic_poster'			=>	(int) $row['topic_poster'],
					'topic_time'			=>	(int) $row['topic_time'],
					'topic_time_limit'		=>	(int) $row['topic_time_limit'],
					'topic_views'			=>	(int) $row['topic_views'],
					'topic_posts_approved'	=>	(int) $row['topic_posts_approved'],
					'topic_posts_unapproved'=>	(int) $row['topic_posts_unapproved'],
					'topic_posts_softdeleted'=>	(int) $row['topic_posts_softdeleted'],
					'topic_status'			=>	ITEM_MOVED,
					'topic_type'			=>	POST_NORMAL,
					'topic_first_post_id'	=>	(int) $row['topic_first_post_id'],
					'topic_first_poster_colour'=>(string) $row['topic_first_poster_colour'],
					'topic_first_poster_name'=>	(string) $row['topic_first_poster_name'],
					'topic_last_post_id'	=>	(int) $row['topic_last_post_id'],
					'topic_last_poster_id'	=>	(int) $row['topic_last_poster_id'],
					'topic_last_poster_colour'=>(string) $row['topic_last_poster_colour'],
					'topic_last_poster_name'=>	(string) $row['topic_last_poster_name'],
					'topic_last_post_subject'=>	(string) $row['topic_last_post_subject'],
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

				/**
				* Perform actions before shadow topic is created.
				*
				* @event core.mcp_main_modify_shadow_sql
				* @var	array	shadow	SQL array to be used by $db->sql_build_array
				* @var	array	row		Topic data
				* @since 3.1.11-RC1
				* @changed 3.1.11-RC1 Added variable: row
				*/
				$vars = array(
					'shadow',
					'row',
				);
				extract($phpbb_dispatcher->trigger_event('core.mcp_main_modify_shadow_sql', compact($vars)));

				$db->sql_query('INSERT INTO ' . TOPICS_TABLE . $db->sql_build_array('INSERT', $shadow));

				// Shadow topics only count on new "topics" and not posts... a shadow topic alone has 0 posts
				$shadow_topics++;
			}
		}
		unset($topic_data);

		$sync_sql = array();
		if ($posts_moved)
		{
			$sync_sql[$to_forum_id][] = 'forum_posts_approved = forum_posts_approved + ' . (int) $posts_moved;
			$sync_sql[$forum_id][] = 'forum_posts_approved = forum_posts_approved - ' . (int) $posts_moved;
		}
		if ($posts_moved_unapproved)
		{
			$sync_sql[$to_forum_id][] = 'forum_posts_unapproved = forum_posts_unapproved + ' . (int) $posts_moved_unapproved;
			$sync_sql[$forum_id][] = 'forum_posts_unapproved = forum_posts_unapproved - ' . (int) $posts_moved_unapproved;
		}
		if ($posts_moved_softdeleted)
		{
			$sync_sql[$to_forum_id][] = 'forum_posts_softdeleted = forum_posts_softdeleted + ' . (int) $posts_moved_softdeleted;
			$sync_sql[$forum_id][] = 'forum_posts_softdeleted = forum_posts_softdeleted - ' . (int) $posts_moved_softdeleted;
		}

		if ($topics_moved)
		{
			$sync_sql[$to_forum_id][] = 'forum_topics_approved = forum_topics_approved + ' . (int) $topics_moved;
			if ($topics_moved - $shadow_topics > 0)
			{
				$sync_sql[$forum_id][] = 'forum_topics_approved = forum_topics_approved - ' . (int) ($topics_moved - $shadow_topics);
			}
		}
		if ($topics_moved_unapproved)
		{
			$sync_sql[$to_forum_id][] = 'forum_topics_unapproved = forum_topics_unapproved + ' . (int) $topics_moved_unapproved;
			$sync_sql[$forum_id][] = 'forum_topics_unapproved = forum_topics_unapproved - ' . (int) $topics_moved_unapproved;
		}
		if ($topics_moved_softdeleted)
		{
			$sync_sql[$to_forum_id][] = 'forum_topics_softdeleted = forum_topics_softdeleted + ' . (int) $topics_moved_softdeleted;
			$sync_sql[$forum_id][] = 'forum_topics_softdeleted = forum_topics_softdeleted - ' . (int) $topics_moved_softdeleted;
		}

		$success_msg = (count($topic_ids) == 1) ? 'TOPIC_MOVED_SUCCESS' : 'TOPICS_MOVED_SUCCESS';

		foreach ($sync_sql as $forum_id_key => $array)
		{
			$sql = 'UPDATE ' . FORUMS_TABLE . '
				SET ' . implode(', ', $array) . '
				WHERE forum_id = ' . $forum_id_key;
			$db->sql_query($sql);
		}

		$db->sql_transaction('commit');

		sync('forum', 'forum_id', array($forum_id, $to_forum_id));
	}
	else
	{
		$template->assign_vars(array(
			'S_FORUM_SELECT'		=> make_forum_select($to_forum_id, $forum_id, false, true, true, true),
			'S_CAN_LEAVE_SHADOW'	=> true,
			'S_CAN_LOCK_TOPIC'		=> ($auth->acl_get('m_lock', $to_forum_id)) ? true : false,
			'ADDITIONAL_MSG'		=> $additional_msg)
		);

		confirm_box(false, 'MOVE_TOPIC' . ((count($topic_ids) == 1) ? '' : 'S'), $s_hidden_fields, 'mcp_move.html');
	}

	$redirect = $request->variable('redirect', "index.$phpEx");
	$redirect = reapply_sid($redirect);

	if (!$success_msg)
	{
		redirect($redirect);
	}
	else
	{
		meta_refresh(3, $redirect);

		$message = $user->lang[$success_msg];
		$message .= '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>');
		$message .= '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], '<a href="' . append_sid("{$phpbb_root_path}viewforum.$phpEx", "f=$forum_id") . '">', '</a>');
		$message .= '<br /><br />' . sprintf($user->lang['RETURN_NEW_FORUM'], '<a href="' . append_sid("{$phpbb_root_path}viewforum.$phpEx", "f=$to_forum_id") . '">', '</a>');

		trigger_error($message);
	}
}

/**
* Restore Topics
*/
function mcp_restore_topic($topic_ids)
{
	global $user, $phpEx, $phpbb_root_path, $request, $phpbb_container, $phpbb_log;

	if (!phpbb_check_ids($topic_ids, TOPICS_TABLE, 'topic_id', array('m_approve')))
	{
		return;
	}

	$redirect = $request->variable('redirect', build_url(array('action', 'quickmod')));
	$forum_id = $request->variable('f', 0);

	$s_hidden_fields = build_hidden_fields(array(
		'topic_id_list'	=> $topic_ids,
		'f'				=> $forum_id,
		'action'		=> 'restore_topic',
		'redirect'		=> $redirect,
	));
	$success_msg = '';

	if (confirm_box(true))
	{
		$success_msg = (count($topic_ids) == 1) ? 'TOPIC_RESTORED_SUCCESS' : 'TOPICS_RESTORED_SUCCESS';

		$data = phpbb_get_topic_data($topic_ids);

		/* @var $phpbb_content_visibility \phpbb\content_visibility */
		$phpbb_content_visibility = $phpbb_container->get('content.visibility');
		foreach ($data as $topic_id => $row)
		{
			$return = $phpbb_content_visibility->set_topic_visibility(ITEM_APPROVED, $topic_id, $row['forum_id'], $user->data['user_id'], time(), '');
			if (!empty($return))
			{
				$phpbb_log->add('mod', $user->data['user_id'], $user->ip, 'LOG_RESTORE_TOPIC', false, array(
					'forum_id' => $row['forum_id'],
					'topic_id' => $topic_id,
					$row['topic_title'],
					$row['topic_first_poster_name']
				));
			}
		}
	}
	else
	{
		confirm_box(false, (count($topic_ids) == 1) ? 'RESTORE_TOPIC' : 'RESTORE_TOPICS', $s_hidden_fields);
	}

	$topic_id = $request->variable('t', 0);
	if (!$request->is_set('quickmod', \phpbb\request\request_interface::REQUEST))
	{
		$redirect = $request->variable('redirect', "index.$phpEx");
		$redirect = reapply_sid($redirect);
		$redirect_message = 'PAGE';
	}
	else if ($topic_id)
	{
		$redirect = append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=' . $topic_id);
		$redirect_message = 'TOPIC';
	}
	else
	{
		$redirect = append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id);
		$redirect_message = 'FORUM';
	}

	if (!$success_msg)
	{
		redirect($redirect);
	}
	else
	{
		meta_refresh(3, $redirect);
		trigger_error($user->lang[$success_msg] . '<br /><br />' . sprintf($user->lang['RETURN_' . $redirect_message], '<a href="' . $redirect . '">', '</a>'));
	}
}

/**
* Delete Topics
*/
function mcp_delete_topic($topic_ids, $is_soft = false, $soft_delete_reason = '', $action = 'delete_topic')
{
	global $auth, $user, $db, $phpEx, $phpbb_root_path, $request, $phpbb_container, $phpbb_log;

	$check_permission = ($is_soft) ? 'm_softdelete' : 'm_delete';
	if (!phpbb_check_ids($topic_ids, TOPICS_TABLE, 'topic_id', array($check_permission)))
	{
		return;
	}

	$redirect = $request->variable('redirect', build_url(array('action', 'quickmod')));
	$forum_id = $request->variable('f', 0);

	$s_hidden_fields = array(
		'topic_id_list'	=> $topic_ids,
		'f'				=> $forum_id,
		'action'		=> $action,
		'redirect'		=> $redirect,
	);
	$success_msg = '';

	if (confirm_box(true))
	{
		$success_msg = (count($topic_ids) == 1) ? 'TOPIC_DELETED_SUCCESS' : 'TOPICS_DELETED_SUCCESS';

		$data = phpbb_get_topic_data($topic_ids);

		foreach ($data as $topic_id => $row)
		{
			if ($row['topic_moved_id'])
			{
				$phpbb_log->add('mod', $user->data['user_id'], $user->ip, 'LOG_DELETE_SHADOW_TOPIC', false, array(
					'forum_id' => $row['forum_id'],
					'topic_id' => $topic_id,
					$row['topic_title']
				));
			}
			else
			{
				// Only soft delete non-shadow topics
				if ($is_soft)
				{
					/* @var $phpbb_content_visibility \phpbb\content_visibility */
					$phpbb_content_visibility = $phpbb_container->get('content.visibility');
					$return = $phpbb_content_visibility->set_topic_visibility(ITEM_DELETED, $topic_id, $row['forum_id'], $user->data['user_id'], time(), $soft_delete_reason);
					if (!empty($return))
					{
						$phpbb_log->add('mod', $user->data['user_id'], $user->ip, 'LOG_SOFTDELETE_TOPIC', false, array(
							'forum_id' => $row['forum_id'],
							'topic_id' => $topic_id,
							$row['topic_title'],
							$row['topic_first_poster_name'],
							$soft_delete_reason
						));
					}
				}
				else
				{
					$phpbb_log->add('mod', $user->data['user_id'], $user->ip, 'LOG_DELETE_TOPIC', false, array(
						'forum_id' => $row['forum_id'],
						'topic_id' => $topic_id,
						$row['topic_title'],
						$row['topic_first_poster_name'],
						$soft_delete_reason
					));
				}
			}
		}

		if (!$is_soft)
		{
			delete_topics('topic_id', $topic_ids);
		}
	}
	else
	{
		global $template;

		$user->add_lang('posting');

		// If there are only shadow topics, we neither need a reason nor softdelete
		$sql = 'SELECT topic_id
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $db->sql_in_set('topic_id', $topic_ids) . '
				AND topic_moved_id = 0';
		$result = $db->sql_query_limit($sql, 1);
		$only_shadow = !$db->sql_fetchfield('topic_id');
		$db->sql_freeresult($result);

		$only_softdeleted = false;
		if (!$only_shadow && $auth->acl_get('m_delete', $forum_id) && $auth->acl_get('m_softdelete', $forum_id))
		{
			// If there are only soft deleted topics, we display a message why the option is not available
			$sql = 'SELECT topic_id
				FROM ' . TOPICS_TABLE . '
				WHERE ' . $db->sql_in_set('topic_id', $topic_ids) . '
					AND topic_visibility <> ' . ITEM_DELETED;
			$result = $db->sql_query_limit($sql, 1);
			$only_softdeleted = !$db->sql_fetchfield('topic_id');
			$db->sql_freeresult($result);
		}

		$template->assign_vars(array(
			'S_SHADOW_TOPICS'					=> $only_shadow,
			'S_SOFTDELETED'						=> $only_softdeleted,
			'S_TOPIC_MODE'						=> true,
			'S_ALLOWED_DELETE'					=> $auth->acl_get('m_delete', $forum_id),
			'S_ALLOWED_SOFTDELETE'				=> $auth->acl_get('m_softdelete', $forum_id),
			'DELETE_TOPIC_PERMANENTLY_EXPLAIN'	=> $user->lang('DELETE_TOPIC_PERMANENTLY', count($topic_ids)),
		));

		$l_confirm = (count($topic_ids) == 1) ? 'DELETE_TOPIC' : 'DELETE_TOPICS';
		if ($only_softdeleted)
		{
			$l_confirm .= '_PERMANENTLY';
			$s_hidden_fields['delete_permanent'] = '1';
		}
		else if ($only_shadow || !$auth->acl_get('m_softdelete', $forum_id))
		{
			$s_hidden_fields['delete_permanent'] = '1';
		}

		confirm_box(false, $l_confirm, build_hidden_fields($s_hidden_fields), 'confirm_delete_body.html');
	}

	$topic_id = $request->variable('t', 0);
	if (!$request->is_set('quickmod', \phpbb\request\request_interface::REQUEST))
	{
		$redirect = $request->variable('redirect', "index.$phpEx");
		$redirect = reapply_sid($redirect);
		$redirect_message = 'PAGE';
	}
	else if ($is_soft && $topic_id)
	{
		$redirect = append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=' . $topic_id);
		$redirect_message = 'TOPIC';
	}
	else
	{
		$redirect = append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id);
		$redirect_message = 'FORUM';
	}

	if (!$success_msg)
	{
		redirect($redirect);
	}
	else
	{
		meta_refresh(3, $redirect);
		trigger_error($user->lang[$success_msg] . '<br /><br />' . sprintf($user->lang['RETURN_' . $redirect_message], '<a href="' . $redirect . '">', '</a>'));
	}
}

/**
* Delete Posts
*/
function mcp_delete_post($post_ids, $is_soft = false, $soft_delete_reason = '', $action = 'delete_post')
{
	global $auth, $user, $db, $phpEx, $phpbb_root_path, $request, $phpbb_container, $phpbb_log;

	$check_permission = ($is_soft) ? 'm_softdelete' : 'm_delete';
	if (!phpbb_check_ids($post_ids, POSTS_TABLE, 'post_id', array($check_permission)))
	{
		return;
	}

	$redirect = $request->variable('redirect', build_url(array('action', 'quickmod')));
	$forum_id = $request->variable('f', 0);

	$s_hidden_fields = array(
		'post_id_list'	=> $post_ids,
		'f'				=> $forum_id,
		'action'		=> $action,
		'redirect'		=> $redirect,
	);
	$success_msg = '';

	if (confirm_box(true) && $is_soft)
	{
		$post_info = phpbb_get_post_data($post_ids);

		$topic_info = $approve_log = array();

		// Group the posts by topic_id
		foreach ($post_info as $post_id => $post_data)
		{
			if ($post_data['post_visibility'] != ITEM_APPROVED)
			{
				continue;
			}
			$topic_id = (int) $post_data['topic_id'];

			$topic_info[$topic_id]['posts'][] = (int) $post_id;
			$topic_info[$topic_id]['forum_id'] = (int) $post_data['forum_id'];

			if ($post_id == $post_data['topic_first_post_id'])
			{
				$topic_info[$topic_id]['first_post'] = true;
			}

			if ($post_id == $post_data['topic_last_post_id'])
			{
				$topic_info[$topic_id]['last_post'] = true;
			}

			$approve_log[] = array(
				'forum_id'		=> $post_data['forum_id'],
				'topic_id'		=> $post_data['topic_id'],
				'post_id'		=> $post_id,
				'post_subject'	=> $post_data['post_subject'],
				'poster_id'		=> $post_data['poster_id'],
				'post_username'	=> $post_data['post_username'],
				'username'		=> $post_data['username'],
			);
		}

		/* @var $phpbb_content_visibility \phpbb\content_visibility */
		$phpbb_content_visibility = $phpbb_container->get('content.visibility');
		foreach ($topic_info as $topic_id => $topic_data)
		{
			$phpbb_content_visibility->set_post_visibility(ITEM_DELETED, $topic_data['posts'], $topic_id, $topic_data['forum_id'], $user->data['user_id'], time(), $soft_delete_reason, isset($topic_data['first_post']), isset($topic_data['last_post']));
		}
		$affected_topics = count($topic_info);
		// None of the topics is really deleted, so a redirect won't hurt much.
		$deleted_topics = 0;

		$success_msg = (count($post_info) == 1) ? $user->lang['POST_DELETED_SUCCESS'] : $user->lang['POSTS_DELETED_SUCCESS'];

		foreach ($approve_log as $row)
		{
			$post_username = ($row['poster_id'] == ANONYMOUS && !empty($row['post_username'])) ? $row['post_username'] : $row['username'];
			$phpbb_log->add('mod', $user->data['user_id'], $user->ip, 'LOG_SOFTDELETE_POST', false, array(
				'forum_id' => $row['forum_id'],
				'topic_id' => $row['topic_id'],
				'post_id'  => $row['post_id'],
				$row['post_subject'],
				$post_username,
				$soft_delete_reason
			));
		}

		$topic_id = $request->variable('t', 0);

		// Return links
		$return_link = array();
		if ($affected_topics == 1 && $topic_id)
		{
			$return_link[] = sprintf($user->lang['RETURN_TOPIC'], '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id") . '">', '</a>');
		}
		$return_link[] = sprintf($user->lang['RETURN_FORUM'], '<a href="' . append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id) . '">', '</a>');

	}
	else if (confirm_box(true))
	{
		if (!function_exists('delete_posts'))
		{
			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		}

		// Count the number of topics that are affected
		// I did not use COUNT(DISTINCT ...) because I remember having problems
		// with it on older versions of MySQL -- Ashe

		$sql = 'SELECT DISTINCT topic_id
			FROM ' . POSTS_TABLE . '
			WHERE ' . $db->sql_in_set('post_id', $post_ids);
		$result = $db->sql_query($sql);

		$topic_id_list = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$topic_id_list[] = $row['topic_id'];
		}
		$affected_topics = count($topic_id_list);
		$db->sql_freeresult($result);

		$post_data = phpbb_get_post_data($post_ids);

		foreach ($post_data as $id => $row)
		{
			$post_username = ($row['poster_id'] == ANONYMOUS && !empty($row['post_username'])) ? $row['post_username'] : $row['username'];
			$phpbb_log->add('mod', $user->data['user_id'], $user->ip, 'LOG_DELETE_POST', false, array(
				'forum_id' => $row['forum_id'],
				'topic_id' => $row['topic_id'],
				'post_id'  => $row['post_id'],
				$row['post_subject'],
				$post_username,
				$soft_delete_reason
			));
		}

		// Now delete the posts, topics and forums are automatically resync'ed
		delete_posts('post_id', $post_ids);

		$sql = 'SELECT COUNT(topic_id) AS topics_left
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $db->sql_in_set('topic_id', $topic_id_list);
		$result = $db->sql_query_limit($sql, 1);

		$deleted_topics = ($row = $db->sql_fetchrow($result)) ? ($affected_topics - $row['topics_left']) : $affected_topics;
		$db->sql_freeresult($result);

		$topic_id = $request->variable('t', 0);

		// Return links
		$return_link = array();
		if ($affected_topics == 1 && !$deleted_topics && $topic_id)
		{
			$return_link[] = sprintf($user->lang['RETURN_TOPIC'], '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id") . '">', '</a>');
		}
		$return_link[] = sprintf($user->lang['RETURN_FORUM'], '<a href="' . append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id) . '">', '</a>');

		if (count($post_ids) == 1)
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
		global $template;

		$user->add_lang('posting');

		$only_softdeleted = false;
		if ($auth->acl_get('m_delete', $forum_id) && $auth->acl_get('m_softdelete', $forum_id))
		{
			// If there are only soft deleted posts, we display a message why the option is not available
			$sql = 'SELECT post_id
				FROM ' . POSTS_TABLE . '
				WHERE ' . $db->sql_in_set('post_id', $post_ids) . '
					AND post_visibility <> ' . ITEM_DELETED;
			$result = $db->sql_query_limit($sql, 1);
			$only_softdeleted = !$db->sql_fetchfield('post_id');
			$db->sql_freeresult($result);
		}

		$template->assign_vars(array(
			'S_SOFTDELETED'						=> $only_softdeleted,
			'S_ALLOWED_DELETE'					=> $auth->acl_get('m_delete', $forum_id),
			'S_ALLOWED_SOFTDELETE'				=> $auth->acl_get('m_softdelete', $forum_id),
			'DELETE_POST_PERMANENTLY_EXPLAIN'	=> $user->lang('DELETE_POST_PERMANENTLY', count($post_ids)),
		));

		$l_confirm = (count($post_ids) == 1) ? 'DELETE_POST' : 'DELETE_POSTS';
		if ($only_softdeleted)
		{
			$l_confirm .= '_PERMANENTLY';
			$s_hidden_fields['delete_permanent'] = '1';
		}
		else if (!$auth->acl_get('m_softdelete', $forum_id))
		{
			$s_hidden_fields['delete_permanent'] = '1';
		}

		confirm_box(false, $l_confirm, build_hidden_fields($s_hidden_fields), 'confirm_delete_body.html');
	}

	$redirect = $request->variable('redirect', "index.$phpEx");
	$redirect = reapply_sid($redirect);

	if (!$success_msg)
	{
		redirect($redirect);
	}
	else
	{
		if ($affected_topics != 1 || $deleted_topics || !$topic_id)
		{
			$redirect = append_sid("{$phpbb_root_path}mcp.$phpEx", "f=$forum_id&i=main&mode=forum_view", false);
		}

		meta_refresh(3, $redirect);
		trigger_error($success_msg . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>') . '<br /><br />' . implode('<br /><br />', $return_link));
	}
}

/**
* Fork Topic
*/
function mcp_fork_topic($topic_ids)
{
	global $auth, $user, $db, $template, $config;
	global $phpEx, $phpbb_root_path, $phpbb_log, $request, $phpbb_dispatcher;

	if (!phpbb_check_ids($topic_ids, TOPICS_TABLE, 'topic_id', array('m_')))
	{
		return;
	}

	$to_forum_id = $request->variable('to_forum_id', 0);
	$forum_id = $request->variable('f', 0);
	$redirect = $request->variable('redirect', build_url(array('action', 'quickmod')));
	$additional_msg = $success_msg = '';
	$counter = array();

	$s_hidden_fields = build_hidden_fields(array(
		'topic_id_list'	=> $topic_ids,
		'f'				=> $forum_id,
		'action'		=> 'fork',
		'redirect'		=> $redirect)
	);

	if ($to_forum_id)
	{
		$forum_data = phpbb_get_forum_data($to_forum_id, 'f_post');

		if (!count($topic_ids))
		{
			$additional_msg = $user->lang['NO_TOPIC_SELECTED'];
		}
		else if (!count($forum_data))
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
	else if (isset($_POST['confirm']))
	{
		$additional_msg = $user->lang['FORUM_NOT_EXIST'];
	}

	if ($additional_msg)
	{
		$request->overwrite('confirm', null, \phpbb\request\request_interface::POST);
		$request->overwrite('confirm_key', null);
	}

	if (confirm_box(true))
	{
		$topic_data = phpbb_get_topic_data($topic_ids, 'f_post');

		$total_topics = $total_topics_unapproved = $total_topics_softdeleted = 0;
		$total_posts = $total_posts_unapproved = $total_posts_softdeleted = 0;
		$new_topic_id_list = array();

		foreach ($topic_data as $topic_id => $topic_row)
		{
			if (!isset($search_type) && $topic_row['enable_indexing'])
			{
				// Select the search method and do some additional checks to ensure it can actually be utilised
				$search_type = $config['search_type'];

				if (!class_exists($search_type))
				{
					trigger_error('NO_SUCH_SEARCH_MODULE');
				}

				$error = false;
				$search = new $search_type($error, $phpbb_root_path, $phpEx, $auth, $config, $db, $user, $phpbb_dispatcher);
				$search_mode = 'post';

				if ($error)
				{
					trigger_error($error);
				}
			}
			else if (!isset($search_type) && !$topic_row['enable_indexing'])
			{
				$search_type = false;
			}

			$sql_ary = array(
				'forum_id'					=> (int) $to_forum_id,
				'icon_id'					=> (int) $topic_row['icon_id'],
				'topic_attachment'			=> (int) $topic_row['topic_attachment'],
				'topic_visibility'			=> (int) $topic_row['topic_visibility'],
				'topic_reported'			=> 0,
				'topic_title'				=> (string) $topic_row['topic_title'],
				'topic_poster'				=> (int) $topic_row['topic_poster'],
				'topic_time'				=> (int) $topic_row['topic_time'],
				'topic_posts_approved'		=> (int) $topic_row['topic_posts_approved'],
				'topic_posts_unapproved'	=> (int) $topic_row['topic_posts_unapproved'],
				'topic_posts_softdeleted'	=> (int) $topic_row['topic_posts_softdeleted'],
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
				'poll_length'				=> (int) $topic_row['poll_length'],
				'poll_max_options'			=> (int) $topic_row['poll_max_options'],
				'poll_vote_change'			=> (int) $topic_row['poll_vote_change'],
			);

			/**
			* Perform actions before forked topic is created.
			*
			* @event core.mcp_main_modify_fork_sql
			* @var	array	sql_ary		SQL array to be used by $db->sql_build_array
			* @var	array	topic_row	Topic data
			* @since 3.1.11-RC1
			* @changed 3.1.11-RC1 Added variable: topic_row
			*/
			$vars = array(
				'sql_ary',
				'topic_row',
			);
			extract($phpbb_dispatcher->trigger_event('core.mcp_main_modify_fork_sql', compact($vars)));

			$db->sql_query('INSERT INTO ' . TOPICS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
			$new_topic_id = $db->sql_nextid();
			$new_topic_id_list[$topic_id] = $new_topic_id;

			switch ($topic_row['topic_visibility'])
			{
				case ITEM_APPROVED:
					$total_topics++;
				break;
				case ITEM_UNAPPROVED:
				case ITEM_REAPPROVE:
					$total_topics_unapproved++;
				break;
				case ITEM_DELETED:
					$total_topics_softdeleted++;
				break;
			}

			if ($topic_row['poll_start'])
			{
				$sql = 'SELECT *
					FROM ' . POLL_OPTIONS_TABLE . "
					WHERE topic_id = $topic_id";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$sql_ary = array(
						'poll_option_id'	=> (int) $row['poll_option_id'],
						'topic_id'			=> (int) $new_topic_id,
						'poll_option_text'	=> (string) $row['poll_option_text'],
						'poll_option_total'	=> 0
					);

					$db->sql_query('INSERT INTO ' . POLL_OPTIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
				}
				$db->sql_freeresult($result);
			}

			$sql = 'SELECT *
				FROM ' . POSTS_TABLE . "
				WHERE topic_id = $topic_id
				ORDER BY post_time ASC, post_id ASC";
			$result = $db->sql_query($sql);

			$post_rows = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$post_rows[] = $row;
			}
			$db->sql_freeresult($result);

			if (!count($post_rows))
			{
				continue;
			}

			foreach ($post_rows as $row)
			{
				$sql_ary = array(
					'topic_id'			=> (int) $new_topic_id,
					'forum_id'			=> (int) $to_forum_id,
					'poster_id'			=> (int) $row['poster_id'],
					'icon_id'			=> (int) $row['icon_id'],
					'poster_ip'			=> (string) $row['poster_ip'],
					'post_time'			=> (int) $row['post_time'],
					'post_visibility'	=> (int) $row['post_visibility'],
					'post_reported'		=> 0,
					'enable_bbcode'		=> (int) $row['enable_bbcode'],
					'enable_smilies'	=> (int) $row['enable_smilies'],
					'enable_magic_url'	=> (int) $row['enable_magic_url'],
					'enable_sig'		=> (int) $row['enable_sig'],
					'post_username'		=> (string) $row['post_username'],
					'post_subject'		=> (string) $row['post_subject'],
					'post_text'			=> (string) $row['post_text'],
					'post_edit_reason'	=> (string) $row['post_edit_reason'],
					'post_edit_user'	=> (int) $row['post_edit_user'],
					'post_checksum'		=> (string) $row['post_checksum'],
					'post_attachment'	=> (int) $row['post_attachment'],
					'bbcode_bitfield'	=> $row['bbcode_bitfield'],
					'bbcode_uid'		=> (string) $row['bbcode_uid'],
					'post_edit_time'	=> (int) $row['post_edit_time'],
					'post_edit_count'	=> (int) $row['post_edit_count'],
					'post_edit_locked'	=> (int) $row['post_edit_locked'],
					'post_postcount'	=> $row['post_postcount'],
				);
				// Adjust post count only if the post can be incremented to the user counter
				if ($row['post_postcount'])
				{
					if (isset($counter[$row['poster_id']]))
					{
						++$counter[$row['poster_id']];
					}
					else
					{
						$counter[$row['poster_id']] = 1;
					}
				}
				$db->sql_query('INSERT INTO ' . POSTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
				$new_post_id = $db->sql_nextid();

				switch ($row['post_visibility'])
				{
					case ITEM_APPROVED:
						$total_posts++;
					break;
					case ITEM_UNAPPROVED:
					case ITEM_REAPPROVE:
						$total_posts_unapproved++;
					break;
					case ITEM_DELETED:
						$total_posts_softdeleted++;
					break;
				}

				// Copy whether the topic is dotted
				markread('post', $to_forum_id, $new_topic_id, 0, $row['poster_id']);

				if (!empty($search_type))
				{
					$search->index($search_mode, $new_post_id, $sql_ary['post_text'], $sql_ary['post_subject'], $sql_ary['poster_id'], ($topic_row['topic_type'] == POST_GLOBAL) ? 0 : $to_forum_id);
					$search_mode = 'reply'; // After one we index replies
				}

				// Copy Attachments
				if ($row['post_attachment'])
				{
					$sql = 'SELECT * FROM ' . ATTACHMENTS_TABLE . "
						WHERE post_msg_id = {$row['post_id']}
							AND topic_id = $topic_id
							AND in_message = 0";
					$result = $db->sql_query($sql);

					$sql_ary = array();
					while ($attach_row = $db->sql_fetchrow($result))
					{
						$sql_ary[] = array(
							'post_msg_id'		=> (int) $new_post_id,
							'topic_id'			=> (int) $new_topic_id,
							'in_message'		=> 0,
							'is_orphan'			=> (int) $attach_row['is_orphan'],
							'poster_id'			=> (int) $attach_row['poster_id'],
							'physical_filename'	=> (string) utf8_basename($attach_row['physical_filename']),
							'real_filename'		=> (string) utf8_basename($attach_row['real_filename']),
							'download_count'	=> (int) $attach_row['download_count'],
							'attach_comment'	=> (string) $attach_row['attach_comment'],
							'extension'			=> (string) $attach_row['extension'],
							'mimetype'			=> (string) $attach_row['mimetype'],
							'filesize'			=> (int) $attach_row['filesize'],
							'filetime'			=> (int) $attach_row['filetime'],
							'thumbnail'			=> (int) $attach_row['thumbnail']
						);
					}
					$db->sql_freeresult($result);

					if (count($sql_ary))
					{
						$db->sql_multi_insert(ATTACHMENTS_TABLE, $sql_ary);
					}
				}
			}

			// Copy topic subscriptions to new topic
			$sql = 'SELECT user_id, notify_status
				FROM ' . TOPICS_WATCH_TABLE . '
				WHERE topic_id = ' . $topic_id;
			$result = $db->sql_query($sql);

			$sql_ary = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$sql_ary[] = array(
					'topic_id'		=> (int) $new_topic_id,
					'user_id'		=> (int) $row['user_id'],
					'notify_status'	=> (int) $row['notify_status'],
				);
			}
			$db->sql_freeresult($result);

			if (count($sql_ary))
			{
				$db->sql_multi_insert(TOPICS_WATCH_TABLE, $sql_ary);
			}

			// Copy bookmarks to new topic
			$sql = 'SELECT user_id
				FROM ' . BOOKMARKS_TABLE . '
				WHERE topic_id = ' . $topic_id;
			$result = $db->sql_query($sql);

			$sql_ary = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$sql_ary[] = array(
					'topic_id'		=> (int) $new_topic_id,
					'user_id'		=> (int) $row['user_id'],
				);
			}
			$db->sql_freeresult($result);

			if (count($sql_ary))
			{
				$db->sql_multi_insert(BOOKMARKS_TABLE, $sql_ary);
			}
		}

		// Sync new topics, parent forums and board stats
		$sql = 'UPDATE ' . FORUMS_TABLE . '
			SET forum_posts_approved = forum_posts_approved + ' . $total_posts . ',
				forum_posts_unapproved = forum_posts_unapproved + ' . $total_posts_unapproved . ',
				forum_posts_softdeleted = forum_posts_softdeleted + ' . $total_posts_softdeleted . ',
				forum_topics_approved = forum_topics_approved + ' . $total_topics . ',
				forum_topics_unapproved = forum_topics_unapproved + ' . $total_topics_unapproved . ',
				forum_topics_softdeleted = forum_topics_softdeleted + ' . $total_topics_softdeleted . '
			WHERE forum_id = ' . $to_forum_id;
		$db->sql_query($sql);

		if (!empty($counter))
		{
			// Do only one query per user and not a query per post.
			foreach ($counter as $user_id => $count)
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_posts = user_posts + ' . (int) $count . '
					WHERE user_id = ' . (int) $user_id;
				$db->sql_query($sql);
			}
		}

		sync('topic', 'topic_id', $new_topic_id_list);
		sync('forum', 'forum_id', $to_forum_id);

		$config->increment('num_topics', count($new_topic_id_list), false);
		$config->increment('num_posts', $total_posts, false);

		foreach ($new_topic_id_list as $topic_id => $new_topic_id)
		{
			$phpbb_log->add('mod', $user->data['user_id'], $user->ip, 'LOG_FORK', false, array(
				'forum_id' => $to_forum_id,
				'topic_id' => $new_topic_id,
				$topic_row['forum_name']
			));
		}

		$success_msg = (count($topic_ids) == 1) ? 'TOPIC_FORKED_SUCCESS' : 'TOPICS_FORKED_SUCCESS';
	}
	else
	{
		$template->assign_vars(array(
			'S_FORUM_SELECT'		=> make_forum_select($to_forum_id, false, false, true, true, true),
			'S_CAN_LEAVE_SHADOW'	=> false,
			'ADDITIONAL_MSG'		=> $additional_msg)
		);

		confirm_box(false, 'FORK_TOPIC' . ((count($topic_ids) == 1) ? '' : 'S'), $s_hidden_fields, 'mcp_move.html');
	}

	$redirect = $request->variable('redirect', "index.$phpEx");
	$redirect = reapply_sid($redirect);

	if (!$success_msg)
	{
		redirect($redirect);
	}
	else
	{
		$redirect_url = append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id);
		meta_refresh(3, $redirect_url);
		$return_link = sprintf($user->lang['RETURN_FORUM'], '<a href="' . $redirect_url . '">', '</a>');

		if ($forum_id != $to_forum_id)
		{
			$return_link .= '<br /><br />' . sprintf($user->lang['RETURN_NEW_FORUM'], '<a href="' . append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $to_forum_id) . '">', '</a>');
		}

		trigger_error($user->lang[$success_msg] . '<br /><br />' . $return_link);
	}
}
