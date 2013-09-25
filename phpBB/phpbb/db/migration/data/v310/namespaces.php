<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class namespaces extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\dev',
		);
	}

	public function update_data()
	{
		return array(
			array('if', array(
				(preg_match('#^phpbb_search_#', $this->config['search_type'])),
				array('config.update', array('search_type', str_replace('phpbb_search_', 'phpbb\\search\\', $this->config['search_type']))),
			)),
			array('custom', array(array($this, 'update_migrations'))),
		);
	}

	public function update_migrations()
	{
		$table = $this->table_prefix . 'migrations';

		$sql = 'SELECT *
			FROM migrations';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$migration_name = $row['migration_name'];

			$row['migration_name'] = $this->namespacify($row['migration_name']);

			$depends_on = ($row['migration_depends_on']) ? unserialize($row['migration_depends_on']) : false;

			if ($depends_on)
			{
				$depends_on_new = array();

				foreach ($depends_on as $migration)
				{
					$depends_on_new[] = $this->namespacify($migration);
				}

				$depends_on = serialize($depends_on_new);
				$row['migration_depends_on'] = $depends_on;
			}

			$sql_update = $this->db->sql_build_array('UPDATE', $row);

			$sql = 'UPDATE ' . MODULES_TABLE . '
				SET ' . $sql_update . '
				WHERE module_id = ' . $migration_name;
			$this->sql_query($sql);
		}
	}

	public function namespacify($migration_name)
	{
		$parts = explode('_', $migration_name);

		$namespace = '';
		$class = '';

		while (count($parts) > 1 && (!$class || !class_exists($class)))
		{
			$namespace = $namespace . '\\' . array_shift($parts);
			$class = $namespace . '\\' . implode('_', $parts);
		}

		return $class;
	}
}
