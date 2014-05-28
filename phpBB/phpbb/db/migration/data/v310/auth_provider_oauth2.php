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

class auth_provider_oauth2 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\auth_provider_oauth',
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(
				array($this, 'update_auth_link_module_auth'),
			)),
		);
	}

	public function update_auth_link_module_auth()
	{
		$sql = 'UPDATE ' . MODULES_TABLE . "
			SET module_auth = 'authmethod_oauth'
			WHERE module_class = 'ucp'
				AND module_basename = 'ucp_auth_link'
				AND module_mode = 'auth_link'
				AND module_auth = ''";
		$this->db->sql_query($sql);
	}
}
