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

namespace phpbb\db\migration\data\v310;

class contact_admin_acp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT module_id
			FROM ' . MODULES_TABLE . "
			WHERE module_class = 'acp'
				AND module_basename = 'acp_contact'
				AND module_langname = 'ACP_CONTACT_SETTINGS'";
		$result = $this->db->sql_query($sql);
		$module_id = $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		return $module_id != false;
	}

	public function update_data()
	{
		return array(
			array('module.add', array(
				'acp',
				'ACP_BOARD_CONFIGURATION',
				array(
					'module_basename'	=> 'acp_contact',
					'module_langname'	=> 'ACP_CONTACT_SETTINGS',
					'module_mode'		=> 'contact',
					'module_auth'		=> 'acl_a_board',
				),
			)),
		);
	}
}
