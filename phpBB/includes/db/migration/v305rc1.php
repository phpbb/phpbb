<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_v305rc1 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_v304');
	}

	function update_schema()
	{
		return array(
			'change_columns' => array(
				$this->table_prefix . 'forums' => array(
					'forum_style' => array('UINT', 0),
				),
			),
		);
	}

	function update_data()
	{
		// Captcha config variables
		set_config('captcha_gd_wave', 0);
		set_config('captcha_gd_3d_noise', 1);
		set_config('captcha_gd_fonts', 1);
		set_config('confirm_refresh', 1);

		// Maximum number of keywords
		set_config('max_num_search_keywords', 10);

		// Remove static config var and put it back as dynamic variable
		$sql = 'UPDATE ' . CONFIG_TABLE . "
			SET is_dynamic = 1
			WHERE config_name = 'search_indexing_state'";
		_sql($sql, $errored, $error_ary);

		// Hash old MD5 passwords
		$sql = 'SELECT user_id, user_password
				FROM ' . USERS_TABLE . '
				WHERE user_pass_convert = 1';
		$result = _sql($sql, $errored, $error_ary);

		while ($row = $db->sql_fetchrow($result))
		{
			if (strlen($row['user_password']) == 32)
			{
				$sql_ary = array(
					'user_password'	=> phpbb_hash($row['user_password']),
				);

				_sql('UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE user_id = ' . $row['user_id'], $errored, $error_ary);
			}
		}
		$db->sql_freeresult($result);

		// Adjust bot entry
		$sql = 'UPDATE ' . BOTS_TABLE . "
			SET bot_agent = 'ichiro/'
			WHERE bot_agent = 'ichiro/2'";
		_sql($sql, $errored, $error_ary);


		// Before we are able to add a unique key to auth_option, we need to remove duplicate entries

		// We get duplicate entries first
		$sql = 'SELECT auth_option
			FROM ' . ACL_OPTIONS_TABLE . '
			GROUP BY auth_option
			HAVING COUNT(*) >= 2';
		$result = $db->sql_query($sql);

		$auth_options = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$auth_options[] = $row['auth_option'];
		}
		$db->sql_freeresult($result);

		// Remove specific auth options
		if (!empty($auth_options))
		{
			foreach ($auth_options as $option)
			{
				// Select auth_option_ids... the largest id will be preserved
				$sql = 'SELECT auth_option_id
					FROM ' . ACL_OPTIONS_TABLE . "
					WHERE auth_option = '" . $db->sql_escape($option) . "'
					ORDER BY auth_option_id DESC";
				// sql_query_limit not possible here, due to bug in postgresql layer
				$result = $db->sql_query($sql);

				// Skip first row, this is our original auth option we want to preserve
				$row = $db->sql_fetchrow($result);

				while ($row = $db->sql_fetchrow($result))
				{
					// Ok, remove this auth option...
					_sql('DELETE FROM ' . ACL_OPTIONS_TABLE . ' WHERE auth_option_id = ' . $row['auth_option_id'], $errored, $error_ary);
					_sql('DELETE FROM ' . ACL_ROLES_DATA_TABLE . ' WHERE auth_option_id = ' . $row['auth_option_id'], $errored, $error_ary);
					_sql('DELETE FROM ' . ACL_GROUPS_TABLE . ' WHERE auth_option_id = ' . $row['auth_option_id'], $errored, $error_ary);
					_sql('DELETE FROM ' . ACL_USERS_TABLE . ' WHERE auth_option_id = ' . $row['auth_option_id'], $errored, $error_ary);
				}
				$db->sql_freeresult($result);
			}
		}

		// Now make auth_option UNIQUE, by dropping the old index and adding a UNIQUE one.
		$changes = array(
			'drop_keys'			=> array(
				ACL_OPTIONS_TABLE		=> array('auth_option'),
			),
		);

		global $db_tools;

		$statements = $db_tools->perform_schema_changes($changes);

		foreach ($statements as $sql)
		{
			_sql($sql, $errored, $error_ary);
		}

		$changes = array(
			'add_unique_index'	=> array(
				ACL_OPTIONS_TABLE		=> array(
					'auth_option'		=> array('auth_option'),
				),
			),
		);

		$statements = $db_tools->perform_schema_changes($changes);

		foreach ($statements as $sql)
		{
			_sql($sql, $errored, $error_ary);
		}
	}
}
