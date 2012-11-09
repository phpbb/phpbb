<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_v3011rc1 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_v3010');
	}

	function update_schema()
	{
		return array();
	}

	function update_data()
	{
		// Updates users having current style a deactivated one
		$sql = 'SELECT style_id
			FROM ' . STYLES_TABLE . '
			WHERE style_active = 0';
		$result = $db->sql_query($sql);

		$deactivated_style_ids = array();
		while ($style_id = $db->sql_fetchfield('style_id', false, $result))
		{
			$deactivated_style_ids[] = (int) $style_id;
		}
		$db->sql_freeresult($result);

		if (!empty($deactivated_style_ids))
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_style = ' . (int) $config['default_style'] .'
				WHERE ' . $db->sql_in_set('user_style', $deactivated_style_ids);
			_sql($sql, $errored, $error_ary);
		}

		// Delete orphan private messages
		$batch_size = 500;

		$sql_array = array(
			'SELECT'	=> 'p.msg_id',
			'FROM'		=> array(
				PRIVMSGS_TABLE	=> 'p',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(PRIVMSGS_TO_TABLE => 't'),
					'ON'	=> 'p.msg_id = t.msg_id',
				),
			),
			'WHERE'		=> 't.user_id IS NULL',
		);
		$sql = $db->sql_build_query('SELECT', $sql_array);

		do
		{
			$result = $db->sql_query_limit($sql, $batch_size);

			$delete_pms = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$delete_pms[] = (int) $row['msg_id'];
			}
			$db->sql_freeresult($result);

			if (!empty($delete_pms))
			{
				$sql = 'DELETE FROM ' . PRIVMSGS_TABLE . '
					WHERE ' . $db->sql_in_set('msg_id', $delete_pms);
				_sql($sql, $errored, $error_ary);
			}
		}
		while (sizeof($delete_pms) == $batch_size);
	}
}
