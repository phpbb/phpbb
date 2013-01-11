<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_3_0_9 extends phpbb_db_migration
{
	static public function depends_on()
	{
		return array('phpbb_db_migration_data_3_0_9_rc4');
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.0.9')),
		);
	}
}
