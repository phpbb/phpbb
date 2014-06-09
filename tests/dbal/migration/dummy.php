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

class phpbb_dbal_migration_dummy extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('installed_migration');
	}

	function update_schema()
	{
		return array(
			'add_columns' => array(
				'phpbb_config' => array(
					'extra_column' => array('UINT', 1),
				),
			),
		);
	}
}
