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

namespace phpbb\db\migration\data\v320;

class announce_global_permission extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT auth_option_id
			FROM ' . ACL_OPTIONS_TABLE . "
			WHERE auth_option = 'f_announce_global'";
		$result = $this->db->sql_query($sql);
		$auth_option_id = $this->db->sql_fetchfield('auth_option_id');
		$this->db->sql_freeresult($result);

		return $auth_option_id !== false;
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v320\dev',
		);
	}

	public function update_data()
	{
		return array(
			array('permission.add', array('f_announce_global', false, 'f_announce')),
		);
	}
}
