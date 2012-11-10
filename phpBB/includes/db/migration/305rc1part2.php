<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_v305rc1part2 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_v305rc1');
	}

	function update_schema()
	{
		return array(
			'drop_keys'			=> array(
				ACL_OPTIONS_TABLE		=> array('auth_option'),
			),
			'add_unique_index'	=> array(
				ACL_OPTIONS_TABLE		=> array(
					'auth_option'		=> array('auth_option'),
				),
			),
		);
	}

	function update_data()
	{
	}
}
