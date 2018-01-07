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

class acp_prune
{
	var $u_action;

	function main($id, $mode)
	{
		global $user, $phpEx, $phpbb_root_path;

		$user->add_lang('acp/prune');

		if (!function_exists('user_active_flip'))
		{
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		}

		switch ($mode)
		{
			case 'forums':
				$this->tpl_name = 'acp_prune_forums';
				$this->page_title = 'ACP_PRUNE_FORUMS';
				$this->prune_forums($id, $mode);
			break;

			case 'users':
				$this->tpl_name = 'acp_prune_users';
				$this->page_title = 'ACP_PRUNE_USERS';
				$this->prune_users($id, $mode);
			break;
		}
	}

	/**
	* Prune forums
	*/
	function prune_forums($id, $mode)
	{
		global $db, $user, $auth, $template, $phpbb_log, $request, $phpbb_dispatcher;

		$all_forums = $request->variable('all_forums', 0);
		$forum_id = $request->variable('f', array(0));
		$submit = (isset($_POST['submit'])) ? true : false;

		if ($all_forums)
		{
			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				ORDER BY left_id';
			$result = $db->sql_query($sql);

			$forum_id = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$forum_id[] = $row['forum_id'];
			}
			$db->sql_freeresult($result);
		}

		if ($submit)
		{
			if (confirm_box(true))
			{
				$prune_posted = $request->variable('prune_days', 0);
				$prune_viewed = $request->variable('prune_vieweddays', 0);
				$prune_all = (!$prune_posted && !$prune_viewed) ? true : false;

				$prune_flags = 0;
				$prune_flags += ($request->variable('prune_old_polls', 0)) ? 2 : 0;
				$prune_flags += ($request->variable('prune_announce', 0)) ? 4 : 0;
				$prune_flags += ($request->variable('prune_sticky', 0)) ? 8 : 0;

				// Convert days to seconds for timestamp functions...
				$prunedate_posted = time() - ($prune_posted * 86400);
				$prunedate_viewed = time() - ($prune_viewed * 86400);

				$template->assign_vars(array(
					'S_PRUNED'		=> true)
				);

				$sql_forum = (count($forum_id)) ? ' AND ' . $db->sql_in_set('forum_id', $forum_id) : '';

				// Get a list of forum's or the data for the forum that we are pruning.
				$sql = 'SELECT forum_id, forum_name
					FROM ' . FORUMS_TABLE . '
					WHERE forum_type = ' . FORUM_POST . "
						$sql_forum
					ORDER BY left_id ASC";
				$result = $db->sql_query($sql);

				if ($row = $db->sql_fetchrow($result))
				{
					$prune_ids = array();
					$p_result['topics'] = 0;
					$p_result['posts'] = 0;
					$log_data = '';

					do
					{
						if (!$auth->acl_get('f_list', $row['forum_id']))
						{
							continue;
						}

						if ($prune_all)
						{
							$p_result = prune($row['forum_id'], 'posted', time(), $prune_flags, false);
						}
						else
						{
							if ($prune_posted)
							{
								$return = prune($row['forum_id'], 'posted', $prunedate_posted, $prune_flags, false);
								$p_result['topics'] += $return['topics'];
								$p_result['posts'] += $return['posts'];
							}

							if ($prune_viewed)
							{
								$return = prune($row['forum_id'], 'viewed', $prunedate_viewed, $prune_flags, false);
								$p_result['topics'] += $return['topics'];
								$p_result['posts'] += $return['posts'];
							}
						}

						$prune_ids[] = $row['forum_id'];

						$template->assign_block_vars('pruned', array(
							'FORUM_NAME'	=> $row['forum_name'],
							'NUM_TOPICS'	=> $p_result['topics'],
							'NUM_POSTS'		=> $p_result['posts'])
						);

						$log_data .= (($log_data != '') ? ', ' : '') . $row['forum_name'];
					}
					while ($row = $db->sql_fetchrow($result));

					// Sync all pruned forums at once
					sync('forum', 'forum_id', $prune_ids, true, true);

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_PRUNE', false, array($log_data));
				}
				$db->sql_freeresult($result);

				return;
			}
			else
			{
				$hidden_fields = array(
					'i'				=> $id,
					'mode'			=> $mode,
					'submit'		=> 1,
					'all_forums'	=> $all_forums,
					'f'				=> $forum_id,

					'prune_days'		=> $request->variable('prune_days', 0),
					'prune_vieweddays'	=> $request->variable('prune_vieweddays', 0),
					'prune_old_polls'	=> $request->variable('prune_old_polls', 0),
					'prune_announce'	=> $request->variable('prune_announce', 0),
					'prune_sticky'		=> $request->variable('prune_sticky', 0),
				);

				/**
				 * Use this event to pass data from the prune form to the confirmation screen
				 *
				 * @event core.prune_forums_settings_confirm
				 * @var array	hidden_fields	Hidden fields that are passed through the confirm screen
				 * @since 3.2.2-RC1
				 */
				$vars = array('hidden_fields');
				extract($phpbb_dispatcher->trigger_event('core.prune_forums_settings_confirm', compact($vars)));

				confirm_box(false, $user->lang['PRUNE_FORUM_CONFIRM'], build_hidden_fields($hidden_fields));
			}
		}

