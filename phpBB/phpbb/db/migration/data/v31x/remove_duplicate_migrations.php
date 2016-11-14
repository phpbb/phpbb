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

namespace phpbb\db\migration\data\v31x;

class remove_duplicate_migrations extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v3110');
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'deduplicate_entries'))),
		);
	}

	public function deduplicate_entries()
	{
		$migration_state = array();
		$duplicate_migrations = array();

		$sql = "SELECT *
			FROM " . $this->table_prefix . 'migrations';
		$result = $this->db->sql_query($sql);

		if (!$this->db->get_sql_error_triggered())
		{
			while ($migration = $this->db->sql_fetchrow($result))
			{
				$migration_state[$migration['migration_name']] = $migration;

				$migration_state[$migration['migration_name']]['migration_depends_on'] = unserialize($migration['migration_depends_on']);
			}
		}

		$this->db->sql_freeresult($result);

		foreach ($migration_state as $name => $migration)
		{
			$prepended_name = ($name[0] == '\\' ? '' : '\\') . $name;
			$prefixless_name = $name[0] == '\\' ? substr($name, 1) : $name;

			if ($prepended_name != $name && isset($migration_state[$prepended_name]) && $migration_state[$prepended_name]['migration_depends_on'] == $migration_state[$name]['migration_depends_on'])
			{
				$duplicate_migrations[] = $name;
				unset($migration_state[$prepended_name]);
			}
			else if ($prefixless_name != $name && isset($migration_state[$prefixless_name]) && $migration_state[$prefixless_name]['migration_depends_on'] == $migration_state[$name]['migration_depends_on'])
			{
				$duplicate_migrations[] = $prefixless_name;
				unset($migration_state[$prefixless_name]);
			}
		}

		if (count($duplicate_migrations))
		{
			$sql = 'DELETE
				FROM ' . $this->table_prefix . 'migrations
				WHERE '  . $this->db->sql_in_set('migration_name', $duplicate_migrations);
			$this->db->sql_query($sql);
		}
	}
}
