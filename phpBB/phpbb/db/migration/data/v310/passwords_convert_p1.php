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

namespace phpbb\db\migration\data\v310;

class passwords_convert_p1 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\passwords_p2');
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'update_passwords'))),
		);
	}

	public function update_passwords($start)
	{
		// Nothing to do if user_pass_convert column doesn't exist
		if (!$this->db_tools->sql_column_exists($this->table_prefix . 'users', 'user_pass_convert'))
		{
			return;
		}

		$start = (int) $start;
		$limit = 1000;
		$converted_users = 0;

		$sql = 'SELECT user_password, user_id
			FROM ' . $this->table_prefix . 'users
			WHERE user_pass_convert = 1
			ORDER BY user_id';
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		$update_users = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$converted_users++;

			$user_id = (int) $row['user_id'];
			// Only prefix passwords without proper prefix
			if (!isset($update_users[$user_id]) && !preg_match('#^\$([a-zA-Z0-9\\\]*?)\$#', $row['user_password']))
			{
				// Use $CP$ prefix for passwords that need to
				// be converted and set pass convert to false.
				$update_users[$user_id] = array(
					'user_password'		=> '$CP$' . $row['user_password'],
					'user_pass_convert'	=> 0,
				);
			}
		}
		$this->db->sql_freeresult($result);

		foreach ($update_users as $user_id => $user_data)
		{
			$sql = 'UPDATE ' . $this->table_prefix . 'users
				SET ' . $this->db->sql_build_array('UPDATE', $user_data) . '
				WHERE user_id = ' . $user_id;
			$this->sql_query($sql);
		}

		if ($converted_users < $limit)
		{
			// There are no more users to be converted
			return;
		}

		// There are still more users to query, return the next start value
		return $start + $limit;
	}
}
