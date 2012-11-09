<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_v307rc2 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_v307rc1');
	}

	function update_schema()
	{
		return array();
	}

	function update_data()
	{
		$sql = 'SELECT user_id, user_email, user_email_hash
			FROM ' . USERS_TABLE . '
			WHERE user_type <> ' . USER_IGNORE . "
				AND user_email <> ''";
		$result = $db->sql_query($sql);

		$i = 0;
		while ($row = $db->sql_fetchrow($result))
		{
			// Snapshot of the phpbb_email_hash() function
			// We cannot call it directly because the auto updater updates the DB first. :/
			$user_email_hash = sprintf('%u', crc32(strtolower($row['user_email']))) . strlen($row['user_email']);

			if ($user_email_hash != $row['user_email_hash'])
			{
				$sql_ary = array(
					'user_email_hash'	=> $user_email_hash,
				);

				$sql = 'UPDATE ' . USERS_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE user_id = ' . (int) $row['user_id'];
				_sql($sql, $errored, $error_ary, ($i % 100 == 0));

				++$i;
			}
		}
		$db->sql_freeresult($result);
	}
}
