<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_3_0_9_rc1 extends phpbb_db_migration
{
	static public function depends_on()
	{
		return array('phpbb_db_migration_data_3_0_8');
	}

	public function update_schema()
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
				$this->table_prefix . 'bbcodes' => array(
					'bbcode_id' => array('USINT', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables' => array(
				$this->table_prefix . 'login_attempts',
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('ip_login_limit_max', 50)),
			array('config.add', array('ip_login_limit_time', 21600)),
			array('config.add', array('ip_login_limit_use_forwarded', 0)),
			array('custom', array(array(&$this, 'update_file_extension_group_names'))),
			array('custom', array(array(&$this, 'fix_firebird_qa_captcha'))),

			array('config.update', array('version', '3.0.9-rc1')),
		);
	}

	public function update_file_extension_group_names()
	{
		// Update file extension group names to use language strings, again.
		$sql = 'SELECT group_id, group_name
			FROM ' . EXTENSION_GROUPS_TABLE . '
			WHERE group_name ' . $this->db->sql_like_expression('EXT_GROUP_' . $this->db->any_char);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$sql_ary = array(
				'group_name'	=> substr($row['group_name'], 10), // Strip off 'EXT_GROUP_'
			);

			$sql = 'UPDATE ' . EXTENSION_GROUPS_TABLE . '
				SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE group_id = ' . $row['group_id'];
			$this->sql_query($sql);
		}
		$this->db->sql_freeresult($result);
	}

	public function fix_firebird_qa_captcha()
	{
		// Recover from potentially broken Q&A CAPTCHA table on firebird
		// Q&A CAPTCHA was uninstallable, so it's safe to remove these
		// without data loss
		if ($this->db_tools->sql_layer == 'firebird')
		{
			$tables = array(
				$this->table_prefix . 'captcha_questions',
				$this->table_prefix . 'captcha_answers',
				$this->table_prefix . 'qa_confirm',
			);
			foreach ($tables as $table)
			{
				if ($this->db_tools->sql_table_exists($table))
				{
					$this->db_tools->sql_table_drop($table);
				}
			}
		}
	}
}
