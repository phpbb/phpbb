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

namespace phpbb\ban\type;

class helper
{
	/**
	 * Config object
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * Database object
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * Construct a type helper object
	 *
	 * @param \phpbb\config\config				$config	Config object
	 * @param \phpbb\db\driver\driver_interface $db		Database object
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db)
	{
		$this->config = $config;
		$this->db = $db;
	}

	/**
	 * Gets an array of founder.
	 *
	 * @param array|string	$columns	The data column to be retrieved from the users table.
	 *
	 * @return array		Returns an array of user_ids being the key and the content of the
	 *               		data column for the specific row being the value.
	 */
	public function get_founder($columns = 'username_clean')
	{
		$founders = array();

		if (!is_array($columns))
		{
			$columns = array($columns);
		}

		$sql = 'SELECT user_id, ' . implode(', ', $columns) . "
			FROM " . USERS_TABLE . '
			WHERE user_type = ' . USER_FOUNDER;
		$result = $this->db->sql_query($sql, 3600);

		$size = sizeof($columns);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($size === 1)
			{
				$founders[$row['user_id']] = $row[$columns[0]];
			}
			else
			{
				$founders[$row['user_id']] = array();
				foreach ($columns as $column)
				{
					if ($column == 'user_id')
					{
						continue;
					}

					$founders[$row['user_id']][$column] = $row[$column];
				}
			}
		}
		$this->db->sql_freeresult($result);

		return $founders;
	}

	/**
	 * Removes duplicate bans from the given database table
	 * so they can be re-inserted with the given new length
	 *
	 * @param array		$banned_items	Array of entities to ban
	 * @param string	$table			The database table the duplicate bans should be removed from
	 * @param string	$column			The column in the database which should be checked
	 * @param string	$column_type	The type of the column as a string (e.g. 'int')
	 * @param bool		$ban_exclude	Are they excluded from banning?
	 */
	public function remove_duplicate_bans(array $banned_items, $table, $column, $column_type, $ban_exclude)
	{
		$duplicate_bans = array();

		$sql = "SELECT $column
			FROM $table
			WHERE $column <> " . (($column_type == 'int') ? '0' : "''") . '
				AND ban_exclude = ' . (int) $ban_exclude;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$duplicate_bans[] = $row[$column];
		}
		$this->db->sql_freeresult($result);

		$duplicate_bans = array_intersect($banned_items, $duplicate_bans);

		if (!empty($duplicate_bans))
		{
			$sql = "DELETE FROM $table
				WHERE " . $this->db->sql_in_set($column, $duplicate_bans) . '
					AND ban_exclude = ' . (int) $ban_exclude;
			$this->db->sql_query($sql);
		}
	}
}