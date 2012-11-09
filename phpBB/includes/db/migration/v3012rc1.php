<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_v3012rc1 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_v3011');
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
