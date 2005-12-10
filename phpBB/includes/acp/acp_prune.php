<?php
/** 
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package acp
*/
class acp_prune
{
	var $u_action = '';

	function main($id, $mode)
	{
		global $user, $phpEx, $SID, $phpbb_admin_path;

		$user->add_lang('acp/prune');

		$this->u_action = "{$phpbb_admin_path}index.$phpEx$SID&amp;i=$id&amp;mode=$mode";

		switch ($mode)
		{
			case 'forums':
				$this->tpl_name = 'acp_prune_forums';
				$this->page_header = 'ACP_PRUNE_FORUMS';
				$this->prune_forums($id, $mode);
			break;

			case 'users':
				$this->tpl_name = 'acp_prune_users';
				$this->page_header = 'ACP_PRUNE_USERS';
				$this->prune_users($id, $mode);
			break;
		}
	}

	/**
	* Prune forums
	*/
	function prune_forums($id, $mode)
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $SID, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$forum_id = request_var('f', array(0));
		$submit = (isset($_POST['submit'])) ? true : false;

		if ($submit)
		{
			$prune_posted = request_var('prune_days', 0);
			$prune_viewed = request_var('prune_vieweddays', 0);
			$prune_all = !$prune_posted && !$prune_viewed;
	
			$prune_flags = 0;
			$prune_flags += (request_var('prune_old_polls', 0)) ? 2 : 0;
			$prune_flags += (request_var('prune_announce', 0)) ? 4 : 0;
			$prune_flags += (request_var('prune_sticky', 0)) ? 8 : 0;

			// Convert days to seconds for timestamp functions...
			$prunedate_posted = time() - ($prune_posted * 86400);
			$prunedate_viewed = time() - ($prune_viewed * 86400);

			$template->assign_vars(array(
				'S_PRUNED'		=> true)
			);

			$sql_forum = (sizeof($forum_id)) ? ' AND forum_id IN (' . implode(', ', $forum_id) . ')' : '';

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
				sync('forum', 'forum_id', $prune_ids, true);
				add_log('admin', 'LOG_PRUNE', $log_data);
			}
			$db->sql_freeresult($result);