		// If they haven't selected a forum for pruning yet then
		// display a select box to use for pruning.
		if (!count($forum_id))
		{
			$template->assign_vars(array(
				'U_ACTION'			=> $this->u_action,
				'S_SELECT_FORUM'	=> true,
				'S_FORUM_OPTIONS'	=> make_forum_select(false, false, false))
			);
		}
		else
		{
			$sql = 'SELECT forum_id, forum_name
				FROM ' . FORUMS_TABLE . '
				WHERE ' . $db->sql_in_set('forum_id', $forum_id);
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);

			if (!$row)
			{
				$db->sql_freeresult($result);
				trigger_error($user->lang['NO_FORUM'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$forum_list = $s_hidden_fields = '';
			do
			{
				$forum_list .= (($forum_list != '') ? ', ' : '') . '<b>' . $row['forum_name'] . '</b>';
				$s_hidden_fields .= '<input type="hidden" name="f[]" value="' . $row['forum_id'] . '" />';
			}
			while ($row = $db->sql_fetchrow($result));

			$db->sql_freeresult($result);

			$l_selected_forums = (count($forum_id) == 1) ? 'SELECTED_FORUM' : 'SELECTED_FORUMS';

			$template_data = array(
				'L_SELECTED_FORUMS'		=> $user->lang[$l_selected_forums],
				'U_ACTION'				=> $this->u_action,
				'U_BACK'				=> $this->u_action,
				'FORUM_LIST'			=> $forum_list,
				'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
			);

			/**
			 * Event to add/modify prune forums settings template data
			 *
			 * @event core.prune_forums_settings_template_data
			 * @var array	template_data	Array with form template data
			 * @since 3.2.2-RC1
			 */
			$vars = array('template_data');
			extract($phpbb_dispatcher->trigger_event('core.prune_forums_settings_template_data', compact($vars)));

			$template->assign_vars($template_data);
		}
	}

	/**
	* Prune users
	*/
	function prune_users($id, $mode)
	{
		global $db, $user, $auth, $template, $phpbb_log, $request;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $phpbb_container;

		/** @var \phpbb\group\helper $group_helper */
		$group_helper = $phpbb_container->get('group_helper');

		$user->add_lang('memberlist');

		$prune = (isset($_POST['prune'])) ? true : false;

		if ($prune)
		{
			$action = $request->variable('action', 'deactivate');
			$deleteposts = $request->variable('deleteposts', 0);

			if (confirm_box(true))
			{
				$user_ids = $usernames = array();

				$this->get_prune_users($user_ids, $usernames);
				if (count($user_ids))
				{
					if ($action == 'deactivate')
					{
						user_active_flip('deactivate', $user_ids);
						$l_log = 'LOG_PRUNE_USER_DEAC';
					}
					else if ($action == 'delete')
					{
						if ($deleteposts)
						{
							user_delete('remove', $user_ids);

							$l_log = 'LOG_PRUNE_USER_DEL_DEL';
						}
						else
						{
							user_delete('retain', $user_ids, true);

							$l_log = 'LOG_PRUNE_USER_DEL_ANON';
						}
					}

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, $l_log, false, array(implode(', ', $usernames)));
					$msg = $user->lang['USER_' . strtoupper($action) . '_SUCCESS'];
				}
				else
				{
					$msg = $user->lang['USER_PRUNE_FAILURE'];
				}

				trigger_error($msg . adm_back_link($this->u_action));
			}
			else
			{
				// We list the users which will be pruned...
				$user_ids = $usernames = array();
				$this->get_prune_users($user_ids, $usernames);

				if (!count($user_ids))
				{
					trigger_error($user->lang['USER_PRUNE_FAILURE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				// Assign to template
				foreach ($user_ids as $user_id)
				{
					$template->assign_block_vars('users', array(
						'USERNAME'			=> $usernames[$user_id],
						'USER_ID'           => $user_id,
						'U_PROFILE'			=> get_username_string('profile', $user_id, $usernames[$user_id]),
						'U_USER_ADMIN'		=> ($auth->acl_get('a_user')) ? append_sid("{$phpbb_admin_path}index.$phpEx", 'i=users&amp;mode=overview&amp;u=' . $user_id, true, $user->session_id) : '',
					));
				}

				$template->assign_vars(array(
					'S_DEACTIVATE'		=> ($action == 'deactivate') ? true : false,
					'S_DELETE'			=> ($action == 'delete') ? true : false,
				));

				confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
					'i'				=> $id,
					'mode'			=> $mode,
					'prune'			=> 1,

					'deleteposts'	=> $request->variable('deleteposts', 0),
					'action'		=> $request->variable('action', ''),
				)), 'confirm_body_prune.html');
			}
		}

		$find_count = array('lt' => $user->lang['LESS_THAN'], 'eq' => $user->lang['EQUAL_TO'], 'gt' => $user->lang['MORE_THAN']);
		$s_find_count = '';

		foreach ($find_count as $key => $value)
		{
			$selected = ($key == 'eq') ? ' selected="selected"' : '';
			$s_find_count .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}

		$find_time = array('lt' => $user->lang['BEFORE'], 'gt' => $user->lang['AFTER']);
		$s_find_active_time = '';
		foreach ($find_time as $key => $value)
		{
			$s_find_active_time .= '<option value="' . $key . '">' . $value . '</option>';
		}

		$sql = 'SELECT group_id, group_name
			FROM ' . GROUPS_TABLE . '
			WHERE group_type <> ' . GROUP_SPECIAL . '
			ORDER BY group_name ASC';
		$result = $db->sql_query($sql);

		$s_group_list = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$s_group_list .= '<option value="' . $row['group_id'] . '">' . $group_helper->get_name($row['group_name']) . '</option>';
		}
		$db->sql_freeresult($result);

