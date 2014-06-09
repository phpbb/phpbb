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

class signature_module_auth extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT module_auth
			FROM ' . MODULES_TABLE . "
			WHERE module_class = 'ucp'
				AND module_basename = 'ucp_profile'
				AND module_mode = 'signature'";
		$result = $this->db->sql_query($sql);
		$module_auth = $this->db->sql_fetchfield('module_auth');
		$this->db->sql_freeresult($result);

		return $module_auth === 'acl_u_sig' || $module_auth === false;
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_data()
	{
		return array(
			array('custom', array(
					array($this, 'update_signature_module_auth'),
				),
			),
		);
	}

	public function update_signature_module_auth()
	{
		$sql = 'UPDATE ' . MODULES_TABLE . "
			SET module_auth = 'acl_u_sig'
			WHERE module_class = 'ucp'
				AND module_basename = 'ucp_profile'
				AND module_mode = 'signature'
				AND module_auth = ''";
		$this->db->sql_query($sql);
	}
}
