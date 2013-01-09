<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_3_0_12_rc1 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_data_3_0_11');
	}

	function update_schema()
	{
		/** @todo DROP LOGIN_ATTEMPT_TABLE.attempt_id in 3.0.12-RC1 */
		return array();
	}

	function update_data()
	{
	}
}