		if ($s_group_list)
		{
			// Only prepend the "All groups" option if there are groups,
			// otherwise we don't want to display this option at all.
			$s_group_list = '<option value="0">' . $user->lang['PRUNE_USERS_GROUP_NONE'] . '</option>' . $s_group_list;
		}

		$template->assign_vars(array(
			'U_ACTION'			=> $this->u_action,
			'S_ACTIVE_OPTIONS'	=> $s_find_active_time,
			'S_GROUP_LIST'		=> $s_group_list,
			'S_COUNT_OPTIONS'	=> $s_find_count,
			'U_FIND_USERNAME'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=acp_prune&amp;field=users'),
		));
	}

	/**
	* Get user_ids/usernames from those being pruned
	*/
	function get_prune_users(&$user_ids, &$usernames)
	{
		global $user, $db, $request;

		$users_by_name = $request->variable('users', '', true);
		$users_by_id = $request->variable('user_ids', array(0));
		$group_id = $request->variable('group_id', 0);
		$posts_on_queue = (trim($request->variable('posts_on_queue', '')) === '') ? false : $request->variable('posts_on_queue', 0);

		if ($users_by_name)
		{
			$users = explode("\n", $users_by_name);
			$where_sql = ' AND ' . $db->sql_in_set('username_clean', array_map('utf8_clean_string', $users));
		}
		else if (!empty($users_by_id))
		{
			$user_ids = $users_by_id;
			user_get_id_name($user_ids, $usernames);

			$where_sql = ' AND ' . $db->sql_in_set('user_id', $user_ids);
		}
		else
		{
			$username = $request->variable('username', '', true);
			$email = $request->variable('email', '');

			$active_select = $request->variable('active_select', 'lt');
			$count_select = $request->variable('count_select', 'eq');
			$queue_select = $request->variable('queue_select', 'gt');
			$joined_before = $request->variable('joined_before', '');
			$joined_after = $request->variable('joined_after', '');
			$active = $request->variable('active', '');

			$count = ($request->variable('count', '') === '') ? false : $request->variable('count', 0);

			$active = ($active) ? explode('-', $active) : array();
			$joined_before = ($joined_before) ? explode('-', $joined_before) : array();
			$joined_after = ($joined_after) ? explode('-', $joined_after) : array();

			// calculate the conditions required by the join time criteria
			$joined_sql = '';
			if (!empty($joined_before) && !empty($joined_after))
			{
				// if the two entered dates are equal, we need to adjust
				// so that our time range is a full day instead of 1 second
				if ($joined_after == $joined_before)
				{
					$joined_after[2] += 1;
				}

				$joined_sql = ' AND user_regdate BETWEEN ' . gmmktime(0, 0, 0, (int) $joined_after[1], (int) $joined_after[2], (int) $joined_after[0]) .
					' AND ' . gmmktime(0, 0, 0, (int) $joined_before[1], (int) $joined_before[2], (int) $joined_before[0]);
			}
			else if (empty($joined_before) && !empty($joined_after))
			{
				$joined_sql = ' AND user_regdate > ' . gmmktime(0, 0, 0, (int) $joined_after[1], (int) $joined_after[2], (int) $joined_after[0]);
			}
			else if (empty($joined_after) && !empty($joined_before))
			{
				$joined_sql = ' AND user_regdate < ' . gmmktime(0, 0, 0, (int) $joined_before[1], (int) $joined_before[2], (int) $joined_before[0]);
			}
			// implicit else when both arrays are empty do nothing

			if ((count($active) && count($active) != 3) || (count($joined_before) && count($joined_before) != 3) || (count($joined_after) && count($joined_after) != 3))
			{
				trigger_error($user->lang['WRONG_ACTIVE_JOINED_DATE'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$key_match = array('lt' => '<', 'gt' => '>', 'eq' => '=');

			$where_sql = '';
			$where_sql .= ($username) ? ' AND username_clean ' . $db->sql_like_expression(str_replace('*', $db->get_any_char(), utf8_clean_string($username))) : '';
			$where_sql .= ($email) ? ' AND user_email ' . $db->sql_like_expression(str_replace('*', $db->get_any_char(), $email)) . ' ' : '';
			$where_sql .= $joined_sql;
			$where_sql .= ($count !== false) ? " AND user_posts " . $key_match[$count_select] . ' ' . (int) $count . ' ' : '';

			// First handle pruning of users who never logged in, last active date is 0000-00-00
			if (count($active) && (int) $active[0] == 0 && (int) $active[1] == 0 && (int) $active[2] == 0)
			{
				$where_sql .= ' AND user_lastvisit = 0';
			}
			else if (count($active) && $active_select != 'lt')
			{
				$where_sql .= ' AND user_lastvisit ' . $key_match[$active_select] . ' ' . gmmktime(0, 0, 0, (int) $active[1], (int) $active[2], (int) $active[0]);
			}
			else if (count($active))
			{
				$where_sql .= ' AND (user_lastvisit > 0 AND user_lastvisit < ' . gmmktime(0, 0, 0, (int) $active[1], (int) $active[2], (int) $active[0]) . ')';
			}
		}

		// If no search criteria were provided, go no further.
		if (!$where_sql && !$group_id && $posts_on_queue === false)
		{
			return;
		}

		// Get bot ids
		$sql = 'SELECT user_id
			FROM ' . BOTS_TABLE;
		$result = $db->sql_query($sql);

		$bot_ids = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$bot_ids[] = $row['user_id'];
		}
		$db->sql_freeresult($result);

		// Protect the admin, do not prune if no options are given...
		if ($where_sql)
		{
			// Do not prune founder members
			$sql = 'SELECT user_id, username
				FROM ' . USERS_TABLE . '
				WHERE user_id <> ' . ANONYMOUS . '
					AND user_type <> ' . USER_FOUNDER . "
				$where_sql";
			$result = $db->sql_query($sql);

			$user_ids = $usernames = array();

			while ($row = $db->sql_fetchrow($result))
			{
				// Do not prune bots and the user currently pruning.
				if ($row['user_id'] != $user->data['user_id'] && !in_array($row['user_id'], $bot_ids))
				{
					$user_ids[] = $row['user_id'];
					$usernames[$row['user_id']] = $row['username'];
				}
			}
			$db->sql_freeresult($result);
		}

		if ($group_id)
		{
			$sql = 'SELECT u.user_id, u.username
				FROM ' . USER_GROUP_TABLE . ' ug, ' . USERS_TABLE . ' u
				WHERE ug.group_id = ' . (int) $group_id . '
					AND ug.user_id <> ' . ANONYMOUS . '
					AND u.user_type <> ' . USER_FOUNDER . '
					AND ug.user_pending = 0
					AND u.user_id = ug.user_id
					' . (!empty($user_ids) ? ' AND ' . $db->sql_in_set('ug.user_id', $user_ids) : '');
			$result = $db->sql_query($sql);

			// we're performing an intersection operation, so all the relevant users
			// come from this most recent query (which was limited to the results of the
			// previous query)
			$user_ids = $usernames = array();
			while ($row = $db->sql_fetchrow($result))
			{
				// Do not prune bots and the user currently pruning.
				if ($row['user_id'] != $user->data['user_id'] && !in_array($row['user_id'], $bot_ids))
				{
					$user_ids[] = $row['user_id'];
					$usernames[$row['user_id']] = $row['username'];
				}
			}
			$db->sql_freeresult($result);
		}

		if ($posts_on_queue !== false)
		{
			$sql = 'SELECT u.user_id, u.username, COUNT(p.post_id) AS queue_posts
				FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
				WHERE u.user_id <> ' . ANONYMOUS . '
					AND u.user_type <> ' . USER_FOUNDER . '
					AND ' . $db->sql_in_set('p.post_visibility', array(ITEM_UNAPPROVED, ITEM_REAPPROVE)) . '
					AND u.user_id = p.poster_id
					' . (!empty($user_ids) ? ' AND ' . $db->sql_in_set('p.poster_id', $user_ids) : '') . '
				GROUP BY p.poster_id
				HAVING queue_posts ' . $key_match[$queue_select] . ' ' . $posts_on_queue;
			$result = $db->sql_query($sql);

			// same intersection logic as the above group ID portion
			$user_ids = $usernames = array();
			while ($row = $db->sql_fetchrow($result))
			{
				// Do not prune bots and the user currently pruning.
				if ($row['user_id'] != $user->data['user_id'] && !in_array($row['user_id'], $bot_ids))
				{
					$user_ids[] = $row['user_id'];
					$usernames[$row['user_id']] = $row['username'];
				}
			}
			$db->sql_freeresult($result);
		}
	}
}
