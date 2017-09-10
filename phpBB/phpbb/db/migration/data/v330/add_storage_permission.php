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

namespace phpbb\db\migration\data\v330;

class add_storage_permission extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return array(
			// Add permission
			array('permission.add', array('a_storage')),

			// Set permissions
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_storage')),
		);
	}
}
