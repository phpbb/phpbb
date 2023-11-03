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

namespace phpbb\db\migration\data\v400;

use phpbb\db\migration\migration;

class add_storage_permission extends migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT auth_option_id
			FROM ' . $this->tables['acl_options'] . "
			WHERE auth_option = 'a_storage'";
		$result = $this->db->sql_query($sql);
		$a_storage_option_id = (int) $this->db->sql_fetchfield('auth_option_id');
		$this->db->sql_freeresult($result);

		return !empty($a_storage_option_id);
	}

	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v400\dev',
		];
	}

	public function update_data()
	{
		return [
			// Add permission
			['permission.add', ['a_storage']],

			// Set permissions
			['if', [
				['permission.role_exists', ['ROLE_ADMIN_FULL']],
				['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_storage']],
			]],
		];
	}
}
