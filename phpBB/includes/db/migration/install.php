<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_install
{
	/**
	* List of phpBB versions/what migration identifier
	* 	contains the updates that should have been installed already
	*
	* @var array
	*/
	protected $version_to_migration = array(
		'3.0.1-rc1'		=> 'phpbb_db_migration_data_3_0_1_rc1',
		'3.0.1'	   		=> 'phpbb_db_migration_data_3_0_1',
		'3.0.2-rc1'		=> 'phpbb_db_migration_data_3_0_2_rc1',
		'3.0.2-rc2'		=> 'phpbb_db_migration_data_3_0_2_rc2',
		'3.0.2'	   		=> 'phpbb_db_migration_data_3_0_2',
		'3.0.3-rc1'		=> 'phpbb_db_migration_data_3_0_3_rc1',
		'3.0.3'	   		=> 'phpbb_db_migration_data_3_0_3',
		'3.0.4-rc1'		=> 'phpbb_db_migration_data_3_0_4_rc1',
		'3.0.4'	   		=> 'phpbb_db_migration_data_3_0_4',
		'3.0.5-rc1'		=> array(
			'phpbb_db_migration_data_3_0_5_rc1',
			'phpbb_db_migration_data_3_0_5_rc1part2',
		),
		'3.0.5'	   		=> 'phpbb_db_migration_data_3_0_5',
		'3.0.6-rc1'		=> 'phpbb_db_migration_data_3_0_6_rc1',
		'3.0.6-rc2'		=> 'phpbb_db_migration_data_3_0_6_rc2',
		'3.0.6-rc3'		=> 'phpbb_db_migration_data_3_0_6_rc3',
		'3.0.6-rc4'		=> 'phpbb_db_migration_data_3_0_6_rc4',
		'3.0.6'	   		=> 'phpbb_db_migration_data_3_0_6',
		'3.0.7-rc1'		=> 'phpbb_db_migration_data_3_0_7_rc1',
		'3.0.7-rc2'		=> 'phpbb_db_migration_data_3_0_7_rc2',
		'3.0.7'	   		=> 'phpbb_db_migration_data_3_0_7',
		'3.0.7-pl1'		=> 'phpbb_db_migration_data_3_0_7_pl1',
		'3.0.8-rc1'		=> 'phpbb_db_migration_data_3_0_8_rc1',
		'3.0.8'			=> 'phpbb_db_migration_data_3_0_8',
		'3.0.9-rc1'     => 'phpbb_db_migration_data_3_0_9_rc1',
		'3.0.9-rc2'     => 'phpbb_db_migration_data_3_0_9_rc2',
		'3.0.9-rc3'     => 'phpbb_db_migration_data_3_0_9_rc3',
		'3.0.9-rc4'     => 'phpbb_db_migration_data_3_0_9_rc4',
		'3.0.9'			=> 'phpbb_db_migration_data_3_0_9',
		'3.0.10-rc1'    => 'phpbb_db_migration_data_3_0_10_rc1',
		'3.0.10-rc2'    => 'phpbb_db_migration_data_3_0_10_rc2',
		'3.0.10-rc3'    => 'phpbb_db_migration_data_3_0_10_rc3',
		'3.0.10'		=> 'phpbb_db_migration_data_3_0_10',
		'3.0.11-rc1'	=> 'phpbb_db_migration_data_3_0_11_rc1',
		'3.0.11-rc2'	=> 'phpbb_db_migration_data_3_0_11_rc2',
		'3.0.11'		=> 'phpbb_db_migration_data_3_0_11',
		'3.0.12-rc1'	=> 'phpbb_db_migration_data_3_0_12_rc1',
		'3.1.0-dev'		=> array(
			'phpbb_db_migration_data_style_update_p1',
			'phpbb_db_migration_data_style_update_p2',
			'phpbb_db_migration_data_timezone',
			'phpbb_db_migration_data_timezone_p2',
			'phpbb_db_migration_data_extensions',
			'phpbb_db_migration_data_3_1_0_dev',
		),
	);

	public function install(phpbb_db_driver $db, phpbb_db_tools $db_tools, $table_prefix, $version)
	{
		$this->create_table($db_tools, $table_prefix);

		$this->guess_installed_migrations($db, $table_prefix, $version);
	}

	protected function create_table(phpbb_db_tools $db_tools, $table_prefix)
	{
		if (!$db_tools->sql_table_exists($table_prefix . 'migrations'))
		{
			$db_tools->sql_create_table($table_prefix . 'migrations', array(
				'COLUMNS'		=> array(
					'migration_name'			=> array('VCHAR', ''),
					'migration_depends_on'		=> array('TEXT', ''),
					'migration_schema_done'		=> array('BOOL', 0),
					'migration_data_done'		=> array('BOOL', 0),
					'migration_data_state'		=> array('TEXT', ''),
					'migration_start_time'		=> array('TIMESTAMP', 0),
					'migration_end_time'		=> array('TIMESTAMP', 0),
				),
				'PRIMARY_KEY'	=> 'migration_name',
			));
		}
	}

	/**
	* Guess what migrations have been installed based on phpBB version
	*
	* @param mixed $version
	*/
	protected function guess_installed_migrations(phpbb_db_driver $db, $table_prefix, $version)
	{
		$installed = array();
		foreach ($this->version_to_migration as $compare => $migration_list)
		{
			if (version_compare($version, $compare, '>='))
			{
				// The migration should have effectively been installed already
				if (!is_array($migration_list))
				{
					$migration_list = array($migration_list);
				}

				foreach ($migration_list as $migration_name)
				{
					$sql = 'SELECT 1 FROM ' . $table_prefix . "migrations
						WHERE migration_name = '" . $db->sql_escape($migration_name) . "'";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!$row)
					{
						$sql_ary = array(
							'migration_name'			=> $migration_name,
							'migration_depends_on'		=> serialize($migration_name::depends_on()),
							'migration_schema_done'		=> 1,
							'migration_data_done'		=> 1,
							'migration_data_state'		=> '',
							'migration_start_time'		=> 0,
							'migration_end_time'		=> 0,
						);
						$sql = 'INSERT INTO ' . $table_prefix . 'migrations ' .
							$db->sql_build_array('INSERT', $sql_ary);
						$db->sql_query($sql);
					}
				}
			}
		}
	}
}
