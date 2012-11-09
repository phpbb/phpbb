<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_v303rc1 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_v302');
	}

	function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'styles_template' => array(
					'template_inherits_id' => array('UINT:4', 0),
					'template_inherit_path' => array('VCHAR', ''),
				),
				$this->table_prefix . 'groups' => array(
					'group_max_recipients' => array('UINT', 0),
				),
			),
		);
	}

	function update_data()
	{
		return array(
			array('config.add', array('enable_queue_trigger', '0')),
			array('config.add', array('queue_trigger_posts', '3')),
			array('config.add', array('pm_max_recipients', '0')),
			array('custom', array('set_group_default_max_recipients'))

			// Not prefilling yet
			set_config('dbms_version', '');

			// Add new permission u_masspm_group and duplicate settings from u_masspm
			include_once($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
			$auth_admin = new auth_admin();

			// Only add the new permission if it does not already exist
			if (empty($auth_admin->acl_options['id']['u_masspm_group']))
			{
				$auth_admin->acl_add_option(array('global' => array('u_masspm_group')));

				// Now the tricky part, filling the permission
				$old_id = $auth_admin->acl_options['id']['u_masspm'];
				$new_id = $auth_admin->acl_options['id']['u_masspm_group'];

				$tables = array(ACL_GROUPS_TABLE, ACL_ROLES_DATA_TABLE, ACL_USERS_TABLE);

				foreach ($tables as $table)
				{
					$sql = 'SELECT *
						FROM ' . $table . '
						WHERE auth_option_id = ' . $old_id;
					$result = _sql($sql, $errored, $error_ary);

					$sql_ary = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$row['auth_option_id'] = $new_id;
						$sql_ary[] = $row;
					}
					$db->sql_freeresult($result);

					if (sizeof($sql_ary))
					{
						$db->sql_multi_insert($table, $sql_ary);
					}
				}

				// Remove any old permission entries
				$auth_admin->acl_clear_prefetch();
			}

			/**
			* Do not resync post counts here. An admin may do this later from the ACP
			$start = 0;
			$step = ($config['num_posts']) ? (max((int) ($config['num_posts'] / 5), 20000)) : 20000;

			$sql = 'UPDATE ' . USERS_TABLE . ' SET user_posts = 0';
			_sql($sql, $errored, $error_ary);

			do
			{
				$sql = 'SELECT COUNT(post_id) AS num_posts, poster_id
					FROM ' . POSTS_TABLE . '
					WHERE post_id BETWEEN ' . ($start + 1) . ' AND ' . ($start + $step) . '
						AND post_postcount = 1 AND post_approved = 1
					GROUP BY poster_id';
				$result = _sql($sql, $errored, $error_ary);

				if ($row = $db->sql_fetchrow($result))
				{
					do
					{
						$sql = 'UPDATE ' . USERS_TABLE . " SET user_posts = user_posts + {$row['num_posts']} WHERE user_id = {$row['poster_id']}";
						_sql($sql, $errored, $error_ary);
					}
					while ($row = $db->sql_fetchrow($result));

					$start += $step;
				}
				else
				{
					$start = 0;
				}
				$db->sql_freeresult($result);
			}
			while ($start);
			*/

			$sql = 'UPDATE ' . MODULES_TABLE . '
				SET module_auth = \'acl_a_email && cfg_email_enable\'
				WHERE module_class = \'acp\'
					AND module_basename = \'email\'';
			_sql($sql, $errored, $error_ary);
	}

	function set_group_default_max_recipients()
	{
		// Set maximum number of recipients for the registered users, bots, guests group
		$sql = 'UPDATE ' . GROUPS_TABLE . ' SET group_max_recipients = 5
			WHERE ' . $this->db->sql_in_set('group_name', array('GUESTS', 'REGISTERED', 'REGISTERED_COPPA', 'BOTS'));
		$this->sql_query($sql);
	}

}
