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
		return array(
			//array('custom', array(array(&$this, '')))
			array('config.add', array('captcha_plugin', 'phpbb_captcha_nogd')),
			array('config.update_if', array(
				($this->config['captcha_gd']),
				'captcha_plugin',
				'phpbb_captcha_gd',
			)),

			array('config.add', array('feed_enable', 0)),
			array('config.add', array('feed_limit', 10)),
			array('config.add', array('feed_overall_forums', 1)),
			array('config.add', array('feed_overall_forums_limit', 15)),
			array('config.add', array('feed_overall_topics', 0)),
			array('config.add', array('feed_overall_topics_limit', 15)),
			array('config.add', array('feed_forum', 1)),
			array('config.add', array('feed_topic', 1)),
			array('config.add', array('feed_item_statistics', 1)),

			array('config.add', array('smilies_per_page', 50)),
			array('config.add', array('allow_pm_report', 1)),
			array('config.add', array('min_post_chars', 1)),
			array('config.add', array('allow_quick_reply', 1)),
			array('config.add', array('new_member_post_limit', 0)),
			array('config.add', array('new_member_group_default', 0)),
			array('config.add', array('delete_time', $this->config['edit_time'])),

			array('config.add', array('allow_avatar', 0)),
			array('config.add_if', array(
				($this->config['allow_avatar_upload'] || $this->config['allow_avatar_local'] || $this->config['allow_avatar_remote']),
				'allow_avatar',
				1,
			)),
			array('config.add', array('allow_avatar_remote_upload', 0)),
			array('config.add_if', array(
				($this->config['allow_avatar_remote'] && $this->config['allow_avatar_upload']),
				'allow_avatar_remote_upload',
				1,
			)),

			array('module.add', array(
				'feed' => array(
					'base'		=> 'board',
					'class'		=> 'acp',
					'title'		=> 'ACP_FEED_SETTINGS',
					'auth'		=> 'acl_a_board',
					'cat'		=> 'ACP_BOARD_CONFIGURATION',
					'after'		=> array('signature', 'ACP_SIGNATURE_SETTINGS')
				),
			)),
			array('module.add', array(
				'warnings' => array(
					'base'		=> 'users',
					'class'		=> 'acp',
					'title'		=> 'ACP_USER_WARNINGS',
					'auth'		=> 'acl_a_user',
					'display'	=> 0,
					'cat'		=> 'ACP_CAT_USERS',
					'after'		=> array('feedback', 'ACP_USER_FEEDBACK')
				),
			)),
			array('module.add', array(
				'send_statistics' => array(
					'base'		=> 'send_statistics',
					'class'		=> 'acp',
					'title'		=> 'ACP_SEND_STATISTICS',
					'auth'		=> 'acl_a_server',
					'cat'		=> 'ACP_SERVER_CONFIGURATION'
				),
			)),
			array('module.add', array(
				'setting_forum_copy' => array(
					'base'		=> 'permissions',
					'class'		=> 'acp',
					'title'		=> 'ACP_FORUM_PERMISSIONS_COPY',
					'auth'		=> 'acl_a_fauth && acl_a_authusers && acl_a_authgroups && acl_a_mauth',
					'cat'		=> 'ACP_FORUM_BASED_PERMISSIONS',
					'after'		=> array('setting_forum_local', 'ACP_FORUM_PERMISSIONS')
				),
			)),
			array('module.add', array(
				'pm_reports' => array(
					'base'		=> 'pm_reports',
					'class'		=> 'mcp',
					'title'		=> 'MCP_PM_REPORTS_OPEN',
					'auth'		=> 'aclf_m_report',
					'cat'		=> 'MCP_REPORTS'
				),
			)),
			array('module.add', array(
				'pm_reports_closed' => array(
					'base'		=> 'pm_reports',
					'class'		=> 'mcp',
					'title'		=> 'MCP_PM_REPORTS_CLOSED',
					'auth'		=> 'aclf_m_report',
					'cat'		=> 'MCP_REPORTS'
				),
			)),
			array('module.add', array(
				'pm_report_details' => array(
					'base'		=> 'pm_reports',
					'class'		=> 'mcp',
					'title'		=> 'MCP_PM_REPORT_DETAILS',
					'auth'		=> 'aclf_m_report',
					'cat'		=> 'MCP_REPORTS'
				),
			)),
			array('custom', array(array(&$this, 'add_newly_registered_group'))),
			array('custom', array(array(&$this, 'set_user_options_default'))),
		);
	}

	function set_user_options_default()
	{
		// 229376 is the added value to enable all three signature options
		$sql = 'UPDATE ' . USERS_TABLE . ' SET user_options = user_options + 229376';
		$this->sql_query($sql);
	}

	function add_newly_registered_group()
	{
		// Add newly_registered group... but check if it already exists (we always supported running the updater on any schema)
		$sql = 'SELECT group_id
			FROM ' . GROUPS_TABLE . "
			WHERE group_name = 'NEWLY_REGISTERED'";
		$result = $this->db->sql_query($sql);
		$group_id = (int) $this->db->sql_fetchfield('group_id');
		$this->db->sql_freeresult($result);

		if (!$group_id)
		{
			$sql = 'INSERT INTO ' .  GROUPS_TABLE . " (group_name, group_type, group_founder_manage, group_colour, group_legend, group_avatar, group_desc, group_desc_uid, group_max_recipients) VALUES ('NEWLY_REGISTERED', 3, 0, '', 0, '', '', '', 5)";
			$this->sql_query($sql);

			$group_id = $this->db->sql_nextid();
		}

		// Insert new user role... at the end of the chain
		$sql = 'SELECT role_id
			FROM ' . ACL_ROLES_TABLE . "
			WHERE role_name = 'ROLE_USER_NEW_MEMBER'
				AND role_type = 'u_'";
		$result = $this->db->sql_query($sql);
		$u_role = (int) $this->db->sql_fetchfield('role_id');
		$this->db->sql_freeresult($result);

		if (!$u_role)
		{
			$sql = 'SELECT MAX(role_order) as max_order_id
				FROM ' . ACL_ROLES_TABLE . "
				WHERE role_type = 'u_'";
			$result = $this->db->sql_query($sql);
			$next_order_id = (int) $this->db->sql_fetchfield('max_order_id');
			$this->db->sql_freeresult($result);

			$next_order_id++;

			$sql = 'INSERT INTO ' . ACL_ROLES_TABLE . " (role_name, role_description, role_type, role_order) VALUES ('ROLE_USER_NEW_MEMBER', 'ROLE_DESCRIPTION_USER_NEW_MEMBER', 'u_', $next_order_id)";
			$this->sql_query($sql);
			$u_role = $this->db->sql_nextid();

			if (!$errored)
			{
				// Now add the correct data to the roles...
				// The standard role says that new users are not able to send a PM, Mass PM, are not able to PM groups
				$sql = 'INSERT INTO ' . ACL_ROLES_DATA_TABLE . " (role_id, auth_option_id, auth_setting) SELECT $u_role, auth_option_id, 0 FROM " . ACL_OPTIONS_TABLE . " WHERE auth_option LIKE 'u_%' AND auth_option IN ('u_sendpm', 'u_masspm', 'u_masspm_group')";
				$this->sql_query($sql);

				// Add user role to group
				$sql = 'INSERT INTO ' . ACL_GROUPS_TABLE . " (group_id, forum_id, auth_option_id, auth_role_id, auth_setting) VALUES ($group_id, 0, 0, $u_role, 0)";
				$this->sql_query($sql);
			}
		}

		// Insert new forum role
		$sql = 'SELECT role_id
			FROM ' . ACL_ROLES_TABLE . "
			WHERE role_name = 'ROLE_FORUM_NEW_MEMBER'
				AND role_type = 'f_'";
		$result = $this->db->sql_query($sql);
		$f_role = (int) $this->db->sql_fetchfield('role_id');
		$this->db->sql_freeresult($result);

		if (!$f_role)
		{
			$sql = 'SELECT MAX(role_order) as max_order_id
				FROM ' . ACL_ROLES_TABLE . "
				WHERE role_type = 'f_'";
			$result = $this->db->sql_query($sql);
			$next_order_id = (int) $this->db->sql_fetchfield('max_order_id');
			$this->db->sql_freeresult($result);

			$next_order_id++;

			$sql = 'INSERT INTO ' . ACL_ROLES_TABLE . " (role_name, role_description, role_type, role_order) VALUES  ('ROLE_FORUM_NEW_MEMBER', 'ROLE_DESCRIPTION_FORUM_NEW_MEMBER', 'f_', $next_order_id)";
			$this->sql_query($sql);
			$f_role = $this->db->sql_nextid();

			if (!$errored)
			{
				$sql = 'INSERT INTO ' . ACL_ROLES_DATA_TABLE . " (role_id, auth_option_id, auth_setting) SELECT $f_role, auth_option_id, 0 FROM " . ACL_OPTIONS_TABLE . " WHERE auth_option LIKE 'f_%' AND auth_option IN ('f_noapprove')";
				$this->sql_query($sql);
			}
		}

		// Set every members user_new column to 0 (old users) only if there is no one yet (this makes sure we do not execute this more than once)
		$sql = 'SELECT 1
			FROM ' . USERS_TABLE . '
			WHERE user_new = 0';
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			$sql = 'UPDATE ' . USERS_TABLE . ' SET user_new = 0';
			$this->sql_query($sql);
		}

		// To mimick the old "feature" we will assign the forum role to every forum, regardless of the setting (this makes sure there are no "this does not work!!!! YUO!!!" posts...
		// Check if the role is already assigned...
		$sql = 'SELECT forum_id
			FROM ' . ACL_GROUPS_TABLE . '
			WHERE group_id = ' . $group_id . '
				AND auth_role_id = ' . $f_role;
		$result = $this->db->sql_query($sql);
		$is_options = (int) $this->db->sql_fetchfield('forum_id');
		$this->db->sql_freeresult($result);

		// Not assigned at all... :/
		if (!$is_options)
		{
			// Get postable forums
			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				WHERE forum_type != ' . FORUM_LINK;
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->sql_query('INSERT INTO ' . ACL_GROUPS_TABLE . ' (group_id, forum_id, auth_option_id, auth_role_id, auth_setting) VALUES (' . $group_id . ', ' . (int) $row['forum_id'] . ', 0, ' . $f_role . ', 0)');
			}
			$this->db->sql_freeresult($result);
		}

		// Clear permissions...
		include_once($this->phpbb_root_path . 'includes/acp/auth.' . $this->phpEx);
		$auth_admin = new auth_admin();
		$auth_admin->acl_clear_prefetch();
	}
}
