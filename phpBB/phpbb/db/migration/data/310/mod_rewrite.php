<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_310_mod_rewrite extends phpbb_db_migration
{
	static public function depends_on()
	{
		return array(
			'phpbb_db_migration_data_310_dev',
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('enable_mod_rewrite', '0')),
		);
	}
}
