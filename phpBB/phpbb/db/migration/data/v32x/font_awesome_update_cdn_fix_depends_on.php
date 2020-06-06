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

namespace phpbb\db\migration\data\v32x;

class font_awesome_update_cdn_fix_depends_on extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v32x\font_awesome_update_cdn',
			'\phpbb\db\migration\data\v32x\add_missing_config',
		];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'fix_depends_on']]],
		];
	}

	public function fix_depends_on()
	{
		$migration_class = '\phpbb\db\migration\data\v32x\font_awesome_update_cdn';
		$migration_depends_on = $migration_class::depends_on();

		$sql = 'UPDATE ' . $this->table_prefix . "migrations
			SET migration_depends_on = '" . $this->db->sql_escape(serialize($migration_depends_on)) . "'
			WHERE migration_name = ' . $migration_class . '";

		$this->db->sql_query($sql);
	}
}
