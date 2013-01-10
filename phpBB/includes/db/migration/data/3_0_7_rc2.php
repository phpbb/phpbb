<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_3_0_7_rc2 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_data_3_0_7_rc1');
	}

	function update_data()
	{
		return array(
			array('custom', array(array(&$this, 'update_email_hash'))),

			array('config.update', array('version', '3.0.7-rc2')),
		);
	}

	function update_email_hash($start = 0)
	{
		$limit = 1000;

		$sql = 'SELECT user_id, user_email, user_email_hash
			FROM ' . USERS_TABLE . '
			WHERE user_type <> ' . USER_IGNORE . "
				AND user_email <> ''";
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		$i = 0;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$i++;

			// Snapshot of the phpbb_email_hash() function
			// We cannot call it directly because the auto updater updates the DB first. :/
			$user_email_hash = sprintf('%u', crc32(strtolower($row['user_email']))) . strlen($row['user_email']);

			if ($user_email_hash != $row['user_email_hash'])
			{
				$sql_ary = array(
					'user_email_hash'	=> $user_email_hash,
				);

				$sql = 'UPDATE ' . USERS_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE user_id = ' . (int) $row['user_id'];
				$this->sql_query($sql);
			}
		}
		$this->db->sql_freeresult($result);

		if ($i < $limit)
		{
			// Completed
			return;
		}

		return $start + $limit;
	}
}
