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

class acp_storage_module extends migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT module_id
			FROM ' . $this->tables['modules'] . "
			WHERE module_class = 'acp'
				AND module_langname = 'ACP_STORAGE_SETTINGS'";
		$result = $this->db->sql_query($sql);
		$acp_storage_module_id = (int) $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		return !empty($acp_storage_module_id);
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
			['module.add', [
				'acp',
				'ACP_SERVER_CONFIGURATION',
				[
					'module_basename'	=> 'acp_storage',
					'module_langname'	=> 'ACP_STORAGE_SETTINGS',
					'module_mode'		=> 'settings',
					'module_auth'		=> 'acl_a_storage',
				],
			]],
		];
	}
}
