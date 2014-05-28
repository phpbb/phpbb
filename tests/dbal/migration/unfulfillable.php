<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

class phpbb_dbal_migration_unfulfillable extends \phpbb\db\migration\migration
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
