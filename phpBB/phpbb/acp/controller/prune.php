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

namespace phpbb\acp\controller;

class prune
{
	var $u_action;

	public function main($id, $mode)
	{
		$this->language->add_lang('acp/prune');

		if (!function_exists('user_active_flip'))
		{
			include($this->root_path . 'includes/functions_user.' . $this->php_ext);
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
		$all_forums = $this->request->variable('all_forums', 0);
		$forum_id = $this->request->variable('f', [0]);
		$submit = ($this->request->is_set_post('submit')) ? true : false;

		if ($all_forums)
		{
			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				ORDER BY left_id';
			$result = $this->db->sql_query($sql);

			$forum_id = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$forum_id[] = $row['forum_id'];
			}
			$this->db->sql_freeresult($result);
		}

		if ($submit)
		{
			if (confirm_box(true))
			{
				$prune_posted = $this->request->variable('prune_days', 0);
				$prune_viewed = $this->request->variable('prune_vieweddays', 0);
				$prune_all = (!$prune_posted && !$prune_viewed) ? true : false;

				$prune_flags = 0;
				$prune_flags += ($this->request->variable('prune_old_polls', 0)) ? 2 : 0;
				$prune_flags += ($this->request->variable('prune_announce', 0)) ? 4 : 0;
				$prune_flags += ($this->request->variable('prune_sticky', 0)) ? 8 : 0;

				// Convert days to seconds for timestamp functions...
				$prunedate_posted = time() - ($prune_posted * 86400);
				$prunedate_viewed = time() - ($prune_viewed * 86400);

				$this->template->assign_vars([
					'S_PRUNED'		=> true]
				);

				$sql_forum = (count($forum_id)) ? ' AND ' . $this->db->sql_in_set('forum_id', $forum_id) : '';

				// Get a list of forum's or the data for the forum that we are pruning.
				$sql = 'SELECT forum_id, forum_name
					FROM ' . FORUMS_TABLE . '
					WHERE forum_type = ' . FORUM_POST . "
						$sql_forum
					ORDER BY left_id ASC";
				$result = $this->db->sql_query($sql);

				if ($row = $this->db->sql_fetchrow($result))
				{
					$prune_ids = [];
					$p_result['topics'] = 0;
					$p_result['posts'] = 0;
					$log_data = '';

					do
					{
						if (!$this->auth->acl_get('f_list', $row['forum_id']))
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

						$this->template->assign_block_vars('pruned', [
							'FORUM_NAME'	=> $row['forum_name'],
							'NUM_TOPICS'	=> $p_result['topics'],
							'NUM_POSTS'		=> $p_result['posts']]
						);

						$log_data .= (($log_data != '') ? ', ' : '') . $row['forum_name'];
					}
					while ($row = $this->db->sql_fetchrow($result));

					// Sync all pruned forums at once
					sync('forum', 'forum_id', $prune_ids, true, true);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PRUNE', false, [$log_data]);
				}
				$this->db->sql_freeresult($result);

				return;
			}
			else
			{
				$hidden_fields = [
					'i'				=> $id,
					'mode'			=> $mode,
					'submit'		=> 1,
					'all_forums'	=> $all_forums,
					'f'				=> $forum_id,

					'prune_days'		=> $this->request->variable('prune_days', 0),
					'prune_vieweddays'	=> $this->request->variable('prune_vieweddays', 0),
					'prune_old_polls'	=> $this->request->variable('prune_old_polls', 0),
					'prune_announce'	=> $this->request->variable('prune_announce', 0),
					'prune_sticky'		=> $this->request->variable('prune_sticky', 0),
				];

				/**
				 * Use this event to pass data from the prune form to the confirmation screen
				 *
				 * @event core.prune_forums_settings_confirm
				 * @var array	hidden_fields	Hidden fields that are passed through the confirm screen
				 * @since 3.2.2-RC1
				 */
				$vars = ['hidden_fields'];
				extract($this->dispatcher->trigger_event('core.prune_forums_settings_confirm', compact($vars)));

				confirm_box(false, $this->language->lang('PRUNE_FORUM_CONFIRM'), build_hidden_fields($hidden_fields));
			}
		}

		// If they haven't selected a forum for pruning yet then
		// display a select box to use for pruning.
		if (!count($forum_id))
		{
			$this->template->assign_vars([
				'U_ACTION'			=> $this->u_action,
				'S_SELECT_FORUM'	=> true,
				'S_FORUM_OPTIONS'	=> make_forum_select(false, false, false)]
			);
		}
		else
		{
			$sql = 'SELECT forum_id, forum_name
				FROM ' . FORUMS_TABLE . '
				WHERE ' . $this->db->sql_in_set('forum_id', $forum_id);
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);

			if (!$row)
			{
				$this->db->sql_freeresult($result);
				trigger_error($this->language->lang('NO_FORUM') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$forum_list = $s_hidden_fields = '';
			do
			{
				$forum_list .= (($forum_list != '') ? ', ' : '') . '<b>' . $row['forum_name'] . '</b>';
				$s_hidden_fields .= '<input type="hidden" name="f[]" value="' . $row['forum_id'] . '" />';
			}
			while ($row = $this->db->sql_fetchrow($result));

			$this->db->sql_freeresult($result);

			$l_selected_forums = (count($forum_id) == 1) ? 'SELECTED_FORUM' : 'SELECTED_FORUMS';

			$template_data = [
				'L_SELECTED_FORUMS'		=> $this->language->lang($l_selected_forums),
				'U_ACTION'				=> $this->u_action,
				'U_BACK'				=> $this->u_action,
				'FORUM_LIST'			=> $forum_list,
				'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
			];

			/**
			 * Event to add/modify prune forums settings template data
			 *
			 * @event core.prune_forums_settings_template_data
			 * @var array	template_data	Array with form template data
			 * @since 3.2.2-RC1
			 */
			$vars = ['template_data'];
			extract($this->dispatcher->trigger_event('core.prune_forums_settings_template_data', compact($vars)));

			$this->template->assign_vars($template_data);
		}
	}

	/**
	 * Prune users
	 */
	function prune_users($id, $mode)
	{
		$this->language->add_lang('memberlist');

		$prune = ($this->request->is_set_post('prune')) ? true : false;

		if ($prune)
		{
			$action = $this->request->variable('action', 'deactivate');
			$deleteposts = $this->request->variable('deleteposts', 0);

			if (confirm_box(true))
			{
				$user_ids = $usernames = [];

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

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $l_log, false, [implode(', ', $usernames)]);
					$msg = $this->language->lang('USER_' . strtoupper($action) . '_SUCCESS');
				}
				else
				{
					$msg = $this->language->lang('USER_PRUNE_FAILURE');
				}

				trigger_error($msg . adm_back_link($this->u_action));
			}
			else
			{
				// We list the users which will be pruned...
				$user_ids = $usernames = [];
				$this->get_prune_users($user_ids, $usernames);

				if (!count($user_ids))
				{
					trigger_error($this->language->lang('USER_PRUNE_FAILURE') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				// Assign to template
				foreach ($user_ids as $user_id)
				{
					$this->template->assign_block_vars('users', [
						'USERNAME'			=> $usernames[$user_id],
						'USER_ID'           => $user_id,
						'U_PROFILE'			=> get_username_string('profile', $user_id, $usernames[$user_id]),
						'U_USER_ADMIN'		=> ($this->auth->acl_get('a_user')) ? append_sid("{$this->admin_path}index.$this->php_ext", 'i=users&amp;mode=overview&amp;u=' . $user_id, true, $this->user->session_id) : '',
					]);
				}

				$this->template->assign_vars([
					'S_DEACTIVATE'		=> ($action == 'deactivate') ? true : false,
					'S_DELETE'			=> ($action == 'delete') ? true : false,
				]);

				confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
					'i'				=> $id,
					'mode'			=> $mode,
					'prune'			=> 1,

					'deleteposts'	=> $this->request->variable('deleteposts', 0),
					'action'		=> $this->request->variable('action', ''),
				]), 'confirm_body_prune.html');
			}
		}

		$find_count = ['lt' => $this->language->lang('LESS_THAN'), 'eq' => $this->language->lang('EQUAL_TO'), 'gt' => $this->language->lang('MORE_THAN')];
		$s_find_count = '';

		foreach ($find_count as $key => $value)
		{
			$selected = ($key == 'eq') ? ' selected="selected"' : '';
			$s_find_count .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}

		$find_time = ['lt' => $this->language->lang('BEFORE'), 'gt' => $this->language->lang('AFTER')];
		$s_find_active_time = '';
		foreach ($find_time as $key => $value)
		{
			$s_find_active_time .= '<option value="' . $key . '">' . $value . '</option>';
		}

		$sql = 'SELECT group_id, group_name
			FROM ' . GROUPS_TABLE . '
			WHERE group_type <> ' . GROUP_SPECIAL . '
			ORDER BY group_name ASC';
		$result = $this->db->sql_query($sql);

		$s_group_list = '';
		while ($row = $this->db->sql_fetchrow($result))
		{
			$s_group_list .= '<option value="' . $row['group_id'] . '">' . $this->group_helper->get_name($row['group_name']) . '</option>';
		}
		$this->db->sql_freeresult($result);

		if ($s_group_list)
		{
			// Only prepend the "All groups" option if there are groups,
			// otherwise we don't want to display this option at all.
			$s_group_list = '<option value="0">' . $this->language->lang('PRUNE_USERS_GROUP_NONE') . '</option>' . $s_group_list;
		}

		$this->template->assign_vars([
			'U_ACTION'			=> $this->u_action,
			'S_ACTIVE_OPTIONS'	=> $s_find_active_time,
			'S_GROUP_LIST'		=> $s_group_list,
			'S_COUNT_OPTIONS'	=> $s_find_count,
			'U_FIND_USERNAME'	=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=acp_prune&amp;field=users'),
		]);
	}

	/**
	 * Get user_ids/usernames from those being pruned
	 */
	function get_prune_users(&$user_ids, &$usernames)
	{
		$users_by_name = $this->request->variable('users', '', true);
		$users_by_id = $this->request->variable('user_ids', [0]);
		$group_id = $this->request->variable('group_id', 0);
		$posts_on_queue = (trim($this->request->variable('posts_on_queue', '')) === '') ? false : $this->request->variable('posts_on_queue', 0);

		if ($users_by_name)
		{
			$users = explode("\n", $users_by_name);
			$where_sql = ' AND ' . $this->db->sql_in_set('username_clean', array_map('utf8_clean_string', $users));
		}
		else if (!empty($users_by_id))
		{
			$user_ids = $users_by_id;
			user_get_id_name($user_ids, $usernames);

			$where_sql = ' AND ' . $this->db->sql_in_set('user_id', $user_ids);
		}
		else
		{
			$username = $this->request->variable('username', '', true);
			$email = $this->request->variable('email', '');

			$active_select = $this->request->variable('active_select', 'lt');
			$count_select = $this->request->variable('count_select', 'eq');
			$queue_select = $this->request->variable('queue_select', 'gt');
			$joined_before = $this->request->variable('joined_before', '');
			$joined_after = $this->request->variable('joined_after', '');
			$active = $this->request->variable('active', '');

			$count = ($this->request->variable('count', '') === '') ? false : $this->request->variable('count', 0);

			$active = ($active) ? explode('-', $active) : [];
			$joined_before = ($joined_before) ? explode('-', $joined_before) : [];
			$joined_after = ($joined_after) ? explode('-', $joined_after) : [];

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
				trigger_error($this->language->lang('WRONG_ACTIVE_JOINED_DATE') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$key_match = ['lt' => '<', 'gt' => '>', 'eq' => '='];

			$where_sql = '';
			$where_sql .= ($username) ? ' AND username_clean ' . $this->db->sql_like_expression(str_replace('*', $this->db->get_any_char(), utf8_clean_string($username))) : '';
			$where_sql .= ($email) ? ' AND user_email ' . $this->db->sql_like_expression(str_replace('*', $this->db->get_any_char(), $email)) . ' ' : '';
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
		$result = $this->db->sql_query($sql);

		$bot_ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$bot_ids[] = $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		// Protect the admin, do not prune if no options are given...
		if ($where_sql)
		{
			// Do not prune founder members
			$sql = 'SELECT user_id, username
				FROM ' . USERS_TABLE . '
				WHERE user_id <> ' . ANONYMOUS . '
					AND user_type <> ' . USER_FOUNDER . "
				$where_sql";
			$result = $this->db->sql_query($sql);

			$user_ids = $usernames = [];

			while ($row = $this->db->sql_fetchrow($result))
			{
				// Do not prune bots and the user currently pruning.
				if ($row['user_id'] != $this->user->data['user_id'] && !in_array($row['user_id'], $bot_ids))
				{
					$user_ids[] = $row['user_id'];
					$usernames[$row['user_id']] = $row['username'];
				}
			}
			$this->db->sql_freeresult($result);
		}

		if ($group_id)
		{
			$sql = 'SELECT u.user_id, u.username
				FROM ' . USER_GROUP_TABLE . ' ug, ' . USERS_TABLE . ' u
				WHERE ug.group_id = ' . (int) $group_id . '
					AND ug.user_id <> ' . ANONYMOUS . '
					AND u.user_type <> ' . USER_FOUNDER . '
					AND ug.user_pending = 0
					AND ug.group_leader = 0
					AND u.user_id = ug.user_id
					' . (!empty($user_ids) ? ' AND ' . $this->db->sql_in_set('ug.user_id', $user_ids) : '');
			$result = $this->db->sql_query($sql);

			// we're performing an intersection operation, so all the relevant users
			// come from this most recent query (which was limited to the results of the
			// previous query)
			$user_ids = $usernames = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				// Do not prune bots and the user currently pruning.
				if ($row['user_id'] != $this->user->data['user_id'] && !in_array($row['user_id'], $bot_ids))
				{
					$user_ids[] = $row['user_id'];
					$usernames[$row['user_id']] = $row['username'];
				}
			}
			$this->db->sql_freeresult($result);
		}

		if ($posts_on_queue !== false)
		{
			$sql = 'SELECT u.user_id, u.username, COUNT(p.post_id) AS queue_posts
				FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
				WHERE u.user_id <> ' . ANONYMOUS . '
					AND u.user_type <> ' . USER_FOUNDER . '
					AND ' . $this->db->sql_in_set('p.post_visibility', [ITEM_UNAPPROVED, ITEM_REAPPROVE]) . '
					AND u.user_id = p.poster_id
					' . (!empty($user_ids) ? ' AND ' . $this->db->sql_in_set('p.poster_id', $user_ids) : '') . '
				GROUP BY p.poster_id
				HAVING queue_posts ' . $key_match[$queue_select] . ' ' . $posts_on_queue;
			$result = $this->db->sql_query($sql);

			// same intersection logic as the above group ID portion
			$user_ids = $usernames = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				// Do not prune bots and the user currently pruning.
				if ($row['user_id'] != $this->user->data['user_id'] && !in_array($row['user_id'], $bot_ids))
				{
					$user_ids[] = $row['user_id'];
					$usernames[$row['user_id']] = $row['username'];
				}
			}
			$this->db->sql_freeresult($result);
		}
	}
}
