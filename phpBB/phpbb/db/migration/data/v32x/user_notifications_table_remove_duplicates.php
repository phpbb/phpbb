<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\db\migration\data\v32x;

class user_notifications_table_remove_duplicates extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v32x\user_notifications_table_temp_index',
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'remove_duplicates'))),
		);
	}

	public function remove_duplicates()
	{
		$insert_buffer = new \phpbb\db\sql_insert_buffer($this->db, $this->table_prefix . 'user_notifications');

		$sql = "SELECT item_type, item_id, user_id, method, MAX(notify) AS notify
				FROM {$this->table_prefix}user_notifications
				GROUP BY item_type, item_id, user_id, method
				HAVING COUNT(item_type) > 1";

		$result = $this->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			// Delete the duplicate entries
			$this->sql_query("DELETE FROM {$this->table_prefix}user_notifications
								WHERE user_id = {$row['user_id']}
								  AND item_type = '{$row['item_type']}'
								  AND method = '{$row['method']}'");

			// And re-insert as a single one
			$insert_buffer->insert($row);
		}
		$this->db->sql_freeresult($result);
	}
}
