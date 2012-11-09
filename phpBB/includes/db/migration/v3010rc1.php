<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_v3010rc1 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_v309');
	}

	function update_schema()
	{
		return array();
	}

	function update_data()
	{
		if (!isset($config['email_max_chunk_size']))
		{
			set_config('email_max_chunk_size', '50');
		}
	}
}
