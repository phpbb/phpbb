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

class acp_storage_module extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return array(
			array('module.add', array(
				'acp',
				'ACP_SERVER_CONFIGURATION',
				array(
					'module_basename'	=> 'acp_storage',
					'module_langname'	=> 'ACP_STORAGE_SETTINGS',
					'module_mode'		=> 'settings',
					'module_auth'		=> 'acl_a_storage',
				),
			)),
		);
	}
}
