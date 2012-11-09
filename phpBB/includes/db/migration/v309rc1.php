<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_v309rc1 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_v308');
	}

	function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . 'login_attempts' => array(
					'COLUMNS' => array(
						// this column was removed from the database updater
						// after 3.0.9-RC3 was released. It might still exist
						// in 3.0.9-RCX installations and has to be dropped in
						// 3.0.12 after the db_tools class is capable of properly
						// removing a primary key.
						// 'attempt_id'			=> array('UINT', NULL, 'auto_increment'),
						'attempt_ip'			=> array('VCHAR:40', ''),
						'attempt_browser'		=> array('VCHAR:150', ''),
						'attempt_forwarded_for'	=> array('VCHAR:255', ''),
						'attempt_time'			=> array('TIMESTAMP', 0),
						'user_id'				=> array('UINT', 0),
						'username'				=> array('VCHAR_UNI:255', 0),
						'username_clean'		=> array('VCHAR_CI', 0),
					),
					//'PRIMARY_KEY' => 'attempt_id',
					'KEYS' => array(
						'att_ip' => array('INDEX', array('attempt_ip', 'attempt_time')),
						'att_for' => array('INDEX', array('attempt_forwarded_for', 'attempt_time')),
						'att_time' => array('INDEX', array('attempt_time')),
						'user_id' => array('INDEX', 'user_id'),
					),
				),
			),
			'change_columns' => array(
				$this->table_prefix . 'bbcode' => array(
					'bbcode_id' => array('USINT', 0),
				),
			),
		);
	}

	function update_data()
	{
		set_config('ip_login_limit_max', '50');
		set_config('ip_login_limit_time', '21600');
		set_config('ip_login_limit_use_forwarded', '0');

		// Update file extension group names to use language strings, again.
		$sql = 'SELECT group_id, group_name
			FROM ' . EXTENSION_GROUPS_TABLE . '
			WHERE group_name ' . $db->sql_like_expression('EXT_GROUP_' . $db->any_char);
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$sql_ary = array(
				'group_name'	=> substr($row['group_name'], 10), // Strip off 'EXT_GROUP_'
			);

			$sql = 'UPDATE ' . EXTENSION_GROUPS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE group_id = ' . $row['group_id'];
			_sql($sql, $errored, $error_ary);
		}
		$db->sql_freeresult($result);

		global $db_tools, $table_prefix;

		// Recover from potentially broken Q&A CAPTCHA table on firebird
		// Q&A CAPTCHA was uninstallable, so it's safe to remove these
		// without data loss
		if ($db_tools->sql_layer == 'firebird')
		{
			$tables = array(
				$table_prefix . 'captcha_questions',
				$table_prefix . 'captcha_answers',
				$table_prefix . 'qa_confirm',
			);
			foreach ($tables as $table)
			{
				if ($db_tools->sql_table_exists($table))
				{
					$db_tools->sql_table_drop($table);
				}
			}
		}
	}
}