			return;
		}

		// If they haven't selected a forum for pruning yet then
		// display a select box to use for pruning.
		if (!sizeof($forum_id))
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
				WHERE forum_id IN (' . implode(', ', $forum_id) . ')';
			$result = $db->sql_query($sql);

			if (!($row = $db->sql_fetchrow($result)))
			{
				trigger_error($user->lang['NO_FORUM'] . adm_back_link($this->u_action));
			}

			$forum_list = $s_hidden_fields = '';
			do
			{
				$forum_list .= (($forum_list != '') ? ', ' : '') . '<b>' . $row['forum_name'] . '</b>';
				$s_hidden_fields .= '<input type="hidden" name="f[]" value="' . $row['forum_id'] . '" />';
			}
			while ($row = $db->sql_fetchrow($result));

			$db->sql_freeresult($result);

			$l_selected_forums = (sizeof($forum_id) == 1) ? 'SELECTED_FORUM' : 'SELECTED_FORUMS';

			$template->assign_vars(array(
				'L_SELECTED_FORUMS'		=> $user->lang[$l_selected_forums],
				'U_ACTION'				=> $this->u_action,
				'U_BACK'				=> $this->u_action,
				'FORUM_LIST'			=> $forum_list,
				'S_HIDDEN_FIELDS'		=> $s_hidden_fields)
			);

		}

	}

	/**
	* Prune users
	*/
	function prune_users($id, $mode)
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $SID, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang('memberlist');

		$prune = (isset($_POST['prune'])) ? true : false;

		if ($prune)
		{
			if (confirm_box(true))
			{
				$users = request_var('users', '');
				$action = request_var('action', 'deactivate');
				$deleteposts = request_var('deleteposts', 0);
		
				if ($users)
				{
					$users = explode("\n", $users);

					$where_sql = '';
		
					foreach ($users as $username)
					{
						$where_sql .= (($where_sql != '') ? ', ' : '') . "'" . $db->sql_escape($username) . "'";
					}
					$where_sql = " AND username IN ($where_sql)";
				}
				else
				{
					$username = request_var('username', '');
					$email = request_var('email', '');

					$joined_select = request_var('joined_select', 'lt');
					$active_select = request_var('active_select', 'lt');
					$count_select = request_var('count_select', 'eq');
					$joined = request_var('joined', '');
					$active = request_var('active', '');

					$active = ($active) ? explode('-', $active) : array();
					$joined = ($joined) ? explode('-', $joined) : array();

					$count = request_var('count', 0);

					$key_match = array('lt' => '<', 'gt' => '>', 'eq' => '=');
					$sort_by_types = array('username', 'user_email', 'user_posts', 'user_regdate', 'user_lastvisit');

					$where_sql = '';
					$where_sql .= ($username) ? " AND username LIKE '" . $db->sql_escape(str_replace('*', '%', $username)) . "'" : '';
					$where_sql .= ($email) ? " AND user_email LIKE '" . $db->sql_escape(str_replace('*', '%', $email)) . "' " : '';
					$where_sql .= (sizeof($joined)) ? " AND user_regdate " . $key_match[$joined_select] . ' ' . gmmktime(0, 0, 0, (int) $joined[1], (int) $joined[2], (int) $joined[0]) : '';
					$where_sql .= ($count) ? " AND user_posts " . $key_match[$count_select] . " $count " : '';
					$where_sql .= (sizeof($active)) ? " AND user_lastvisit " . $key_match[$active_select] . " " . gmmktime(0, 0, 0, (int) $active[1], (int) $active[2], (int) $active[0]) : '';
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
				
				$sql = 'SELECT username, user_id FROM ' . USERS_TABLE . '
					WHERE user_id <> ' . ANONYMOUS . "
					$where_sql";
				$result = $db->sql_query($sql);

				$where_sql = '';
				$user_ids = $usernames = array();

				if ($row = $db->sql_fetchrow($result))
				{
					do
					{
						if (!in_array($row['user_id'], $bot_ids))
						{
							$where_sql .= (($where_sql != '') ? ', ' : '') . $row['user_id'];
							$user_ids[] = $row['user_id'];
							$usernames[] = $row['username'];
						}
					}
					while ($row = $db->sql_fetchrow($result));

					if ($where_sql)
					{
						$where_sql = " AND user_id IN ($where_sql)";
					}
				}
				$db->sql_freeresult($result);

				if ($where_sql)
				{
					$sql = '';

					if ($action == 'delete')
					{
						if ($deleteposts)
						{
							delete_posts('poster_id', $user_ids, true);
							$l_log = 'LOG_PRUNE_USER_DEL_DEL';
						}
						else
						{
							for ($i = 0, $size = sizeof($user_ids); $i < $size; $i++)
							{
								$sql = 'UPDATE ' . POSTS_TABLE . '
									SET poster_id = ' . ANONYMOUS . ", post_username = '" . $db->sql_escape($usernames[$i]) . "'
									WHERE user_id = " . $userids[$i];
								$db->sql_query($sql);
							}

							$l_log = 'LOG_PRUNE_USER_DEL_ANON';
						}

						$sql = 'DELETE FROM ' . USERS_TABLE;
					}
					else if ($action == 'deactivate')
					{
						$sql = 'UPDATE ' . USERS_TABLE . " 
							SET user_active = 0";

						$l_log = 'LOG_PRUNE_USER_DEAC';
					}

					$sql .= ' WHERE user_id <> ' . ANONYMOUS . "
						$where_sql";
					$db->sql_query($sql);

					add_log('admin', $l_log, implode(', ', $usernames));
				}

				trigger_error($user->lang['USER_' . strtoupper($action) . '_SUCCESS'] . adm_back_link($this->u_action));
			}
			else
			{
				confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
					'i'				=> $id,
					'mode'			=> $mode,
					'prune'			=> 1,

					'users'			=> request_var('users', ''),
					'username'		=> request_var('username', ''),
					'email'			=> request_var('email', ''),
					'joined_select'	=> request_var('joined_select', ''),
					'joined'		=> request_var('joined', ''),
					'active_select'	=> request_var('active_select', ''),
					'active'		=> request_var('active', ''),
					'count_select'	=> request_var('count_select', ''),
					'count'			=> request_var('count', 0),
					'deleteposts'	=> request_var('deleteposts', 0),

					'action'		=> request_var('action', ''),
				)));
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
		$s_find_join_time = '';
		foreach ($find_time as $key => $value)
		{
			$s_find_join_time .= '<option value="' . $key . '">' . $value . '</option>';
		}
		
		$s_find_active_time = '';
		foreach ($find_time as $key => $value)
		{
			$s_find_active_time .= '<option value="' . $key . '">' . $value . '</option>';
		}

		$template->assign_vars(array(
			'U_ACTION'			=> $this->u_action,
			'S_JOINED_OPTIONS'	=> $s_find_join_time,
			'S_ACTIVE_OPTIONS'	=> $s_find_active_time,
			'S_COUNT_OPTIONS'	=> $s_find_count,
			'U_FIND_USER'		=> $phpbb_root_path . "memberlist.$phpEx$SID&amp;mode=searchuser&amp;form=acp_prune&amp;field=users")
		);

	}
}

/**
* @package module_install
*/
class acp_prune_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_prune',
			'title'		=> 'ACP_PRUNING',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'forums'	=> array('title' => 'ACP_PRUNE_FORUMS', 'auth' => 'acl_a_prune'),
				'users'		=> array('title' => 'ACP_PRUNE_USERS', 'auth' => 'acl_a_userdel'),
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