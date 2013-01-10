<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_3_0_7 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_data_3_0_7_rc2');
	}

	function update_data()
	{
		return array(
			array('config.update', array('version', '3.0.7')),
		);
	}
}
