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

use phpbb\exception\back_exception;

class prune
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\group\helper */
	protected $group_helper;

	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB admin path */
	protected $admin_path;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth					$auth			Auth object
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\event\dispatcher			$dispatcher		Event dispatcher object
	 * @param \phpbb\group\helper				$group_helper	Group helper object
	 * @param \phpbb\acp\helper\controller		$helper			ACP Controller helper object
	 * @param \phpbb\language\language			$lang			Language object
	 * @param \phpbb\log\log					$log			Log object
	 * @param \phpbb\request\request			$request		Request object
	 * @param \phpbb\template\template			$template		Template object
	 * @param \phpbb\user						$user			User object
	 * @param string							$admin_path		phpBB admin path
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 * @param array								$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\group\helper $group_helper,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$admin_path,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth			= $auth;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->group_helper	= $group_helper;
		$this->helper		= $helper;
		$this->lang			= $lang;
		$this->log			= $log;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->admin_path	= $admin_path;
		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	function main($mode)
	{
		$this->lang->add_lang('acp/prune');

		if (!function_exists('user_active_flip'))
		{
			include($this->root_path . 'includes/functions_user.' . $this->php_ext);
		}

		return $this->{'prune_' . $mode}();
	}

	/**
	 * Prune forums
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	function prune_forums()
	{
		$all_forums	= $this->request->variable('all_forums', 0);
		$forum_ids	= $this->request->variable('f', [0]);
		$submit		= $this->request->is_set_post('submit');

		if ($all_forums)
		{
			$forum_ids = [];

			$sql = 'SELECT forum_id
				FROM ' . $this->tables['forums'] . '
				ORDER BY left_id';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$forum_ids[] = (int) $row['forum_id'];
			}
			$this->db->sql_freeresult($result);
		}

		if ($submit)
		{
			if (confirm_box(true))
			{
				$prune_posted	= $this->request->variable('prune_days', 0);
				$prune_viewed	= $this->request->variable('prune_vieweddays', 0);
				$prune_all		= (!$prune_posted && !$prune_viewed) ? true : false;

				$prune_flags	= 0;
				$prune_flags	+= $this->request->variable('prune_old_polls', 0) ? 2 : 0;
				$prune_flags	+= $this->request->variable('prune_announce', 0) ? 4 : 0;
				$prune_flags	+= $this->request->variable('prune_sticky', 0) ? 8 : 0;

				// Convert days to seconds for timestamp functions...
				$prunedate_posted = time() - ($prune_posted * 86400);
				$prunedate_viewed = time() - ($prune_viewed * 86400);

				$this->template->assign_var('S_PRUNED', true);

				// Get a list of forum's or the data for the forum that we are pruning.
				$sql = 'SELECT forum_id, forum_name
					FROM ' . $this->tables['forums'] . '
					WHERE forum_type = ' . FORUM_POST .
						(!empty($forum_ids) ? ' AND ' . $this->db->sql_in_set('forum_id', $forum_ids) : '') . '
					ORDER BY left_id ASC';
				$result = $this->db->sql_query($sql);

				if ($row = $this->db->sql_fetchrow($result))
				{
					$log_data	= '';
					$prune_ids	= [];

					$p_result['topics']	= 0;
					$p_result['posts']	= 0;

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

								$p_result['topics']	+= $return['topics'];
								$p_result['posts']	+= $return['posts'];
							}

							if ($prune_viewed)
							{
								$return = prune($row['forum_id'], 'viewed', $prunedate_viewed, $prune_flags, false);

								$p_result['topics']	+= $return['topics'];
								$p_result['posts']	+= $return['posts'];
							}
						}

						$prune_ids[] = (int) $row['forum_id'];

						$this->template->assign_block_vars('pruned', [
							'FORUM_NAME'	=> $row['forum_name'],
							'NUM_TOPICS'	=> $p_result['topics'],
							'NUM_POSTS'		=> $p_result['posts'],
						]);

						$log_data .= ($log_data !== '' ? $this->lang->lang('COMMA_SEPARATOR') : '') . $row['forum_name'];
					}
					while ($row = $this->db->sql_fetchrow($result));

					// Sync all pruned forums at once
					sync('forum', 'forum_id', $prune_ids, true, true);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PRUNE', false, [$log_data]);
				}
				$this->db->sql_freeresult($result);

				return $this->helper->render('acp_prune_forums.html', 'ACP_PRUNE_FORUMS');
			}
			else
			{
				$hidden_fields = [
					'submit'		=> 1,
					'all_forums'	=> $all_forums,
					'f'				=> $forum_ids,

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

				confirm_box(false, $this->lang->lang('PRUNE_FORUM_CONFIRM'), build_hidden_fields($hidden_fields));

				return redirect($this->helper->route('acp_forums_prune'));
			}
		}

		// If they haven't selected a forum for pruning yet then
		// display a select box to use for pruning.
		if (empty($forum_ids))
		{
			$this->template->assign_vars([
				'S_SELECT_FORUM'	=> true,
				'S_FORUM_OPTIONS'	=> make_forum_select(false, false, false),
				'U_ACTION'			=> $this->helper->route('acp_forums_prune'),
			]);
		}
		else
		{
			$sql = 'SELECT forum_id, forum_name
				FROM ' . $this->tables['forums'] . '
				WHERE ' . $this->db->sql_in_set('forum_id', $forum_ids);
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);

			if ($row === false)
			{
				$this->db->sql_freeresult($result);

				throw new back_exception(404, 'NO_FORUM', 'acp_forums_prune');
			}

			$forum_list = $s_hidden_fields = '';
			do
			{
				$forum_list .= ($forum_list !== '' ? $this->lang->lang('COMMA_SEPARATOR') : '') . '<strong>' . $row['forum_name'] . '</strong>';
				$s_hidden_fields .= '<input type="hidden" name="f[]" value="' . $row['forum_id'] . '" />';
			}
			while ($row = $this->db->sql_fetchrow($result));

			$this->db->sql_freeresult($result);

			$l_selected_forums = count($forum_ids) === 1 ? 'SELECTED_FORUM' : 'SELECTED_FORUMS';

			$template_data = [
				'L_SELECTED_FORUMS'		=> $this->lang->lang($l_selected_forums),
				'U_ACTION'				=> $this->helper->route('acp_forums_prune'),
				'U_BACK'				=> $this->helper->route('acp_forums_prune'),
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

		return $this->helper->render('acp_prune_forums.html', 'ACP_PRUNE_FORUMS');
	}

	/**
	 * Prune users
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	function prune_users()
	{
		$this->lang->add_lang('memberlist');

		$prune = $this->request->is_set_post('prune');

		if ($prune)
		{
			$action = $this->request->variable('action', 'deactivate');
			$deleteposts = $this->request->variable('deleteposts', 0);

			if (confirm_box(true))
			{
				$l_log = '';
				$user_ids = $usernames = [];

				$this->get_prune_users($user_ids, $usernames);

				if (!empty($user_ids))
				{
					if ($action === 'deactivate')
					{
						user_active_flip('deactivate', $user_ids);

						$l_log = 'LOG_PRUNE_USER_DEAC';
					}
					else if ($action === 'delete')
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

					$msg = $this->lang->lang('USER_' . strtoupper($action) . '_SUCCESS');
				}
				else
				{
					$msg = $this->lang->lang('USER_PRUNE_FAILURE');
				}

				return $this->helper->message_back($msg, 'acp_users_prune');
			}
			else
			{
				// We list the users which will be pruned...
				$user_ids = $usernames = [];
				$this->get_prune_users($user_ids, $usernames);

				if (empty($user_ids))
				{
					throw new back_exception(400, 'USER_PRUNE_FAILURE', 'acp_users_prune');
				}

				// Assign to template
				foreach ($user_ids as $user_id)
				{
					$this->template->assign_block_vars('users', [
						'USERNAME'			=> $usernames[$user_id],
						'USER_ID'			=> $user_id,
						'U_PROFILE'			=> get_username_string('profile', $user_id, $usernames[$user_id]),
						'U_USER_ADMIN'		=> $this->auth->acl_get('a_user') ? $this->helper->route('acp_users_manage', ['mode' => 'overview', 'u' => $user_id], true, $this->user->session_id) : '',
					]);
				}

				$this->template->assign_vars([
					'S_DEACTIVATE'		=> $action === 'deactivate',
					'S_DELETE'			=> $action === 'delete',
				]);

				confirm_box(false, $this->lang->lang('CONFIRM_OPERATION'), build_hidden_fields([
					'prune'			=> 1,
					'action'		=> $this->request->variable('action', ''),
					'deleteposts'	=> $this->request->variable('deleteposts', 0),
				]), 'confirm_body_prune.html');

				return redirect($this->helper->route('acp_users_prune'));
			}
		}

		$find_count = ['lt' => $this->lang->lang('LESS_THAN'), 'eq' => $this->lang->lang('EQUAL_TO'), 'gt' => $this->lang->lang('MORE_THAN')];
		$s_find_count = '';

		foreach ($find_count as $key => $value)
		{
			$selected = $key === 'eq' ? ' selected="selected"' : '';
			$s_find_count .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}

		$find_time = ['lt' => $this->lang->lang('BEFORE'), 'gt' => $this->lang->lang('AFTER')];
		$s_find_active_time = '';

		foreach ($find_time as $key => $value)
		{
			$s_find_active_time .= '<option value="' . $key . '">' . $value . '</option>';
		}

		$s_group_list = '';

		$sql = 'SELECT group_id, group_name
			FROM ' . $this->tables['groups'] . '
			WHERE group_type <> ' . GROUP_SPECIAL . '
			ORDER BY group_name ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$s_group_list .= '<option value="' . $row['group_id'] . '">' . $this->group_helper->get_name($row['group_name']) . '</option>';
		}
		$this->db->sql_freeresult($result);

		if ($s_group_list)
		{
			// Only prepend the "All groups" option if there are groups,
			// otherwise we don't want to display this option at all.
			$s_group_list = '<option value="0">' . $this->lang->lang('PRUNE_USERS_GROUP_NONE') . '</option>' . $s_group_list;
		}

		$this->template->assign_vars([
			'S_ACTIVE_OPTIONS'	=> $s_find_active_time,
			'S_COUNT_OPTIONS'	=> $s_find_count,
			'S_GROUP_LIST'		=> $s_group_list,
			'U_FIND_USERNAME'	=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=acp_prune&amp;field=users'),
			'U_ACTION'			=> $this->helper->route('acp_users_prune'),
		]);

		return $this->helper->render('acp_prune_users.html', 'ACP_PRUNE_USERS');
	}

	/**
	 * Get user_ids/usernames from those being pruned
	 *
	 * @param array		$user_ids	The user identifiers
	 * @param array		$usernames	The usernames
	 * @return void
	 */
	function get_prune_users(array &$user_ids, array &$usernames)
	{
		$group_id		= $this->request->variable('group_id', 0);
		$users_by_id	= $this->request->variable('user_ids', [0]);
		$users_by_name	= $this->request->variable('users', '', true);
		$posts_on_queue	= $this->request->variable('posts_on_queue', '') === '' ? false : $this->request->variable('posts_on_queue', 0);

		$key_match		= [];
		$queue_select	= '';

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
			$email			= $this->request->variable('email', '');
			$username		= $this->request->variable('username', '', true);

			$active_select	= $this->request->variable('active_select', 'lt');
			$count_select	= $this->request->variable('count_select', 'eq');
			$queue_select	= $this->request->variable('queue_select', 'gt');
			$joined_after	= $this->request->variable('joined_after', '');
			$joined_before	= $this->request->variable('joined_before', '');

			$active			= $this->request->variable('active', '');
			$count			= $this->request->variable('count', '');

			$active			= $active ? explode('-', $active) : [];
			$count			= $count === '' ? false : $this->request->variable('count', 0);
			$joined_after	= $joined_after ? explode('-', $joined_after) : [];
			$joined_before	= $joined_before ? explode('-', $joined_before) : [];

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

			if ((!empty($active) && count($active) !== 3) || (!empty($joined_before) && count($joined_before) !== 3) || (!empty($joined_after) && count($joined_after) !== 3))
			{
				throw new back_exception(400, 'WRONG_ACTIVE_JOINED_DATE', 'acp_users_prune');
			}

			$key_match = ['lt' => '<', 'gt' => '>', 'eq' => '='];

			$where_sql = '';
			$where_sql .= $username ? ' AND username_clean ' . $this->db->sql_like_expression(str_replace('*', $this->db->get_any_char(), utf8_clean_string($username))) : '';
			$where_sql .= $email ? ' AND user_email ' . $this->db->sql_like_expression(str_replace('*', $this->db->get_any_char(), $email)) . ' ' : '';
			$where_sql .= $joined_sql;
			$where_sql .= $count !== false ? " AND user_posts " . $key_match[$count_select] . ' ' . (int) $count . ' ' : '';

			// First handle pruning of users who never logged in, last active date is 0000-00-00
			if (!empty($active) && (int) $active[0] == 0 && (int) $active[1] == 0 && (int) $active[2] == 0)
			{
				$where_sql .= ' AND user_lastvisit = 0';
			}
			else if (!empty($active) && $active_select != 'lt')
			{
				$where_sql .= ' AND user_lastvisit ' . $key_match[$active_select] . ' ' . gmmktime(0, 0, 0, (int) $active[1], (int) $active[2], (int) $active[0]);
			}
			else if (!empty($active))
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
		$bot_ids = [];

		$sql = 'SELECT user_id
			FROM ' . $this->tables['bots'];
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$bot_ids[] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		// Protect the admin, do not prune if no options are given...
		if ($where_sql)
		{
			// Do not prune founder members
			$user_ids = $usernames = [];

			$sql = 'SELECT user_id, username
				FROM ' . $this->tables['users'] . '
				WHERE user_id <> ' . ANONYMOUS . '
					AND user_type <> ' . USER_FOUNDER . "
				$where_sql";
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				// Do not prune bots and the user currently pruning.
				if ($row['user_id'] != $this->user->data['user_id'] && !in_array($row['user_id'], $bot_ids))
				{
					$user_ids[] = (int) $row['user_id'];
					$usernames[(int) $row['user_id']] = $row['username'];
				}
			}
			$this->db->sql_freeresult($result);
		}

		if ($group_id)
		{
			$sql = 'SELECT u.user_id, u.username
				FROM ' . $this->tables['user_group'] . ' ug, ' . $this->tables['users'] . ' u
				WHERE ug.group_id = ' . (int) $group_id . '
					AND ug.user_id <> ' . ANONYMOUS . '
					AND u.user_type <> ' . USER_FOUNDER . '
					AND ug.user_pending = 0
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
					$user_ids[] = (int) $row['user_id'];
					$usernames[(int) $row['user_id']] = $row['username'];
				}
			}
			$this->db->sql_freeresult($result);
		}

		if ($posts_on_queue !== false)
		{
			$sql = 'SELECT u.user_id, u.username, COUNT(p.post_id) AS queue_posts
				FROM ' . $this->tables['posts'] . ' p, ' . $this->tables['users'] . ' u
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
					$user_ids[] = (int) $row['user_id'];
					$usernames[(int) $row['user_id']] = $row['username'];
				}
			}
			$this->db->sql_freeresult($result);
		}
	}
}
