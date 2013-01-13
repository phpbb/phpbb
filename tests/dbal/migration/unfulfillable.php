<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class phpbb_dbal_migration_unfulfillable extends phpbb_db_migration
{
	static public function depends_on()
	{
		return array('installed_migration', 'phpbb_dbal_migration_dummy', 'non_existant_migration');
	}

	function update_schema()
	{
		trigger_error('Schema update of migration with unfulfillable dependency was run!');
	}

	function update_data()
	{
		trigger_error('Data update of migration with unfulfillable dependency was run!');
	}
}
