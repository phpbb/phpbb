<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_v306rc1 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_v305');
	}

	function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'confirm' => array(
					'attempts' => array('UINT', 0),
				),
				$this->table_prefix . 'users' => array(
					'user_new' => array('BOOL', 1),
					'user_reminded' => array('TINT:4', 0),
					'user_reminded_time' => array('TIMESTAMP', 0),
				),
				$this->table_prefix . 'groups' => array(
					'group_skip_auth' => array('BOOL', 0, 'after' => 'group_founder_manage'),
				),
				$this->table_prefix . 'privmsgs' => array(
					'message_reported' => array('BOOL', 0),
				),
				$this->table_prefix . 'reports' => array(
					'pm_id' => array('UINT', 0),
				),
				$this->table_prefix . 'fields'			=> array(
					'field_show_on_vt' => array('BOOL', 0),
				),
				$this->table_prefix . 'forums' => array(
					'forum_options' => array('UINT:20', 0),
				),
			),
			'change_columns' => array(
				$this->table_prefix . 'users' => array(
					'user_options' => array('UINT:11', 230271),
				),
			),
			'add_index' => array(
				$this->table_prefix . 'reports' => array(
					'post_id' => array('post_id'),
					'pm_id' => array('pm_id'),
				),
				$this->table_prefix . 'posts' => array(
					'post_username' => array('post_username:255'),
				),
			),
		);
	}

	function update_data()
	{
		// Let's see if the GD Captcha can be enabled... we simply look for what *is* enabled...
		if (!empty($config['captcha_gd']) && !isset($config['captcha_plugin']))
		{
			set_config('captcha_plugin', 'phpbb_captcha_gd');
		}
		else if (!isset($config['captcha_plugin']))
		{
			set_config('captcha_plugin', 'phpbb_captcha_nogd');
		}

		// Entries for the Feed Feature
		set_config('feed_enable', '0');
		set_config('feed_limit', '10');

		set_config('feed_overall_forums', '1');
		set_config('feed_overall_forums_limit', '15');

		set_config('feed_overall_topics', '0');
		set_config('feed_overall_topics_limit', '15');

		set_config('feed_forum', '1');
		set_config('feed_topic', '1');
		set_config('feed_item_statistics', '1');

		// Entries for smiley pagination
		set_config('smilies_per_page', '50');

		// Entry for reporting PMs
		set_config('allow_pm_report', '1');

		// Install modules
		$modules_to_install = array(
			'feed'					=> array(
				'base'		=> 'board',
				'class'		=> 'acp',
				'title'		=> 'ACP_FEED_SETTINGS',
				'auth'		=> 'acl_a_board',
				'cat'		=> 'ACP_BOARD_CONFIGURATION',
				'after'		=> array('signature', 'ACP_SIGNATURE_SETTINGS')
			),
			'warnings'				=> array(
				'base'		=> 'users',
				'class'		=> 'acp',
				'title'		=> 'ACP_USER_WARNINGS',
				'auth'		=> 'acl_a_user',
				'display'	=> 0,
				'cat'		=> 'ACP_CAT_USERS',
				'after'		=> array('feedback', 'ACP_USER_FEEDBACK')
			),
			'send_statistics'		=> array(
				'base'		=> 'send_statistics',
				'class'		=> 'acp',
				'title'		=> 'ACP_SEND_STATISTICS',
				'auth'		=> 'acl_a_server',
				'cat'		=> 'ACP_SERVER_CONFIGURATION'
			),
			'setting_forum_copy'	=> array(
				'base'		=> 'permissions',
				'class'		=> 'acp',
				'title'		=> 'ACP_FORUM_PERMISSIONS_COPY',
				'auth'		=> 'acl_a_fauth && acl_a_authusers && acl_a_authgroups && acl_a_mauth',
				'cat'		=> 'ACP_FORUM_BASED_PERMISSIONS',
				'after'		=> array('setting_forum_local', 'ACP_FORUM_PERMISSIONS')
			),
			'pm_reports'			=> array(
				'base'		=> 'pm_reports',
				'class'		=> 'mcp',
				'title'		=> 'MCP_PM_REPORTS_OPEN',
				'auth'		=> 'aclf_m_report',
				'cat'		=> 'MCP_REPORTS'
			),
			'pm_reports_closed'		=> array(
				'base'		=> 'pm_reports',
				'class'		=> 'mcp',
				'title'		=> 'MCP_PM_REPORTS_CLOSED',
				'auth'		=> 'aclf_m_report',
				'cat'		=> 'MCP_REPORTS'
			),
			'pm_report_details'		=> array(
				'base'		=> 'pm_reports',
				'class'		=> 'mcp',
				'title'		=> 'MCP_PM_REPORT_DETAILS',
				'auth'		=> 'aclf_m_report',
				'cat'		=> 'MCP_REPORTS'
			),
		);

		_add_modules($modules_to_install);

		// Add newly_registered group... but check if it already exists (we always supported running the updater on any schema)
		$sql = 'SELECT group_id
			FROM ' . GROUPS_TABLE . "
			WHERE group_name = 'NEWLY_REGISTERED'";
		$result = $db->sql_query($sql);
		$group_id = (int) $db->sql_fetchfield('group_id');
		$db->sql_freeresult($result);

		if (!$group_id)
		{
			$sql = 'INSERT INTO ' .  GROUPS_TABLE . " (group_name, group_type, group_founder_manage, group_colour, group_legend, group_avatar, group_desc, group_desc_uid, group_max_recipients) VALUES ('NEWLY_REGISTERED', 3, 0, '', 0, '', '', '', 5)";
			_sql($sql, $errored, $error_ary);

			$group_id = $db->sql_nextid();
		}

		// Insert new user role... at the end of the chain
		$sql = 'SELECT role_id
			FROM ' . ACL_ROLES_TABLE . "
			WHERE role_name = 'ROLE_USER_NEW_MEMBER'
				AND role_type = 'u_'";
		$result = $db->sql_query($sql);
		$u_role = (int) $db->sql_fetchfield('role_id');
		$db->sql_freeresult($result);

		if (!$u_role)
		{
			$sql = 'SELECT MAX(role_order) as max_order_id
				FROM ' . ACL_ROLES_TABLE . "
				WHERE role_type = 'u_'";
			$result = $db->sql_query($sql);
			$next_order_id = (int) $db->sql_fetchfield('max_order_id');
			$db->sql_freeresult($result);

			$next_order_id++;

			$sql = 'INSERT INTO ' . ACL_ROLES_TABLE . " (role_name, role_description, role_type, role_order) VALUES ('ROLE_USER_NEW_MEMBER', 'ROLE_DESCRIPTION_USER_NEW_MEMBER', 'u_', $next_order_id)";
			_sql($sql, $errored, $error_ary);
			$u_role = $db->sql_nextid();

			if (!$errored)
			{
				// Now add the correct data to the roles...
				// The standard role says that new users are not able to send a PM, Mass PM, are not able to PM groups
				$sql = 'INSERT INTO ' . ACL_ROLES_DATA_TABLE . " (role_id, auth_option_id, auth_setting) SELECT $u_role, auth_option_id, 0 FROM " . ACL_OPTIONS_TABLE . " WHERE auth_option LIKE 'u_%' AND auth_option IN ('u_sendpm', 'u_masspm', 'u_masspm_group')";
				_sql($sql, $errored, $error_ary);

				// Add user role to group
				$sql = 'INSERT INTO ' . ACL_GROUPS_TABLE . " (group_id, forum_id, auth_option_id, auth_role_id, auth_setting) VALUES ($group_id, 0, 0, $u_role, 0)";
				_sql($sql, $errored, $error_ary);
			}
		}

		// Insert new forum role
		$sql = 'SELECT role_id
			FROM ' . ACL_ROLES_TABLE . "
			WHERE role_name = 'ROLE_FORUM_NEW_MEMBER'
				AND role_type = 'f_'";
		$result = $db->sql_query($sql);
		$f_role = (int) $db->sql_fetchfield('role_id');
		$db->sql_freeresult($result);

		if (!$f_role)
		{
			$sql = 'SELECT MAX(role_order) as max_order_id
				FROM ' . ACL_ROLES_TABLE . "
				WHERE role_type = 'f_'";
			$result = $db->sql_query($sql);
			$next_order_id = (int) $db->sql_fetchfield('max_order_id');
			$db->sql_freeresult($result);

			$next_order_id++;

			$sql = 'INSERT INTO ' . ACL_ROLES_TABLE . " (role_name, role_description, role_type, role_order) VALUES  ('ROLE_FORUM_NEW_MEMBER', 'ROLE_DESCRIPTION_FORUM_NEW_MEMBER', 'f_', $next_order_id)";
			_sql($sql, $errored, $error_ary);
			$f_role = $db->sql_nextid();

			if (!$errored)
			{
				$sql = 'INSERT INTO ' . ACL_ROLES_DATA_TABLE . " (role_id, auth_option_id, auth_setting) SELECT $f_role, auth_option_id, 0 FROM " . ACL_OPTIONS_TABLE . " WHERE auth_option LIKE 'f_%' AND auth_option IN ('f_noapprove')";
				_sql($sql, $errored, $error_ary);
			}
		}

		// Set every members user_new column to 0 (old users) only if there is no one yet (this makes sure we do not execute this more than once)
		$sql = 'SELECT 1
			FROM ' . USERS_TABLE . '
			WHERE user_new = 0';
		$result = $db->sql_query_limit($sql, 1);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$row)
		{
			$sql = 'UPDATE ' . USERS_TABLE . ' SET user_new = 0';
			_sql($sql, $errored, $error_ary);
		}

		// Newly registered users limit
		if (!isset($config['new_member_post_limit']))
		{
			set_config('new_member_post_limit', (!empty($config['enable_queue_trigger'])) ? $config['queue_trigger_posts'] : 0);
		}

		if (!isset($config['new_member_group_default']))
		{
			set_config('new_member_group_default', 0);
		}

		// To mimick the old "feature" we will assign the forum role to every forum, regardless of the setting (this makes sure there are no "this does not work!!!! YUO!!!" posts...
		// Check if the role is already assigned...
		$sql = 'SELECT forum_id
			FROM ' . ACL_GROUPS_TABLE . '
			WHERE group_id = ' . $group_id . '
				AND auth_role_id = ' . $f_role;
		$result = $db->sql_query($sql);
		$is_options = (int) $db->sql_fetchfield('forum_id');
		$db->sql_freeresult($result);

		// Not assigned at all... :/
		if (!$is_options)
		{
			// Get postable forums
			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				WHERE forum_type != ' . FORUM_LINK;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				_sql('INSERT INTO ' . ACL_GROUPS_TABLE . ' (group_id, forum_id, auth_option_id, auth_role_id, auth_setting) VALUES (' . $group_id . ', ' . (int) $row['forum_id'] . ', 0, ' . $f_role . ', 0)', $errored, $error_ary);
			}
			$db->sql_freeresult($result);
		}

		// Clear permissions...
		include_once($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
		$auth_admin = new auth_admin();
		$auth_admin->acl_clear_prefetch();

		if (!isset($config['allow_avatar']))
		{
			if ($config['allow_avatar_upload'] || $config['allow_avatar_local'] || $config['allow_avatar_remote'])
			{
				set_config('allow_avatar', '1');
			}
			else
			{
				set_config('allow_avatar', '0');
			}
		}

		if (!isset($config['allow_avatar_remote_upload']))
		{
			if ($config['allow_avatar_remote'] && $config['allow_avatar_upload'])
			{
				set_config('allow_avatar_remote_upload', '1');
			}
			else
			{
				set_config('allow_avatar_remote_upload', '0');
			}
		}

		// Minimum number of characters
		if (!isset($config['min_post_chars']))
		{
			set_config('min_post_chars', '1');
		}

		if (!isset($config['allow_quick_reply']))
		{
			set_config('allow_quick_reply', '1');
		}

		// Set every members user_options column to enable
		// bbcode, smilies and URLs for signatures by default
		$sql = 'SELECT user_options
			FROM ' . USERS_TABLE . '
			WHERE user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')';
		$result = $db->sql_query_limit($sql, 1);
		$user_option = (int) $db->sql_fetchfield('user_options');
		$db->sql_freeresult($result);

		// Check if we already updated the database by checking bit 15 which we used to store the sig_bbcode option
		if (!($user_option & 1 << 15))
		{
			// 229376 is the added value to enable all three signature options
			$sql = 'UPDATE ' . USERS_TABLE . ' SET user_options = user_options + 229376';
			_sql($sql, $errored, $error_ary);
		}

		if (!isset($config['delete_time']))
		{
			set_config('delete_time', $config['edit_time']);
		}
	}
}
